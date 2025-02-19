@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <form method="get" action="pra-report">
                <div class="row">
                    <div class="col-5">
                        <label for="">From Date</label>
                        <input type="date" name="from" class="form-control" value="{{$from}}">
                    </div>
                    <div class="col-5">
                        <label for="">To Date</label>
                        <input type="date" name="to" class="form-control" value="{{$to}}">
                    </div>
                    <div class="col-2">
                        <br>
                        <button type="submit" class="btn btn-primary"> Submit</button>
                    </div>
                </div>        
            </form>
        </div>
    </div>
</div>
@endsection