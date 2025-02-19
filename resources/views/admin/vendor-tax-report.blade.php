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
                    <h5>Vendor Tax Invoice</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('vendorTaxInvoiceDownload')}}" method="POST">
	                   	@csrf
	                    <div class="row">
	                    	<div class="col-md-4">
	                            <div class="form-group form-static-label form-default">
	                                <input type="date" name="date_from" id="date_from" class="form-control" required="required">
	                            </div>
	                        </div>

	                        <div class="col-md-4">
	                            <div class="form-group form-static-label form-default">
	                                <input type="date" name="date_to" id="date_to" class="form-control" required="required">
	                            </div>
	                        </div>

	                        <div class="col-md-4">
	                            <div class="form-group form-static-label form-default">
	                                <select id="vendors" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{ old('vendor_id') }}">
	                                    <option selected="" disabled="" hidden="">Select Vendor</option>
	                                    @foreach($vendors as $key=> $vendor)
	                                        <option value="{{$vendor->id}}" value="@if(old('vendor_id')) {{ old('vendor_id') }} @endif" @if(old('vendor_id')) {{ 'selected' }} @endif>{{$vendor->vendor_name}}</option>
	                                    @endforeach
	                                </select>
	                                <span class="form-bar"></span>
	                                @error('vendor_id')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
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