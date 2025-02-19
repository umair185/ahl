@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 2%; /* or whatever */
    }
    .heading
    {
        font-weight: bold !important;
    }
</style>
@endsection
@section('content')

<div class="page-body">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Update Parcel Weight</h5>
                </div>
                @if(session()->has('success'))
                    <div class="alert alert-success">
                        {{ session()->get('success') }}
                    </div>
                @endif
                <div class="card-block">
                    <div class="row">
                        <div class="col-6">
                            <h5>Order Reference Number : {{$find_parcel->order_reference}}</h5>
                            <h5>Customer Name: {{$find_parcel->consignee_first_name}}  {{$find_parcel->consignee_last_name}}</h5>
                            <h5>Customer Phone Number : {{$find_parcel->consignee_phone}}</h5>
                            <h5>Customer Address : {{$find_parcel->consignee_address}}</h5>
                        </div>
                        <div class="col-6">
                            <h3>Parcel Weight: {{$find_parcel->vendorWeight->ahlWeight->weight}} KG</h3>
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <form method="POST" class="form-material" action="{{ route('saveChangeWeight') }}">
                        @csrf
                        <input type="hidden" value="{{$find_parcel->id}}" name="parcel_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select id="vendor_weight_id" name="vendor_weight_id" class="form-control  @error('vendor_weight_id') is-invalid @enderror" value="{{ old('vendor_weight_id') }}">
                                        <option selected="" disabled="" hidden="">Select Parcel Weight</option>
                                        @foreach($vendor_weights as $key=> $vendorWeight)
                                            <option value="{{$vendorWeight->id}}" value="@if(old('vendor_weight_id')) {{ old('vendor_weight_id') }} @endif" @if(old('vendor_weight_id')) {{ 'selected' }} @endif>{{$vendorWeight->ahlWeight->weight . ' ' . 'Kg'}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('vendor_weight_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Weight</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection