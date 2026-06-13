<?php

/**
 * Filter the contract selectors
 */
add_filter( 'acf/fields/post_object/query/key=field_533da797b07ce', 'eman_filter_contracts_by_company', 10, 3 );
function eman_filter_contracts_by_company( $args, $field, $post )
{
	if ( eman_check_role('sub') )
	{
		$user_id      = get_current_user_id();
		$user_company = get_user_meta($user_id, 'company', true);

		$args['meta_key']   = 'company';
		$args['meta_value'] = $user_company;
	}

    return $args;
}