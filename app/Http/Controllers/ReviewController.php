<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\hotel;
use App\Models\Review;
use App\Models\room_facility;
use App\Models\facility_category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(){
        return Review::all();
    }

    public function findHotelType($id){
        return hotel::select()->where('id', $id)->get();
    }

    public function getHotelReview(){
        $user = Auth::user();
        $hotel_id = $user->hotel->id;

        if($user->user_level == 1){
            $avg = DB::table('hotel')
                ->join('review','hotel.id','=','review.hotel_id')
                ->where('hotel_id',$hotel_id)
                ->avg('hotel_rating');
            return json_encode($avg);
        } else {
            return "akses ditolak";
        }
    }

}