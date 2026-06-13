<?php

/**
 * Change the redirects for forms
 */

add_action( 'acf/save_post', 'update_form_return_urls', 30 );
function update_form_return_urls( $post_id )
{
	$post_type = get_post_type($post_id);
	$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : false;

	/**
	 * Update return url
	 */
	if ( 'em_reviews' == $post_type )
	{
		$_POST['return'] = add_query_arg( 'action', 'reviewed', get_permalink($GLOBALS['post']->ID) );
	}
	elseif ( $cpt && ! empty($cpt['type']) && 'settings' == $cpt['type'] )
	{
		$_POST['return'] = add_query_arg( 'added', 'true', home_url('/settings/' . str_replace('em_', '', $post_type)) );
	}
	elseif ( $post_id && 'message' != $post_type )
	{
		// Redirect to the single post template
		$notification = ( ! empty($_GET['edit']) ) ? 'edited' : 'added';
		if ( in_array($post_type, array('em_tickets','em_noc','em_dcr','em_invoice')) && 'draft' == emanager_post::status($post_id, 'slug') ) {
			$notification = 'confirm';
		}
		$_POST['return'] = add_query_arg( $notification, 'true', get_permalink($post_id) );
	}
}