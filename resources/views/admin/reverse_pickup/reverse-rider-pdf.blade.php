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
                    <h2 class="text-center" style="text-align: center;">{{ $title }} - {{ config('app.name') }}</h2>
                </div>
                <div class="col-md-12 col-lg-12">
                    <h2 class="text-center" style="text-align: center;">{{ $date }} </h2>
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
                                <th>Weight</th>
                                <th>Amount</th>
                                <th>Order Type</th>
                                <th>Vendor Name</th>
                                <th>Vendor Order Id</th>
                                <th>Consignee Address</th>
                                <th>Pickup Date</th>
                                <th>Rider Name</th>
                                <th>Current Status</th>
                                <th>Consignee Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcelPdfData as $key => $order)
                            <tr>
                                <td style="text-align:center; font-size: 9px">{{ $key++ }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->order_reference}}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignee_first_name}} {{ $order->consignee_last_name}}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->vendorWeight->ahlWeight->weight }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignment_cod_price}}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->orderType->name }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->vendor->vendor_name }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignment_order_id }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->consignee_address }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->scanOrder->created_at }}</td>
                                <td style="text-align:center; font-size: 9px">{{ $riderName}}</td>
                                <td style="text-align:center; font-size: 9px">{{ $order->orderStatus->name }}</td>
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