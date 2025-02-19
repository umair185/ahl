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
                    <h5>Upload Vendor Financial Payment Proof</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('uploadVendorFinancialPayment') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="financial_id" value="{{ $vendorFinancial->id }}">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="file" name="financial_payment" class="form-control  @error('financial_payment') is-invalid @enderror" value="{{ old('financial_payment') }}" accept="image/*">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Upload Image</label>
                                    @error('financial_payment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Upload Proof</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection