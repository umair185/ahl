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
                    <h5>Edit Flyer</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('updateFlyer') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="flyer_id" class="form-control  @error('flyer_name') is-invalid @enderror" value="{{ $find_flyer->id }}">
                                    <input type="text" name="flyer_name" class="form-control  @error('flyer_name') is-invalid @enderror" value="{{ $find_flyer->name }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Name</label>
                                    @error('flyer_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-static-label form-default">
                                    <input type="number" name="flyer_price" class="form-control  @error('flyer_price') is-invalid @enderror" value="{{ $find_flyer->price }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Price</label>
                                    @error('flyer_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>  
                        </div>                        

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Flyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection