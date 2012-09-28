<link type="text/css" href="/modules/{$module}/css/ui-lightness/jquery-ui-1.8.20.custom.css" rel="stylesheet" />
<script type="text/javascript" src="/modules/{$module}/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/modules/{$module}/js/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#loading').hide();
	var api='a5355ba79864cee263e829b0d5ddef89';
	var url='/modules/{$module}/getcity.php';
	
	$( "#city" ).autocomplete({
		minLength:3,
		source: function (request, response){
			var city=$('#city').val();
			$('#loading').show();
			
			$.post(url, 
				{ 
					'API-Key': api,
					query: city,
					type: 'destination',
					courier: 'jne',
					format: 'json'
				},
				function(data) {
					response(data.cities);
					$('#loading').hide();
				}
			);
		}
	});
	
	$('#submitCity').click(function(){
		var city=$('#city').val();
		var url='/modules/{$module}/savecity.php';
		
		// Submit City Value
		
	});
});
</script>