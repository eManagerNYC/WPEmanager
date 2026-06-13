<?php

/**
 * Add inbox
 */
add_action( 'emanager/install/inbox', 'eman_install_inbox' );
function eman_install_inbox()
{
	$page = get_page_by_path('inbox');
	if ( ! $page )
	{
		$post = array(
			'post_content' => "",
			'post_name'    => "inbox",
			'post_status'  => "publish",
			'post_title'   => "Inbox",
			'post_type'    => "page",
			'menu_order'   => 10,
		);
		wp_insert_post($post);
	}
	flush_rewrite_rules(true);

	// Updates an option to keep the theme from overwriting things when it is turned off and on.
	add_option( 'emanager_inbox', current_time('timestamp') );
}