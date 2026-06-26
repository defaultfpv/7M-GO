<?php

namespace App\Http\Controllers\Api\V1\WebSocket;

use App\Http\Controllers\Controller;
use App\Services\WebSocket\Connections\ConnectionManager;
use App\Services\WebSocket\Publishers\RedisPublisher;
use Illuminate\Http\Request;

class WebSocketController extends Controller
{
    public function __construct(
        private readonly ConnectionManager $connectionManager,
        private readonly RedisPublisher $redisPublisher  // Добавляем
    ) {}

    // ===== ТЕСТОВЫЙ МЕТОД =====
    public function test(Request $request) {
        $request->validate([
            'channel' => 'required|string',
            'event' => 'required|string',
            'data' => 'required|array'
        ]);

        // Публикуем в Redis
        $this->redisPublisher->publish('auth-hub', [
            'channel' => $request->channel,
            'event' => $request->event,
            'data' => $request->data
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event published to Redis',
            'data' => [
                'channel' => $request->channel,
                'event' => $request->event,
                'payload' => $request->data
            ]
        ]);
    }


    // проверка валидности токена для сокетсервера
    public function verifyToken(Request $request) {
        $user = $request->user();
        return response()->json(['valid' => true], 201);
    }


}