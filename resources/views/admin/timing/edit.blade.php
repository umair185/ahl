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
                    <h5>Update Time Slot</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveEditTiming') }}">
                        @csrf
                        <input type="hidden" value="{{$timing->id}}" name="timing_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="text" name="timing" class="form-control  @error('timing') is-invalid @enderror" value="{{$timing->timings}}">
                                    <span class="form-bar"></span>
                                    @error('timing')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Time Slot</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection