<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\SubArea;


class SubAreaController extends Controller
{
    public function index(){
        $cities = City::all();
        return view('admin.subArea.add-area',compact('cities'));
    }

    public function createArea(Request $request){
        $validate = $request->validate([
            'area_name' => 'required',
            'area_city' => 'required',
        ]);

        $subarea = new SubArea();

        $subarea->area_name = $request->area_name;
        $subarea->city_id = $request->area_city;

        $subarea->save();

        return redirect()->back();
    }

    public function areaList(){
        $areas = SubArea::all();

        return view('admin.subArea.area-list',compact('areas'));
    }

    public function editArea($id){
        $cities = City::all();
        $area = SubArea::where('id',$id)->first();
            return view('admin.subArea.update-area',compact('area','cities'));
    }

    public function updateArea(Request $request){
        $validate = $request->validate([
            'area_name' => 'required',
            'area_city' => 'required',
        ]);
        
        $id = $request->area_id;
        $area = SubArea::find($id);
        $area->area_name = $request->area_name;
        $area->city_id = $request->area_city;
        $area->save();

        return redirect()->route('areaList');
    }
    
}
