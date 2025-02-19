<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Vendor;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderAssigned;

class BookingController extends Controller
{
    /*public function createBooking(Request $request) {
        
        $breadcrumbs = [
            'name' => 'Assign Parcel To Rider', 
        ];

        $vendors = Vendor::all();
        $vendorId = "";
        if($request->has('id'))
        {
            $vendorId = $request->id;
            $parcelList = Order::where(['vendor_id'=> $request->id,'order_status'=>4])->get();
        } else {
            $vendorId = "";
            $parcelList = Order::where('vendor_id', 0)->get();
        }
        return view('admin.booking.booking_form', compact('parcelList','vendorId','vendors','breadcrumbs'));
    }*/
    
    /*public function createSelectBooking(Request $request) {
        
        $vendors = Vendor::all();
        $vendorId = "";
        if($request->has('id'))
        {
            $vendorId = $request->id;
            $parcelList = Order::where('vendor_id', $request->id)->get();
        } else {
            $vendorId = "";
            $parcelList = Order::where('vendor_id', 0)->get();
        }
        return view('admin.booking.select_booking_form', compact('parcelList','vendorId','vendors'));
    }*/
    
    public function vendorParcel(Request $request)
    {
        return route('createBooking', ['id' => $request->vendor_id]);
    }
    
    public function vendorSelectParcel(Request $request)
    {
        return route('createSelectBooking', ['id' => $request->vendor_id]);
    }
    
    public function assignRider($id)
    {
        $order = Order::find($id);
        $vendorId = $order->vendor_id;
        //$riderId = User::where('id','!=',1)->get();
        $riderId = User::whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            }
        )->get();

        return view('admin.booking.assign_rider', compact('order','vendorId','riderId'));
    }
    
    public function saveAssignRider(Request $request)
    {
        $validatedData = $request->validate([
            //company detail
            'vendor_id' => 'required',
            'order_id' => 'required',
            'rider_id' => 'required',
            'drop_off_location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        
        $assign = [
            'vendor_id' => $request->vendor_id,
            'order_id' => $request->order_id,
            'rider_id' => $request->rider_id,
            'drop_off_location' => $request->drop_off_location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'trip_status_id' => 1,
            'status' => 1,
        ];
        

        OrderAssigned::create($assign);
        
        return back();
    }

    public function deliveryRatio(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Delivery Ratio', 
        ];

        $vendors_data = Vendor::all();
        $vendorRequest = 'any';

        if($request->from && $request->to && $request->vendor == 'any')
        {
            $from = $request->from;
            $to = $request->to;
            $vendorRequest = 'any';

            $overall_orders = Order::where('parcel_nature',1)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6,10,9])->count();
            $overall_delivered = Order::where('parcel_nature',1)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6])->count();
            $overall_cancel = Order::where('parcel_nature',1)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[10,9])->count();

            if($overall_orders > 0)
            {
                $overall_wining_ratio = ($overall_delivered/$overall_orders)*100;
                $overall_return_ratio = ($overall_cancel/$overall_orders)*100;
            }
            else
            {
                $overall_wining_ratio = 0;
                $overall_return_ratio = 0;
            }

            $vendors = Vendor::all();

            $vendor_name = [];
            $vendor_success_ratio = [];
            $vendor_failure_ratio = [];
            $vendor_total_order = [];
            $vendor_delivered_order = [];
            $vendor_cancel_order = [];

            foreach($vendors as $vendor)
            {
                $vendor_orders = Order::where('parcel_nature',1)->where('vendor_id', $vendor->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6,10,9])->count();
                $vendor_delivered = Order::where('parcel_nature',1)->where('vendor_id', $vendor->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6])->count();
                $vendor_cancel = Order::where('parcel_nature',1)->where('vendor_id', $vendor->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[10,9])->count();

                if($vendor_orders > 0)
                {
                    $vendor_wining_ratio = ($vendor_delivered/$vendor_orders)*100;
                    $vendor_return_ratio = ($vendor_cancel/$vendor_orders)*100;
                }
                else
                {
                    $vendor_wining_ratio = 0;
                    $vendor_return_ratio = 0;
                }

                $vendor_name[] = $vendor->vendor_name;
                $vendor_success_ratio[] = $vendor_wining_ratio;
                $vendor_failure_ratio[] = $vendor_return_ratio;
                $vendor_total_order[] = $vendor_orders;
                $vendor_delivered_order[] = $vendor_delivered;
                $vendor_cancel_order[] = $vendor_cancel;
            }
        }
        elseif($request->from && $request->to && $request->vendor <> 'any')
        {
            $from = $request->from;
            $to = $request->to;
            $vendorRequest = $request->vendor;

            $overall_orders = Order::where('parcel_nature',1)->where('vendor_id', $vendorRequest)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6,10,9])->count();
            $overall_delivered = Order::where('parcel_nature',1)->where('vendor_id', $vendorRequest)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6])->count();
            $overall_cancel = Order::where('parcel_nature',1)->where('vendor_id', $vendorRequest)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[10,9])->count();

            if($overall_orders > 0)
            {
                $overall_wining_ratio = ($overall_delivered/$overall_orders)*100;
                $overall_return_ratio = ($overall_cancel/$overall_orders)*100;
            }
            else
            {
                $overall_wining_ratio = 0;
                $overall_return_ratio = 0;
            }

            $vendors = Vendor::where('id', $vendorRequest)->get();

            $vendor_name = [];
            $vendor_success_ratio = [];
            $vendor_failure_ratio = [];
            $vendor_total_order = [];
            $vendor_delivered_order = [];
            $vendor_cancel_order = [];

            foreach($vendors as $vendor)
            {
                $vendor_orders = Order::where('parcel_nature',1)->where('vendor_id', $vendor->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6,10,9])->count();
                $vendor_delivered = Order::where('parcel_nature',1)->where('vendor_id', $vendor->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[6])->count();
                $vendor_cancel = Order::where('parcel_nature',1)->where('vendor_id', $vendor->id)->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->whereIn('order_status',[10,9])->count();

                if($vendor_orders > 0)
                {
                    $vendor_wining_ratio = ($vendor_delivered/$vendor_orders)*100;
                    $vendor_return_ratio = ($vendor_cancel/$vendor_orders)*100;
                }
                else
                {
                    $vendor_wining_ratio = 0;
                    $vendor_return_ratio = 0;
                }

                $vendor_name[] = $vendor->vendor_name;
                $vendor_success_ratio[] = $vendor_wining_ratio;
                $vendor_failure_ratio[] = $vendor_return_ratio;
                $vendor_total_order[] = $vendor_orders;
                $vendor_delivered_order[] = $vendor_delivered;
                $vendor_cancel_order[] = $vendor_cancel;
            }
        }
        else
        {
            $overall_wining_ratio = 0;
            $overall_return_ratio = 0;
            $vendor_name = [];
            $vendor_success_ratio = [];
            $vendor_failure_ratio = [];
            $vendor_total_order = [];
            $vendor_delivered_order = [];
            $vendor_cancel_order = [];
        }

        return view('admin.delivery-ratio',compact('vendors_data','overall_wining_ratio','overall_return_ratio','vendor_name','vendor_success_ratio','vendor_failure_ratio','vendorRequest','vendor_total_order','vendor_delivered_order','vendor_cancel_order'));
    }
    
}
