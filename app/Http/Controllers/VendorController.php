<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;

use App\Models\Vendor;
use App\Models\Order;
use App\Models\ScanOrder;
use App\Models\City;
use App\Models\AhlTimings;
use App\Models\AhlWeight;
use App\Models\VendorTiming;
use App\Models\VendorWeight;
use App\Models\WarehouseLocation;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Status;
use App\Models\PickupRequest;
use App\Models\AssignCity;
use App\Models\VendorFinancial;
use App\Models\ShiperAdviser;
use App\Models\OrderAssigned;
use App\Models\SubArea;
use App\Models\UserCity;

//Helper
use App\Helpers\Helper;
use AHLHelper;

use PDF;

class VendorController extends Controller
{
    public function dashboard(Request $request)
    {
        if(Auth::user()->isAdmin() || Auth::user()->isFirstMan() || Auth::user()->isMiddleMan() || Auth::user()->isSupervisor() || Auth::user()->isCashier() || Auth::user()->isFinancer()){
            return redirect()->route('adminDashboard');
        }

        $breadcrumbs = [
            'name' => 'Dashboard', 
        ];
        
        $from = $request->from;
        $to = $request->to;

        $vendorId = Auth::user()->vendor_id;
        $status = Status::all();
        $cities = City::all();
        
        if($from && $to && $request->city <> 'any')
        {
            $total_parcel = Order::whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->where('consignee_city',$request->city)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('parcel_nature',1)->count();
            $assigned_parcels = OrderAssigned::where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
            $delivered_parcel = Order::whereIn('id', $assigned_parcels)->where('order_status',6)->where('vendor_id', $vendorId)->where('parcel_nature',1)->count();
            $returntovendor_parcel = Order::where('order_status', 10)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->where('parcel_nature',1)->count();
            $pending = Order::whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->where('parcel_nature',1)->count();
            $allCancelledParcel = Order::whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->where('parcel_nature',1)->count();
            
            //parcel amount
            $overall_sum = Order::whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('parcel_nature',1)->sum('consignment_cod_price');
            $delivered_sum = Order::whereIn('id', $assigned_parcels)->where('order_status',6)->where('vendor_id', $vendorId)->where('parcel_nature',1)->sum('consignment_cod_price');
            $returntovendor_parcel_sum = Order::where('order_status', 10)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->where('parcel_nature',1)->sum('consignment_cod_price');
            $pending_sum = Order::whereIn('id', $assigned_parcels)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->where('parcel_nature',1)->sum('consignment_cod_price');
            $cancelled_sum = Order::whereIn('id', $assigned_parcels)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->where('parcel_nature',1)->sum('consignment_cod_price');
            
            //Payable to vendor
            $vendor_payable = Order::whereIn('order_status',[6,14])->where('vendor_id', $vendorId)->where('parcel_nature',1)->sum('consignment_cod_price');
            $vendor_parcel_commission = Helper::ahlCommissionParcelSumNew($vendorId);
            $find_vendor = Vendor::where('id', $vendorId)->first();
            $vendor_tax = $find_vendor->vendorGst;
            $notFilterTaxAmount = $vendor_parcel_commission*$vendor_tax/100;
            $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
            $final_cash_paid_to_vendor = $vendor_payable - ($vendor_parcel_commission + $notFilterTaxAmount);
            $final_cash_payable_to_vendor = $final_cash_paid_to_vendor - $cash_paid_to_vendor;
            
            //COD Parcels amount
            $cod_parcel = Order::whereIn('order_status', [2,3,4,5,7,9])->where('consignment_order_type',1)->where('vendor_id', $vendorId)->where('parcel_nature',1)->sum('consignment_cod_price');
            
            //parcel statuses
            $awaiting = Order::where('order_status', 1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('parcel_nature',1)->count();
            $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
            $pickup = Order::whereIn('id', $pickup_scan_order)->where('parcel_nature',1)->where('order_status', 2)->where('vendor_id', $vendorId)->count();
            $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
            $warehouse = Order::whereIn('id', $warehouse_scan_order)->where('parcel_nature',1)->where('order_status', 3)->where('vendor_id', $vendorId)->count();
            $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
            $dispatched = Order::whereIn('id', $dispatch_scan_order)->where('parcel_nature',1)->where('order_status', 5)->where('vendor_id', $vendorId)->count();
            $delivered = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->count();
            $requestforreattempt = Order::where('order_status', 7)->where('parcel_nature',1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('vendor_id', $vendorId)->count();
            $reattempt = Order::where('order_status', 8)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $cancelled = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
            $returntovendor = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
            $returntovendorinprogress = Order::where('order_status', 19)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
            $cancelbyadmin = Order::where('order_status', 11)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $cancelbyvendor = Order::where('order_status', 12)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $voidlabel = Order::where('order_status', 13)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $replace= Order::where('order_status', 14)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            
            $cityRequest = $request->city;
            return view('vendor/dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','final_cash_paid_to_vendor','replace','final_cash_payable_to_vendor','cod_parcel','cities','cityRequest','cash_paid_to_vendor'));
        }

        if($from && $to && $request->city == 'any')
        {
            $total_parcel = Order::whereNotIn('order_status', [11,12,13,14])->where('parcel_nature',1)->where('vendor_id', $vendorId)->where('consignee_city',$request->city)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $assigned_parcels = OrderAssigned::where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
            $delivered_parcel = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->count();
            $returntovendor_parcel = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
            $pending = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->count();
            $allCancelledParcel = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->count();
            
            //parcel amount
            $overall_sum = Order::whereNotIn('order_status', [11,12,13,14])->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('parcel_nature',1)->sum('consignment_cod_price');
            $delivered_sum = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
            $returntovendor_parcel_sum = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->sum('consignment_cod_price');
            $pending_sum = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
            $cancelled_sum = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
            
            //Payable to vendor
            $vendor_payable = Order::whereIn('order_status',[6,14])->where('parcel_nature',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
            $vendor_parcel_commission = Helper::ahlCommissionParcelSumNew($vendorId);
            $find_vendor = Vendor::where('id', $vendorId)->first();
            $vendor_tax = $find_vendor->vendorGst;
            $notFilterTaxAmount = $vendor_parcel_commission*$vendor_tax/100;
            $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
            $final_cash_paid_to_vendor = $vendor_payable - ($vendor_parcel_commission + $notFilterTaxAmount);
            $final_cash_payable_to_vendor = $final_cash_paid_to_vendor - $cash_paid_to_vendor;
            
            //COD Parcels amount
            $cod_parcel = Order::whereIn('order_status', [2,3,4,5,7,9])->where('parcel_nature',1)->where('consignment_order_type',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
            
            //parcel statuses
            $awaiting = Order::where('order_status', 1)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $pickup_scan_order = ScanOrder::whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->pluck('order_id');
            $pickup = Order::whereIn('id', $pickup_scan_order)->where('parcel_nature',1)->where('order_status', 2)->where('vendor_id', $vendorId)->count();
            $warehouse_scan_order = ScanOrder::whereDate('middle_man_scan_date','>=', $from)->whereDate('middle_man_scan_date', '<=',$to)->pluck('order_id');
            $warehouse = Order::whereIn('id', $warehouse_scan_order)->where('parcel_nature',1)->where('order_status', 3)->where('vendor_id', $vendorId)->count();
            $dispatch_scan_order = ScanOrder::whereDate('supervisor_scan_date','>=', $from)->whereDate('supervisor_scan_date', '<=',$to)->pluck('order_id');
            $dispatched = Order::whereIn('id', $dispatch_scan_order)->where('parcel_nature',1)->where('order_status', 5)->where('vendor_id', $vendorId)->count();
            $delivered = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->count();
            $requestforreattempt = Order::where('order_status', 7)->where('parcel_nature',1)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->where('vendor_id', $vendorId)->count();
            $reattempt = Order::where('order_status', 8)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $cancelled = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
            $returntovendor = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
            $returntovendorinprogress = Order::where('order_status', 19)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('updated_at','>=', $from)->whereDate('updated_at', '<=',$to)->count();
            $cancelbyadmin = Order::where('order_status', 11)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $cancelbyvendor = Order::where('order_status', 12)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $voidlabel = Order::where('order_status', 13)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            $replace= Order::where('order_status', 14)->where('parcel_nature',1)->where('vendor_id', $vendorId)->whereDate('created_at','>=', $from)->whereDate('created_at', '<=',$to)->count();
            
            $cityRequest = 'any';
            return view('vendor/dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','final_cash_paid_to_vendor','replace','final_cash_payable_to_vendor','cod_parcel','cities','cityRequest','cash_paid_to_vendor'));
        }
        
        $total_parcel = Order::whereNotIn('order_status', [11,12,13,14])->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $assigned_parcels = OrderAssigned::where('vendor_id', $vendorId)->pluck('order_id');
        $delivered_parcel = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->count();
        $returntovendor_parcel = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $pending = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->count();
        $allCancelledParcel = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->count();
            
        //parcel amount
        $overall_sum = Order::whereNotIn('order_status', [11,12,13,14])->where('parcel_nature',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        $delivered_sum = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        $returntovendor_parcel_sum = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        $pending_sum = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereNotIn('order_status', [1,6,10,9,11,12,13,14])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        $cancelled_sum = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
            
        //Payable to vendor
        $vendor_payable = Order::whereIn('order_status',[6,14])->where('parcel_nature',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        $vendor_parcel_commission = Helper::ahlCommissionParcelSumNew($vendorId);
        $find_vendor = Vendor::where('id', $vendorId)->first();
        $vendor_tax = $find_vendor->vendorGst;
        $notFilterTaxAmount = $vendor_parcel_commission*$vendor_tax/100;
        $cash_paid_to_vendor = VendorFinancial::where('vendor_id', $vendorId)->sum('amount');
        $final_cash_paid_to_vendor = $vendor_payable - ($vendor_parcel_commission + $notFilterTaxAmount);
        $final_cash_payable_to_vendor = $final_cash_paid_to_vendor - $cash_paid_to_vendor;
        
        //COD Parcels amount
        $cod_parcel = Order::whereIn('order_status', [2,3,4,5,7,9])->where('parcel_nature',1)->where('consignment_order_type',1)->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        
        //parcel statuses
        $awaiting = Order::where('order_status', 1)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $check_orders = Order::where('parcel_nature',1)->where('vendor_id', $vendorId)->pluck('id');
        $pickup_scan_order = ScanOrder::whereIn('order_id', $check_orders)->pluck('order_id');
        $pickup = Order::whereIn('id', $pickup_scan_order)->where('parcel_nature',1)->where('order_status', 2)->where('vendor_id', $vendorId)->count();
        $warehouse_scan_order = ScanOrder::whereIn('order_id', $check_orders)->pluck('order_id');
        $warehouse = Order::whereIn('id', $warehouse_scan_order)->where('parcel_nature',1)->whereIn('order_status', [3,15])->where('vendor_id', $vendorId)->count();
        $dispatch_scan_order = ScanOrder::whereIn('order_id', $check_orders)->pluck('order_id');
        $dispatched = Order::whereIn('id', $dispatch_scan_order)->where('parcel_nature',1)->where('order_status', 5)->where('vendor_id', $vendorId)->count();
        $delivered = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status',6)->where('vendor_id', $vendorId)->count();
        $requestforreattempt = Order::where('order_status', 7)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $reattempt = Order::where('order_status', 8)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $cancelled = Order::whereIn('id', $assigned_parcels)->where('parcel_nature',1)->where('order_status', 9)->where('vendor_id', $vendorId)->count();
        $returntovendor = Order::where('order_status', 10)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $returntovendorinprogress = Order::where('order_status', 19)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $cancelbyadmin = Order::where('order_status', 11)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $cancelbyvendor = Order::where('order_status', 12)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $voidlabel = Order::where('order_status', 13)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();
        $replace= Order::where('order_status', 14)->where('parcel_nature',1)->where('vendor_id', $vendorId)->count();

        $cityRequest = 'any';
        return view('vendor/dashboard', compact('total_parcel','delivered_parcel','returntovendor_parcel','pending','allCancelledParcel','overall_sum','delivered_sum',
                    'returntovendor_parcel_sum','pending_sum','cancelled_sum','awaiting','pickup','warehouse','dispatched','delivered','requestforreattempt','reattempt','cancelled',
                    'returntovendor','returntovendorinprogress','cancelbyadmin','cancelbyvendor','voidlabel','final_cash_paid_to_vendor','replace','final_cash_payable_to_vendor','cod_parcel','cities','cityRequest','cash_paid_to_vendor'));
    }

    public function vendorList()
    {
        $breadcrumbs = [
            'name' => 'Vendor List', 
        ];

        $vendor = Vendor::all();

        $vendorGroupBy = Vendor::select(DB::raw('count(*) as total'),'status')->groupBy('status')->get();
        //dd($vendor);
        /*$vendorData = DB::select("select status,
            sum(case when status=1 then 1 else 0 end) as total_active,
            sum(case when status=0 then 1 else 0 end) as total_block
            from Vendors
            group by status
        ");*/

        foreach ($vendorGroupBy as $key => $vendorStatusGroup) {
            if($vendorStatusGroup->status == 1){
                $active = $vendorStatusGroup->total;
            }else{
                $block = $vendorStatusGroup->total;
            }
        }

        (isset($active)) ? $active : $active = 0;
        (isset($block)) ? $block : $block = 0;

        $estimateVendor = [
            'active' => $active,
            'block' => $block
        ];

        return view('admin.vendor.index', compact('estimateVendor','vendor','breadcrumbs'));
    }

    public function createVendor()
    {
        $breadcrumbs = [
            'name' => 'Create Vendor', 
        ];

        if(Auth::user()->hasAnyRole('admin')){

            $user_cities = City::all();
            $cities = City::all();
            $ahlTimings = AhlTimings::all();
            $ahlWeights = AhlWeight::all();
        }
        elseif(Auth::user()->hasAnyRole('sales|bd|bdm')){
            $userId = Auth()->user()->id;
            $usercities = UserCity::where('user_id',$userId)->pluck('city_id');
            $user_cities = City::whereIn('id',$usercities)->get();
            $cities = City::all();
    
            $ahlTimings = AhlTimings::all();
            $ahlWeights = AhlWeight::all();
        }

        $sale_staffs = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->whereIn('name', ['bd','bdm']);
            })
        ->get();

        $csr_staffs = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'csr');
            })
        ->get();

        $pickup_staffs = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'first_man');
            })
        ->get();
        

    	return view('admin.vendor.create', compact('ahlTimings','ahlWeights','breadcrumbs','cities','user_cities','sale_staffs','csr_staffs','pickup_staffs'));
    }

    public function editVendor(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Edit Vendor', 
        ];

        $requestId = $request->id;
        $cities = City::all();

        $sale_staffs = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->whereIn('name', ['bd','bdm']);
            })
        ->get();

        $csr_staffs = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'csr');
            })
        ->get();

        $pickup_staffs = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'first_man');
            })
        ->get();

        $vendor = Vendor::where('id',$requestId)->with([
            'vendorCountry' => function($query){
                $query->select('id','name');
            },
            'vendorState' => function($query){
                $query->select('id','name');
            },
            'vendorCity' => function($query){
                $query->select('id','name');
            },
            'vendorLoginDetail' => function($query){
                $query->select('id','vendor_id','email');
            },
            'pickupLocation' => function($query){
                $query->select('id','vendor_id','address')->where('status', 1);
            },
            'vendorWeights.ahlWeight' => function($query){
                $query->select('id','weight');
            },
            'timings' => function($query){
                $query->select('id','vendor_id','timing_slot_id');
            },
        ])->first();

        foreach ($vendor->pickupLocation as $key => $location) {
            $dbPickupLocation[$location->id] = $location->address;
        }


        foreach ($vendor->vendorWeights as $key => $vendorWeight) {
            
            $dbweight[$vendorWeight->id] = 
                [
                    'vendor_weight_id' => $vendorWeight->id,
                    'ahl_weight_id' => $vendorWeight->ahl_weight_id,
                    'weight' => $vendorWeight->ahlWeight->weight,
                    'price' => $vendorWeight->price,
                ];
        }

        foreach ($vendor->timings as $key => $timing) {
            $dbTiming[$timing->id] = $timing->timing_slot_id;
        }

        $ahlTimings = AhlTimings::all();
        $ahlWeights = AhlWeight::all();

        return view('admin.vendor.edit', compact('breadcrumbs','vendor','dbPickupLocation','dbweight','dbTiming','ahlTimings','ahlWeights','cities','sale_staffs','csr_staffs','pickup_staffs'));
    }

    public function updateVendor(Request $request)
    {
        $vendorId = $request->id;
        $userId = $request->user_id;//id from user table
        
        $validatedData = [
            //company detail
            'vendor_name' => 'required',
            'vendor_email' => 'required|email|unique:vendors,vendor_email,'.$vendorId,
            'addational_Kgs' => 'required',
            'vendor_address' => 'required',
            'vendor_phone' => 'required|numeric',
            'latitude' => 'required',
            'longitude' => 'required',
            'cnic' => 'required',
            'ntn' => 'nullable',//optional
            'strn' => 'nullable',//optional
            'website' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'gst' => 'nullable',
            'fuel' => 'nullable',

            //focal person
            'focal_person_phone'=> 'required|numeric',
           'focal_person_name'=> 'required',
           'focal_person_email'=> 'required|email',
           'focal_person_address'=> 'required',
            
            //Bank
           'bank_name'=> 'nullable',//optional
           'bank_title'=> 'nullable',//optional
           'bank_account'=> 'nullable',//optional
            
            //pickup locations
            //'pickupAddress'=> 'required',
            'timing' => 'nullable',
            //'ahl_weight.*.weight_id' => 'required',

            //add vendor weight
            //'vendorWeights'=> 'required',

            //Login Credentials
            'login_email' => 'required|email|unique:users,email,'.$userId,
            'login_password' => 'nullable|min:6',
            'login_confirm_password' => 'same:login_password'
        ];

        $validator = Validator::make($request->all(), $validatedData);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->all());
        }

        $vendorObj = Vendor::find($vendorId);

        try {

            //Transaction
            DB::beginTransaction();

            $vendor = [
                'vendor_name' => $request->vendor_name,
                'vendor_phone' => $request->vendor_phone,
                'vendor_email' => $request->vendor_email,
                'addational_kgs' => $request->addational_Kgs,
                'vendor_address' => $request->vendor_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'cnic' => $request->cnic,
                'ntn' => $request->ntn,
                'strn' => $request->strn,
                'website' => $request->website,
                'country_id' => $request->country,
                'state_id' => $request->state,
                'city_id' => $request->city,
                'focal_person_name' => $request->focal_person_name,
                'focal_person_email' => $request->focal_person_email,
                'focal_person_phone' => $request->focal_person_phone,
                'focal_person_address' => $request->focal_person_address,
                'bank_name' => $request->bank_name,
                'bank_title' => $request->bank_title,
                'bank_account' => $request->bank_account,
                'gst' => $request->gst,
                'fuel' => $request->fuel,
                'remarks' => $request->remarks,
                'payment_mode' => $request->payment_mode,
                'ntn_buyer' => $request->ntn_buyer,
                'ntn_city' => $request->ntn_city,
                'complain_number' => $request->complain_number,
                'payment' => $request->payment,
                'category' => $request->category,
            ];

            $vendorObj->update($vendor);

            if(Auth::user()->hasAnyRole('admin','bdm','bd'))
            {
                if($request->poc != $vendorObj->poc)
                {
                    $poc_obj = [
                        'poc' => $request->poc,
                        'datentime' => now(),
                        'poc_assigned_by' => Auth::user()->id,
                    ];

                    $vendorObj->update($poc_obj);
                }

                if($request->csr != $vendorObj->csr)
                {
                    $csr_obj = [
                        'csr' => $request->csr,
                        'csr_datentime' => now(),
                        'csr_assigned_by' => Auth::user()->id,
                    ];

                    $vendorObj->update($csr_obj);
                }

                if($request->pickup != $vendorObj->pickup)
                {
                    $pickup_obj = [
                        'pickup' => $request->pickup,
                        'pickup_datentime' => now(),
                        'pickup_assigned_by' => Auth::user()->id,
                    ];

                    $vendorObj->update($pickup_obj);
                }
            }
            
            $login = [
                'name' => $request->vendor_name,
                'email' => $request->login_email,
                'phone_number' => $request->focal_person_phone,
            ];

            if($request->login_password){
                $login = array_merge($login, ['password' => Hash::make($request->login_password), 'password_status' => 1]);
            }
            
            if($request->has('timing'))
            {
                $pickup_timing = $request->timing;
                $vendorTiming = $vendorId;

                foreach($pickup_timing as $keyOne => $pickup)
                {
                    $dataOne = [
                        'vendor_id' => $vendorTiming,
                        'timing_slot_id' => $pickup,
                        'status' => 1,
                    ];
                    
                    $timing = VendorTiming::create($dataOne);
                }
            }

            $user = User::whereId($userId)->update($login);

            DB::commit();
            // all good

        } catch (Throwable $e) {
            DB::rollback();

            report($e);
            return false;
        }
        

        return redirect()->route('vendorList');
    }
    
    public function saveVendor(Request $request)
    {

        //dd($request->all());
        $validatedData = [
            //company detail
            'vendor_name' => 'required',
            'vendor_email' => 'required|email|unique:vendors,vendor_email',
            'vendor_address' => 'required',
            'addational_Kgs' => 'required',
            'vendor_phone' => 'required|numeric',
            'latitude' => 'required',
            'longitude' => 'required',
            'cnic' => 'required',
            'ntn' => 'nullable',//optional
            'strn' => 'nullable',//optional
            'website' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'gst' => 'nullable',
            'fuel' => 'nullable',

            //focal person
            'focal_person_phone'=> 'required|numeric',
	       'focal_person_name'=> 'required',
	       'focal_person_email'=> 'required|email',
	       'focal_person_address'=> 'required',
            
            //Bank
	       'bank_name'=> 'nullable',//optional
	       'bank_title'=> 'nullable',//optional
	       'bank_account'=> 'nullable',//optional
            
            //pickup locations
            'pickupAddress'=> 'required',
            'timing' => 'required',
            //'ahl_weight.*.weight_id' => 'required',

            //add vendor weight
            'vendorWeights'=> 'required',

            //Login Credentials
            'login_email' => 'required|email|unique:users,email',
            'login_password' => 'required|min:6',
            'login_confirm_password' => 'required|same:login_password'
        ];

        $validator = Validator::make($request->all(), $validatedData);
        //$ahlWeightCount = AhlWeight::get()->count();

        //$flag = 0;
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->all());
        }

        $vendor_number = 0;
        $vendor_number_format = 0;

        $get_city_data = City::where('id', $request->city)->first();
        if(!empty($get_city_data))
        {
            $city_code = $get_city_data->code;
        }
        else
        {
            $city_code = 'AHL';
        }

        $vendor_data = Vendor::orderBy('id','DESC')->first();
        $vendor_number_format = $vendor_data->vendor_token + 1;
        $vendor_number = $city_code."#00".$vendor_number_format;

        try {

            //Transaction
            DB::beginTransaction();

            $vendor = [
                'vendor_name' => $request->vendor_name,
                'vendor_phone' => $request->vendor_phone,
                'vendor_email' => $request->vendor_email,
                'vendor_address' => $request->vendor_address,
                'addational_kgs' => $request->addational_Kgs,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'cnic' => $request->cnic,
                'ntn' => $request->ntn,
                'strn' => $request->strn,
                'website' => $request->website,
                'country_id' => $request->country,
                'state_id' => $request->state,
                'city_id' => $request->city,
                'focal_person_name' => $request->focal_person_name,
                'focal_person_email' => $request->focal_person_email,
                'focal_person_phone' => $request->focal_person_phone,
                'focal_person_address' => $request->focal_person_address,
                'bank_name' => $request->bank_name,
                'bank_title' => $request->bank_title,
                'bank_account' => $request->bank_account,
                'gst' => $request->gst,
                'fuel' => $request->fuel,
                'vendor_token' => $vendor_number_format,
                'vendor_number' => $vendor_number,
                'remarks' => $request->remarks,
                'payment_mode' => $request->payment_mode,
                'ntn_buyer' => $request->ntn_buyer,
                'ntn_city' => $request->ntn_city,
                'complain_number' => $request->complain_number,
                'created_by' => Auth::user()->id,
                'payment' => $request->payment,
                'category' => $request->category,
            ];
            // dd($vendor);
            $saveVendor = Vendor::create($vendor);

            if(Auth::user()->hasAnyRole('admin','bdm','bd'))
            {
                $vendorObj = Vendor::find($saveVendor->id);
                $poc_obj = [
                    'poc' => $request->poc,
                    'datentime' => now(),
                    'poc_assigned_by' => Auth::user()->id,
                ];

                $vendorObj->update($poc_obj);

                $csr_obj = [
                    'csr' => $request->csr,
                    'csr_datentime' => now(),
                    'csr_assigned_by' => Auth::user()->id,
                ];

                $vendorObj->update($csr_obj);
                
                $pickup_obj = [
                    'pickup' => $request->pickup,
                    'pickup_datentime' => now(),
                    'pickup_assigned_by' => Auth::user()->id,
                ];

                $vendorObj->update($pickup_obj);
            }

            $vendorId = $saveVendor->id;
            //$vendorId = 1;
            //dump($saveVendor);
            $login = [
                'name' => $request->vendor_name,
                'email' => $request->login_email,
                'password' => Hash::make($request->login_password),
                'vendor_id' => $vendorId,
                'phone_number' => $request->focal_person_phone,
            ];

            if($request->has('pickupAddress'))
            {
                $names = $request->pickupAddress;
                //$vendorId = $vendor_new;
                
                foreach($names as $keyOne => $name)
                {
                    $data = [
                        'vendor_id' => $vendorId,
                        'address' => $names[$keyOne],
                        'status' => 1,
                    ];
                    
                    $location = WarehouseLocation::create($data);
                }
            }
            
            if($request->has('timing'))
            {
                $pickup_timing = $request->timing;
                $vendorTiming = $vendorId;
                
                foreach($pickup_timing as $keyOne => $pickup)
                {
                    $dataOne = [
                        'vendor_id' => $vendorTiming,
                        'timing_slot_id' => $pickup_timing[$keyOne],
                        'status' => 1,
                    ];
                    
                    $timing = VendorTiming::create($dataOne);
                }
            }

            //Add AHL weight
            if($request->has('vendorWeights'))
            {
                $addWeightInAHL = $request->vendorWeights;
                
                foreach($addWeightInAHL as $key => $weight)
                {

                    $dataOne = [
                        'weight' => $weight,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $ahlWeight = AhlWeight::create($dataOne);

                    $ahlWeightId[] = $ahlWeight->id;
                }
            }

            //Add Vendor Weight
            if($request->has('vendorWeightsPrice'))
            {
                $vendorWeighPrices = $request->vendorWeightsPrice;
                $city = $request->vendorWeightscity;
                $vendorWeightsMin = $request->vendorWeightsMin;
                $vendorWeightsMax = $request->vendorWeightsMax;
                    // dd($city);
                foreach($vendorWeighPrices as $key => $weightPrice)
                {
                    $dataOne = [
                        'vendor_id' => $vendorId,
                        'ahl_weight_id' => $ahlWeightId[$key],
                        'price' => $weightPrice,
                        'city_id' => $city[$key],
                        'min_weight' => $vendorWeightsMin[$key],
                        'max_weight' => $vendorWeightsMax[$key],
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $vendorWeight = VendorWeight::create($dataOne);
                }
            }
            
            //dd();
            $user = User::create($login);
            $user->assignRole('vendor_admin');
            
            DB::commit();
            // all good

        } catch (Throwable $e) {
            DB::rollback();

            report($e);
            return false;
        }
        

        return redirect()->route('vendorList');
    }

    public function getCities(Request $request)
    {
        if (!$request->id) {
            $html = '<option value="">Select City</option>';
        } else {
            $html = '';
            $cities = City::where('state_id', $request->id)->get();
            foreach ($cities as $city) {
                $html .= '<option value="' . $city->id . '">' . $city->name . '</option>';
            }
        }
        if (!empty($html))
            return response()->json(['html' => $html]);
        else {
            $html = '<option value="">Select City</option>';
            return response()->json(['html' => $html]);
        }
    }
    
    public function pickupRequestList()
    {
        $breadcrumbs = [
            'name' => 'Pickup Request', 
        ];

        $vendorId = Auth::user()->vendor_id;
        $pickup_requests = PickupRequest::where('vendor_id',$vendorId)->where('status',1)->get();
        
        return view('vendor.pickup_request', compact('pickup_requests','breadcrumbs'));
    }
    
    public function createPickup()
    {
        $breadcrumbs = [
            'name' => 'Create Pickup Request', 
        ];

        $vendorId = Auth::user()->vendor_id;
        $timing = VendorTiming::where('vendor_id', $vendorId)->get();
        $location = WarehouseLocation::where('vendor_id', $vendorId)->get();
        $cities = City::all();
        
        return view('vendor.create_pickup', compact('timing','location','vendorId','breadcrumbs','cities'));
    }
    
    public function generatePickupRequest(Request $request)
    {
        $validatedData = $request->validate([
            //company detail
            'vendor_id' => 'required',
            'pickup_date' => 'required',
            'estimated_parcel' => 'required|numeric',
            'time_slot' => 'required',
            'pickup_location' => 'required',
            'city' => 'required',
        ]);
        
        //dd($request->all());
        $pickup = [
            'vendor_id' => $request->vendor_id,
            'vendor_time_id' => $request->time_slot,
            'warehouse_location_id' => $request->pickup_location,
            'pickup_date' => $request->pickup_date,
            'estimated_parcel' => $request->estimated_parcel,
            'status' => 1,
            'remarks' => $request->remarks,
            'city_id' => $request->city,
        ];
        
        PickupRequest::create($pickup);
        
        return back();
    }    
    
    public function vendorPickupRequestList()
    {
        $breadcrumbs = [
            'name' => 'Vendor Pickup Request', 
        ];

        if(Auth::user()->hasAnyRole('admin'))
        {
            $pickup_requests = PickupRequest::orderBy('created_at', 'DESC')->where('status',1)->get();
        }
        elseif(Auth::user()->hasAnyRole('first_man'))
        {
            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

            $pickup_requests = PickupRequest::orderBy('created_at', 'DESC')->whereIn('city_id',$usercity)->where('status',1)->get();
        }

        // dd($pickup_requests);
        

        return view('admin.pickup_request.all_pickup_requests', compact('pickup_requests','breadcrumbs'));
    }
    
    public function vendorUsersList($id)
    {
        $breadcrumbs = [
            'name' => 'Vendor Staff', 
        ];

        $vendor = Vendor::find($id);
        $vendor_user = User::where('vendor_id', $vendor->id)->get();
        return view('admin.vendor.user_list', compact('vendor_user','vendor','breadcrumbs'));
    }
    
    public function createEditor($id)
    {
        $breadcrumbs = [
            'name' => 'Create Vendor Editor', 
        ];

        $vendor = Vendor::find($id);
        return view('admin.vendor.create_editor', compact('vendor','breadcrumbs'));
    }
    
    public function saveVendorEditor(Request $request)
    {
        $validatedData = $request->validate([
            //Login Credentials
            'vendor_id' => 'required',
            'user_name' => 'required',
            'login_email' => 'required|email|unique:users,email',
            'login_password' => 'required|min:6',
            'login_confirm_password' => 'required|same:login_password'
        ]);

        $login = [
            'name' => $request->user_name,
            'email' => $request->login_email,
            'password' => Hash::make($request->login_password),
            'vendor_id' => $request->vendor_id,
            'phone_number' => $request->phone_number,
        ];

        $user = User::create($login);
        $user->assignRole('vendor_editor');
        
        return back()->with('success','Vendor Editor Create Successfully!');
    }

    public function createStaff()
    {
        $breadcrumbs = [
            'name' => 'Create Staff', 
        ];
        if(Auth::user()->hasAnyRole('admin'))
        {
            $cities = City::all();
            $allowStaffList = AHLHelper::staffAllow();

            $roles = DB::table('roles')->whereIn('id',$allowStaffList)->get();
            //$roles = Role::whereIn('id',[4,5,6,7])->get();
        }
        elseif(Auth::user()->hasAnyRole('hr'))
        {
            $cities = City::all();
            $allowStaffList = AHLHelper::staffAllow();

            $roles = DB::table('roles')->whereIn('id',$allowStaffList)->get();
            //$roles = Role::whereIn('id',[4,5,6,7])->get();
        }
        elseif(Auth::user()->hasAnyRole('supervisor','lead_supervisor','first_man'))
        {
            $allowStaffList = AHLHelper::staffAllow();
            $roles = DB::table('roles')->whereIn('id',$allowStaffList)->get();

            $userId = Auth::user()->id;
            $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

            $cities  = City::whereIn('id', $usercity)->get();

        }
        return view('admin/staff/create_staff',compact('roles','breadcrumbs','cities'));
    }

    public function saveStaff(Request $request)
    {
        $rules = [
            //Login Credentials
            'staff_role' => 'required',
            'user_name' => 'required',
            'login_email' => 'required|email|unique:users,email',
            'user_id' => 'required|unique:users,user_id',
            'login_password' => 'required|min:6',
            'login_confirm_password' => 'required|same:login_password',
            //User Detail
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'cnic' => 'required',
            'salary' => 'required',
            'phone' => 'required',
            'staff_address' => 'nullable',
            //'vehicle' => 'required',
            //new detail
            'reporting_to' => 'required',
            'location' => 'required',
            'hiring_by' => 'required',
            'interviewed_by' => 'required',
            'hiring_platform' => 'required',
            'joining_date' => 'required',
            'company_assets' => 'required',
        ];
        
        // dd($request->all());
        $authUser = Auth::user();
        $authUserId = $authUser->id;
        $requestRole = $request->staff_role; 

        if($requestRole == 'picker' || $requestRole == 'rider'){
            $rules['vehicle'] = 'required';
            $rules['commission'] = 'required';
        }

        $validation = Validator::make($request->all(),$rules);

        if ($validation->fails()) {
            return redirect('/create-staff')
                ->withErrors($validation)
                ->withInput();
        }

        //first create user
        $login = [
            'name' => $request->user_name,
            'email' => $request->login_email,
            'user_id' => $request->user_id,
            'password' => Hash::make($request->login_password),
            'phone_number'  => $request->phone,
        ];

        $user = User::create($login);
        $userId = $user->id;
        $user->assignRole($request->staff_role);

        /*if($request->staff_role == 'financer'){
            $user->assignRole('admin');
        }else{
            $user->assignRole($request->staff_role);
        }*/

        //user Detail
        $userDetail = [
            'created_by' => $authUserId,
            'user_id' => $userId,
            'phone'  => $request->phone,
            'cnic' => $request->cnic,
            'vehicle' => $request->vehicle,
            'salary' => $request->salary,
            'commission' => $request->commission,
            'address' => $request->staff_address,
            'account_number' => $request->account_number,
            'account_title' => $request->account_title,
            'bank_name' => $request->bank_name,
            'reporting_to' => $request->reporting_to,
            'location' => $request->location,
            'hiring_by' => $request->hiring_by,
            'interviewed_by' => $request->interviewed_by,
            'hiring_platform' => $request->hiring_platform,
            'joining_date' => $request->joining_date,
            'leaving_date' => $request->leaving_date,
            'company_assets' => $request->company_assets,
            'remarks' => $request->remarks,
            
        ];
        // dd($userDetail);
        $userDetailModel = UserDetail::create($userDetail);
        // dd($userDetailModel);
        
        //add city or assign city
        $assignCityDetail = [
            'user_detail_id' => $userDetailModel->id,
            'assign_by' => $authUserId,
            'country_id' => $request->country,
            'state_id' => $request->state,
            'city_id' => $request->city,
        ];

        AssignCity::create($assignCityDetail);

        return redirect('/staff-list');
    }

    public function barcode(Request $request)
    {
        $validatedData = $request->validate([
            //Login Credentials
            'paracel_order_id' => 'required',
        ]);

        //$orderReferencce = '#AHL'.$request->paracel_order_id;

        $orderParcel = Order::where('order_reference',$request->paracel_order_id)->first();

        dd($orderParcel);
    }
    
    public function vendorEditorsList()
    {
        $breadcrumbs = [
            'name' => 'Vendor Staff List', 
        ];

        $vendor = Auth::user()->vendor_id;
        $vendor_user = User::where('vendor_id', $vendor)->get();
        return view('vendor.editor_list', compact('vendor_user','vendor','breadcrumbs'));
    }
    
    public function createVendorEditor()
    {
        $breadcrumbs = [
            'name' => 'Create Editor', 
        ];

        return view('vendor.create_editor',compact('breadcrumbs',));
    }
    
    public function saveVendorEditorStaff(Request $request)
    {
        $validatedData = $request->validate([
            //Login Credentials
            'vendor_id' => 'required',
            'user_name' => 'required',
            'login_email' => 'required|email|unique:users,email',
            'login_password' => 'required|min:6',
            'login_confirm_password' => 'required|same:login_password'
        ]);

        $login = [
            'name' => $request->user_name,
            'email' => $request->login_email,
            'password' => Hash::make($request->login_password),
            'vendor_id' => $request->vendor_id,
            'phone_number' => $request->phone_number,
        ];

        $user = User::create($login);
        $user->assignRole('vendor_editor');
        
        return redirect()->route('vendorEditorsList');
    }

    public function citiesIdList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Cities Lists', 
        ];

        if ($request->city) {
            $cityData = City::with([
                'state.country' => function($query){
                    $query->select('id','name');
                }
            ])
            ->where('id',$request->city)
            ->get();

            foreach ($cityData as $key => $data) {
                $city = [
                    'country_id' => $data->state->country->id,
                    'country_name' => $data->state->country->name,
                    'state_id' => $data->state->id,
                    'state_name' => $data->state->name,
                    'city_id' => $data->id,
                    'city_name' => $data->name,
                ];
            }

            return response()->json([
                'status' => 1,
                'city' => $city,
            ]);
        }

        return view('vendor/cities-ids-lists',compact('breadcrumbs'));
    }

    public function cityarea($id){
        
        $subareas = SubArea::where('city_id',$id)->get();
        
        return view('vendor.city-area-list',compact('subareas'));
    }

    public function completePickupRequest()
    {
        $breadcrumbs = [
            'name' => 'Complete Request List', 
        ];

        $authUser = Auth::user();
        $authUserVendorId = $authUser->vendor_id;

        if($authUserVendorId){
            $completePickupRequest = PickupRequest::where(['vendor_id' => $authUserVendorId,'status' => 3])->orderBy('id', 'DESC')->get();
        }else{
            $completePickupRequest = PickupRequest::where(['status' => 3])->get();
        }
        
        return view('vendor/complete-pickup-request-list',compact('completePickupRequest','breadcrumbs'));

    }

    public function pickupRequestScanParcelList(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Pickup Request Scan Parcel List', 
        ];

        $authUser = Auth::user();
        $authUserVendorId = $authUser->vendor_id;

        $pickupRequestId = $request->id;

        $scanOrders = ScanOrder::where('pickup_request_id',$pickupRequestId)->with('orderDetail')->get();
        
        $pickup_orders = ScanOrder::where('pickup_request_id',$pickupRequestId)->pluck('order_id');
//        dd($pickup_orders);
        $order_details = Order::whereIn('id', $pickup_orders)->sum('consignment_cod_price');

        return view('vendor/pickup-request-scan-parcel-list',compact('scanOrders','breadcrumbs', 'order_details'));
    }
    
    public function pickupRequestScanParcelListpdf(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Pickup Request Scan Parcel List', 
        ];

        $authUser = Auth::user();
        $authUserVendorId = $authUser->vendor_id;
//        dd($authUserVendorId);
        $vendor_detail = Vendor::where('id', $authUserVendorId)->first();
        $pickupRequestId = $request->id;
//        dd($pickupRequestId);

        $scanOrders = ScanOrder::where('pickup_request_id',$pickupRequestId)->with('orderDetail')->get();
//        dd($scanOrders);
        $picker_name = ScanOrder::where('pickup_request_id', $pickupRequestId)->select('picker_id')->first();
        if(!empty($picker_name))
        {
            $picker_detail = User::where('id', $picker_name->picker_id)->first();
            $pickup_name = $picker_detail->name;
        }
        else
        {
            $pickup_name = "AHL Rider";
        }
        
        $pickup_request = PickupRequest::where('id', $pickupRequestId)->first();
        $pickup_orders = ScanOrder::where('pickup_request_id',$pickupRequestId)->pluck('order_id');
//        dd($pickup_orders);
        $order_details = Order::whereIn('id', $pickup_orders)->sum('consignment_cod_price');
//        dd($order_details);
        
//        return view('vendor/complete-pickup-request-pdf-report',compact('scanOrders','breadcrumbs','pickup_name','vendor_detail','pickup_request','order_details'));

        $pdf = PDF::loadView('vendor/complete-pickup-request-pdf-report',compact('scanOrders','breadcrumbs','pickup_name','vendor_detail','pickup_request','order_details'))->setPaper('a4', 'landscape');
        $pdf_name =$vendor_detail->vendor_name."_Pickup_Parcels_Report_".date("Y_m_d_h_i_s").".pdf";
            // dd($pdf_name);
        return $pdf->download($pdf_name);
    }

    public function cancelByVendor(Request $request)
    {
        $parcelId = $request->paracel_id;

        $order = Order::find($parcelId);
        $order->update(['order_status' => 12]);
        return response()->json([
            'status' => 1, 
        ]);
        //return response()->json(['html' => $html]);
    }

    public function ahlPayReport()
    {
        $breadcrumbs = [
            'name' => 'AHL Pay Report', 
        ];
        
        $vendorId =  Auth::user()->vendor_id;
        //$selectedVendor = Vendor::where('id',$vendorId)->first();
        $vendorFinancialsReport = VendorFinancial::where('vendor_id',$vendorId)->with([
            'vendorName' => function($query){
                $query->select('id','vendor_name');
            },
            'cashierName' => function($query){
                $query->select('id','name');
            }
        ])
        ->orderBy('id','desc')
        ->get();
        
        return view('vendor.ahl-pay-report',compact('vendorFinancialsReport','breadcrumbs'));
    }

    public function generateTaxInvoice()
    {
        $breadcrumbs = [
            'name' => 'Vendor Tax Invoice', 
        ];

        return view('vendor.generate-invoice-report',compact('breadcrumbs'));
    }

    public function taxInvoiceDownload(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required',
        ]);
        
        $requestDate = $request->date;
        $vendorId = Auth::user()->vendor_id;

        $vendor = Vendor::select('id','vendor_name','vendor_email','vendor_address','ntn','gst')->where('id',$vendorId)->get();

        $ordersIds = Order::select('id','order_status','updated_at','created_at','vendor_id')
            //->where(['order_status'=>6,'vendor_id'=>$vendorId])//order status must be 6
            ->whereIn('order_status',[6,10])//order status must be 6 or 10 for return to vendor
            ->where(['vendor_id'=>$vendorId])//order status must be 6
            ->whereDate('updated_at',$requestDate)//created_at return must be updated_at
            ->get()
            ->pluck('id')
            ->toArray();
        
        $orders = Order::selectRaw('count(id) as count,vendor_weight_id,sum(consignment_cod_price) as total_parcel_amount')
            ->groupBy('vendor_weight_id')
            ->whereIn('id',$ordersIds)
            ->with([
                'vendorWeight' => function($query){
                    $query->select('id','price');
                }
            ])
            ->get()
            ->toArray();
        
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

        $financial = [
            'totalParcelAmount' => $totalParcelAmount,
        ];

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

        //$path = 'logo/ahl_logo.png';
        //$type = pathinfo($path, PATHINFO_EXTENSION);
        //$data = file_get_contents($path);
        //$logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        
        $title = 'Invoice List';
        // share data to view
        $pdf = PDF::loadView('tax-invoice', compact('vendor','invoiceData','invoiceTotal','taxRate','title','financial'));
        $fileName = date('m-d-y').'-'.$vendor[0]['vendor_name'].'-'.'invoice';
        // download PDF file with download method
        //return view()->share('middle_man.generate-reattempt-pdf',$orders);
        //return view('tax-invoice',compact('vendor','invoiceData','invoiceTotal','taxRate','title'));
        return $pdf->download($fileName.'.pdf');
    }

    public function shiperAdviser(Request $request)
    {
        $breadcrumbs = [
            'name' => 'Shiper Adviseer', 
        ];

        $authVendorId = Auth::user()->vendor_id;
        
        $shipper_advise = ShiperAdviser::all()->pluck('order_id');

        $orders = Order::where(['vendor_id'=>$authVendorId])->whereIn('order_status',[9])
        ->with([
            'vendor' => function($query){
                $query->select('id','vendor_name');
            },
            'orderStatus' => function($query){
                $query->select('id','name');
            },
            'shiperAdviser' => function($query){
                $query->select('id','order_id','advise','ahl_reply');
            },
        ])
        ->get();
        
        return view('vendor.shiper-adviser',compact('breadcrumbs','orders'));
    }

    public function shiperParcelAdvice(Request $request)
    {
        $parcelId = $request->id;

        if(array_key_exists("advise",$request->all()) && $request->advise == null){
            $validation = Validator::make($request->all(),[
                'advise' => 'required',
            ]);

            if($validation->fails()){
                $error = $validation->errors();
                return back()->withErrors($error)->withInput($request->all());
            }
        }

        if($request->advise && $request->order_id){

            $orderId = $request->order_id;
            $advise = $request->advise;
            $data = [
                'order_id' => $orderId,
                'advise' => $advise,
                'status' => 1,
            ];

            $addAdvise = ShiperAdviser::create($data);

            if($addAdvise){
                return redirect('vendor-shiper-adviser')->with(['success'=>'Parcel Advice Send Successfully']);
            }

        }else{
            $authVendorId = Auth::user()->vendor_id;
            $order = Order::select('id','vendor_id','order_reference')->whereId($parcelId)->where('vendor_id',$authVendorId)->first();
        }

        $breadcrumbs = [
            'name' => 'Parcel Advice of '.$order->order_reference, 
        ];

        return view('vendor.shiper-parcel-advice',compact('breadcrumbs','order'));
    }

    public function shiperParcelAdviceEdit(Request $request)
    {
        $parcelAdviceId = $request->advise_id;

        if(array_key_exists("advise",$request->all()) && $request->advise == null){
            $validation = Validator::make($request->all(),[
                'advise' => 'required',
            ]);

            if($validation->fails()){
                $error = $validation->errors();
                return back()->withErrors($error)->withInput($request->all());
            }
        }

        if($request->advise && $request->shiper_advise_id){
            
            $orderShiperAdviseId = $request->shiper_advise_id;
            $advise = $request->advise;
            $data = [
                'advise' => $advise,
            ];

            $updateAdvise = ShiperAdviser::whereId($orderShiperAdviseId)->update($data);

            if($updateAdvise){
                return redirect('vendor-shiper-adviser')->with(['success'=>'Parcel Advise Update Successfully']);
            }

        }else{
            $shiperAdviser = ShiperAdviser::where('id',$parcelAdviceId)->with([
                'Order' => function($query){
                    $query->select('id','order_reference');
                }
            ])->first();
        }

        return view('vendor.shiper-advise-edit',compact('shiperAdviser'));
    }

    public function viewProfile()
    {
        $breadcrumbs = [
            'name' => 'Vendor Profile', 
        ];

        $user = Auth::user();
        $authVendorId = $user->vendor_id;
        $vendorPickupLocations = WarehouseLocation::whereVendorId($authVendorId)->get();
        $vendorWeights = VendorWeight::where('vendor_id',$authVendorId)->get();
        $vendor_details = Vendor::where('id', $authVendorId)->first();
        return view('vendor/profile',compact('vendorPickupLocations','vendorWeights','breadcrumbs','vendor_details'));
    }

    public function savePrintingSlip(Request $request)
    {
        $vendor_detail = Vendor::find($request->vendor_id);
        $vendorData = [
            'printing_slips'=> $request->printing_slip,
        ];

        $vendor_detail->update($vendorData);

        return redirect()->back();
    }

    public function updateEditor($id)
    {
        $breadcrumbs = [
            'name' => 'Update Vendor Editor', 
        ];

        $vendor = User::find($id);
        return view('admin.vendor.update_editor', compact('vendor','breadcrumbs'));
    }
    
    public function updateVendorEditor(Request $request)
    {
        $password = $request->login_password;
        $find_user = User::find($request->user_id);

        $login = [
            'name' => $request->user_name,
            'phone_number' => $request->phone_number,
        ];

        if($password){
            $login = [
                'name' => $request->user_name,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($password),
            ];
        }
        else
        {
            $login = [
                'name' => $request->user_name,
                'phone_number' => $request->phone_number,
            ];
        }

        $find_user->update($login);
        
        return back()->with('success','Vendor Editor Updated Successfully!');
    }

    public function vendorUpdateEditor($id)
    {
        $breadcrumbs = [
            'name' => 'Update Vendor Editor', 
        ];

        $vendor = User::find($id);
        return view('vendor.update-editor', compact('vendor','breadcrumbs'));
    }
    
    public function updateVendorSideEditor(Request $request)
    {
        $password = $request->login_password;
        $find_user = User::find($request->user_id);

        if($password){
            $login = [
                'name' => $request->user_name,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($password),
            ];
        }
        else
        {
            $login = [
                'name' => $request->user_name,
                'phone_number' => $request->phone_number,
            ];
        }

        $find_user->update($login);
        
        return back()->with('success','Vendor Editor Updated Successfully!');
    }

    //Vendor Assign POC n CSR
    public function salesStaffList()
    {
        $breadcrumbs = [
            'name' => 'Assign Sales Staff To Vendor', 
        ];

        $sales_staff= User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->whereIn('name', ['bd','bdm']);
            }
        )
        ->get();
        
        return view('admin.vendor.POC.index',compact('sales_staff','breadcrumbs'));
    }

    public function assignSale($id)
    {
        // dd($id);
        $breadcrumbs = [
            'name' => 'Assign Sales Staff To Vendor', 
        ];

        $sale = User::find($id);
        $saleId = $sale->id;
        // $vendors = Vendor::where('status',1)->where('poc', '=', Null)->get();
        $vendors = Vendor::where('status', 1)
            ->where(function($query) use ($id) {
                $query->whereNull('poc')
                    ->orWhere('poc', $id);
            })
            ->get();

        $assignedVendorIds = Vendor::where('poc', $id)->pluck('id')->toArray();

        return view('admin.vendor.POC.assign_sale', compact('breadcrumbs','sale','saleId','vendors','assignedVendorIds'));
    }

    public function saveAssignSale(Request $request)
    {
        $selectedVendorIds = $request->vendor_id;
        $saleStaffId = $request->sale_staff;

        $currentlyAssignedVendors = Vendor::where('status', 1)->where('poc', $saleStaffId)->pluck('id')->toArray();

        // Vendors that are newly selected but were not previously assigned
        $vendorsToAssign = array_diff($selectedVendorIds, $currentlyAssignedVendors);

        // Vendors that were previously assigned but are now deselected
        $vendorsToUnassign = array_diff($currentlyAssignedVendors, $selectedVendorIds);

        $poc_obj = [
            'poc' => $saleStaffId,
            'datentime' => now(),
            'poc_assigned_by' => Auth::user()->id,
        ];

        $un_poc_obj = [
            'poc' => null,
            'datentime' => null,
            'poc_assigned_by' => null,
        ];

        Vendor::whereIn('id', $vendorsToAssign)->update($poc_obj);
        Vendor::whereIn('id', $vendorsToUnassign)->update($un_poc_obj);

        return back()->with(['flash'=>'success','flash_message'=> 'Vendor Assign To Sales!','flash_alert'=>'success']);        
    }

    //csr
    public function csrStaffList()
    {
        $breadcrumbs = [
            'name' => 'Assign CSR Staff To Vendor', 
        ];

        $csr_staff= User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'csr');
            }
        )
        ->get();
        
        return view('admin.vendor.CSR.index',compact('csr_staff','breadcrumbs'));
    }

    public function assignCSR($id)
    {
        // dd($id);
        $breadcrumbs = [
            'name' => 'Assign CSR Staff To Vendor', 
        ];

        $csr = User::find($id);
        $csrId = $csr->id;
        // $vendors = Vendor::where('status',1)->where('poc', '=', Null)->get();
        $vendors = Vendor::where('status', 1)
            ->where(function($query) use ($id) {
                $query->whereNull('csr')
                    ->orWhere('csr', $id);
            })
            ->get();

        $assignedVendorIds = Vendor::where('csr', $id)->pluck('id')->toArray();

        return view('admin.vendor.CSR.assign_csr', compact('breadcrumbs','csr','csrId','vendors','assignedVendorIds'));
    }

    public function saveAssignCSR(Request $request)
    {
        $selectedVendorIds = $request->vendor_id;
        $csrStaffId = $request->csr_staff;

        $currentlyAssignedVendors = Vendor::where('status', 1)->where('csr', $csrStaffId)->pluck('id')->toArray();

        // Vendors that are newly selected but were not previously assigned
        $vendorsToAssign = array_diff($selectedVendorIds, $currentlyAssignedVendors);

        // Vendors that were previously assigned but are now deselected
        $vendorsToUnassign = array_diff($currentlyAssignedVendors, $selectedVendorIds);

        $csr_obj = [
            'csr' => $csrStaffId,
            'csr_datentime' => now(),
            'csr_assigned_by' => Auth::user()->id,
        ];

        $un_csr_obj = [
            'csr' => null,
            'csr_datentime' => null,
            'csr_assigned_by' => null,
        ];

        Vendor::whereIn('id', $vendorsToAssign)->update($csr_obj);
        Vendor::whereIn('id', $vendorsToUnassign)->update($un_csr_obj);

        return back()->with(['flash'=>'success','flash_message'=> 'Vendor Assign To CSR!','flash_alert'=>'success']);        
    }

    //pickup
    public function pickupStaffList()
    {
        $breadcrumbs = [
            'name' => 'Assign Pickup Supervisor Staff To Vendor', 
        ];

        $firstman_staff= User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'first_man');
            }
        )
        ->get();
        
        return view('admin.vendor.firstman.index',compact('firstman_staff','breadcrumbs'));
    }

    public function assignPickup($id)
    {
        // dd($id);
        $breadcrumbs = [
            'name' => 'Assign Pickup Supervisor Staff To Vendor', 
        ];

        $pickup = User::find($id);
        $pickupId = $pickup->id;
        // $vendors = Vendor::where('status',1)->where('poc', '=', Null)->get();
        $vendors = Vendor::where('status', 1)
            ->where(function($query) use ($id) {
                $query->whereNull('pickup')
                    ->orWhere('pickup', $id);
            })
            ->get();

        $assignedVendorIds = Vendor::where('pickup', $id)->pluck('id')->toArray();

        return view('admin.vendor.firstman.assign_pickup', compact('breadcrumbs','pickup','pickupId','vendors','assignedVendorIds'));
    }

    public function saveAssignPickup(Request $request)
    {
        $selectedVendorIds = $request->vendor_id;
        $pickupStaffId = $request->pickup_staff;

        $currentlyAssignedVendors = Vendor::where('status', 1)->where('pickup', $pickupStaffId)->pluck('id')->toArray();

        // Vendors that are newly selected but were not previously assigned
        $vendorsToAssign = array_diff($selectedVendorIds, $currentlyAssignedVendors);

        // Vendors that were previously assigned but are now deselected
        $vendorsToUnassign = array_diff($currentlyAssignedVendors, $selectedVendorIds);

        $pickup_obj = [
            'pickup' => $pickupStaffId,
            'pickup_datentime' => now(),
            'pickup_assigned_by' => Auth::user()->id,
        ];

        $un_pickup_obj = [
            'pickup' => null,
            'pickup_datentime' => null,
            'pickup_assigned_by' => null,
        ];

        Vendor::whereIn('id', $vendorsToAssign)->update($pickup_obj);
        Vendor::whereIn('id', $vendorsToUnassign)->update($un_pickup_obj);

        return back()->with(['flash'=>'success','flash_message'=> 'Vendor Assign To Pickup Supervisor!','flash_alert'=>'success']);        
    }

    //first_man vendors list
    public function pickupAssignedVendors()
    {
        $breadcrumbs = [
            'name' => 'Pickup Supervisor Vendors List', 
        ];
        $userId = Auth::user()->id;

        $currentlyAssignedVendors = Vendor::where('status', 1)->where('pickup', $userId)->get();

        return view('admin.vendor.firstman.vendor_list',compact('currentlyAssignedVendors','breadcrumbs'));
    }

    //csr vendors list
    public function csrAssignedVendors()
    {
        $breadcrumbs = [
            'name' => 'CSR Vendors List', 
        ];
        $userId = Auth::user()->id;

        $currentlyAssignedVendors = Vendor::where('status', 1)->where('csr', $userId)->get();

        return view('admin.vendor.CSR.vendor_list',compact('currentlyAssignedVendors','breadcrumbs'));
    }

    //sales vendors list
    public function salesAssignedVendors()
    {
        $breadcrumbs = [
            'name' => 'Sales Vendors List', 
        ];
        $userId = Auth::user()->id;

        $currentlyAssignedVendors = Vendor::where('status', 1)->where('poc', $userId)->get();

        return view('admin.vendor.poc.vendor_list',compact('currentlyAssignedVendors','breadcrumbs'));
    }

    //supervisor
    public function supervisorStaffList()
    {
        $breadcrumbs = [
            'name' => 'Assign Rider Staff To Supervisor', 
        ];

        $staff_details= User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'supervisor');
            }
        )
        ->get();
        
        return view('admin.staff.supervisor.index',compact('staff_details','breadcrumbs'));
    }

    public function assignSupervisor($id)
    {
        // dd($id);
        $breadcrumbs = [
            'name' => 'Assign Rider Staff To Supervisor', 
        ];

        $user_data = User::find($id);
        $userId = $user_data->id;
        $riders = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'rider');
            }
        )->where(function($query) use ($id) {
             $query->whereNull('supervisor_id')
                 ->orWhere('supervisor_id', $id);
        })
        ->get();

        $assignedRiderIds = User::where('supervisor_id', $id)->pluck('id')->toArray();

        return view('admin.staff.supervisor.assign_staff', compact('breadcrumbs','user_data','userId','riders','assignedRiderIds'));
    }

    public function saveAssignSupervisor(Request $request)
    {
        $selectedRidersIds = $request->rider_id;
        $staff_id = $request->staff_id;

        $currentlyAssignedRiders = User::where('status', 1)->where('supervisor_id', $staff_id)->pluck('id')->toArray();

        // Riders that are newly selected but were not previously assigned
        $ridersToAssign = array_diff($selectedRidersIds, $currentlyAssignedRiders);

        // Riders that were previously assigned but are now deselected
        $ridersToUnassign = array_diff($currentlyAssignedRiders, $selectedRidersIds);

        $rider_obj = [
            'supervisor_id' => $staff_id,
            'sup_datentime' => now(),
            'sup_assigned_by' => Auth::user()->id,
        ];

        $un_rider_obj = [
            'supervisor_id' => null,
            'sup_datentime' => null,
            'sup_assigned_by' => null,
        ];

        User::whereIn('id', $ridersToAssign)->update($rider_obj);
        User::whereIn('id', $ridersToUnassign)->update($un_rider_obj);

        return back()->with(['flash'=>'success','flash_message'=> 'Riders Assign To Supervisor!','flash_alert'=>'success']);        
    }

    //picker supervisor
    public function pickerStaffList()
    {
        $breadcrumbs = [
            'name' => 'Assign Picker Staff To Picker Supervisor', 
        ];

        $staff_details= User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'first_man');
            }
        )
        ->get();
        
        return view('admin.staff.picker.index',compact('staff_details','breadcrumbs'));
    }

    public function assignPickerStaff($id)
    {
        // dd($id);
        $breadcrumbs = [
            'name' => 'Assign Picker Staff To Picker Supervisor', 
        ];

        $user_data = User::find($id);
        $userId = $user_data->id;
        $riders = User::where('status', 1)->whereHas(
            'roles', function($q){
                $q->where('name', 'picker');
            }
        )->where(function($query) use ($id) {
             $query->whereNull('picker_id')
                 ->orWhere('picker_id', $id);
        })
        ->get();

        $assignedRiderIds = User::where('picker_id', $id)->pluck('id')->toArray();

        return view('admin.staff.picker.assign_staff', compact('breadcrumbs','user_data','userId','riders','assignedRiderIds'));
    }

    public function saveAssignPickerStaff(Request $request)
    {
        $selectedRidersIds = $request->rider_id;
        $staff_id = $request->staff_id;

        $currentlyAssignedRiders = User::where('status', 1)->where('picker_id', $staff_id)->pluck('id')->toArray();

        // Riders that are newly selected but were not previously assigned
        $ridersToAssign = array_diff($selectedRidersIds, $currentlyAssignedRiders);

        // Riders that were previously assigned but are now deselected
        $ridersToUnassign = array_diff($currentlyAssignedRiders, $selectedRidersIds);

        $rider_obj = [
            'picker_id' => $staff_id,
            'pickup_datentime' => now(),
            'pickup_assigned_by' => Auth::user()->id,
        ];

        $un_rider_obj = [
            'picker_id' => null,
            'pickup_datentime' => null,
            'pickup_assigned_by' => null,
        ];

        User::whereIn('id', $ridersToAssign)->update($rider_obj);
        User::whereIn('id', $ridersToUnassign)->update($un_rider_obj);

        return back()->with(['flash'=>'success','flash_message'=> 'Riders Assign To Supervisor!','flash_alert'=>'success']);        
    }

    //supervisor riders list
    public function supervisorAssignedRiders()
    {
        $breadcrumbs = [
            'name' => 'Supervisor Riders List', 
        ];
        $userId = Auth::user()->id;

        $currentlyAssignedRiders = User::where('status', 1)->where('supervisor_id', $userId)->get();

        return view('admin.staff.supervisor.rider_list',compact('currentlyAssignedRiders','breadcrumbs'));
    }

    //pickup supervisor riders list
    public function pickupAssignedRiders()
    {
        $breadcrumbs = [
            'name' => 'Pickup Supervisor Riders List', 
        ];
        $userId = Auth::user()->id;

        $currentlyAssignedRiders = User::where('status', 1)->where('picker_id', $userId)->get();

        return view('admin.staff.picker.rider_list',compact('currentlyAssignedRiders','breadcrumbs'));
    }
}
