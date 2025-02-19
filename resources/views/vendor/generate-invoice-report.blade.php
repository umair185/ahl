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
                    <h5>Tax Invoice</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('taxInvoiceDownload')}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                    	<div class="col-md-10">
	                            <div class="form-group form-static-label form-default">
	                                <input type="date" name="date" id="date" class="form-control" required="required">
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
@endsection