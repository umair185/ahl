<?php

namespace App\Exports;

use App\Models\OrderAssigned;
use App\Models\UserCity;
use App\Models\City;
use App\Models\Order;
use App\Models\ScanOrder;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Auth;

class SpecificSupervisorScanHistory implements FromArray,WithHeadings
{
    private $scan_orders;

    public function __construct($to,$from)
    {
        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $scan_orders = OrderAssigned::whereDate('created_at','>=', $from)->whereDate('created_at','<=',$to)->whereHas('orderDetail',function($query) use($usercity){
                    $query->where('consignee_city',$usercity);
                })->whereHas('scanOrder', function($q) use($userId){
                    $q->where('supervisor_id', $userId);
                })->with('orderDetail')->orderBy('id','DESC')->get();

        $this->scan_orders = $scan_orders;
        
    }
    /**s
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $scanOrderDetails = $this->scan_orders;
        
        foreach ($scanOrderDetails as $key => $scanOrderDetail) {
            // code...
            $data[] = [
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->order_reference : '',
                $scanOrderDetail->orderDetail ? Date('d-m-Y',strtotime($scanOrderDetail->orderDetail->scanOrder->created_at)) : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignment_order_id : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignee_first_name .' '. $scanOrderDetail->orderDetail->consignee_last_name : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignee_address : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->vendor->vendor_name : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->customerCity->name : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignment_cod_price : '',
                $scanOrderDetail->scanOrder->scanBySupervisor ? $scanOrderDetail->scanOrder->scanBySupervisor->name : '',
                $scanOrderDetail->rider ? $scanOrderDetail->rider->name : '',

            ];
            // dd($data);
        }
        
        if(isset($data)){
            return $data;
        }else{
            return [];
        }
    }

    public function headings(): array
    {
        return [
            'order_reference',
            'pickup_date',
            'order_id',
            'customer_name',
            'customer_address',
            'vendor_name',
            'destination',
            'consignment_cod_price',
            'scan_by' ,
            'rider_name',
        ];
    }
}
