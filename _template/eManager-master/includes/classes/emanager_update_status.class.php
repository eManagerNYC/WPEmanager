<?php
/*
Plugin Name: Update Status Form
Description: Update the status and create a review
Version: 1.0.0
Author: Jake Snyder
*/

if ( ! class_exists('emanager_update_status') ) :

add_action( 'init', array('emanager_update_status', 'init') );

class emanager_update_status {
	/**
	 * Class prefix
	 *
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Settings
	 *
	 * @var 	string
	 */
	public static $settings = array();

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function init() {
		// actions
		add_action( 'acf/save_post',                          array(__CLASS__, 'save_post'), 20 );

		// filters
		add_filter( 'acf/load_field/key=field_53a44050a1123', array(__CLASS__, 'issue_status') );
	}

	/**
	 * Update issue status field based on user's role
	 * Add value from the parent post
	 */
	public static function issue_status( $field ) {
		// reset choices
		$field['choices'] = array();

		$ps = get_terms( 'em_status', 'orderby=name&hide_empty=0' );

		$status_choices     = array();
		$acceptable_choices = array();
		if ( emanager_post::same_company_as_post($GLOBALS['post']) ) { #eman_can_edit($GLOBALS['post'])
			$acceptable_choices = array('draft', 'open', 'addressed', 'verified', 'rejected', 'disputed', 'closed', 'void');
		} elseif ( eman_check_role('sub') ) {
			$acceptable_choices = array('open','addressed');
		} else {
			$acceptable_choices = array('open','addressed','verified','rejected','disputed');
		}

		foreach ( $ps as $p ) {
			if ( in_array($p->slug, $acceptable_choices) ) {
				$status_choices[$p->slug] = $p->name;
			}
		}

		$order_array    = array_flip($acceptable_choices);
		$status_choices = array_merge($order_array, $status_choices);

		$field['choices'] = $status_choices;

		if ( ! empty($GLOBALS['post']) ) {
			$field['value'] = emanager_post::status($GLOBALS['post'], 'slug');
		}

		return $field;
	}

	/**
	 * Add a review for status updates
	 */
	public static function save_post( $post_id ) {
		if ( ! empty($_POST['post_type']) && 'em_reviews' == $_POST['post_type'] && ! empty($_POST['step']) && 'status' == $_POST['step'] ) {
			$parent_id = ( isset($GLOBALS['post']->ID) ) ? $GLOBALS['post']->ID : 0;

			$args = array(
				'ID'          => $post_id,
				'post_type'   => 'em_reviews',
				'post_status' => 'publish',
			);
			wp_update_post( $args );

			if ( ! empty($_POST['fields']['field_52b026ea3fcab']) ) {
				$bic = $_POST['fields']['field_52b026ea3fcab'];
			} elseif ( ! empty($_POST['fields']['field_52b026eletterbic']) ) {
				$bic = $_POST['fields']['field_52b026eletterbic'];
			} else {
				$bic = get_post_meta($parent_id, 'bic', $parent_id);
			}
			update_post_meta($post_id, 'send_to', $bic);
			update_post_meta($post_id, 'reviewed_id', $parent_id);

			$status = 'draft';
			if ( ! empty($_POST['fields']['field_53a44taxstatus']) ) {
				$term = get_term_by( 'id', $_POST['fields']['field_53a44taxstatus'], 'em_status' );
				$status = $term->slug;
			} elseif ( ! empty($_POST['fields']['field_53a44050a1123']) ) {
				$status = $_POST['fields']['field_53a44050a1123'];
			}

			wp_set_object_terms( $parent_id, $status, 'em_status' );
			wp_set_object_terms( $post_id,   $status, 'em_status' );
		}
	}
}

endif;