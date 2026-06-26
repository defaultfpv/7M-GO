<?php

namespace App\Services\Max;

class MaxBotAttachment
{
    
    /**
     * Создание кнопки с callback данными
     */
    public function createButtons($buttons)
    {
        return [
            'type' => 'inline_keyboard',
            'payload' => ['buttons' => $buttons]
        ];
    }


    /**
     * Создание локации
     */
    public function createLocation($latitude, $longitude)
    {
        return [
            'type' => 'location',
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
    }


    /**
     * Создание изображения
     */
    public function createImage($url)
    {
        return [
            'type' => 'image',
            'payload' => ['url' => $url]
        ];
    }


    /**
     * Создание стикера
     */
    public function createSticker($code)
    {
        return [
            'type' => 'sticker',
            'payload' => ['code' => $code]
        ];
    }


    /**
     * Создание контакта
     */
    public function createContact($user_id)
    {
        return [
            'type' => 'contact',
            'payload' => ['contact_id' => $user_id]
        ];
    }

    
}

