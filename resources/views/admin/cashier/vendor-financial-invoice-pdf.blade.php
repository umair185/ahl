<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Payment Invoice Detail</title>
        <style type="text/css">
            .clearfix:after {
                content: "";
                display: table;
                clear: both;
            }

            a {
                color: #5D6975;
                text-decoration: underline;
            }

            body {
                position: relative;
                width: 21cm;  
                height: 29.7cm; 
                margin: 0 auto; 
                color: #001028;
                background: #FFFFFF; 
                font-family: Arial, sans-serif; 
                font-size: 12px; 
                font-family: Arial;
            }

            header {
                padding: 10px 0;
                margin-bottom: 30px;
            }

            #logo {
                text-align: center;
                margin-bottom: 10px;
            }

            #logo img {
                width: 90px;
            }

            h1 {
                border-top: 1px solid  #5D6975;
                border-bottom: 1px solid  #5D6975;
                color: #5D6975;
                font-size: 2.4em;
                line-height: 1.4em;
                font-weight: normal;
                text-align: center;
                margin: 0 0 20px 0;
                background: url(dimension.png);
            }

            #project {
                float: left;
            }

            #project span {
                color: #5D6975;
                text-align: right;
                width: 52px;
                margin-right: 10px;
                display: inline-block;
                font-size: 0.8em;
            }

            #company {
                float: right;
                text-align: right;
            }

            #project div,
            #company div {
                white-space: nowrap;        
            }

            table {
                width: 100%;
                border-collapse: collapse;
                border-spacing: 0;
                margin-bottom: 20px;
            }

            table tr:nth-child(2n-1) td {
                background: #F5F5F5;
            }

            table th,
            table td {
                text-align: center;
            }

            table th {
                padding: 5px 20px;
                color: #5D6975;
                border-bottom: 1px solid #C1CED9;
                white-space: nowrap;        
                font-weight: normal;
            }

            table .service,
            table .desc {
                text-align: left;
            }

            table td {
                padding: 20px;
                text-align: right;
            }

            table td.service,
            table td.desc {
                vertical-align: top;
            }

            table td.unit,
            table td.qty,
            table td.total {
                font-size: 1.2em;
            }

            table td.grand {
                border-top: 1px solid #5D6975;;
            }

            #notices .notice {
                color: #5D6975;
                font-size: 1.2em;
            }

            footer {
                color: #5D6975;
                width: 100%;
                height: 30px;
                position: absolute;
                bottom: 0;
                border-top: 1px solid #C1CED9;
                padding: 8px 0;
                text-align: center;
            }


        </style>
        <?php 
            $vendor_gst = $financial_id->VendorName->gst;
            $sales_tax = $financial_id->ahl_gst;
            $deduction_amount = $financial_id->deduction_amount;
            $initial_amount = $financial_id->ahl_commission + $sales_tax + $financial_id->amount + $financial_id->flyer_amount + $financial_id->fuel_adjustment;
            $sub_total = $financial_id->amount + $financial_id->advance_amount;

            $deduction_remarks = '';
            if($financial_id->deduction_remarks == 'normal')
            {
                $deduction_remarks = 'Normal Amount Deduction';
            }
            else
            {
                $deduction_remarks = 'Advance Amount Deduction';
            }
        ?>
    </head>
    <body>
        <div>
            <header class="row" style="width: 90%;" class="clearfix">
                <div id="logo">
                        <!-- <img width="100px" height="50px" src="logo/ahl_logo.png"> -->
                    <img width="100px" height="50px" src="logo/ahl_logo_pdf.png">
                </div>
                <h1>INVOICE ( {{ date('d M Y', strtoTime($financial_id->created_at)) }} )</h1>
                <div style="float: right;" class="clearfix">
                    <div>AH Logistic</div>
                    <div>1-House no 279, N block,<br> Johar Town Phase 2, Lahore</div>
                    <div>+92 310 3338511</div>
                    <div><a href="mailto:info@ahl.pk">info@ahlogistic.pk</a></div>
                    <br>
                    @if($vendor_gst > 0)
                    <div><span>NTN No: </span> 6194423-2 </div>
                    @endif
                </div>
                <div style="float: left;">
                    <div><span>Invoice #: </span> {{ $financial_id->invoice_number }}</div>
                    <div><span>Bill To: </span> {{ $financial_id->vendorName->vendor_name }}</div>
                    <!-- <div><span>CLIENT</span> John Doe</div> -->
                    <div><span>ADDRESS: </span> {{ $financial_id->vendorName->vendor_address }}</div>
                    <div><span>EMAIL: </span> <a href="mailto:{{ $financial_id->vendorName->vendor_email }}">{{ $financial_id->vendorName->vendor_email }}</a></div>
                    <br>
                    <div><span>VAT No: </span> {{ ($financial_id->vendorName->ntn) ? $financial_id->vendorName->ntn : 'N/A' }}</div>
                    <div><span>Payment Period: </span> {{ date('d M Y', strtoTime($financial_id->date_from)) }} - {{ date('d M Y', strtoTime($financial_id->date_to)) }}</div>
                    <div><span>Term: </span> Due On Receipt</div>
                </div>
            </header>

            <table style="margin-top: 160px;width: 90%;">
                <thead>
                    <tr>
                        <th class="service">DATE</th>
                        <th class="desc">ACTIVITY</th>
                        <th>DESCRIPTION</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($financial_id->created_at)) }}</td>
                        <td class="desc">Payment Given to Vendor</td>
                        <td class="unit">Payment against order delivery</td>
                        <td class="total">{{ $initial_amount }}</td>
                    </tr>
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($financial_id->created_at)) }}</td>
                        <td class="desc">Commission Charged</td>
                        <td class="unit">Commission against order delivery</td>
                        <td class="total">{{ $financial_id->ahl_commission }}</td>
                    </tr>
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($financial_id->created_at)) }}</td>
                        <td class="desc">Fuel Adjustment Charged</td>
                        <td class="unit">Fuel Adjustment against<br> Commission Charged</td>
                        <td class="total">{{ $financial_id->fuel_adjustment }}</td>
                    </tr>
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($financial_id->created_at)) }}</td>
                        <td class="desc">Flyers Amount Charged</td>
                        <td class="unit">Amount against Flyers Delivered</td>
                        <td class="total">{{ $financial_id->flyer_amount }}</td>
                    </tr>
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($financial_id->created_at)) }}</td>
                        <td class="desc">Advance Payment Added</td>
                        <td class="unit">Amount against Advance<br> Payment to {{ $financial_id->vendorName->vendor_name }}</td>
                        <td class="total">{{ $financial_id->advance_amount }}</td>
                    </tr>
                    @if($sales_tax > 0)
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($financial_id->created_at)) }}</td>
                        <td class="desc">Sales Tax</td>
                        <td class="unit">Sales Tax Over Commission</td>
                        <td class="total">{{ $sales_tax }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="3">SUBTOTAL</td>
                        <td class="total">{{ $sub_total }}</td>
                    </tr>
                    <tr>
                        <td colspan="3">DEDUCTION AGAINST INVOICE<br>({{$deduction_remarks}})</td>
                        <td class="total">{{ $financial_id->deduction_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="grand total" style="font-size: large;">FINAL BALANCE</td>
                        <td class="grand total" style="font-size: large;"><span style="font-size: small;">PKR</span> {{ $sub_total - $deduction_amount }}</td>
                    </tr>
                </tbody>
            </table>

            <img style="margin-top: -10px;margin-left: 250px;" width="200px" height="150px" src="logo/paid.png">

        </div>
    </body>
</html>