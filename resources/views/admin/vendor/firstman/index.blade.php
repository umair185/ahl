@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Pickup Supervisor Staff</h5>
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
                        @foreach($firstman_staff as $key => $firstman)
                            <tr>
                                <th scope="row">{{ ++$key }}</th>
                                <td>{{$firstman->name}}</td>
                                <td>{{$firstman->userDetail ? $firstman->userDetail->phone : ''}}</td>
                                <td>{{$firstman->email}}</td>
                                <td>
                                    @foreach($firstman->pickupVendor as $key => $saleVendorList)
                                        {{ $saleVendorList->vendor_name}},
                                    @endforeach
                                </td>
                                <td><a href="{{route('assignPickup', $firstman->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Assign Vendors</button></a></td>
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