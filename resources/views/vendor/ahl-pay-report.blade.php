@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>Financial Report</h5>
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                    <li><i class="fa fa-window-maximize full-card"></i></li>
                    <li><i class="fa fa-minus minimize-card"></i></li>
                    <li><i class="fa fa-refresh reload-card"></i></li>
                    <li><i class="fa fa-trash close-card"></i></li>
                </ul>
            </div>
        </div>

	    <div class="card-block table-border-style">
	        <div class="table-responsive">
	            <table class="table table-hover" id="example">
	                <thead>
	                    <tr>
	                        <th>#</th>
	                        <th>Invoice #</th>
	                        <th>Vendor</th>
	                        <th>Cashier</th>
	                        <th>Amount</th>
	                        <th>AHL Commission</th>
	                        <th>Remarks</th>
	                        <th>Date From</th>
	                        <th>Date To</th>
	                        <th>Amount Pay Date</th>
	                        <th>Action</th>
	                        <th>Invoice</th>
	                    </tr>
	                </thead>
	                <tbody>
	                	
	                    @foreach($vendorFinancialsReport as $financial)
	                    <tr>
	                        <th scope="row">{{ $loop->iteration }}</th>
	                        <td>Invoice #: {{$financial->invoice_number}}</td>
	                        <td>{{$financial->vendorName->vendor_name}}</td>
	                        <td>{{$financial->cashierName->name}}</td>
	                        <td>{{$financial->amount}}</td>
	                        <td>{{$financial->ahl_commission}}</td>
	                        <td>{{$financial->remarks}}</td>
	                        <td>{{date('d M Y', strtoTime($financial->date_from))}}</td>
	                        <td>{{date('d M Y', strtoTime($financial->date_to))}}</td>
	                        <td>{{date('d M Y H:i A', strtoTime($financial->created_at))}}</td>
	                        <td>
	                        	@if($financial->financial_report)
	                        	<a target="_blank" href="{{route('downloadVendorFinancialReport', $financial->id)}}"><i class="fa fa fa-download"></i></a>
	                        	@endif
	                        </td>
	                        <td><a target="_blank" href="{{route('indiviualTaxInvoice', $financial->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Download</button></a></td>
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
    var table = $('#example').DataTable();
});
</SCRIPT>
@endsection