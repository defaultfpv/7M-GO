<?php

namespace App\Services\WebSocket\Connections;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ConnectionManager
{
    private const SESSION_KEY = 'ws:session:';
    private const USER_KEY = 'ws:user:';
    private const SOCKET_KEY = 'ws:socket:';
    private const TTL = 86400; // 24 часа
    
    /**
     * Зарегистрировать новое подключение
     */
    public function register(string $sessionId, string $socketId, string $serverId = 'default'): void
    {
        $data = [
            'socket_id' => $socketId,
            'server_id' => $serverId,
            'connected_at' => time(),
            'last_seen' => time()
        ];
        
        // session_id -> данные сокета
        Redis::setex(self::SESSION_KEY . $sessionId, self::TTL, json_encode($data));
        
        // socket_id -> session_id (обратная связка)
        Redis::setex(self::SOCKET_KEY . $socketId, self::TTL, $sessionId);
        
        Log::info('Socket registered', [
            'session_id' => $sessionId,
            'socket_id' => $socketId,
            'server_id' => $serverId
        ]);
    }
    
    /**
     * Привязать пользователя к сессии (после авторизации)
     */
    public function attachUser(string $sessionId, int $userId): void
    {
        // Обновляем данные сессии
        $sessionData = $this->getSession($sessionId);
        if ($sessionData) {
            $sessionData['user_id'] = $userId;
            Redis::setex(self::SESSION_KEY . $sessionId, self::TTL, json_encode($sessionData));
        }
        
        // Добавляем в список сессий пользователя
        $userSessions = $this->getUserSessions($userId);
        if (!in_array($sessionId, $userSessions)) {
            $userSessions[] = $sessionId;
            Redis::setex(self::USER_KEY . $userId, self::TTL, json_encode($userSessions));
        }
        
        Log::info('User attached to session', [
            'session_id' => $sessionId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Получить данные сессии
     */
    public function getSession(string $sessionId): ?array
    {
        $data = Redis::get(self::SESSION_KEY . $sessionId);
        return $data ? json_decode($data, true) : null;
    }
    
    /**
     * Получить все сессии пользователя
     */
    public function getUserSessions(int $userId): array
    {
        $data = Redis::get(self::USER_KEY . $userId);
        return $data ? json_decode($data, true) : [];
    }
    
    /**
     * Найти сервер для session_id
     */
    public function findServer(string $sessionId): ?string
    {
        $session = $this->getSession($sessionId);
        return $session['server_id'] ?? null;
    }
    
    /**
     * Найти socket_id для session_id
     */
    public function findSocket(string $sessionId): ?string
    {
        $session = $this->getSession($sessionId);
        return $session['socket_id'] ?? null;
    }
    
    /**
     * Отключение (удаляем все связки)
     */
    public function disconnect(string $sessionId, ?int $userId = null): void
    {
        // Получаем данные перед удалением
        $session = $this->getSession($sessionId);
        
        if ($session && isset($session['socket_id'])) {
            Redis::del(self::SOCKET_KEY . $session['socket_id']);
        }
        
        Redis::del(self::SESSION_KEY . $sessionId);
        
        // Если был привязан пользователь - обновляем его список
        if ($userId || ($session && isset($session['user_id']))) {
            $uid = $userId ?? $session['user_id'];
            $this->removeUserSession($uid, $sessionId);
        }
        
        Log::info('Socket disconnected', ['session_id' => $sessionId]);
    }
    
    /**
     * Удалить сессию из списка пользователя
     */
    private function removeUserSession(int $userId, string $sessionId): void
    {
        $sessions = $this->getUserSessions($userId);
        $sessions = array_filter($sessions, fn($s) => $s !== $sessionId);
        
        if (empty($sessions)) {
            Redis::del(self::USER_KEY . $userId);
        } else {
            Redis::setex(self::USER_KEY . $userId, self::TTL, json_encode(array_values($sessions)));
        }
    }
    
    /**
     * Обновить время последней активности
     */
    public function updateLastSeen(string $sessionId): void
    {
        $session = $this->getSession($sessionId);
        if ($session) {
            $session['last_seen'] = time();
            Redis::setex(self::SESSION_KEY . $sessionId, self::TTL, json_encode($session));
        }
    }
}