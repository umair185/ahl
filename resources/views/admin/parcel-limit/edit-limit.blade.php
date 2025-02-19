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
                    <h5>Edit Parcel Limit</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{route('updateParcelLimit')}}">
                        @csrf
                        <input type="hidden" name="limit_id" value="{{$find_limit->id}}">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select id="city_id" name="city_id" disabled class="form-control" required>
                                        <option selected="" disabled="" hidden="">Select City</option>
                                        @foreach ($cities as $key => $city)
                                            <option value="{{ $city->id }}" {{$city->id == $find_limit->city_id ? 'selected' : ''}}>{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    
                                    <input type="number" name="limit" class="form-control" value="{{$find_limit->limit}}" min="1" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Enter your Limit</label>
                                    @error('limit')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Parcel Limit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection