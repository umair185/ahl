<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Vendor Parcels QR</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <style type="text/css">
            /*.container {
                margin-left: auto;
                margin-right: auto;
                padding-left: 15px;
                padding-right: 15px;
            }
    
            @media (min-width: 992px) {
                .container {
                    width: 970px;
                 }
            }
            .row {
                display: grid;
                grid-template-columns: repeat(12, 1fr);
                grid-gap: 20px;
            }*/

            /*.container {
              position: relative;
            }
    
            .secondary-content {
              position: absolute;
              top: 0;
              right: 0;
              bottom: 0;
              left: 0;
              width: 20%;
              overflow-y: scroll;
            }*/

            .qrCenter {
                margin: auto;
                padding: 5px;
            }

            .other-pages{
                page-break-before: always;
            }

        </style>
    </head>
    <body>
        @foreach($orders as $order)
        <div class="container {{ ($loop->iteration / 2 == 0) ? 'other-pages'  : '' }} " style="margin-left: 10px; margin-bottom: 0px;  width: 95%; margin-top: 0px">  
            <div class="row">
                <div class="col-xs-3"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:left; font-size: 10px;">
                    <span style="font-weight: bold">Shipping Date:</span> {{ date('d/m/y') }}
                </div>
                <div class="col-xs-5"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:center; font-size: 10px;">
                    <span style="font-weight: bold">AHL</span>
                </div>
                <div class="col-xs-4"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:right; font-size: 10px;">
                    <span style="font-weight: bold">Shipping Time:</span> {{ date('h:i:s A') }}
                </div>
            </div>

            <div class="row" style="height: auto">
                <div class="col-xs-2" style="border:1px solid black; height: 80px; text-align: center">
                    <img src="logo/ahl_logo.png" width="80px" height="45px" style="margin-top: 15px">
                </div>
                <div class="col-xs-8" style="border:1px solid black; height: 80px;">
                    <div style="height: auto;">
                        <div style="font-weight: bold; text-align: center; border-bottom:1px solid gray;">Consignee Information</div>
                        <div class="col-xs-6" style="margin-top: 5px; margin-left: -15px; height: auto;">
                            <p style="line-height: 0.5;width: 100%"><span style="font-weight: bold; font-size: 11px;">Name:</span> <span style="font-size: 13px">{{$order->consignee_first_name}} {{$order->consignee_last_name ? $order->consignee_last_name : ''}}</span></p>
                            <p style="line-height: 0.5;width: 100%"><span style="font-weight: bold; font-size: 11px;">Phone:</span> <span style="font-size: 13px">{{$order->consignee_phone}}</span></p>
                            <p style="line-height: 0.5;width: 100%"><span style="font-weight: bold; font-size: 11px;">Destination City:</span> <span style="font-size: 13px">{{$order->customerCity->name}}</span></p>
                        </div>
                        <div class="col-xs-6" style="margin-top: 15px; height: auto;">
                            @php
                            $reference = $order->order_reference;

                            echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($reference, 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';

                            @endphp
                        </div>
                    </div>
                </div>
                <div class="col-xs-2" style="border:1px solid black; height: 80px; text-align: center">
                    @if(!empty($vendor->vendor_image))
                    <img src="{{$vendor->vendor_image}}" width="60px" height="60px" style="margin-top: 5px">
                    @else
                    <p style="line-height: 1;"><span style="font-size: 20px; font-weight: bold; margin-top: 20px">{{$vendor->vendor_name}}</span></p>
                    @endif
                </div>
            </div>

            <div class="row" style="border:1px solid black;">
                <div class="col-xs-2 qrCenter" style="border:1px solid black; height: 190px">
                    <div style="text-align: center">
                    {!! QrCode::size(80)->generate('https://vendor.ahlogistic.pk/api/change-parcel-status-qr?'.$order->id) !!}
                    </div>
                    <br>
                    <div style="text-align: left; border-top:1px solid black; margin-top: -10px;">
                        <p style="font-weight: bold; font-size: 10px">Payment Details</p>
                        <p style="line-height: 0.5; font-size: 10px;">Payment Mode:</p>
                        <p style="line-height: 0.5; font-size: 10px;text-align: center"><span style="font-weight: bold">{{$order->orderType->name}}</span> </p>
                        <p style="line-height: 0.5; font-size: 10px;">Collection Amount:</p>
                        <p style="line-height: 0.5; font-size: 16px;text-align: center"><span style="font-weight: bold">{{$order->consignment_cod_price}}</span></p>
                    </div>
                </div>
                <div class="col-xs-7" style="border:1px solid black;height: 190px;">
                    <p style="font-weight: bold; text-align: center; border-bottom:1px solid gray">Parcel Details</p>
                    <p style="line-height: 0.5;"><span style="font-weight: bold; font-size: 12px;">Quantity:</span> <span style="font-size: 11px">{{$order->consignment_pieces}}</span></p>
                    <p style="line-height: 0.5;"><span style="font-weight: bold; font-size: 12px;">Weight:</span> <span style="font-size: 11px">{{$order->vendorWeight->ahlWeight->weight}}kg</span></p>
                    <p style="line-height: 0.8; width: 100%; border-bottom: 1px solid gray;"><span style="font-weight: bold; font-size: 12px;">Address:</span> <span style="font-size: 11px">{{$order->consignee_address}}</span></p>
                    <p style="line-height: 0.8;  border-bottom: 1px solid gray;"><span style="font-weight: bold; font-size: 12px;">Additional Note:</span> {{$order->additional_services_type}}</p>
                    <p style="line-height: 0.7;"><span style="font-weight: bold; font-size: 12px;">Description:</span> <span style="font-size: 9px">{{$order->consignment_description}}</span></p>
                        
                </div>
                <div class="col-xs-3" style="border:1px solid black;height: 190px;">
                        
                    <div>
                        <p style="font-weight: bold; border-bottom:1px solid gray">Shipper Information</p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Shipper Name:</span></p>
                        <p style="line-height: 0.4; text-align: center;"><span style="font-size: 20px; font-weight: bold">{{$vendor->vendor_name}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Order ID:</span></p>
                        <p style="line-height: 0.4; text-align: center;"><span style="font-size: 11px">{{$order->consignment_order_id}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Parcel Nature:</span></p>
                        <p style="line-height: 0.4; text-align: center;"><span style="font-size: 11px">{{$order->parcelNature->name}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Origin City: </span><span style="font-size: 11px">{{$vendor->vendorCity->name}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Shipper Complain Number: </span><span style="font-size: 11px">{{$vendor->complain_number}}</span></p>
                    </div>
                </div>
            </div>

            <div class="row" style="border:2px solid black;text-align:center;font-size:9px">
                <div class="col-xs-12">Do not give extra charges to the AHL Driver. If the package is torn or damaged, do not accept, and return the shipment.
                </div>
            </div>
            <hr style="border-top: dotted 1px;" >
        </div>
        @if($vendor->printing_slips == 2)
        <div class="container {{ ($loop->iteration / 2 == 0) ? 'other-pages'  : '' }} " style="margin-left: 10px; margin-bottom: 0px;  width: 95%; margin-top: 0px">  
            <div class="row">
                <div class="col-xs-3"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:left; font-size: 10px;">
                    <span style="font-weight: bold">Shipping Date:</span> {{ date('d/m/y') }}
                </div>
                <div class="col-xs-5"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:center; font-size: 10px;">
                    <span style="font-weight: bold">Customer Copy</span>
                </div>
                <div class="col-xs-4"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:right; font-size: 10px;">
                    <span style="font-weight: bold">Shipping Time:</span> {{ date('h:i:s A') }}
                </div>
            </div>

            <div class="row" style="height: auto">
                <div class="col-xs-2" style="border:1px solid black; height: 80px; text-align: center">
                    <img src="logo/ahl_logo.png" width="80px" height="45px" style="margin-top: 15px">
                </div>
                <div class="col-xs-8" style="border:1px solid black; height: 80px;">
                    <div style="height: auto;">
                        <div style="font-weight: bold; text-align: center; border-bottom:1px solid gray;">Consignee Information</div>
                        <div class="col-xs-6" style="margin-top: 5px; margin-left: -15px; height: auto;">
                            <p style="line-height: 0.5;width: 100%"><span style="font-weight: bold; font-size: 11px;">Name:</span> <span style="font-size: 13px">{{$order->consignee_first_name}} {{$order->consignee_last_name ? $order->consignee_last_name : ''}}</span></p>
                            <p style="line-height: 0.5;width: 100%"><span style="font-weight: bold; font-size: 11px;">Phone:</span> <span style="font-size: 13px">{{$order->consignee_phone}}</span></p>
                            <p style="line-height: 0.5;width: 100%"><span style="font-weight: bold; font-size: 11px;">Destination City:</span> <span style="font-size: 13px">{{$order->customerCity->name}}</span></p>
                        </div>
                        <div class="col-xs-6" style="margin-top: 15px; height: auto;">
                            @php
                            $reference = $order->order_reference;

                            echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($reference, 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';

                            @endphp
                        </div>
                    </div>
                </div>
                <div class="col-xs-2" style="border:1px solid black; height: 80px; text-align: center">
                    @if(!empty($vendor->vendor_image))
                    <img src="{{$vendor->vendor_image}}" width="60px" height="60px" style="margin-top: 5px">
                    @else
                    <p style="line-height: 1;"><span style="font-size: 20px; font-weight: bold; margin-top: 20px">{{$vendor->vendor_name}}</span></p>
                    @endif
                </div>
            </div>

            <div class="row" style="border:1px solid black;">
                <div class="col-xs-2 qrCenter" style="border:1px solid black; height: 190px">
                    <div style="text-align: center">
                    {!! QrCode::size(80)->generate('https://vendor.ahlogistic.pk/api/change-parcel-status-qr?'.$order->id) !!}
                    </div>
                    <br>
                    <div style="text-align: left; border-top:1px solid black; margin-top: -10px;">
                        <p style="font-weight: bold; font-size: 10px">Payment Details</p>
                        <p style="line-height: 0.5; font-size: 10px;">Payment Mode:</p>
                        <p style="line-height: 0.5; font-size: 10px;text-align: center"><span style="font-weight: bold">{{$order->orderType->name}}</span> </p>
                        <p style="line-height: 0.5; font-size: 10px;">Collection Amount:</p>
                        <p style="line-height: 0.5; font-size: 16px;text-align: center"><span style="font-weight: bold">{{$order->consignment_cod_price}}</span></p>
                    </div>
                </div>
                <div class="col-xs-7" style="border:1px solid black;height: 190px;">
                    <p style="font-weight: bold; text-align: center; border-bottom:1px solid gray">Parcel Details</p>
                    <p style="line-height: 0.5;"><span style="font-weight: bold; font-size: 12px;">Quantity:</span> <span style="font-size: 11px">{{$order->consignment_pieces}}</span></p>
                    <p style="line-height: 0.5;"><span style="font-weight: bold; font-size: 12px;">Weight:</span> <span style="font-size: 11px">{{$order->vendorWeight->ahlWeight->weight}}kg</span></p>
                    <p style="line-height: 0.8; width: 100%; border-bottom: 1px solid gray;"><span style="font-weight: bold; font-size: 12px;">Address:</span> <span style="font-size: 11px">{{$order->consignee_address}}</span></p>
                    <p style="line-height: 0.8;  border-bottom: 1px solid gray;"><span style="font-weight: bold; font-size: 12px;">Additional Note:</span> {{$order->additional_services_type}}</p>
                    <p style="line-height: 0.7;"><span style="font-weight: bold; font-size: 12px;">Description:</span> <span style="font-size: 9px">{{$order->consignment_description}}</span></p>
                        
                </div>
                <div class="col-xs-3" style="border:1px solid black;height: 190px;">
                        
                    <div>
                        <p style="font-weight: bold; border-bottom:1px solid gray">Shipper Information</p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Shipper Name:</span></p>
                        <p style="line-height: 0.4; text-align: center;"><span style="font-size: 20px; font-weight: bold">{{$vendor->vendor_name}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Order ID:</span></p>
                        <p style="line-height: 0.4; text-align: center;"><span style="font-size: 11px">{{$order->consignment_order_id}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Parcel Nature:</span></p>
                        <p style="line-height: 0.4; text-align: center;"><span style="font-size: 11px">{{$order->parcelNature->name}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Origin City: </span><span style="font-size: 11px">{{$vendor->vendorCity->name}}</span></p>
                        <p style="line-height: 0.4;"><span style="font-weight: bold; font-size: 12px;">Shipper Complain Number: </span><span style="font-size: 11px">{{$vendor->complain_number}}</span></p>
                    </div>
                </div>
            </div>

            <div class="row" style="border:2px solid black;text-align:center;font-size:9px">
                <div class="col-xs-12">Do not give extra charges to the AHL Driver. If the package is torn or damaged, do not accept, and return the shipment.
                </div>
            </div>
            <hr style="border-top: dotted 1px;" >
        </div>
        @endif
        @endforeach
    </body>
</html>

<script type="text/javascript">
$(document).ready(function () {
    window.print();
});
</script>
