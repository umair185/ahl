<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\State;
use App\Models\City;
use App\Models\TagLine;
class CityController extends Controller
{
    public function index(){

        $states = State::where('country_id','166')->get();
        return view('admin.city.add-city',compact('states'));
    }

    public function createCity(request $request){

        $validate = $request->validate([
            'city_name' => 'required',
            'city_code' => 'required',
            'city_state' => 'required',
        ]);

        $city = new City();

        $city->name = $request->city_name;
        $city->code = $request->city_code;
        $city->state_id = $request->city_state;

        $city->save();

        return redirect()->back();
    }

    public function cityList(){
        $cities = City::get();

        return view('admin.city.city-list',compact('cities'));
    }

    public function editCity($id){
        $states = State::where('country_id','166')->get();
        $city = City::where('id',$id)->first();
            return view('admin.city.update-city',compact('city','states'));
    }

    public function updateCity(request $request){
        $validate = $request->validate([
            'city_name' => 'required',
            'city_code' => 'required',
            'city_state' => 'required',
        ]);
        
        $id = $request->city_id;
        $city = City::find($id);
        $city->name = $request->city_name;
        $city->code = $request->city_code;
        $city->state_id = $request->city_state;
        $city->save();

        return redirect()->route('cityList');
    }

    public function createTagLine(){

        return view('admin.tagline.add-tagline');
    }

    public function saveTagLine(request $request){

        $tag_line = $request->tag_line;

        $data = [
            'tag_line' => $tag_line,
            'status' => 1,
        ];

        TagLine::create($data);

        return redirect()->route('TagLineList');
    }

    public function TagLineList(){
        $tag_lines = TagLine::all();

        return view('admin.tagline.tagline-list',compact('tag_lines'));
    }

    public function editTagLine($id){
        $tag_line = TagLine::where('id',$id)->first();
        return view('admin.tagline.update-tagline',compact('tag_line'));
    }

    public function updateTagLine(request $request){

        $id = $request->tag_line_id;
        $find_tagline = TagLine::find($id);
        $tag_line = $request->tag_line;
        $status = $request->status;

        $data = [
            'tag_line' => $tag_line,
            'status' => $status,
        ];

        $find_tagline->update($data);

        return redirect()->route('TagLineList');
    }
}
