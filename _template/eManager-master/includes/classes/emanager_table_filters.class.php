<?php
/*
Plugin Name: eManager table filter
Description: Used to control the table filters as needed
Version: 1.0.0
Author: Jake Snyder
*/

if ( ! class_exists('emanager_table_filters') ) :

if ( ! is_admin() ) {
	add_action( 'init', array( 'emanager_table_filters', 'init' ) );
}

class emanager_table_filters
{
	/**
	 * Class prefix
	 *
	 * @since 	1.0.0
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
	 * Manage post type and its settings
	 *
	 * @var 	string
	 */
	public static $post_type;
	public static $post_type_object;
	public static $post_type_settings;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function init()
	{
		add_action( 'pre_get_posts',             array(__CLASS__, 'filter_pre_get_search') );
		add_filter( 'posts_clauses',             array(__CLASS__, 'orderby_status'), 10, 2 );
		add_filter( 'posts_clauses',             array(__CLASS__, 'search_meta_index'), 10, 2 );
		add_action( self::PREFIX . '/filters',   array(__CLASS__, 'create_filters') );

		// filters
		add_filter( self::PREFIX . '/sort_url',  array(__CLASS__, 'sort_url') );

		#add_action( 'save_post',                 array(__CLASS__, 'update_meta_index') );
		add_action( 'acf/save_post',             array(__CLASS__, 'update_meta_index'), 99);
		#add_action( 'updated_postmeta',          array(__CLASS__, 'meta_update_meta_index'), 10, 4 );
	}

	/**
	 * Set up the filters for a table
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function create_filters( $args=false )
	{
		$defaults = array(
			'post_type' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $post_type ) {
			$post_type = get_post_type();
		}
		if ( empty($post_type) ) {
			return false;
		}
		$post_type_object   = get_post_type_object( $post_type );
		$post_type_settings = eman_post_types( $post_type_object );

		// Get the filters
		$filter_type = $post_type;
		if ( get_query_var('pco') ) {
			$filter_type .= '_pco';
		}
		$filters = eman_get_filters( $filter_type );

		// Get search term if one exists
		$search_term = false;
        if ( ! empty($_GET['filter-search']) ) {
            $search_term = $_GET['filter-search'];
        }

		$current_url = get_post_type_archive_link( $post_type );
		if ( get_query_var('pco') ) {
			$current_url .= 'pco/';
		}

		require_once( get_template_directory() . '/partials/archive/search-filters.php' );
	}

	/**
	 * Get submitted filters from url
	 *
	 * @author  Jake Snyder
	 * @return	$order_type slug of column to orderby.
	 */
	public static function get_url_filters()
	{
		$filters = array();

		if ( ! empty($_GET) )
		{
			foreach ( $_GET as $key => $value )
			{
				if ( 0 === strpos($key, 'filter-') )
				{
					$filters[$key] = $value;
				}
			}
		}

		return $filters;
	}

	/**
	 * Build sort link
	 *
	 * @author  Jake Snyder
	 * @return	$order_type slug of column to orderby.
	 */
	public static function sort_url( $order_type )
	{
		if ( ! $order_type ) return false;

		$url  = '?sort=' . $order_type;
		$url .= '&order=' . ( ! empty($_GET['order']) && 'asc' == $_GET['order'] ? 'desc' : 'asc');

		if ( $filters = self::get_url_filters() )
		{
			foreach ( $filters as $key => $value )
			{
				$url .= '&' . esc_attr($key) . '=' . esc_attr($value);
			}
		}

		return $url;
	}

	/**
	 * Use submitted filters to search posts
	 *
	 * @author  Jake Snyder
	 * @return	$obj Filtered posts.
	 */
	public static function get_filtered_posts( $args=false )
	{
		$defaults = array(
			'post_type' => false
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $post_type ) {
			$post_type = get_post_type();
		}
	}

//add_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );

	/**
	 * Maintain a meta index to aid in searching
	 *
	 * @author  Jake Snyder
	 * @return	$obj Filtered posts.
	 */
	public static function meta_update_meta_index( $meta_id, $object_id, $meta_key, $meta_value )
	{
		self::update_meta_index($object_id);
	}

	/**
	 * Maintain a meta index to aid in searching
	 *
	 * @author  Jake Snyder
	 * @return	$post_id Filtered posts.
	 */
	public static function update_meta_index( $post_id )
	{
		global $wpdb;

		$table = "{$wpdb->prefix}meta_index";

		if ( ! is_numeric($post_id) ) { return; }

		$post_type = get_post_type($post_id);
		if ( 'em_reviews' == $post_type ) {
			$post_id = ( isset($GLOBALS['post']->ID) ) ? $GLOBALS['post']->ID : 0;
		}

		// Grabs an already concatenated list of meta values, comma separated.
		// Leaves out an entries whose key start with "_"
		$all_meta = $wpdb->get_var("SELECT GROUP_CONCAT(meta_value) FROM {$wpdb->prefix}postmeta WHERE `post_id`={$post_id} AND `meta_key` NOT RegExp '(^[_0-9].+$)' AND `meta_value`!='' AND `meta_value`!='null' AND `meta_key`NOT LIKE'%signature%'");

		$users = $wpdb->get_results("SELECT p.post_author, pm.meta_value FROM {$wpdb->prefix}posts AS p INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id WHERE p.ID={$post_id} AND pm.meta_key='bic_user'");
		if ( ! empty($users[0]) )
		{
			if ( ! empty($users[0]->post_author) ) {
				$name = eman_users_name( $users[0]->post_author );
				if ( $name ) {
					$all_meta .= ",$name";
				}
			}
			if ( ! empty($users[0]->meta_value) ) {
				$name = eman_users_name( $users[0]->meta_value );
				if ( $name ) {
					$all_meta .= ",$name";
				}
			}
		}

		$table = "{$wpdb->prefix}meta_index";

		// If we got anything, add it to the custom index table for searching.
		if ( $all_meta && $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table ) {
			$sql = "INSERT INTO $table (`post_id`,`meta_values`) VALUES (%d,'%s') ON DUPLICATE KEY UPDATE `meta_values` = '%s'";
			$wpdb->query( $wpdb->prepare( $sql, $post_id, $all_meta, $all_meta ) );
		}
	}

	public static function search_meta_index( $clauses, $wp_query )
	{
		if ( ! is_admin() && $wp_query->is_main_query() && ! empty($_GET['filter-search']) )
		{
			global $wpdb;

			$table = "{$wpdb->prefix}meta_index";
			if ( $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table )
			{
				$meta_query       = "MATCH(mi.meta_values) AGAINST('{$_GET['filter-search']}')";
				$search_meta      = "OR ($meta_query)";
				$clauses['where'] = str_replace( ")))", ") ".$search_meta."))", $clauses['where'] );

				$clauses['join'] .= " INNER JOIN $table AS mi ON ({$wpdb->prefix}posts.ID = mi.post_id)";
			}
		}

		return $clauses;
	}

	public static function orderby_status( $clauses, $wp_query )
	{
		if ( is_admin() || ! is_main_query() ) {
			return;
		}

		if ( 'status' == $wp_query->get('orderby') )
		{
			global $wpdb;

			$clauses['join'] .= <<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;

			$clauses['where']   .= " AND (taxonomy = 'em_status' OR taxonomy IS NULL)";
			$clauses['groupby']  = "object_id";
			$clauses['orderby']  = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
			$clauses['orderby'] .= ( 'ASC' == strtoupper($wp_query->get('order')) ) ? 'ASC' : 'DESC';
		}

		return $clauses;
	}

	/**
	 * Used to search through pre_get_posts
	 *
	 * Too much is going on in the filtering and this is going to need to be abandoned for a more custom approach
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function filter_pre_get_search( $query )
	{
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$tax_query = $meta_query = array();

		// Limit non-turner users to their own comapny
		if ( is_archive() && ! eman_check_role('turner') && ! eman_check_role('owner') )
		{
			$user_id = get_current_user_id();
			$current_company = get_user_meta($user_id, 'company', true);

			$meta_query[] = array(
	            'key'     => 'company',
	            'value'   => $current_company,
	            'type'    => 'numeric',
			);
		}

		// Only show published posts on the front end to simplify the queries (removes private/draft search)
		$query->set( 'post_status', 'publish' );

		$number_items = array('pco_number','noc_number','noc_total','value','letter_number');
		if ( ! empty($_GET['sort']) )
		{
			switch ( $_GET['sort'] )
			{
				case 'requester' :
				case 'createdby' :
					$query->set( 'orderby', 'author' );
					break;
				case 'bic_company' :
					break;
				case 'date_modified' :
					$query->set( 'orderby', 'modified' );
					break;
/** /
					$review = emanager_post::latest_review( $post );
					if ( $review ) {
						$value = $review->post_modified;
					} else {
						$value = $post->post_modified;
					}
					$output = ( $value ) ? date_i18n('m/d/Y', strtotime($value)) : '';
/**/
				case 'title' :
					$query->set( 'orderby', 'title' );
					break;
				case 'status' :
					$query->set( 'orderby', 'status' ); // uses orderby_status method above
					break;
				#case 'noc_total' : // not supported yet
					#break;
				default :
					$column = ( 'reviewer' == $_GET['sort'] ) ? 'bic_user' : esc_sql($_GET['sort']);
					$query->set( 'meta_key', $column );

					$orderby = 'meta_value';
					if ( in_array($column, $number_items) || false !== strpos($column, 'number') ) {
						$orderby = 'meta_value_num';
					} elseif ( false !== strpos($_GET['sort'], 'date') ) {
						$orderby = 'meta_value_datetime';
					}

					$query->set( 'orderby', $orderby );
					break;
			}
		}

		if ( ! empty($_GET['order']) ) {
			$query->set( 'order', $_GET['order'] );
		}

		/**
		 * FILTER
		 */
		if ( $filters = self::get_url_filters() )
		{
			$post_type = get_query_var('post_type');
			if ( 'em_noc' == $post_type && get_query_var('pco') ) {
				$post_type = 'noc_pco';
			}

			/**
			 * SEARCH
			 */
			$filter_name = 'filter-search';
			if ( isset($filters[$filter_name]) )
			{
				if ( ! empty($filters[$filter_name]) && 'null' != $filters[$filter_name] )
				{
					$term = esc_sql( $filters[$filter_name] );
			    	$query->set( 's', $term );
/** /
					switch ( $post_type )
					{
						case 'em_noc' :
							$meta_query[] = array(
					            'key'     => 'noc_number',
					            'value'   => $term, 
					            'compare' => 'LIKE'
							);
							$meta_query[] = array(
					            'key'     => 'pco_number',
					            'value'   => $term, 
					            'compare' => 'LIKE'
							);
							break;
						case 'noc_pco' :
							break;
						case 'em_dcr' :
							break;
						case 'em_tickets' :
							break;
					}
/**/
		    	}
				unset($filters[$filter_name]);
			}
				/* Search all table cols, this is... intense * /
				$meta_query = array( 'relation' => 'OR' );
				if ( ! empty(self::$post_type_settings['table_cols']) ) : foreach ( self::$post_type_settings['table_cols'] as $key => $value ) :
					$meta_query[] = array(
			            'key'     => $key,
			            'value'   => $term, 
			            'compare' => 'LIKE'
					);
				endforeach; endif;
		    	$query->set( 'meta_query', $meta_query );
				/**/


			/**
			 * FILTER: CHARGE (taxonomy)
			 */
			$filter_name = 'filter-charge';
			if ( isset($filters[$filter_name]) )
			{
				if ( ! empty($filters[$filter_name]) && 'null' != $filters[$filter_name] )
				{
					$tax_query[] = array(
						'taxonomy' => 'em_charge',
						'field'    => 'slug',
						'terms'    => $filters[$filter_name],
					);
				}
				unset($filters[$filter_name]);
			}




			/**
			 * FILTER: STATUS (taxonomy)
			 */
			$filter_name = 'filter-status';
			if ( isset($filters[$filter_name]) )
			{
				if ( ! empty($filters[$filter_name]) && 'null' != $filters[$filter_name] )
				{
					$tax_query[] = array(
						'taxonomy' => 'em_status',
						'field'    => 'slug',
						'terms'    => $filters[$filter_name],
					);
				}
				unset($filters[$filter_name]);
			} else {
				$tax_query[] = array(
					'taxonomy' => 'em_status',
					'field'    => 'slug',
					'terms'    => ['approved','executed','void'],
					'operator' => 'NOT IN',
				);
			}

			/**
			 * FILTER: REQUESTER (post author)
			 */
			$filter_name = 'filter-requester';
			if ( isset($filters[$filter_name]) )
			{
				if ( ! empty($filters[$filter_name]) && 'null' != $filters[$filter_name] ) {
					$query->set( 'author', $filters[$filter_name] );
				}
				unset($filters[$filter_name]);
			}


			/**
			 * FILTER: META FIELDS (the rest)
			 *
			 * Consider customizing these when you add a new filter to make sure they are at most efficient in database
			 * EG: Don't use "LIKE" unless you have to...
			 */

			$numeric_meta = array('bic_user', 'company');

			if ($filters) foreach ( $filters as $key => $value )
			{
				if ( 0 !== strpos($key, 'filter-') ) {
					continue;
				}
				if ( ! $value || 'null' == $value ) {
					continue;
				}

				$meta_key = str_replace('filter-','',$key);
				$args = array(
		            'key'     => $meta_key,
		            'value'   => $value
				);
				if ( in_array($meta_key, $numeric_meta) ) {
					$args['type'] = 'numeric';
				} else {
					$args['compare'] = 'LIKE';
				}
				$meta_query[] = $args;
			}
		} elseif ( 'em_noc' != get_query_var('post_type') ) {
			$tax_query[] = array(
				'taxonomy' => 'em_status',
				'field'    => 'slug',
				'terms'    => ['approved','executed','void'],
				'operator' => 'NOT IN',
			);
		}


		if ( $tax_query ) {
			$query->set( 'tax_query', $tax_query );
		}
		if ( $meta_query )
		{
			if ( 1 < count($meta_query) ) {
				//$meta_query['relation'] = 'OR';
			}
			$query->set( 'meta_query', $meta_query );
		}

	}
}

endif;
