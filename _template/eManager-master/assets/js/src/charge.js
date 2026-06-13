jQuery(document).ready(function($){

	/**
	 * Billed / Paid status
	 */
	$('#charge_billed, #charge_paid').on('click', function() {
		var $checkbox = $(this),
			selected  = $checkbox.is(':checked'),
			value     = $checkbox.val();

		$.ajax({
			url :      eman.ajaxurl,
			type :     'post',
			async :    true,
			cache :    false,
			dataType : 'json',
			data : {
				'action'   : 'eman_update_charge',
				'post_id'  : eman.post_id,
				'selected' : selected,
				'value'    : value,
			},
			success: function( return_data )
			{
				//console.log(return_data);
				if ( return_data.status )
				{
					$('.sewn_notifications').trigger('sewn/notifications/add', ['Status updated', {'fade': true, 'error': false}]);

					var text = '';
					if ( $('#charge_paid').is(':checked') ) {
						text = '(Paid)';
					} else if ( $('#charge_billed').is(':checked') ) {
						text = '(Billed)';
					} else {
						text = '';
					}
					$('.current-status .charge').text(text);
				}
				else
				{
					$('.sewn_notifications').trigger('sewn/notifications/add', ['There was a problem updating status', {'fade': true, 'error': true}]);
					if ( selected ) {
						$checkbox.attr('checked', (selected ? false : true));
					}
				}
			},
			error: function( xhr )
			{
				console.log(xhr.responseText);
			}
		});

	});

});