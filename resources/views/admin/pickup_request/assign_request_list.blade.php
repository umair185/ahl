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
    <button class="btn btn-danger waves-effect waves-light" id='delete_br'>Bulk Force Complete</button>
    <div class="card">
        <div class="card-header">
            <h5>Picker Assign Requests</h5>
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
                            <th><input type="checkbox" id="selectall"/></th>
                            <th>#</th>
                            <th>Vendor Name</th>
                            <th>Pickup Date</th>
                            <th>Pickup Location</th>
                            <th>Est. Parcels</th>
                            <th>Exact Picked Parcels</th>
                            <th>Picker Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($assignRequest as $key => $assign)
                        <tr>
                            <td align="center">
                                <input type="checkbox" class="case" name="case" value="{{$assign->id}}"/>
                            </td>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{ $assign->pickerRequest->vendorName->vendor_name }}</td>
                            <td>{{ date('d M Y', strtoTime($assign->pickerRequest->pickup_date)) }}</td>
                            <td>{{ $assign->pickerRequest->pickupLocation->address ?? '' }}</td>
                            <td>{{ $assign->pickerRequest->estimated_parcel }}</td>
                            <td>{{ count($assign->pickerRequest->scanParcel) }}</td>
                            <td>{{ $assign->pickerName->name }}</td>
                            <td>
                                <a href="{{route('forceRequestComplete', ['assign_id'=>$assign->id,'pickup_request_id'=>$assign->pickerRequest->id])}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Force Complete</button></a>
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

    // add multiple select / deselect functionality
    $("#selectall").click(function () {
        var checkedArray = [];
        $('.case').prop('checked', this.checked);
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
    });

    // if all checkbox are selected, check the selectall checkbox and viceversa
    $(".case").click(function () {
        if ($(".case").length == $(".case:checked").length) {
            $("#selectall").prop("checked", true);
        } else {
            $("#selectall").prop('checked', false);
        }

        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
    });

    $("#delete_br").click(function () {
        var proceed = confirm("Are you sure you want to proceed?");
        if(proceed)
        {
            var checkedArray = [];
            $("input:checkbox[name=case]:checked").each(function () {
                checkedArray.push($(this).val());
            });

            if (checkedArray.length == 0) {
                alert('please select some Request for Force Completion');
            } else {
                //var newWin = window.open();
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    /* the route pointing to the post function */
                    url: '/bulk-force-request-complete',
                    type: 'POST',
                    /* send the csrf-token and the input to the controller */
                    data: {_token: CSRF_TOKEN, pickupRequests: checkedArray},
                    dataType: 'json',
                    /* remind that 'data' is the response of the AjaxController */
                    success: function (response) {
                        if(response.status == 1){                            
                            alert('Request Force Complete Successfully, let the page refresh to view results');
                            location.reload();
                        }
                    }
                });
            }
        }
        else
        {

        }
    });
});
</SCRIPT>
@endsection