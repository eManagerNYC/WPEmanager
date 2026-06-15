<?php
/**
 * Email notifications.
 *
 * When a record advances through its workflow, the people who care are emailed:
 * the record's owner, and whoever now holds the "ball in court" (the party roles
 * allowed to act on the new status). Delivery is opt-out per user and can be
 * disabled globally. This turns the workflow engine into something that actively
 * pushes the next action to the right person — the patent's "up-to-date
 * notifications and status reports to all parties."
 *
 * @package eManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sends workflow notification emails.
 */
class EM_Notify {

	/**
	 * Hook into workflow events.
	 */
	public static function init() {
		if ( ! self::enabled() ) {
			return;
		}
		add_action( 'em_record_transitioned', array( __CLASS__, 'on_transition' ), 10, 3 );
	}

	/**
	 * Are notifications enabled site-wide?
	 *
	 * @return bool
	 */
	public static function enabled() {
		return '0' !== (string) get_option( 'em_notifications', '1' );
	}

	/**
	 * Email recipients after a workflow transition.
	 *
	 * @param array $record     Updated record.
	 * @param array $module     Module definition.
	 * @param array $transition The transition just performed.
	 */
	public static function on_transition( $record, $module, $transition ) {
		unset( $transition );
		$recipients = self::recipients_for( $record, $module );
		if ( empty( $recipients ) ) {
			return;
		}

		$project      = get_option( 'em_project', array() );
		$project_name = $project['name'] ?? get_bloginfo( 'name' );
		$status       = $record['status'] ?? '';
		$title        = self::record_title( $module, $record );

		$dashboard = get_permalink( (int) get_option( 'em_page_dashboard' ) );
		$link      = $dashboard ? $dashboard . '#/' . $module['section'] . '/' . $module['id'] . '/view/' . $record['id'] : '';

		$actor = wp_get_current_user();

		/* translators: 1: project name, 2: module name, 3: status */
		$subject = sprintf( __( '[%1$s] %2$s: %3$s', 'emanager' ), $project_name, $module['name'], $status );

		foreach ( $recipients as $user ) {
			$lines = array(
				/* translators: %s: module name */
				sprintf( __( 'A %s record was updated.', 'emanager' ), $module['name'] ),
				'',
				/* translators: %s: record title */
				sprintf( __( 'Record: %s', 'emanager' ), $title ),
				/* translators: %s: status */
				sprintf( __( 'Status: %s', 'emanager' ), $status ),
				/* translators: %s: user display name */
				sprintf( __( 'Updated by: %s', 'emanager' ), $actor->display_name ),
			);
			if ( $link ) {
				$lines[] = '';
				$lines[] = __( 'View:', 'emanager' ) . ' ' . $link;
			}

			$message = implode( "\n", $lines );

			/**
			 * Filter the notification email body.
			 *
			 * @param string  $message Email body.
			 * @param array   $record  Record.
			 * @param array   $module  Module definition.
			 * @param WP_User $user    Recipient.
			 */
			$message = apply_filters( 'em_notify_message', $message, $record, $module, $user );

			wp_mail( $user->user_email, $subject, $message );
		}
	}

	/**
	 * Resolve the users to notify: the record owner plus ball-in-court party
	 * holders for the new status, minus the acting user and anyone opted out.
	 *
	 * @param array $record Record.
	 * @param array $module Module definition.
	 * @return WP_User[] Keyed by user id.
	 */
	public static function recipients_for( $record, $module ) {
		$actor = get_current_user_id();
		$ids   = array();

		if ( ! empty( $record['created_by'] ) ) {
			$ids[] = (int) $record['created_by'];
		}

		// Parties that can act on the new status = the ball-in-court for next step.
		$parties = array();
		foreach ( EM_Workflow::transitions_from( $module, $record['status'] ?? '' ) as $transition ) {
			foreach ( (array) ( $transition['party'] ?? array() ) as $party ) {
				$parties[ $party ] = true;
			}
		}
		if ( $parties ) {
			$party_users = get_users(
				array(
					'number'     => 25,
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- bounded admin-scale lookup of party-role holders.
						array(
							'key'     => 'em_party_role',
							'value'   => array_keys( $parties ),
							'compare' => 'IN',
						),
					),
				)
			);
			foreach ( $party_users as $user ) {
				$ids[] = $user->ID;
			}
		}

		$out = array();
		foreach ( array_unique( array_filter( $ids ) ) as $id ) {
			if ( (int) $id === (int) $actor ) {
				continue; // Don't notify the person who made the change.
			}
			if ( '0' === (string) get_user_meta( $id, 'em_email_notify', true ) ) {
				continue; // Opted out.
			}
			$user = get_user_by( 'id', $id );
			if ( $user && is_email( $user->user_email ) && user_can( $user, 'em_read' ) ) {
				$out[ $id ] = $user;
			}
		}

		/**
		 * Filter the notification recipients.
		 *
		 * @param WP_User[] $out    Recipients keyed by id.
		 * @param array     $record Record.
		 * @param array     $module Module definition.
		 */
		return apply_filters( 'em_notify_recipients', $out, $record, $module );
	}

	/**
	 * Display title for a record (title/subject/name field, else first field).
	 *
	 * @param array $module Module definition.
	 * @param array $record Record.
	 * @return string
	 */
	private static function record_title( $module, $record ) {
		foreach ( array( 'title', 'subject', 'name' ) as $key ) {
			if ( ! empty( $record[ $key ] ) ) {
				return wp_strip_all_tags( (string) $record[ $key ] );
			}
		}
		$first = $module['fields'][0]['name'] ?? '';
		return $first && ! empty( $record[ $first ] ) ? wp_strip_all_tags( (string) $record[ $first ] ) : ( '#' . ( $record['id'] ?? '' ) );
	}
}
