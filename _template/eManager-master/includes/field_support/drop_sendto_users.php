<?php

/**
* Get all users by role for "send to:" select
*/
add_filter( 'wp_ajax_update_users_by_role', 'eman_update_users_by_role');
function eman_update_users_by_role()
{
	if ( empty($_POST['role']) ) {
		return false;
	}

	global $wpdb;

	$args = array(
	    'meta_query' => array(
	        'relation' => 'OR'
	    )
	);

	foreach ( (array) $_POST['role'] as $rolename )
	{
		$args['meta_query'][] = array(
            'key'     => $wpdb->prefix . 'capabilities',
            'value'   => '"' . $rolename . '"',
            'compare' => 'like',
        );
	}
	$user_query = new WP_User_Query( $args );

	if ( ! empty($user_query->results) )
	{
		foreach ( $user_query->results as $user )
		{
			echo "<option value=\"{$user->ID}\">{$user->display_name}</p>";
		}
	} else {
		echo 'No users found.';
	}
	die;
}