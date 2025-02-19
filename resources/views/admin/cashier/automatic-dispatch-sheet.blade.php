@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Dispatched Parcels List</h5>
            <div class="text-right">
                <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive" >
                <table class="table table-hover" id="riders_table">
                    <thead>
                        <tr>
                            <th>Sr. #</th>
                            <th>Vendor</th>
                            <th>Pickup Date</th>
                            <th>Order Reference</th>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Customer Phone</th>
                            <th>Order Price</th>
                            <th>Order Type</th>
                            <th>Current Status</th>
                            <th>Rider Status</th>
                            <th>Address</th>
                            <th>Dispatch Date</th>
                            <th>Rider Name</th>
                            <th>Parcel Weight</th>
                            <th>Vendor Price</th>
                            <th>Vendor Tax Price</th>
                            <th>Vendor Fuel</th>
                            <th>Invoice #</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders_delivered as $parcel)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $parcel->riderVendor->vendor_name }}</td>
                            <td>{{ Date('d-m-Y',strtotime($parcel->order->scanOrder->created_at)) }}</td>
                            <td>{{ $parcel->order->order_reference }}</td>
                            <td>{{ $parcel->order->consignment_order_id }}</td>
                            <td>{{ $parcel->order->consignee_first_name }} {{ $parcel->order->consignee_last_name }}</td>
                            <td>{{ $parcel->order->consignee_phone }}</td>
                            <td>{{ $parcel->order->consignment_cod_price }}</td>
                            <td>{{ $parcel->order->orderType->name }}</td>
                            <td>{{ $parcel->order->orderStatus->name }}</td>
                            <td>{{ $parcel->tripStatus->description }}</td>
                            <td>{{ $parcel->order->consignee_address }}</td>
                            <td>{{ Date('d-m-Y',strtotime($parcel->created_at)) }}</td>
                            <td>{{ $parcel->rider->name }}</td>
                            <td>{{ $parcel->order->vendorWeight->ahlWeight->weight . ' (' . $parcel->order->vendorWeight->city->first()->name . ')' }}</td>
                            <td>{{ $parcel->order->vendor_weight_price }}</td>
                            <td>{{ $parcel->order->vendor_tax_price }}</td>
                            <td>{{ $parcel->order->vendor_fuel_price }}</td>
                            <td>Invoice #: {{$finance_id->invoice_number}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function fnExcelReport()
{
    var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
    var textRange; var j=0;
    tab = document.getElementById('riders_table'); // id of table

    for(j = 0 ; j < tab.rows.length ; j++) 
    {     
        tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
        //tab_text=tab_text+"</tr>";
    }

    tab_text=tab_text+"</table>";
    tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
    tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
    tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE "); 

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
    {
        txtArea1.document.open("txt/html","replace");
        txtArea1.document.write(tab_text);
        txtArea1.document.close();
        txtArea1.focus(); 
        sa=txtArea1.document.execCommand("SaveAs",true,"Say Thanks to Sumit.xls");
    }  
    else                 //other browser not tested on IE 11
        sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  

    return (sa);
}
</script>
@endsection