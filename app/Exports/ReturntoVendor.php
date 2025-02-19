<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App\Models\OrderAssigned;
use App\Models\UserCity;
use App\Models\City;
use App\Models\Order;
use App\Models\ScanOrder;
use Auth;

class ReturntoVendor implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct($from,$to,$vendor_id)
    {
        // if login user is admin can download with all and selected city sheet.
        if(Auth::user()->hasAnyRole('admin'))
        {
            $orders_assigned = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('vendor_id',$vendor_id)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
            $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id',$vendor_id)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
            $collection = collect([$orders_assigned,$returned_parcels]);
            $collapsed = $collection->collapse();
            $parcels = $collapsed->all();

            $orderAssigned = Order::whereIn('id', $parcels)
                ->whereIn('order_status', [10])->with([
                    'vendorWeight' => function($query){
                        $query->select('id','ahl_weight_id','price','city_id')->with([
                            'ahlWeight' =>  function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'customerCity' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                ])->get();
                // dd($orderAssigned);
                //->toArray();
                // dd($orderAssigned);
                $this->orderAssigned = $orderAssigned;
        }

        // if login user is financer or cashier can download with only assigned cities.
        if(Auth::user()->hasAnyRole('financer','head_of_account'))
        {
            $userId = Auth::user()->id;
            $userCity = UserCity::where('user_id',$userId)->pluck('city_id');

            $orders_assigned = OrderAssigned::whereDate('created_at','>=',$from)->whereDate('created_at','<=',$to)->where('vendor_id',$vendor_id)->where('status',1)->where('trip_status_id', 4)->pluck('order_id');
            $returned_parcels = OrderAssigned::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('vendor_id',$vendor_id)->where('trip_status_id', 5)->where('status',0)->pluck('order_id');
            $collection = collect([$orders_assigned,$returned_parcels]);
            $collapsed = $collection->collapse();
            $parcels = $collapsed->all();

            $orderAssigned = Order::whereIn('id', $parcels)->whereIn('consignee_city', $userCity)
                ->whereIn('order_status', [10])->with([
                    'vendorWeight' => function($query){
                        $query->select('id','ahl_weight_id','price','city_id')->with([
                            'ahlWeight' =>  function($query){
                                $query->select('id','weight');
                            },
                            'city' => function($query){
                                $query->select('id','name');
                            }
                        ]);
                    },
                    'orderType' => function($query){
                        $query->select('id','name');
                    },
                    'vendor' => function($query){
                        $query->select('id','vendor_name');
                    },
                    'customerCity' => function($query){
                        $query->select('id','name');
                    },
                    'orderStatus' => function($query){
                        $query->select('id','name');
                    },
                ])->get();
                // dd($orderAssigned);
                //->toArray();
                // dd($orderAssigned);
                $this->orderAssigned = $orderAssigned;
        }

        
    }
    /**s
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $orderAssigneds = $this->orderAssigned;
        
        foreach ($orderAssigneds as $key => $orderAssigned) {
            // code...
            $data[] = [
                $orderAssigned->vendor ? $orderAssigned->vendor->vendor_name : '',
                $orderAssigned ? $orderAssigned->order_reference : '',
                $orderAssigned ? $orderAssigned->consignment_order_id : '',
                $orderAssigned ? $orderAssigned->consignee_first_name.''.$orderAssigned->consignee_last_name : '',
                $orderAssigned ? $orderAssigned->consignee_phone : '',
                $orderAssigned ? $orderAssigned->consignment_cod_price : '',
                $orderAssigned->orderType ? $orderAssigned->orderType->name : '',
                $orderAssigned ? $orderAssigned->consignee_address : '',
                $orderAssigned->vendorWeight ? $orderAssigned->vendorWeight->ahlWeight->weight . ' ('. $orderAssigned->vendorWeight->city->first()->name . ')' : '',
                $orderAssigned ? $orderAssigned->vendor_weight_price : '',
                $orderAssigned ? $orderAssigned->vendor_tax_price : '',
                $orderAssigned ? $orderAssigned->vendor_fuel_price : '',
                $orderAssigned->customerCity ? $orderAssigned->customerCity->name : '',
                $orderAssigned->orderStatus ? $orderAssigned->orderStatus->name : '',

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
            'vendor_name',
            'order_reference',
            'consignment_order_id',
            'consignee_name',
            'consignee_phone',
            'consignment_cod_price',
            'order_type' ,
            'consignee_address',
            'vendor_order_weight',
            'weight_price',
            'vendor_tax_price',
            'vendor_fuel',
            'city',
            'status'
        ];
    }
}
