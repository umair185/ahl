<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Packing;
use App\Models\AhlTimings;

class PackingController extends Controller
{
    public function index() {
        $breadcrumbs = [
            'name' => 'Packing List', 
        ];

        $packing = Packing::all();
        return view('admin.packing.index', compact('packing','breadcrumbs'));
    }
    
    public function create() {
        $breadcrumbs = [
            'name' => 'Packing List', 
        ];

        return view('admin.packing.create',compact('breadcrumbs'));
    }
    
    public function savePacking(Request $request) {
        $validatedData = $request->validate([
            //company detail
            'name' => 'required',
        ]);
        
        $packing = [
            'name' => $request->name,
        ];
        
        Packing::create($packing);
        
        return redirect()->route('packing');
    }
    
    public function edit($id) {
        $packing = Packing::findOrFail($id);
        return view('admin.packing.edit', compact('packing'));
    }
    
    public function saveEditPacking(Request $request) {
        $validatedData = $request->validate([
            //company detail
            'name' => 'required',
            'packing_id' => 'required',
        ]);
        
        $id = $request->packing_id;
        $packing = Packing::find($id);
        $packing->name = $request->name;
        $packing->save();
        
        return redirect()->route('packing');
    }
    
    public function timeIndex() {
        $breadcrumbs = [
            'name' => 'AHL Timing List', 
        ];

        $timings = AhlTimings::all();
        return view('admin.timing.index', compact('timings','breadcrumbs'));
    }
    
    public function createTiming() {
        return view('admin.timing.create');
    }
    
    public function saveTiming(Request $request) {
        $validatedData = $request->validate([
            'timing' => 'required'
        ]);
        
        $timing = [
            'timings' => $request->timing,
        ];
        
        AhlTimings::create($timing);
        
        return redirect()->route('timeIndex');
    }
    
    public function editTiming($id) {
        $timing = AhlTimings::find($id);
        return view('admin.timing.edit', compact('timing'));
    }
    
    public function saveEditTiming(Request $request) {
        $validateData = $request->validate([
            'timing' => 'required',
            'timing_id' => 'required',
        ]);
        
        $id = $request->timing_id;
        $timing = AhlTimings::find($id);
        $timing->timings = $request->timing;
        $timing->save();
        
        return redirect()->route('timeIndex');
    }
    
}
