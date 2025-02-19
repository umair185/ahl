@extends('layouts.app')

@section('custom-css')
<style type="text/css">

</style>
@endsection
@section('content')

<div class="page-body">
    
    <div class="card">
        <div class="card-header">
            <h5>Pickup Requests</h5>
            <span>Pickup Request History</span>
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
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pickup Date</th>
                            <th>Time Slot</th>
                            <th>Pickup Location</th>
                            <th>Est. Parcels</th>
                            <th>Remarks</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pickup_requests as $pickup_request)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{date('d M Y', strtoTime($pickup_request->pickup_date))}}</td>
                            <td>{{$pickup_request->requestTiming->vendorTiming->timings}}</td>
                            <td>{{$pickup_request->pickupLocation->address}}</td>
                            <td>{{$pickup_request->estimated_parcel}}</td>
                            <td>{{$pickup_request->remarks}}</td>
                            <td>{{date('d M Y H:i A', strtoTime($pickup_request->created_at))}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection