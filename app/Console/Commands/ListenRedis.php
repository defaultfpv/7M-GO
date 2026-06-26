<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\WebSocket\Connections\ConnectionManager;

class ListenRedis extends Command
{
    protected $signature = 'redis:listen';
    protected $description = 'Listen to Redis events and update connections';

    public function __construct(
        private readonly ConnectionManager $connectionManager
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('📡 Listening to Redis channel: auth-hub');
        
        Redis::connection()->subscribe(['auth-hub'], function ($message) {
            $this->info('📥 Received: ' . $message);
            
            try {
                $data = json_decode($message, true);
                
                // Обрабатываем регистрацию сокета
                if ($data['type'] === 'system' && $data['event'] === 'socket:register') {
                    $this->connectionManager->register(
                        sessionId: $data['data']['session_id'],
                        socketId: $data['data']['socket_id'],
                        serverId: $data['data']['server_id'] ?? 'default'
                    );
                    $this->info('✅ Registered session: ' . $data['data']['session_id']);
                }
                
                // Здесь можно добавить обработку других типов сообщений
                
            } catch (\Exception $e) {
                $this->error('❌ Error: ' . $e->getMessage());
            }
        });
    }
}