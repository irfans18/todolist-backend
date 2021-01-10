<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomFacilityController;
use App\Http\Controllers\FacilityCategoryController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/user/register/owner', [UserController::class, 'register']);
Route::post('/user/register/customer', [UserController::class, 'registerCustomer']);
Route::post('/user/login/owner', [UserController::class, 'login']);
Route::post('/user/login/customer', [UserController::class, 'loginCustomer']);
Route::post('/auth/refresh_token', [UserController::class, 'refreshToken']);

Route::middleware('jwt.verify')->group(function () {  
    Route::get('test', [UserController::class, 'validateToken']);   
	Route::get('user', [UserController::class, 'index']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/user/update-picture', [UserController::class, 'updatePicture']);
    Route::post('/user/update-password', [UserController::class, 'updatePassword']);
    Route::delete('/user/delete', [UserController::class, 'delete']);
    Route::get('/user/logout', [UserController::class, 'logout']);


    Route::post('/task/create', [TaskController::class, 'create']);
    Route::get('/task/private', [TaskController::class, 'getTasks']);
    Route::get('/task/public', [TaskController::class, 'getSharedTasks']);
    Route::delete('/task/delete/{id}', [TaskController::class, 'deleteTask']);
    Route::get('/task/detail/{id}', [TaskController::class, 'getDetailTask']);
    Route::post('/task/update/{id}', [TaskController::class, 'updateTask']);

    // Route::get('/hotel', [HotelController::class,'index']);
    // Route::get('/hotel/search/{param}', [HotelController::class,'getHotelByParam']);
    // Route::post('/hotel/create', [HotelController::class,'create']);
    // Route::post('/hotel/update', [HotelController::class,'update']);
    // Route::delete('/hotel/delete/{id}', [HotelController::class,'delete']);
    // Route::get('/hotel/detail-by-id/{id}', [HotelController::class,'getHotelById']);
    // Route::get('/hotel/detail/', [HotelController::class,'getHotelByOwner']);
    // Route::get('/hotel/profile', [HotelController::class,'getHotelProfile']);
    // Route::post('/hotel/upload-picture', [HotelController::class,'uploadPicture']);
    // Route::get('/hotel/facility', [HotelController::class,'getHotelFacilities']);
    // Route::get('/hotel/price', [HotelController::class,'getHotelPrice']);
    
    // Route::post('/booking/create', [BookingController::class,'create']);
    // Route::post('/booking/update/{id}', [BookingController::class,'update']);
    // Route::post('/booking/change-status/{id}/{status}', [BookingController::class,'updateBookingStatus']);
    // Route::delete('/booking/delete/{id}', [BookingController::class,'delete']);
    // Route::get('/booking/list/{status_id}', [BookingController::class,'showBookings']);
    // Route::get('/booking/detail/{id}', [BookingController::class,'showBookingById']);
    // // Route::get('/booking/detail/{id}', [BookingController::class,'findBookingType']);
    // Route::post('/booking/check', [BookingController::class,'checkBooking']);

    // Route::get('/room', [RoomController::class,'index']);
    // Route::get('/room/list/{id}', [RoomController::class, 'getRoomById']);
    // Route::post('/room/create', [RoomController::class,'create']);
    // Route::post('/room/update/{id}', [RoomController::class,'update']);
    // Route::post('/room/upload-picture/{id}', [RoomController::class,'uploadPicture']);
    // Route::delete('/room/delete/{id}', [RoomController::class,'delete']);
    // Route::get('/room/detail/{id}', [RoomController::class,'getRoomDetail']);
    // Route::get('/room/hotel/{id}', [RoomController::class,'showRoomByHotel']); 
    // Route::get('/room/list', [RoomController::class,'getHotelRoom']);
    // Route::post('/room/validate-time/{hotel_id}', [RoomController::class,'getRoomByTime']);
    // Route::post('/room/show-available/{id}', [RoomController::class,'getAvailableRoom']);

    // Route::get('/facility-category', [FacilityCategoryController::class, 'index']);
    // Route::post('/facility-category/create', [FacilityCategoryController::class, 'create']);
    // Route::put('/facility-category/update/{id}', [FacilityCategoryController::class, 'update']);
    // Route::delete('/facility-category/delete/{id}', [FacilityCategoryController::class, 'delete']);

    // Route::get('/room-facility', [RoomFacilityController::class, 'index']);
    // Route::get('/room-facility/list/{room_id}', [RoomFacilityController::class, 'getFacilityByRoomId']);
    // Route::post('/room-facility/create/{room}/{facility}', [RoomFacilityController::class, 'create']);
    // Route::post('/room-facility/create-many/{room_id}', [RoomFacilityController::class, 'createManyRow']);
    // Route::post('/room-facility/update-many/{room_id}', [RoomFacilityController::class, 'updateManyRow']);
    // Route::put('/room-facility/update/{id}', [RoomFacilityController::class, 'update']);
    // Route::delete('/room-facility/delete/{id}', [RoomFacilityController::class, 'delete']);

    // Route::get('/review/hotel', [ReviewController::class, 'getHotelReview']);
});
