<?php

namespace App\Services\Auth;

use App\Models\AccessToken;
use App\Models\User;
use App\Models\Account;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\WebSocket\Publishers\RedisPublisher;


class AuthService
{

    protected $redisPublisher;
    protected $startCoin = 2000;  // Стартовый бонусный баланс
    
    public function __construct(RedisPublisher $redisPublisher)
    {
        $this->redisPublisher = $redisPublisher;
    }


    // Регистрация нового пользователя
    public function register($request)
    {
        Log::info("Начинаю регистрацию пользователя", $request);
        $userId = $request['user_id'];

        // Определяем тип регистрации
        if (array_key_exists('messanger', $request)) {
            $authType = 'messanger';
            $messanger = $request['messanger'];
            $chatId = $request['chat_id'];
        } else {
            $authType = 'email';
            $email = $request['email'];
        }

        $accessToken = $request['access_token'];
        if (array_key_exists('token_info', $request)) {
            $tokenInfo = $request['token_info'];
            Log::info("Токен пришел с полной информацией об источники: ", $tokenInfo);
        } else {
            $tokenInfo = $this->nullTokenInfo();
            Log::error("Токен пришел без инфомрации о источнике");
        }

        $user = User::where('external_id', $userId)->first();
        if ($user) {
            Log::info('Аккаунт этого пользователя уже зарегистрирован', $user->toArray());
            Log::info('Обновляю токен ...');
            try {
                $token = $this->addToken($user, $accessToken, $tokenInfo);
                return $token;
            } catch (\Exception $e) {
                Log::error("Ошибка обновления токена", ['error' => $e->getMessage(), 'user_id' => $user->id ?? null]);
                return false;
            }
        }
        DB::beginTransaction();
        try {
            if ($authType == 'messanger') {
                $user = User::create(['external_id' => $userId, 'role' => 'admin', 'status' => 'active', 'color' => '#ff0000', 'messanger' => $messanger, 'chat_id' => $chatId]);
            } elseif ($authType == 'email') {
                $user = User::create(['external_id' => $userId, 'role' => 'admin', 'status' => 'active', 'color' => '#ff0000', 'email' => $email]);
            }
            $token = $this->addToken($user, $accessToken, $tokenInfo);
            $account = Account::create(['user_id' => $user->id, 'balance' => 0, 'bonus_balance' => $this->startCoin]);
            $workspace = Workspace::create(['account_id' => $account->id, 'title' => 'Рабочее пространство', 'status' => 'active']);
            $user->workspaces()->attach($workspace->id);

            DB::commit();
            Log::info("Пользователь успешно создан", ['user_id' => $user->id, 'account_id' => $account->id,'workspace_id' => $workspace->id]);
            return $token;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Ошибка при создании пользователя: " . $e->getMessage(), ['request' => $request]);
            throw $e;
        }
    }


    public function login($request)
    {
        Log::info("Начинаю авторизацию пользователя", $request);
        $userId = $request['user_id'];
        $accessToken = $request['access_token'];
        if (array_key_exists('token_info', $request)) {
            $tokenInfo = $request['token_info'];
            Log::info("Токен пришел с полной информацией об источники: ", $tokenInfo);
        } else {
            $tokenInfo = $this->nullTokenInfo();
            Log::error("Токен пришел без инфомрации о источнике");
        }

        $user = User::where('external_id', $userId)->first();
        if (!$user) {
            Log::error('Аккаунт пользователя не найден');
            return false;
        }
        try {
            $token = $this->addToken($user, $accessToken, $tokenInfo);
            Log::info("Пользователь успешно авторизован", $user->toArray());
            return $token;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Ошибка авторизации: " . $e->getMessage(), ['request' => $request]);
            throw $e;
        }
    }


    public function logout($token) {
        Log::info("Начинаю logout пользователя. Токен: $token");
        try {
            AccessToken::where('token', $token)->delete();
            Log::info('logout success');
            return true;
        } catch (\Exception $e) {
            Log::error("logout error. Токен: $token");
            return false;
        }
    }

    private function addToken($user, $accessToken, $tokenInfo) {
        $allowedSessionsCount = env('PROFILE_ALLOWED_SESSIONS_COUNT', 3);
        $activeSeesions = AccessToken::where('user_id', $user->id)->get();
        $activeSeesionsCount = $activeSeesions->count();
        Log::info("Количество текущий активных сессий: $activeSeesionsCount");
        if ($activeSeesions->count() >= $allowedSessionsCount) {
            $oldSession = $activeSeesions->first()?->toArray();
            Log::info("Завершаю самую старую сессию, чтобы освободить место для новой: ", $oldSession);
            AccessToken::where('id', $oldSession['id'])->delete();
            Log::info("Сессия успешно удалена! отправляю сокет выход пользователя удаленной сессии ...", $oldSession);
            $channel = 'user:' . $user->id;
            $this->redisPublisher->publish('auth-hub', [
                'channel' => $channel,
                'event' => 'exit',
                'data' => ['token' => $oldSession['token']]
            ]);
        }
        AccessToken::create([
                'external_id' => $tokenInfo['id'],
                'user_id' => $user->id,
                'name' => $tokenInfo['name'],
                'token' => $accessToken,
                'ip_address' => $tokenInfo['ip_address'],
                'user_agent' => $tokenInfo['user_agent'],
                'device_type' => $tokenInfo['device_type'],
                'browser' => $tokenInfo['browser'],
                'platform' => $tokenInfo['platform'],
                'last_used_at' => now(),
                'expires_at' => null
            ]);
        Log::info("Токен успешно добавлен");
        return $accessToken;
    }


    // параметры для токена, который пришел без доп информации
    private function nullTokenInfo() {
        return [
                    'id' => null,
                    'name' => null,
                    'ip_address' => null,
                    'user_agent' => null,
                    'device_type' => null,
                    'browser' => null,
                    'platform' => null,
                    'last_used_at' => null
                ];
    }


    public function getSessions($user) {
        $tokens = AccessToken::where('user_id', $user->id)->get();
        $sessions = [];
        foreach ($tokens as $token) {
            $session = [
                'id' =>$token->id,
                'ip_address' => $token->ip_address,
                'user_agent' => $token->user_agent,
                'device_type' => $token->device_type,
                'browser' => $token->browser,
                'platform' => $token->platform,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at
            ];
            $sessions[] = $session;
        }
        return $sessions;
    }


    public function deleteSessions($user, $sessionIds) {
        foreach  ($sessionIds as $sessionId) {
            $token = AccessToken::where('id', $sessionId)->where('user_id', $user->id)->first();
            if (!$token) continue;
            $token->delete();
            $this->redisPublisher->publish('auth-hub', [
                'channel' => 'user:'.$user->id,
                'event' => 'exit',
                'data' => ['token' => $token->token]
            ]);
        }
        return ["success" => true];
    }
}