<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Task;


class TaskController extends Controller
{
    public function createTask(request $request)
    {
        $user = Auth::user();
        
        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->deadline = $request->deadline;
        $task->checked = 0;        
        
        $task->save();
        
        return response()->json([
            'success' => true,
            'message' => "Berhasil"
            ]);
    }
        
        
    public function getTasks()
    {
        $user = Auth::user();
            
        $tasks = Task::all()->where('user_id', $user->id);
            
        return response()->json(compact('tasks'),200);
    }
        
    public function getSharedTasks(){
    $tasks = Task::all()->where('privacy', '1');
            
    return response()->json(compact('tasks'),200);
    }

    public function getDetailTask($id)
    {
        $user = Auth::user();
            
        $tasks = Task::find($id);
    
        return response()->json(compact('tasks'),200);
        
    }
        
    public function updateTask($id, request $request)
    {
        $user = Auth::user();
            
        $tasks = Task::find($id);
            if ($tasks->id == $id && $tasks->user_id == $user->id){
                $tasks->title = $request->title;
                $tasks->description = $request->description;
                $tasks->deadline = $request->deadline;
                $tasks->privacy = $request->privacy;
                
                $tasks->update_at = now();
                $tasks->save();
                
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil update"
                    ]);
                }else {
                    return response()->json([
                        'success' => false,
                        'message' => "Akses Ditolak"
                        ]);
                    }
    }

    public function deleteTask($id)
    {
        $user = Auth::user();
        $tasks = Task::find($id);
                 
        if ($tasks->user_id == $user->id) {
            $tasks->delete();
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
                    
                    
                    