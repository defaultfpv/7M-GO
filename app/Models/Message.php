<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'deal_id',
        'author_id',
        'text'
    ];


    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

}