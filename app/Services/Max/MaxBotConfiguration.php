<?php

namespace App\Services\Max;

use App\Models\User;
use App\Models\AuthorizationData;
use Illuminate\Support\Facades\Log;

class MaxBotConfiguration
{
    /**
     * Проверка является ли пользователь администратором
     */
    public function checkAdminStatus(int $maxId): bool
    {
        $authData = AuthorizationData::where('max_id', $maxId)->first();
        if (!$authData) return false;

        $user = User::find($authData->user_id);
        return $user && $user->role === 'admin';
    }

    /**
     * Получение списка root чатов (для рассылок)
     */
    public function getRootChats(): array
    {
        $rootChats = env('MAX_ROOT_CHAT_ID', '');
        return array_map('intval', explode(',', $rootChats));
    }
}