@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body"> 
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Pickup History Record</h5>
                </div>
                <div class="card-block">
                    <form method="get">
                        <div class="row">
                            <div class="col-xl-10 col-md-6">
                                <div class="form-group">
                                    <label>Vendors</label>
                                    <select name="vendor_id" id="vendor" class="form-control" required="required">
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
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Pickup History List</h5>
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
                                <th>Vendor Name</th>
                                <th>Pickup Request Created At</th>
                                <th>Pickup Request Completed At</th>
                                <th>Picked Parcels</th>
                                <th>Rider Name</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pickupRequests as $key => $pickupRequest)
                            <tr>
                                <th scope="row">{{ ++$key }}</th>
                                <td>{{$pickupRequest->vendorName->vendor_name}}</td>
                                <td>{{date('d M Y h:m a', strtoTime($pickupRequest->created_at))}}</td>
                                <td>{{date('d M Y h:m a', strtoTime($pickupRequest->updated_at))}}</td>
                                <td>{{$pickupRequest->assignRequest->total_picked_parcel}}</td>
                                <td>{{$pickupRequest->assignRequest->pickerName ? $pickupRequest->assignRequest->pickerName->name : 'N/A'}}</td>
                                <td>{{$pickupRequest->remarks}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">

$(function () {
    /* Data Table */
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