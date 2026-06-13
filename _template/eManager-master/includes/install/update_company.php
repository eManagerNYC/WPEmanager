<?php

/**
 * Add inbox
 */
add_action( 'emanager/install/update_company', 'eman_install_update_company' );
function eman_install_update_company()
{
	$all_posts = new WP_Query( array(
		'post_type'      => array( 'em_dcr', 'em_tickets', 'em_noc' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	if ( ! empty($all_posts->posts) ) foreach ( $all_posts->posts as $post_id )
	{
		// Add company
		$company = $company_id = false;
		if ( $company_id = get_post_meta($post_id, 'company', true) ) {
			$company = $company_id;
		}

		if ( ! $company || ! is_numeric($company) )
		{
			if ( $company_id = get_post_meta($post_id, 'company_id', true) ) {
				$company = $company_id;
			}
			if ( ! $company || ! is_numeric($company) )
			{
				$post_author = get_post_field( 'post_author', $post_id );
				$company = get_user_meta($post_author, 'company', true);
			}
			update_post_meta($post_id, 'company', $company);
		}

		$bic = emanager_bic::get_bic( $post_id, 'ID' );
		update_post_meta($post_id, 'bic_user', $bic);
	}

	// Updates an option to keep the theme from overwriting things when it is turned off and on.
	#add_option( 'emanager_update_company', current_time('timestamp') );
	add_option( 'emanager_update_company_2', current_time('timestamp') );
}