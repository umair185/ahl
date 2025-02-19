<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Staff Verification Document</title>
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
    </head>
    <body>
        <div>
            <header class="row" style="width: 90%;" class="clearfix">
                <div id="logo">
                        <!-- <img width="100px" height="50px" src="logo/ahl_logo.png"> -->
                    <img width="100px" height="50px" src="logo/ahl_logo_pdf.png">
                </div>
                <h1>STAFF VERIFICATION DOCUMENT</h1>
                <div style="float: right;" class="clearfix">
                    <h3 style="text-decoration: underline;">Company Detail</h3>
                    <div>AH Logistic</div>
                    <div>1-House no 279, N block,<br> Johar Town Phase 2, Lahore</div>
                    <div>+92 310 3338511</div>
                    <div><a href="mailto:info@ahl.pk">info@ahlogistic.pk</a></div>
                    <br>
                </div>
                <div style="float: left;">
                    <h3 style="text-decoration: underline;">Basic Detail</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Staff Name: </span> {{ $find_user->name }}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Staff CNIC: </span> {{$find_user->userDetail->cnic}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Staff Phone: </span> {{$find_user->userDetail->phone}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Marital Status: </span> {{$find_user->userDetail->marital_status}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Pin Location: </span> {{$find_user->userDetail->pin_location}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Date of Birth: </span> {{date('d M Y', strtoTime($find_user->userDetail->dob))}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Number of Siblings: </span> {{ $find_user->userDetail->siblings}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Bike Number: </span> {{ $find_user->userDetail->bike_number}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Current Address: </span> {{ $find_user->userDetail->address }}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Permanent Address: </span> {{ $find_user->userDetail->permanent_staff_address }}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">House Status: </span> {{ $find_user->userDetail->house_status }}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Live From: </span> {{ $find_user->userDetail->live_from }}</div>
                    <h3 style="text-decoration: underline;">CNIC</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">CNIC Front: </span>
                        <p>
                            <img id="house_output" width="500" height="200" src="{{$find_user->userDetail->cnic_front}}" />
                        </p>
                    </div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">CNIC Back: </span>
                        <p>
                            <img id="house_output" width="500" height="200" src="{{$find_user->userDetail->cnic_back}}" />
                        </p>
                    </div>
                    <h3 style="text-decoration: underline;">Family Detail</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Father Name: </span> {{$find_user->userDetail->father_name}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Father CNIC: </span> {{$find_user->userDetail->father_cnic}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Father Phone: </span> {{$find_user->userDetail->father_phone}}</div>
                    <br>
                    <h3 style="text-decoration: underline;">Emergency Contact Detail</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Emergency Contact Name: </span> {{$find_user->userDetail->emergency_name}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Emergency Contact Relation: </span> {{$find_user->userDetail->emergency_relation}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Emergency Contact Phone: </span> {{$find_user->userDetail->emergency_phone}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Emergency Contact Picture: </span>
                        <p>
                            <img id="house_output" width="500" height="500" src="{{$find_user->userDetail->emergency_picture}}" />
                        </p>
                    </div>
                    <br>
                    <h3 style="text-decoration: underline;">Images</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">House Image: </span>
                        <p>
                            <img id="house_output" width="500" height="500" src="{{$find_user->userDetail->house_image}}" />
                        </p>
                    </div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Payment Cheque: </span>
                        <p>
                            <img id="house_output" width="500" height="300" src="{{$find_user->userDetail->payment_cheque}}" />
                        </p>
                    </div>
                    <h3 style="text-decoration: underline;">Grantor One Basic Details</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Name: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_name : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 CNIC: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_cnic : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Phone: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_phone : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Father Name: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_father_name : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Relation: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_relation : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Pin Location: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_pin_location : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Age: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_age : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Job: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_job : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Income: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_income : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Address: </span> {{ $find_user->userGrantor ? $find_user->userGrantor->grantor_address : ''}}</div>
                    <br>
                    <h3 style="text-decoration: underline;">Images</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 House Image: </span>
                        <p>
                            <img id="house_output" width="500" height="600" src="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_house : ''}}" />
                        </p>
                    </div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 1 Image: </span>
                        <p>
                            <img id="house_output" width="500" height="600" src="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_image_one : ''}}" />
                        </p>
                    </div>
                    <h3 style="text-decoration: underline;">Grantor Two Basic Details</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Name: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_name_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 CNIC: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_cnic_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Phone: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_phone_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Father Name: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_father_name_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Relation: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_relation_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Pin Location: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_pin_location_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Age: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_age_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Job: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_job_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Income: </span> {{$find_user->userGrantor ? $find_user->userGrantor->grantor_income_two : ''}}</div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Address: </span> {{ $find_user->userGrantor ? $find_user->userGrantor->grantor_address_two : ''}}</div>
                    <br>
                    <h3 style="text-decoration: underline;">Images</h3>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 House Image: </span>
                        <p>
                            <img id="house_output" width="500" height="600" src="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_house_two : ''}}" />
                        </p>
                    </div>
                    <div style="font-size: 17px; line-height: 25px;"><span style="font-size: 12px">Grantor 2 Image: </span>
                        <p>
                            <img id="house_output" width="500" height="600" src="{{$find_user->userGrantor ? $find_user->userGrantor->grantor_image_two : ''}}" />
                        </p>
                    </div>
                </div>
            </header>

        </div>
    </body>
</html>