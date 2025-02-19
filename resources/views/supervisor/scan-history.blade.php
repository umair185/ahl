@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Scanned Parcels List</h5>
            <form method="get">
                <div class="row">
                    <div class="col-xl-5 col-md-6">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="from" id="from" class="form-control" required="required" value="<?php
                            if (isset($_GET['from'])) {
                                echo $_GET['from'];
                            }
                            ?>">
                        </div>
                    </div>
                    <div class="col-xl-5 col-md-6">
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                            if (isset($_GET['to'])) {
                                echo $_GET['to'];
                            }
                            ?>">
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
            @if (isset($_GET['to']))
            <div class="text-right">
                <a href="{{ route('supervisorScanHistoryDownload') }}/@isset($_GET['from'])@isset($_GET['to'])?from={{$_GET['from']}}&to={{$_GET['to']}}@endisset @endisset" class="btn btn-info mb-2">Export to Excel</a>
            </div>
            @endif
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive" >
                <table class="table table-hover" id="riders_table">
                    <thead>
                        <tr>
                            <th>Sr. #</th>
                            <th>Order Reference</th>
                            <th>Vendor</th>
                            <th>Destination</th>
                            <th>COD Amount</th>
                            <th>Scanned By</th>
                            <th>Rider</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scan_orders as $key => $parcel)
                        <tr>
                            <th scope="row">{{ $key++ }}</th>
                            <td>{{ $parcel->orderDetail ? $parcel->orderDetail->order_reference : '' }}</td>
                            <td>{{ $parcel->orderDetail ? $parcel->orderDetail->vendor->vendor_name : ''}}</td>
                            <td>{{ $parcel->orderDetail ? $parcel->orderDetail->customerCity->name : ''}}</td>
                            <td>{{ $parcel->orderDetail ? $parcel->orderDetail->consignment_cod_price : '' }}</td>
                            <td>{{ $parcel->scanOrder->scanBySupervisor ? $parcel->scanOrder->scanBySupervisor->name : '' }}</td>
                            <td>{{ $parcel->rider ? $parcel->rider->name : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <br>
    </div>
</div>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">

$(function () {
    /* Data Table */
    var table = $('#riders_table').DataTable();
});
</SCRIPT>
@endsection