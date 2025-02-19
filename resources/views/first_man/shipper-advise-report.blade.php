@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h5>Shipper Advise Report</h5>
                </div>
                <div class="card-block">
                    <form method="get">
	                   	@csrf
	                    <div class="row">
	                        <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="from" id="from" class="form-control" required value="<?php
                                    if (isset($_GET['from'])) {
                                        echo $_GET['from'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="to" id="to" class="form-control" required value="<?php
                                    if (isset($_GET['to'])) {
                                        echo $_GET['to'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-xl-4 col-md-6">
                                <div class="form-group">
                                    <select name="vendor" id="vendor" class="form-control" required>
                                        <option value="any">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                        <option {{$vendorRequest == $vendor->id ? 'selected' : ''}} value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

	                        <div class="col-md-2">
	                            <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
	                            	Filter
	                        	</button>
	                        </div>
	                    </div>
	                </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5>Shipper Advise Report</h5>
            <div class="text-right">
                <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="card proj-progress-card">
                <div class="card-block">
                    <div class="row">
                    	<?php
                    		$total = 0;
                    		$delivered = 0;
                    		$un_delivered = 0;

                    		foreach($shipper_advise as $shiper)
                    		{
                                if($vendorRequest != 'any')
                                {
                                    if($vendorRequest == $shiper->Order->vendor_id)
                                    {
                                        $get_order = $shiper->Order ? $shiper->Order->order_status : '';

                                        if($get_order == 6)
                                        {
                                            $delivered = $delivered + 1;
                                        }
                                        else
                                        {
                                            $un_delivered = $un_delivered + 1;
                                        }
                                    }
                                }
                                else
                                {
                                    $get_order = $shiper->Order ? $shiper->Order->order_status : '';

                                    if($get_order == 6)
                                    {
                                        $delivered = $delivered + 1;
                                    }
                                    else
                                    {
                                        $un_delivered = $un_delivered + 1;
                                    }
                                }
                    		}
                    	?>
                        <div class="col-xl-3 col-md-6">
                            <h6>Total Advises</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$delivered + $un_delivered}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Delivered Advises</h6>
                            <h5 class="m-b-30 f-w-700 text-c-green">{{$delivered}}</h5>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <h6>Un-Delivered Advises</h6>
                            <h5 class="m-b-30 f-w-700 text-c-red">{{$un_delivered}}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

	    <div class="card-block table-border-style">
	        <div class="table-responsive">
	            <table class="table table-hover" id="example">
	                <thead>
	                    <tr>
	                        <th>#</th>
                            <th>Order Number</th>
	                        <th>Vendor Name</th>
                            <th>Parcel Attempts</th>
	                        <th>Order Status</th>
	                        <th>Vendor Advise</th>
							<th>Vendor Advise At</th>
	                        <th>AHL Reply</th>
							<th>AHL Reply At</th>
	                    </tr>
	                </thead>
	                <tbody>
	                    @foreach($shipper_advise as $key => $shipper)
                        @if($vendorRequest != 'any')
                        @if($vendorRequest == $shipper->Order->vendor_id)
	                    <tr>
	                        <th scope="row">{{ ++$key }}</th>
                            <td>{{$shipper->Order ? $shipper->Order->order_reference : ''}}</td>
	                        <td>{{$shipper->Order ? $shipper->Order->vendor->vendor_name : ''}}</td>
                            @if(!empty($shipper->Order->parcel_limit))
                            <td> {{ $shipper->Order ? $shipper->Order->parcel_attempts : '' }} / {{ $shipper->Order ? $shipper->Order->parcel_limit : '' }} </td>
                            @else
                            <td>0 / 0</td>
                            @endif
	                        <td>{{$shipper->Order ? $shipper->Order->orderStatus->name : ''}}</td>
	                        <td>{{$shipper->advise}}</td>
	                        <td>{{date('d M Y H:i A', strtoTime($shipper->created_at))}}</td>
	                        <td>{{$shipper->ahl_reply}}</td>
	                        @if(!empty($shipper->ahl_reply))
	                        <td>{{date('d M Y H:i A', strtoTime($shipper->updated_at))}}</td>
	                        @else
	                        <td></td>
	                        @endif
	                    </tr>
                        @endif
                        @else
                        <tr>
                            <th scope="row">{{ ++$key }}</th>
                            <td>{{$shipper->Order ? $shipper->Order->order_reference : ''}}</td>
                            <td>{{$shipper->Order ? $shipper->Order->vendor->vendor_name : ''}}</td>
                            @if(!empty($shipper->Order->parcel_limit))
                            <td> {{ $shipper->Order ? $shipper->Order->parcel_attempts : '' }} / {{ $shipper->Order ? $shipper->Order->parcel_limit : '' }} </td>
                            @else
                            <td>0 / 0</td>
                            @endif
                            <td>{{$shipper->Order ? $shipper->Order->orderStatus->name : ''}}</td>
                            <td>{{$shipper->advise}}</td>
                            <td>{{date('d M Y H:i A', strtoTime($shipper->created_at))}}</td>
                            <td>{{$shipper->ahl_reply}}</td>
                            @if(!empty($shipper->ahl_reply))
                            <td>{{date('d M Y H:i A', strtoTime($shipper->updated_at))}}</td>
                            @else
                            <td></td>
                            @endif
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