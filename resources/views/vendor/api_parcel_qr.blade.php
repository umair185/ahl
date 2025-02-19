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
                padding: 10px;
            }

            .other-pages{
                page-break-before: always;
            }

        </style>
    </head>
    <body>
        @foreach($orders as $order)
        <div class="container {{ ($loop->iteration / 2 == 0) ? 'other-pages'  : '' }} " style="margin-left: 10px; margin-bottom: 30px;  width: 95%; margin-top: 20px">  
            <div class="row">
                <div class="col-xs-3"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:left">
                    <span style="font-weight: bold">Shipping Date:</span> {{ date('d/m/y') }}
                </div>
                <div class="col-xs-5"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:center">
                    <span style="font-weight: bold">AHL</span>
                </div>
                <div class="col-xs-4"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:right">
                    <span style="font-weight: bold">Shipping Time:</span> {{ date('h:i:s A') }}
                </div>
            </div>

            <div class="row" style="height: auto">
                <div class="col-xs-4" style="border:1px solid black; height: 120px; text-align: center">
                    <img width="80px" height="60px" style="margin-top: 20px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFwAAAApCAYAAAC4AE4qAAAACXBIWXMAAAexAAAHsQEGxWGGAAAGxmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNi4wLWMwMDIgNzkuMTY0NDg4LCAyMDIwLzA3LzEwLTIyOjA2OjUzICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgMjIuMSAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMjEtMDMtMTdUMTg6MDI6MjcrMDU6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDIxLTA2LTEyVDE4OjAxOjI5KzA1OjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDIxLTA2LTEyVDE4OjAxOjI5KzA1OjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgcGhvdG9zaG9wOklDQ1Byb2ZpbGU9InNSR0IgSUVDNjE5NjYtMi4xIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjE4Zjg0OWU2LTVjYTktNGQyOS05NDMzLWMzMzVmMGY1NDJmNyIgeG1wTU06RG9jdW1lbnRJRD0iYWRvYmU6ZG9jaWQ6cGhvdG9zaG9wOjc4Y2U5ODg1LTcxMzEtYzU0Ny1iNTM4LTg3OWE5OTRiNjFmYSIgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjEyOTI3Y2VjLTA1MjktNGI2Ny05YzhiLTJmY2VmZmI1YzcxYyI+IDx4bXBNTTpIaXN0b3J5PiA8cmRmOlNlcT4gPHJkZjpsaSBzdEV2dDphY3Rpb249ImNyZWF0ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MTI5MjdjZWMtMDUyOS00YjY3LTljOGItMmZjZWZmYjVjNzFjIiBzdEV2dDp3aGVuPSIyMDIxLTAzLTE3VDE4OjAyOjI3KzA1OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMSAoTWFjaW50b3NoKSIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MzE5ZGU2YTAtMmMxMC00NmIyLTg4MTItOGI1ZjczN2E2NDY0IiBzdEV2dDp3aGVuPSIyMDIxLTA2LTEyVDE3OjU5OjIzKzA1OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMCAoTWFjaW50b3NoKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MThmODQ5ZTYtNWNhOS00ZDI5LTk0MzMtYzMzNWYwZjU0MmY3IiBzdEV2dDp3aGVuPSIyMDIxLTA2LTEyVDE4OjAxOjI5KzA1OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMCAoTWFjaW50b3NoKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8L3JkZjpTZXE+IDwveG1wTU06SGlzdG9yeT4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz62ksfz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" />
                </div>
                <div class="col-xs-8" style="border:1px solid black; height: 120px;">
                    <div style="height: auto;">
                        <div style="font-weight: bold">Consignee Information</div>
                        <div class="col-xs-6" style="margin-top: 10px; height: auto;">
                            <p style="line-height: 1;width: 40%"><span style="font-weight: bold">Name:</span> {{$order->consignee_first_name}} {{$order->consignee_last_name ? $order->consignee_last_name : ''}}</p>
                            <p style="line-height: 1; width: 40%"><span style="font-weight: bold">Phone:</span> {{$order->consignee_phone}}</p>
                        </div>
                        <div class="col-xs-6" style="margin-top: 10px; height: auto;">
                            <p style="line-height: 1;width: 40%"><span style="font-weight: bold">Email:</span> {{$order->consignee_email}}</p>
                            <p style="line-height: 1;width: 40%"><span style="font-weight: bold">Destination City:</span> {{$order->customerCity->name}}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="border:1px solid black;">
                <div class="col-xs-4 qrCenter" style="border:1px solid black; height: 280px">
                    <div style="text-align: center">
                    {!! QrCode::size(150)->generate('https://staging.ahlogistic.pk/api/change-parcel-status-qr?'.$order->id) !!}
                    </div>
                    <br>
                    <div style="text-align: center">
                        @php
                        $reference = $order->order_reference;

                        echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($reference, 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';

                        @endphp
                    </div>
                </div>
                <div class="col-xs-4" style="border:1px solid black;height: 280px;">
                    <p style="font-weight: bold">Parcel Details</p>
                    <p style="line-height: 0.8;"><span style="font-weight: bold">Quantity:</span> {{$order->consignment_pieces}}</p>
                    <p style="line-height: 0.8;"><span style="font-weight: bold">Weight:</span> {{$order->vendorWeight->ahlWeight->weight}}kg</p>
                    <p style="line-height: 1; width: 100%;"><span style="font-weight: bold">Address:</span> {{$order->consignee_address}}</p>
                    <p style="line-height: 1;  border-top: 1px solid black;"><span style="font-weight: bold">Additional Note:</span> {{$order->additional_services_type}}</p>
                    <p style="line-height: 1; border-top: 1px solid black"><span style="font-weight: bold">Description:</span> <span style="font-size: 12px">{{$order->consignment_description}}</span></p>
                        
                </div>
                <div class="col-xs-4" style="border:1px solid black;height: 280px;">
                        
                    <div style="border-bottom: 1px solid black">
                        <p style="font-weight: bold">Shipper Information</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Order ID:</span> {{$order->consignment_order_id}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Shipper Name:</span> {{$vendor->vendor_name}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Origin City:</span> {{$vendor->vendorCity->name}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Shipper Complain Number: </span>{{$vendor->complain_number}}</p>
                    </div>
                    <div style="border-top: 1px solid black">
                        <p style="font-weight: bold">Payment Details</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Payment Mode:</span> {{$order->orderType->name}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Collection Amount:</span> {{$order->consignment_cod_price}}</p>
                    </div>
                </div>
            </div>

            <div class="row" style="border:2px solid black;text-align:center;font-size:10px">
                <div class="col-xs-12">Do not give extra charges to the AHL Driver. If the package is torn or damaged, do not accept, and return the shipment.
                </div>
            </div>
            <hr>
        </div>
        <!--div class="container {{ ($loop->iteration / 2 == 0) ? 'other-pages'  : '' }} " style="margin-left: 10px; margin-bottom: 30px;  width: 95%; margin-top: 20px">  
            <div class="row">
                <div class="col-xs-3"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:left">
                    <span style="font-weight: bold">Shipping Date:</span> {{ date('d/m/y') }}
                </div>
                <div class="col-xs-5"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:center">
                    <span style="font-weight: bold">Customer Copy</span>
                </div>
                <div class="col-xs-4"  style="border:1px solid black; height: auto;
                     width: 33.3%; text-align:right">
                    <span style="font-weight: bold">Shipping Time:</span> {{ date('h:i:s A') }}
                </div>
            </div>

            <div class="row" style="height: auto">
                <div class="col-xs-4" style="border:1px solid black; height: 120px; text-align: center">
                    <img width="80px" height="60px" style="margin-top: 20px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFwAAAApCAYAAAC4AE4qAAAACXBIWXMAAAexAAAHsQEGxWGGAAAGxmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNi4wLWMwMDIgNzkuMTY0NDg4LCAyMDIwLzA3LzEwLTIyOjA2OjUzICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgMjIuMSAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMjEtMDMtMTdUMTg6MDI6MjcrMDU6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDIxLTA2LTEyVDE4OjAxOjI5KzA1OjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDIxLTA2LTEyVDE4OjAxOjI5KzA1OjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgcGhvdG9zaG9wOklDQ1Byb2ZpbGU9InNSR0IgSUVDNjE5NjYtMi4xIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjE4Zjg0OWU2LTVjYTktNGQyOS05NDMzLWMzMzVmMGY1NDJmNyIgeG1wTU06RG9jdW1lbnRJRD0iYWRvYmU6ZG9jaWQ6cGhvdG9zaG9wOjc4Y2U5ODg1LTcxMzEtYzU0Ny1iNTM4LTg3OWE5OTRiNjFmYSIgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjEyOTI3Y2VjLTA1MjktNGI2Ny05YzhiLTJmY2VmZmI1YzcxYyI+IDx4bXBNTTpIaXN0b3J5PiA8cmRmOlNlcT4gPHJkZjpsaSBzdEV2dDphY3Rpb249ImNyZWF0ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MTI5MjdjZWMtMDUyOS00YjY3LTljOGItMmZjZWZmYjVjNzFjIiBzdEV2dDp3aGVuPSIyMDIxLTAzLTE3VDE4OjAyOjI3KzA1OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMSAoTWFjaW50b3NoKSIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MzE5ZGU2YTAtMmMxMC00NmIyLTg4MTItOGI1ZjczN2E2NDY0IiBzdEV2dDp3aGVuPSIyMDIxLTA2LTEyVDE3OjU5OjIzKzA1OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMCAoTWFjaW50b3NoKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6MThmODQ5ZTYtNWNhOS00ZDI5LTk0MzMtYzMzNWYwZjU0MmY3IiBzdEV2dDp3aGVuPSIyMDIxLTA2LTEyVDE4OjAxOjI5KzA1OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgMjIuMCAoTWFjaW50b3NoKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8L3JkZjpTZXE+IDwveG1wTU06SGlzdG9yeT4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz62ksfz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" />
                </div>
                <div class="col-xs-8" style="border:1px solid black; height: 120px;">
                    <div style="height: auto;">
                        <div style="font-weight: bold">Consignee Information</div>
                        <div class="col-xs-6" style="margin-top: 10px; height: auto;">
                            <p style="line-height: 1;width: 40%"><span style="font-weight: bold">Name:</span> {{$order->consignee_first_name}} {{$order->consignee_last_name ? $order->consignee_last_name : ''}}</p>
                            <p style="line-height: 1; width: 40%"><span style="font-weight: bold">Phone:</span> {{$order->consignee_phone}}</p>
                        </div>
                        <div class="col-xs-6" style="margin-top: 10px; height: auto;">
                            <p style="line-height: 1;width: 40%"><span style="font-weight: bold">Email:</span> {{$order->consignee_email}}</p>
                            <p style="line-height: 1;width: 40%"><span style="font-weight: bold">Destination City:</span> {{$order->customerCity->name}}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="border:1px solid black;">
                <div class="col-xs-4 qrCenter" style="border:1px solid black; height: 280px">
                    <div style="text-align: center">
                    {!! QrCode::size(150)->generate('https://staging.ahlogistic.pk/api/change-parcel-status-qr?'.$order->id) !!}
                    </div>
                    <br>
                    <div style="text-align: center">
                        @php
                        $reference = $order->order_reference;

                        echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($reference, 'C128A',1,33,array(1,1,1),true) . '" alt="barcode"   />';

                        @endphp
                    </div>
                </div>
                <div class="col-xs-4" style="border:1px solid black;height: 280px;">
                    <p style="font-weight: bold">Parcel Details</p>
                    <p style="line-height: 0.8;"><span style="font-weight: bold">Quantity:</span> {{$order->consignment_pieces}}</p>
                    <p style="line-height: 0.8;"><span style="font-weight: bold">Weight:</span> {{$order->vendorWeight->ahlWeight->weight}}kg</p>
                    <p style="line-height: 1; width: 100%;"><span style="font-weight: bold">Address:</span> {{$order->consignee_address}}</p>
                    <p style="line-height: 1;  border-top: 1px solid black;"><span style="font-weight: bold">Additional Note:</span> {{$order->additional_services_type}}</p>
                    <p style="line-height: 1; border-top: 1px solid black"><span style="font-weight: bold">Description:</span> <span style="font-size: 12px">{{$order->consignment_description}}</span></p>
                        
                </div>
                <div class="col-xs-4" style="border:1px solid black;height: 280px;">
                        
                    <div style="border-bottom: 1px solid black">
                        <p style="font-weight: bold">Shipper Information</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Order ID:</span> {{$order->consignment_order_id}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Shipper Name:</span> {{$vendor->vendor_name}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Origin City:</span> {{$vendor->vendorCity->name}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Shipper Complain Number: </span>{{$vendor->complain_number}}</p>
                    </div>
                    <div style="border-top: 1px solid black">
                        <p style="font-weight: bold">Payment Details</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Payment Mode:</span> {{$order->orderType->name}}</p>
                        <p style="line-height: 1;"><span style="font-weight: bold">Collection Amount:</span> {{$order->consignment_cod_price}}</p>
                    </div>
                </div>
            </div>

            <div class="row" style="border:2px solid black;text-align:center;font-size:10px">
                <div class="col-xs-12">Do not give extra charges to the AHL Driver. If the package is torn or damaged, do not accept, and return the shipment.
                </div>
            </div>
            <hr>
        </div-->
        @endforeach
    </body>
</html>

<script type="text/javascript">
$(document).ready(function () {
    window.print();
});
</script>
