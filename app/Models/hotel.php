<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class hotel extends Model
{
    use HasFactory;

    protected $table = 'hotel';
    public $timestamps = false;
    
    protected $fillable = [
        'hotel_name', 'hotel_location', 'hotel_desc', 'hotel_picture', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function rooms()
    {
        return $this->hasMany('App\Models\room');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review');
    }
}
