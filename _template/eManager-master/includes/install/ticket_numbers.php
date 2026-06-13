<?php

/**
 * Update ticket numbers to make sure every ticket has one
 */
add_action( 'emanager/install/ticket_numbers', 'eman_install_ticket_numbers' );
function eman_install_ticket_numbers()
{
	$tickets = new WP_Query( array(
		'post_type' => 'em_tickets',
	) );

	if ( $tickets->have_posts() ) : while( $tickets->have_posts() ) : $tickets->the_post();
		$request_number = eman_get_field( 'request_number', get_the_ID() );
		if ( ! $request_number ) {
			update_post_meta( get_the_ID(), 'request_number', uniqid() );
		}
	endwhile; endif;

	add_option( 'emanager_installed_ticketnumbers', current_time('timestamp') );
}