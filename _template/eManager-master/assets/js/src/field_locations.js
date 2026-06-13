jQuery(document).ready(function($){

	function update_location_by_floor( $select, autofill )
	{
		var post_id      = $select.val(),
			key          = $select.closest('tr').find('.order').text(),
			$next_select = $select.closest('.field').next().find('select');

		if ( ! isNaN(post_id) )
		{
			$.ajax({
				url :      eman.ajaxurl,
				type :     'post',
				async :    true,
				cache :    false,
				dataType : 'html',
				data :     {
					'action'    : 'load_field_location',
					'post_id'   : post_id,
					'ticket_id' : eman.post_id,
					'key'       : key,
				},
				success: function( return_data )
				{
					if ( return_data ) {
						$next_select.html(return_data).select2();
					}
				},
				error: function( xhr )
				{
					console.log(xhr.responseText);
				}
			});
		}
	};

	function update_floors_by_building( $select, autofill )
	{
		var post_id      = $select.val(),
			key          = $select.closest('td').siblings('.order').text(),
			$next_select = $select.closest('.field').next().find('select');

		if ( ! isNaN(post_id) )
		{
			$.ajax({
				url :      eman.ajaxurl,
				type :     'post',
				async :    true,
				cache :    false,
				dataType : 'html',
				data :     {
					'action'    : 'load_field_floor',
					'post_id'   : post_id,
					'ticket_id' : eman.post_id,
					'key'       : key,
				},
				success: function( return_data )
				{
//console.log(return_data);
					if ( return_data ) {
						$next_select.html(return_data).select2();
						update_location_by_floor( $next_select, autofill );
					}
				},
				error: function( xhr )
				{
					console.log(xhr.responseText);
				}
			});
		}
	}

	/**
	 * Generally set up fields
	 */
	$(document).on('acf/setup_fields', function(e, div) {

		var $buildingSelect = $('.field[data-field_name="building"] select'),
			$floorSelect = $('[data-field_name="floor"] select');

		// Update on load
		$buildingSelect.each(function() {
			update_floors_by_building($(this), true);
		});

		/**
		 * When a Location building is selected, update the floors select
		 */
		$buildingSelect.on('change', function() {
			var $field = $(this),
				val    = $field.val();

			// If a building is selected
			if ( val ) {
				update_floors_by_building( $field );
			}
		});

		/**
		 * When a Location floor is selected, update the locations select
		 */
		$floorSelect.on('change', function() {
			var $field = $(this),
				val    = $field.val();

			// If a building is selected
			if ( val ) {
				update_location_by_floor( $field );
			}
		});

	});

});