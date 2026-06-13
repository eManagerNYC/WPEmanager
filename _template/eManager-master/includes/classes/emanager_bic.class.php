<?php
/*
Plugin Name: Ball in Court
Description: Tracks user's current responsibilities
Version: 1.0.0
Author: Jake Snyder
*/

if ( ! class_exists('emanager_bic') ) :

add_action( 'init', array('emanager_bic', 'init') );

class emanager_bic
{
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
	public static function init()
	{
		self::$settings['bic_users'] = array();

		// actions
		add_action( 'acf/save_post',                       array(__CLASS__, 'save_post'), 30 );

		// filters
		add_filter( 'wp_ajax_update_users_bic',            array(__CLASS__, 'update_users_bic') );
		add_filter( 'acf/load_field/key=field_bic_turner', array(__CLASS__, 'field_bic_turner_value') );
	}

	/**
	 * Add value to the BIC field
	 */
	public static function field_bic_turner_value( $field )
	{
		if ( ! empty($GLOBALS['post']) ) {
			$field['value'] = get_post_meta($GLOBALS['post']->ID, 'bic', true);
		}
		return $field;
	}

	public static function setup_review( $review_id, $parent_id )
	{
		$post_type = get_post_type($parent_id);

		// Add the parent id to the review post
		update_post_meta( $review_id, 'reviewed_id', $parent_id );

		// Get current parent post status, default is draft
		$new_status = $status = emanager_post::status($parent_id, 'slug');
		#wp_set_object_terms( $review_id, $status, 'em_status' );

		/**
		 * Update review and parent statuses
		 */
		if ( 'bic' == $_POST['step'] )
		{
			wp_set_object_terms( $review_id, $status, 'em_status' );
			update_post_meta( $review_id, 'bic', current_time('timestamp') );
		}
		elseif ( eman_can_review($parent_id) )
		{
			$new_status = false;

			if ( 'superintendent' == $status ) {
				$new_status = ( 'em_dcr' == $post_type ) ? 'approved' : 'manager';
			} elseif ( 'manager' == $status && 'em_noc' == $post_type ) {
				$new_status = 'ready';
			} elseif ( 'manager' == $status && 'em_tickets' == $post_type ) {
				$new_status = 'approved';
			} elseif ( 'ready' == $status ) {
				$new_status = 'submitted';
			}

			// Take action based on button clicked
			if ( ! empty($_POST['submit']) )
			{
				if ( 'Authorize' == $_POST['submit'] )
				{
					/**
					 * Copy the numbers into the NOC meta from the review meta
					 */
					if ( 'submitted' == $new_status && 'em_noc' == $post_type )
					{
						// Noc number
						if ( ! empty($_POST['fields']['field_52b342bbfc4da']) ) {
							update_post_meta($parent_id, 'noc_number', $_POST['fields']['field_52b342bbfc4da']);
						}
						// Pco number
						if ( ! empty($_POST['fields']['field_52b342acfc4d9']) ) {
							update_post_meta($parent_id, 'pco_number', $_POST['fields']['field_52b342acfc4d9']);
						}
					}
					emanager_table_filters::update_meta_index($parent_id);
				}
				elseif ( 'Void' == $_POST['submit'] )
				{
					$new_status = 'void';
				}
				elseif ( 'Revise' == $_POST['submit'] )
				{
					$new_status = 'revise';
				}
				elseif ( 'Recommend' == $_POST['submit'] && 'em_noc' == $post_type && in_array($status, array('submitted','recommend')) && (HAS_ROLE_OWNER || HAS_ROLE_TURNER) )
				{
					// Direction
					if ( ! empty($_POST['fields']['field_52b4f7b2ed8d6']) ) {
						update_post_meta( $parent_id, 'direction', $_POST['fields']['field_52b4f7b2ed8d6'] );
					}
					$new_status = 'recommend';
				}
				elseif ( 'Execute' == $_POST['submit'] && 'em_noc' == $post_type && in_array($status, array('submitted','recommend')) && HAS_ROLE_OWNER )
				{
					// Direction
					if ( ! empty($_POST['fields']['field_52b4f7b2ed8d6']) ) {
						update_post_meta( $parent_id, 'direction', $_POST['fields']['field_52b4f7b2ed8d6'] );
					}
					$new_status = 'executed';
				}
			}

			if ( $new_status )
			{
				wp_set_object_terms( $parent_id, $new_status, 'em_status' );
				wp_set_object_terms( $review_id, $new_status, 'em_status' );
			}
		}
	}

	public static function update_bic( $review_id, $parent_id )
	{
		// Updating main post
		if ( $review_id == $parent_id ) {
			$bic_user = get_post_meta($review_id, 'turner_responsible', true);
		}
		// Updating a review
		else {
			$bic_user = get_post_meta($review_id, 'send_to', true);
		}

		$status  = emanager_post::status($parent_id, 'slug');
		$subject = 'An item has been submitted for your review';
		$message = 'Please review: <a href="' . get_permalink($parent_id) . '">' . get_the_title($parent_id) . "</a>\n\r\n\r";
		$message .= 'View here: <a href="' . get_permalink($parent_id) . '">' . get_permalink($parent_id) . "</a>\n\r\n\r";

/**/
		// Update invoice bic separately, and make a few specific adjustments
		if ( 'em_invoice' == get_post_type($parent_id) )
		{
			if ( 'draft' != $status )
			{
				// Update the meta field into real status
				$status_meta = get_metadata('post', $review_id, 'new_status', true);
				if ( $status_meta ) {
					$status = $status_meta;
					wp_set_object_terms( $review_id, $status_meta, 'em_status' );
					wp_set_object_terms( $parent_id, $status_meta, 'em_status' );
				}

				// Add WBSE to parent post if set
				$wbse           = get_metadata('post', $review_id, 'wbse', true);
				if ( $wbse ) {
					update_metadata('post', $parent_id, 'wbse', $wbse);
				}

				$job_id         = get_metadata('post', $parent_id, 'job_id', true);
				$current_bic    = get_metadata('post', $parent_id, 'bic_user', true);
				$reason         = get_metadata('post', $review_id, 'rejection_reason', true);
				$superintendent = get_option('options_invoice_reviewer_' . $job_id);
				$accountant     = get_option('options_invoice_accounting_reviewer');

				// Custom BIC and message
				if ( 'superintendent' == $status ) {
					$bic_user = $superintendent;
					if ( $accountant == $current_bic ) {
						$message = 'An invoice has been returned for revision. Reason: ' . $reason;
					}
				} elseif ( 'manager' == $status ) {
					$bic_user = $accountant;
				} elseif ( 'revise' == $status ) {
					$bic_user = get_post_field( 'post_author', $parent_id );
					$message  = 'An invoice has been returned for revision. Reason: ' . $reason;
				}

				update_metadata('post', $parent_id, 'turner_responsible', $bic_user);
#exit;
			}
		}
		// Update request bic separately, and make a few specific adjustments
		if ( 'em_request' == get_post_type($parent_id) && 'superintendent' == $status )
		{
			$job_id         = get_metadata('post', $parent_id, 'job_id', true);
			$current_bic    = get_metadata('post', $parent_id, 'bic_user', true);
			$superintendent = get_option('options_request_reviewers');

			// Custom BIC and message
			$bic_user = $superintendent;
			if ( $accountant == $current_bic ) {
				$message = 'An invoice has been returned for revision. Reason: ' . $reason;
			}

			update_metadata('post', $parent_id, 'turner_responsible', $bic_user);
		}
/**/

		if ( ! empty($_POST['fields']['field_bic_action_noc_manager']) && 'authorizer' == $_POST['fields']['field_bic_action_noc_manager'] ) {
			update_metadata('post', $parent_id, 'turner_responsible', $bic_user);
		}

		// Take action based on button clicked
		if ( ! empty($_POST['submit']) )
		{
			if ( 'Void' == $_POST['submit'] || ( ! empty($_POST['fields']['field_53a44050a1123']) && 'closed' == $_POST['fields']['field_53a44050a1123']) ) {
				$bic_user = false;
			} elseif ( 'Revise' == $_POST['submit'] ) {
				$bic_user = get_post_field( 'post_author', $parent_id );
			}
		}

		// If this items is finished, remove BIC
		if ( in_array($status, array('approve','approved','executed','void')) ) {
			$bic_user = false;
		}

		// Update BIC
		update_metadata('post', $review_id, 'send_to', $bic_user);
		update_metadata('post', $parent_id, 'bic_user', $bic_user);

		if ( $bic_user )
		{
			// Add a message to new BIC user
			$args = array(
				'subject'    => $subject,
				'message'    => $message,
				'to'         => $bic_user,
				'private'    => true,
			);
			do_action( 'sewn/messenger/add_message', $args );
		}
	}

	public static function add_review( $send_to=false, $post_id=null, $status=null )
	{
		$args = array(
			'post_type'   => 'em_reviews',
			'post_status' => 'publish',
		);
		$review_id = wp_insert_post( $args );

		if ( $send_to && is_numeric($send_to) ) {
			update_post_meta( $review_id, 'send_to', $send_to );
		}

		// Add post to review
		if ( $post_id ) {
			update_post_meta( $review_id, 'reviewed_id', $post_id );
		}

		// Mark status
		if ( $status ) {
			wp_set_object_terms( $review_id, $status, 'em_status' );
			wp_set_object_terms( $post_id, $status, 'em_status' );
		}

		do_action('acf/save_post', $review_id);

		return $review_id;
	}

	public static function save_post( $post_id )
	{
		$parent_id = ( isset($GLOBALS['post']->ID) ) ? $GLOBALS['post']->ID : 0;

		/**
		 * If reviewed, set up the review
		 */
		if ( ! empty($_POST['step']) && in_array($_POST['step'], array('review','bic')) ) {
			self::setup_review( $post_id, $parent_id );
		}

		/**
		 * Update the BIC
		 */
		self::update_bic( $post_id, $parent_id );
	}

	/**
	 * Get the ball in court user
	 *
	 * @author  Jake Snyder
	 * @param   obj|int $post The post object or post id for the post to get ball in court user for
	 * @param   string $field Optional user field or user meta field to be returned
	 * @return	obj|string Either the full user object or a particular field from that user if $field is provided
	 */
	public static function get_bic( $post, $field=false )
	{
		if ( is_numeric($post) ) $post = get_post($post);
		if ( ! is_object($post) ) return false;

		$terms         = wp_get_post_terms( $post->ID, 'em_status' );
		$status        = ( empty($terms[0]) ) ? "draft" : $terms[0]->slug;

		/**
		 * Add reviewer
		 */

		$user = false;

		// If nothing cached in the class settings array for this post
		if ( empty(self::$settings['bic_users'][$post->ID]) || ! ($user = self::$settings['bic_users'][$post->ID]) )
		{
			$bic_user = false;

			$bic_user = get_post_meta($post->ID, 'bic_user', true);

			if ( ! $bic_user ) :

				// Get the latest review
				$latest_review = emanager_post::latest_review($post->ID);
				$review_status = false;
				if ( $latest_review )
				{
					$review_terms  = wp_get_post_terms( $post->ID, 'em_status' );
					$review_status = ( empty($review_terms[0]) ) ? "draft" : $review_terms[0]->slug;
				}
		
				// If we have a review and it is current (compare status to post)
				if ( $review_status == $status )
				{
					$send_to = eman_get_field( 'send_to', $latest_review->ID );
					if ( is_array($send_to) ) {
						$bic_user = $send_to['ID'];
					} elseif ( ! empty($latest_review->send_to) ) {
						$bic_user = $latest_review->send_to;
					}
				}
	
				// If no BIC user from the reviews, look at the older methods
				if ( ! $bic_user )
				{
					if ( in_array($status, array('revise','draft')) )
					{
						$bic_user .= $post->post_author;
					}
					elseif ( 'em_noc' == $post->post_type )
					{
						if ( 'manager' == $status )
						{
							$turner_responsible = eman_get_field( 'turner_responsible', $post->ID );
							$bic_user = $turner_responsible['ID'];
						}
					}
					elseif ( 'em_tickets' == $post->post_type )
					{
						if ( 'superintendent' == $status )
						{
							$turner_responsible = eman_get_field( 'turner_responsible', $post->ID );
							$bic_user = ( ! empty($turner_responsible['ID']) ) ? $turner_responsible['ID'] : false;
						}
					}
				}

			endif;

			if ( is_numeric($bic_user) ) {
				$user = self::$settings['bic_users'][$post->ID] = get_user_by('id', $bic_user);
			}
			elseif ( is_array($bic_user) ) {
				$user = $bic_user;
			}
		}

		if ( ! $user ) return false;

		if ( $field ) {
			if ( 'display_name' == $field ) {
				return eman_users_name($user);
			} elseif ( ! empty($user->$field) ) {
				return $user->$field;
			} elseif ( $output = get_user_meta($user->ID, $field, true) ) {
				return $output;
			}
		}

		return $user;
	}

	/**
	 * Customizing users for "send to:" select
	 *
	 * @return void
	 */
	public static function update_users_bic()
	{
		if ( empty($_POST['group']) ) {
			return false;
		}

		global $wpdb;

		foreach ( (array) $_POST['group'] as $group )
		{
			$args = array(
			    'meta_query' => array(
			        'relation' => 'OR'
			    )
			);

			if ( 'authorizer' == $group )
			{
				$approvers = eman_get_field('pco_approvers', 'option');
				if ( is_array($approvers) )
				{
					foreach ( $approvers as $approver )
					{
						echo '<option value="' . $approver['ID'] . '">' . $approver['display_name'] . '</p>';
					}
				}
			}
			elseif ( 'gatekeeper' == $group )
			{
				$approvers = eman_get_field('noc_gatekeeper', 'option');
				if ( is_array($approvers) )
				{
					foreach ( $approvers as $approver )
					{
						echo '<option value="' . $approver['ID'] . '">' . $approver['display_name'] . '</p>';
					}
				}
			}
			elseif ( 'turner' == $group )
			{
				$args['meta_query'][] = array(
		            'key'     => $wpdb->prefix . 'capabilities',
		            'value'   => '"editor"',
		            'compare' => 'like',
		        );
				$args['meta_query'][] = array(
		            'key'     => $wpdb->prefix . 'capabilities',
		            'value'   => '"administrator"',
		            'compare' => 'like',
		        );
		        $user_query = new WP_User_Query( $args );
		        $remove_array = array('mmemma', 'jglatt', 'jacobsnyder', 'managewp@mangercloud');
				if ( ! empty($user_query->results) )
				{
					foreach ( $user_query->results as $user )
					{
						if ( ! in_array($user->display_name, $remove_array) && ! in_array($user->user_email, $remove_array) ) {
							echo "<option value=\"{$user->ID}\">{$user->display_name}</p>";
						}
					}
				}
				else
				{
					echo 'No users found.';
				}
			}
			elseif ( 'revise' == $group && ! empty($_POST['post_id']) )
			{
				$post = get_post($_POST['post_id']);
				if ( ! empty($post->post_author) )
				{
					$user = get_user_by('id', $post->post_author);
					echo "<option value=\"{$user->ID}\">{$user->display_name}</p>";
				}
				else
				{
					echo 'No users found.';
				}
			}
			elseif ( 'owner' == $group )
			{
				$args['meta_query'][] = array(
		            'key'     => $wpdb->prefix . 'capabilities',
		            'value'   => '"owner"',
		            'compare' => 'like',
		        );
		        $user_query = new WP_User_Query( $args );
				if ( ! empty($user_query->results) )
				{
					foreach ( $user_query->results as $user )
					{
						echo "<option value=\"{$user->ID}\">{$user->display_name}</p>";
					}
				}
			}
			elseif ( 'owners_rep' == $group )
			{
				$args['meta_query'][] = array(
		            'key'     => $wpdb->prefix . 'capabilities',
		            'value'   => '"owners_rep"',
		            'compare' => 'like',
		        );
		        $user_query = new WP_User_Query( $args );
				if ( ! empty($user_query->results) )
				{
					foreach ( $user_query->results as $user )
					{
						echo "<option value=\"{$user->ID}\">{$user->display_name}</p>";
					}
				}
			}
			elseif ( 'consultant' == $group )
			{
				$args['meta_query'][] = array(
		            'key'     => $wpdb->prefix . 'capabilities',
		            'value'   => '"consultant"',
		            'compare' => 'like',
		        );
		        $user_query = new WP_User_Query( $args );
				if ( ! empty($user_query->results) )
				{
					foreach ( $user_query->results as $user )
					{
						echo "<option value=\"{$user->ID}\">{$user->display_name}</p>";
					}
				}
			}
		}

		die;
	}
}

endif;