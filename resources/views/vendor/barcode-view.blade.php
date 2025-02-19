@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html>
<head>
	<title>Barcode view</title>
</head>
<body>

	<input type="text" name="barcode" id="barcode" autofocus="">

</body>
</html>

@endsection

@section('custom-js')
<script type="text/javascript">
    $('#barcode').on('keyup',function(e){
    	if(e.keyCode == 13){
    		var parcelOrderId = $("#barcode").val();
    		//console.log(barcode);
            //document.getElementById("sendForm").submit();
            //$("#barcode").val('');
            //$("#barcode").focus();
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
            	url: '/barcode',
                type: 'POST',
                /* send the csrf-token and the input to the controller */
                data: {_token: CSRF_TOKEN, paracel_order_id: parcelOrderId},
                dataType: 'json',

            	success: function(result){
            		alert(result);
			    	//$("#div1").html(result);
				}
			});
        }
    });
</script>
@endsection