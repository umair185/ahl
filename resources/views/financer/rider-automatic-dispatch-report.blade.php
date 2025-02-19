@extends('layouts.app')

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
                    <h5>Vendor's Automatic Dispatch Report</h5>
                </div>
                <div class="card-block">
                    <form action="{{route('riderAutomaticDispatchReportDownload')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="">From Date</label>
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="from" class="form-control" required="required">
                                </div>
                            </div>
                            <div class="col-md-4">
                            <label for="">To Date</label>
                                <div class="form-group form-static-label form-default">
                                    <input type="date" name="to" class="form-control" required="required">
                                </div>
                            </div>
                            <div class="col-md-4">
                            <label for="">Vendor</label>
                                <div class="form-group form-static-label form-default">
                                    <select name="vendor" class="form-control" required="required">
                                        <option value="any">Any</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->vendor_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light" id="assign_parcel_to_rider">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection