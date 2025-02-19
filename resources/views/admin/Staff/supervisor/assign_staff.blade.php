@extends('layouts.app')

@section('custom-css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
@endsection
@section('content')

@if(session('flash'))
    <div class="alert alert-{{ session('flash_alert') }}">
        {{ session('flash_message') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            <!-- foreach ($errors->all() as $error) -->
                <li>Please Select Riders</li>
            <!-- endforeach -->
        </ul>
    </div>
@endif

<form method="POST" class="form-material" action="{{ route('saveAssignSupervisor') }}">
    @csrf
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Assign Riders To {{ $user_data->name }}</h5>
            </div>
            <input type="hidden" name="staff_id" value="{{$userId}}">
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectall"/></th>
                                <th>#</th>
                                <th>Name</th>
                                <th>Thumb ID</th>
                                <th>Phone Number</th>
                                <th>Joining Date</th>
                                <th>Joining Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $counter = 0; @endphp
                            @foreach($riders as $rider)
                            <tr>
                                <td align="center"><input type="checkbox" class="case" name="rider_id[]" value="{{$rider->id}}" @if(in_array($rider->id, $assignedRiderIds)) checked @endif/></td>
                                <th scope="row">{{ ++$counter}}</th>
                                <td>{{$rider->name}}</td>
                                <td>{{$rider->user_id}}</td>
                                <td>{{$rider->userDetail ? $rider->userDetail->phone : ''}}</td>
                                @if(!empty($rider->userDetail->joining_date))
                                <td>{{date('d-M-Y', strtotime($rider->userDetail ? $rider->userDetail->joining_date : ''))}}</td>
                                <td>{{\Carbon\Carbon::parse($rider->userDetail->joining_date)->diffInDays(\Carbon\Carbon::now())}} Days</td>
                                @else
                                <td></td>
                                <td></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
        </div>
    </div>
</form>
@endsection

@section('custom-js')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<SCRIPT language="javascript">
var checkedArray = [];
$(function () {
    /* Data Table */
    var table = $('#example').DataTable({
      "lengthMenu": [ 75, 100, 500, 1000, 5000 ]
    });

    // add multiple select / deselect functionality

    $("#selectall").click(function () {
        var checkedArray = [];
        //$('.case').attr('checked', this.checked);
        $('.case').prop('checked', this.checked);
        /*$("input:checkbox[name=case]:checked").map(function(){
         checkedArray.push($(this).val());
         })*/
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });

        console.log(checkedArray);
    });



    // if all checkbox are selected, check the selectall checkbox
    // and viceversa
    $(".case").click(function () {
        if ($(".case").length == $(".case:checked").length) {
            //$("#selectall").attr("checked", "checked");
            $("#selectall").prop("checked", true);
        } else {
            //$("#selectall").removeAttr("checked");
            $("#selectall").prop('checked', false);
        }

        var checkedArray = [];
        $("input:checkbox[name=case]:checked").each(function () {
            checkedArray.push($(this).val());
        });
        console.log(checkedArray);

    });

});
</SCRIPT>
@endsection