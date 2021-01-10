<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\hotel;
use App\Models\room_facility;
use App\Models\facility_category;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function index(){
        return hotel::all();
    }

    public function findHotelType(){
        $user = Auth::user();
        $hotel = hotel::select()->where('user_id', $user->id)->get();
        
        //return hotel::select()->where('id', $id)->get();
        return response()->json(['hotel' => $hotel]);
    }

    public function getHotelById($id){
        $hotel = hotel::select()->where('id', $id)->first();
        
        return response()->json(['hotel' => $hotel], 200);
    }

    public function getHotelByParam(Request $request, $param){
        if($param == 'location'){
            $hotel = DB::table('hotel')
            ->leftJoin('room','hotel.id','=','room.hotel_id')
            ->where('hotel.hotel_location', 'LIKE', "%".$request->query('hotel_location')."%")
            ->select(DB::raw(
                'hotel.id,
                hotel.hotel_name,
                hotel.hotel_location,
                hotel.hotel_desc,
                hotel.hotel_picture,
                (CASE
                    WHEN min(room.room_price) is null THEN "0"
                    ELSE min(room.room_price)
                END) as hotel_price'
                )
            )
            ->groupBy([
                'hotel.id',
                'hotel.hotel_name',
                'hotel.hotel_location',
                'hotel.hotel_desc',
                'hotel.hotel_picture',
            ])
            ->get();
            
            //return json_encode($hotel);
            return response()->json(['hotelList' => $hotel], 200);
        }
    }

    public function getHotelByOwner(){
        $user = Auth::user();
        $hotel = hotel::select()->where('user_id', $user->id)->get();
        if($user->user_level == 1){
            return response()->json(['hotel' => $hotel]);
        } else {
            return "akses ditolak";
        }
    }


    public function getHotelProfile(){
        $user = Auth::user();
        $hotel_id = $user->hotel->id;

        if($user->user_level == 1){
            $hotel = DB::table('hotel')
            ->leftJoin('room','hotel.id','=','room.hotel_id')
            ->leftJoin('review','hotel.id','=','review.hotel_id')
            ->where('hotel.id',$hotel_id)
            ->select(DB::raw(
                'hotel.id,
                hotel.hotel_name,
                hotel.hotel_location,
                hotel.hotel_desc,
                hotel.hotel_picture,
                hotel.user_id, 
                (CASE
                    WHEN avg(review.hotel_rating) is null THEN "0"
                    ELSE avg(review.hotel_rating)
                END) as hotel_rating, 
                min(room.room_price) as hotel_price'
                )
            )
            ->groupBy([
                'hotel.id',
                'hotel.hotel_name',
                'hotel.hotel_location',
                'hotel.hotel_desc',
                'hotel.hotel_picture',
                'hotel.user_id'
            ])
            ->get();
            
            

            

            $facilitiy = DB::table('room')
                ->join('room_facility','room.id','=','room_facility.room_id')
                ->join('facility_category','facility_category.id','=','room_facility.facility_category_id')
                ->where('hotel_id',$hotel_id)
                ->distinct('facility_category.id')
                ->select('facility_category.*')
                ->get();

            //return json_encode($hotel);
            return response()->json([
                'hotel' => $hotel,
                'facility' => $facilitiy,
                'room' => $user->hotel->rooms
                ]);
        } else {
            return "akses ditolak";
        }
    }


    public function getHotelFacilities(){
        $user = Auth::user();
        $hotel_id = $user->hotel->id;

        if($user->user_level == 1){
            $fac = DB::table('room')
                ->join('room_facility','room.id','=','room_facility.room_id')
                ->join('facility_category','facility_category.id','=','room_facility.facility_category_id')
                ->where('hotel_id',$hotel_id)
                ->distinct('facility_category.id')
                ->select('facility_category.*')
                ->get();
            return json_encode($fac);
        } else {
            return "akses ditolak";
        }
    }

    public function getHotelPrice(){
        $user = Auth::user();
        $hotel_id = $user->hotel->id;

        if($user->user_level == 1){
            $price = DB::table('hotel')
                ->join('room','hotel.id','=','room.hotel_id')
                ->where('hotel_id',$hotel_id)
                ->min('room_price');
            return json_encode($price);
        } else {
            return "akses ditolak";
        }
    }

    public function create(Request $request){
        $hotel = new hotel();
        $user = Auth::user();

        $checkUser = hotel::firstOrNew([
            'user_id' => $user->id
        ]);
        
        if($user->user_level == 1 && !$checkUser->exists){
            $hotel->hotel_name = $request->hotel_name;
            $hotel->hotel_location = $request->hotel_location;
            $hotel->hotel_desc = $request->hotel_desc;
            $hotel->user_id = $user->id;

            if(!empty($request->file('hotel_picture'))) {
                $validator = Validator::make($request->all(), [
                    'hotel_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors()->toJson(), 400);
                }
                $file = $request->file('hotel_picture');
                $upload_dest = 'hotel_picture';
                $extension = $file->extension();
                $path = $file->storeAs(
                    $upload_dest, $user->id.'.'.$extension
                );
                $hotel->hotel_picture = $path;

            }
            $hotel->save();

            return $hotel;
        }else{
            return "Sudah ada Hotel";
        }
    }

    public function uploadPicture(Request $request){
        $user = Auth::user();
        $hotel = $user->hotel;

        if(!empty($request->file('hotel_picture'))) {

            $validator = Validator::make($request->all(), [
                'hotel_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }
            
            if($user->id == $hotel->user_id){
                if($hotel->hotel_picture != null){
                    unlink('storage/'.$hotel->hotel_picture);
                }
                $file = $request->file('hotel_picture');
                $upload_dest = 'hotel_picture';
                $extension = $file->extension();
                $path = $file->store($upload_dest);
                $hotel->hotel_picture = $path;

            $hotel->save();
            }
        }

        return $hotel;
        
    }

    public function update(Request $request){
        $user = Auth::user();
        $hotel = $user->hotel;

        $validator = Validator::make($request->all(), [
            'hotel_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        if($user->user_level == 1 && $user->id == $hotel->user_id){
            $hotel->hotel_name = $request->hotel_name;
            $hotel->hotel_location = $request->hotel_location;
            $hotel->hotel_desc = $request->hotel_desc;

            if(!empty($request->file('hotel_picture'))) {
                
                $file = $request->file('hotel_picture');
                $upload_dest = 'hotel_picture';
                $extension = $file->extension();
                $path = $file->store($upload_dest);
                $hotel->hotel_picture = $path;
            }

            $hotel->save();

            return response()->json(['hotel' => $hotel]);
        }else{
            return "Akses Ditolak";
        }
    }

    public function delete($id){
        $hotel = hotel::find($id);
        $user = Auth::user();

        if($user->user_level == 1 && $user->id == $hotel->user_id){
            if($hotel->hotel_picture != null){
                unlink('storage/'.$hotel->hotel_picture);
            }
            $hotel->delete();

            return "Data berhasil dihapus";
        }else{
            return "Akses Ditolak";
        }
    }
}