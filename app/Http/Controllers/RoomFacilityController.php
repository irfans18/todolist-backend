<?php

namespace App\Http\Controllers;

use App\Models\hotel;
use App\Models\room;
use Illuminate\Http\Request;
use App\Models\room_facility;
use Illuminate\Support\Facades\Auth;

class RoomFacilityController extends Controller
{
    public function index(){
        return room_facility::all();
    }

    public function getFacilityByRoomId($room_id){
        return room_facility::where('room_id', '=', $room_id)->get();
    }

    public function create(request $request, $room_id, $facility_id){
        // $roomFacility = new room_facility();
        // $user = Auth::user();
        // $room = room::find($room_id);
        // $hotel = hotel::where('id', $room->hotel_id)->first();

        // if($user->user_level == 1 && $user->id == $hotel->user_id){
        //     $roomFacility->room_id = $room->id;
        //     $roomFacility->facility_category_id = $facility_id;
        //     $roomFacility->description = $request->description;
        //     $roomFacility->save();

        //     return $roomFacility;    
        // }else{
        //     return "Akses Ditolak";
        // }
    }

    public function createManyRow(Request $request, $room_id){
        $data = [[]];
        $i = 0;
        $j = 0;
        foreach ($request->all() as $key => $value) {
            if($j%2 == 0){
                $data[$i]["room_id"] = $room_id;
                $data[$i]["facility_category_id"] = $value;    
            } else {
                $data[$i]["description"] = $value;
                $i++;
            }
            $j++;
        }

        $user = Auth::user();
        $room = room::find($room_id);
        $hotel = hotel::where('id', $room->hotel_id)->first();

        if($user->user_level == 1 && $user->id == $hotel->user_id){
            
            $result = room_facility::insert($data);
            return $result;    
        }else{
            return "Akses Ditolak";
        }
    }

    public function updateManyRow(Request $request, $room_id){
        room_facility::where('room_id',$room_id)->delete();
        $data = [[]];
        $i = 0;
        $j = 0;
        foreach ($request->all() as $key => $value) {
            if($j%2 == 0){
                $data[$i]["room_id"] = $room_id;
                $data[$i]["facility_category_id"] = $value;    
            } else {
                $data[$i]["description"] = $value;
                $i++;
            }
            $j++;
        }

        $user = Auth::user();
        $room = room::find($room_id);
        $hotel = hotel::where('id', $room->hotel_id)->first();

        if($user->user_level == 1 && $user->id == $hotel->user_id){
            
            $result = room_facility::insert($data);
            return $result;    
        }else{
            return "Akses Ditolak";
        }
    }

    public function update(request $request, $id){
        $roomFacility = room_facility::find($id);
        
        $roomFacility->room_id = $request->room_id;
        $roomFacility->facility_category_id = $request->facility_category_id;
        $roomFacility->description = $request->description;
        $roomFacility->save();

        return "Data berhasil diubah";
    }

    public function delete($id){
        $roomFacility = room_facility::find($id);
        $roomFacility->delete();

        return "Data berhasil dihapus";
    }
}
