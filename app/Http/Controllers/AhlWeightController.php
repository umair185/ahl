<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AhlWeight;

class AhlWeightController extends Controller
{

    public function index()
    {
        $breadcrumbs = [
            'name' => 'AHL Weights', 
        ];

        $ahlWeights = AhlWeight::with('weightCity')->get();

        // dd($ahlWeights);

        return view('admin.weight.index',compact('breadcrumbs','ahlWeights'));
    }

    public function create()
    {
        $breadcrumbs = [
            'name' => 'Create Weight', 
        ];

        return view('admin.weight.create',compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'weight' => 'required',
        ]);

        $requestWeight = $request->weight;

        $ahlWeightData = [
            'weight' =>  $requestWeight,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        AhlWeight::create($ahlWeightData);

        return back()->with(['success'=>'Weight Created Successfully!']);
    }

    public function edit(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Update Weight', 
        ];

        $weightId = $request->id;
        $ahlWeight = AhlWeight::find($weightId);

        return view('admin.weight.edit',compact('breadcrumbs','ahlWeight'));
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'weight' => 'required',
            'weight_id' => 'required',
        ]);

        //dd($request->all());
        $requestWeight = $request->weight;
        $requestWeightId = $request->weight_id;

        $weight = AhlWeight::where('id',$requestWeightId)->update([
            'weight' => $requestWeight,
            'updated_at' => now(),
        ]);

        return back()->with(['success'=>'Weight Updated Successfully!']);
    }
}
