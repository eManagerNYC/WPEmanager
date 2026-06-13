jQuery(document).ready(function($){

	/**
	 * Add select2 to the messenger fields
	 */
	$(document).on('acf/setup_fields cbox_complete loaded.bs.modal shown.bs.modal', function(e, div) {
		$('#acf-field-to').select2({
			minimumInputLength: 2,
			placeholder: "Select a User",
			width: '100%',
		});

		$('#acf-field-message_filters').select2({
			placeholder: "Select a Filter",
			width: '100%',
		});
	});

	/**
	 * Update users when a role is selected on comments
	 */
	$('.comment-form-messenger-role select').each(function(){
		var $select    = $(this),
			select_val = $select.val();

		// If a building is selected
		if ( select_val )
		{
			update_users_by_role( $select );
		}

		$select.on('change', function(e) {
			update_users_by_role( $select );
		});
	});
		function update_users_by_role( $select )
		{
			var role = $select.val(),
				$next_select = $select.closest('form').find('#messenger-user');

			$.ajax({
				url :      eman.ajaxurl,
				type :     'post',
				async :    true,
				cache :    false,
				dataType : 'html',
				data :     {
					'action' : 'update_users_by_role',
					'role'   : role,
				},
				success: function( return_data )
				{
					console.log(return_data);
					if ( return_data && 'No users found.' != return_data )
					{
						$next_select.html(return_data).select2();
					}
				},
				error: function( xhr )
				{
					console.log(xhr.responseText);
				}
			});
		};

});