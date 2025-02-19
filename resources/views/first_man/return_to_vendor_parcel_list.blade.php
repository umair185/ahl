@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>Return to Vendor Parcels List</h5>
            <form method="get">
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="from" id="from" class="form-control" required="required" value="<?php
                            if (isset($_GET['from'])) {
                                echo $_GET['from'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                            if (isset($_GET['to'])) {
                            echo $_GET['to'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="form-group">
                            <label>Vendors</label>
                            <select name="vendor" id="vendor" class="form-control" required="required">
                                <option value="any">Any</option>
                                @foreach($vendors as $vendor)
                                <option {{$vendorRequest == $vendor->id ? 'selected' : ''}} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="form-group">
                            <br>
                            <button type="submit" class="btn btn-primary mt-1">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
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
                            <th>Vendor</th>
                            <th>Order Ref #</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Order Type</th>
                            <th>Packing Box</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $parcel)
                        <tr id="tr-{{$parcel->id }}">
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{$parcel->vendor->vendor_name}}</td>
                            <td>{{$parcel->order_reference}}</td>
                            <td>{{$parcel->consignment_order_id}}</td>
                            <td>{{$parcel->consignment_cod_price}}</td>
                            <td>{{$parcel->consignment_pieces}}</td>
                            <td>{{$parcel->vendorWeight->ahlWeight->weight . ' (' .           $parcel->vendorWeight->city->first()->name . ')'}}</td>
                            <td>{{$parcel->orderType->name}}</td>
                            <td>{{$parcel->orderPacking->name}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <br>
    </div>
</div>
@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">
$(function () {
    /* Data Table */
    var table = $('#example').DataTable({
      "lengthMenu": [ 10, 25, 50, 75, 100, 500, 1000, 5000 ]
    });
});
</SCRIPT>
<script type="text/javascript">
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
</script>
@endsection