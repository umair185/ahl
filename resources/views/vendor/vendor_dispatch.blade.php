@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <form method="get" action="get-vendor-dispatch-parcel">
                <div class="row">
                    <div class="col-3">
                        <label for="">From Date</label>
                        <input type="date" name="from" class="form-control" value="{{$from}}">
                    </div>
                    <div class="col-3">
                        <label for="">To Date</label>
                        <input type="date" name="to" class="form-control" value="{{$to}}">
                    </div>
                    <div class="col-3">
                        <label for="">Vendors</label>
                        <select name="vendor_id" class="form-control">
                            <option value="any">Any</option>
                            @foreach($vendors as $vendor)
                            <option value="{{$vendor->id}}" @if($vendor->id==$vendor_id) selected @endif name="vendor_id">{{$vendor->vendor_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                            <label for="">Statuses</label>
                        <select name="status" class="form-control">
                            <option value="any">Any</option>
                            @foreach($statuses as $status)
                            <option value="{{$status->id}}">{{$status->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary"> Submit</button>
                    </div>
                </div>        
            </form>
        </div>
    </div>
</div>
@endsection