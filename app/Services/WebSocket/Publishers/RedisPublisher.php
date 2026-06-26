<?php

namespace App\Services\WebSocket\Publishers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class RedisPublisher
{
    /**
     * Опубликовать событие в Redis
     */
    public function publish(string $channel, array $data): void
    {
        try {
            $message = json_encode([
                'event' => $data['event'] ?? null,
                'data' => $data['data'] ?? [],
                'channel' => $data['channel'] ?? null,
                'session_id' => $data['session_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'timestamp' => time(),
                'message_id' => uniqid('msg_', true)
            ]);
            
            Redis::publish($channel, $message);  // вот тут
            
            Log::debug('Redis message published', [
                'channel' => $channel,
                'event' => $data['event'] ?? null,
                'channel_name' => $data['channel'] ?? null,
                'session_id' => $data['session_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Redis publish failed', [
                'error' => $e->getMessage(),
                'channel' => $channel
            ]);
        }
    }
    
    /**
     * Отправить конкретному клиенту по session_id
     */
    public function toSession(string $sessionId, string $event, array $data = []): void
    {
        $this->publish('auth-hub', [
            'event' => $event,
            'data' => $data,
            'session_id' => $sessionId,
            'type' => 'session'
            // channel не нужен здесь
        ]);
    }
    
    /**
     * Отправить конкретному пользователю (на все его устройства)
     */
    public function toUser(int $userId, string $event, array $data = []): void
    {
        $this->publish('auth-hub', [
            'event' => $event,
            'data' => $data,
            'user_id' => $userId,
            'type' => 'user'
            // channel не нужен здесь
        ]);
    }
    
    /**
     * Отправить всем (broadcast)
     */
    public function broadcast(string $event, array $data = [], array $except = []): void
    {
        $this->publish('auth-hub', [
            'event' => $event,
            'data' => $data,
            'type' => 'broadcast',
            'except' => $except
            // channel не нужен здесь
        ]);
    }
}