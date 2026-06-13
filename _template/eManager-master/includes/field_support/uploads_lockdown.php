<?php

/**
 * Locked down uploads to only show user's own uploads
 */
add_filter( 'posts_where', 'eman_posts_where' );
function eman_posts_where( $where )
{
	$current_user = wp_get_current_user();
	if ( is_user_logged_in() )
	{
		// logged in user, but ware we viewing the library?
		if ( isset( $_POST['action'] ) && 'query-attachments' == $_POST['action'] ) {
			$where .= ' AND post_author=' . $current_user->data->ID;
		}
	}

	return $where;
}