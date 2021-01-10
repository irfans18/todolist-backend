<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'review';
    public $timestamps = false;
    
    protected $guarded = [
        'id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function hotel()
    {
        return $this->belongsTo('App\Models\hotel');
    }
}
