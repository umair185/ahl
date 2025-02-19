<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Complain;
use App\Models\OrderAssigned;
use App\Models\Order;
use App\Models\ParcelLimit;
use App\Models\City;
use Auth;

class ComplainController extends Controller
{
    public function createVendorComplain()
    {
        $breadcrumbs = [
            'name' => 'Create Vendor Complain', 
        ];

        return view('vendor.complain.create-complain',compact('breadcrumbs'));
    }

    public function saveVendorComplain(Request $request)
    {
        $vendor_id = Auth::user()->vendor_id;
        $complain = $request->complain;

        $data = [
            'vendor_id' => $vendor_id,
            'complain' => $complain,
            'status' => 1,
        ];

        Complain::create($data);

        return redirect()->route('pendingVendorComplain')->with('success','Complain Generated Successfully!');
    }

    public function pendingVendorComplain()
    {
        $breadcrumbs = [
            'name' => 'Pending Vendor Complain', 
        ];
        $vendor_id = Auth::user()->vendor_id;
        $complains = Complain::where('vendor_id', $vendor_id)->where('status', 1)->get();

        return view('vendor.complain.pending-complain',compact('breadcrumbs','complains'));
    }

    public function deleteVendorComplain($id)
    {
        $find_complain = Complain::find($id);
        $find_complain->update(['status'=>0]);

        return redirect()->back()->with('success','Complain Deleted Successfully!');
    }

    public function inProgressVendorComplain()
    {
        $breadcrumbs = [
            'name' => 'In-Progress Vendor Complain', 
        ];
        $vendor_id = Auth::user()->vendor_id;
        $complains = Complain::where('vendor_id', $vendor_id)->where('status', 2)->get();

        return view('vendor.complain.in-progress-complain',compact('breadcrumbs','complains'));
    }

    public function resolvedVendorComplain()
    {
        $breadcrumbs = [
            'name' => 'Resolved Vendor Complain', 
        ];
        $vendor_id = Auth::user()->vendor_id;
        $complains = Complain::where('vendor_id', $vendor_id)->where('status', 3)->get();

        return view('vendor.complain.complete-complain',compact('breadcrumbs','complains'));
    }

    //admin side

    public function pendingComplain()
    {
        $breadcrumbs = [
            'name' => 'Pending Complains', 
        ];
        $complains = Complain::where('status', 1)->get();

        return view('admin.complain.pending-complain',compact('breadcrumbs','complains'));
    }

    public function actionComplain($id)
    {
        $breadcrumbs = [
            'name' => 'Action over Complains', 
        ];
        $complain = Complain::find($id);

        return view('admin.complain.action-complain',compact('breadcrumbs','complain'));
    }

    public function saveActionComplain(Request $request)
    {
        $find_complain = Complain::find($request->id);
        $find_complain->update(['status'=>2, 'action' => $request->remarks, 'action_by'=> Auth::user()->id]);

        return redirect()->route('inProgressComplain')->with('success','Complain Responded Successfully!');
    }

    public function inProgressComplain()
    {
        $breadcrumbs = [
            'name' => 'In-Progress Complains', 
        ];
        $complains = Complain::where('status', 2)->get();

        return view('admin.complain.in-progress-complain',compact('breadcrumbs','complains'));
    }

    public function saveResolvedComplain($id)
    {
        $find_complain = Complain::find($id);
        $find_complain->update(['status'=>3]);

        return redirect()->route('resolvedComplain')->with('success','Complain Resolved Successfully!');
    }

    public function revertComplain($id)
    {
        $find_complain = Complain::find($id);
        $find_complain->update(['status'=>2]);

        return redirect()->route('inProgressComplain')->with('success','Complain Reverted Successfully!');
    }

    public function resolvedComplain()
    {
        $breadcrumbs = [
            'name' => 'Resolved Complains', 
        ];
        $complains = Complain::where('status', 3)->get();

        return view('admin.complain.complete-complain',compact('breadcrumbs','complains'));
    }

    public function pendingRemarksList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Pending Remarks List', 
        ];
        if($request->date)
        {
            $pending_remarks = OrderAssigned::whereDate('created_at', $request->date)->where('remarks_status',0)->whereIn('trip_status_id', [5,6])->where('status', 0)->orderBy('id','DESC')->get();
        }
        else
        {
            $pending_remarks = [];
        }
        
        return view('admin.remarks.pending-remarks',compact('breadcrumbs','pending_remarks'));
    }

    public function pendingRemark($id)
    {
        $order = OrderAssigned::find($id);

        return view('admin.remarks.remarks', compact('order'));
    }

    public function savePendingRemark(Request $request)
    {
        $order = OrderAssigned::find($request->order_id);

        $towerRemarks = $request->remarks;
        $order->update(['remarks' => $towerRemarks, 'remarks_by' => Auth::user()->id, 'remarks_status' => 1]);

        return redirect()->back()->with('message','Remarks Added Successfully....!');
    }

    public function completeRemarksList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Remarks List', 
        ];
        if($request->date)
        {
            $pending_remarks = OrderAssigned::whereDate('created_at', $request->date)->where('remarks_status',1)->whereIn('trip_status_id', [5,6])->where('status', 0)->orderBy('id','DESC')->get();
        }
        else
        {
            $pending_remarks = [];
        }
        
        return view('admin.remarks.complete-remarks',compact('breadcrumbs','pending_remarks'));
    }

    //Parcel Limit Management
    public function createParcelLimit()
    {
        $breadcrumbs = [
            'name' => 'Create Parcel Limit', 
        ];

        $cities = City::all();
        return view('admin.parcel-limit.create-limit',compact('breadcrumbs','cities'));
    }

    public function saveParcelLimit(Request $request)
    {
        $city_id = $request->city_id;
        $limit = $request->limit;
        $created_by = Auth::user()->id;

        $check_limit = ParcelLimit::where('city_id', $city_id)->first();
        if(!empty($check_limit))
        {
            return redirect()->back()->with('error','Limit Already Set for this City');
        }

        $data = [
            'city_id' => $city_id,
            'limit' => $limit,
            'created_by' => $created_by,
        ];

        ParcelLimit::create($data);

        return redirect()->route('listParcelLimit');
    }

    public function listParcelLimit()
    {
        $breadcrumbs = [
            'name' => 'List Parcel Limit', 
        ];

        $limits = ParcelLimit::all();
        return view('admin.parcel-limit.list-limit',compact('breadcrumbs','limits'));
    }

    public function editParcelLimit($id)
    {
        $breadcrumbs = [
            'name' => 'Edit Parcel Limit', 
        ];

        $cities = City::all();
        $find_limit = ParcelLimit::find($id);
        return view('admin.parcel-limit.edit-limit',compact('breadcrumbs','cities','find_limit'));
    }

    public function updateParcelLimit(Request $request)
    {
        $limit = $request->limit;
        $limit_id = $request->limit_id;

        $find_limit = ParcelLimit::find($limit_id);

        $data = [
            'limit' => $limit,
        ];

        $find_limit->update($data);
        return redirect()->route('listParcelLimit');
    }
}
