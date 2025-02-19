@extends('layouts.app',$breadcrumbs)

@section('custom-css')
<style type="text/css">
    .divider {
      margin-top: 5%; /* or whatever */
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
                    <h5>Manual Order</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('updateParcel') }}">
                        @csrf
                        <h4 class="sub-title divider">SHIPPER</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{$order->vendor_id}}">
                                    <input type="hidden" name="order_id" class="form-control  @error('order_id') is-invalid @enderror" value="{{$order->id}}">
                                    <input type="text" name="vendor_name" class="form-control  @error('vendor_name') is-invalid @enderror" value="{{$order->vendor->vendor_name}}" disabled>
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
                                    <input type="text" name="vendor_email" class="form-control  @error('vendor_email') is-invalid @enderror" value="{{$order->vendor->vendor_email}}" disabled>
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
                                    <input type="text" name="vendor_address" class="form-control  @error('vendor_address') is-invalid @enderror" value="{{$order->vendor->vendor_address}}" disabled>
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
                        
                        @hasanyrole('vendor_admin|vendor_editor|admin|middle_man|csr')
                        <h4 class="sub-title divider">CONSIGNEE</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="consignee_phone" class="form-control  @error('consignee_phone') is-invalid @enderror" value="{{ $order->consignee_phone }}">
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
                                    <input type="text" name="consignee_first_name" class="form-control  @error('consignee_first_name') is-invalid @enderror" value="{{ $order->consignee_first_name }}">
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
                                    <input type="text" name="consignee_last_name" class="form-control  @error('consignee_last_name') is-invalid @enderror" value="{{ $order->consignee_last_name }}">
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
                                    <input type="text" name="consignee_email" class="form-control  @error('consignee_email') is-invalid @enderror" value="{{ $order->consignee_email }}">
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
                                    <input type="text" name="consignee_address" class="form-control  @error('consignee_address') is-invalid @enderror" value="{{ $order->consignee_address }}">
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
                        @hasanyrole('vendor_admin|vendor_editor')

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <select id="consignee_country" name="consignee_country" class="form-control  @error('consignee_country') is-invalid @enderror" value="{{ $order->consignee_country ?? old('consignee_country') }}">
                                        <option selected="" disabled="" hidden="">Select Country</option>
                                        @foreach(Helper::getCountry() as $key=> $country)
                                            <option value="{{$country->id}}" {{$order->consignee_country == $country->id  ? 'selected' : ''}}>{{$country->name}}</option>
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
                                    <select id="consignee_state" name="consignee_state" class="form-control  @error('consignee_state') is-invalid @enderror" value="{{ $order->consignee_state }}">
                                        <option selected="" disabled="" hidden="">Select State</option>
                                        @foreach(Helper::getStates() as $key=> $state)
                                            <option value="{{$state->id}}" {{$order->consignee_state == $state->id  ? 'selected' : ''}}>{{$state->name}}</option>
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
                                    <select id="consignee_city" name="consignee_city" class="form-control  @error('consignee_city') is-invalid @enderror" value="{{ $order->consignee_city }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach(Helper::getCities() as $key=> $city)
                                            <option value="{{$city->id}}" {{ ($order->consignee_city == $city->id) ? 'selected' : ''  }}>{{$city->name}}</option>
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <span class="form-bar"></span>
                                    <input type="text" name="consignment_description" class="form-control  @error('consignment_description') is-invalid @enderror" value="{{ $order->consignment_description }}">
                                    @error('consignment_description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        @endhasanyrole

                        @hasanyrole('admin|first_man|middle_man')
                        <h4 class="sub-title divider">CONSIGNEMENT</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="consignment_order_id" class="form-control  @error('consignment_order_id') is-invalid @enderror" value="{{ ($order->consignment_order_id) ?? old('consignment_order_id') }}" disabled>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Order ID</label>
                                    @error('consignment_order_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            @hasrole('admin')
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select name="consignment_order_type" class="form-control  @error('consignment_order_type') is-invalid @enderror" value="{{ $order->consignment_order_type }}">
                                        <option selected="" disabled="" hidden="">Select Order Type</option>
                                        @foreach($order_type as $order_types)
                                        <option value="{{$order_types->id}}" {{ ($order_types->id == $order->consignment_order_type) ? 'selected' : '' }}>{{$order_types->name}}</option>
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
                                    <input type="text" name="consignment_cod_price" class="form-control  @error('consignment_cod_price') is-invalid @enderror" value="{{ ($order->consignment_cod_price) ?? old('consignment_cod_price') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">COD Price</label>
                                    @error('consignment_cod_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            @endrole
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
                                    <select id="vendor_weight_id" required name="vendor_weight_id" class="form-control  @error('vendor_weight_id') is-invalid @enderror" value="{{ $order->vendor_weight_id }}">
                                        <option selected="" disabled="" hidden="">Select Parcel Weight</option>
                                        @foreach($vendorWeights as $key=> $vendorWeight)
                                            <option value="{{$vendorWeight->id}}" {{ ($vendorWeight->id == $order->vendor_weight_id) ? 'selected' : '' }}>{{$vendorWeight->ahlWeight->weight . ' ' . 'Kg ' .' (' . $vendorWeight->city->first()->name . ')'}}</option>
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
                            <div class="col-md-4">
                                <div class="form-group form-static-label form-primary">
                                    <select name="consignment_packaging" class="form-control  @error('consignment_packaging') is-invalid @enderror" value="{{ $order->consignment_order_type }}">
                                        <option selected="" disabled="" hidden="">Select Packing</option>
                                        @foreach($packing as $packings)
                                        <option value="{{$packings->id}}" {{ ($order->consignment_order_type == $packings->id) ? 'selected' : '' }}>{{$packings->name}}</option>
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
                                    <input type="text" name="consignment_pieces" class="form-control  @error('consignment_pieces') is-invalid @enderror" value="{{ ($order->consignment_pieces) ?? old('consignment_pieces')}}" >
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
                                    <input type="text" name="consignment_description" class="form-control  @error('consignment_description') is-invalid @enderror" value="{{ $order->consignment_description }}">
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
                                        <option value="{{$pickup_locations->id}}" {{ ($pickup_locations->id == $order->pickup_location ) ? 'selected' : '' }}>{{$pickup_locations->address}}</option>
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
                                    <select name="consignment_origin_city" class="form-control  @error('consignment_origin_city') is-invalid @enderror" value="{{ ($order->consignment_origin_city) ?? old('consignment_origin_city') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach(Helper::getCities() as $key=> $city)
                                            <option value="{{$city->id}}" {{ ($order->consignment_origin_city == $city->id) ? 'selected' : '' }} disabled>{{$city->name}}</option>
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
                                    <input type="text" name="additional_services_type" class="form-control  @error('additional_services_type') is-invalid @enderror" value="{{ ($order->additional_services_type) ?? old('additional_services_type') }}">
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
                        @endhasanyrole
                        @hasanyrole('admin|hub_manager|csr')
                        <!-- limit -->
                        <h4 class="sub-title divider">Parcel Limit</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="parcel_limit" class="form-control" value="{{ $order->parcel_limit }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Parcel Limit</label>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Parcel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

