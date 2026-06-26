<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';

    protected $fillable = [
        'type',
        'title',
        'description',
        'price',
    ];


    public function reports()
    {
        return $this->belongsToMany(Report::class, 'reports_offers', 'offer_id', 'report_id')
                ->withPivot('description', 'price')
                ->withTimestamps();
    }

}