@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Pickup Supervisor Riders List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Picker Name</th>
                            <th>Picker ID</th>
                            <th>Picker Phone Number</th>
                            <th>Joining Date</th>
                            <th>Joining Days</th>
                            <th>Date & Time</th>
                            <th>Assigned By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentlyAssignedRiders as $key => $rider)
                            <tr>
                                <th scope="row">{{ ++$key }}</th>
                                <td>{{$rider->name}}</td>
                                <td>{{$rider->user_id}}</td>
                                <td>{{$rider->userDetail ? $rider->userDetail->phone : ''}}</td>
                                @if(!empty($rider->userDetail->joining_date))
                                <td>{{date('d-M-Y', strtotime($rider->userDetail ? $rider->userDetail->joining_date : ''))}}</td>
                                <td>{{\Carbon\Carbon::parse($rider->userDetail->joining_date)->diffInDays(\Carbon\Carbon::now())}} Days</td>
                                @else
                                <td></td>
                                <td></td>
                                @endif
                                @if($rider->pickup_datentime == NULL)
                                <td></td>
                                @else
                                <td>{{date('d M Y h:i a', strtoTime($rider->pickup_datentime))}}</td>
                                @endif
                                <td>{{$rider->pickerAssignedBy ? $rider->pickerAssignedBy->name : ''}}</td>
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