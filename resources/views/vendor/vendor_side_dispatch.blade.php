@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <form method="get" action="get-vendor-side-dispatch-parcel">
                <div class="row">
                    <div class="col-3">
                        <input type="date" name="from" class="form-control" value="{{$from}}">
                    </div>
                    <div class="col-3">
                        <input type="date" name="to" class="form-control" value="{{$to}}">
                    </div>
                    <div class="col-3">
                        <select name="status" class="form-control">
                            <option value="any">Any</option>
                            @foreach($statuses as $status)
                            <option value="{{$status->id}}">{{$status->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-primary"> Submit</button>
                    </div>
                </div>        
            </form>
        </div>
    </div>
</div>
@endsection