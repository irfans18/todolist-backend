<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function refreshToken(){
        $token = JWTAuth::getToken();
        if(!$token){
            throw new BadRequestHtttpException('Token not provided');
        }
        try{
            $token = JWTAuth::refresh($token);
        }catch(TokenInvalidException $e){
            throw new AccessDeniedHttpException('The token is invalid');
        }
        return response()->json(['token'=>$token]);
    }

    public function validateToken(){
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['success' => false]);

        } catch (TokenInvalidException $e) {

            return response()->json(['success' => false]);

        } catch (JWTException $e) {

            return response()->json(['success' => false]);

        }

        return response()->json(['success' => true]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'),200);
    }

    public function loginCustomer(Request $request){
        $credentials = $request->only('email', 'password');
        
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user = Auth::user();
        
        if($user->user_level != 2){
            return response()->json(['error' => 'invalid_credentials'], 400);
        }else{
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
        // return response()->json(compact('token'),200);

        }
    }

    public function index()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'),200);
    }


    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out'],200);
    }


    public function registerCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required|string|max:255',
            // 'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if($validator->fails()){
            $msg = $validator->errors();
            return response()->json(['error' => $msg], 400, ['error' => $msg->first()]);
        }

        $user = new User;
        // $user->username = $request->username;
        // $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        // $user->user_level = 2;
        // $user->gender = $request->gender;
        // $user->telp = $request->telp;
        // $user->address = $request->address;
        $user->save();

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'username' => 'required|string|max:255|unique:users',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8|confirmed',
    //         //'user_level' => 'required|integer|max:2|min:1',
    //         'user_picture' => 'image|mimes:jpeg,png,jpg|max:2048',
    //     ]);
        

    //     if($validator->fails()){
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }

    //     $user = new User;
    //     $user->username = $request->username;
    //     $user->name = $request->name;
    //     $user->email = $request->email;
    //     $user->password = Hash::make($request->password);
    //     $user->user_level = 1;

    //     if(!empty($request->file('user_picture'))) {
    //         $file = $request->file('user_picture');
    //         $upload_dest = 'user_picture';
    //         $extension = $file->extension();
    //         $path = $file->storeAs(
    //             $upload_dest, $request->username.'.'.$extension
    //         );
    //         $user->user_picture = $path;

    //     } 
        
    //     $user->save();
    //     $token = JWTAuth::fromUser($user);
    //     return response()->json(compact('user','token'),200);
    // }
    // public function update(Request $request){
    //     $user = Auth::user();

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'username' => 'required|string|max:255|unique:users,id,$id',
    //         'email' => 'required|string|email|max:255|unique:users,id,$id',
    //         //'user_level' => 'required|integer|min:1|max:2',
    //         'user_picture' => 'image|mimes:jpeg,jpg,png|max:2048',
    //     ]);

    //     if($validator->fails()){
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }
        
    //     if($request->username != null){
    //         $user->username = $request->username;
    //     }
            
        
    //     if($request->name  != null){
    //         $user->name = $request->name;
    //     }

    //     if($request->email != null){
    //         $user->email = $request->email;
    //     }
        
    //     if($request->gender != null){
    //         $user->gender = $request->gender;    
    //     }

    //     if($request->telp != null){
    //         $user->telp = $request->telp;
    //     }

    //     if($request->address != null){
    //         $user->address = $request->address;
    //     }

    //     $user->save();

    //     return response()->json(compact('user'));
    // }

    // public function updatePicture(Request $request){
    //     $user = Auth::user();

    //     $validator = Validator::make($request->all(), [
    //         'user_picture' => 'required|image|mimes:jpeg,jpg,png|max:2048',
    //     ]);

    //     if($validator->fails()){
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }
    //     if($user->user_picture != null){
    //         unlink('storage/'.$user->user_picture);
    //     }
    //     $file = $request->file('user_picture');
    //     $upload_dest = 'user_picture';
    //     $extension = $file->extension();
    //     $path = $file->storeAs(
    //         $upload_dest, $user->username.'.'.$extension
    //     );
    //     $user->user_picture = $path;
    //     $user->save();

    //     return response()->json(compact('user'));
    // }

    // public function updatePassword(Request $request){
    //     $user = Auth::user();

    //     $validator = Validator::make($request->all(), [
    //         'password' => 'required|string|min:6|confirmed',
    //         'old_password' => 'required|string|min:6|max:100'
    //     ]);
    //     if($validator->fails()){
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }
        
    //     if(Hash::check($request->password, $user->password)){
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Password baru dan lama tidak boleh sama"
    //         ]);
    //     }else if(Hash::check($request->old_password, $user->password)){
            
    //         $user->password = Hash::make($request->password);
    //         $user->save();
    //         auth()->logout();
    //         return response()->json([
    //             'success' => true,
    //             'message' => "Berhasil"
    //         ]);
    //     }else{
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Password lama salah"
    //         ]);
    //     }


    //     // return response()->json(['message' => 'Password Update Successfully'],200);

    // }

    // public function delete(){

    //     $user = Auth::user();
    
    //     unlink('storage/'.$user->user_picture);
    //     $user->delete();

    //     return response()->json(['message' => 'Delete Successfully'],200);
    // }
    
}