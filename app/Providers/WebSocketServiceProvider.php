<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WebSocket\WebSocketService;
use App\Services\WebSocket\Publishers\RedisPublisher;
use App\Services\WebSocket\Connections\ConnectionManager;
use App\Services\WebSocket\Connections\UserConnections;

class WebSocketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConnectionManager::class);
        $this->app->singleton(UserConnections::class);
        $this->app->singleton(RedisPublisher::class);
        
        $this->app->singleton(WebSocketService::class, function ($app) {
            return new WebSocketService(
                $app->make(RedisPublisher::class),
                $app->make(ConnectionManager::class),
                $app->make(UserConnections::class)
            );
        });
    }
    
    public function boot(): void
    {
        //
    }
}