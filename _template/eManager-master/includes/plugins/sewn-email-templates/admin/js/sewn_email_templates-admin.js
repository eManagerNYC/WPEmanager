jQuery(document).ready(function($) {

	/**
	 * Send email preview
	 */
	$('#' + email_preview.prefix + '_send_preview').on('click', function(e) {
		e.preventDefault();

		var $this = $(this);
		var $message = $('#message');

		// Add loading animation
		$message.append(' <div class="' + email_preview.prefix + '_spinner"><img src="' + email_preview.spinner + '" /></div>');

		$.ajax({
			url:      email_preview.url,
			type:     'post',
			async:    true,
			cache:    false,
			dataType: 'html',
			data: {
				action: email_preview.action,
				nonce:  email_preview.nonce,
				email:  $this.siblings('#' + email_preview.prefix + '_email').val(),
				type:   $this.siblings('#' + email_preview.prefix + '_type').val()
			},

			success: function( response )
			{
				console.log(response);

				// Remove animation
				$('.' + email_preview.prefix + '_spinner').remove();

				if ( response )
				{
					$message.addClass('updated').html('<p>' + response + '</p>').show();
				}
			},

			error: function( xhr )
			{
				//console.log(xhr.responseText);
			}
		});
	});

});