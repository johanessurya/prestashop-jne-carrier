<script type="text/javascript">
$(function() {
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
					type: 'origin',
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
});
</script>

<h2>JNE</h2>
<fieldset>
	<legend><img src="/modules/{$module}/logo.gif" alt="">Status</legend>
	{if $sourceCity eq 0}
		<img src="/img/admin/warn2.png"><strong>JNE is not configured yet, please:</strong><br>
		<img src="/img/admin/warn2.png">Configure source city first<br>
	{else}
		<img src="/img/admin/module_install.png"><strong>JNE is configured and online!</strong>
	{/if}
</fieldset>

<div class="clear">&nbsp;</div>
<style>
	#tabList { clear: left; }
	.tabItem { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
</style>
<div id="tabList">
	<div class="tabItem">
		<form action="{$uri}" method="post" class="form" id="configForm">
			<fieldset style="border: 0px;">
				<table border="0" width="400" cellpadding="0" cellspacing="0" id="form">
						<tbody><tr><td colspan="2">Please specify Your current city. JNE will calculate shipping cost</td></tr>
						<tr>
							<td width="130" style="height: 35px;">Your City</td>
							<td><input id="city" type="text" size="20" name="{$module}city" value="{$city}"> <img id="loading" src="/modules/{$module}/loading.gif" title="Loading" /></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><input class="button" name="btnSubmit" value="Update settings" type="submit"></td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</form>
	</div>
<div>
			
