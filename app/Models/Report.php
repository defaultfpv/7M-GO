<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    
    protected $fillable = [
        'deal_id',
        'title',
        'description',
        'price'
    ];  
    
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function files()
    {
        return $this->hasMany(File::class, 'report_id');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'reports_offers', 'report_id', 'offer_id')
                ->withPivot('description', 'price')
                ->withTimestamps();
    }

}
