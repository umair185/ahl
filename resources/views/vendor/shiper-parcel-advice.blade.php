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
                    <h5>Parcel Advice Of | {{ $order->order_reference }}</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('vendorShiperParcelAdvice',$order->id) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="order_id" class="form-control  @error('order_id') is-invalid @enderror" value="{{ $order->id }}">
                                    
                                    <!--<input type="text" name="advise" class="form-control  @error('advise') is-invalid @enderror" value="{{ old('advise') }}">-->
                                    <select id="riders" name="advise" required="" class="form-control  @error('staff_id') is-invalid @enderror" value="{{ old('advise') }}">
                                        <option selected="" disabled="" hidden="">Select Reason</option>
                                        <option value="Return to Vendor">Return to Vendor</option>
                                        <option value="Cancel">Cancel</option>
                                        <option value="Customer didn't pick the call">Customer didn't pick the call</option>
                                        <option value="Please Re-attempt the Parcel">Re-attempt the Parcel</option>
                                    </select>
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

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Send Advise</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection