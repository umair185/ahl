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
                    <h5>Create Flyer Request</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveFlyerRequest') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <center> Select </center>
                            </div>
                            <div class="col-md-3">
                                <center> Size </center>
                            </div>
                            <div class="col-md-3">
                                <center> Price </center>
                            </div>
                            <div class="col-md-4">
                                <center> Quantity </center>
                            </div>
                        </div><hr>
                        @csrf
                        @foreach($flyers as $key => $flyer)
                        <div class="row">
                            <div class="col-md-2">
                                <center> <input type="checkbox" name="flyer_id[{{$key}}]" class="" value="{{ $flyer->id }}"> </center>
                            </div>
                            <div class="col-md-3">
                                <input readonly type="text" name="title[]" class="form-control" value="{{ $flyer->name }}">
                            </div>
                            <div class="col-md-3">
                                <input readonly type="text" name="title[]" class="form-control" value="PKR {{ $flyer->price }}">
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="quantity[]" max="{{ $flyer->current_stock }}" min="0" value="0" class="form-control" placeholder="Enter Quantity">
                                <span style="color: red; font-size: 10px; font-weight: bold;">You can place an order of max. {{$flyer->current_stock}} flyers of this size</span>
                            </div>
                        </div><br>
                        @endforeach
                        <hr>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Send Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection