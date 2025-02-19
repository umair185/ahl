@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Sales Staff</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Assigned Vendors</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales_staff as $key => $sale)
                            <tr>
                                <th scope="row">{{ ++$key }}</th>
                                <td>{{$sale->name}}</td>
                                <td>{{$sale->userDetail ? $sale->userDetail->phone : ''}}</td>
                                <td>{{$sale->email}}</td>
                                <td>
                                    @foreach($sale->saleVendor as $key => $saleVendorList)
                                        {{ $saleVendorList->vendor_name}},
                                    @endforeach
                                </td>
                                <td><a href="{{route('assignSale', $sale->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Assign Vendors</button></a></td>
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