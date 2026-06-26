<?php

namespace App\Services\WebSocket\Connections;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class UserConnections
{
    private const USER_STATUS_KEY = 'ws:status:';
    private const USER_METADATA_KEY = 'ws:meta:';
    private const TTL = 86400; // 24 часа
    
    /**
     * Отметить пользователя онлайн
     */
    public function setOnline(int $userId, array $metadata = []): void
    {
        Redis::setex(self::USER_STATUS_KEY . $userId, self::TTL, 'online');
        
        if (!empty($metadata)) {
            Redis::setex(self::USER_METADATA_KEY . $userId, self::TTL, json_encode([
                'last_seen' => time(),
                ...$metadata
            ]));
        }
        
        Log::debug('User online', ['user_id' => $userId]);
    }
    
    /**
     * Отметить пользователя офлайн
     */
    public function setOffline(int $userId): void
    {
        Redis::del(self::USER_STATUS_KEY . $userId);
        // Метаданные не удаляем, они еще пригодятся
        
        Log::debug('User offline', ['user_id' => $userId]);
    }
    
    /**
     * Проверить онлайн ли пользователь
     */
    public function isOnline(int $userId): bool
    {
        return Redis::get(self::USER_STATUS_KEY . $userId) === 'online';
    }
    
    /**
     * Получить список онлайн пользователей
     */
    public function getOnlineUsers(array $userIds = []): array
    {
        if (empty($userIds)) {
            // Получаем все ключи с онлайн статусами (осторожно на продакшне!)
            $keys = Redis::keys(self::USER_STATUS_KEY . '*');
            $userIds = array_map(fn($key) => str_replace(self::USER_STATUS_KEY, '', $key), $keys);
        }
        
        $online = [];
        foreach ($userIds as $userId) {
            if ($this->isOnline($userId)) {
                $online[] = $userId;
            }
        }
        
        return $online;
    }
    
    /**
     * Получить метаданные пользователя
     */
    public function getMetadata(int $userId): ?array
    {
        $data = Redis::get(self::USER_METADATA_KEY . $userId);
        return $data ? json_decode($data, true) : null;
    }
    
    /**
     * Обновить метаданные
     */
    public function updateMetadata(int $userId, array $metadata): void
    {
        $existing = $this->getMetadata($userId) ?? [];
        $merged = array_merge($existing, $metadata);
        
        Redis::setex(self::USER_METADATA_KEY . $userId, self::TTL, json_encode($merged));
    }
}