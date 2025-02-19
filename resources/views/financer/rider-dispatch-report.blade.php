@extends('layouts.app')

@section('content')

<div class="page-body"> 
	@if (session('success'))
	    <div class="alert alert-success">
	        {{ session('success') }}
	    </div>
	@endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Rider's Dispatch Report</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('riderDispatchReportDownload')}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                    	<div class="col-md-4">
	                            <div class="form-group form-static-label form-default">
	                                <input type="date" name="date" id="date" class="form-control" required="required">
	                            </div>
	                        </div>

							<div class="col-md-4">
	                            <select name="rider_city" class="form-control">
	                               <option value="any">Any</option>
								   @foreach($cities as $city)
	                               <option value="{{$city->id}}">{{$city->name}}</option>
								   @endforeach
								</select>
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
@endsection