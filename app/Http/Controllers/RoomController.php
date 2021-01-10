<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\room;
use App\Models\hotel;
use App\Models\booking;
use App\Models\room_facility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateInterval;
use DatePeriod;

class RoomController extends Controller
{
    public function index(){
        //return room::all();
        $user = Auth::user();
        if($user->user_level == 1){
            return $user->hotel->rooms;
        } else {
            return "akses ditolak";
        }
    }
    
    public function getRoomById($id){
        $user = Auth::user();
        if($user->user_level == 1){
            return room::select('*')->where('id', $id)->first();
        }
        return "Akses Ditolak";
    }

    public function getRoomDetail($id){
        $user = Auth::user();
        $hotel = hotel::select('hotel.*')
                    ->leftJoin('room','hotel.id','=','room.hotel_id')
                    ->where('room.id',$id)
                    ->first();
            $room = room::where('id', $id)->first();
            $facility = room_facility::select('facility_category.*')
                        ->leftJoin('facility_category','facility_category.id','=','room_facility.facility_category_id')
                        ->where('room_facility.room_id',$id)
                        ->get();

        if($user->user_level == 1){
            return response()->json([
                'hotel' => $hotel,
                'room' => $room,
                'facility' => $facility
                ]);
        }else if($user->user_level == 2){
            return response()->json(['room' => $room], 200);
        }else{
            return "Akses Ditolak";
        }
        
    }

    public function getHotelRoom(){
        $user = Auth::user();
        if($user->user_level == 1){
            return $user->hotel->rooms;
        } else {
            return "akses ditolak";
        }
    }

    public function showRoomByHotel($hotel_id){
        //return booking::where('hotel_id', '=', $hotel_id);
        $hotel = DB::table('room')
            ->leftJoin('hotel','hotel.id','=','room.hotel_id')
            ->leftJoin('room_facility','room.id','=','room_facility.room_id')
            ->leftJoin('facility_category','facility_category.id','=','room_facility.facility_category_id')
            ->join('booking', 'booking.room_id', 'room.id')
            ->where('hotel.id',$hotel_id)
            ->select('room.*','hotel.hotel_name',
                'room_facility.facility_category_id',
                'facility_category.facility_name',
                'facility_category.facility_icon',
                'booking.booking_status', 
                'booking.check_in',
                'booking.check_out',
                'booking.user_id')
            // ->select('room.id', 'room.hotel_id', 'booking.booking_status', 'booking.user_id')

            ->orderBy('room.id','asc')
            ->orderBy('room_facility.facility_category_id','asc')
            ->get();
        return $hotel;
    }

    public function form(){
        $hotel = hotel::all();

        return $hotel;
    }
  
    public function findRoomType($id){
        $room = room::select('*')->where('id', $id)->first(); 
        $facility = room_facility::select('*')->where('room_id', $room->id)->get();
        
        echo $room;
        echo $room->id;
        echo $facility;
    }

    public function create(request $request){
        $room = new room;
        $user = Auth::user();
        
        $checkHotel = hotel::firstOrNew([
            'user_id' => $user->id
        ]);

        if($user->user_level == 1 && $checkHotel->exists){
            $hotel = hotel::where('user_id', '=', $user->id)->first();

            $room->hotel_id = $hotel->id;
            $room->room_code = $request->room_code;
            $room->room_type = $request->room_type;
            $room->bed_type = $request->bed_type;
            $room->bed_count = $request->bed_count;
            $room->room_price = $request->room_price;
            $room->guest_capacity = $request->guest_capacity;
            if(!empty($request->file('room_picture'))) {

                $validator = Validator::make($request->all(), [
                    'room_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors()->toJson(), 400);
                }

                $file = $request->file('room_picture');
                $upload_dest = 'room_picture';
                $extension = $file->extension();
                $path = $file->store($upload_dest);
                $room->room_picture = $path;

            }
            $room->save();

            return $room;
        }else{
            return "Akses Ditolak";
        }
    }

    public function uploadPicture(Request $request, $id){
        $user = Auth::user();
        $room = room::find($id);
        if(!empty($request->file('room_picture'))) {

            $validator = Validator::make($request->all(), [
                'room_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }

            if($room->room_picture != null){
                unlink('storage/'.$room->room_picture);
            }

            if($user->id == $room->hotel->user_id && $user->user_level == 1){
                $file = $request->file('room_picture');
                $upload_dest = 'room_picture';
                $extension = $file->extension();
                $path = $file->store($upload_dest);
                $room->room_picture = $path;

            }
            $room->save();
        }
        return $room;
        
    }

    public function update(Request $request, $id){
        $room = room::find($id);
        $user = Auth::user();

        if($user->user_level == 1 && $user->id == $room->hotel->user_id){
            $room->room_code = $request->room_code;
            $room->room_type = $request->room_type;
            $room->bed_type = $request->bed_type;
            $room->bed_count = $request->bed_count;
            $room->room_price = $request->room_price;
            $room->guest_capacity = $request->guest_capacity;

            if(!empty($request->file('room_picture'))) {
                $validator = Validator::make($request->all(), [
                    'room_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
                ]);

                if($validator->fails()){
                    return response()->json($validator->errors()->toJson(), 400);
                }
                if($room->room_picture != null){
                    unlink('storage/'.$room->room_picture);
                }
                $file = $request->file('room_picture');
                $upload_dest = 'room_picture';
                $extension = $file->extension();
                $path = $file->store($upload_dest);
                $room->room_picture = $path;

            }
            $room->save();

            return $room;
        }else{
            return "Akses Ditolak";
        }
    }

    public function delete($id){
        $room = room::find($id);
        $user = Auth::user();

        if($user->id == $room->hotel->user_id && $user->user_level == 1){
            if($room->room_picture != null){
                unlink('storage/'.$room->room_picture);
            }
            $room->delete();

             return response()->json(['success' => true], 200);
        }else{
            return "Akses Ditolak";
        }
    }

    public function getRoomByTime(request $request, $hotel_id){       
        $room = DB::table('room')
            ->leftJoin('hotel','hotel.id','=','room.hotel_id')
            ->leftJoin('room_facility','room.id','=','room_facility.room_id')
            ->leftJoin('facility_category','facility_category.id','=','room_facility.facility_category_id')
            ->join('booking', 'booking.room_id', 'room.id')
            ->where('hotel.id',$hotel_id)
            ->select('room.*','hotel.hotel_name',
                'room_facility.facility_category_id',
                'facility_category.facility_name',
                'facility_category.facility_icon',
                'booking.booking_status', 
                'booking.check_in',
                'booking.check_out',
                'booking.user_id')
            // ->select('room.id', 'room.hotel_id', 'booking.booking_status', 'booking.user_id')

            ->orderBy('room.id','asc')
            ->orderBy('room_facility.facility_category_id','asc')
             ->get();
         
         $start = $request->check_in;
         $end = $request->check_out;
         $interval = DateInterval::createFromDateString('1 day');
         $period = new DatePeriod(new DateTime($start), $interval, new DateTime($end));
 
         foreach($room as $rm){
             $rm->is_booked = false;
             $booking = DB::table('booking')
                 ->select('*')
                 ->where('room_id', $rm->id)
                 ->where('booking_status', 1)
                 ->get();
             
                 foreach($period as $dt){
                     if(!$booking->isEmpty()){
                         foreach($booking as $book){
                             $tempInterval = DateInterval::createFromDateString('1 day');
                             $tempPeriod = new DatePeriod(new DateTime($book->check_in), $tempInterval, new DateTime($book->check_out));
         
                             foreach($tempPeriod as $tmp){
                                 if($tmp == $dt){
                                     $rm->is_booked = true;
                                 }
                             }
                         }   
                     }
                 }
         }
         
         //$room = $room->where('is_booked', false);
         return $room;
     }

     public function getAvailableRoom(Request $request, $id){
        $room = DB::table('room')
            ->select('*')
            ->where('room.hotel_id', $id)
            ->get();
        
        $start = $request->check_in;
        $end = $request->check_out;
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod(new DateTime($start), $interval, new DateTime($end));

        foreach($room as $rm){
            $rm->is_booked = false;
            $booking = DB::table('booking')
                ->select('*')
                ->where('room_id', $rm->id)
                ->where('booking_status', 1)
                ->get();
            
                foreach($period as $dt){
                    if(!$booking->isEmpty()){
                        foreach($booking as $book){
                            $tempInterval = DateInterval::createFromDateString('1 day');
                            $tempPeriod = new DatePeriod(new DateTime($book->check_in), $tempInterval, new DateTime($book->check_out));
        
                            foreach($tempPeriod as $tmp){
                                if($tmp == $dt){
                                    $rm->is_booked = true;
                                }
                            }
                        }   
                    }
                }
        }
        
        //$room = $room::select('*')->where('is_booked', false);
        return response()->json(['roomList' => $room], 200);
     }
}
