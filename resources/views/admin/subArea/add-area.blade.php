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
                    <h5>Create Area</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveArea') }}">
                        @csrf
                        <h4 class="sub-title divider heading">AREA DETAILS</h4>
                        

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="area_name" class="form-control  @error('area_name') is-invalid @enderror" value="{{ old('area_name') }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Area Name</label>
                                    @error('area_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <select id="area_city" name="area_city" class="form-control  @error('area_city') is-invalid @enderror" value="{{ old('area_city') }}">
                                        <option>Select City</option>
                                        @foreach($cities as $city)
                                            <option value="{{$city->id}}">{{$city->name}}</option>
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

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Area</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection