<?php

namespace App\Services\Max;

class MaxBotButton
{
    /**
     * Создание кнопки с callback данными
     */
    public function createCallbackButton($text, $callbackData)
    {
        return [
            'type' => 'callback',
            'text' => $text,
            'payload' => $callbackData
        ];
    }


    /**
     * Создание кнопки с ссылкой
     */
    public function createLinkButton($text, $url)
    {
        return [
            'type' => 'link',
            'text' => $text,
            'url' => $url
        ];
    }


    /**
     * Создание кнопки запроса ГЕО
     */
    public function createRequestGeoButton($text, $quick = false)
    {
        return [
            'type' => 'request_geo_location',
            'text' => $text,
            'quick' => $quick
        ];
    }


    /**
     * Создание кнопки запроса контакта
     */
    public function createRequestContactButton($text)
    {
        return [
            'type' => 'request_contact',
            'text' => $text
        ];
    }


    /**
     * Создание кнопки отправки смс
     */
    public function createMessageButton($text)
    {
        return [
            'type' => 'message',
            'text' => $text
        ];
    }


    /**
     * Создание кнопки открытия приложения
     */
    public function createOpenAppButton($text, $app_url, $payload = '')
    {
        return [
            'type' => 'open_app',
            'text' => $text,
            'web_app' => $app_url,
            'payload' => $payload
        ];
    }
    
}