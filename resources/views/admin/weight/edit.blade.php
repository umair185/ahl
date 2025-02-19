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
                    <h5>Edit Weight</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('ahlWeightUpdate') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="weight_id" value="{{ $ahlWeight->id }}">

                                    <input type="text" name="weight" class="form-control  @error('weight') is-invalid @enderror" value="{{ $ahlWeight->weight }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Weight</label>
                                    @error('weight')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection