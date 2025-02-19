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
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Generate Pickup Request</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveFirstManPickUp') }}">
                        @csrf
                        <h4 class="sub-title divider heading">Pickup Request</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select name="vendor" id="select_vendor" class="form-control  @error('vendor') is-invalid @enderror" required="" value="{{ old('vendor') }}">
                                        <option selected="" disabled="" hidden="">Select Vendor</option>
                                        @foreach($all_vendors as $key=> $vendor)
                                            <option value="{{$vendor->id}}" value="@if(old('vendor')) {{ old('vendor') }} @endif" @if(old('vendor')) {{ 'selected' }} @endif>{{$vendor->vendor_name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('vendor')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>    
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="pickup_date" class="form-control  @error('pickup_date') is-invalid @enderror" value="{{ old('pickup_date') }}">
                                    <span class="form-bar"></span>
                                    
                                    @error('pickup_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="estimated_parcel" class="form-control  @error('estimated_parcel') is-invalid @enderror" min="0" max="10000" value="{{ old('estimated_parcel') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Estimated Parcel</label>
                                    @error('estimated_parcel')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="city" class="form-control  @error('city') is-invalid @enderror" required="" value="{{ old('city') }}">
                                        <option selected="" disabled="">Select City</option>
                                        @foreach($cities as $key=> $city)
                                            <option value="{{$city->id}}" value="@if(old('city')) {{ old('city') }} @endif" @if(old('city')) {{ 'selected' }} @endif>{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="pickup_location" id="pickup_location" class="form-control  @error('pickup_location') is-invalid @enderror" value="{{ old('pickup_location') }}">
                                        <option selected="" disabled="" hidden="">Select Pickup Location</option>
                                        
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('pickup_location')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="time_slot" id="time_slot" class="form-control  @error('time_slot') is-invalid @enderror" value="{{ old('time_slot') }}">
                                        <option selected="" disabled="" hidden="">Select Time Slot</option>
                                        
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('time_slot')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" min="0" max="10000" value="{{ old('remarks') }}" placeholder="Enter Remarks">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Remarks</label>
                                    @error('remarks')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Send Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

@section('custom-js')

<SCRIPT language="javascript">

    $(document).ready(function(){
        $('#select_vendor').change(function(){
           var vendor_id = $(this).val();  
        //    alert(vendor_id);
           $.ajax({  
                url:"select-vedor-data/"+vendor_id,
                method:"GET",
                data:{"_token":"{{ csrf_token() }}"},
                datatype:"json",
                success:function(data){  
                     
                    $('#time_slot').empty();
                    $('#pickup_location').empty();
                    
                    $.each(data.timing, function (key, val)
                    {
                        $('#time_slot').append('<option value="' + val['timing_slot_id'] + '" >' + val['vendor_timing']['timings'] + '</option>');  
                    });

                    $.each(data.location, function (key, val)
                    {
                        $('#pickup_location').append('<option value="' + val['id'] + '" >' + val['address'] + '</option>');  
                    });
                    
                }  
           });  
      });  
    });

</SCRIPT>
@endsection