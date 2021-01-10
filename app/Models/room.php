<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class room extends Model
{
    use HasFactory;

    protected $table = 'room';
    public $timestamps = false;

    public function hotel()
    {
        return $this->belongsTo('App\Models\hotel');
    }

    public function room_facilities()
    {
        return $this->hasMany('App\Models\room_facility');
    }
}
