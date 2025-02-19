<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\UserCity;
use App\Models\City;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TodayReport implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct()
    {

        if(Auth()->user()->hasAnyRole('admin'))
        {
            $orderAssigned = Order::whereIn('order_status', [3,8,9])
                ->with([
                    'vendorWeight' => function($query){
                        $query->select('id','ahl_weight_id','price','city_id')->with([
                        'ahlWeight' =>  function($query){
                            $query->select('id','weight');
                        },
                        'city' => function($query){
                            $query->select('id','name');
                        },
                        ]);
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    }
                ])
                ->get();
        }
        elseif(Auth()->user()->hasAnyRole('middle_man'))
        {
            $userid = Auth()->user()->id;
            $usercity = UserCity::where('user_id',$userid)->pluck('city_id');
            $cities = City::whereIn('id',$usercity)->get();
            
            $orderAssigned = Order::whereIn('order_status', [3,8,9])->whereIn('consignee_city',$usercity)
                ->with([
                    'vendorWeight' => function($query){
                        $query->select('id','ahl_weight_id','price','city_id')->with([
                        'ahlWeight' =>  function($query){
                            $query->select('id','weight');
                        },
                        'city' => function($query){
                            $query->select('id','name');
                        },
                        ]);
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    }
                ])
                ->get();
        }
        
        
        //->toArray();
        //dd($orderAssigned);
        $this->orderAssigned = $orderAssigned;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $orderAssigneds = $this->orderAssigned;

        if(Auth()->user()->hasAnyRole('admin'))
        {
            foreach ($orderAssigneds as $key => $orderAssigned) {
                // code...
                $data[] = [
                    $orderAssigned->order_reference,
                    $orderAssigned->vendor->vendor_name,
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->consignment_order_id,
                    $orderAssigned->consignee_first_name.''.$orderAssigned->consignee_last_name,
                    $orderAssigned->consignee_phone,
                    $orderAssigned->consignee_address,
                    Date('d-m-Y',strtotime($orderAssigned->scanOrder ? $orderAssigned->scanOrder->created_at : '')),
                    $orderAssigned->orderStatus->name,
                    $orderAssigned->customerCity->name,
                    $orderAssigned->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->vendorWeight->city->first()->name . ')',
                    $orderAssigned->vendor_weight_price,
                    $orderAssigned->vendor_tax_price,
                    $orderAssigned->vendor_fuel_price,
                    $orderAssigned->consignment_cod_price,
                ];
            }
        }
        if(Auth()->user()->hasAnyRole('middle_man'))
        {
            foreach ($orderAssigneds as $key => $orderAssigned) {
                // code...
                $data[] = [
                    $orderAssigned->order_reference,
                    $orderAssigned->vendor->vendor_name,
                    Date('d-m-Y',strtotime($orderAssigned->created_at)),
                    $orderAssigned->consignment_order_id,
                    $orderAssigned->consignee_first_name.''.$orderAssigned->consignee_last_name,
                    $orderAssigned->consignee_phone,
                    $orderAssigned->consignee_address,
                    Date('d-m-Y',strtotime($orderAssigned->scanOrder ? $orderAssigned->scanOrder->created_at : '')),
                    $orderAssigned->orderStatus->name,
                    $orderAssigned->customerCity->name,
                    $orderAssigned->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->vendorWeight->city->first()->name . ')',
                    $orderAssigned->vendor_weight_price,
                    $orderAssigned->vendor_tax_price,
                    $orderAssigned->vendor_fuel_price,
                    $orderAssigned->consignment_cod_price,
                ];
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
        if(Auth()->user()->hasAnyRole('admin'))
        {
            return [
                'order_reference',
                'vendor_name',
                'booking_date',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignee_address',
                'pickup_date',
                'order_current_status',
                'destination',
                'vendor_order_weight',
                'vendor_order_weight_price',
                'vendor_tax_price',
                'vendor_fuel',
                'consignment_cod_price',
            ];
        }
        if(Auth()->user()->hasAnyRole('middle_man'))
        {
            return [
                'order_reference',
                'vendor_name',
                'booking_date',
                'consignment_order_id',
                'consignee_name',
                'consignee_phone',
                'consignee_address',
                'pickup_date',
                'order_current_status',
                'destination',
                'vendor_order_weight',
                'vendor_order_weight_price',
                'vendor_tax_price',
                'vendor_fuel',
                'consignment_cod_price',
            ];
        }
    }
}
