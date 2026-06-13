jQuery(document).ready(function($) {

	var $sewn_notifications = $('.sewn_notifications');

	$sewn_notifications
		.on('sewn/notifications/setup', function() {

			$('p', this).each(function(){

				var $notification = $(this);
				//$notification.addClass('stupid');

				/**
				 * Fade notification
				 */
				if ( $notification.attr('data-fade') )
				{
					var wait = ( 1 < $notification.data('fade') ) ? $notification.data('fade') : 3000;
					$notification.delay(wait).fadeOut(400, function(){ $notification.remove(); });
				}

				/**
				 * Dismiss notifications
				 */
				$('.sewn_notifications-dismiss', $notification).on('click', function(e) {
					e.preventDefault()

					var $this = $(this);

					$notification.slideUp(400, function(){ $notification.remove(); });

					if ( $this.attr('data-event') )
					{
						$.ajax({
							url:      frontnotify.url,
							type:     'post',
							async:    true,
							cache:    false,
							dataType: 'html',
							data: {
								action:   frontnotify.action,
								event:    $this.data('event')
							}
						});
					}
				});

			});

		})

		.on('sewn/notifications/add', function(e, message, args) {
			var $this = $(this),
				message_attr = {
					text : message
				};
			if ( args.error ) message_attr.class = 'sewn_notifications-error';

			var $new_notification = $('<p/>', message_attr);

			if ( args.fade ) $new_notification.attr('data-fade', 'true');

			$sewn_notifications.append( $new_notification ).trigger('sewn/notifications/setup');
		})

		.trigger('sewn/notifications/setup');;

});