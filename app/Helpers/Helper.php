<?php

namespace app\Helpers;

use Log;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Collection;

use App\Models\User;
use App\Models\AhlTimings;
use App\Models\AhlWeight;
use App\Models\VendorWeight;
use App\Models\State;
use App\Models\Order;
use App\Models\ScanOrder;
use App\Models\OrderAssigned;
use App\Models\VendorFinancial;
use App\Models\StaffFinancial;
use App\Models\RiderCashCollection;
use App\Models\Vendor;
use App\Models\UserCity;
use App\Models\TagLine;

use DateTime;

//use Illuminate\Support\Facades\Config;


class Helper
{
    public static function orderReference($value)
    {
        $orderReference = str_pad($value, 8, "0", STR_PAD_LEFT);

        return strtoupper($orderReference);
    }

    public static function encrypt($string)
    {
        return $encrypted = Crypt::encrypt($string);
    }

    public static function decrypt($string)
    {
        return $decrypted  = Crypt::decrypt($string);
    }

    public static function genrateOrderReference() {
        $mt = explode(' ', microtime());
        $genrateMT = ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
        $random = mt_rand(10000, 99999);
        $latestOrder = Order::orderBy('created_at','DESC')->first();
        $orderReference = $latestOrder->id + $genrateMT + $random;
        return $orderReference;
    }

    /*public static function genrateOrderReference()
    {
        $d = new DateTime();
        return $d->format("Y-m-d H:i:s.v"); // v : Milliseconds
    }*/

    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    public static function getCountry()
    {
        return DB::table('countries')
            ->where('id', 166)
            ->get();
    }

    public static function getStates()
    {
        return DB::table('states')
            ->where('country_id', 166)
                ->where('id', 2728)
            ->where('status', 1)->get();
    }

    public static function getCities()
    {
        return DB::table('cities')->get();
    }
    
    public static function regions()
    {
        $regions = DB::table('regions')->get();

        return $regions;
    }
    
    
    public static function todayOrder()
    {
        if(date("H") >= 10){
            //dayOne is current day
            //dayTwo is next day
            $startDate = date("Y-m-d").' '.'10:00:00';
            $dateStrTime = strtotime("1 day", strtotime($startDate));
            $date = date("Y-m-d", $dateStrTime);
            $endDate = $date.' '.'06:00:00';   
        }else{
            //dayTwo is current date
            //dayOne date is previous date
            $endDate = date("Y-m-d").' '.'06:00:00';
            $dateStrTime = strtotime("-1 day", strtotime($endDate));
            $date = date("Y-m-d", $dateStrTime);
            $startDate = $date.' '.'10:00:00';
        }

        $order = ['startDate'=>$startDate,'endDate'=>$endDate];
        return $order;
    }

    public static function bulkFormat()
    {
        return [
            'consignee_first_name' => 'Consignee first name',
            'consignee_last_name' => 'Consignee last name',
            'consignee_email' => 'Consignee email',
            'consignee_address' => 'Consignee address',
            'consignee_phone' => 'Consignee phone number',
            'consignee_city' => 'Consignee city',
            'consignment_order_id' => 'Vendor parcel id or sku',
            'consignment_order_type' => 'Order type for this parcel i.e COD/NONCOD',
            'consignment_cod_price' => 'Total amount of a parcel',
            //'consignment_weight' => 'Total weight of a parcel',
            'vendor_weight_id' => 'Select your weight id from vendor weight ids section',
            'consignment_pieces' => 'Quantity in a parcel',
            'consignment_description' => 'Description of a parcel',
            'consignment_origin_city' => 'Parcel origin city',
            'pickup_location' => 'Vendor pickup location id',
        ];
    }

    public static function bulkFormatHeading()
    {
        return [
            'consignee_first_name',
            'consignee_last_name',
            'consignee_email',
            'consignee_address',
            'consignee_phone',
            'consignee_city',
            'consignment_order_id',
            'consignment_order_type',
            'consignment_cod_price',
            //'consignment_weight',
            'vendor_weight_id',
            'consignment_pieces',
            'consignment_description',
            'consignment_origin_city',
            'pickup_location_id',
        ];
    }

    public static function status($statusId)
    {
        switch ($statusId) {
            case 1:
                $status = 'Active';
                break;
            case 2:
                $status = 'In Active';
                break;
            
            default:
                # code...
                break;
        }

        return $status;   
    }

    public static function statuses($statusId)
    {
        switch ($statusId) {
            case 1:
                $status = 'Awaiting Pickup';
                $color = 'Active';
                break;
            case 2:
                $status = 'Pickup';
                $color = 'In Active';
                break;
            case 3:
                $status = 'At AHL Warehouse';
                $color = 'Active';
                break;
            case 4:
                $status = 'Dispatched';
                $color = 'In Active';
                break;
            case 5:
                $status = 'Delivered';
                $color = 'Active';
                break;
            case 6:
                $status = 'Request For Reattempt';
                $color = 'In Active';
                break;
            case 7:
                $status = 'Reattempt';
                $color = 'Active';
                break;
            case 8:
                $status = 'Cancelled';
                $color = 'In Active';
                break;
            case 9:
                $status = 'Returned To Vendor';
                $color = 'Active';
                break;
            case 10:
                $status = 'Cancelled By AHL';
                $color = 'In Active';
                break;
            case 11:
                $status = 'Cancelled By Vendor';
                $color = 'Active';
                break;
            case 12:
                $status = 'Void Label';
                $color = 'In Active';
                break;
            
            default:
                # code...
                break;
        }


        return [
            'status' => $status,
            'color' => $color
        ];
        
    }

    public static function ahlTimings()
    {
        return AhlTimings::all();
    }

    public static function ahlWeights()
    {
        return AhlWeight::all();
    }

    public static function overallParcelSum($vendorId,$dateFrom = '',$dateTo = '')
    {
        if($dateFrom && $dateTo){
            $overallParcelSum = Order::where('vendor_id', $vendorId)->whereDate('updated_at','>=',$dateFrom)->whereDate('updated_at','<=',$dateTo)->sum('consignment_cod_price');
        }else{

            $overallParcelSum = Order::where('vendor_id', $vendorId)->whereNotIn('order_status',[9,11,12])->sum('consignment_cod_price');
        }
        
        return $overallParcelSum;
    }

    public static function deliveredParcelSum($vendorId,$dateFrom = '',$dateTo = '')
    {
        if($dateFrom && $dateTo){
            $parcels = OrderAssigned::whereDate('created_at','>=',$dateFrom)->whereDate('created_at','<=',$dateTo)->where('status',1)->where('trip_status_id',4)->pluck('order_id');
            // dd($parcels);
            $deliveredParcelSum = Order::whereIn('id', $parcels)->whereIn('order_status',[6,14])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }else{

            $deliveredParcelSum = Order::whereIn('order_status',[6,14])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }
        
        return $deliveredParcelSum;
    }

    public static function cancelledParcelSum($vendorId,$dateFrom = '',$dateTo = '')
    {
        if($dateFrom && $dateTo){
            $parcels = OrderAssigned::whereDate('created_at','>=',$dateFrom)->whereDate('created_at','<=',$dateTo)->where('status',0)->where('trip_status_id', 5)->pluck('order_id');
            $cancelledParcelSum = Order::whereIn('id', $parcels)->whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }else{

            $cancelledParcelSum = Order::whereIn('order_status', [9,11,12])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }

        
        return $cancelledParcelSum; 
    }
    
    public static function pendingParcelSum($vendorId)
    {
        $pendingParcelSum = Order::whereNotIn('order_status', [6,9,11,12])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        
        return $pendingParcelSum; 
    }

    public static function reattemptParcelSum($vendorId,$dateFrom = '',$dateTo = '')
    {
        if($dateFrom && $dateTo){
            $reattemptParcelSum = Order::whereIn('order_status', [7])->where('vendor_id', $vendorId)->whereDate('updated_at','>=',$dateFrom)->whereDate('updated_at','<=',$dateTo)->sum('consignment_cod_price');
        }else{

           $reattemptParcelSum = Order::whereIn('order_status', [7])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }

        return $reattemptParcelSum; 
    }

    public static function returnToVendorParcelSum($vendorId,$dateFrom = '',$dateTo = '')
    {
        if($dateFrom && $dateTo){
            $parcels = OrderAssigned::whereDate('created_at','>=',$dateFrom)->whereDate('created_at','<=',$dateTo)->where('status',0)->where('trip_status_id', 5)->pluck('order_id');
            $returnToVendorParcelSum = Order::whereIn('id', $parcels)->whereIn('order_status', [10])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }else{

           $returnToVendorParcelSum = Order::whereIn('order_status', [10])->where('vendor_id', $vendorId)->sum('consignment_cod_price');
        }
        
        return $returnToVendorParcelSum; 
    }

    //this functino is excute when admin filter date and vendor
    //this function is not excute when admin not set filter
    //this else condition only worked when vendor login
    public static function ahlCommissionParcelSum($vendorId,$from = '',$to = '')
    {
        $check_vendor = Vendor::find($vendorId);

        if($check_vendor->commision == 1)
        {
            if($from && $to){
                $parcels = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',0)->where('trip_status_id', 5)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $result_parcel = $collapsed->all();
                $ahlCommissionParcels = Order::whereIn('id', $result_parcel)->selectRaw('count(id) as count, vendor_weight_id,order_status')
                ->groupBy('vendor_weight_id','order_status')
                ->whereIn('order_status', [6,10,9,13,14])
                ->where('vendor_id',$vendorId)
                ->get();

            }else{
                $ahlCommissionParcels = Order::selectRaw('count(id) as count, vendor_weight_id,order_status')
                ->groupBy('vendor_weight_id','order_status')
                ->whereIn('order_status', [6,10,9,13,14])
                ->where('vendor_id',$vendorId)
                ->get();

            }
        }
        else {
            if($from && $to){
                $parcels = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',0)->where('trip_status_id', 5)->pluck('order_id');
                $collection = collect([$parcels,$returned_parcels]);
                $collapsed = $collection->collapse();
                $result_parcel = $collapsed->all();
                $ahlCommissionParcels = Order::whereIn('id', $result_parcel)->selectRaw('count(id) as count, vendor_weight_id,order_status')
                ->groupBy('vendor_weight_id','order_status')
                ->whereIn('order_status', [6,13,14])
                ->where('vendor_id',$vendorId)
                ->get();

            }else{
                $ahlCommissionParcels = Order::selectRaw('count(id) as count, vendor_weight_id,order_status')
                ->groupBy('vendor_weight_id','order_status')
                ->whereIn('order_status', [6,13,14])
                ->where('vendor_id',$vendorId)
                ->get();

            }
        }
        

        $vendorWeight = VendorWeight::select(['id','price','ahl_weight_id','vendor_id'])->where('vendor_id',$vendorId)->get()->toArray();
        //dump($ahlCommissionParcels->toArray());
        foreach ($vendorWeight as $key => $weight) {
            $weightArr[$weight['id']] = $weight;
        }
        //dump($weightArr);
        if(count($ahlCommissionParcels) > 0 && $vendorWeight){
            foreach ($ahlCommissionParcels as $key => $ahlCommissionParcel) {
                $commissionArr[] = $ahlCommissionParcel->count * $weightArr[$ahlCommissionParcel->vendor_weight_id]['price'];
            }
        }else{$commissionArr[] = 0;}
        //dump($commissionArr);
        return $result = array_sum($commissionArr); 
    }

    public static function ahlCommissionParcelSumNew($vendorId,$from = '',$to = '')
    {
        $check_vendor = Vendor::find($vendorId);

        if($check_vendor->commision == 1)
        {
            if($from && $to){
                $orders_assigned = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$orders_assigned,$returned_parcels]);
                $collapsed = $collection->collapse();
                $parcels = $collapsed->all();

                $ahlCommissionParcels = Order::whereIn('id', $parcels)
                ->whereIn('order_status', [6,10,9,13,14,19])
                ->where('vendor_id',$vendorId)
                ->sum('vendor_weight_price');

            }else{
                $ahlCommissionParcels = Order::whereIn('order_status', [6,10,9,13,14,19])
                ->where('vendor_id',$vendorId)
                ->sum('vendor_weight_price');

            }
        }
        else {
            if($from && $to){
                $orders_assigned = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
                $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
                $collection = collect([$orders_assigned,$returned_parcels]);
                $collapsed = $collection->collapse();
                $parcels = $collapsed->all();
                $ahlCommissionParcels = Order::whereIn('id', $parcels)
                ->whereIn('order_status', [6,10,9,13,14,19])
                ->where('vendor_id',$vendorId)
                ->sum('vendor_weight_price');

            }else{
                $ahlCommissionParcels = Order::whereIn('order_status', [6,10,9,13,14,19])
                ->where('vendor_id',$vendorId)
                ->sum('vendor_weight_price');

            }
        }

        return $result = $ahlCommissionParcels; 
    }

    public static function ahlFuelCalculation($vendorId,$from = '',$to = '')
    {
        $check_vendor = Vendor::find($vendorId);

        if($from && $to){
            $orders_assigned = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
            $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
            $collection = collect([$orders_assigned,$returned_parcels]);
            $collapsed = $collection->collapse();
            $parcels = $collapsed->all();
            $ahlFuelAmount = Order::whereIn('id', $parcels)
            ->whereIn('order_status', [6,10,9,13,14,19])
            ->where('vendor_id',$vendorId)
            ->sum('vendor_fuel_price');

        }else{
            $ahlFuelAmount = Order::whereIn('order_status', [6,10,9,13,14,19])
            ->where('vendor_id',$vendorId)
            ->sum('vendor_fuel_price');

        }

        return $result = $ahlFuelAmount; 
    }

    public static function ahlGSTCalculation($vendorId,$from = '',$to = '')
    {
        $check_vendor = Vendor::find($vendorId);

        if($from && $to){
            $orders_assigned = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
            $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
            $collection = collect([$orders_assigned,$returned_parcels]);
            $collapsed = $collection->collapse();
            $parcels = $collapsed->all();
            $ahlFuelAmount = Order::whereIn('id', $parcels)
            ->whereIn('order_status', [6,10,9,13,14,19])
            ->where('vendor_id',$vendorId)
            ->sum('vendor_tax_price');

        }else{
            $ahlFuelAmount = Order::whereIn('order_status', [6,10,9,13,14,19])
            ->where('vendor_id',$vendorId)
            ->sum('vendor_tax_price');

        }

        return $result = $ahlFuelAmount; 
    }

    public static function payableToVendor($data)
    {
        $taxRate = $data['vendorGst'];
        //dump('payable tax '.$taxRate);
        $taxAmount = $data['ahlCommissionParcelSum']*$taxRate/100;
        
        //new 
        if(isset($data['notFilterAhlCommissionParcelSum'])){
            $notFilterTaxAmount = $data['notFilterAhlCommissionParcelSum']*$taxRate/100;
        }
        
        $filterPayableToVendor = $data['deliveredParcelSum'] - ($data['ahlCommissionParcelSum']);
        
        //new
        if(isset($data['notFilterDeliveredParcelSum'])){
            $notFilterPayableToVendor = $data['notFilterDeliveredParcelSum'] - ($data['notFilterAhlCommissionParcelSum'] + $notFilterTaxAmount);
        }
        

        $VendorFinancial = VendorFinancial::select(
            DB::raw("SUM(amount) as total_pay_amount"),
            DB::raw("SUM(ahl_commission) as total_ahl_commission_deduction")
        )
        ->where('vendor_id',$data['vendorId'])
        ->groupBy("vendor_id")
        ->get();
        //dump('financials '.$VendorFinancial);
        if(count($VendorFinancial) > 0){
            $total_pay_amount = $VendorFinancial[0]->total_pay_amount;
            $total_ahl_commission_deduction = $VendorFinancial[0]->total_ahl_commission_deduction;
            
            if(isset($data['flag']) && $data['flag'] == 'vendor_dashboard'){
                $filterPayableToVendor = $filterPayableToVendor - $total_pay_amount;
            }

            //new
            if(isset($notFilterPayableToVendor))
            {
                // $notFilterPayableToVendor = $notFilterPayableToVendor - $total_pay_amount - $VendorFinancial[0]->total_ahl_commission_deduction;
                $notFilterPayableToVendor = $notFilterPayableToVendor - $total_pay_amount;
            }
            // dd('payable vendor '.$notFilterPayableToVendor);
        }else{
            $total_pay_amount = 0;
            $total_ahl_commission_deduction = 0;
        }

        return $payable = [
            'total_pay_amount' => $total_pay_amount,
            'total_ahl_commission_deduction' => $total_ahl_commission_deduction,
            'filter_payable_to_vendor' => $filterPayableToVendor,
            'payableToVendor' => $filterPayableToVendor,
            'notFilterPayableToVendor' => isset($notFilterPayableToVendor) ? $notFilterPayableToVendor : '',
            'taxAmount' => $taxAmount,
        ];
    }

    public static function newPayableToVendor($data,$ahlCommission)
    {
        $vendorGst =  $data['gst'];
        $vendorTotalPay = $data['vendor_total_pay_amount'];
        $countVendorFinancials = $data['count_vendor_financials'];
        $vendorId = $data['vendor_id'];
        $taxAmount = $ahlCommission[$vendorId]['ahl_orders_commission'] * $vendorGst/100;
        $payableToVendor = $ahlCommission[$vendorId]['vendor_delivered_orders_amount'] - ($ahlCommission[$vendorId]['ahl_orders_commission'] + $taxAmount);
        
        if($countVendorFinancials > 0 && $ahlCommission[$vendorId]['vendor_delivered_orders_amount'] > 0){

            $total_pay_amount = $vendorTotalPay;
            $payableToVendor = $payableToVendor - $total_pay_amount;
        }else{
            $total_pay_amount = 0;
        }

        /*return $payable = [
            'total_pay_amount' => $total_pay_amount,
            'payableToVendor' => (int) $payableToVendor,
        ];*/

        //return $payableToVendor;
        return (int) $payableToVendor;
    }

    public static function staffCommission($staff)
    {
        $staffId = $staff->id;
        if($staff->isPicker()){
            $totalOrder = ScanOrder::where('picker_id',$staffId)->count();
        }

        if($staff->isMiddleMan()){
            $totalOrder = ScanOrder::where('middle_man_id',$staffId)->count();
        }

        if($staff->isSupervisor()){
            $totalOrder = ScanOrder::where('supervisor_id',$staffId)->count();
        }

        if($staff->isRider()){
            //status 4 is complete 
            $totalOrder = OrderAssigned::where('rider_id',$staffId)->where(['trip_status_id'=>4,'status'=>1])->count();
        }
        
        $totalCommission = $staff->userDetail->commission * $totalOrder;
        $totalPaidCommission = StaffFinancial::where('staff_id',$staffId)->sum('amount');
        $remaingCommission = $totalCommission - $totalPaidCommission;

        return $staffCommission = [
            'totalOrder' => $totalOrder,
            'totalCommission' => $totalCommission,
            'totalPaidCommission' => $totalPaidCommission,
            'remaingCommission' => $remaingCommission,
        ];
    }

    public static function riderCashCollection($rider,$requestDate = '')
    {
        $riderId = $rider->id;
        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        //today total delivered orders add date attribute
        $todayOrder = OrderAssigned::where(['rider_id'=>$riderId])->whereDate('created_at', now())->where('status',1)->count();
        
        //remaing parcels
        $remaingOrder = OrderAssigned::where(['rider_id'=>$riderId])->whereIn('trip_status_id',[1,2])->where('status',1)->count();

        //total delivered order cash
        //order status 6 is delivered

        if($requestDate){
            $totalCashByRider = OrderAssigned::where('rider_id',$riderId)
            ->whereDate('created_at',$requestDate)
            ->where('status',1)
            /*->with([
                'order' => function($query){
                    $query->where('order_status', 6)->sum('consignment_cod_price');
                }
            ])*/
            ->withCount([
            'order' => function ($query) use($usercity) {
                    //$query->sum('consignment_cod_price');
                    $query->select(DB::raw("SUM(consignment_cod_price) as price"))->whereIn('consignee_city',$usercity)->where('order_status', 6);
                }
            ])
            ->get()
            ->pluck('order_count')
            ->toArray();

            $totalCollectCashFromRider = RiderCashCollection::where('rider_id',$riderId)->whereDate('created_at',$requestDate)->sum('amount');
        }else{

            $totalCashByRider = OrderAssigned::where('rider_id',$riderId)
            ->where('status',1)
            ->withCount([
            'order' => function ($query) use($usercity) {
                    //$query->sum('consignment_cod_price');
                    $query->select(DB::raw("SUM(consignment_cod_price) as price"))->whereIn('consignee_city',$usercity)->where('order_status', 6);
                }
            ])
            ->get()
            ->pluck('order_count')
            ->toArray();
            

            $totalCollectCashFromRider = RiderCashCollection::where('rider_id',$riderId)->sum('amount');
        }

        $parcelMoneyCollect = array_sum($totalCashByRider);

        //collcet cash from rider
        
        (count($totalCashByRider) > 0) ? $totalCash = $parcelMoneyCollect : $totalCash = 0;
        
        $remainingCash = ($totalCash - $totalCollectCashFromRider);

        //$collectMoneyFromRider = $parcelMoneyCollect - $remainingCash;

        return $staffCommission = [
            'todayOrder' => $todayOrder,
            'remaingOrder' => $remaingOrder,
            'totalCashByRider' => $totalCash,
            'totalCollectCashFromRider' => $totalCollectCashFromRider,
            'remainingCash' => $remainingCash,
            //'collectMoneyFromRider' => $collectMoneyFromRider,
        ];
    }

    public static function getVendorId()
    {
        $authVendorId = Auth::user()->vendor_id;
        if($authVendorId){
            return $authVendorId;
        }else{
            //dd('not vendor found');   
        }
    }
    
    public static function sendMessage($data) {
        $number = $data['number'];
        $message_data = $data['message'];
        
        $last_four = substr ($number, -10);
        try {

            $key='5c60add7db78fca4fb59256f1a8c4fb8';
            $sender='AHL';
            $mobile=$last_four;
            $message=$message_data;
            $post ="sender=".urlencode($sender)."&receiver=".urlencode($mobile)."&message=".$message."&response_type =json";
            $url = 'https://bsms.its.com.pk/api.php?key='.$key;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
            $result = curl_exec($ch);
            if ($result === false) {
                die('Curl failed: ' . curl_error($ch));
            }

            curl_close($ch);
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }
    
    public static function sendMessageNew($data) {
        $number = $data['number'];
        $message = $data['message'];
        try {

            $url = 'https://connect.jazzcmt.com/sendsms_url.html?Username=03018613398&Password=Ahl@19981&From=AHL&To=' . $number . '&Message=' . $message;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            if ($result === false) {
                die('Curl failed: ' . curl_error($ch));
            }

            curl_close($ch);
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }
    
    public static function getActiveVendors() {
        $vendors = \App\Models\Vendor::where('status',1)->get();
        
        return $vendors;
    }

    public static function getTaglines()
    {
        $tag_lines = TagLine::where('status', 1)->get();
        return $tag_lines;
    }
    
}
