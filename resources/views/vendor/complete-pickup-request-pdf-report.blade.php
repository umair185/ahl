<html>
    <head>
        <style>
            th, td {
                border: 1px solid black;
            }
        </style>
    </head>
    <body>
        <div style="text-align: center">
            <h3>AHL Pickup Load Sheet</h3>
            <h4>-----------------------------------------------</h4>
        </div>
        <div style="text-align: left">
            <h3><span style="font-size: 13px">Customer Name:</span><span style="margin-left: 50px">{{$vendor_detail->vendor_name}}</span></h3>
            <h3><span style="font-size: 13px">Report Run Time:</span><span style="margin-left: 50px">{{date('d M Y h:i a', strtoTime($pickup_request->updated_at))}}</span></h3>
        </div>
        <div>
            <p style="font-weight: bold; font-size: 11px">Received Parcel Details are given below:</p>
        </div>
        <table class="table" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th style="font-size: 9px">Sr. #</th>
                    <th style="font-size: 9px">Order Reference</th>
                    <th style="font-size: 9px">Booking Date</th>
                    <th style="font-size: 9px">Customer Ref #</th>
                    <th style="font-size: 9px">Cust. Name</th>
                    <th style="font-size: 9px">Cust. Address</th>
                    <th style="font-size: 9px">Pickup Location</th>
                    <th style="font-size: 9px">Weight</th>
                    <th style="font-size: 9px">Pieces</th>
                    <th style="font-size: 9px">COD Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scanOrders as $parcel)
                <tr>
                    <th style="font-size: 8px">{{ $loop->iteration }}</th>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->order_reference }}</td>
                    <td style="font-size: 8px">{{ date('d M Y', strtoTime($parcel->created_at)) }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->consignment_order_id }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->consignee_first_name }} {{ $parcel->orderDetail->consignee_last_name }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->consignee_address }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->pickupLocation->address }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->vendorWeight->ahlWeight->weight }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->consignment_pieces }}</td>
                    <td style="font-size: 8px">{{ $parcel->orderDetail->consignment_cod_price }}</td>
                </tr>
                @endforeach
                <tr>
                    <th scope="row" colspan="9" style="text-align: right">Total</th>
                    <td>{{number_format($order_details)}}</td>
                </tr>
            </tbody>
        </table>
        <h3>Disclaimer</h3>
        <p><span style="font-weight: bold; font-size: 11px">{{$vendor_detail->vendor_name}}</span> is to ensure that the items being handed over to AHL pickup staff are pasted with right address labels.</p>
        <p><span style="font-weight: bold; font-size: 11px">{{$vendor_detail->vendor_name}}</span> will be responsible for the content packed inside the shipment.</p>
        <p><span style="font-weight: bold; font-size: 11px">{{$vendor_detail->vendor_name}}</span> will ensure the availability with the required COD amount for hassle free delivery.</p>
        <p style="font-size: 11px">Booked weight may vary with invoice/billing weight as our manifested weight will be treated as final weight.</p>
        <br>
        <table class="table" style="width: 80%;">
            <tbody>
                <tr>
                    <th>Customer</th>
                    <th colspan="2">AHL Pick-up Staff</th>
                </tr>
                <tr>
                    <td rowspan="2">Name: {{$vendor_detail->vendor_name}}</td>
                    <td>Name:</td>
                    <td>{{$pickup_name}}</td>
                </tr>
                <tr>
                    <td>Courier:</td>
                    <td>AHL</td>
                </tr>
                <tr>
                    <td rowspan="2">Sign & Stamp</td>
                    <td>Sign:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Date & Time:</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>