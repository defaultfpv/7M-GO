<?php

namespace App\Services\Max;

use App\Services\Users\UserService;
use Illuminate\Support\Facades\Log;
use App\Services\Auth\MessengerAuthService;

class MaxBotSender
{
    private $token;
    private $apiUrl = 'https://platform-api.max.ru';
    
    public function __construct($token = null)
    {
        $this->token = $token ?: env('MAX_BOT_TOKEN');
    }
    
    /**
     * Отправка сообщения пользователю
     */
    public function sendMessage($chatId, $text, $attach = [])
    {
        $postData = ['text' => $text, 'format' => 'html'];
        
        // Добавляем кнопки, если они есть
        if (!empty($attach)) {
            $postData['attachments'] = $attach;
        }
        
        return $this->makeRequest('POST', "/messages?chat_id=" . urlencode($chatId), $postData);
    }

    
    /**
     * Выполнение запроса к API Max
     */
    private function makeRequest($method, $endpoint, $data = null)
    {
        $ch = curl_init();
        
        $url = $this->apiUrl . $endpoint;
        $jsonData = $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token,
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'MaxBot/1.0'
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($jsonData) {
                $options[CURLOPT_POSTFIELDS] = $jsonData;
                $options[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen($jsonData);
            }
        } elseif ($method !== 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            if ($jsonData) {
                $options[CURLOPT_POSTFIELDS] = $jsonData;
            }
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data' => json_decode($response, true),
            'raw_response' => $response
        ];
    }
}

