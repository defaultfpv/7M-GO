<?php

namespace App\Services\Telegram;

use App\Models\User;
use App\Models\AuthorizationData;
use App\Services\Users\UserService;

class TelegramBotRouteService
{
    protected $telegramBotService;
    protected $telegramBotConfiguration;
    protected $userService;

    public function __construct(TelegramBotService $telegramBotService, UserService $userService, TelegramBotConfiguration $telegramBotConfiguration)
    {
        $this->telegramBotService = $telegramBotService;
        $this->telegramBotConfiguration = $telegramBotConfiguration;
        $this->userService = $userService;
    }



    // маршутизатор для обработки телеграм сообщений
    public function router($update)
    {
        file_put_contents(storage_path('logs/telegram_info.txt'), print_r($update->all(), true));
        $updateData = $update->toArray();

        // Обработка нажатия на кнопку
        if (!empty($updateData['callback_query'])) {
            $result = $this->callbackDataRouter($updateData['callback_query']);
            return response()->json(['status' => 'callback', 'result' => $result]);
        }
        // Обработка сообщений
        $result = $this->messageRouter($updateData);
        return response()->json($result);
    }



    // роутинг для обработки команд и кнопок клавиатуры
    protected function messageRouter($updateData)
    {
        $message = $updateData['message'] ?? null;
        if (!$message) {
            return 'no_message';
        }

        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        // Ищем в authorization_data по telegram_id
        $authData = AuthorizationData::where('telegram_id', $chatId)->first();
        $user = $authData ? User::find($authData->user_id) : null;
        
        if ($user && array_key_exists('username', $message['from'])) {
            $username = '@' . $message['from']['username'];
            $user->username = $username;
            $user->save();
        }

        // Обработка /start команды (с payload или без)
        if (str_starts_with($text, '/start')) {
            $payload = null;
            if (str_contains($text, ' ')) {
                $parts = explode(' ', $text, 2);
                $payload = $parts[1] ?? null;
            }
            return $this->telegramBotService->start($message, $chatId, $payload);
        }
        // if ($text === '/support') return $this->telegramBotService->support($chatId);
        // if ($text === '/terms') return $this->telegramBotService->terms($chatId);
        // if ($text === 'Поиск') return $this->telegramBotService->search($chatId);
        // if ($text === 'Пользователи') return $this->telegramBotService->buyers($chatId);

        return $this->callbackModeRouter($chatId, $text);
    }



    // роутинг для обработки колбэк команд
    protected function callbackDataRouter($callbackQuery)
    {
        // TODO: реализовать позже
        return null;
    }


    // роутер для обработки обычных сообщений
    public function callbackModeRouter($chat_id, $text = null)
    {
        // Ищем пользователя через authorization_data
        $authData = AuthorizationData::where('telegram_id', $chat_id)->first();
        $user = $authData ? User::find($authData->user_id) : null;
        
        if (!$user) {
            return false;
        }

        // TODO: реализовать логику callbackMode
        return true;
    }

}