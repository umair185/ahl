@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Complete Pickup Request Lists</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Timing</th>
                            <th>Vendor</th>
                            <th>Location Address</th>
                            <th>Pickup Date</th>
                            <th>Estimated Parcel</th>
                            <th>Picked Parcels</th>
                            <th>Pickup Location</th>
                            <th>Picker</th>
                            <th>Request Created At</th>
                            <th>Completed At</th>
                            <th>Download Report</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completePickupRequest as $pickRequest)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $pickRequest->requestTiming->vendorTiming->timings }}</td>
                            <td>{{ $pickRequest->vendorName->vendor_name }}</td>
                            <td>{{ $pickRequest->pickupLocation->address }}</td>
                            <td>{{ date('d M Y', strtoTime($pickRequest->pickup_date)) }}</td>
                            <td>{{ $pickRequest->estimated_parcel }}</td>
                            <td>{{ count($pickRequest->scanParcel) }}</td>
                            <td>{{ $pickRequest->pickupLocation->address }}</td>
                            <td>{{ $pickRequest->assignRequest ? $pickRequest->assignRequest->pickerName->name : '' }}</td>
                            <td>{{ date('d M Y h:i A', strtoTime($pickRequest->created_at)) }}</td>
                            <td>{{ date('d M Y h:i A', strtoTime($pickRequest->updated_at)) }}</td>
                            <td><a href="{{route('pickupRequestScanParcelListpdf', $pickRequest->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>Download</button></a></td>
                            <td><a href="{{route('pickupRequestScanParcelList', $pickRequest->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>View Scan Parcels</button></a></td>
                            
                        </tr>
                       @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection