<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class room_facility extends Model
{
    use HasFactory;

    protected $table = 'room_facility';
    public $timestamps = false;

    public function room()
    {
        return $this->belongsTo('App\Models\room');
    }

    public function facility_category()
    {
        return $this->belongsTo('App\Models\facility_category');
    }
}
