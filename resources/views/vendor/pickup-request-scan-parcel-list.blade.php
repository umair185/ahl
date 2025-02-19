@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Scan Parcels List</h5>
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
                            <th>Order Reference</th>
                            <th>Booking Date</th>
                            <th>Customer Ref #</th>
                            <th>Destination</th>
                            <th>Pickup Location</th>
                            <th>Pieces</th>
                            <th>Weight</th>
                            <th>COD Amount</th>
                            <th>Current Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scanOrders as $parcel)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $parcel->orderDetail->order_reference }}</td>
                            <td>{{ date('d M Y', strtoTime($parcel->created_at)) }}</td>
                            <td>{{ $parcel->orderDetail->consignment_order_id }}</td>
                            <td>{{ $parcel->orderDetail->consignee_address }}</td>
                            <td>{{ $parcel->orderDetail->pickupLocation->address }}</td>
                            <td>{{ $parcel->orderDetail->consignment_pieces }}</td>
                            <td>{{ $parcel->orderDetail->vendorWeight->ahlWeight->weight . ' (' . $parcel->orderDetail->vendorWeight->city->first()->name . ')' }}</td>
                            <td>{{ $parcel->orderDetail->consignment_cod_price }}</td>
                            <td>{{ $parcel->orderDetail->orderStatus->name}}</td>
                        </tr>
                       @endforeach
                       <tr>
                            <th colspan="7" style="font-weight: bold; text-align: right">Total</th>
                            <td style="font-weight: bold">Rs. {{ $order_details }}</td>
                            <td style="font-weight: bold"></td>
                        </tr>
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