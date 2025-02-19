@extends('layouts.app')

@section('content')

<div class="card-block">
    <div class="page-body display" id="printableArea">
        <div class="card">
            <div class="card-header">
                <h5>Pickup Parcels Record List</h5>
                <form method="get">
                    <div class="row">
                        <div class="col-xl-5 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" name="date" id="date" class="form-control" required="required" value="<?php
                                if (isset($_GET['date'])) {
                                    echo $_GET['date'];
                                }
                                ?>">
                            </div>
                        </div>
                        <div class="col-xl-5 col-md-6">
                            <div class="form-group">
                                <label>Select Date</label>
                                <input type="date" name="to" id="to" class="form-control" required="required" value="<?php
                                if (isset($_GET['to'])) {
                                    echo $_GET['to'];
                                }
                                ?>">
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <div class="form-group">
                                <br>
                                <button type="submit" class="btn btn-primary mt-1">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-block table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <th>Picker Name</th>
                                <th>Picker Number</th>
                                <th>Supervisor Name</th>
                                <th>Supervisor Number</th>
                                <th>Joining Date</th>
                                <th>Joining Days</th>
                                <th>Working Days</th>
                                @hasanyrole('admin')
                                <th>Total COD</th>
                                @endhasanyrole
                                <th>Number of Parcels</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $vendor)
                            @if((count($vendor->scanOrder)) > 0)
                            <tr>
                                <td>{{$vendor->name}} - ({{$vendor->userDetail->cnic}})</td>
                                <td>{{$vendor->userDetail ? $vendor->userDetail->phone : ''}}</td>
                                <td>{{$vendor->pickerPerson ? $vendor->pickerPerson->name : ''}}</td>
                                <td>{{$vendor->pickerPerson ? $vendor->pickerPerson->userDetail->phone : ''}}</td>
                                @if(!empty($vendor->userDetail->joining_date))
                                <td>{{date('d-M-Y', strtotime($vendor->userDetail ? $vendor->userDetail->joining_date : ''))}}</td>
                                <td>{{\Carbon\Carbon::parse($vendor->userDetail->joining_date)->diffInDays(\Carbon\Carbon::now())}} Days</td>
                                @else
                                <td></td>
                                <td></td>
                                @endif
                                @if(!empty($_GET['date']))
                                <td>{{\App\Models\ScanOrder::where('picker_id', $vendor->id)->whereDate('created_at','>=', $_GET['date'])->whereDate('created_at','<=',$_GET['to'])->distinct()->count(DB::raw('DATE(created_at)'))}} Days</td>
                                @else
                                <td>1 Day</td>
                                @endif
                                <?php
                                    $total_sum = 0;

                                    foreach ($vendor->scanOrder as $key => $torder) {
                                        $total_sum = $torder->orderDetail->consignment_cod_price + $total_sum;
                                    }
                                ?>
                                @hasanyrole('admin')
                                <td>{{$total_sum}}</td>
                                @endhasanyrole
                                <td>{{count($vendor->scanOrder)}}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        <tfooter>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                @hasanyrole('admin')
                                <td>Rs. <span id="val"></span></td>
                                @endhasanyrole
                                <td><span id="valTwo"></span></td>
                            </tr>
                        </tfooter>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('custom-js')
<script>
            
    var table = document.getElementById("example"), sumVal = 0, sumValTwo = 0;
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumVal = sumVal + parseInt(table.rows[i].cells[7].innerHTML);
    }
            
    for(var i = 1; i < table.rows.length - 1; i++)
    {
        sumValTwo = sumValTwo + parseInt(table.rows[i].cells[8].innerHTML);
    }

    document.getElementById("val").innerHTML = sumVal;
    document.getElementById("valTwo").innerHTML = sumValTwo;
    // alert(sumVal);
            
</script>
@endsection