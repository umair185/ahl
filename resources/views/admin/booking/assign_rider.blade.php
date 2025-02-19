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
                    <h5>Assign Parcel to Rider</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveAssignRider') }}">
                        @csrf
                        <h4 class="sub-title divider heading">Assign Rider</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="order_id" class="form-control  @error('order_id') is-invalid @enderror" value="{{ $order->id }}">
                                    <input type="hidden" name="vendor_id" class="form-control  @error('vendor_id') is-invalid @enderror" value="{{ $vendorId }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" id="address" name="drop_off_location" class="form-control  @error('drop_off_location') is-invalid @enderror" value="{{ old('drop_off_location') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label"></label>
                                    @error('drop_off_location')
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
                                    <input type="text" readonly id="latitude" placeholder="latitude" name="latitude" class="form-control  @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label"></label>
                                    @error('latitude')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" readonly id="longitude" placeholder="longitude" name="longitude" class="form-control  @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label"></label>
                                    @error('longitude')
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
                                    <select name="rider_id" class="form-control  @error('rider_id') is-invalid @enderror" required="" value="{{ old('rider_id') }}">
                                        <option selected="" disabled="" hidden="">Select Rider</option>
                                        @foreach($riderId as $rider)
                                        <option value="{{$rider->id}}">{{$rider->id}} + {{$rider->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('rider_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Assign Rider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBl-jpsktXKHLD7rFQo9NT3Hfgm16b27C0&libraries=places&callback=initialize" async defer></script>
<script>
    $(document).ready(function() {
        $.noConflict();
        initialize();
    });

    function initialize() {

        var options = {
            componentRestrictions: {
                country: "pk"
            }
        };

        var input = document.getElementById('address');
        var autocomplete = new google.maps.places.Autocomplete(input, options);

        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();

            $('#latitude').val(place.geometry['location'].lat());
            $('#longitude').val(place.geometry['location'].lng());
        });
    }
    
</script>
@endsection