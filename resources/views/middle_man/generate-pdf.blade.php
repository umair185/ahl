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
		</div>
		<div class="row">
			<div class="col-md-12 col-lg-12">
				<table class="table" id="general_ledger" style="width: 100%;">
					<thead>
						<tr>
							<th>#</th>
							<th>Order Parcel Number</th>
							<th>Vendor</th>
						</tr>
					</thead>
					<tbody>
						@foreach($orders as $order)
						<tr>
							<td style="text-align:center">{{$loop->iteration}}</td>
							<td style="text-align:center">{{ $order->order_reference}}</td>
							<td style="text-align:center">{{ $order->vendor->vendor_name}}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</body>
</html>