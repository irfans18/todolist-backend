<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Task;


class TaskController extends Controller
{

    public function index(){
        return Task::all();
    }

    public function createTask(request $request)
    {
        $user = Auth::user();
        
        $task = new Task();
        $task->title = $request->title;
        // $task->user_id = $user->id;
        $task->description = $request->description;
        $task->deadline = $request->deadline;
        // $task->privacy = $request->privacy;
        $task->checked = 0;        
        
        $task->save();
        
        return response()->json([
            'success' => true,
            'message' => "Berhasil"
            ]);
    }
        
        
    public function getTasks()
    {
        // $user = Auth::user();
            
        // $task = Task::all()->where('user_id', $user->id);
        $task = Task::all();
            
        return response()->json(compact('task'),200);
    }
        
    public function getSharedTasks(){
    $task = Task::all()->where('privacy', '1');
            
    return response()->json(compact('task'),200);
    }

    public function getDetailTask($id)
    {
        // $user = Auth::user();
            
        $task = Task::find($id);
    
        return response()->json(compact('task'),200);
        
    }
        
    public function updateTask($id, request $request)
    {
        // $user = Auth::user();
            $task = Task::find($id);
        // $task = Task::select('*')->where('id', $id)->get();
            // if ($task->id == $id && $task->user_id == $user->id){
            // if ($task->id == $id){
                $task->title = $request->title;
                $task->description = $request->description;
                $task->deadline = $request->deadline;
                // $task->privacy = $request->privacy;
                
                // $task->update_at = now();
                $task->save();
                
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil update"
                    ]);
                // }else {
                //     return response()->json([
                //         'success' => false,
                //         'message' => "Akses Ditolak"
                //         ]);
                //     }
    }

    public function deleteTask($id)
    {
        // $user = Auth::user();
        $task = Task::find($id);
                 
        // if ($task->user_id == $user->id) {
            $task->delete();
            return response()->json([
                'success' => true,
                'message' => "Berhasil delete"
                ]);
        // } else {
        //     return response()->json([
        //        'success' => false,
        //        'message' => "Akses Ditolak"
        //         ]);
        // }

    }    

    public function checkItem($id)
    {
        $task = Task::find($id);

        $task->checked = 1;
        $task->save();
                
                return response()->json([
                    'success' => true,
                    'message' => "Semangat! 1 task selesai."
                    ]);
    }
}
                    
                    
                    