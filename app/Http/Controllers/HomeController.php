<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Status;
use App\Models\Order;
use App\Models\AhlWeight;
use App\Models\OrderAssigned;
use App\Models\ScanOrder;
use App\Models\PickerAssign;
use App\Models\RiderCashCollection;
use App\Models\VendorFinancial;
use App\Models\City;
use App\Models\UserCity;
use App\Models\ParcelSag;
use App\Models\OrderInSag;
use App\Models\Bilty;
use App\Models\SagInBilty;
use App\Models\RackParcelList;
use App\Models\RackBalancing;

use App\Helpers\Helper;
use App\Helpers\AHLHelper;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VendorDispatchReportExport;
use App\Exports\PickupVendorParcelCount;
use App\Exports\PRAReportExport;

use PDF;
use DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function pickerRider()
    {
        $breadcrumbs = [
            'name' => 'Assign Picker To Vendor', 
        ];

        if(Auth::user()->hasAnyRole('admin'))
        {
            $picker= User::where('status', 1)->whereHas(
                'roles', function($q){
                    $q->where('name', 'picker');
                }
            )
            ->with([
                'pickerVendor.vendor' => function($query){
                    $query->where('status', 1);
                },
            ])
            ->get();
        }
        elseif(Auth::user()->hasAnyRole('first_man'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');
    
            $picker= User::where('status', 1)->whereHas(
                'roles', function($q){
                    $q->where('name', 'picker');
                }
            )
            ->whereHas(
                'usercity', function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                }
            )->with([
                'pickerVendor.vendor' => function($query){
                    $query->where('status', 1);
                },
            ])
            ->get();
        }

        // dd($picker);
        //dd($picker[0]->pickerVendor[0]->vendor->vendor_name);
        return view('admin.PickupRider.index',compact('picker','breadcrumbs'));
    }
    
    public function assignVendor($id)
    {
        $breadcrumbs = [
            'name' => 'Assign Picker To Vendor', 
        ];

        $picker = User::find($id);
        $pickerId = $picker->id;
        //$riderId = User::where('id','!=',1)->get();
        $vendors = Vendor::where('status',1)->get();

        return view('admin.PickupRider.assign_picker', compact('breadcrumbs','picker','pickerId','vendors'));
    }
    
    public function saveAssignVendor(Request $request)
    {
        $validatedData = $request->validate([
            'picker_rider' => 'required',
            'vendor_id' => 'required'
        ]);
        
        $vendor_id = $request->vendor_id;
        $picker_id = $request->picker_rider;
        
        $pickerAssignCheck = PickerAssign::where('picker_id',$picker_id)->whereIn('vendor_id',$vendor_id)->get();
        
        

        foreach ($pickerAssignCheck as $key => $vendor) {
            $assingVendor[] = $vendor->vendor_id; 
        }

        
        if(isset($assingVendor)){
            $diffVendor = array_diff($vendor_id,$assingVendor);
        }else{
            $diffVendor = $vendor_id;
        }
        
        if(count($diffVendor) > 0){
            
            foreach($diffVendor as $one => $vendor)
            {
                $data = [
                    'vendor_id'=> $vendor_id[$one],
                    'picker_id'=> $picker_id,
                    'status' => 1,
                ];

                PickerAssign::create($data);
            }
            
            return back()->with(['flash'=>'success','flash_message'=> 'Vendor Assign To Picker!','flash_alert'=>'success']);

        }else{
            return back()->with(['flash'=>'error','flash_message'=> 'Already Assign!','flash_alert'=>'danger']);
        }
        
    }
    
    public function adminDashboard(Request $request)
    {
        if(Auth::user()->isVendorAdmin() || Auth::user()->isVendorEditor()){
            return redirect()->route('index');
        }
        
        $breadcrumbs = [
            'name' => 'Dashboard', 
        ];

        $from = $request->from;
        $to = $request->to;
        
        $status = Status::all();
        $vendors = Vendor::where('status',1)->get();



        if( Auth::user()->hasAnyRole('admin','hr') )
        {
            $vendorId = $request->vendor;
            $cityId = $request->city;
            $cities = City::all();
            if ($from && $to && $request->vendor <> 'any' && $request->city <> 'any') {

                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->where('consignee_city',$cityId)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,11,12,13,14])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->where('consignee_city',$cityId)->whereDate('created_at', '<=',$to)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status',6)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $vendor_parcel_commission = Helper::ahlCommissionParcelSumNew($vendorId);
                $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
                $final_cash_paid_to_vendor = ($vendor_payable - $vendor_parcel_commission) - $cash_paid_to_vendor;
                    // dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('order_status', 2)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('order_status', 3)->where('delayed_status', 0)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('order_status', 3)->where('delayed_status', 1)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 4)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 5)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 17)->where('vendor_id', $vendorId)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 18)->where('vendor_id', $vendorId)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->where('consignee_city',$cityId)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
    
                $vendorRequest = $request->vendor;
                $cityRequest = $request->city;

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }
            
            if ($from && $to && $request->vendor == 'any' && $request->city <> 'any') {
    
                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('consignee_city',$cityId)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('order_status',6)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');
                $vendor_parcel_commission = 0;
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                    // dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('consignee_city',$cityId)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('consignee_city',$cityId)->where('order_status', 2)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$cityId)->where('delayed_status', 0)->where('order_status', 3)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$cityId)->where('delayed_status', 1)->where('order_status', 3)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$cityId)->where('order_status', 4)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$cityId)->where('order_status', 5)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 9)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 17)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 18)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->where('consignee_city',$cityId)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
    
                $vendorRequest = 'any';
                $cityRequest = $request->city;

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }
    
            if ($from && $to && $request->vendor <> 'any' && $request->city == 'any') {
    
                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('vendor_id', $vendorId)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->where('vendor_id', $vendorId)->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status',6)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');
                $vendor_parcel_commission = 0;
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->sum('consignment_cod_price');
                $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                    // dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('order_status', 2)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 0)->where('order_status', 3)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 1)->where('order_status', 3)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 4)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 5)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 9)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 17)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 18)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
    
                $vendorRequest = $request->vendor;
                $cityRequest = 'any';

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }

            if ($from && $to && $request->vendor == 'any' && $request->city == 'any') {
    
                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status',6)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');
                $vendor_parcel_commission = 0;
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->sum('consignment_cod_price');
                $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                    // dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('order_status', 2)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 0)->where('order_status', 3)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 1)->where('order_status', 3)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 4)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 5)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 9)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 17)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 18)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
    
                $vendorRequest = 'any';
                $cityRequest = 'any';

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }

            if(!empty($request->vendor) && !empty($request->city))
            {
                if ($request->vendor <> 'any' && $request->city <> 'any') {

                    //parcel count
                    $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $delivered_parcel = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $pending = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,11,12,13,14])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    
                    //parcel amount
                    $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $delivered_sum = Order::where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $pending_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cancelled_sum = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    
                    //Expected Amounts                
                    $expected_order_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $expected_order_delivered_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                    $expected_order_remaining_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');

                    $cash_collected = RiderCashCollection::all()->sum('amount');
                    $in_cash_collected = RiderCashCollection::all()->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::all()->sum('ibft_collection');
                    $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $vendor_parcel_commission = 0;
                    $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
                    $final_cash_paid_to_vendor = ($vendor_payable - $vendor_parcel_commission) - $cash_paid_to_vendor;
                        // dd($cash_collected);
                    
                    //COD Parcels amount
                    $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    
                    //parcel statuses
                    $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $pickup = Order::where('parcel_nature',1)->where('order_status', 2)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 0)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $delayed_warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 1)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $check_by_supervisor = Order::where('parcel_nature',1)->where('order_status', 4)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $dispatched = Order::where('parcel_nature',1)->where('order_status', 5)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $delivered = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $cancelled = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
                    $cancel_by_rider = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 17)->where('vendor_id', $vendorId)->count();
                    $cancel_by_supervisor = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 18)->where('vendor_id', $vendorId)->count();
                    $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
        
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;

                    $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('vendor_weight_price');
                    $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('vendor_fuel_price');

                    $commission_value = $comm_value + $fuel_value;

                    $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
                    $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

                    $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                    $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                    if($t_dispatch > 0)
                    {
                        $d_ratio = ($t_delivered / $t_dispatch) * 100;
                        $delivery_ratio = number_format($d_ratio);
                    }
                    else
                    {
                        $delivery_ratio = 0;
                    }

                    return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                    'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
                }
            }

            if(!empty($request->city))
            {
                if ($request->vendor == 'any' && $request->city <> 'any') {
    
                    //parcel count
                    $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('consignee_city',$cityId)->count();
                    $delivered_parcel = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->count();
                    $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->count();
                    $pending = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                    $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->count();
                    $allCancelledParcel = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->count();
                    
                    //parcel amount
                    $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $delivered_sum = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                    $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $pending_sum = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                    $cancelled_sum = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                    
                    //Expected Amounts                
                    $expected_order_amount = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $expected_order_delivered_amount = Order::where('parcel_nature',1)->where('order_status',6)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $expected_order_remaining_amount = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cash_collected = RiderCashCollection::all()->sum('amount');
                    $in_cash_collected = RiderCashCollection::all()->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::all()->sum('ibft_collection');
                    $vendor_parcel_commission = 0;
                    $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                    $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                        // dd($cash_collected);
                    
                    //COD Parcels amount
                    $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    
                    //parcel statuses
                    $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('consignee_city',$cityId)->count();
                    $pickup = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 2)->count();
                    $warehouse = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('delayed_status', 0)->where('order_status', 3)->count();
                    $delayed_warehouse = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('delayed_status', 1)->where('order_status', 3)->count();
                    $check_by_supervisor = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 4)->count();
                    $dispatched = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 5)->count();
                    $delivered = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->count();
                    $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->count();
                    $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->count();
                    $cancelled = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 9)->count();
                    $cancel_by_rider = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 17)->count();
                    $cancel_by_supervisor = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 18)->count();
                    $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->count();
                    $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->count();
                    $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->count();
                    $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->count();
                    $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->count();
                    $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->count();
                    $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->where('consignee_city',$cityId)->count();
                    $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->count();
        
                    $vendorRequest = 'any';
                    $cityRequest = $request->city;

                    $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('consignee_city',$cityId)->sum('vendor_weight_price');
                    $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('consignee_city',$cityId)->sum('vendor_fuel_price');

                    $commission_value = $comm_value + $fuel_value;

                    $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
                    $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

                    $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                    $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                    if($t_dispatch > 0)
                    {
                        $d_ratio = ($t_delivered / $t_dispatch) * 100;
                        $delivery_ratio = number_format($d_ratio);
                    }
                    else
                    {
                        $delivery_ratio = 0;
                    }

                    return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                    'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
                }
            }

            if(!empty($request->vendor))
            {
                if ($request->vendor <> 'any' && $request->city == 'any') {
    
                    //parcel count
                    $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->count();
                    $delivered_parcel = OrderAssigned::where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $returntovendor_parcel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 10)->count();
                    $pending = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                    $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->where('vendor_id', $vendorId)->count();
                    $allCancelledParcel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('order_status', [9,11,12])->count();
                    
                    //parcel amount
                    $overall_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereNotIn('order_status', [11,12,13,14])->sum('consignment_cod_price');
                    $delivered_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status',6)->sum('consignment_cod_price');
                    $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 10)->sum('consignment_cod_price');
                    $pending_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                    $cancelled_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                    
                    //Expected Amounts                
                    $expected_order_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                    $expected_order_delivered_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status',6)->sum('consignment_cod_price');
                    $expected_order_remaining_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                    $cash_collected = RiderCashCollection::all()->sum('amount');
                    $in_cash_collected = RiderCashCollection::all()->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::all()->sum('ibft_collection');
                    $vendor_parcel_commission = 0;
                    $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                    $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
                    $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                        // dd($cash_collected);
                    
                    //COD Parcels amount
                    $cod_parcel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
                    $cod_parcel_ahl = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
                    
                    //parcel statuses
                    $awaiting = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 1)->count();
                    $pickup = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 2)->count();
                    $warehouse = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('delayed_status', 0)->where('order_status', 3)->count();
                    $delayed_warehouse = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('delayed_status', 1)->where('order_status', 3)->count();
                    $check_by_supervisor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 4)->count();
                    $dispatched = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 5)->count();
                    $delivered = OrderAssigned::where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $requestforreattempt = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 7)->count();
                    $reattempt = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 8)->count();
                    $cancelled = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 9)->count();
                    $cancel_by_rider = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 17)->count();
                    $cancel_by_supervisor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 18)->count();
                    $returntovendor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 10)->count();
                    $returntovendorinprogress = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 19)->count();
                    $cancelbyadmin = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 11)->count();
                    $cancelbyvendor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 12)->count();
                    $voidlabel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 13)->count();
                    $replace = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 14)->count();
                    $enroute= Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 15)->count();
                    $riderreattempt = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 16)->count();
        
                    $vendorRequest = $request->vendor;
                    $cityRequest = 'any';

                    $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->sum('vendor_weight_price');
                    $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->sum('vendor_fuel_price');

                    $commission_value = $comm_value + $fuel_value;

                    $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
                    $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

                    $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                    $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                    if($t_dispatch > 0)
                    {
                        $d_ratio = ($t_delivered / $t_dispatch) * 100;
                        $delivery_ratio = number_format($d_ratio);
                    }
                    else
                    {
                        $delivery_ratio = 0;
                    }

                    return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                    'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
                }
            }
            
            //parcel count
            $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->count();
            $delivered_parcel = Order::where('parcel_nature',1)->where('order_status',6)->count();
            $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->count();
            $pending = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
            $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->count();
            $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->count();
                
            //parcel amount
            $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->sum('consignment_cod_price');
            $delivered_sum = Order::where('parcel_nature',1)->where('order_status',6)->sum('consignment_cod_price');
            $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->sum('consignment_cod_price');
            $pending_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
            $cancelled_sum = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
            
            //Expected Amounts
            $expected_amount = OrderAssigned::whereDate('created_at',now())->pluck('order_id');
            $expected_delivered_amount = OrderAssigned::whereDate('created_at',now())->where('trip_status_id',4)->where('status',1)->pluck('order_id');
            $expected_remaining_amount = OrderAssigned::whereDate('created_at',now())->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
            $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->sum('consignment_cod_price');
            $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('order_status',6)->sum('consignment_cod_price');
            $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->sum('consignment_cod_price');
            $cash_collected = RiderCashCollection::whereDate('created_at', now())->sum('amount');
            $in_cash_collected = RiderCashCollection::whereDate('created_at',now())->sum('in_cash_collection');
            $ibft_collected = RiderCashCollection::whereDate('created_at',now())->sum('ibft_collection');
            $vendor_parcel_commission = 0;
            $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->sum('consignment_cod_price');
            $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
            $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                // dd($cash_collected);
            
            //COD Parcels amount
            $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
            $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
            
            //parcel statuses
            $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->count();
            $pickup = Order::where('parcel_nature',1)->where('order_status', 2)->count();
            $warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 0)->count();
            $delayed_warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 1)->count();
            $check_by_supervisor = Order::where('parcel_nature',1)->where('order_status', 4)->count();
            $dispatched = Order::where('parcel_nature',1)->where('order_status', 5)->count();
            $delivered = Order::where('parcel_nature',1)->where('order_status', 6)->count();
            $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->count();
            $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->count();
            $cancelled = Order::where('parcel_nature',1)->where('order_status', 9)->count();
            $cancel_by_rider = Order::where('parcel_nature',1)->where('order_status', 17)->count();
            $cancel_by_supervisor = Order::where('parcel_nature',1)->where('order_status', 18)->count();
            $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->count();
            $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->count();
            $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->count();
            $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->count();
            $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->count();
            $replace = Order::where('parcel_nature',1)->where('order_status', 14)->count();
            $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->count();
            $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->count();
    
            $vendorRequest = 'any';
            $cityRequest = 'any';

            $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->sum('vendor_weight_price');
            $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->sum('vendor_fuel_price');

            $commission_value = $comm_value + $fuel_value;

            $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
            $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

            $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
            $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

            if($t_dispatch > 0)
            {
                $d_ratio = ($t_delivered / $t_dispatch) * 100;
                $delivery_ratio = number_format($d_ratio);
            }
            else
            {
                $delivery_ratio = 0;
            }

            return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
            'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
            'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
            'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','head_of_account','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','lead_supervisor','data_analyst'))
        {
            $vendorId = $request->vendor;
            $cityId = $request->city;
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if ($from && $to && $request->vendor <> 'any' && $request->city <> 'any') {

                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status',6)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                $vendor_parcel_commission = Helper::ahlCommissionParcelSumNew($vendorId);
                $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
                $final_cash_paid_to_vendor = ($vendor_payable - $vendor_parcel_commission) - $cash_paid_to_vendor;
                    // dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('consignee_city',$cityId)->where('order_status', 2)->where('vendor_id', $vendorId)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$cityId)->where('order_status', 3)->where('vendor_id', $vendorId)->where('delayed_status', 0)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$cityId)->where('order_status', 3)->where('vendor_id', $vendorId)->where('delayed_status', 1)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$cityId)->where('order_status', 4)->where('vendor_id', $vendorId)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$cityId)->where('order_status', 5)->where('vendor_id', $vendorId)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->where('vendor_id', $vendorId)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 17)->where('vendor_id', $vendorId)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 18)->where('vendor_id', $vendorId)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->whereIn('consignee_city',$usercity)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
    
                $vendorRequest = $request->vendor;
                $cityRequest = $request->city;

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }
            
            if ($from && $to && $request->vendor == 'any' && $request->city <> 'any') {
    
                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('consignee_city',$cityId)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');
                $vendor_parcel_commission = 0;
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                    // dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('consignee_city',$cityId)->where('order_status', 2)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$cityId)->where('order_status', 3)->where('delayed_status', 0)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$cityId)->where('order_status', 3)->where('delayed_status', 1)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$cityId)->where('order_status', 4)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$cityId)->where('order_status', 5)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereHas('orderDetail',function($query) use($cityId){
                    $query->where('consignee_city',$cityId);
                })->where('trip_status_id',4)->where('status',1)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 9)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 17)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('consignee_city',$cityId)->where('order_status', 18)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->whereIn('consignee_city',$usercity)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();

                $vendorRequest = 'any';
                $cityRequest = $request->city;

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }
    
            if ($from && $to && $request->vendor <> 'any' && $request->city == 'any') {
    
                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,11,12,13,14])->where('vendor_id', $vendorId)->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->where('vendor_id', $vendorId)->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status',6)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');                
                $vendor_parcel_commission = 0;
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->sum('consignment_cod_price');
                $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                        //   dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('order_status', 2)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 0)->where('order_status', 3)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 1)->where('order_status', 3)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 4)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 5)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 9)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 17)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 18)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->whereIn('consignee_city',$usercity)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();

                $vendorRequest = $request->vendor;
                $cityRequest = 'any';

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }

            if ($from && $to && $request->vendor == 'any' && $request->city == 'any') {
    
                //parcel count
                $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('consignee_city',$usercity)->count();
                $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $delivered_parcel = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->count();
                $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $pending = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                $pending_new = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [3,8])->count();
                $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->count();
                
                //parcel amount
                $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('consignment_cod_price');
                $delivered_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status',6)->sum('consignment_cod_price');
                $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
                $pending_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                $cancelled_sum = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                
                //Expected Amounts
                $expected_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $expected_delivered_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->pluck('order_id');
                $expected_remaining_amount = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
                $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->sum('consignment_cod_price');
                $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->where('order_status',6)->sum('consignment_cod_price');
                $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->sum('consignment_cod_price');
                $cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('amount');
                $in_cash_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('in_cash_collection');
                $ibft_collected = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->sum('ibft_collection');                
                $vendor_parcel_commission = 0;
                $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->sum('consignment_cod_price');
                $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                        //   dd($cash_collected);
                
                //COD Parcels amount
                $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
                $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
                
                //parcel statuses
                $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
                $pickup = Order::where('parcel_nature',1)->whereIn('id', $pickup_scan_order)->where('order_status', 2)->count();
                $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
                $warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 0)->where('order_status', 3)->count();
                $delayed_warehouse = Order::where('parcel_nature',1)->whereIn('id', $warehouse_scan_order)->where('delayed_status', 1)->where('order_status', 3)->count();
                $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
                $check_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 4)->count();
                $dispatched = Order::where('parcel_nature',1)->whereIn('id', $dispatch_scan_order)->where('order_status', 5)->count();
                $delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('trip_status_id',4)->where('status',1)->count();
                $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelled = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 9)->count();
                $cancel_by_rider = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 17)->count();
                $cancel_by_supervisor = Order::where('parcel_nature',1)->whereIn('id', $assigned_parcels)->where('order_status', 18)->count();
                $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
                $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $replace = Order::where('parcel_nature',1)->where('order_status', 14)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
                $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->whereIn('consignee_city',$usercity)->count();
                $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();

                $vendorRequest = 'any';
                $cityRequest = 'any';

                $parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 4)->where('status',1)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $orders_assigned = $collapsed->all();

                $comm_value = Order::whereIn('id', $orders_assigned)->sum('vendor_weight_price');
                $fuel_value = Order::whereIn('id', $orders_assigned)->sum('vendor_fuel_price');

                $commission_value = $comm_value + $fuel_value;

                $rider_count = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');
                $cash_count = RiderCashCollection::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->groupBy('rider_id')->pluck('rider_id');

                $t_dispatch = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                $t_delivered = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                if($t_dispatch > 0)
                {
                    $d_ratio = ($t_delivered / $t_dispatch) * 100;
                    $delivery_ratio = number_format($d_ratio);
                }
                else
                {
                    $delivery_ratio = 0;
                }

                return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
            }

            if(!empty($request->vendor) && !empty($request->city))
            {
                if ($request->vendor <> 'any' && $request->city <> 'any') {

                    //parcel count
                    $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $delivered_parcel = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $pending = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,11,12,13,14])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    
                    //parcel amount
                    $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $delivered_sum = Order::where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $pending_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cancelled_sum = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    
                    //Expected Amounts                
                    $expected_order_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $expected_order_delivered_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                    $expected_order_remaining_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');

                    $cash_collected = RiderCashCollection::all()->sum('amount');
                    $in_cash_collected = RiderCashCollection::all()->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::all()->sum('ibft_collection');
                    $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $vendor_parcel_commission = 0;
                    $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
                    $final_cash_paid_to_vendor = ($vendor_payable - $vendor_parcel_commission) - $cash_paid_to_vendor;
                        // dd($cash_collected);
                    
                    //COD Parcels amount
                    $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    
                    //parcel statuses
                    $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $pickup = Order::where('parcel_nature',1)->where('order_status', 2)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 0)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $delayed_warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 1)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $check_by_supervisor = Order::where('parcel_nature',1)->where('order_status', 4)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $dispatched = Order::where('parcel_nature',1)->where('order_status', 5)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $delivered = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $cancelled = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
                    $cancel_by_rider = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 17)->where('vendor_id', $vendorId)->count();
                    $cancel_by_supervisor = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 18)->where('vendor_id', $vendorId)->count();
                    $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
                    $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->count();
                    $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->where('vendor_id', $vendorId)->count();
        
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;

                    $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('vendor_weight_price');
                    $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->where('consignee_city',$cityId)->sum('vendor_fuel_price');

                    $commission_value = $comm_value + $fuel_value;

                    $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
                    $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

                    $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                    $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                    if($t_dispatch > 0)
                    {
                        $d_ratio = ($t_delivered / $t_dispatch) * 100;
                        $delivery_ratio = number_format($d_ratio);
                    }
                    else
                    {
                        $delivery_ratio = 0;
                    }

                    return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                    'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
                }
            }
            
            if(!empty($request->city))
            {
                if ($request->vendor == 'any' && $request->city <> 'any') {
        
                    //parcel count
                    $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('consignee_city',$cityId)->count();
                    $delivered_parcel = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->count();
                    $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->count();
                    $pending = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,11,12,13,14])->count();
                    $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->where('consignee_city',$cityId)->count();
                    $allCancelledParcel = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->count();
                    
                    //parcel amount
                    $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $delivered_sum = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status',6)->sum('consignment_cod_price');
                    $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $pending_sum = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                    $cancelled_sum = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                    
                    //Expected Amounts                
                    $expected_order_amount = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $expected_order_delivered_amount = Order::where('parcel_nature',1)->where('order_status',6)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $expected_order_remaining_amount = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cash_collected = RiderCashCollection::all()->sum('amount');
                    $in_cash_collected = RiderCashCollection::all()->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::all()->sum('ibft_collection');
                    $vendor_parcel_commission = 0;
                    $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
                    $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                        // dd($cash_collected);
                    
                    //COD Parcels amount
                    $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->where('consignee_city',$cityId)->sum('consignment_cod_price');
                    
                    //parcel statuses
                    $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->where('consignee_city',$cityId)->count();
                    $pickup = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 2)->count();
                    $warehouse = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('delayed_status', 0)->where('order_status', 3)->count();
                    $delayed_warehouse = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('delayed_status', 1)->where('order_status', 3)->count();
                    $check_by_supervisor = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 4)->count();
                    $dispatched = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 5)->count();
                    $delivered = OrderAssigned::whereHas('orderDetail',function($query) use($cityId){
                        $query->where('consignee_city',$cityId);
                    })->where('trip_status_id',4)->where('status',1)->count();
                    $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->where('consignee_city',$cityId)->count();
                    $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->where('consignee_city',$cityId)->count();
                    $cancelled = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 9)->count();
                    $cancel_by_rider = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 17)->count();
                    $cancel_by_supervisor = Order::where('parcel_nature',1)->where('consignee_city',$cityId)->where('order_status', 18)->count();
                    $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->where('consignee_city',$cityId)->count();
                    $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->where('consignee_city',$cityId)->count();
                    $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->where('consignee_city',$cityId)->count();
                    $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->where('consignee_city',$cityId)->count();
                    $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->where('consignee_city',$cityId)->count();
                    $replace = Order::where('parcel_nature',1)->where('order_status', 14)->where('consignee_city',$cityId)->count();
                    $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->where('consignee_city',$cityId)->count();
                    $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->where('consignee_city',$cityId)->count();
        
                    $vendorRequest = 'any';
                    $cityRequest = $request->city;

                    $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('consignee_city',$cityId)->sum('vendor_weight_price');
                    $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('consignee_city',$cityId)->sum('vendor_fuel_price');

                    $commission_value = $comm_value + $fuel_value;

                    $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
                    $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

                    $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                    $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                    if($t_dispatch > 0)
                    {
                        $d_ratio = ($t_delivered / $t_dispatch) * 100;
                        $delivery_ratio = number_format($d_ratio);
                    }
                    else
                    {
                        $delivery_ratio = 0;
                    }
                    return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                    'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
                }
            }

            if(!empty($request->vendor))
            {
                if ($request->vendor <> 'any' && $request->city == 'any') {
        
                    //parcel count
                    $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->count();
                    $delivered_parcel = OrderAssigned::where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->count();
                    $returntovendor_parcel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status', 10)->whereIn('consignee_city',$usercity)->count();
                    $pending = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereNotIn('order_status', [1,6,10,11,12,13,14])->whereIn('consignee_city',$usercity)->count();
                    $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->whereIn('consignee_city',$usercity)->where('vendor_id', $vendorId)->count();
                    $allCancelledParcel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('order_status', [9,11,12])->whereIn('consignee_city',$usercity)->count();
                    
                    //parcel amount
                    $overall_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereNotIn('order_status', [11,12,13,14])->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
                    $delivered_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status',6)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
                    $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 10)->sum('consignment_cod_price');
                    $pending_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->sum('consignment_cod_price');
                    $cancelled_sum = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->whereIn('order_status', [9,11,12])->sum('consignment_cod_price');
                    
                    //Expected Amounts                
                    $expected_order_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
                    $expected_order_delivered_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->where('order_status',6)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
                    $expected_order_remaining_amount = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
                    $cash_collected = RiderCashCollection::all()->sum('amount');
                    $in_cash_collected = RiderCashCollection::all()->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::all()->sum('ibft_collection');
                    $vendor_parcel_commission = 0;
                    $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
                    $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
                    $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                        // dd($cash_collected);
                    
                    //COD Parcels amount
                    $cod_parcel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->whereIn('order_status', [2,3,5,16,17,8,9,19])->where('consignment_order_type',1)->sum('consignment_cod_price');
                    $cod_parcel_ahl = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->whereIn('order_status', [8,3])->where('consignment_order_type',1)->sum('consignment_cod_price');
                    
                    //parcel statuses
                    $awaiting = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 1)->count();
                    $pickup = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 2)->count();
                    $warehouse = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('delayed_status', 0)->where('order_status', 3)->count();
                    $delayed_warehouse = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('delayed_status', 1)->where('order_status', 3)->count();
                    $check_by_supervisor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 4)->count();
                    $dispatched = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 5)->count();
                    $delivered = OrderAssigned::where('trip_status_id',4)->where('status',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->count();
                    $requestforreattempt = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 7)->count();
                    $reattempt = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 8)->count();
                    $cancelled = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 9)->count();
                    $cancel_by_rider = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 17)->count();
                    $cancel_by_supervisor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 18)->count();
                    $returntovendor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 10)->count();
                    $returntovendorinprogress = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 19)->count();
                    $cancelbyadmin = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 11)->count();
                    $cancelbyvendor = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 12)->count();
                    $voidlabel = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 13)->count();
                    $replace = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 14)->count();
                    $enroute= Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 15)->count();
                    $riderreattempt = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->where('order_status', 16)->count();
        
                    $vendorRequest = $request->vendor;
                    $cityRequest = 'any';

                    $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->sum('vendor_weight_price');
                    $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->where('vendor_id', $vendorId)->whereIn('consignee_city',$usercity)->sum('vendor_fuel_price');

                    $commission_value = $comm_value + $fuel_value;

                    $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
                    $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

                    $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
                    $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

                    if($t_dispatch > 0)
                    {
                        $d_ratio = ($t_delivered / $t_dispatch) * 100;
                        $delivery_ratio = number_format($d_ratio);
                    }
                    else
                    {
                        $delivery_ratio = 0;
                    }

                    return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
                    'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
                }
            }
            
            //parcel count
            $total_parcel = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereIn('consignee_city',$usercity)->count();
            $delivered_parcel = Order::where('parcel_nature',1)->where('order_status',6)->whereIn('consignee_city',$usercity)->count();
            $returntovendor_parcel = Order::where('parcel_nature',1)->where('order_status', 10)->whereIn('consignee_city',$usercity)->count();
            $pending = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,11,12,13,14])->whereIn('consignee_city',$usercity)->count();
            $pending_new = Order::where('parcel_nature',1)->whereIn('order_status', [3,8])->whereIn('consignee_city',$usercity)->count();
            $allCancelledParcel = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->whereIn('consignee_city',$usercity)->count();
                
            //parcel amount
            $overall_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [11,12,13,14])->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $delivered_sum = Order::where('parcel_nature',1)->where('order_status',6)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $returntovendor_parcel_sum = Order::where('parcel_nature',1)->where('order_status', 10)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $pending_sum = Order::where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $cancelled_sum = Order::where('parcel_nature',1)->whereIn('order_status', [9,11,12])->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            
            //Expected Amounts
            $expected_amount = OrderAssigned::whereDate('created_at',now())->pluck('order_id');
            $expected_delivered_amount = OrderAssigned::whereDate('created_at',now())->where('trip_status_id',4)->where('status',1)->pluck('order_id');
            $expected_remaining_amount = OrderAssigned::whereDate('created_at',now())->where(['status'=>1])->whereIn('trip_status_id',[1,2,3])->pluck('order_id');
                
            $expected_order_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_amount)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $expected_order_delivered_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_delivered_amount)->whereIn('consignee_city',$usercity)->where('order_status',6)->sum('consignment_cod_price');
            $expected_order_remaining_amount = Order::where('parcel_nature',1)->whereIn('id', $expected_remaining_amount)->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $cash_collected = RiderCashCollection::whereDate('created_at', now())->sum('amount');
            $in_cash_collected = RiderCashCollection::whereDate('created_at', now())->sum('in_cash_collection');
                    $ibft_collected = RiderCashCollection::whereDate('created_at', now())->sum('ibft_collection');
            $vendor_parcel_commission = 0;
            $vendor_payable = Order::where('parcel_nature',1)->whereIn('order_status',[6])->whereIn('consignee_city',$usercity)->sum('consignment_cod_price');
            $cash_paid_to_vendor = VendorFinancial::all()->sum('amount');
            $final_cash_paid_to_vendor = $vendor_payable - $cash_paid_to_vendor;
                // dd($cash_collected);
            
            //COD Parcels amount
            $cod_parcel = Order::where('parcel_nature',1)->whereIn('order_status', [2,3,5,16,17,8,9,19])->whereIn('consignee_city',$usercity)->where('consignment_order_type',1)->sum('consignment_cod_price');
            $cod_parcel_ahl = Order::where('parcel_nature',1)->whereIn('order_status', [8,3])->whereIn('consignee_city',$usercity)->where('consignment_order_type',1)->sum('consignment_cod_price');
            
            //parcel statuses
            $awaiting = Order::where('parcel_nature',1)->where('order_status', 1)->whereIn('consignee_city',$usercity)->count();
            $pickup = Order::where('parcel_nature',1)->where('order_status', 2)->whereIn('consignee_city',$usercity)->count();
            $warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 0)->whereIn('consignee_city',$usercity)->count();
            $delayed_warehouse = Order::where('parcel_nature',1)->where('order_status', 3)->where('delayed_status', 1)->whereIn('consignee_city',$usercity)->count();
            $check_by_supervisor = Order::where('parcel_nature',1)->where('order_status', 4)->whereIn('consignee_city',$usercity)->count();
            $dispatched = Order::where('parcel_nature',1)->where('order_status', 5)->whereIn('consignee_city',$usercity)->count();
            $delivered = Order::where('parcel_nature',1)->where('order_status', 6)->whereIn('consignee_city',$usercity)->count();
            $requestforreattempt = Order::where('parcel_nature',1)->where('order_status', 7)->whereIn('consignee_city',$usercity)->count();
            $reattempt = Order::where('parcel_nature',1)->where('order_status', 8)->whereIn('consignee_city',$usercity)->count();
            $cancelled = Order::where('parcel_nature',1)->where('order_status', 9)->whereIn('consignee_city',$usercity)->count();
            $cancel_by_rider = Order::where('parcel_nature',1)->where('order_status', 17)->whereIn('consignee_city',$usercity)->count();
            $cancel_by_supervisor = Order::where('parcel_nature',1)->where('order_status', 18)->whereIn('consignee_city',$usercity)->count();
            $returntovendor = Order::where('parcel_nature',1)->where('order_status', 10)->whereIn('consignee_city',$usercity)->count();
            $returntovendorinprogress = Order::where('parcel_nature',1)->where('order_status', 19)->whereIn('consignee_city',$usercity)->count();
            $cancelbyadmin = Order::where('parcel_nature',1)->where('order_status', 11)->whereIn('consignee_city',$usercity)->count();
            $cancelbyvendor = Order::where('parcel_nature',1)->where('order_status', 12)->whereIn('consignee_city',$usercity)->count();
            $voidlabel = Order::where('parcel_nature',1)->where('order_status', 13)->whereIn('consignee_city',$usercity)->count();
            $replace = Order::where('parcel_nature',1)->where('order_status', 14)->whereIn('consignee_city',$usercity)->count();
            $enroute= Order::where('parcel_nature',1)->where('order_status', 15)->whereIn('consignee_city',$usercity)->count();
            $riderreattempt = Order::where('parcel_nature',1)->where('order_status', 16)->whereIn('consignee_city',$usercity)->count();
    
            $vendorRequest = 'any';
            $cityRequest = 'any';

            $comm_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->whereIn('consignee_city',$usercity)->sum('vendor_weight_price');
            $fuel_value = Order::where('parcel_nature',1)->whereIn('order_status', [6,9,10])->whereIn('consignee_city',$usercity)->sum('vendor_fuel_price');

            $commission_value = $comm_value + $fuel_value;

            $rider_count = OrderAssigned::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');
            $cash_count = RiderCashCollection::whereDate('created_at', now())->groupBy('rider_id')->pluck('rider_id');

            $t_dispatch = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [1,2,3,4,5,6,7])->where('force_status',1)->count();
            $t_delivered = OrderAssigned::whereDate('created_at', now())->whereIn('trip_status_id', [4])->where('status',1)->where('force_status',1)->count();

            if($t_dispatch > 0)
            {
                $d_ratio = ($t_delivered / $t_dispatch) * 100;
                $delivery_ratio = number_format($d_ratio);
            }
            else
            {
                $delivery_ratio = 0;
            }

            return view('admin/admin_dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','pending_new','allCancelledParcel','overall_sum','delivered_sum',
            'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
            'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','vendorRequest','vendors','expected_order_amount','expected_order_delivered_amount',
            'expected_order_remaining_amount','cash_collected','in_cash_collected','ibft_collected','vendor_parcel_commission','final_cash_paid_to_vendor','replace','cod_parcel','cod_parcel_ahl','cities','cityRequest','enroute','delayed_warehouse','riderreattempt','check_by_supervisor','cancel_by_rider','cancel_by_supervisor','commission_value','rider_count','cash_count','delivery_ratio'));
        }
        
        
    }

    public function currentStatusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $orders = Order::where('parcel_nature',1)->where('vendor_id', $authUserVendorId)->whereNotIn('order_status',[6,10])->with([
                'vendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'orderType' => function($query){
                    $query->select('id','name');
                },
                'orderPacking' => function($query){
                    $query->select('id','name');
                },
                'orderStatus' => function($query){
                    $query->select('id','name');
                },
                'vendorWeight' => function($query){
                    $query->with([
                        'ahlWeight' => function($query){
                            $query->select('id','weight');
                        },
                        'city' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                }
            ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('parcel_nature',1)->whereNotIn('order_status',[6,10])->with([
                'vendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'orderType' => function($query){
                    $query->select('id','name');
                },
                'orderPacking' => function($query){
                    $query->select('id','name');
                },
                'orderStatus' => function($query){
                    $query->select('id','name');
                },
                'vendorWeight' => function($query){
                    $query->with([
                        'ahlWeight' => function($query){
                            $query->select('id','weight');
                        },
                        'city' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                }
            ])->orderBy('id','DESC')->get();
        }

        return view('current-status-report',compact('orders','breadcrumbs'));
    }

    public function statusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' =>$authUserVendorId];
        }else{
            $condition = ['order_status'=>$orderStatusId];
        }

        if($orderStatusId){
            $orders = Order::where('parcel_nature',1)->where($condition)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('id',0)->get();
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled'));
    }

    public function warehouseStatusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' =>$authUserVendorId];
        }else{
            $condition = ['order_status'=>$orderStatusId];
        }

        if($orderStatusId){
            $orders = Order::where('parcel_nature',1)->whereIn('order_status',[3,15])->where('vendor_id',$authUserVendorId)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            }
                        ]);
                    }
                ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('id',0)->get();
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled'));
    }
    
    public function deliveredStatusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' =>$authUserVendorId];
        }else{
            $condition = ['order_status'=>$orderStatusId];
        }

        if($orderStatusId){
            $orders = Order::where('parcel_nature',1)->where($condition)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('id',0)->get();
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('delivered-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled'));
    }
    
    public function cancelStatusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' =>$authUserVendorId];
        }else{
            $condition = ['order_status'=>$orderStatusId];
        }

        if($orderStatusId){
            $orders = Order::where('parcel_nature',1)->where($condition)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('id',0)->get();
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('cancel-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled'));
    }
    public function returnStatusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' =>$authUserVendorId];
        }else{
            $condition = ['order_status'=>$orderStatusId];
        }

        if($orderStatusId){
            $orders = Order::where('parcel_nature',1)->where($condition)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('id',0)->get();
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('return-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled'));
    }
    public function returnInProgressStatusReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Current Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        $authUser = Auth::user();

        if($authUser->isVendorAdmin() || $authUser->isVendorEditor()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' =>$authUserVendorId];
        }else{
            $condition = ['order_status'=>$orderStatusId];
        }

        if($orderStatusId){
            $orders = Order::where('parcel_nature',1)->where($condition)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                    'vendorWeight' => function($query){
                        $query->with([
                            'ahlWeight' => function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])->orderBy('id','DESC')->get();
        }else{
            $orders = Order::where('id',0)->get();
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('return-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled'));
    }
    public function statusReportData(Request $request)
    {
        $statuses = Status::all();
        $orderStatusId = $request->status;

        $authUser = Auth::user();
        //$authUserVendorId = $authUser->vendor_id;
        if($authUser->isVendorAdmin()){
            $authUserVendorId = $authUser->vendor_id;
            $condition = ['order_status'=>$orderStatusId,'vendor_id' => $authUserVendorId];
        }else{
            $authUserVendorId = null;
            $condition = ['order_status' => $orderStatusId];
        }


        /*if($authUserVendorId){
        }else{
        }*/

        //if($authUserVendorId){
            $orders = Order::where('parcel_nature',1)->where($condition)->with([
                'vendor' => function($query){
                    $query->select('id','vendor_name');
                },
                'orderType' => function($query){
                    $query->select('id','name');
                },
                'orderPacking' => function($query){
                    $query->select('id','name');
                },
                'orderStatus' => function($query){
                    $query->select('id','name');
                },
                'vendorWeight' => function($query){
                    $query->with([
                        'ahlWeight' => function($query){
                            $query->select('id','weight');
                        },
                        'city' => function($query){
                            $query->select('id','name');
                        },
                    ]);
                }
            ])->get();
        //}else{
            /*$orders = Order::where('order_status',$statusId)->with([
                'orderType' => function($query){
                    $query->select('id','name');
                },
                'orderPacking' => function($query){
                    $query->select('id','name');
                },
                'orderStatus' => function($query){
                    $query->select('id','name');
                },
            ])->get();*/
        //} 


        return response()->json([
            'status' => 'success',
            'parcels' => $orders,
        ]);

        //return view('status-report',compact('statuses'));
    }

    public function cancelBy(Request $request)
    {
        $parcelId = $request->paracel_id;
        
        $authUser = Auth::user();
        $order = Order::find($parcelId);

        if($authUser->isVendorAdmin()){
            $updateOrderStatus = 12;
            
        }elseif($authUser->isAdmin()){
            $updateOrderStatus = 11;
        }

        $order->update(['order_status' => $updateOrderStatus]);
        
        return response()->json([
            'status' => 1, 
        ]);
    }

    public function scanHistory(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Scan Parcel History', 
        ];

        $today_date = Carbon::now(+5)->format('Y-m-d');
        
        if(isset($request->date)){
            $requestDate = $request->date;
        }else{
            $requestDate = now();
        }

        if(isset($request->to)){
            $requestDateTo = $request->to;
        }else{
            $requestDateTo = now();
        }

        if(Auth::user()->hasAnyRole('admin'))
        {
            //overall & remaining
            $overall_parcel = Order::whereIn('order_status', [3,8,9])->count();

            //total scan
            $total_scan = ScanOrder::whereDate('middle_man_scan_date', '>=', $requestDate)->whereDate('middle_man_scan_date', '<=', $requestDateTo)->count();

            //total dispatch
            $total_dispatch = OrderAssigned::whereDate('created_at', '>=', $requestDate)->whereDate('created_at', '<=', $requestDateTo)->where('force_status',1)->count();

            //3 boxes
            $at_ahl = Order::whereIn('order_status', [3])->count();
            $reattempt = Order::whereIn('order_status', [8])->count();
            $cancel = Order::whereIn('order_status', [9])->count();
        }
        else
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId )->pluck('city_id');

            //overall & remaining
            $overall_parcel = Order::whereIn('order_status', [3,8,9])->whereIn('consignee_city',$userCity)->count();

            //total scan
            $total_scan = ScanOrder::whereDate('middle_man_scan_date', '>=', $requestDate)->whereDate('middle_man_scan_date', '<=', $requestDateTo)->count();

            //total dispatch
            $total_dispatch = OrderAssigned::whereDate('created_at', '>=', $requestDate)->whereDate('created_at', '<=', $requestDateTo)->where('force_status',1)->count();

            //3 boxes
            $at_ahl = Order::whereIn('order_status', [3])->whereIn('consignee_city',$userCity)->count();
            $reattempt = Order::whereIn('order_status', [8])->whereIn('consignee_city',$userCity)->count();
            $cancel = Order::whereIn('order_status', [9])->whereIn('consignee_city',$userCity)->count();
        }

        $scanOrders = [
            'overall_count' => $overall_parcel,
            'total_scan' => $total_scan,
            'total_dispatch' => $total_dispatch,
            'at_ahl' => $at_ahl,
            'reattempt' => $reattempt,
            'cancel' => $cancel,
        ];
        
        return view('scan-history',compact('breadcrumbs','scanOrders','today_date'));
    }

    public function searchParcel(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Track Your Parcel', 
        ];

        if(isset($request->order_reference)){
            $parcelReference = $request->order_reference;
            $order = Order::where('parcel_nature',1)->where('order_reference',$parcelReference)->first();
            
            if($order){
                $parcelId = $order->id;
                
                $orderDetail =  Order::where('parcel_nature',1)->where('id',$parcelId)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                ])->first();
                
                $scanOrder = ScanOrder::where('order_id',$parcelId)
                    ->with([
                        'scanByPicker' => function($query){
                            $query->select('id','name');
                        },

                        'scanByMiddleMan' => function($query){
                            $query->select('id','name');
                        },

                        'scanBySupervisor' => function($query){
                            $query->select('id','name');
                        },
                    ])
                ->first();

                $delivery = OrderAssigned::select('id','vendor_id',
                'order_id','rider_id','drop_off_location','latitude','longitude','trip_status_id','status')
                ->where(['order_id'=>$parcelId,'trip_status_id'=>4,'status'=>1])
                ->with([
                    'rider' => function($query){
                        $query->select('id','name')->with([
                            'userDetail' =>function($query){
                                    $query->select('id','phone');
                            }
                        ]);
                    },
                    'orderDelivery' => function($query){
                        $query->select('id','order_assigned_id','amount','consignee_relation_id','other_relation','receiver_name','cnic','comment','signature','location_picture')->with([
                            'consigneeRelation' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'orderDecline' => function($query){
                        $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                            'orderDeclineStatus' => function($query){
                                $query->select('id','name');
                            },
                            'orderDeclineReason' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->first();
        
                $decline = OrderAssigned::select('id','vendor_id',
                'order_id','rider_id','drop_off_location','latitude','longitude','trip_status_id','status')
                ->where(['order_id'=>$parcelId,'status'=>0])
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'orderDecline' => function($query){
                        $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                            'orderDeclineStatus' => function($query){
                                $query->select('id','name');
                            },
                            'orderDeclineReason' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
        
                $decline_dates = OrderAssigned::where('order_id', $parcelId)->where('status', 0)->whereIn('trip_status_id',[4,5,6])->get();
                $assigned_dates = OrderAssigned::where('order_id', $parcelId)->whereIn('trip_status_id',[4,5,6])->get();
                
                $rider_detail = OrderAssigned::where('order_id', $parcelId)->orderBy('id', 'DESC')->first();

                $get_sag_orders = OrderInSag::where('order_id', $parcelId)->get();
                
                return view('parcel-detail',compact('orderDetail','delivery','decline','scanOrder','breadcrumbs', 'decline_dates','assigned_dates', 'rider_detail','get_sag_orders'));
            }else{
                return view('no-track-order');
            }

        }else{
            return view('no-track-order');
        }
    }

    public function vendorTaxInvoice()
    {
        $breadcrumbs = [
            'name' => 'Vendor Tax Invoice', 
        ];

        $vendors = Vendor::select('id','vendor_name')->get();
        return view('admin.vendor-tax-report',compact('breadcrumbs','vendors'));
    }

    public function vendorTaxInvoiceDownload(Request $request)
    {
        $validatedData = $request->validate([
            'date_from' => 'required',
            'date_to' => 'required',
            'vendor_id' => 'required'
        ]);
        
        $requestDateFrom = $request->date_from;
        $requestDateTo = $request->date_to;
        $vendorId = $request->vendor_id;

        $vendor = Vendor::select('id','vendor_name','vendor_email','vendor_address','ntn','gst')->where('id',$vendorId)->get();

        $ordersIds = Order::where('parcel_nature',1)->select('id','order_status','updated_at','created_at','vendor_id')
            //->where(['order_status'=>6,'vendor_id'=>$vendorId])//order status must be 6
            ->whereIn('order_status',[6,10])//order status must be 6
            ->where(['vendor_id'=>$vendorId])//order status must be 6
            ->whereDate('updated_at','>=',$requestDateFrom)//created_at return must be updated_at
            ->whereDate('updated_at','<=',$requestDateTo)//created_at return must be updated_at
            ->get()
            ->pluck('id')
            ->toArray();
        
        $orders = Order::where('parcel_nature',1)->selectRaw('count(id) as count,vendor_weight_id,sum(consignment_cod_price) as total_parcel_amount')
            ->groupBy('vendor_weight_id')
            ->whereIn('id',$ordersIds)
            ->with([
                'vendorWeight' => function($query){
                    $query->select('id','price');
                }
            ])
            ->get()
            ->toArray();

        //dd($orders);
        
        $subTotal = 0;
        $totalParcelAmount = 0;
        if($orders){
            foreach($orders as $key => $order){
                $amount = $order['count'] * $order['vendor_weight']['price'];
                $invoiceData[] = [
                    'qty' => $order['count'],
                    'rate' => $order['vendor_weight']['price'],
                    'amount' => $amount,
                ];

                $subTotal = $amount + $subTotal;
                $totalParcelAmount = $order['total_parcel_amount'] + $totalParcelAmount;
            }
        }else{
            $invoiceData = [];
            $subTotal = 0;
        }
        
        $taxRate = $vendor[0]['gst'];
        $taxAmount = $subTotal*$taxRate/100;
        //$total=$price+$tax;
        //$calculatedTaxRate=(($total-$price)/$price)*100;      // 16
        
        $balanceDue = $taxAmount + (float) $subTotal;
        
        $invoiceTotal = [
            'subTotal' => $subTotal,
            'taxAmount' => $taxAmount,
            'balanceDue' => $balanceDue,
        ];

        //dd($subTotal);

        $financial = [
            'totalParcelAmount' => $totalParcelAmount,
        ];



        $path = 'logo/ahl_logo.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        
        $title = 'Vendor Invoice List';
        // share data to view
        $pdf = PDF::loadView('tax-invoice', compact('logo','vendor','invoiceData','invoiceTotal','taxRate','title','financial'));
        $fileName = date('m-d-y').'-'.$vendor[0]['vendor_name'].'-'.'invoice';
        // download PDF file with download method
        //return view()->share('middle_man.generate-reattempt-pdf',$orders);
        //return view('tax-invoice',compact('logo','vendor','invoiceData','invoiceTotal','taxRate','title'));
        return $pdf->download($fileName.'.pdf');
        
    }
    
    public function vendorDispatchSheet(Request $request)
    {
        $to = $request->to;
        // dd($to);
        $from = $request->from;
        $vendor_id = $request->vendor_id;
        $status = $request->status;
        
        $vendors = Vendor::where('status',1)->get();
        $statuses = Status::whereIn('id',[5,6,7,9,10,14])->get();
        $find_vendor = Vendor::find($vendor_id);
        if(empty($find_vendor)){
            $vendor_name = 'All Vendors';
        }else{
            $vendor_name = $find_vendor->vendor_name;
        }
        if($request->to && $request->from && $request->vendor_id && $status)
        {
            $fileName = 'Quality Service Report ('.$vendor_name.')';
            return Excel::download(new VendorDispatchReportExport($from,$to,$vendor_id,$status), $fileName.'.xlsx');
        }
        
        return view('vendor.vendor_dispatch', compact('vendors', 'to', 'from', 'vendor_id','statuses'));
    }
    
    public function vendorSideDispatchSheet(Request $request)
    {
        $to = $request->to;
        $from = $request->from;
        $status = $request->status;
        $vendor_id = Auth::user()->vendor_id;
        $find_vendor = Vendor::find($vendor_id);

        $statuses = Status::whereIn('id',[5,6,7,9,10,14])->get();
        if($request->to && $request->from)
        {
            $fileName = 'Quality Service Report ('.$find_vendor->vendor_name.')';
            return Excel::download(new VendorDispatchReportExport($from,$to,$vendor_id,$status), $fileName.'.xlsx');
        }
        
        return view('vendor.vendor_side_dispatch', compact('to', 'from', 'vendor_id','statuses'));
    }
    
    public function vendorParcelCount()
    {
        $today = \Carbon\Carbon::now();

        if(Auth::user()->hasAnyRole('admin'))
        {
            $vendors = Vendor::where('status',1)->with('awaitingParcel')->with([
                'awaitingTodayParcel' => function($query) use($today){
                    $query->whereDate('created_at', $today);
                },
            ])->get();
        }
        
        if(Auth::user()->hasAnyRole('middle_man','first_man','supervisor','cashier','head_of_account','vendor_admin','vendor_editor','picker','rider','financer','sales','bd','bdm','csr','hub_manager'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');

            $vendors = Vendor::where('status',1)->with([
                'awaitingParcel' => function($query) use( $usercity){
                    $query->whereIn('consignee_city', $usercity);
                },
                ])->with([
                'awaitingTodayParcel' => function($query) use($today, $usercity){
                    $query->whereDate('created_at', $today)->whereIn('consignee_city', $usercity);
                },
                ])->get();
        }
        // dd($vendors);
        return view('awaiting-parcel-count', compact('vendors'));
    }

    public function pickupParcelCount(Request $request)
    {
        if($request->date && $request->to)
        {
            $today = $request->date;
            $to = $request->to;
        }
        else
        {
            $today = \Carbon\Carbon::now();
            $to = \Carbon\Carbon::now();
        }

        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $authUser = Auth::user();

        if($authUser->isAdmin())
        {
            $vendors= User::whereHas(
                'roles', function($q){
                    $q->where('name', 'picker');
                }
                )->with(['scanOrder' => function($query) use($today, $to){
                    $query->whereDate('created_at','>=', $today)->whereDate('created_at','<=', $to);
                },
            ])->get();
        }
        else
        {
            $vendors= User::whereHas(
                'roles', function($q){
                    $q->where('name', 'picker');
                }
                )->whereHas('usercity',function($query) use($usercity){
                    $query->whereIn('city_id',$usercity);
                }
                )->with(['scanOrder' => function($query) use($today, $to){
                    $query->whereDate('created_at','>=', $today)->whereDate('created_at','<=', $to);
                },
            ])->get();
        }
        
        return view('pickup-parcel-count', compact('vendors'));
    }

    public function pickupVendorParcelCount(Request $request)
    {
        if($request->date && $request->to)
        {
            $today = \Carbon\Carbon::parse($request->date)->format('Y-m-d H:i:s');
            $to = \Carbon\Carbon::parse($request->to)->format('Y-m-d H:i:s');
        }
        else
        {
            $today = \Carbon\Carbon::now();
            $to = \Carbon\Carbon::now();
        }

        $vendor_details = DB::table('vendors')
            ->join('orders', 'orders.vendor_id', 'vendors.id')
            ->join('scan_orders', 'scan_orders.order_id', 'orders.id')
            ->where('scan_orders.created_at','>=', $today)->where('scan_orders.created_at', '<=',$to)
            ->select(
                DB::raw('vendors.vendor_name as name'),
                DB::raw('count(scan_orders.id) as total'),
                DB::raw('vendors.id as vendor_id')
            )
            ->groupBy(DB::raw('name'))->orderBy('total', 'DESC')->get();

        $overall_count = DB::table('vendors')
            ->join('orders', 'orders.vendor_id', 'vendors.id')
            ->join('scan_orders', 'scan_orders.order_id', 'orders.id')
            ->where('scan_orders.created_at','>=', $today)->where('scan_orders.created_at', '<=',$to)
            ->count();
        
        return view('pickup-vendor-parcel-count', compact('vendor_details','overall_count'));
    }

    public function searchOrderView()
    {
        return view('search-order');
    }

    public function searchParcelOrder(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Track Your Parcel', 
        ];

        if(isset($request->order_id)){
            $parcelReference = $request->order_id;
            $order = Order::where('parcel_nature',1)->where('consignment_order_id',$parcelReference)->first();
            
            if($order){
                $parcelId = $order->id;
                
                $orderDetail =  Order::where('parcel_nature',1)->where('id',$parcelId)->with([
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'orderPacking' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                ])->first();
                
                $scanOrder = ScanOrder::where('order_id',$parcelId)
                    ->with([
                        'scanByPicker' => function($query){
                            $query->select('id','name');
                        },

                        'scanByMiddleMan' => function($query){
                            $query->select('id','name');
                        },

                        'scanBySupervisor' => function($query){
                            $query->select('id','name');
                        },
                    ])
                ->first();

                $delivery = OrderAssigned::select('id','vendor_id',
                'order_id','rider_id','drop_off_location','latitude','longitude','trip_status_id','status')
                ->where(['order_id'=>$parcelId,'trip_status_id'=>4,'status'=>1])
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'orderDelivery' => function($query){
                        $query->select('id','order_assigned_id','amount','consignee_relation_id','other_relation','receiver_name','cnic','comment','signature','location_picture')->with([
                            'consigneeRelation' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'orderDecline' => function($query){
                        $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                            'orderDeclineStatus' => function($query){
                                $query->select('id','name');
                            },
                            'orderDeclineReason' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->first();
        
                $decline = OrderAssigned::select('id','vendor_id',
                'order_id','rider_id','drop_off_location','latitude','longitude','trip_status_id','status')
                ->where(['order_id'=>$parcelId,'status'=>0])
                ->with([
                    'rider' => function($query){
                        $query->select('id','name');
                    },
                    'orderDecline' => function($query){
                        $query->select('id','order_assigned_id','order_decline_status_id','order_decline_reason_id','additional_note','image')->with([
                            'orderDeclineStatus' => function($query){
                                $query->select('id','name');
                            },
                            'orderDeclineReason' => function($query){
                                $query->select('id','name');
                            },
                        ]);
                    }
                ])
                ->get();
        
                $decline_dates = OrderAssigned::where('order_id', $parcelId)->where('status', 0)->whereIn('trip_status_id',[4,5,6])->get();
                $assigned_dates = OrderAssigned::where('order_id', $parcelId)->whereIn('trip_status_id',[4,5,6])->get();
                
                $rider_detail = OrderAssigned::where('order_id', $parcelId)->orderBy('id', 'DESC')->first();

                $get_sag_orders = OrderInSag::where('order_id', $parcelId)->get();
                
                return view('parcel-detail',compact('orderDetail','delivery','decline','scanOrder','breadcrumbs', 'decline_dates','assigned_dates', 'rider_detail','get_sag_orders'));
            }else{
                return view('no-track-order');
            }

        }else{
            return view('no-track-order');
        }
    }

    public function awaitingAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Awaiting Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
	        $cities  = City::all();

                if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->where('consignee_city',$request->city)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }

        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){

            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->where('consignee_city',$request->city)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }

        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function pickupAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Pickup Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr')){
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest   = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }

        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){

            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $pickup_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest   = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }

        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function atAhlAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'At AHL Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr')){
            $cities = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest  = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){

            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest  = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 0)->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.atahl-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function supervisorAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Scan by Supervisor Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
       
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                        $cityRequest = $request->city;
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                        $cityRequest = $request->city;
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->whereIn('consignee_city',$usercity)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.check-supervisor',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function dispatchedAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Dispatched Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
	        $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
	        $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $request->from)->whereDate('supervisor_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $dispatch_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function deliveredAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Delivered Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {

            $cities = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){

            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();
            
            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.delivered-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function requestforReattemptAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Request for Re-attempt Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        $cities = City::all();

        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
	        $cities  = City::all();

            if($orderStatusId)
            {
                $vendorRequest = 'any';
                $cityRequest = 'any';
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId)
            {
                $vendorRequest = 'any';
                $cityRequest = 'any';
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function ReattemptAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Re-attempt Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }
        

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function cancelAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cancelled Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.cancel-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function returnAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Return Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        
        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();


            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.return-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function returnInProgressAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Return Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        
        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();


            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.return-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function cancelAhlAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cancel by AHL Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function cancelVendorAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cancel by Vendor Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function voidAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Void Label Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function replaceAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Replace Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    
                        $cityRequest = $request->city;

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
	        $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
	        $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    
                        $cityRequest = $request->city;

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('updated_at','>=', $request->from)->whereDate('updated_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function atAhlDelayedAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'At AHL Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr')){
            $cities = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest  = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){

            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest  = 'any';
    
                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $request->from)->whereDate('middle_man_scan_date', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $warehouse_scan_order)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
    
                    $vendorRequest = $request->vendor;
                    $cityRequest  = $request->city;
                }
                else
                {
                    
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->where('delayed_status', 1)->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.atahl-delayed-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function riderReattemptAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rider Reattempt Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

                if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->where('consignee_city',$request->city)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }

        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst')){

            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->where('consignee_city',$request->city)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                }
                            ]);
                        }
                    ])->orderBy('id','DESC')->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }

        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }
    
    public function cancelbyRiderAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cancelled Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function cancelbySupervisorAdminReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cancelled Parcels Status', 
        ];
        
        if($request->status){
            $orderStatusId = Helper::decrypt($request->status);
        }else{
            //empty order status
            $orderStatusId = $request->status;
        }
        $authUser = Auth::user();
        $vendors = Vendor::where('status',1)->get();
        
        $statuses = Status::whereNotIn('id', [6,9])->get();
        $delivered = Status::where('id', 6)->get();
        $cancelled = Status::where('id', 9)->get();

        if(Auth::user()->hasAnyRole('admin','hr'))
        {
            $cities  = City::all();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }

        if(Auth::user()->hasAnyRole('supervisor','cashier','first_man','middle_man','financer','sales','bd','bdm','csr','hub_manager','head_of_account','data_analyst'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id', $userId)->pluck('city_id');
            $cities  = City::whereIn('id', $usercity)->get();

            if($orderStatusId){
                $vendorRequest = 'any';
                $cityRequest = 'any';

                if($request->from && $request->to && $request->vendor == 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $cityRequest = $request->city;
                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                }
                elseif($request->from && $request->to && $request->vendor == 'any' && $request->city == 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                }
                elseif($request->from && $request->to && $request->vendor <> 'any' && $request->city <> 'any')
                {
                    $assigned_parcels = OrderAssigned::whereDate('created_at','>=', $request->from)->whereDate('created_at', '<=',$request->to)->pluck('order_id');
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('id', $assigned_parcels)
                        ->where('vendor_id', $request->vendor)->where('consignee_city',$request->city)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();

                    $vendorRequest = $request->vendor;
                    $cityRequest = $request->city;
                }
                else
                {
                    $orders = Order::where('parcel_nature',1)->where('order_status',$orderStatusId)->whereIn('consignee_city',$usercity)
                        ->with([
                        'vendor' => function($query){
                            $query->select('id','vendor_name');
                        },
                        'orderType' => function($query){
                            $query->select('id','name');
                        },
                        'orderPacking' => function($query){
                            $query->select('id','name');
                        },
                        'orderStatus' => function($query){
                            $query->select('id','name');
                        },
                        'vendorWeight' => function($query){
                            $query->with([
                                'ahlWeight' => function($query){
                                    $query->select('id','weight');
                                },
                                'city' => function($query){
                                    $query->select('id','name');
                                },
                            ]);
                        }
                    ])->orderBy('id','DESC')->limit(4000)->get();
                }
            }else{
                $orders = Order::where('id',0)->get();
            }
        }
            

        if($authUser->isAdmin() || $authUser->isFirstMan()){
            $cancelStatus = [1,2,4];
        }elseif($authUser->isVendorAdmin()){
            $cancelStatus = [1,2];
        }else{
            //means no when see except admin and vendor admin
            $cancelStatus = [0];
        }

        return view('reports.cancel-status-report',compact('statuses','cancelStatus','orders','breadcrumbs','delivered','cancelled','vendors','vendorRequest','cities','cityRequest'));
    }

    public function cashierCollectionReport(Request $request)
    {
        if($request->date && $request->to)
        {
            $today = $request->date;
            $to = $request->to;
        }
        else
        {
            $today = \Carbon\Carbon::now();
            $to = \Carbon\Carbon::now();
        }

        $collection_report = RiderCashCollection::whereDate('created_at','>=', $today)->whereDate('created_at', '<=',$to)->get();
        $collection_amount = RiderCashCollection::whereDate('created_at','>=', $today)->whereDate('created_at', '<=',$to)->sum('amount');
        
        return view('cashier-collection-report', compact('collection_report','collection_amount'));
    }
    
    public function riderCashReport(Request $request)
    {
        if($request->date)
        {
            $today = $request->date;
        }
        else
        {
            $today = \Carbon\Carbon::now();
        }

        $rider_details = DB::table('users')
            ->where('users.status', 1)
            ->join('order_assigneds', 'order_assigneds.rider_id', 'users.id')
            ->whereDate('order_assigneds.created_at', $today)
            ->where('order_assigneds.trip_status_id', 4)->where('order_assigneds.status', 1)
            ->join('orders', 'orders.id', 'order_assigneds.order_id')->where('orders.order_status', 6)
            ->select(
                DB::raw('users.name as name'),
                DB::raw('sum(orders.consignment_cod_price) as total')
            )
            ->groupBy(DB::raw('name'))->orderBy('name', 'ASC')->get();

        $rider_cash_details = DB::table('users')
            ->where('users.status', 1)
            ->join('rider_cash_collections', 'rider_cash_collections.rider_id', 'users.id')
            ->whereDate('rider_cash_collections.created_at', $today)
            ->select(
                DB::raw('users.name as name'),
                DB::raw('sum(rider_cash_collections.amount) as total')
            )
            ->groupBy(DB::raw('name'))->orderBy('name', 'ASC')->get();
        
        return view('rider-cash-report', compact('rider_details','rider_cash_details'));
    }

    public function pickupVendorParcelCountDownload(Request $request)
    {

        if($request->from && $request->to)
        {
            $from = \Carbon\Carbon::parse($request->from)->format('Y-m-d H:i:s');
            $to = \Carbon\Carbon::parse($request->to)->format('Y-m-d H:i:s');
        }
        else
        {
            $from = \Carbon\Carbon::now();
            $to = \Carbon\Carbon::now();
        }

        $vendor_id = $request->vendor_id;
        $fileName = 'Pickup Vendor Parcel Count';

        return Excel::download(new PickupVendorParcelCount($from,$to,$vendor_id), $fileName.'.xlsx');
    }

    public function bulkCancelByVendor(Request $request)
    {
        $parcels = $request->parcels;
        
        $authUser = Auth::user();
        foreach($parcels as $parcel)
        {
            $order = Order::find($parcel);
            if($authUser->isVendorAdmin()){
                $updateOrderStatus = 12;
                
            }elseif($authUser->isAdmin()){
                $updateOrderStatus = 11;
            }

            $order->update(['order_status' => $updateOrderStatus]);
        }
        
        return response()->json([
            'status' => 1, 
        ]);
    }

    //rack scanning

    public function rackCancelParcel(Request $request)
    {
        if(empty($request->date))
        {
            $response = [
                'status' => 'dateFrom',
                'message' => 'Please Select Date From First',
                'html_data' => 0,
                'scan_html_data' => 0,
            ];

            return response()->json($response);
        }

        if(empty($request->to))
        {
            $response = [
                'status' => 'dateTo',
                'message' => 'Please Select Date To First',
                'html_data' => 0,
                'scan_html_data' => 0,
            ];

            return response()->json($response);
        }

        if(Auth::user()->hasAnyRole('admin'))
        {
            $find_rack_parcel_all = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->pluck('order_id');
            $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->whereIn('status', [9])->orderBy('id', 'DESC')->get();

            $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [9])->get();

            $get_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'cancel')->first();
            if(!empty($get_remarks))
            {
                $fetch_remarks = $get_remarks->remarks;
            }
            else
            {
                $fetch_remarks = '';
            }
        }
        else
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId )->pluck('city_id');
            $find_rack_parcel_all = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->pluck('order_id');
            $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->whereIn('status', [9])->orderBy('id', 'DESC')->get();

            $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [9])->whereIn('consignee_city', $userCity)->get();

            $get_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'cancel')->first();
            if(!empty($get_remarks))
            {
                $fetch_remarks = $get_remarks->remarks;
            }
            else
            {
                $fetch_remarks = '';
            }
        }

        if(!empty($orders))
        {
            //show data
            $html = '<thead>';
            $html .= '<tr><th>#</th><th>Customer Ref #</th></thead>';
            $html .= '<tbody>';
            foreach ($orders as $key => $order) {
            $html .= '<tr>';
                $html .= '<td>' . ++$key . '</td>
                    <td>' . $order->order_reference . '</td>
                </tr>';
            }
            $html .= '</tbody>';

            //scan data
            $scan_html = '<thead>';
            $scan_html .= '<tr><th>#</th><th style="display:none">Sr.</th><th>Customer Ref #</th><th>Age</th></thead>';
            $scan_html .= '<tbody id="reattempt-data">';
            foreach ($find_rack_parcel_all_sets as $key => $order) {
            $scan_html .= '<tr style="background-color: #E25041; color: white">';
                $scan_html .= '<td>' . ++$key . '</td><td style="display:none">1</td>
                    <td>' . $order->orderDetail->order_reference . '</td>';
                if(!empty($order->orderDetail->scanOrder->middle_man_scan_date))
                {
                    $html .='<td>'. Carbon::parse($order->orderDetail->scanOrder->middle_man_scan_date)->diffInDays(Carbon::now()) .' Days</td>
                    </tr>';
                }
                else
                {
                    $html .='<td></td></tr>';
                }
            }
            $scan_html .= '</tbody>';
        }
        else
        {
            $html = '';
            $scan_html = '';
        }

        $status = 'Success';
        $message = 'Status Change Successfully';
        $html = $html;
        $scan_html = $scan_html;
        $fetch_remarks = $fetch_remarks;

        $response = [
            'status' => 'success',
            'message' => 'Status Change Successfully',
            'html_data' => $html,
            'scan_html_data' => $scan_html,
            'fetch_remarks' => $fetch_remarks,
        ];

        return response()->json($response);   
    }

    public function rackReattemptParcel(Request $request)
    {
        if(empty($request->date))
        {
            $response = [
                'status' => 'dateFrom',
                'message' => 'Please Select Date From First',
                'html_data' => 0,
                'scan_html_data' => 0,
            ];

            return response()->json($response);
        }

        if(empty($request->to))
        {
            $response = [
                'status' => 'dateTo',
                'message' => 'Please Select Date To First',
                'html_data' => 0,
                'scan_html_data' => 0,
            ];

            return response()->json($response);
        }

        if(Auth::user()->hasAnyRole('admin'))
        {
            $find_rack_parcel_all = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->pluck('order_id');
            $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->whereIn('status', [3,8])->orderBy('id', 'DESC')->get();

            $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [3,8])->get();

            $get_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'at_ahl')->first();
            if(!empty($get_remarks))
            {
                $fetch_remarks = $get_remarks->remarks;
            }
            else
            {
                $fetch_remarks = '';
            }
        }
        else
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId )->pluck('city_id');
            $find_rack_parcel_all = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->pluck('order_id');
            $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->whereIn('status', [3,8])->orderBy('id', 'DESC')->get();

            $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [3,8])->whereIn('consignee_city', $userCity)->get();

            $get_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'at_ahl')->first();
            if(!empty($get_remarks))
            {
                $fetch_remarks = $get_remarks->remarks;
            }
            else
            {
                $fetch_remarks = '';
            }
        }
        if(!empty($orders))
        {
            //show data
            $html = '<thead>';
            $html .= '<tr><th>#</th><th>Customer Ref #</th></thead>';
            $html .= '<tbody>';
            foreach ($orders as $key => $order) {
            $html .= '<tr>';
                $html .= '<td>' . ++$key . '</td>
                    <td>' . $order->order_reference . '</td>
                </tr>';
            }
            $html .= '</tbody>';

            //scan data
            $scan_html = '<thead>';
            $scan_html .= '<tr><th>#</th><th style="display:none">Sr.</th><th>Customer Ref #</th><th>Age</th></thead>';
            $scan_html .= '<tbody id="reattempt-data">';
            foreach ($find_rack_parcel_all_sets as $key => $order) {
            $color_code = '';
                if($order->orderDetail->order_status == 3)
                {
                    $color_code = '#33C1FF';
                }
                else
                {
                    $color_code = '#A0E064';
                }
            $scan_html .= '<tr style="background-color: '. $color_code .'; color: white">';
                $scan_html .= '<td>' . ++$key . '</td><td style="display:none">1</td>
                    <td>' . $order->orderDetail->order_reference . '</td>';
                if(!empty($order->orderDetail->scanOrder->middle_man_scan_date))
                {
                    $scan_html .='<td>'. Carbon::parse($order->orderDetail->scanOrder->middle_man_scan_date)->diffInDays(Carbon::now()) .' Days</td>
                    </tr>';
                }
                else
                {
                    $scan_html .='<td></td></tr>';
                }
            }
            $scan_html .= '</tbody>';
        }
        else
        {
            $html = '';
            $scan_html = '';
        }

        $status = 'Success';
        $message = 'Status Change Successfully';
        $html = $html;
        $scan_html = $scan_html;
        $fetch_remarks = $fetch_remarks;

        $response = [
            'status' => 'success',
            'message' => 'Status Change Successfully',
            'html_data' => $html,
            'scan_html_data' => $scan_html,
            'fetch_remarks' => $fetch_remarks,
        ];

        return response()->json($response);   
    }

    public function reattemptRackParcel(Request $request)
    {
        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;

        $check_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'at_ahl')->first();
        if(!empty($check_remarks))
        {
            $response = [
                'status' => 'Already',
                'message' => 'Remarks Already Added, Now you can not Scan any parcel',
            ];

            return response()->json($response);
        }

        $orderReferencce = $request->order_parcel_reference_no;
        $date = $request->date;
        $to = $request->to;
        if(Auth::user()->hasAnyRole('admin'))
        {
            $parcel = Order::where('order_reference',$orderReferencce)->whereIn('order_status', [3,8])->first();
        }
        else
        {
            $userCity = UserCity::where('user_id',$authMiddleManId )->pluck('city_id');
            $parcel = Order::where('order_reference',$orderReferencce)->whereIn('order_status', [3,8])->whereIn('consignee_city', $userCity)->first();
        }

        if($parcel){
            $find_rack_parcel = RackParcelList::where('order_id', $parcel->id)->whereDate('date_from', $date)->whereDate('date_to', $to)->first();

            if(empty($find_rack_parcel))
            {
                $data_set = [
                    'date_from' => $date,
                    'date_to' => $to,
                    'order_id' => $parcel->id,
                    'status' => $parcel->order_status,
                    'scan_by' => $authMiddleManId,
                ];

                $create_rack = RackParcelList::create($data_set);

                $find_rack_parcel_all = RackParcelList::whereDate('date_from', $date)->whereDate('date_to', $to)->pluck('order_id');
                $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $date)->whereDate('date_to', $to)->whereIn('status', [3,8])->orderBy('id', 'DESC')->get();

                if(Auth::user()->hasAnyRole('admin'))
                {
                    $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [3,8])->get();
                }
                else
                {
                    $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [3,8])->whereIn('consignee_city', $userCity)->get();
                }

                if(!empty($orders))
                {
                    //show data
                    $html = '<thead>';
                    $html .= '<tr><th>#</th><th>Customer Ref #</th></thead>';
                    $html .= '<tbody>';
                    foreach ($orders as $key => $order) {
                    $html .= '<tr>';
                        $html .= '<td>' . ++$key . '</td>
                            <td>' . $order->order_reference . '</td>
                        </tr>';
                    }
                    $html .= '</tbody>';

                    //scan data
                    $scan_html = '<thead>';
                    $scan_html .= '<tr><th>#</th><th style="display:none">Sr.</th><th>Customer Ref #</th><th>Age</th></thead>';
                    $scan_html .= '<tbody id="reattempt-data">';
                    foreach ($find_rack_parcel_all_sets as $key => $order) {
                    $color_code = '';
                        if($order->orderDetail->order_status == 3)
                        {
                            $color_code = '#33C1FF';
                        }
                        else
                        {
                            $color_code = '#A0E064';
                        }
                    $scan_html .= '<tr style="background-color: '. $color_code .'; color: white">';
                        $scan_html .= '<td>' . ++$key . '</td><td style="display:none">1</td>
                            <td>' . $order->orderDetail->order_reference . '</td>';
                        if(!empty($order->orderDetail->scanOrder->middle_man_scan_date))
                        {
                            $scan_html .='<td>'. Carbon::parse($order->orderDetail->scanOrder->middle_man_scan_date)->diffInDays(Carbon::now()) .' Days</td>
                            </tr>';
                        }
                        else
                        {
                            $scan_html .='<td></td></tr>';
                        }
                    }
                    $scan_html .= '</tbody>';
                }
                else
                {
                    $html = '';
                    $scan_html = '';
                }

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;
                $html = $html;
                $scan_html = $scan_html;
            }
            else
            {

                $status = 'Scanned';
                $message = 'Pacel Already Scan';
                $data = 0;
                $html = 0;
                $scan_html = 0;
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
            $html = 0;
            $scan_html = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'html_data' => $html,
            'scan_html' => $scan_html,
        ];

        return response()->json($response);
    }

    public function cancelRackParcel(Request $request)
    {
        $authUser = Auth::user();
        $authMiddleManId = $authUser->id;

        $check_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'cancel')->first();
        if(!empty($check_remarks))
        {
            $response = [
                'status' => 'Already',
                'message' => 'Remarks Already Added, Now you can not Scan any parcel',
            ];

            return response()->json($response);
        }

        $orderReferencce = $request->order_parcel_reference_no;
        $date = $request->date;
        $to = $request->to;
        if(Auth::user()->hasAnyRole('admin'))
        {
            $parcel = Order::where('order_reference',$orderReferencce)->whereIn('order_status', [9])->first();
        }
        else
        {
            $userCity = UserCity::where('user_id',$authMiddleManId )->pluck('city_id');
            $parcel = Order::where('order_reference',$orderReferencce)->whereIn('order_status', [9])->whereIn('consignee_city', $userCity)->first();
        }

        if($parcel){
            $find_rack_parcel = RackParcelList::where('order_id', $parcel->id)->whereDate('date_from', $date)->whereDate('date_to', $to)->first();

            if(empty($find_rack_parcel))
            {
                $data_set = [
                    'date_from' => $date,
                    'date_to' => $to,
                    'order_id' => $parcel->id,
                    'status' => $parcel->order_status,
                    'scan_by' => $authMiddleManId,
                ];

                $create_rack = RackParcelList::create($data_set);

                $find_rack_parcel_all = RackParcelList::whereDate('date_from', $date)->whereDate('date_to', $to)->pluck('order_id');
                $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $date)->whereDate('date_to', $to)->whereIn('status', [9])->orderBy('id', 'DESC')->get();

                if(Auth::user()->hasAnyRole('admin'))
                {
                    $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [9])->get();
                }
                else
                {
                    $orders = Order::whereNotIn('id', $find_rack_parcel_all)->whereIn('order_status', [9])->whereIn('consignee_city', $userCity)->get();
                }

                if(!empty($orders))
                {
                    //show data
                    $html = '<thead>';
                    $html .= '<tr><th>#</th><th>Customer Ref #</th></thead>';
                    $html .= '<tbody>';
                    foreach ($orders as $key => $order) {
                    $html .= '<tr>';
                        $html .= '<td>' . ++$key . '</td>
                            <td>' . $order->order_reference . '</td>
                        </tr>';
                    }
                    $html .= '</tbody>';

                    //scan data
                    $scan_html = '<thead>';
                    $scan_html .= '<tr><th>#</th><th style="display:none">Sr.</th><th>Customer Ref #</th><th>Age</th></thead>';
                    $scan_html .= '<tbody id="reattempt-data">';
                    foreach ($find_rack_parcel_all_sets as $key => $order) {
                    $scan_html .= '<tr style="background-color: #E25041; color: white">';
                        $scan_html .= '<td>' . ++$key . '</td><td style="display:none">1</td>
                        <td>' . $order->orderDetail->order_reference . '</td>';
                        if(!empty($order->orderDetail->scanOrder->middle_man_scan_date))
                        {
                            $html .='<td>'. Carbon::parse($order->orderDetail->scanOrder->middle_man_scan_date)->diffInDays(Carbon::now()) .' Days</td>
                            </tr>';
                        }
                        else
                        {
                            $html .='<td></td></tr>';
                        }
                    }
                    $scan_html .= '</tbody>';
                }
                else
                {
                    $html = '';
                    $scan_html = '';
                }

                $status = 'Success';
                $message = 'Status Change Successfully';
                $data = $parcel;
                $html = $html;
                $scan_html = $scan_html;
            }
            else
            {

                $status = 'Scanned';
                $message = 'Pacel Already Scan';
                $data = 0;
                $html = 0;
                $scan_html = 0;
            }
        }else{
            $status = 'Invalid';
            $message = 'Invalid Parcel Reference Number';
            $data = 0;
            $html = 0;
            $scan_html = 0;
        }

        $response = [
            'status' => $status,
            'message' => $message,
            'parcel' => $data,
            'html_data' => $html,
            'scan_html' => $scan_html,
        ];

        return response()->json($response);
    }

    public function scanHistoryReport(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Rack Balancing Report', 
        ];

        $today_date = Carbon::now(+5)->format('Y-m-d');
        
        if(isset($request->date)){
            $requestDate = $request->date;
        }else{
            $requestDate = now();
        }

        if(isset($request->to)){
            $requestDateTo = $request->to;
        }else{
            $requestDateTo = now();
        }

        $userId = Auth::user()->id;

        if($request->status_value == 'cancel')
        {
            $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $requestDate)->whereDate('date_to', $requestDateTo)->whereIn('status', [9])->get();

            $get_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'cancel')->first();

            if(!empty($get_remarks))
            {
                $remarks_data = $get_remarks;
            }
            else
            {
                $remarks_data = '';
            }
        }
        elseif($request->status_value == 'at_ahl')
        {
            $find_rack_parcel_all_sets = RackParcelList::whereDate('date_from', $requestDate)->whereDate('date_to', $requestDateTo)->whereIn('status', [3,8])->get();

            $get_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->where('mode', 'at_ahl')->first();

            if(!empty($get_remarks))
            {
                $remarks_data = $get_remarks;
            }
            else
            {
                $remarks_data = '';
            }
        }
        else
        {
            $find_rack_parcel_all_sets = [];
            $remarks_data = '';
        }

        return view('scan-history-report',compact('breadcrumbs','find_rack_parcel_all_sets','today_date','remarks_data'));
    }

    public function rackBalanceRemarks(Request $request)
    {
        $check_remarks = RackBalancing::whereDate('date_from', $request->date)->whereDate('date_to', $request->to)->first();
        if(!empty($check_remarks))
        {
            $response = [
                'status' => 'Already',
                'message' => 'Remarks Already Added By You',
            ];

            return response()->json($response);
        }

        if(empty($request->date))
        {
            $response = [
                'status' => 'dateFrom',
                'message' => 'Please Select Date From First',
            ];

            return response()->json($response);
        }

        if(empty($request->to))
        {
            $response = [
                'status' => 'dateTo',
                'message' => 'Please Select Date To First',
            ];

            return response()->json($response);
        }

        if(empty($request->remarks))
        {
            $response = [
                'status' => 'remarks',
                'message' => 'Please Enter Remarks',
            ];

            return response()->json($response);
        }


        $userId = Auth::user()->id;
        $scan_parcels = $request->sumVal;

        if($request->show_data_content == 'Re-attempt Parcels List')
        {
            $total_parcels = $request->at_ahl_parcel;
            $mode = 'at_ahl';
        }
        else
        {
            $total_parcels = $request->cancel_parcel;
            $mode = 'cancel';
        }

        $data_set = [
            'date_from' => $request->date,
            'date_to' => $request->to,
            'total_parcels' => $total_parcels,
            'scan_parcels' => $scan_parcels,
            'mode' => $mode,
            'remarks' => $request->remarks,
            'remarks_by' => $userId,
        ];

        RackBalancing::create($data_set);

        $response = [
            'status' => 'success',
            'message' => 'Rack Balancing Saved Successfully',
        ];

        return response()->json($response);
    }

    public function parcelAggingReport()
    {
        $breadcrumbs = [
            'name' => 'Parcels Agging Report', 
        ];

        $orders = Order::whereIn('order_status', [3,8])->get();

        return view('admin.agging-report',compact('orders','breadcrumbs'));
    }

    public function searchParcelThroughMobile(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Track Your Parcel Through Mobile Number', 
        ];

        if(isset($request->mobile)){
            $parcelMobile = $request->mobile;
            $orders = Order::where('parcel_nature',1)->where('consignee_phone',$parcelMobile)->get();
            
            if(count($orders) > 0)
            {
                return view('parcel-list',compact('breadcrumbs','orders'));
            }
            else
            {
                return view('no-track-order');
            }

        }else{
            return view('no-track-order');
        }
    }

    public function reportPRA(Request $request)
    {
        $to = $request->to;
        $from = $request->from;
        
        if($request->to && $request->from)
        {
            $fileName = 'PRA Report From '.$from.' to '.$to;
            return Excel::download(new PRAReportExport($from,$to), $fileName.'.xlsx');
        }
        
        return view('admin.pra_report', compact('to', 'from'));
    }

    //New Report
    public function vendorParcelsReport(Request $request)
    {
        if($request->date && $request->to)
        {
            $today = \Carbon\Carbon::parse($request->date)->format('Y-m-d H:i:s');
            $to = \Carbon\Carbon::parse($request->to)->format('Y-m-d H:i:s');

            $vendors = Vendor::where('status',1)->with([
                'freshOrders' => function($query) use($today, $to){
                    $query->whereDate('created_at','>=', $today)->where('created_at', '<=',$to);
                },
                'pickOrders' => function($query) use($today, $to){
                    $query->with([
                        'scanOrder' => function($query) use($today, $to){
                            $query->whereDate('created_at','>=', $today)->where('created_at', '<=',$to);
                        },
                    ]);
                },
            ])->get();
        }
        else
        {
            $vendors = [];
        }
        // dd($vendors);
        return view('parcels-new-report', compact('vendors'));
    }
}
