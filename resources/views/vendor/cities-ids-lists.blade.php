@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>City List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>City Name</th>
                            <th>City Code</th>
                            <th>Sub Area</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(Helper::getCities() as $key=> $city)
                            <tr>
                                <td>{{++$key}}</td>
                                <td>{{$city->name}}</td>
                                <td>{{$city->code}}</td>
                                <td><a href="{{route('cityarea',$city->id)}}" class="btn btn-primary">Areas</a></td>
                            </tr>
                        @endforeach    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')

<!-- <SCRIPT language="javascript">
    var i = 0; 
$(function () {

    $("#city_filter").click(function () {
        var cityId = $("#city").val();
        console.log(cityId);
        if (cityId == null) {
            alert('please select city');
        } else {
            //var newWin = window.open();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                /* the route pointing to the post function */
                url: '/cities-ids-for-bulk-order',
                type: 'get',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, city: cityId},
                dataType: 'json',
                /* remind that 'data' is the response of the AjaxController */
                success: function (response) {
                    if(response.status == 1){
                        ++i;
                        var result = response.city;
                        $("table tbody").append(
                            "<tr>"
                               + "<th scope='row'>"+ i +"</th>"
                               + "<td>" + result.city_id +"</td>"
                               + "<td>" + result.city_name + "</td>"
                               + "<td>" + result.state_id + "</td>"
                               + "<td>" + result.state_name + "</td>"
                               + "<td>" + result.country_id +"</td>"
                               + "<td>" + result.country_name +"</td>"
                            + "</tr>"
                        );
                    }
                }
            });
        }
    });
    
});
</SCRIPT> -->
@endsection