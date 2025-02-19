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
                    <h5>Assign Cities To Rider</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('assignCityToRider') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="rider_id" class="form-control  @error('rider_id') is-invalid @enderror" required="" value="{{ old('rider_id') }}">
                                        <option selected="" disabled="" hidden="">Select Rider</option>
                                        @foreach($riders as $rider)
                                        <option value="{{$rider->id}}">{{$rider->name}} ( {{ $rider->userDetail->cnic }} )</option>
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
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select name="city_id" class="form-control  @error('city_id') is-invalid @enderror" required="" value="{{ old('city_id') }}">
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach($cities as $city)
                                        <option value="{{$city->id}}">{{$city->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('city_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Assign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection