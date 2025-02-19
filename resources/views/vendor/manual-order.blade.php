@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<style type="text/css">
    .divider {
      margin-top: 5%; /* or whatever */
    }
</style>
@endsection
@section('content')
@if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
<div class="page-body">
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Manual Order</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveManualOrder') }}">
                        @csrf
                        <h4 class="sub-title divider">SHIPPER</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" id="vendor_id" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{$vendor->id}}">
                                    <input type="text" name="vendor_name" class="form-control  @error('vendor_name') is-invalid @enderror" value="{{$vendor->vendor_name}}" disabled>
                                    <span class="form-bar"></span>
                                    <!--<label class="float-label">Name</label>-->
                                    @error('vendor_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="vendor_email" class="form-control  @error('vendor_email') is-invalid @enderror" value="{{$vendor->vendor_email}}" disabled>
                                    <span class="form-bar"></span>
                                    <!--<label class="float-label">Email</label>-->
                                    @error('vendor_email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="vendor_address" class="form-control  @error('vendor_address') is-invalid @enderror" value="{{$vendor->vendor_address}}" disabled>
                                    <span class="form-bar"></span>
                                    <!--<label class="float-label">Address</label>-->
                                    @error('vendor_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        

                        <h4 class="sub-title divider">CONSIGNEE</h4>
                        <div class="row">
                            <div class="col-md-12">
                            <div class="form-group form-static-label form-default">
                                <select id="parcel_nature" name="parcel_nature" class="form-control  @error('parcel_nature') is-invalid @enderror" value="{{ old('parcel_nature') }}" required>
                                    <option selected="" disabled="" hidden="">Select Parcel Nature</option>
                                    @foreach($parcelNatures as $key=> $parcelNature)
                                        <option value="{{$parcelNature->id}}" value="@if(old('parcel_nature')) {{ old('parcel_nature') }} @endif" @if(old('parcel_nature')) {{ 'selected' }} @endif>{{$parcelNature->name}}</option>
                                    @endforeach
                                </select>
                                <span class="form-bar"></span>
                                @error('parcel_nature')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="consignee_phone" class="form-control  @error('consignee_phone') is-invalid @enderror" value="{{ old('consignee_phone') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Phone</label>
                                    @error('consignee_phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <input type="text" name="consignee_first_name" class="form-control  @error('consignee_first_name') is-invalid @enderror" value="{{ old('consignee_first_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">First Name</label>
                                    @error('consignee_first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-success">
                                    <input type="text" name="consignee_last_name" class="form-control  @error('consignee_last_name') is-invalid @enderror" value="{{ old('consignee_last_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Last Name</label>
                                    @error('consignee_last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="consignee_email" class="form-control  @error('consignee_email') is-invalid @enderror" value="{{ old('consignee_email') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Email</label>
                                    @error('consignee_email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group form-static-label form-primary">
                                    <input type="text" name="consignee_address" class="form-control  @error('consignee_address') is-invalid @enderror" value="{{ old('consignee_address') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Address</label>
                                    @error('consignee_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <select id="consignee_country" name="consignee_country" class="form-control  @error('consignee_country') is-invalid @enderror" value="{{ old('consignee_country') }}">
                                        <option selected="" disabled="" hidden="">Select Country</option>
                                        @foreach(Helper::getCountry() as $key=> $country)
                                            <option value="{{$country->id}}" value="@if(old('consignee_country')) {{ old('consignee_country') }} @endif" @if(old('consignee_country')) {{ 'selected' }} @endif>{{$country->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignee_country')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select id="consignee_state" name="consignee_state" class="form-control  @error('consignee_state') is-invalid @enderror" value="{{ old('consignee_state') }}">
                                        <option selected="" disabled="" hidden="">Select State</option>
                                        @foreach(Helper::getStates() as $key=> $state)
                                            <option value="{{$state->id}}" value="@if(old('consignee_state')) {{ old('consignee_state') }} @endif" @if(old('consignee_state')) {{ 'selected' }} @endif>{{$state->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignee_state')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select id="consignee_city" name="consignee_city" class="form-control  @error('consignee_city') is-invalid @enderror" value="{{ old('consignee_city') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach(Helper::getCities() as $key=> $city)
                                            <option value="{{$city->id}}" value="@if(old('consignee_city')) {{ old('consignee_city') }} @endif" @if(old('consignee_city')) {{ 'selected' }} @endif>{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignee_city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="sub-title divider">CONSIGNEMENT</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="consignment_order_id" class="form-control  @error('consignment_order_id') is-invalid @enderror" value="{{ old('consignment_order_id') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Order ID</label>
                                    @error('consignment_order_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select name="consignment_order_type" class="form-control  @error('consignment_order_type') is-invalid @enderror" value="{{ old('consignment_order_type') }}">
                                        <option selected="" disabled="" hidden="">Select Order Type</option>
                                        @foreach($order_type as $order_types)
                                        <option value="{{$order_types->id}}" value="@if(old('$order_types')) {{ old('$order_types') }} @endif" @if(old('$order_types')) {{ 'selected' }} @endif>{{$order_types->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignment_order_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-success">
                                    <input type="text" name="consignment_cod_price" class="form-control  @error('consignment_cod_price') is-invalid @enderror" value="{{ old('consignment_cod_price') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">COD Price</label>
                                    @error('consignment_cod_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="consignment_weight" class="form-control  @error('consignment_weight') is-invalid @enderror" value="{{ old('consignment_weight') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Weight</label>
                                    @error('consignment_weight')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <select id="vendor_weight_id" name="vendor_weight_id" class="form-control  @error('vendor_weight_id') is-invalid @enderror" value="{{ old('vendor_weight_id') }}">
                                        <option selected="" disabled="" hidden="">Select Parcel Weight</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('vendor_weight_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select name="consignment_packaging" class="form-control  @error('consignment_packaging') is-invalid @enderror" value="{{ old('consignment_order_type') }}">
                                        <option selected="" disabled="" hidden="">Select Packing</option>
                                        @foreach($packing as $packings)
                                        <option value="{{$packings->id}}" value="@if(old('$packings')) {{ old('$packings') }} @endif" @if(old('$packings')) {{ 'selected' }} @endif>{{$packings->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignment_packaging')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <input type="text" name="consignment_pieces" class="form-control  @error('consignment_pieces') is-invalid @enderror" value="{{ old('consignment_pieces') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Pieces</label>
                                    @error('consignment_pieces')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <span class="form-bar"></span>
                                    <textarea rows="5" cols="5" name="consignment_description" class="form-control  @error('consignment_description') is-invalid @enderror" value="{{ old('consignment_description') }}" placeholder="Parcel Description"></textarea>
                                    @error('consignment_description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-primary">
                                    <select name="consignment_pickup_location" class="form-control  @error('consignment_pickup_location') is-invalid @enderror" value="{{ old('consignment_pickup_location') }}">
                                        <option selected="" disabled="" hidden="">Select Pickup Location</option>
                                        @foreach($pickup_location as $pickup_locations)
                                        <option value="{{$pickup_locations->id}}" value="@if(old('$pickup_locations')) {{ old('$pickup_locations') }} @endif" @if(old('$pickup_locations')) {{ 'selected' }} @endif>{{$pickup_locations->address}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignment_pickup_location')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-primary">
                                    <select name="consignment_origin_city" class="form-control  @error('consignment_origin_city') is-invalid @enderror" value="{{ old('consignment_origin_city') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach(Helper::getCities() as $key=> $city)
                                            <option value="{{$city->id}}" @if($city->id == 31456) {{ 'selected' }} @endif>{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('consignment_origin_city')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
 
                        <h4 class="sub-title divider">Additional Services</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="additional_services_type" class="form-control  @error('additional_services_type') is-invalid @enderror" value="{{ old('additional_services_type') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Type</label>
                                    @error('additional_services_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Parcel</button>
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
        $('#consignee_city').change(function(){
           var city_id = $(this).val();
           var vendorId = $("#vendor_id").val();  
           // alert(vendorId);
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
           $.ajax({  
                url: '/select-city-data',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, vendor_id: vendorId, city_id: city_id},
                dataType: 'json',
                success:function(data){ 
                    if(data.status == 'success'){
                        $('#vendor_weight_id').html(data.html_data);
                    }                    
                }  
           });  
      });  
    });

</SCRIPT>
@endsection

