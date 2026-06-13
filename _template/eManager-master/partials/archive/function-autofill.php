<?php

$GLOBALS['autofill_id'] = null;

function eman_add_values_to_fields( $fields=[] ) {
	static $posts = null;

	if ( ! $posts ) {
		if ( empty($_GET['n']) || ! wp_verify_nonce( $_GET['n'], 'emanAutofillForm' ) ) {
			return false;
		}

		$current_user = wp_get_current_user();
		$company      = $current_user->company;
		if ( ! empty($_GET['company']) && eman_check_role('turner') ) {
			$company = esc_sql($_GET['company']);
		}

		$args    = array(
			'post_type'      => get_query_var('post_type'),
	#		'author'         => $current_user->ID,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => 1,
			'meta_key'       => 'company',
			'meta_value'     => $company,
		);
		$posts   = new WP_Query( $args );
		wp_reset_query();

		if ( is_object($posts) && ! empty($posts->post) ) {
			$GLOBALS['autofill_id'] = $posts->post->ID;
			do_action( 'sewn/notifications/add', 'Copied from ' . date_i18n(get_option('date_format'), strtotime($posts->post->work_date)) );
		}
	}

	if ( $fields && is_object($posts) && ! empty($posts->post) ) {
		$post_id = $posts->post->ID;
		foreach ( $fields as $key => $field ) {
			if ( ! in_array($field['name'], array('date','work_date', 'em_tickets_signature', 'signature')) ) {
				$field['value']        = apply_filters('acf/load_value', false, $post_id, $field);
				$fields[$key]['value'] = apply_filters('acf/format_value', $field['value'], $post_id, $field);
			}
		}
	}
	return $fields;
}
eman_add_values_to_fields();

if ( ! empty($_GET['autofill']) ) {
	add_filter( 'acf/field_group/get_fields', 'eman_add_values_to_fields' );
}
