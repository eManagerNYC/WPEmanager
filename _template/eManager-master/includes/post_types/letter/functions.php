<?php

/**
 * Set status to "closed" via ajax
 */
add_action( 'wp_ajax_letter_close_button', 'eman_letter_close_button' );
function eman_letter_close_button()
{
	$status = false;
	if ( current_user_can('manage_options') && ! empty($_POST['letter_id']) ) {
		#wp_set_object_terms( $_POST['letter_id'], 'closed', 'em_status' );
		$current_user = wp_get_current_user();
		$review_id = emanager_bic::add_review( $current_user->ID, $_POST['letter_id'], 'closed' );
		$status = $review_id;
	}

	echo json_encode( array( 'status' => $status ) );
	die;
}

add_filter( 'acf/fields/taxonomy/wp_list_categories', 'letter_taxonomy_query', 10, 3 );
function letter_taxonomy_query( $args, $field ) {
	if ( 'field_53a44taxstatus' == $field['key'] ) {
		$terms = get_terms( 'em_status', array(
			'hide_empty' => false,
		));
		$exlude_ids = array();
		$current_terms = array( 'pending','recommend','approved','rejected' );
		foreach ( $terms as $term ) {
			if ( ! in_array($term->slug, $current_terms) ) {
				$exlude_ids[] = $term->term_id;
			}
		}
		#$args['orderby'] = 'count';
		#$args['order'] = 'DESC';
		//'draft','pending','recommend','approved','rejected'
		#$args['exclude'] = array(2,4,5,7,8,9,10,12,15,16,17,18,19,20);
		#$args['exclude'] = array(13,11,5,6,7,8,9);
		$args['exclude'] = $exlude_ids;
	}
	return $args;
}
