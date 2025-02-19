@extends('layouts.app')

@section('content')

<div class="page-body display" id="printableArea">
    <div class="card">
        <div class="card-header">
            <h5>Middle Men Today Report</h5>
            <div class="text-center">
                <a href="{{ route('TodayReportDownload') }}" class="btn btn-info mb-2">Download CN</a>
            </div>
            <div class="text-right" style="margin-top: -50px">
                <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Download Count</button>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive" >
                <table class="table table-hover" id="riders_table">
                    <thead>
                        <tr>
                            <th>Sr. #</th>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>At Ahl Warehouse</td>
                            <td>{{$atAhl}}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Re-Attempt</td>
                            <td>{{$reAttempt}}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Cancelled</td>
                            <td>{{$cancelled}}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="font-weight: bold; font-size: 18px;">Total</td>
                            <td style="font-weight: bold; font-size: 18px;">{{$total}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <br>
    </div>
</div>
<script type="text/javascript">
    function fnExcelReport()
    {
        var tab_text = "<table border='2px'><tr bgcolor='#87AFC6'>";
        var textRange;
        var j = 0;
        tab = document.getElementById('riders_table'); // id of table

        for (j = 0; j < tab.rows.length; j++)
        {
            tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
            //tab_text=tab_text+"</tr>";
        }

        tab_text = tab_text + "</table>";
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
        tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
        tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
        {
            txtArea1.document.open("txt/html", "replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa = txtArea1.document.execCommand("SaveAs", true, "Say Thanks to Sumit.xls");
        }
        else                 //other browser not tested on IE 11
            sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));

        return (sa);
    }
</script>
@endsection