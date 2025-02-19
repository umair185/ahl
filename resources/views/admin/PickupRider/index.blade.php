@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Picker Riders</h5>
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                    <li><i class="fa fa-window-maxiprintableAreamize full-card"></i></li>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Assigned Vendors</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($picker as $key => $pickup_rider)
                            <tr>
                                <th scope="row">{{ ++$key }}</th>
                                <td>{{$pickup_rider->name}}</td>
                                <td>{{$pickup_rider->email}}</td>
                                
                                <td>
                                @foreach($pickup_rider->pickerVendor as $key => $pickerVendorList)
                                    {{ $pickerVendorList->vendor ? $pickerVendorList->vendor->vendor_name : '' }},
                                @endforeach
                                </td>
                                <td><a href="{{route('assignVendor', $pickup_rider->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Assign Vendors</button></a></td>
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