@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
<style type="text/css">
    .selected {
        background-color: brown !important;
        color: #FFF !important;
    }
</style>
@endsection

@section('content')

<div class="page-body"> 
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Riders Record</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('bulkStatusView')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                    if (isset($_POST['date'])) {
                                        echo $_POST['date'];
                                    }
                                    ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group form-static-label form-default">
                                    <select id="riders" name="staff_id" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('staff_id') }}" required="">
                                        <option selected="" disabled="" hidden="">Select Staff</option>
                                        @foreach($staffList as $key=> $staff)
                                            <option value="{{$staff->id}}" @if(isset($_POST['staff_id']) && $_POST['staff_id'] == $staff->id) {{ 'selected' }} @endif>{{$staff->name}} ( {{ $staff->userDetail->cnic }} )</option>
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
</div>

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Rider Parcels List</h5>
            </div>
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reference Number</th>
                                <th>Current Status</th>
                            </tr>
                        </thead>
                        <tbody id="bulkselection">
                            @foreach($defaultOrders as $key => $defaultOrder)
                            <tr>
                                <td>{{$defaultOrder->id}}</td>
                                <td>{{$defaultOrder->order_reference}}</td>
                                <td>{{$defaultOrder->orderStatus->name}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <input type="button" name="OK" class="ok btn-primary" value="Mark as Delivered"/>
                </div>
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
        pageLength : 100
    });
});
$("#bulkselection tr").click(function(){
   $(this).toggleClass('selected');    
});

$('.ok').on('click', function(e){
    var selected = [];
    $("#bulkselection tr.selected").each(function(){
        selected.push($('td:first', this).html());
    });
    if (selected.length == 0) {
        alert('please select some paracels to change their status');
    }
    else {
        //var newWin = window.open();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            /* the route pointing to the post function */
            url: '/mark-bulk-status',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, paracels: selected},
            
            success: function (data) {
                location.reload();
            }
        });
    }
});
</SCRIPT>
@endsection