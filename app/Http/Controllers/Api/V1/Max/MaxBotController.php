<?php

namespace App\Http\Controllers\Api\V1\Max;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Max\MaxBotRouteService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MaxBotController extends Controller
{
    protected $maxBotRouteService;
    protected $botToken;

    public function __construct(MaxBotRouteService $maxBotRouteService)
    {
        $this->maxBotRouteService = $maxBotRouteService;
        $this->botToken = env('MAX_BOT_TOKEN');
    }

    public function handleUpdates(Request $request)
    {
        try {
            $method = env('MAX_UPDATE_METHOD', 'webhook');

            if ($method === 'getupdates') $update = $this->handleGetUpdates();
            else $update = $request->all();

            file_put_contents(storage_path('logs/max_info.txt'), print_r($request->all(), true));
            
            $processing = $this->maxBotRouteService->router($update);
            return response()->json($processing, 200);
           
        } catch (\Exception $e) {
            Log::error('Max webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }


    
    // Обработка режима getUpdates (сами дёргаем API Max)
    protected function handleGetUpdates()
    {
        try {
            $ch = curl_init();
    
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://platform-api.max.ru/updates',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $this->botToken,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'PostmanRuntime/7.49.1' // для совместимости
            ]);
    
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
    
            // Декодируем JSON ответ в массив
            $data = json_decode($response, true);
    
            // Проверяем, что ответ содержит ожидаемую структуру
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data['updates'][0];
            } else {
                return [
                    'success' => false,
                    'error' => 'Invalid JSON response',
                    'raw_response' => $response,
                    'http_code' => $httpCode
                ];
            }

        } catch (\Exception $e) {
            Log::error('Max getUpdates exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error'], 500);
        }
    }

}