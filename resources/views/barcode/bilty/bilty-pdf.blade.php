<!DOCTYPE html>
<html>
    <head>
        <title>{{ $title }} - {{ config('app.name', 'Laravel') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <style type="text/css">
            table, th, td{
                border: 1px solid;
                border-collapse: collapse;
            }
        </style>
    </head>
    <body>
        <div class="fluid-container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <h3 class="text-center" style="text-align: center;">{{ $title }}</h3>
                    <h4 class="text-center" style="text-align: center;">
                        @php
                            $reference = $bilty;
                            echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($reference, 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';
                        @endphp
                    </h4>
                    <h4 class="text-center" style="text-align: center;">Bilty Number: {{ $bilty }} </h4>
                    <h4 class="text-center" style="text-align: center;">Manual Bilty Number: {{ $manual_number }} </h4>
                    <h4 class="text-center" style="text-align: center;">Total Sags: {{ $count_sags }} </h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <table class="table" id="general_ledger" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sag Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcelPdfData as $key => $data)
                            <tr>
                                <td style="text-align:center; font-size: 9px">{{ ++$key }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $data->sag_number}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>