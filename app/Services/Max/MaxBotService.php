<?php

namespace App\Services\Max;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\Max\MaxBotAttachment;
use App\Services\Max\MaxBotSender;
use App\Services\Max\MaxBotButton;

class MaxBotService
{

    protected $botToken;
    protected $button;
    protected $attach;
    protected $sender;
    
    public function __construct( MaxBotButton $button, MaxBotAttachment $attach, MaxBotSender $sender)
    {
        $this->botToken = env('MAX_BOT_TOKEN');
        $this->button = $button;
        $this->attach = $attach;
        $this->sender = $sender;
    }
    
    
    public function sendAuthSuccessMessage($chatId, $project_title) {
        $text = "<b>$project_title</b>\n\n✅ Авторизация прошла <b>успешно!</b>\n\nВернитесь в браузер"; 
        return $this->sender->sendMessage($chatId, $text);
    }


    public function sendAuthErrorMessage($chatId) {
        $text = "⚠️ <b>Ошибка авторизации</b>\n\nНеверные данные входа или истекло время ссылки.\nПопробуйте получить новую ссылку";
        return $this->sender->sendMessage($chatId, $text);
    }
    

    public function sendWelcomeMessage($chatId) {
        $text = "Добро пожаловать в <b>7Bears!</b>\nДля ознакомления с нашими продуктами перейдите по ссылке";
        $buttons = [];
        $buttons[0][] = $this->button->createLinkButton('Наши продукты', 'https://7bears.ru/gpro/');
        $buttons[1][] = $this->button->createLinkButton('Партнерская программа', 'https://7bears.ru/1c/');
        $attach = [];
        $attach[] = $this->attach->createButtons($buttons);
        return $this->sender->sendMessage($chatId, $text, $attach);
    }


    public function sendUserInfoMessage($chatId, $userId) {
        $isGroup = str_starts_with((string)$chatId, '-');
        $text = "<b>Ваш чат-id</b> 👇";
        $this->sender->SendMessage($chatId, $text);
        if ($isGroup) $text = "<i>$chatId</i>";
        else $text = "<i>$userId</i>";
        return $this->sender->SendMessage($chatId, $text);
    }


    public function sendlocationMessage($chatId) {
        $text = 'Вот тестовая локация';
        $latitude = '53.259224';
        $longitude = '34.416731';
        $attach = [];
        $attach[] = $this->attach->createLocation($latitude, $longitude);
        return $this->sender->SendMessage($chatId, $text, $attach);
    }


    public function sendimagesMessage($chatId) {
        $text = 'Вот тестовое изображение';
        $url1 = 'https://7bears.ru/ui/header/logo.webp';
        $url2 = 'https://ir.ozone.ru/s3/multimedia-1-8/wc1000/9101827916.jpg';
        $attach = [];
        $attach[] = $this->attach->createImage($url1);
        $attach[] = $this->attach->createImage($url2);
        return $this->sender->SendMessage($chatId, $text, $attach);
    }


    public function sendStickerMessage($chatId) {
        $text = '';
        // $code = '15bee6dbb';
        $code = '15bf6ffbb';
        $attach = [];
        $attach[] = $this->attach->createSticker($code);;
        return $this->sender->SendMessage($chatId, $text, $attach);
    }


    public function sendContactMessage($chatId, $userId) {
        $text = 'Привет';
        $attach = [];
        $attach[] = $this->attach->createContact($userId);
        return $this->sender->SendMessage($chatId, $text, $attach);
    }


    public function sendMessageWithCallbackButton($chatId) {
        $text = 'Сообщение с кнопкой';
        $buttons = [];
        $attach = [];
        // $buttons[0][] = $this->button->createCallbackButton('Тестовая кнопка', 'test_callback_data');
        // $buttons[1][] = $this->button->createLinkButton('Наш сайт', 'https://7bears.ru');
        // $buttons[2][] = $this->button->createMessageButton('Нажмите чтобы отправить');
        // $buttons[3][] = $this->button->createRequestGeoButton('Поделиться местоположением');
        $buttons[0][] = $this->button->createRequestContactButton('Поделиться контактом');
        $attach[] = $this->attach->createButtons($buttons);
        return $this->sender->sendMessage($chatId, $text, $attach);
    }
}