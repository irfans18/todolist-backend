<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\booking;
use App\Models\room;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        return booking::all();
    }

    public function checkBooking(request $request){

        $bookingCheck = DB::table('room')
                        ->join('booking', 'booking.room_id', 'room.id')
                        ->where('booking.booking_status', '=', '1')
                        ->where('booking.check_in', '=', $request->check_in)
                        // ->where('booking.check_out', '=', $request->check_out)
                        ->select('room.id', 'room.hotel_id', 'booking.booking_status', 'booking.user_id')
                        ->get();

        return $bookingCheck;

        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'username' => 'required|string|max:255|unique:users',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|min:6|confirmed',
        //     //'user_level' => 'required|integer|max:2|min:1',
        //     'user_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
        // ]);

        // if($validator->fails()){
        //     return response()->json($validator->errors()->toJson(), 400);
        // }
    }

    public function create(request $request)
    {
        $user = Auth::user();
        // $bookingTime = DB::table('booking')
        //                 ->where('booking_status', '=', 1)
        //                 ->where('check_in', '>', $request->check_in)
        //                 ->where('check_out', '<', $request->check_out);

        if ($user->user_level == 2) {
            // if ($bookingTime){
            if (DB::table('booking')
                ->where([
                    ['booking_status', '=', 1],
                    ['check_in', '=', $request->check_in],
                    ['check_out', '<>', $request->check_out]
                    ])
                ->whereBetween('check_in', [$request->check_in, $request->check_out])
                ->doesntExist()
            ) {
                $booking = new booking();
                $booking->user_id = $user->id;
                $booking->room_id = $request->room_id;
                $booking->booking_status = 1;
                $booking->check_in = $request->check_in;
                $booking->check_out = $request->check_out;

                $booking->booking_time = now();
                $booking->save();

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil"
                ]);
            } else{

                return response()->json([
                    'success' => false,
                    'message' => "Room penuh"
                ]);
            }
        }else{

        return response()->json([
            'success' => false,
            'message' => "Gagal"
        ]);
        }
    }

    public function updateBookingStatus($id, $status)
    {
        $user = Auth::user();
        $booking = booking::find($id);
        if ($status >= 1 && $status <= 3) {
            if ($booking->user_id == $user->id) {
                $booking->booking_status = $status;
                $booking->save();
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil update"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Akses Ditolak"
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Salah Input Status"
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $booking = booking::find($id);
        $user = Auth::user();

        if ($booking->user_id == $user->id) {
            $booking->room_id = $request->room_id;
            $booking->check_in = $request->check_in;
            $booking->check_out = $request->check_out;
            $booking->save();
            return response()->json([
                'success' => true,
                'message' => "Berhasil update"
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Akses Ditolak"
            ]);
        }
    }

    public function delete($id)
    {
        $user = Auth::user();
        if ($user->user_level == 1) {
            $hotel_id = $user->hotel->id;
            $room = room::select('id')->where('hotel_id', $hotel_id)->get();
            $booking = booking::find($id);

            foreach ($room as $room_id) {
                if ($booking->room_id == $room_id->id) {
                    $booking->delete();
                    return response()->json([
                        'success' => true,
                        'message' => "Berhasil delete"
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => "Gagal delete"
            ]);
        } else if ($user->user_level == 2) {
            $booking = booking::find($id);
            if ($booking->user_id == $user->id) {
                $booking->delete();
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil delete"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Akses Ditolak"
                ]);
            }
        }
    }

    public function findBookingType($id)
    {
        return booking::select('user_id', 'room_id')->where('id', $id)->get();
    }

    public function showBookings($status_id)
    {
        $user = Auth::user();
        //return booking::where('booking_status',$status_id);
        if ($status_id == null || ($status_id >= 1 && $status_id <= 3)) {
            if ($user->user_level == 1) {
                $hotel_id = $user->hotel->id;
                $booking = DB::table('hotel')
                    ->join('room', 'hotel.id', '=', 'room.hotel_id')
                    ->join('booking', 'room.id', '=', 'booking.room_id')
                    ->join('users', 'users.id', '=', 'booking.user_id')
                    ->where('hotel.id', $hotel_id)
                    ->select(
                        'booking.*',
                        'hotel.hotel_name',
                        'room.room_type',
                        'room.room_code',
                        'room.bed_type',
                        'room.room_price',
                        'users.name'
                    );
                if ($status_id == null) {
                    return $booking->get();
                } else {
                    return response()->json(["booking" => $booking->where('booking.booking_status', $status_id)->get()]);
                    //return $booking->where('booking.booking_status',$status_id)->get();
                }
            } else if ($user->user_level == 2) {
                $booking = DB::table('users')
                    ->join('booking', 'users.id', '=', 'booking.user_id')
                    ->join('room', 'room.id', '=', 'booking.room_id')
                    ->join('hotel', 'hotel.id', '=', 'room.hotel_id')
                    ->where('users.id', $user->id)
                    ->select(
                        'booking.id',
                        'booking.check_in',
                        'booking.check_out',
                        'booking.total_price',
                        'hotel.hotel_name',
                        'hotel.hotel_picture',
                        'hotel.hotel_location',
                        'room.room_code',
                        'room.room_type'
                    );
                if ($status_id == null) {
                    return $booking->get();
                } else {
                    return response()->json(["booking" => $booking->where('booking.booking_status', $status_id)->get()]);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Status ID tidak ditemukan"
            ]);
        }


        //return booking::where('booking_status', '=', 1)->paginate(15);
    }

    public function showBookingById($id)
    {
        $booking = DB::table('booking')
            ->join('users', 'users.id', '=', 'booking.user_id')
            ->join('room', 'room.id', '=', 'booking.room_id')
            ->join('hotel', 'hotel.id', '=', 'room.hotel_id')
            ->where('booking.id', $id)
            ->select(
                'booking.*',
                'users.name',
                'users.email',
                'users.telp',
                'hotel.hotel_name',
                'hotel.hotel_picture',
                'hotel.hotel_location',
                'room.room_type',
                'room.room_code',
                'room.bed_type',
                'room.room_price',
                'room.guest_capacity'
            )
            ->first();

        return response()->json(compact('booking'), 200);
    }
}
