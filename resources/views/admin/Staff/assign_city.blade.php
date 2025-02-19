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
                    <h5>Assign City</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{route('saveusercity')}}">
                        @csrf

                        <input type="hidden" name="userid" value ="{{$user->id}}">
                        <div class="row">
                            <div class="col-md-2">
                                <center> Select </center>
                            </div>
                            <div class="col-md-3">
                                <center> City </center>
                            </div>
                        </div><hr>
                        
                        @foreach($cities as $key => $city)
                        <div class="row">
                            <div class="col-md-2">
                                <center> <input type="checkbox" name="city_id[{{$key}}]"  value="{{ $city->id }}" 
                                {{ $usercity->contains($city->id) ? 'checked' : '' }} > </center>
                            </div>
                            <div class="col-md-3">
                                <input readonly type="text" name="city_name[]" class="form-control" value="{{ $city->name }}" >
                            </div>
                        </div><br>
                        @endforeach
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save City</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection