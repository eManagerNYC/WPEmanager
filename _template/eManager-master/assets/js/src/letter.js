jQuery(document).ready(function($) {

	$('#letter_close').on('click', function() {

		var $button   = $(this),
			$spinner  = $('<img />', {src: eman.spinner}),
			data      = {
				action:    'letter_close_button',
				letter_id: $button.data('post_id'),
			};

		$spinner.css({marginLeft: '10px'});

		$button
			.prop('disabled', true)
			.after( $spinner );

		$.ajax({
			url:      eman.ajaxurl,
			type:     'post',
			async:    true,
			cache:    false,
			dataType: 'json',
			data:     data,
			success:  function( return_data )
			{
				//console.log(return_data);
				if ( return_data && return_data.status )
				{
					var success = document.createElement('p');
						success.setAttribute('class', 'alert alert-success')
						success.innerHTML = "Successfully closed.";
					$spinner.remove();
					$button.after( success );
					$button.remove();
					$('.review-update').remove();
				}
			},
			error:     function( xhr )
			{
				console.log(xhr.responseText);
			}
		});
	});

});