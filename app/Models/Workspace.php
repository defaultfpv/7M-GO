<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $table = 'workspaces';

    protected $fillable = [
        'account_id',
        'title',
        'description',
        'status',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_workspaces', 'workspace_id', 'user_id')->withTimestamps();
    }

}