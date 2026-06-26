<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{ 
    protected $table = 'deals';

    protected $fillable = [
        'worker_id',
        'phone',
        'name',
        'description',
        'coords',
        'address',
        'comment',
        'price',
        'start_at',
        'end_at',
        'status',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function report()
    {
        return $this->hasOne(Report::class);
    }


}
