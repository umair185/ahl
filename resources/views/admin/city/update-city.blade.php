@extends('layouts.app')

@section('custom-css')
<style type="text/css">
    .divider {
        margin-top: 2%; / or whatever /
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
                    <h5>Update City</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('updateCity') }}">
                        @csrf
                        <h4 class="sub-title divider heading">CITY DETAILS</h4>

                        <input type="hidden" name="city_id" value="{{$city->id}}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="city_name" class="form-control  @error('city_name') is-invalid @enderror" value="{{$city->name }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">City Name</label>
                                    @error('city_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="city_code" class="form-control  @error('city_code') is-invalid @enderror" value="{{ $city->code }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">city Code</label>
                                    @error('city_code')
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
                                    <select id="city_state" name="city_state" class="form-control  @error('city_state') is-invalid @enderror">
                                        <option>Select City State</option>
                                        @foreach($states as $state)
                                            <option {{$city->state_id == $state->id ? 'selected' : ''}} value="{{$state->id}}" >{{$state->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('city_state')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save City</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection