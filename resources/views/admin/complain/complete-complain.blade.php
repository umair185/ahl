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
            <h5>Resolved Complains</h5>
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
                            <th>Complain</th>
                            <th>Response</th>
                            <th>Response By</th>
                            <th>Status</th>
                            <th>Date & Time</th>
                            @role('admin')
                            <th>Revert</th>
                            @endrole
                        </tr>
                    </thead>
                    <tbody id="parcel-data">
                        <!-- parcel data prepand here through also ajax -->

                        @foreach($complains as $complain)
                            <tr id="tr-{{$complain->id}}">
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $complain->vendorDetail ? $complain->vendorDetail->vendor_name : '' }} </td>
                                <td> {{ $complain->complain }} </td>
                                <td> {{ $complain->action }} </td>
                                <td> {{ $complain->userDetail ? $complain->userDetail->name : 'N/A' }} </td>
                                <td> Resolved </td>
                                <td> {{ date('d M Y H:i A', strtoTime($complain->updated_at)) }} </td>
                                @role('admin')
                                <td>
                                    <a href="{{route('revertComplain', $complain->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Revert</button></a>
                                </td>
                                @endrole
                            </tr>
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