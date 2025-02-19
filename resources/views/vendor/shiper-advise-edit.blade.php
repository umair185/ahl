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
                    <h5>Parcel Advice Of | {{ $shiperAdviser->order->order_reference }}</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('vendorShiperParcelAdviceEdit',$shiperAdviser->id) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="shiper_advise_id" class="form-control  @error('shiper_advise_id') is-invalid @enderror" value="{{ $shiperAdviser->id }}">
                                    
                                    <input type="text" name="advise" class="form-control  @error('advise') is-invalid @enderror" value="{{ $shiperAdviser->advise }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Parcel Advice</label>
                                    @error('advise')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Advise</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection