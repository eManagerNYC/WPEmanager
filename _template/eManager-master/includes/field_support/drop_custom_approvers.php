<?php

/**
 * Creating our own user field to add reviewers to it
 * DCR & TICKETS
 */
add_filter( 'acf/load_field/key=field_bic_custom_approvers', 'eman_field_bic_custom_approvers', 1 );
function eman_field_bic_custom_approvers( $field )
{
	$field['choices'] = $selected = array();


	/**
	 * Add approvers
	 */

	if ( (is_post_type_archive('em_dcr') || ( ! empty($GLOBALS['post']) && 'em_dcr' == $GLOBALS['post']->post_type)) && 'send_to' == $field['_name'] ) {
		// DCR approvers
		$ticket_approvers = eman_get_field('dcr_reviewers', 'option');
	} elseif ( (is_post_type_archive('em_tickets') || ( ! empty($GLOBALS['post']) && 'em_tickets' == $GLOBALS['post']->post_type)) && 'send_to' == $field['_name'] ) {
		// TICKET approvers
		$ticket_approvers = eman_get_field('ticket_approvers', 'option');
	}

	if ( is_array($ticket_approvers) )
	{
		$field['choices']['Approvers'] = array();
		foreach ( $ticket_approvers as $ticket_approver ) {
			$field['choices']['Approvers'][ $ticket_approver['ID'] ] = eman_users_name($ticket_approver);
			$selected[] = $ticket_approver['ID'];
		}
	}


	/**
	 * Add users
	 */

	if ( ! function_exists( 'get_editable_roles' ) ) { 
		// if using front-end forms then we need to add this core file
		require_once( ABSPATH . '/wp-admin/includes/user.php' ); 
	}

	// editable roles
	$editable_roles = get_editable_roles();

	// Update ISSUE roles to include subcontractors
	if ( (is_post_type_archive('em_issue') || ( ! empty($GLOBALS['post']) && 'em_issue' == $GLOBALS['post']->post_type)) && 'send_to' == $field['_name'] ) {
		$field['role'][] = 'all';
	}

	if ( ! empty($field['role']) )
	{
		if ( ! in_array('all', $field['role']) )
		{
			foreach ( $editable_roles as $role => $role_info )
			{
				if ( ! in_array($role, $field['role']) ) {
					unset( $editable_roles[ $role ] );
				}
			}
		}
	}

	// filters
	$args = array();
	$args = apply_filters('acf/fields/user/query', $args, $field, $GLOBALS['post']->ID);
	$args = apply_filters('acf/fields/user/query/name=' . $field['_name'], $args, $field, $GLOBALS['post']->ID );
	$args = apply_filters('acf/fields/user/query/key=' . $field['key'], $args, $field, $GLOBALS['post']->ID );

	// get users
	$users = get_users( $args );
	if ( ! empty($users) && ! empty($editable_roles) )
	{
		foreach ( $editable_roles as $role => $role_info )
		{
			// vars
			$this_users = array();
			$this_json = array();

			// loop over users
			$keys = array_keys($users);
			foreach ( $keys as $key )
			{
				if ( in_array($role, $users[ $key ]->roles) )
				{
					$this_users[] = $users[ $key ];
					unset( $users[ $key ] );
				}
			}

			// bail early if no users for this role
			if ( empty($this_users) ) {
				continue;
			}

			// label
			$label = translate_user_role( $role_info['name'] );

			// append to choices
			$field['choices'][ $label ] = array();

			foreach( $this_users as $user )
			{
				if ( ! in_array($user->ID , $selected) ) {
					$field['choices'][ $label ][ $user->ID ] = ucfirst( $user->display_name );
				}
			}
		}
	}


	return $field;
}
