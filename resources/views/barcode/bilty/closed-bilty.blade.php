@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Closed Sags List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bilty Number</th>
                            <th>Manual Bilty Number</th>
                            <th>Create By</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Open By</th>
                            <th>Open In</th>
                            <th>No. of Sags</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bilties as $key => $bilty)
                        <tr>
                            <th>{{ ++$key }}</th>
                            <td>{{ $bilty->bilty_number }}</td>
                            <td>{{ $bilty->manual_bilty_number }}</td>
                            <td>{{ $bilty->createdBy->name }}</td>
                            <td>{{ $bilty->From->name }}</td>
                            <td>{{ $bilty->To->name }}</td>
                            <td>{{ $bilty->openBy->name }}</td>
                            <td>{{ $bilty->toCity->name }}</td>
                            <td>{{ count($bilty->sags) }}</td>
                            <td><a href="{{route('biltySagList', $bilty->id)}}"><button class="btn waves-effect waves-light btn-primary"><i class="fa fa-arrow-circle-o-right"></i>View Sags</button></a></td>
                            
                        </tr>
                       @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection