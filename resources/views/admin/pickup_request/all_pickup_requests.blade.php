@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h5>Vendors Pickup Requests</h5>
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
                            <th>Vendor Name</th>
                            <th>Pickup Date</th>
                            <th>Time Slot</th>
                            <th>Pickup Location</th>
                            <th>Est. Parcels</th>
                            <th>Remarks</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pickup_requests as $key => $pickup_request)
                        <tr>
                            <th scope="row">{{++$key}}</th>
                            <td>{{$pickup_request->vendorName->vendor_name}}</td>
                            <td>{{date('d M Y', strtoTime($pickup_request->pickup_date))}}</td>
                            <td>{{$pickup_request->requestTiming->vendorTiming->timings}}</td>
                            <td>{{$pickup_request->pickupLocation->address}}</td>
                            <td>{{$pickup_request->estimated_parcel}}</td>
                            <td>{{$pickup_request->remarks}}</td>
                            <td>{{date('d M Y H:i A', strtoTime($pickup_request->created_at))}}</td>
                            <td>
                                <a href="{{route('assignRequest', $pickup_request->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Send to Picker</button></a>
                                <a href="{{route('forceRequestDelete', $pickup_request->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Delete</button></a>
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
    var table = $('#example').DataTable();
});
</SCRIPT>
@endsection