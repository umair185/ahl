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

class PickupVendorParcelCount implements FromArray,WithHeadings
{
    private $scan_orders;

    public function __construct($from,$to,$vendor_id)
    {
        $vendor_id = $vendor_id;

        $scan_orders = ScanOrder::whereHas('orderDetail',function($query) use($vendor_id){
                $query->where('vendor_id',$vendor_id);
            })->with('orderDetail')->where('created_at','>=', $from)->where('created_at','<=', $to)->orderBy('id','DESC')->get();

        $this->scan_orders = $scan_orders;
        
    }
    /**s
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $scanOrderDetails = $this->scan_orders;

        if(Auth()->user()->hasAnyRole('admin','vendor_admin'))
        {
            foreach ($scanOrderDetails as $key => $scanOrderDetail) {
                // code...
                $data[] = [
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->order_reference : '',
                    Date('d-m-Y',strtotime($scanOrderDetail->created_at)),
                    Date('h:i a',strtotime($scanOrderDetail->created_at)),
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->vendor->vendor_name : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->customerCity->name : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignment_cod_price : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->orderStatus->name : '',
                    $scanOrderDetail->scanByPicker ? $scanOrderDetail->scanByPicker->name : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->parcelNature->name : '',

                ];
                // dd($data);
            }
        }
        else
        {
            foreach ($scanOrderDetails as $key => $scanOrderDetail) {
                // code...
                $data[] = [
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->order_reference : '',
                    Date('d-m-Y',strtotime($scanOrderDetail->created_at)),
                    Date('h:i a',strtotime($scanOrderDetail->created_at)),
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->vendor->vendor_name : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->customerCity->name : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->consignment_cod_price : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->orderStatus->name : '',
                    $scanOrderDetail->scanByPicker ? $scanOrderDetail->scanByPicker->name : '',
                    $scanOrderDetail->orderDetail ? $scanOrderDetail->orderDetail->parcelNature->name : '',

                ];
                // dd($data);
            }
        }
        
        if(isset($data)){
            return $data;
        }else{
            return [];
        }
    }

    public function headings(): array
    {
        if(Auth()->user()->hasAnyRole('admin','vendor_admin'))
        {
            return [
                'order_reference',
                'scan_date',
                'scan_time',
                'vendor_name',
                'destination',
                'consignment_cod_price',
                'status',
                'scan_by' ,
                'parcel_nature',
            ];
        }
        else
        {
            return [
                'order_reference',
                'scan_date',
                'scan_time',
                'vendor_name',
                'destination',
                'consignment_cod_price',
                'status',
                'scan_by' ,
                'parcel_nature',
            ];
        }
    }
}
