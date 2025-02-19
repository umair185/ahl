<?php

namespace App\Exports;

use App\Models\VendorFinancial;
use Illuminate\Support\Facades\Log;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PRAReportExport implements FromArray,WithHeadings
{
    private $orderAssigned;

    public function __construct($from,$to)
    {
        $vendor_financials = VendorFinancial::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->where('invoice_type', 'IBFT')->get();

        $this->vendorFinancial = $vendor_financials;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $vendorFinancials = $this->vendorFinancial;

        foreach ($vendorFinancials as $key => $vendorFinancial)
        {
            $data[] = [
                ++$key,
                $vendorFinancial->vendorName ? $vendorFinancial->vendorName->ntn : 'N\A',
                '',
                $vendorFinancial->vendorName ? $vendorFinancial->vendorName->ntn_buyer : 'N\A',
                $vendorFinancial->vendorName ? $vendorFinancial->vendorName->ntn_city : 'N\A',
                'End_Consumer',
                '',
                $vendorFinancial->invoice_number,
                Date('d-m-Y',strtotime($vendorFinancial->created_at)),
                '',
                'Services',
                '15.00',
                $vendorFinancial->ahl_commission,
                (number_format(($vendorFinancial->ahl_commission/100)*15)),
                '',
                '',
            ];
        }
        
        if(isset($data))
        {
            return $data;
        }
        else
        {
            return [];
        }
    }

    public function headings(): array
    {
        return [
            'Sr',
            'NTN',
            'cnic',
            'Name of Buyer',
            'District of Buyer',
            'Buyer Type',
            'Document Type',
            'Document Number',
            'Document Date',
            'HS Code',
            'Sale Type',
            'Rate',
            'Value of Sales Excluding Sales Tax',
            'Sales Tax Involved',
            'ST Withheld at Source',
            'Tax Reverse Charged u/s 4',
        ];
    }
}
