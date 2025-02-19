@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h5>Pending Remarks</h5>
            <div class="card-block">
                <form method="get">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group form-static-label form-default">
                                <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                if (isset($_GET['date'])) {
                                    echo $_GET['date'];
                                }
                                ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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
                            <th>Order CN</th>
                            <th>Picked At</th>
                            <th>Limit</th>
                            <th>Age</th>
                            <th>Rider Name</th>
                            <th>Supervisor Name</th>
                            <th>Cancel Reason</th>
                            <th>IVR Remarks</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-data">
                        <!-- parcel data prepand here through also ajax -->

                        @foreach($pending_remarks as $pending_remark)
                        @if($pending_remark->order->order_status == 16 || $pending_remark->order->order_status == 17)
                            <tr id="tr-{{$pending_remark->id}}">
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $pending_remark->riderVendor ? $pending_remark->riderVendor->vendor_name : '' }} </td>
                                <td> {{ $pending_remark->order ? $pending_remark->order->order_reference : '' }} </td>
                                @if(!empty($pending_remark->order->scanOrder->created_at))
                                <td> {{ date('d M Y h:m a', strtoTime($pending_remark->order->scanOrder->created_at)) }} </td>
                                @else
                                <td></td>
                                @endif
                                @if(!empty($pending_remark->order->parcel_limit))
                                <td>
                                    {{ $pending_remark->order->parcel_attempts }} / {{ $pending_remark->order->parcel_limit }}
                                </td>
                                @else
                                <td>
                                    0 / 0
                                </td>
                                @endif
                                @if(!empty($pending_remark->order->scanOrder->middle_man_scan_date))
                                <td>
                                    {{\Carbon\Carbon::parse($pending_remark->order->scanOrder->middle_man_scan_date)->diffInDays(\Carbon\Carbon::now())}} Days
                                </td>
                                @else
                                <td></td>
                                @endif
                                <td> {{ $pending_remark->rider ? $pending_remark->rider->name : 'N/A' }} - {{ $pending_remark->rider ? $pending_remark->rider->userDetail->phone : 'N/A' }} </td>
                                @if(empty($pending_remark->rider))
                                <td></td>
                                @else
                                <td> {{ $pending_remark->rider->supervisorPerson ? $pending_remark->rider->supervisorPerson->name : 'N/A' }} </td>
                                @endif
                                <td> {{ $pending_remark->orderDecline ? $pending_remark->orderDecline->additional_note : 'N/A' }} </td>
                                <?php 
                                    $call_input_value = '';
                                    if($pending_remark->ivr_value == '479') //Re-attempt
                                    {
                                        if($pending_remark->call_input == '0')
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                        elseif($pending_remark->call_input == '1')
                                        {
                                            $call_input_value = 'Please Re-Attempt My Parcel';
                                        }
                                        elseif($pending_remark->call_input == '2')
                                        {
                                            $call_input_value = 'Do-Not Re Attempt I want my parcel /Rider Add fake Remakrs';
                                        }
                                        else
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                    }
                                    elseif($pending_remark->ivr_value == '480') //Cancel
                                    {
                                        if($pending_remark->call_input == '0')
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                        elseif($pending_remark->call_input == '1')
                                        {
                                            $call_input_value = 'Please Cancel my order /Confirm Cancel by Custumer';
                                        }
                                        elseif($pending_remark->call_input == '2')
                                        {
                                            $call_input_value = 'Do-Not Cancel my order I want may order / Rider Add fake Remarks';
                                        }
                                        else
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                    }
                                    else
                                    {
                                        if($pending_remark->call_input == 0)
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                        elseif($pending_remark->call_input == 1)
                                        {
                                            $call_input_value = 'Cancel Input';
                                        }
                                        elseif($pending_remark->call_input == 2)
                                        {
                                            $call_input_value = 'Re-Attempt Input';
                                        }
                                        else
                                        {
                                            $call_input_value = 'No Input';
                                        }
                                    }
                                ?>
                                <td> {{ $call_input_value }} </td>
                                <td> {{ $pending_remark->trip_status_id == 5 ? 'Cancel' : 'Re-Attempt' }} </td>
                                <td> {{ Date('d-M-y h:i a', strtotime($pending_remark->updated_at)) }} </td>
                                <td> {{ $pending_remark->order->orderStatus->name }} </td>
                                <td>
                                    <a href="{{route('pendingRemark', $pending_remark->id)}}" target="_blank"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-plus"></i>Add Remarks</button></a>
                                </td>
                            </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
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
    var table = $('#example').DataTable({
        'lengthMenu': [100, 250, 500, 1000, 5000, 10000],
        'pageLength': 100
    });
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