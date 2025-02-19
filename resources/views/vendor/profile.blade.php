@extends('layouts.app')

@section('content')

<div class="page-body">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Printing Slip</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('savePrintingSlip') }}">
                        @csrf
                        <h4 class="sub-title divider">Select Number of Prints</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="hidden" name="vendor_id" value="{{$vendor_details->id}}">
                                <div class="form-group form-static-label form-default">
                                    <select id="printing_slip" name="printing_slip" class="form-control  @error('printing_slip') is-invalid @enderror" value="{{ old('printing_slip') }}" required>
                                        <option selected="" disabled="" hidden="">Select Number of Printing Slips</option>
                                        <option value="1" {{$vendor_details->printing_slips == 1 ? 'selected' : ''}}>1</option>
                                        <option value="2" {{$vendor_details->printing_slips == 2 ? 'selected' : ''}}>2</option>
                                    </select>
                                    <span class="form-bar"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">Save Slips</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
<div class="page-body">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Vendor Details</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-4">
                            <h4 class="sub-title divider">Vendor POC</h4>
                            <br>
                            <h6>{{$vendor_details->focal_person_name}}</h6>
                        </div>
                        <div class="col-md-4">
                            <h4 class="sub-title divider">Vendor Email</h4>
                            <br>
                            <h6>{{$vendor_details->focal_person_email}}</h6>
                        </div>
                        <div class="col-md-4">
                            <h4 class="sub-title divider">Vendor POC Number</h4>
                            <br>
                            <h6>{{$vendor_details->focal_person_phone}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>AHL Details</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="sub-title divider">Account Created By</h4>
                            <br>
                            <h6>{{$vendor_details->createdBy ? $vendor_details->createdBy->name : ''}}</h6>
                        </div>
                        <div class="col-md-6">
                            <h4 class="sub-title divider">Vendor Registered At</h4>
                            <br>
                            <h6>{{date('d M Y h:i a', strtoTime($vendor_details->created_at))}}</h6>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Assigned Sales Person</h4>
                            <br>
                            <h6>{{$vendor_details->pocPerson ? $vendor_details->pocPerson->name : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Sales Person Number</h4>
                            <br>
                            <h6>{{$vendor_details->pocPerson ? $vendor_details->pocPerson->userDetail->phone : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Sales Person Email</h4>
                            <br>
                            <h6>{{$vendor_details->pocPerson ? $vendor_details->pocPerson->email : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Sales Person Assign Date</h4>
                            <br>
                            @if($vendor_details->datentime == NULL)
                            <h6></h6>
                            @else
                            <h6>{{date('d M Y h:i a', strtoTime($vendor_details->datentime))}}</h6>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Assigned CS Person</h4>
                            <br>
                            <h6>{{$vendor_details->csrPerson ? $vendor_details->csrPerson->name : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">CS Person Number</h4>
                            <br>
                            <h6>{{$vendor_details->csrPerson ? $vendor_details->csrPerson->userDetail->phone : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">CS Person Email</h4>
                            <br>
                            <h6>{{$vendor_details->csrPerson ? $vendor_details->csrPerson->email : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">CS Person Assign Date</h4>
                            <br>
                            @if($vendor_details->csr_datentime == NULL)
                            <h6></h6>
                            @else
                            <h6>{{date('d M Y h:i a', strtoTime($vendor_details->csr_datentime))}}</h6>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Assigned Pickup Supervisor</h4>
                            <br>
                            <h6>{{$vendor_details->pickupPerson ? $vendor_details->pickupPerson->name : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Pickup Supervisor Number</h4>
                            <br>
                            <h6>{{$vendor_details->pickupPerson ? $vendor_details->pickupPerson->userDetail->phone : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Pickup Supervisor Email</h4>
                            <br>
                            <h6>{{$vendor_details->pickupPerson ? $vendor_details->pickupPerson->email : ''}}</h6>
                        </div>
                        <div class="col-md-3">
                            <h4 class="sub-title divider">Pickup Supervisor Assign Date</h4>
                            <br>
                            @if($vendor_details->pickup_datentime == NULL)
                            <h6></h6>
                            @else
                            <h6>{{date('d M Y h:i a', strtoTime($vendor_details->pickup_datentime))}}</h6>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5>Additional Details</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="sub-title divider">Vendor Fuel Charges</h4>
                            <br>
                            <h6>{{$vendor_details->fuel}}</h6>
                        </div>
                        <div class="col-md-6">
                            <h4 class="sub-title divider">Vendor GST</h4>
                            <br>
                            <h6>{{$vendor_details->gst}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>Vendor Weight ID's </h5>
            <span>Use <code>Weight ID's</code> In your Bulk Order</span>
            <div class="card-header-right">
                <h5>Additional Kg</h5>
                <br>
                <h6>Rs. {{$vendor_details->addational_kgs}}</h6>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th># Weight ID</th>
                            <th>Weight</th> 
                            <th>Price</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($vendorWeights) > 0)
                            @foreach($vendorWeights as $key => $vendorWeight)
                            <tr>
                                <td>{{$vendorWeight->id}}</td>
                                <td>{{$vendorWeight->ahlWeight->weight . ' (' . $vendorWeight->city->first()->name . ')'}}</td>
                                <td>Rs. {{$vendorWeight->price}}</td>
                            </tr>
                            @endforeach
                        @else
                            <div class="alert alert-danger" role="alert">
                                First Add Your Weight Prices
                            </div>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>Vendor Pickup Locations</h5>
            <span>Use <code>Pickup Locations Id</code> In your Bulk Order</span>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th># Pickup Location ID</th>
                            <th>Location Name</th>  
                            <th>Location Status</th>  
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendorPickupLocations as $key => $location)
                        <tr>
                            <td>{{$location->id}}</td>
                            <td>{{$location->address}}</td>
                            <td>{{ Helper::status($location->status) }}</td>
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

@endsection