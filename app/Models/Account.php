<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'user_id',
        'balance',
        'bonus_balance',
        'day_rate_price',
        'status'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

}