<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// class User
class User extends Model
{

    protected $table = 'users';

    // Разрешённые для массового заполнения поля
    protected $fillable = [
        'external_id',
        'phone',
        'first_name',
        'last_name',
        'description',
        'role',
        'status',
        'color',
        'email',
        'messanger',
        'chat_id'
    ];


    public function tokens()
    {
        return $this->hasMany(AccessToken::class);
    }

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'worker_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'author_id');
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'users_workspaces', 'user_id', 'workspace_id')->withTimestamps();
    }
}

