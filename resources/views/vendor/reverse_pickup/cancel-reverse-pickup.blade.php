@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Cancel Reverse Pickup Parcels List</h5>
                    <div class="text-right">
                        <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
                    </div>
                </div>
                <div class="card-block table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover" id="example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Ref #</th>
                                    <th>Vendor Name</th>
                                    <th>Amount</th>
                                    <th>Qty</th>
                                    <th>Customer Name</th>
                                    <th>Phone #</th>
                                    <th>Weight</th>
                                    <th>Vendor Order ID</th>
                                    <th>Order Type</th>
                                    <th>Parcel Created At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="parcel-data">
                                <!-- parcel data prepand here through also ajax -->

                                @foreach($orders as $order)
                                    <tr id="tr-{{$order->id}}">
                                        <td> {{ $loop->iteration }} </td>
                                        <td> {{ $order->order_reference }} </td>
                                        <td> {{ $order->vendor->vendor_name }} </td>
                                        <td> {{ $order->consignment_cod_price }} </td>
                                        <td> {{ $order->consignment_pieces }} </td>
                                        <td> {{ $order->consignee_first_name }} {{ $order->consignee_last_name }} </td>
                                        <td> {{ $order->consignee_phone }} </td>
                                        <td> {{ $order->vendorWeight->ahlWeight->weight . ' (' .$order->vendorWeight->city->first()->name . ')'}} </td>
                                        <td> {{ $order->consignment_order_id }} </td>
                                        <td> {{ $order->orderType->name }} </td>
                                        <td> {{ date('d M Y h:m a', strtoTime($order->created_at)) }} </td>
                                        <td> {{ $order->orderStatus->name }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">
var table;
$(function () {
    var table = $('#example').DataTable();
});
function fnExcelReport()
    {
        var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
        var textRange; var j=0;
        tab = document.getElementById('example'); // id of table
    
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
</SCRIPT>
@endsection