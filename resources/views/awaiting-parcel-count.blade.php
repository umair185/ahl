@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Awaiting Pickup Parcels Record List</h5>
            </div>
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendor Name</th>
                                <th>Overall Parcels</th>
                                <th>Today Parcels</th>
                                <th>Assigned Sales Person</th>
                                <th>Assigned Sales Person Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $key => $vendor)
                            @if((count($vendor->awaitingParcel)) > 0)
                            <tr>
                                <td></td>
                                <td>{{$vendor->vendor_name}}</td>
                                <td>{{count($vendor->awaitingParcel)}}</td>
                                <td>{{count($vendor->awaitingTodayParcel)}}</td>
                                <td>{{$vendor->pocPerson ? $vendor->pocPerson->name : ''}}</td>
                                <td>{{$vendor->pocPerson ? $vendor->pocPerson->userDetail->phone : ''}}</td>
                            </tr>
                            @endif
                            @endforeach
                            <tr>
                                <th></th>
                                <th style="font-weight: bold;font-size: 18px;">Total</th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="val"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"><span id="valTwo"></span></th>
                                <th style="font-weight: bold;font-size: 18px;"></th>
                                <th style="font-weight: bold;font-size: 18px;"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
            
    var table = document.getElementById("example"), sumVal = 0, sumValTwo = 0;
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumVal = sumVal + parseInt(table.rows[i].cells[2].innerHTML);
    }
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValTwo = sumValTwo + parseInt(table.rows[i].cells[3].innerHTML);
    }
    document.getElementById("val").innerHTML = sumVal;
    document.getElementById("valTwo").innerHTML = sumValTwo;
    // alert(sumVal);
            
</script>
<script>
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.children[0].textContent = index + 1;
    });
</script>
@endsection