<?php

namespace app\Helpers;

use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\OrderAssigned;
use App\Models\Packing;
use App\Models\OrderType;
use App\Models\Vendor;
use App\Models\VendorWeight;
use App\Models\WarehouseLocation;
use App\Models\User;
use App\Models\UserCity;

class AHLHelper
{
    public static function staffAllow() {
        $authUser = Auth::user();
        if($authUser->isAdmin()){
            $staffAllow = [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19];
            //all roles 12 for CSR, 13 for BD, 14 for BDM
        }elseif($authUser->isFirstMan()){
            $staffAllow = [6];
        }elseif($authUser->isSupervisor()){
            $staffAllow = [7];
        }elseif($authUser->isHubManager()){
            $staffAllow = [4,5,6,7,9,17];
        }elseif($authUser->isBDM()){
            $staffAllow = [12,13];
        }elseif($authUser->isHR()){
            $staffAllow = [4,5,6,7,8,9,10,11,12,13,14,16,17,18,19];
        }

        return $staffAllow;
    }

    public static function vendorGST($vendorId)
    {
        $vendor = Vendor::select('id','gst')->where('id',$vendorId)->first()->toArray();
        return $vendor['gst'];
    }

    public static function rackBalancing($scanOrderIds)
    {
        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');
    	$orders = Order::select('id','order_reference','order_status')
    	->whereIn('id',$scanOrderIds)->whereIn('consignee_city',$usercity)
        ->get();
        
        if($orders){
            foreach ($orders as $key => $order) {
                switch ($order->order_status) {
                    case 2:
                        //At AHL Warehouse
                        $atAHLParcels[] = $order->order_reference;
                        break;
                    case 3:
                        //At AHL Warehouse
                        $atAHLParcels[] = $order->order_reference;
                        break;
                    case 6:
                        //cancelled status
                        $deliveredParcels[] = $order->order_reference;
                        break;
                    case 7:
                        //request for reattempt
                        $requestReattemptParcels[] = $order->order_reference;
                        break;
                    case 16:
                        //reattempt by rider
                        $reattemptParcels[] = $order->order_reference;
                        break;
                    case 9:
                        //cancelled status
                        $cancelledParcels[] = $order->order_reference;
                        break;
                    case 17:
                        //cancelled by rider
                        $cancelledbyRider[] = $order->order_reference;
                        break;
                    case 18:
                        //cancelled by supervisor
                        //add status 18 in Live Database
                        $cancelledbySupervisor[] = $order->order_reference;
                        break;
                    
                    
                    default:
                        # code...
                        break;
                }

                //$allParcels[] =  $order->order_reference;
            }
        }

        isset($atAHLParcels)  ? $atAhl = $atAHLParcels : $atAhl = 0;
        isset($deliveredParcels)  ? $delivered = $deliveredParcels : $delivered = 0;
        isset($requestReattemptParcels)  ? $requestReattempt = $requestReattemptParcels : $requestReattempt = 0;
        isset($cancelledParcels)  ? $cancelled = $cancelledParcels : $cancelled = 0;
        isset($reattemptParcels)  ? $reattempt = $reattemptParcels : $reattempt = 0;
        isset($cancelledbyRider)  ? $riderCancelled = $cancelledbyRider : $riderCancelled = 0;
        isset($cancelledbySupervisor)  ? $supervisorCancelled = $cancelledbySupervisor : $supervisorCancelled = 0;

        return  [
        	'deliveredParcels' => $delivered,
        	'requestReattemptParcels' => $requestReattempt,
        	'cancelledParcels' => $cancelled,
        	'atAHLParcels' => $atAhl,
            'reattemptParcels' => $reattempt,
            'riderCancelled' => $riderCancelled,
            'supervisorCancelled' => $supervisorCancelled,
        ];
    }

    public static function orderType()
    {
        return $orderType = OrderType::all();
    }

    public static function vendorWeight()
    {
        $authUser = Auth::user();
        $authVendorId = $authUser->vendor_id;
        return $vendorWeight = VendorWeight::where('vendor_id',$authVendorId)->get();
    }

    public static function packaging()
    {
        return $packing = Packing::all();
    }

    public static function vendorWarehouseLocation()
    {
        $authUser = Auth::user();
        $authVendorId = $authUser->vendor_id;
        return $warehouseLocation = WarehouseLocation::where('vendor_id',$authVendorId)->get();
    }

    public static function findJsonError()
    {
        $error = json_last_error();
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error =  ' - No errors';
            break;
            case JSON_ERROR_DEPTH:
                $error =   ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $error =  ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $error =   ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $error =   ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $error =   ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                $error =   ' - Unknown error';
            break;
        }
        
        return $error;
        //$shopifyProduct = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $requestShopifyProduct), true);
    }

    public static function checkStatus($statusId)
    {
        switch ($statusId) {
            case 1:
                $status = 'Active';
                $class = 'text-success font-bold';
                break;
            
            default:
                $status = 'Block';
                $class = 'text-danger font-bold';
                break;
        }


        return $status = [
            'status' => $status, 
            'class' => $class, 
        ];
    }

    public static function todayCash()
    {
        $todayOrderAssigned = OrderAssigned::select('id','order_id','trip_status_id','created_at','status')
        ->whereDate('created_at',now())
        ->where(['status'=>1])
        ->whereIn('trip_status_id',[1,2,3,4])
        ->with([
            'order' => function($query){
                $query->select('id','consignment_cod_price');
            }
        ])
        ->get();

        $totalCollection = 0;
        $remaingCollection = 0;
        foreach($todayOrderAssigned as $key => $assignedOrder){
            if($assignedOrder->trip_status_id == 4){
                $totalCollection = $totalCollection +  $assignedOrder->order->consignment_cod_price;
            }else{
                $remaingCollection = $remaingCollection +  $assignedOrder->order->consignment_cod_price;
            }
        }

        $todayExpectedCash = $totalCollection + $remaingCollection;

        return [
            'todayCollection' => $totalCollection,
            'todayRemaingCollection' => $remaingCollection,
            'todayExpectedCash' => $todayExpectedCash,
        ];
    }

    public static function dashboardOrders($orders)
    {
        //statuses
        $awaiting = 0;
        $pickup = 0;
        $warehouse = 0;
        $dispatched = 0;
        $delivered_parcel = 0;
        $requestforreattempt = 0;
        $reattempt = 0;
        $cancelled = 0;
        $returntovendor = 0;
        $cancelbyadmin = 0;
        $cancelbyvendor = 0;
        $voidlabel = 0;

        $total_parcel = 0;
        $overall_sum = 0;
        $delivered_sum = 0;

        //whereIn [9,11,12]
        $allCancelledParcel = 0;
        $cancelled_sum = 0;

        //whereNotIn [6,9,11,12]
        $pending = 0;
        $pending_sum = 0;

        foreach ($orders as $key => $order) {
            switch ($order->order_status) {
                case 1:
                    // awaiting
                    $awaiting++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 2:
                    // pickup
                    $pickup++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 3:
                    // warehouse...
                    $warehouse++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 5:
                    // dispatched...
                    $dispatched++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 6:
                    // delivered_parcel...
                    $delivered_parcel++;
                    //delivered parcel sum
                    $delivered_sum = $delivered_sum + $order->consignment_cod_price;
                    break;
                case 7:
                    // requestforreattempt...
                    $requestforreattempt++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 8:
                    // reattempt...
                    $reattempt++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 9:
                    // cancelled...
                    $cancelled++;
                    //count cancelled parcel
                    $allCancelledParcel++;
                    //cancelled parcel sum
                    $cancelled_sum = $cancelled_sum + $order->consignment_cod_price;
                    break;
                case 10:
                    // returntovendor...
                    $returntovendor++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                case 11:
                    // cancelbyadmin...
                    $cancelbyadmin++;
                    //count cancelled parcel
                    $allCancelledParcel++;
                    //cancelled parcel sum
                    $cancelled_sum = $cancelled_sum + $order->consignment_cod_price;
                    break;
                case 12:
                    // cancelbyvendor...
                    $cancelbyvendor++;
                    //count cancelled parcel
                    $allCancelledParcel++;
                    //cancelled parcel sum
                    $cancelled_sum = $cancelled_sum + $order->consignment_cod_price;
                    break;
                case 13:
                    // voidlabel...
                    $voidlabel++;
                    //count pending
                    $pending++;
                    //pending parcel sum
                    $pending_sum = $pending_sum + $order->consignment_cod_price;
                    break;
                
                default:
                    // default status 4 check by supervisor
                    break;
            }

            $total_parcel++;
            $overall_sum = $overall_sum + $order->consignment_cod_price;
        }

        return  [
            'total_parcel' => $total_parcel,
            'overall_sum' => $overall_sum,
            'delivered_sum' => $delivered_sum,
            'allCancelledParcel' => $allCancelledParcel,
            'cancelled_sum' => $cancelled_sum,
            'pending' => $pending,
            'pending_sum' => $pending_sum,

            'awaiting' => $awaiting,
            'pickup' => $pickup,
            'warehouse' => $warehouse,
            'dispatched' => $dispatched,
            'delivered_parcel' => $delivered_parcel,
            'requestforreattempt' => $requestforreattempt,
            'reattempt' => $reattempt,
            'cancelled' => $cancelled,
            'returntovendor' => $returntovendor,
            'cancelbyadmin' => $cancelbyadmin,
            'cancelbyvendor' => $cancelbyvendor,
            'voidlabel' => $voidlabel,
        ];
    }
}
