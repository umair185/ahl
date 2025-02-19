@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">

    <div class="card">
        <div class="card-header">
            <h5>Rider's Cash Collection</h5>
            <span>cash collection</span>
        </div>
        <div class="card-block">
            <form  method="GET">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group form-static-label form-default">
                            <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                            if (isset($_GET['date'])) {
                                echo $_GET['date'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group form-static-label form-default">
                            <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                            if (isset($_GET['to'])) {
                                echo $_GET['to'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
                            Filter
                        </button>
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
                            <th>Rider</th>
                            <th>Cashier</th>
                            <th>Cashier City</th>
                            <th>Amount</th>
                            <th>In Cash Amount</th>
                            <th>IBFT Amount</th>
                            <th>IBFT Comment</th>
                            <th>Remaining Amount</th>
                            <th>Note</th>
                            <th>Collection Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riderCashCollection as $key => $riderCash)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{$riderCash->rider->name}}</td>
                            <td>{{$riderCash->cashier->name}}</td>
                            <td>
                                @foreach($riderCash->cashier->usercity as $key => $userCity)
                                    {{++$key}} - {{$userCity->cityDetail ? $userCity->cityDetail->name : ''}} <br>
                                @endforeach
                            </td>
                            <td>{{ $riderCash->amount }}</td>
                            <td>{{ $riderCash->in_cash_collection }}</td>
                            <td>{{ $riderCash->ibft_collection }}</td>
                            <td>{{ $riderCash->ibft_comment }}</td>
                            <td>{{ $riderCash->remaining_amount }}</td>
                            <td>{{ ($riderCash->note) ? $riderCash->note : 'N\A'}}</td>
                            <td>{{ Date('d M Y', strtotime($riderCash->created_at)) }}</td>
                            </td>
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

$(function () {
    /* Data Table */
    var table = $('#example').DataTable({
        'lengthMenu': [100, 250, 500, 1000, 5000, 10000],
        'pageLength': 100
    });
});
</SCRIPT>
<SCRIPT language="javascript">
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