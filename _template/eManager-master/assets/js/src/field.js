jQuery(document).ready(function($){

	/**
	 * Generally set up fields
	 */
	$(document).on('acf/setup_fields', function(e, div) {
		//$('.select2-container', div).remove();
		//$('select', div).select2({'dropdownAutoWidth' : true});
		//$("select").addClass('form-control').select2({'dropdownAutoWidth' : true});

		$('.select2-container', div).remove();
		//$("select").addClass('form-control').select2({'dropdownAutoWidth' : true});

		$('.acf-button').removeClass('acf-button').addClass('btn btn-default btn-xs').prepend(' ').prepend( $('<i />', {'class' : 'fa fa-plus'}) );
		$('#poststuff .field input[type="submit"], .field-submit input').addClass('btn btn-primary btn-lg');

		// Update materials and equipment amount used unit
		$('.field_key-field_52b00995de834, .field_key-field_52b00ae7de83c', div).find('input').on('change', function(){
			update_amount_used( $(this).closest('.field') );
		});

		// Update materials and equipment
		$('.field_key-field_52b0080b04e2a, .field_key-field_52b00a85de839', div).find('select').on('change', function(){

			var $this   = $(this),
				$field  = $this.closest('.field'),
				post_id = $this.val();

			$.ajax({
				url :      eman.ajaxurl,
				type :     'post',
				async :    true,
				cache :    false,
				dataType : 'json',
				data :     {
					'action' :  'populate_material_equipment',
					'post_id' : post_id
				},
				success: function( return_data )
				{
					//console.log(return_data);
					if ( return_data.status )
					{
						delete return_data.status;
						$.each(return_data, function( key, value ) {
							
							var $input_field = $field.siblings('[data-field_name="' + key + '"]'),
								$input       = $field.siblings('[data-field_name="' + key + '"]').find('input');

							// If this is "Amount Used", only update if empty
							if ( 'usage' === key || 'amount_used' === key )
							{
								if ( '' === $input.val() )
								{
									$input.val(value);
								}
							}
							else
							{
								$input.val(value);
							}

							// If this is "Unit of Measure", also append unit to "Amount Used"
							if ( 'measure' == key )
							{
								update_amount_used( $input_field );
							}
						});
					}
				},
				error: function( xhr )
				{
					console.log(xhr.responseText);
				}
			});

		});

	});

});