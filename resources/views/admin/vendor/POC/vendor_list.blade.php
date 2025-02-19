@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Sales Staff Vendors List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendor Name</th>
                            <th>Date & Time</th>
                            <th>Assigned By</th>
                            <th>Category</th>
                            <th>Payment Mode</th>
                            <th>Payment By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentlyAssignedVendors as $key => $vendor)
                            <tr>
                                <th scope="row">{{ ++$key }}</th>
                                <td>{{$vendor->vendor_name}}</td>
                                @if($vendor->datentime == NULL)
                                <td></td>
                                @else
                                <td>{{date('d M Y h:i a', strtoTime($vendor->datentime))}}</td>
                                @endif
                                <td>{{$vendor->pocAssignedBy ? $vendor->pocAssignedBy->name : ''}}</td>
                                <td>{{$vendor->category}}</td>
                                <td>{{$vendor->payment_mode}}</td>
                                <td>{{$vendor->payment}}</td>
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
      "lengthMenu": [ 75, 100, 500, 1000, 5000 ]
    });
});
</SCRIPT>
@endsection