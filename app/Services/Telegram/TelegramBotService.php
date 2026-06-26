<?php

namespace App\Services\Telegram;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Services\Auth\MessengerAuthService;


class TelegramBotService
{
    protected $messengerAuthService;

    public function __construct(MessengerAuthService $messengerAuthService)
    {
        $this->messengerAuthService = $messengerAuthService;

    }

    public function start($message, $chatId, $payload = null)
    {
        // Логируем для отладки
        Log::info('Telegram /start command', [
            'chat_id' => $chatId,
            'payload' => $payload,
            'from' => $message['from'] ?? null
        ]);

        // Если есть payload - это запрос на авторизацию
        if ($payload) {
            $auth = $this->messengerAuthService->telegramAuth($payload, $message['from']); // здесь мы можем сразу использовать MessengerAuthService
            if ($auth) return $this->sendAuthSuccessMessage($chatId, $auth['project_title']);
            return $this->sendAuthErrorMessage($chatId);
        }

        // Если нет payload - просто приветствие
        return $this->sendWelcomeMessage($chatId);
    }

   
    /**
     * Отправка приветственного сообщения
     */
    protected function sendWelcomeMessage($chatId)
    {
        return Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "здарова",
            'parse_mode' => 'HTML'
        ]);
    }

    /**
     * Отправка сообщения об успехе
     */
    protected function sendAuthSuccessMessage($chatId, $project_title)
    {
        return Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "<b>$project_title</b>\n\n✅ Авторизация прошла <b>успешно!</b>\n\nВернитесь в браузер",
            'parse_mode' => 'HTML'
        ]);
    }

    /**
     * Отправка сообщения об ошибке
     */
    protected function sendAuthErrorMessage($chatId)
    {
        $entities = [
            [
                'offset' => 0,
                'length' => 1,         
                'type' => 'custom_emoji',
                'custom_emoji_id' => '5447644880824181073'
            ],
            [
                'offset' => 2,
                'length' => 6,
                'type' => 'bold'
            ]
        ];
        return Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "⚠️ <b>Ошибка авторизации</b>\n\nНеверные данные входа или истекло время ссылки.\nПопробуйте получить новую ссылку",
            'entities' => json_encode($entities)
        ]);
    }



}