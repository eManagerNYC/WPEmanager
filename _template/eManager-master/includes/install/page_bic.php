<?php

/**
 * Add inbox
 */
add_action( 'emanager/install/bic', 'eman_install_bic' );
function eman_install_bic()
{
	$page = get_page_by_path('bic');
	if ( ! $page )
	{
		$post = array(
			'post_content' => "",
			'post_name'    => "bic",
			'post_status'  => "publish",
			'post_title'   => "Ball In Court",
			'post_type'    => "page",
			'menu_order'   => 20,
		);
		wp_insert_post($post);
	}
	flush_rewrite_rules(true);

	// Updates an option to keep the theme from overwriting things when it is turned off and on.
	add_option( 'emanager_bic', current_time('timestamp') );
}