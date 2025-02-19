@extends('layouts.app')

@section('content')


<div class="page-body">
    <div class="row">
    	<div class="col-xl-12 col-md-12">
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach($errors->all() as $error)
                        {{$error}}
                    @endforeach
                </div>
            @endif
            <div class="row">
		    	<div class="col-md-6">
                    <form action="{{ route('saveBulkOrder') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="bulk_order" id="fileupload" onchange="form.submit()" style="display:none"/>  
                        <div id="OpenFileUpload" class="card text-center order-visitor-card">
    		                <div class="card-block">
    		                    <h6 class="m-b-0">Upload</h6>
    		                    <h4 class="m-t-15 m-b-15"><i class="fa fa-arrow-up m-r-15 text-c-green"></i>Bulk Order</h4>
    		                    <p class="m-b-0">Upload Bulk Order In Excel / CSV </p>
    		                </div>
    		            </div>
                    </form>
		        </div>
		        <div class="col-md-6">
		        	<a href="{{route('exportBulkFormat')}} ">
			            <div class="card text-center order-visitor-card">
			                <div class="card-block">
			                    <h6 class="m-b-0">Download</h6>
			                    <h4 class="m-t-15 m-b-15"><i class="fa fa-arrow-down m-r-15 text-c-red"></i>Download Bulk Format</h4>
			                    <p class="m-b-0">Download AHL Excel / CSV Bulk Format</p>
			                </div>
			            </div>
		            </a>
		        </div>
		    </div>
		</div>
    </div>
</div>

<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>Vendor Pickup Locations</h5>
            <span>Use <code>Pickup Locations Id</code> In your Bulk Order</span>
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
            <h5>Bulk Format Template Details</h5>
            <span>use this <code>Bulk Format Template</code> to upload bulk orders</span>
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
                            <th># Serial No</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach($bulkFormat as $key => $format)
                        <tr>
                            <th scope="row">{{++$counter}}</th>
                            <td>{{$key}}</td>
                            <td>{{$format}}</td>
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
<script type="text/javascript">
    $('#OpenFileUpload').click(function(){ $('#fileupload').trigger('click'); });
</script>
@endsection