<script type="text/javascript">
$(document).ready(function(){
	var loadingEl;
	
	$('#loading').hide();
	var api='a5355ba79864cee263e829b0d5ddef89';
	var url='/modules/{$module}/getcity.php';
	
	//$('input[name*="man"]').val('has man in it!');
	var form, formInput, formCountry;
	
	// For Address
	form='form[action*="address.php"]';
	formInput=form+' input#city';
	formCountry=form+' select#id_country';
	
	// append loading image
	loadingEl=appendLoading(formInput);
	loadingEl.hide();
	$(formInput).autocomplete({
		minLength:3,
		source: function (request, response){
			var city=$(formInput).val();
			var country=$(formCountry).val();
			if(country==111){
				// Show loading
				loadingEl.show();
				$.post(url, 
					{ 
						'API-Key': api,
						query: city,
						type: 'origin',
						courier: 'jne',
						format: 'json'
					},
					function(data) {
						response(data.cities);
						loadingEl.show();
					}
				);
			}
		}
	});
	
	// For Auth
	form='form[action*="authentication.php"]';
	formInput=form+' input#city';
	formCountry=form+' select#id_country';
	
	loadingEl=appendLoading(formInput).find('#loading');
	loadingEl.hide();
	$(formInput).autocomplete({
		minLength:3,
		source: function (request, response){
			var city=$(formInput).val();
			var country=$(formCountry).val();
			if(country==111){
				loadingEl.show();
				$.post(url, 
					{ 
						'API-Key': api,
						query: city,
						type: 'origin',
						courier: 'jne',
						format: 'json'
					},
					function(data) {
						response(data.cities);
						loadingEl.hide();
					}
				);
			}
		},
		change: function(event, ui) {
			if (!ui.item) {
                $(this).val('');
            }
		}
	});
	
	// Check if city has fill with item above
});

function appendLoading(formInput){
	var loading='<img class="ajaxHide" style="position:relative" id="loading" src="/modules/jnecarrierbykenjiro43/loading.gif" title="Loading" />';
	
	// Append last
	return $(formInput).parent().append(loading);	
}
</script>