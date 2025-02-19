@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .anyClass {
      height:150px;
      overflow-y: scroll;
    }
</style>
@endsection

@section('content')

<div class="page-body"> 

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Staff</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('deliveryRatio')}}" method="POST">
	                   	@csrf
	                    <div class="row">
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="from" id="from" class="form-control" required="required" value="<?php
                                    if (isset($_POST['from'])) {
                                        echo $_POST['from'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                                    if (isset($_POST['to'])) {
                                        echo $_POST['to'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select name="vendor" id="vendor" class="form-control" required="required">
                                        <option value="any">Any</option>
                                        @foreach($vendors_data as $vendor)
                                        <option {{$vendorRequest == $vendor->id ? 'selected' : ''}} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

	                        <div class="col-md-2">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Submit
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-6 col-md-6">
                            <h6>Overall Delivery Ratio</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{ number_format($overall_wining_ratio) }}%</h5>
                        </div>
                        <div class="col-xl-6 col-md-6">
                            <h6>Overall Return Ratio</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{ number_format($overall_return_ratio) }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="text-right">
                    <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
                </div>
                <div class="card-block table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover" id="example">
                            <thead>
                                <tr>
                                    <th style="font-weight: bold;">Vendor Name</th>
                                    <th style="font-weight: bold;">Total Parcel</th>
                                    <th style="font-weight: bold;">Delivered Parcel</th>
                                    <th style="font-weight: bold;">Cancel Parcel</th>
                                    <th style="font-weight: bold;">Delivery Ratio</th>
                                    <th style="font-weight: bold;">Return Ratio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendor_name as $key => $vendor)
                                <tr>
                                    <td style="color: black; font-size: 14px">{{$vendor}}</td>
                                    <td style="color: black; font-weight: bold; font-size: 14px">{{number_format($vendor_total_order[$key])}}</td>
                                    <td style="color: black; font-weight: bold; font-size: 14px">{{number_format($vendor_delivered_order[$key])}}</td>
                                    <td style="color: black; font-weight: bold; font-size: 14px">{{number_format($vendor_cancel_order[$key])}}</td>
                                    <td style="color: black; font-weight: bold; font-size: 14px">{{number_format($vendor_success_ratio[$key])}} %</td>
                                    <td style="color: black; font-weight: bold; font-size: 14px">{{number_format($vendor_failure_ratio[$key])}} %</td>
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
@endsection

@section('custom-js')
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