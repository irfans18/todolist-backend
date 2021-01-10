<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class facility_category extends Model
{
    use HasFactory;

    protected $table = 'facility_category';
    public $timestamps = false;

    public function room_facilities()
    {
        return $this->hasMany('App\Models\room_facility');
    }
}
