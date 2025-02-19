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
                    <h5 class="text-center" style="text-align: center;">{{ $date }} </h5>
                    <h4 class="text-center" style="text-align: center;">
                        @php
                            $reference = $sag;
                            echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($reference, 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';
                        @endphp
                    </h4>
                    <h4 class="text-center" style="text-align: center;">Sag Number: {{ $sag }} </h4>
                    <h4 class="text-center" style="text-align: center;">Seal Number: {{ $seal_number }} </h4>
                    <h4 class="text-center" style="text-align: center;">Total Parcels: {{ $count_orders }} </h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <table class="table" id="general_ledger" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order Parcel Number</th>
                                <th>Consignee Name</th>
                                <th>Order Type</th>
                                <th>Vendor Name</th>
                                <th>Consignee Address</th>
                                <th>Consignee Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcelPdfData as $key => $order)
                            <tr>
                                <td style="text-align:center; font-size: 9px">{{ ++$key }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->order_reference}}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignee_first_name}} {{ $order->consignee_last_name}}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->orderType->name }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->vendor->vendor_name }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignee_address }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignee_phone}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>