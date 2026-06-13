<?php

/**
 * Limit the drop downs to items added by current user's company
 */

	// Ticket Labor select
add_action( 'acf/fields/post_object/query/key=field_52b0052282f60', 'eman_filter_by_company', 10, 3 );
	// Ticket Employee select
add_action( 'acf/fields/post_object/query/key=field_52b0045382f59', 'eman_filter_by_company', 10, 3 );
	// Ticket Material select
add_action( 'acf/fields/post_object/query/key=field_52b0080b04e2a', 'eman_filter_by_company', 10, 3 );
	// Ticket Equipment select
add_action( 'acf/fields/post_object/query/key=field_52b00a85de839', 'eman_filter_by_company', 10, 3 );
	// DCR Labor select
add_action( 'acf/fields/post_object/query/key=field_529f4a5115158', 'eman_filter_by_company', 10, 3 );
	// DCR Employee select
add_action( 'acf/fields/post_object/query/key=field_529f489d15151', 'eman_filter_by_company', 10, 3 );
	// DCR Material select
add_action( 'acf/fields/post_object/query/key=field_529f4d5337815', 'eman_filter_by_company', 10, 3 );
	// DCR Equipment select
add_action( 'acf/fields/post_object/query/key=field_52a5587233ebe', 'eman_filter_by_company', 10, 3 );

function eman_filter_by_company( $args, $field, $post )
{
	$current_user    = wp_get_current_user();
	$current_company = get_user_meta($current_user->ID, 'company', true);
	if ( is_array($current_company) ) {
		$current_company = $current_company[0];
	}

	if ( eman_check_role('sub') )
	{
		$args['meta_key']   = 'company';
		$args['meta_value'] = $current_company;
	}

	return $args;
}