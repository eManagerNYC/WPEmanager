<?php

/**
 * Add the search index
 */
add_action( 'emanager/install/search_index', 'eman_install_search_index' );
function eman_install_search_index()
{
	global $wpdb;

	$charset_collate = '';
	if ( ! empty($wpdb->charset) ) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty($wpdb->collate) ) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}

	$table        = "{$wpdb->prefix}meta_index";
	$success      = false;
	$table_exists = ( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ) ? false : true;
	if ( ! $table_exists ) 
	{
		$sql = "CREATE TABLE `$table` (
			`post_id` INT(10) NOT NULL,
			`meta_values` text,
			PRIMARY KEY (`post_id`),
			FULLTEXT KEY `meta_values` (`meta_values`)
		) ENGINE = MYISAM; {$charset_collate};";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$success = dbDelta( $sql );
	}

	if ( $success || $table_exists )
	{
		$all_posts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts");

		foreach ( $all_posts as $all_post ) {
			// Fix pco/noc numbers into meta
			#$noc_numbers = eman_noc_numbers( $all_post );
			#if ( ! empty($noc_numbers['noc']) ) update_post_meta($all_post->ID, 'noc_number', $noc_numbers['noc']);
			#if ( ! empty($noc_numbers['pco']) ) update_post_meta($all_post->ID, 'pco_number', $noc_numbers['pco']);

			// update meta index table
			emanager_table_filters::update_meta_index( $all_post->ID );
		}

		add_option( 'emanager_install_index2', current_time('timestamp') );
	}
}
