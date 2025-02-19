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
                    <h5>Update Packing</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveEditPacking') }}">
                        @csrf
                        <h4 class="sub-title divider heading">PACKING DETAIL</h4>
                        <input type="hidden" value="{{$packing->id}}" name="packing_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="name" class="form-control  @error('name') is-invalid @enderror" value="{{$packing->name}}">
                                    <span class="form-bar"></span>
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Packing</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection