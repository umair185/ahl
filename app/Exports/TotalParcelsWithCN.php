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

class TotalParcelsWithCN implements FromArray,WithHeadings
{
    private $scan_orders;

    public function __construct($date)
    {
        $userId = Auth::user()->id;
        $usercity = UserCity::where('user_id',$userId)->pluck('city_id');

        $scan_orders = ScanOrder::whereHas('orderDetail',function($query) use($usercity){
                $query->whereIn('consignee_city',$usercity);
            }
            )->with('orderDetail')->whereDate('middle_man_scan_date','=', $date)->orderBy('id','DESC')->get();

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
                Date('d-m-Y',strtotime($scanOrderDetail->middle_man_scan_date)),
                Date('h:i a',strtotime($scanOrderDetail->middle_man_scan_date)),
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->vendor->vendor_name : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->customerCity->name : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignment_cod_price : '',
                $scanOrderDetail->scanByMiddleMan ? $scanOrderDetail->scanByMiddleMan->name : '',
                $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->parcelNature->name : '',

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
            'scan_date',
            'scan_time',
            'vendor_name',
            'destination',
            'consignment_cod_price',
            'scan_by' ,
            'order_nature' ,
        ];
    }
}
