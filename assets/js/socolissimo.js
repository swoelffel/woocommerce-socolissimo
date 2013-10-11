jQuery(document).ready(function($) {
	$('#customer_details').find('div.col-2').hide();
	$('#customer_details').find('div.col-1').hide();
	$('#customer_details').find('div.col-1').css('width', '100%');

	// On insére la checkbox au début
	var billtoshipping = '<p id="billtoshipping" class="form-row"><input id="billtoshipping-checkbox" checked="checked" class="input-checkbox" type="checkbox" value="1" name="billtoshipping"><label class="checkbox" for="billtoshipping-checkbox">Facturer à l\'adresse de livraison</label></p>';
	$('#customer_details').prepend(billtoshipping);

	// Hide et show du formulaire de facturation
	$('#billtoshipping-checkbox').click(function() {
		if ($('#billtoshipping-checkbox').prop('checked'))
			$('#customer_details').find('div.col-1').hide();
		else
			$('#customer_details').find('div.col-1').show();
	});

	// Bind de l'envoi du formulaire
	$('form.checkout').submit(function(event) {
		// Si facturer sur adresse de livraison on copie les données de champs à champs
		if ($('#billtoshipping-checkbox').prop('checked')) {
			$('#billing_first_name').val($('#shipping_first_name').val());
			$('#billing_last_name').val($('#shipping_last_name').val());
			$('#billing_company').val($('#shipping_company').val());
			$('#billing_address_1').val($('#shipping_address_1').val());
			$('#billing_address_2').val($('#shipping_address_2').val());
			$('#billing_postcode').val($('#shipping_postcode').val());
			$('#billing_city').val($('#shipping_city').val());
			$('#billing_state').val($('#shipping_state').val());
			
		}
	});
});