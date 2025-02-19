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
                    <h5>Complain</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{route('saveVendorComplain')}}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    
                                    <input type="text" name="complain" class="form-control  @error('complain') is-invalid @enderror" value="{{ old('complain') }}" required>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Enter your Complain</label>
                                    @error('advise')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Send Complain to AHL</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection