@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Rider Cash Report</h5>
                <form method="get">
                    <div class="row">
                        <div class="col-xl-10 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                if (isset($_GET['date'])) {
                                    echo $_GET['date'];
                                }
                                ?>">
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
                <!-- <div class="text-right">
                    <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
                </div> -->
            </div>
            <div class="row">
                <div class="col-xl-6">
                    <div class="card-block table-border-style">
                        <h5>Cash to be Collected</h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="example">
                                <thead>
                                    <tr>
                                        <th>Rider Name</th>
                                        <th>Total Cash</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rider_details as $key => $rider_detail)
                                    <tr>
                                        <td>{{$rider_detail->name}}</td>
                                        <td>{{round($rider_detail->total)}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card-block table-border-style">
                        <h5>Cash Collected</h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="example">
                                <thead>
                                    <tr>
                                        <th>Rider Name</th>
                                        <th>Total Cash Collected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rider_cash_details as $key => $rider_cash_detail)
                                    <tr>
                                        <td>{{$rider_cash_detail->name}}</td>
                                        <td>{{round($rider_cash_detail->total)}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
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