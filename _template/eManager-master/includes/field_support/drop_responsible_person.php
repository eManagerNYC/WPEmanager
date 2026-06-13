<?php

/**
 * Filter the responsible_person selectors
 */
add_filter( 'acf/load_field/name=turner_responsible', 'eman_responsible_person', 1, 2 );
add_filter( 'acf/load_field/name=send_to', 'eman_responsible_person', 1, 2 );
function eman_responsible_person( $field, $post_id=false )
{
	if (
		! empty($field['_name']) &&
		! empty($field['key']) &&
		'field_bic_turner' != $field['key'] &&
		'field_bic_custom_approvers' != $field['key']
	) {
		$approvers = $post_type = null;
		// Most cases, this is an archive
		if ( is_post_type_archive() ) {
			$post_type = $GLOBALS['wp_query']->query['post_type'];
		// Sometimes a plain post
		} elseif ( ! empty($GLOBALS['post']) ) {
			$post_type = $GLOBALS['post']->post_type;
		}

		// Initial turner responsible field in PCO
		if ( 'em_noc' === $post_type && 'turner_responsible' == $field['_name'] ) {
			$approvers  = eman_get_field('pco_approvers', 'option');
		}
		// Initial turner responsible field in DCR
		elseif ( 'em_dcr' === $post_type && 'turner_responsible' == $field['_name'] ) {
			$approvers  = eman_get_field('dcr_reviewers', 'option');
		}
		// Review "send to" field
		elseif ( 'em_noc' === $post_type && 'send_to' == $field['_name'] ) {
			$approvers  = eman_get_field('noc_gatekeeper', 'option');
		}
		// Ticket "send to" field
		elseif ( 'em_tickets' === $post_type && 'turner_responsible' == $field['_name'] ) {
			$approvers  = eman_get_field('ticket_approvers', 'option');
		}

		if ( $approvers ) {#&& isset($field['choices']['Turner']) ) {
			// Reset choices
			$field['choices'] = array();
			// Add new choices
			foreach ( $approvers as $approver ) {
				$field['choices'][ $approver['ID'] ] = eman_users_name( $approver );
			}
			if ( 2 > count($approvers) ) {
				$field['allow_null'] = 0;
			}
			#do_action('acf/create_field', $field);
			#return '';
		}

	}

	return $field;
}
