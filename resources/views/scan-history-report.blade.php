@extends('layouts.app')

@section('content')

<div class="col-xl-12">
    <div class="card proj-progress-card">
        <div class="card-block">
            <form method="get">
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="date" id="date" class="form-control" max="{{$today_date}}" required="required" value="<?php
                            if (isset($_GET['date'])) {
                                echo $_GET['date'];
                            }
                            ?>" onChange="restaurantTime()">
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to" id="to" class="form-control" max="{{$today_date}}" required="required" value="<?php
                            if (isset($_GET['to'])) {
                                echo $_GET['to'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="form-group">
                            <label>Select Status</label>
                            <select name="status_value" id="status_value" class="form-control" required="required">
                                <option>Any</option>
                                <option @if (isset($_GET['status_value'])){{$_GET['status_value'] == 'at_ahl' ? 'selected' : ''}}@endif value="at_ahl">At AHL & Re-attempt Parcels</option>
                                <option @if (isset($_GET['status_value'])){{$_GET['status_value'] == 'cancel' ? 'selected' : ''}}@endif value="cancel">Cancel Parcels</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-6">
                        <div class="form-group">
                            <br>
                            <button type="submit" class="btn btn-primary mt-1">Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if(!empty($remarks_data))
<div class="col-xl-12">
    <div class="card proj-progress-card">
        <div class="card-block">
            <div class="row">
                <div class="col-xl-4 col-md-4">
                    <h6>Total Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-blue">{{$remarks_data->total_parcels}}</h5>
                </div>
                <div class="col-xl-4 col-md-4">
                    <h6>Total Scanned Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-green">{{$remarks_data->scan_parcels}}</h5>
                </div>
                <div class="col-xl-4 col-md-4">
                    <h6>Missing Parcels</h6>
                    <h5 class="m-b-30 f-w-700 text-c-red">{{$remarks_data->total_parcels - $remarks_data->scan_parcels}}</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-md-12">
                    <h6>Remarks</h6>
                    <h5 class="m-b-30 f-w-700 text-c-blue">{{$remarks_data->remarks}}</h5>
                </div>
                <div class="col-xl-12 col-md-12">
                    <h6>Remarks By</h6>
                    <h5 class="m-b-30 f-w-700 text-c-blue">{{$remarks_data->remarksBy ? $remarks_data->remarksBy->name : ''}}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="col-xl-12 col-md-12">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 id="show-data-content">Parcels List</h5>
                </div>
                <div class="card-block table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Ref #</th>
                                    <th>Age</th>
                                    <th>Scan By</th>
                                    <th>Scan At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($find_rack_parcel_all_sets as $key => $find_rack_parcel_all_set)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{$find_rack_parcel_all_set->orderDetail->order_reference}}</td>
                                    @if(!empty($find_rack_parcel_all_set->orderDetail->scanOrder->middle_man_scan_date))
                                    <td>{{\Carbon\Carbon::parse($find_rack_parcel_all_set->orderDetail->scanOrder->middle_man_scan_date)->diffInDays(\Carbon\Carbon::parse($find_rack_parcel_all_set->created_at))}} Days</td>
                                    @else
                                    <td></td>
                                    @endif
                                    <td>{{$find_rack_parcel_all_set->userDetail ? $find_rack_parcel_all_set->userDetail->name : ''}}</td>
                                    <td>{{date('d-M-Y h:i a', strtotime($find_rack_parcel_all_set->created_at))}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script type="text/javascript">
  function restaurantTime() {
    var time = $("#date").val();

    $("#to").attr({
      "min" : time,
    });
  }
</script>
@endsection