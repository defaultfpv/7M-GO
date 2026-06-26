<?php

namespace App\Http\Controllers\Api\V1\Telegram;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\Telegram\TelegramBotRouteService;

class TelegramBotController extends Controller
{
    protected $telegramBotRouteService;

    public function __construct(TelegramBotRouteService $telegramBotRouteService)
    {
        $this->telegramBotRouteService = $telegramBotRouteService;
    }

    public function handleUpdates(Request $request)
    {
        $method = env('TELEGRAM_UPDATE_METHOD', 'webhook');
        
        $update = null;
        if ($method === 'getupdates') {
            $updates = Telegram::getUpdates();  // эту
            if (!empty($updates)) $update = end($updates);
        } else $update = Telegram::getWebhookUpdates(); // и эту
        
        if (!$update) return response()->json(['status' => 'no_update'], 200);
        
        return $this->telegramBotRouteService->router($update);
    }

}