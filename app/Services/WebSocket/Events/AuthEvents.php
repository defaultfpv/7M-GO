<?php

namespace App\Services\WebSocket\Events;

class AuthEvents
{
    // События авторизации
    public const AUTH_SUCCESS = 'auth:success';
    public const AUTH_FAILED = 'auth:failed';
    public const TOKEN_REFRESH = 'token:refresh';
    
    // События пользователя
    public const USER_ONLINE = 'user:online';
    public const USER_OFFLINE = 'user:offline';
    public const USER_STATUS = 'user:status';
    
    // Системные
    public const PING = 'ping';
    public const PONG = 'pong';
    public const ERROR = 'error';
    
    // Каналы Redis
    public const REDIS_CHANNEL = 'websocket';
}