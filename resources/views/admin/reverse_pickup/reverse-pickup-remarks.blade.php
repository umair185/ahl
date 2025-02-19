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
                    <h5>Parcel Remarks Of | {{ $orders->order_reference }}</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveReversePickupRemarks',$orders->id) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    
                                    <input type="text" name="remarks" class="form-control  @error('remarks') is-invalid @enderror" value="{{ $orders->remarks }}">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Parcel Remarks</label>
                                    @error('remarks')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Remarks</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection