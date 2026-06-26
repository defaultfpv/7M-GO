<?php

namespace App\Services\WebSocket;

use App\Services\WebSocket\Publishers\RedisPublisher;
use App\Services\WebSocket\Connections\ConnectionManager;
use App\Services\WebSocket\Connections\UserConnections;
use App\Services\WebSocket\Events\AuthEvents;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    public function __construct(
        private readonly RedisPublisher $publisher,
        private readonly ConnectionManager $connections,
        private readonly UserConnections $userConnections
    ) {}
    
    /**
     * Отправить событие конкретной сессии (по session_id)
     */
    public function toSession(string $sessionId, string $event, array $data = []): bool
    {
        try {
            // Проверяем существует ли такая сессия
            $server = $this->connections->findServer($sessionId);
            
            if (!$server) {
                Log::warning('Session not found for websocket', ['session_id' => $sessionId]);
                return false;
            }
            
            $this->publisher->toSession($sessionId, $event, $data);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send to session', [
                'session_id' => $sessionId,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Отправить событие пользователю (на все его сессии)
     */
    public function toUser(int $userId, string $event, array $data = []): bool
    {
        try {
            $sessions = $this->connections->getUserSessions($userId);
            
            if (empty($sessions)) {
                Log::debug('No active sessions for user', ['user_id' => $userId]);
                return false;
            }
            
            $this->publisher->toUser($userId, $event, [
                'data' => $data,
                'sessions' => $sessions
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send to user', [
                'user_id' => $userId,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Отправить событие всем (broadcast)
     */
    public function broadcast(string $event, array $data = [], array $except = []): void
    {
        $this->publisher->broadcast($event, $data, $except);
    }
    
    /**
     * Отметить пользователя онлайн (после авторизации)
     */
    public function userOnline(int $userId, string $sessionId, array $metadata = []): void
    {
        // Привязываем пользователя к сессии
        $this->connections->attachUser($sessionId, $userId);
        
        // Отмечаем онлайн
        $this->userConnections->setOnline($userId, array_merge($metadata, [
            'last_login' => time(),
            'session_id' => $sessionId
        ]));
        
        // Оповещаем всех
        $this->broadcast(AuthEvents::USER_ONLINE, [
            'user_id' => $userId,
            'timestamp' => time()
        ], except: [$sessionId]); // кроме этой сессии
        
        Log::info('User online', ['user_id' => $userId, 'session_id' => $sessionId]);
    }
    
    /**
     * Отметить пользователя офлайн
     */
    public function userOffline(int $userId, string $sessionId): void
    {
        // Проверяем остались ли еще сессии у пользователя
        $sessions = $this->connections->getUserSessions($userId);
        
        // Убираем текущую сессию (она уже отключается)
        $sessions = array_filter($sessions, fn($s) => $s !== $sessionId);
        
        if (empty($sessions)) {
            // Больше нет активных сессий - пользователь офлайн
            $this->userConnections->setOffline($userId);
            
            $this->broadcast(AuthEvents::USER_OFFLINE, [
                'user_id' => $userId,
                'timestamp' => time()
            ]);
            
            Log::info('User offline', ['user_id' => $userId]);
        }
    }
    
    /**
     * Отправить событие авторизации (успех)
     */
    public function authSuccess(string $sessionId, string $token, array $user, int $projectId): bool
    {
        $result = $this->toSession($sessionId, AuthEvents::AUTH_SUCCESS, [
            'token' => $token
        ]);
        
        if ($result && isset($user['id'])) {
            // Отмечаем пользователя онлайн
            $this->userOnline($user['id'], $sessionId, [
                'project_id' => $projectId
            ]);
        }
        
        return $result;
    }
    
    /**
     * Отправить событие ошибки авторизации
     */
    public function authFailed(string $sessionId, string $reason): bool
    {
        return $this->toSession($sessionId, AuthEvents::AUTH_FAILED, [
            'reason' => $reason,
            'timestamp' => time()
        ]);
    }
}