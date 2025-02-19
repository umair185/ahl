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
            <h5>Shiper Adviser Parcel</h5>
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
                            <th>Customer Ref</th>
                            <th>Parcel Attempts</th>
                            <th>Vendor Name</th>
                            <th>Age</th>
                            <th>Amount</th>
                            <th>Order ID</th>
                            <th>Status</th>
                            <th>Cancel At</th>
                            <th>Customer Name</th>
                            <th>Customer Address</th>
                            <th>Customer Phone</th>
                            <th>Shiper Advise</th>
                            <th>Shiper Date</th>
                            <th>AHL Reply</th>
                            <th>AHL Reply At</th>
                            <th style="min-width: 100px;">IVR Remarks</th>
                            <th>Send Reply</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-data">
                        <!-- parcel data prepand here through also ajax -->

                        @foreach($shiperParcelsAdvise as $parcelAdvise)
                            @if($parcelAdvise->order)
                                <tr id="tr-{{$parcelAdvise->id}}">
                                    <td> {{ $loop->iteration }} </td>
                                    <td> {{ $parcelAdvise->order->order_reference }} </td>
                                    @if(!empty($parcelAdvise->order->parcel_limit))
                                    <td> {{ $parcelAdvise->order ? $parcelAdvise->order->parcel_attempts : '' }} / {{ $parcelAdvise->order ? $parcelAdvise->order->parcel_limit : '' }} </td>
                                    @else
                                    <td>0 / 0</td>
                                    @endif
                                    <td> {{ $parcelAdvise->order->vendor->vendor_name }} </td>
                                    @if(!empty($parcelAdvise->order->scanOrder->middle_man_scan_date))
                                    <td>
                                        {{\Carbon\Carbon::parse($parcelAdvise->order->scanOrder->middle_man_scan_date)->diffInDays(\Carbon\Carbon::now())}} Days
                                    </td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td> {{ $parcelAdvise->order->consignment_cod_price }} </td>
                                    <td> {{ $parcelAdvise->order->consignment_order_id }} </td>
                                    <td> {{ $parcelAdvise->order->orderStatus->name }} </td>
                                    <td>
                                        @if($parcelAdvise->order->orderStatus->name == 'Cancelled')
                                        {{ date('d-m-Y H:i A', strtotime($parcelAdvise->order->updated_at)) }} </td>
                                        @endif
                                    </td>
                                    <td> {{ $parcelAdvise->order->full_name }} </td>
                                    <td> {{ $parcelAdvise->order->consignee_address }} </td>
                                    <td> {{ $parcelAdvise->order->consignee_phone }} </td>
                                    <td> {{ ($parcelAdvise->advise) ? $parcelAdvise->advise : '' }} </td>
                                    <td> {{ date('d-m-Y H:i A', strtotime($parcelAdvise->created_at)) }} </td>
                                    <td> {{ ($parcelAdvise->ahl_reply) ? $parcelAdvise->ahl_reply : '' }} </td>
                                    @if(!empty($parcelAdvise->ahl_reply))
                                    <td>{{date('d M Y H:i A', strtoTime($parcelAdvise->updated_at))}}</td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td style="min-width: 100px;">
                                        @foreach($parcelAdvise->order->countOrderAssigned as $key => $orderAssigned)
                                            <?php 
                                                $call_input_value = '';
                                                if($orderAssigned->ivr_value == '479') //Re-attempt
                                                {
                                                    if($orderAssigned->call_input == '0')
                                                    {
                                                        $call_input_value = 'No Input';
                                                    }
                                                    elseif($orderAssigned->call_input == '1')
                                                    {
                                                        $call_input_value = 'Please Re-Attempt My Parcel';
                                                    }
                                                    elseif($orderAssigned->call_input == '2')
                                                    {
                                                        $call_input_value = 'Do-Not Re Attempt I want my parcel /Rider Add fake Remakrs';
                                                    }
                                                    else
                                                    {
                                                        $call_input_value = 'No Input';
                                                    }
                                                }
                                                elseif($orderAssigned->ivr_value == '480') //Cancel
                                                {
                                                    if($orderAssigned->call_input == '0')
                                                    {
                                                        $call_input_value = 'No Input';
                                                    }
                                                    elseif($orderAssigned->call_input == '1')
                                                    {
                                                        $call_input_value = 'Please Cancel my order /Confirm Cancel by Custumer';
                                                    }
                                                    elseif($orderAssigned->call_input == '2')
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
                                                    if($orderAssigned->call_input == 0)
                                                    {
                                                        $call_input_value = 'No Input';
                                                    }
                                                    elseif($orderAssigned->call_input == 1)
                                                    {
                                                        $call_input_value = 'Cancel Input';
                                                    }
                                                    elseif($orderAssigned->call_input == 2)
                                                    {
                                                        $call_input_value = 'Re-Attempt Input';
                                                    }
                                                    else
                                                    {
                                                        $call_input_value = 'No Input';
                                                    }
                                                }
                                            ?>
                                            {{$call_input_value}}
                                            <hr>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a target="_blank" href="{{route('shiperReply', $parcelAdvise->id)}}">
                                            <button class="btn waves-effect waves-light btn-primary"><i class="fa fa-edit"></i>Edit</button>
                                        </a>
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