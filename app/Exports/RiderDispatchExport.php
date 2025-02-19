<?php

namespace App\Exports;

use App\Models\OrderAssigned;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RiderDispatchExport implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct($date)
    {
        $this->orderAssigned = $date;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $orderAssigneds = $this->orderAssigned;
        //dd($orderAssigneds);
        return $orderAssigneds;
    }

    public function headings(): array
    {
        return [
            'order_reference',
            'consignee_name',
            'vendor_order_weight',
            'consignment_cod_price',
            'order_type' ,
            'vendor_name',
            'consignment_order_id',
            'consignee_address',
            'pickup_date',
            'rider_name',
            'order_current_status',
            'consignee_phone',
        ];
    }
}
