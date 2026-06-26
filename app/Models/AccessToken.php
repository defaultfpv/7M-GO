<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AccessToken extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'personal_access_tokens';

    // Разрешённые для массового заполнения поля
    protected $fillable = [
        'external_id',
        'user_id',
        'name',
        'token',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'last_used_at',
        'expires_at'
    ];


    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

