<!DOCTYPE html>
<html lang="en">
  	<head>
    	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    	<title>{{ $title }} - {{ config('app.name', 'Laravel') }}</title>
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
		      	<h1>INVOICE ( {{ Date('d M Y') }} )</h1>
		      	<div style="float: right;" class="clearfix">
		        	<div>AH Logistic</div>
		        	<div>1-House no 279, N block,<br> Johar Town Phase 2, Lahore</div>
		        	<div>+92 342 4983850</div>
		        	<div><a href="mailto:info@ahl.com">info@ahlogistic.com</a></div>
		      	</div>
		      	<div style="float: left;">
		        	<div><span>Bill To: </span> {{ $vendor[0]['vendor_name'] }}</div>
		        	<div><span>Invoice No: </span> {{ Date('d-m-Y') }}/{{ time() }} </div>
		        	<!-- <div><span>CLIENT</span> John Doe</div> -->
		        	<div><span>ADDRESS: </span> {{ $vendor[0]['vendor_address'] }}</div>
		        	<div><span>EMAIL: </span> <a href="mailto:john@example.com">{{ $vendor[0]['vendor_email'] }}</a></div>
		        	<br>
		        	<div><span>VAT No: </span> {{ ($vendor[0]['ntn']) ? $vendor[0]['ntn'] : 'N/A' }}</div>
		        	<div><span>DATE: </span>{{ Date('d-m-y') }}</div>
		        	<div><span>DUE DATE: </span>{{ Date('d-m-y') }}</div>
		        	<div><span>Term: </span> Due On Receipt</div>
		      	</div>
		    </header>

  			<table style="margin-top: 160px;width: 90%;">
		        <thead>
		          	<tr>
		            	<th class="service">DATE</th>
		            	<th class="desc">ACTIVITY</th>
		            	<th>DESCRIPTION</th>
		            	<th>TAX</th>
		            	<th>QTY</th>
		            	<th>RATE</th>
		            	<th>Amount</th>
		          	</tr>
		        	</thead>
		        	<tbody>
			        	@foreach($invoiceData as $key => $data)
				        <tr>
				            <td class="service">{{ Date('d-m-Y') }}</td>
				            <td class="desc">Delivery Charges</td>
				            <td class="unit">invoice against order delivery</td>
				            <td class="qty">Sales Tax (PRA)</td>
				            <td class="total">{{ $data['qty'] }}</td>
				            <td class="total">{{ $data['rate'] }}</td>
				            <td class="total">{{ $data['amount'] }}</td>
				        </tr>
			          @endforeach
			          	<tr>
				            <td colspan="6">COMMISSION</td>
				            <td class="total">{{ $invoiceTotal['subTotal'] }}</td>
				        </tr>
				        <tr>
				            <td colspan="6">TAX {{ $taxRate }}%</td>
				            <td class="total">{{ $invoiceTotal['taxAmount'] }}</td>
				        </tr>
				        <tr>
				            <td colspan="6" class="grand total" style="font-size: large;">BALANCE DUE</td>
				            <td class="grand total" style="font-size: large;">{{ isset($financial['totalParcelAmount']) ? $financial['totalParcelAmount'] - $invoiceTotal['balanceDue'] : '' }}</td>
				        </tr>
		        </tbody>
  			</table>

   			<br>
	      	<table style="width: 90%">
	        	<thead>
		          	<tr>
		            	<th>RATE</th>
		            	<th>TAX</th>
		            	<th>NET</th>
		            	<th>PARCEL AMOUNT</th>
		            	<th>PAYING TO VENDOR</th>
		          	</tr>
	        	</thead>
	        	<tbody>
	          		<tr>
	            		<td style="text-align: center;">Sales Tax (PRA) @ {{ $taxRate }}%</td>
	            		<td style="text-align: center;">{{ $invoiceTotal['taxAmount'] }}</td>
	            		<td style="text-align: center;">{{ $invoiceTotal['subTotal'] }}</td>
	            		<td style="text-align: center;">{{ isset($financial['totalParcelAmount']) ? $financial['totalParcelAmount'] : '' }}</td>
	            		<td style="text-align: center;">{{ isset($financial['totalParcelAmount']) ? $financial['totalParcelAmount'] - $invoiceTotal['balanceDue'] : '' }}</td>
	          		</tr>
	        	</tbody>
	      	</table>

		    <!-- <footer>
		      Invoice was created on a computer and is valid without the signature and seal.
		    </footer> -->
		    <footer>
		    	We appreciate your busines and look forward to service you again soon
		    </footer> 
		</div>
  	</body>
</html>