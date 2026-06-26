<?php

namespace App\Services\Max;

use App\Services\Users\UserService;
use Illuminate\Support\Facades\Log;
use App\Services\Auth\MessengerAuthService;


class MaxBotRouteService
{
    protected $maxBotService;
    protected $maxBotConfiguration;
    protected $userService;
    protected $messengerAuthService;

    public function __construct(
        MaxBotService $maxBotService,
        UserService $userService,
        MaxBotConfiguration $maxBotConfiguration,
        MessengerAuthService $messengerAuthService
    ) {
        $this->maxBotService = $maxBotService;
        $this->maxBotConfiguration = $maxBotConfiguration;
        $this->userService = $userService;
        $this->messengerAuthService = $messengerAuthService;
    }

    
    // Маршрутизатор для обработки Max сообщений
    public function router(array $update)
    {
        $updateType = $update['update_type'] ?? null;

        switch ($updateType) {
            case 'bot_started':
                return $this->handleBotStarted($update);
                break;
                
            case 'message_created':
                return $this->handleMessage($update);
                break;
                
            default:
                Log::info('Unknown update type', ['type' => $updateType]);
        }
    }

    
    // Обработка события bot_started (пользователь запустил бота)
    protected function handleBotStarted(array $update)
    {
        $userId = $update['user_id'] ?? null;
        $userData = $update['user'] ?? [];
        $chatId = $update['chat_id'] ?? null;
        $payload = $update['payload'] ?? null;

        if (!$userId || !$chatId) {
            Log::error('Invalid bot_started data', ['update' => $update]);
            return;
        }

        Log::info('Max bot started', [
            'user_id' => $userId,
            'chat_id' => $chatId,
            'payload' => $payload
        ]);

        // Если есть payload - это авторизация
        if ($payload) {
            $auth = $this->messengerAuthService->maxAuth($payload, $update);
            if ($auth) return $this->maxBotService->sendAuthSuccessMessage($chatId, $auth['project_title']);
            else return $this->maxBotService->sendAuthErrorMessage($chatId);
        } else {
            // Просто запуск бота без payload
            $this->maxBotService->sendWelcomeMessage($chatId);
        }
    }

    
    // Обработка обычного сообщения
    protected function handleMessage(array $update)
    {
        $text = $update['message']['body']['text'];
        $chatId = $update['message']['recipient']['chat_id'];
        $userId = $update['message']['sender']['user_id'];
        switch ($text) {
            case '/start':
                return $this->maxBotService->sendWelcomeMessage($chatId);
            case '/this_id':
                return $this->maxBotService->sendUserInfoMessage($chatId, $userId);
            case '/test_image':
                return $this->maxBotService->sendImagesMessage($chatId);
            case '/test_sticker':
                return $this->maxBotService->sendStickerMessage($chatId);
            case '/test_contact':
                return $this->maxBotService->sendContactMessage($chatId, $userId);
            case '/test_location':
                return $this->maxBotService->sendlocationMessage($chatId);
            case '/test_button':
                return $this->maxBotService->sendMessageWithCallbackButton($chatId);

            case '/test_video':
                // return $this->maxBotService->sendlocationMessage($chatId);
            case '/test_audio':
                // return $this->maxBotService->sendlocationMessage($chatId);
            case '/test_fail':
                // return $this->maxBotService->sendlocationMessage($chatId);
            case '/test_share':
                // return $this->maxBotService->sendlocationMessage($chatId);
            default:
                return $this->callbackMode($text, $chatId, $userId);
        }
    }


    // обработка ожидающего сообщения
    protected function callbackMode($text, $chatId, $userId) {
        return true;
    }
}