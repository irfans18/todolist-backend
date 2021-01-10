<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\facility_category;
use Illuminate\Support\Facades\Validator;

class FacilityCategoryController extends Controller
{
    public function index(){
        return facility_category::all();
    }

    public function create(request $request){
        $facilityCategory = new facility_category();

        $facilityCategory->facility_name = $request->facility_name;
        if(!empty($request->file('facility_icon'))) {
            $validator = Validator::make($request->all(), [
                'facility_icon' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }
            $file = $request->file('facility_icon');
            $upload_dest = 'facility_category';
            $extension = $file->extension();
            $path = $file->store($upload_dest);
            $facilityCategory->facility_icon = $path;

        }
        $facilityCategory->save();

        return $facilityCategory;
    }

    public function update(request $request, $id){
        $facilityCategory = facility_category::find($id);
        
        $facilityCategory->facility_name = $request->facility_name;
        if(!empty($request->file('facility_icon'))) {
            $validator = Validator::make($request->all(), [
                'facility_icon' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }
            unlink('storage/'.$facilityCategory->facility_icon);
            $file = $request->file('facility_icon');
            $upload_dest = 'facility_category';
            $extension = $file->extension();
            $path = $file->store($upload_dest);
            $facilityCategory->facility_icon = $path;

        }
        $facilityCategory->save();

        return $facilityCategory;
    }

    public function delete($id){
        $facilityCategory = facility_category::find($id);
        unlink('storage/'.$facilityCategory->facility_icon);
        $facilityCategory->delete();

        return "Data Berhasil Dihapus";
    }
}
