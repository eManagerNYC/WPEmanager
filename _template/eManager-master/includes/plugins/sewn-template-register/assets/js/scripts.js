jQuery(document).ready(function($) {

	//if ( $('body').is('.register') )
	if ( 'Register' == $('h1.page-title').text() )
	{
		var $field_email = $('#acf-field-email'),
			$field       = $field_email.closest('.field'),
			$sewn_notifications = $('.sewn_notifications');

		$field_email.on('change', function() {

			var $spinner = $('<div />', {'class' : customize_register.prefix + '_spinner'}),
				$spinner_img = $('<img />', {'src' : customize_register.spinner}),
				email = $field_email.val();

			// Add loading animation
			$field_email.after( $spinner.append( $spinner_img ) );

			// Make sure the rest of the form is showing
			$field
				.siblings().show()
				.closest('.postbox')
					.siblings().show();

			// Remove links if they exist
			$('.' + customize_register.prefix + '_links', $field).remove();
			$('.' + customize_register.prefix + '_clear', $field).remove();

			$.ajax({
				url:      customize_register.url,
				type:     'post',
				async:    true,
				cache:    false,
				dataType: 'html',
				data: {
					action:   customize_register.action,
					email:    email
				},

				success: function( response )
				{
					//console.log(response);

					// Remove animation
					$spinner.remove();

					// Hide the form and add options and a clear button
					if ( 'exists' == response )
					{
						var $links        = $('<div />', {'class' : customize_register.prefix + '_links'}),
							$link_login   = $('<a />', {'class' : customize_register.prefix + '_login button', 'href' : customize_register.links.login.href.replace('[email]', email), 'text' : customize_register.links.login.text}),
							$link_recover = $('<a />', {'class' : customize_register.prefix + '_recover button', 'href' : customize_register.links.recover.href, 'text' : customize_register.links.recover.text}),
							$link_clear   = $('<a />', {'class' : customize_register.prefix + '_clear', 'href' : '#javascript_required', 'html' : "&times;"});

						// Make some of the links work
						$link_recover.on('click', function(e) {
							e.preventDefault();
							var $loginform = $('#loginform');
							$('#user_login', $loginform).val( email );
							$loginform.submit();
						});
						$link_clear.on('click', function(e) {
							e.preventDefault();
							var $notification = $('p', $sewn_notifications);

							$field_email.val('').trigger('change');
							$notification.slideUp(400, function(){ $notification.remove(); });
						});

						// Add a link to clear the field
						$field_email.after( $link_clear );

						// Add action links
						$field.append( $links.append($link_login).append($link_recover).show() );
						// Hide the rest of the form
						$field
							.siblings().hide()
							.closest('.postbox')
								.siblings().hide();

						$sewn_notifications.trigger('sewn/notifications/add', [customize_register.messages.exists.message]);
					}
					else if ( 'invalid' == response )
					{
						$sewn_notifications.trigger('sewn/notifications/add', [customize_register.messages.invalid.message, {fade: true}]);
					}
					else
					{
						
					}
				},

				error: function( xhr )
				{
					//console.log(xhr.responseText);
				}
			});

		});
	}

});