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
                    <h5>Send Request to Picker</h5>
                </div>
                <div class="card-block">
                    <form method="POST" class="form-material" action="{{ route('saveAssignPickerRequest') }}">
                        @csrf
                        <h4 class="sub-title divider heading">Assign Request</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <input type="hidden" name="pickup_request_id" class="form-control  @error('pickup_request_id') is-invalid @enderror" value="{{ $pickupId }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-static-label form-default">
                                    <select name="rider_id" class="form-control  @error('rider_id') is-invalid @enderror" required="" value="{{ old('rider_id') }}">
                                        <option selected="" disabled="" hidden="">Select Picker</option>
                                        @foreach($riderId as $rider)
                                        <option value="{{$rider->id}}">{{$rider->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-bar"></span>
                                    @error('rider_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary waves-effect waves-light">Assign Picker</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection