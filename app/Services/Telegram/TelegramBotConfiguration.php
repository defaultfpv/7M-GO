<?php

namespace App\Services\Telegram;

use Telegram\Bot\Keyboard\Keyboard;

class TelegramBotConfiguration
{

    // Клавиатура администратора
    public function root_keyboard() {
        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->row([Keyboard::button('Поиск')])
            ->row([Keyboard::button('Пользователи')])
            ->row([Keyboard::button('Партнеры')])
            ->row([Keyboard::button('Проекты')]);
        return $keyboard;
    }
    

    // проверить является ли администратором текущий чат-id
    public function check_admin_status($chat_id)
    {
        $rootChatIds = env('TELEGRAM_ROOT_CHAT_ID', '');
        $rootChatIds = array_filter(array_map('trim', explode(',', $rootChatIds)));
        foreach ($rootChatIds as $rootChatId) {
            if ($rootChatId == $chat_id) return true;
        }
    }
}