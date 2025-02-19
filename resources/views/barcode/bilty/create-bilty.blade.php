@extends('layouts.app')

@section('content')

<div class="page-body">  
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Create Bilty</h5>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">From City</span>
                                <select id="from_city" name="from_city" class="form-control  @error('from_city') is-invalid @enderror" value="{{ old('from_city') }}" required>
                                    <option selected="" disabled="" hidden="">Select From City</option>
                                    @foreach($userCities as $key=> $userCity)
                                        <option value="{{$userCity->id}}">{{$userCity->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">To City</span>
                                <select id="to_city" name="to_city" class="form-control  @error('to_city') is-invalid @enderror" value="{{ old('to_city') }}" required>
                                    <option selected="" disabled="" hidden="">Select To City</option>
                                    @foreach($getCities as $key=> $getCity)
                                        <option value="{{$getCity->id}}">{{$getCity->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">Manual Bilty Number</span>
                                <input type="text" name="manual_bilty" id="manual_bilty" class="form-control" placeholder="LHR-SKP-0001">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group form-static-label form-default">
                                <span class="form-bar">Bilty Number</span>
                                <input type="text" readonly name="bilty_number" id="bilty_number" class="form-control" placeholder="LHR-SKP-0001">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-static-label form-default">
                                <br>
                                <p class="btn btn-primary" id="getBilty" onclick="getBilty()">Generate Bilty</p>
                                <p class="btn btn-primary" id="closeBilty" style="display:none;" onclick="closeBilty()">Close Bilty</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Sags List</h5>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectall"/></th>
                            <th>#</th>
                            <th>Sag Number</th>
                            <th>Parcel Count</th>
                        </tr>
                    </thead>
                    <tbody id="parcel-data">
                        <!-- parcel data prepand here through ajax -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')<!-- 
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script> -->
<SCRIPT language="javascript">
var checkedArray = [];
$(function () {

    $("#selectall").click(function () {
        var checkedArray = [];
        $('.case').prop('checked', this.checked);
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        console.log(checkedArray);
    });

    $(".case").click(function () {
        if ($(".case").length == $(".case:checked").length) {
            $("#selectall").prop("checked", true);
        } else {
            $("#selectall").prop('checked', false);
        }

        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
        console.log(checkedArray);
    });
});

function getBilty() {

    var fromCity = $("#from_city").val();
    var toCity = $("#to_city").val();
    var manual_bilty = $("#manual_bilty").val();

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: '/get-bilty-number',
        type: 'POST',
        /* send the csrf-token and the input to the controller */
        data: {_token: CSRF_TOKEN, fromCity: fromCity, toCity: toCity, manual_bilty: manual_bilty},
        dataType: 'json',
        success: function(response){
            var result = response.parcel;
            var sags = response.sags;

            if(response.status == 'fromCity'){
                alert(response.message);
            }

            if(response.status == 'toCity'){
                alert(response.message);
            }

            if(response.status == 'ManualBilty'){
                alert(response.message);
            }

            if(response.status == 'Duplicate'){
                alert(response.message);
            }

            if(response.status == 'Success'){
                $("#bilty_number").val(result);
                $("#parcel-data").html(sags);
                var paragraph = document.getElementById("getBilty");
                // Hide the paragraph by changing its display property
                paragraph.style.display = "none";
                var close_paragraph = document.getElementById("closeBilty");
                // Hide the paragraph by changing its display property
                close_paragraph.style.display = "block";

                var message = 'Bilty '+ result +' has been created successfully! Start Selecting Sags.';
                alert(message);
            }
        }
    });
}

function closeBilty()
{
    var checkedArray = [];
    $("input:checkbox[name=case]:checked").each(function () {
        checkedArray.push($(this).val());
    });

    if (checkedArray.length == 0) {
        alert('please select some Sags to close the Bilty');
    }
    else
    {
        var bilty_number = $("#bilty_number").val();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '/close-bilty-number',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {_token: CSRF_TOKEN, bilty_number: bilty_number, sags: checkedArray},
            dataType: 'json',
            success: function(response){
                if(response.status == 'Invalid'){
                    alert(response.message);
                }

                if(response.status == 'Success'){
                    alert(response.message);
                    window.location.href = "/generate-bilty-pdf/" + bilty_number;
                    setTimeout(function(){ location.reload() }, 5000);
                }
            }
        });
    }
}
</SCRIPT>
@endsection