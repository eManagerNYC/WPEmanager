<?php

/**
 * Get a user's name, this makes up for admin users who haven't added a first and last and formats the name for you.
 *
 * @author  Jake Snyder
 * @return  string User's name
 */
function eman_users_name( $user )
{
	if ( ! $user ) {
		$user = wp_get_current_user();
	}

	if ( is_numeric($user) ) {
		$user = get_user_by('id', $user);
	} elseif ( is_array($user) ) {
		$user = (object) $user;
	}

	if ( ! is_object($user) || (empty($user->user_firstname) && empty($user->user_lastname) && empty($user->display_name)) ) {
		return false;
	}

	$name = $user->user_firstname . ' ' . $user->user_lastname;
	if ( ' ' == $name ) {
		$name = $user->display_name;
	}

	return $name;
}