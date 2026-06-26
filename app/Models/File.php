<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    protected $table = 'files';

    protected $fillable = [
        'type',
        'path'
    ];


    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
