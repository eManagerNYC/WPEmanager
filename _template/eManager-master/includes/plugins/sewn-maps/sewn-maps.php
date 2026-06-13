<?php
/*
Plugin Name: Sewn In Maps
Plugin URI: 
Description: Create maps and lists of maps for the front end using Advanced Custom Fields.
Version: 1.0.0
Author: Jake Snyder
Author URI: http://Jupitercow.com/

Road map:
* filtering by taxonomy
* change the location grabber to only happen on button similar to starbucks
* add some kind of a "all locations" link that resets things
* blue dot where user is currently
* add ability to turn off auto geocode
* add button to try and get geocode
* probably pagination of the results or something down the road, but not pressing at the moment

------------------------------------------------------------------------
Copyright 2014 Jupitercow, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if (! class_exists('sewn_maps') ) :

add_action( 'init', array('sewn_maps', 'init') );

class sewn_maps
{
	/**
	 * Class prefix
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Current version of plugin
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	const VERSION = '1.0.0';

	/**
	 * Settings
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	public static $settings;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function init()
	{
		// vars
		self::$settings = add_filter( self::PREFIX . '/settings', array(
			'basename'   => plugin_basename( __FILE__ ),
			'path'       => plugin_dir_path( __FILE__ ),
			'dir'        => plugin_dir_url( __FILE__ ),

			'map_class'  => 'acf-locations-map',
			'list_class' => 'acf-locations-list',
			'post_types' => array(
				array(
					'slug' => 'locations',
					'args' => array(
						'labels' => array(
							'name'               => __( "Locations", 'acf_locations' ),
							'singular_name'      => __( "Location", 'acf_locations' ),
							'all_items'          => __( "All Locations", 'acf_locations' ),
							'add_new'            => __( "Add New", 'acf_locations' ),
							'add_new_item'       => __( "Add New Location", 'acf_locations' ),
							'edit'               => __( "Edit", 'acf_locations'  ),
							'edit_item'          => __( "Edit Locations", 'acf_locations' ),
							'new_item'           => __( "New Location", 'acf_locations' ),
							'view_item'          => __( "View Location", 'acf_locations' ),
							'search_items'       => __( "Search Locations", 'acf_locations' ), 
							'not_found'          => __( "Nothing found in the Database.", 'acf_locations' ), 
							'not_found_in_trash' => __( "Nothing found in Trash", 'acf_locations' ),
							'parent_item_colon'  => '',
						),
						'description'         => __( "Locations.", 'acf_locations' ),
						'public'              => true,
						'publicly_queryable'  => true,
						'exclude_from_search' => false,
						'show_ui'             => true,
						'query_var'           => true,
						'rewrite'	          => array( 'slug' => 'location', 'with_front' => false ),
						'has_archive'         => 'locations',
						'capability_type'     => 'post',
						'hierarchical'        => true,
						'supports'            => array( 'title', 'author' ),
					),
				),
			),
			'taxonomies' => array(
				array(
					'slug' => 'location_type',
					'args' => array(
						'labels' => array(
							'name'              => __( "Location Filters", 'acf_locations' ),
							'singular_name'     => __( "Location Filter", 'acf_locations' ),
							'search_items'      => __( "Search Filters", 'acf_locations' ),
							'all_items'         => __( "All Filters", 'acf_locations' ),
							'parent_item'       => __( "Parent Filters", 'acf_locations' ),
							'parent_item_colon' => __( "Parent Filter:", 'acf_locations' ),
							'edit_item'         => __( "Edit Filter", 'acf_locations' ),
							'update_item'       => __( "Update Filter", 'acf_locations' ),
							'add_new_item'      => __( "Add New Filter", 'acf_locations' ),
							'new_item_name'     => __( "New Filter", 'acf_locations' ),
						),
						'hierarchical'      => true,
						'show_admin_column' => true,
						'show_ui'           => true,
						'query_var'         => true,
						'rewrite'           => array( 'slug' => 'locations' ),
					),
				),
			),
			'distances'  => array(5,10,25,50),
			'markers'    => array(
				array(
					'slug'  => 'teardrop',
					'title' => 'Teardrop',
					'url'   => 'http://maps.google.com/mapfiles/ms/micons/red.png',
				),
				array(
					'slug'  => 'pushpin',
					'title' => 'Pushpin',
					'url'   => 'http://maps.google.com/mapfiles/ms/micons/red-pushpin.png',
				),
				array(
					'slug'  => 'arrow',
					'title' => 'Arrow',
					'url'   => 'http://maps.google.com/mapfiles/arrow.png',
				),
				array(
					'slug'  => 'smalldrop',
					'title' => 'Smalldrop',
					'url'   => 'http://labs.google.com/ridefinder/images/mm_20_red.png',
				),
				array(
					'slug'  => 'star',
					'title' => 'Star',
					'url'   => 'http://maps.google.com/mapfiles/kml/pal4/icon47.png',
				),
				array(
					'slug'  => 'house',
					'title' => 'House',
					'url'   => 'http://maps.google.com/mapfiles/ms/micons/homegardenbusiness.png',
				),
				array(
					'slug'  => 'tree',
					'title' => 'Tree',
					'url'   => 'http://maps.google.com/mapfiles/ms/micons/tree.png',
				),
			),
			'marker_user'  => plugin_dir_url( __FILE__ ) . '/assets/images/dot_blue.png',
			'load_spinner' => plugin_dir_url( __FILE__ ) . '/assets/images/loading_bar.gif',
		) );

		// actions
		add_action( self::PREFIX . '/head',                       array(__CLASS__, 'head') );
		add_action( self::PREFIX . '/enqueue_scripts',            array(__CLASS__, 'enqueue_scripts') );
		add_action( self::PREFIX . '/enqueue_styles',             array(__CLASS__, 'enqueue_styles') );
		add_action( self::PREFIX . '/search_field',               array(__CLASS__, 'search_field'), 10, 2 );

		add_action( self::PREFIX . '/map',                        array(__CLASS__, 'create_map'), 10, 2 );
		add_action( self::PREFIX . '/list',                       array(__CLASS__, 'create_list') );
		#add_action( 'wp',                                         array(__CLASS__, 'zip_search') );
		add_action( 'wp_ajax_' . self::PREFIX . '_update',        array(__CLASS__, 'update_locations') );
		add_action( 'wp_ajax_nopriv_' . self::PREFIX . '_update', array(__CLASS__, 'update_locations') );
		add_action( 'get_sidebar',                                array(__CLASS__, 'get_sidebar') );

		// filters
		add_filter( self::PREFIX . '/get_template',               array(__CLASS__, 'get_template') );
		add_filter( 'template_include',                           array(__CLASS__, 'template_include'), 99 );
		add_filter( 'acf/update_value/key=field_535803640df3c',   array(__CLASS__, 'acf_update_value_location'), 10, 3 );

		// Set up custom post types
		self::add_post_types();

		// Set up custom taxonomies
		self::add_taxonomies();

		// Install options
		self::install();

		// Add custom fields for send form
		self::register_field_group();
	}

	public static function get_sidebar( $name )
	{
		if ( 'locations' == $name )
		{
			// Avoid recurrsion: remove itself
			remove_filter( current_filter(), __METHOD__ );

			$filename = "sidebar-{$name}.php";
			$template = array( $filename );
		
			// Backward compat code will be removed in a future release
			if ( '' == locate_template($template, true) && is_file(self::$settings['path'] . 'views/' . $filename) ) {
				load_template( self::$settings['path'] . 'views/' . $filename );
			}
		}
		return $name;
	}

	/**
	 * Save the lat/lng into separate meta so that we can run Haversine proximity search
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string	$value		The value of the field as found in the $_POST object
	 * @param	string	$post_id	The post id to save against
	 * @param	string	$field		The field object (actually an array, not object)
	 * @return	array	$value		The updated value
	 */
	public static function acf_update_value_location( $value, $post_id, $field )
	{
		update_post_meta( $post_id, $field['name'] . '_lat', $value['lat'] );
		update_post_meta( $post_id, $field['name'] . '_lng', $value['lng'] );

		return $value;
	}

	/**
	 * Standardized text input for zip code search
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string	$placeholder	Text to use as placeholder for field
	 * @return	void
	 */
	public static function search_field( $args=array() )
	{
		$defaults = array(
			'placeholder'   => __("ZIP code or city and state", 'acf_locations'),
			'button_text'   => __("Search", 'acf_locations'),
			'button_title'  => __("Search", 'acf_locations'),
			'show_distance' => false,
			'show_locate'   => true,
			'locate_text'   => __("Use your location", 'acf_locations'),
			'locate_title'  => __("Use your location", 'acf_locations'),
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		?>
		<form id="<?php echo self::PREFIX; ?>_form" method="get">
			<input type="text" id="<?php echo self::PREFIX; ?>_search" placeholder="<?php echo $placeholder; ?>" />
			<?php if ( $show_distance ) :
				$unit = ucwords( apply_filters( self::PREFIX . '/defaults/earth_radius', 'miles' ) ); ?>
			<select id="<?php echo self::PREFIX; ?>_distance">
				<?php
				$distances = apply_filters( self::PREFIX . '/defaults/distances', self::$settings['distances'] );
				foreach ( $distances as $distance )
				{
					echo '<option value="' . $distance . '">' . $distance . ' ' . $unit . '</option>';
				}
				?>
			</select>
			<?php endif; ?>
			<button class="button btn" title="<?php echo $button_title; ?>"><?php echo $button_text; ?></button>
			<?php if ( $show_locate ) : ?>
				<a href="#javascript_required" class="<?php echo self::PREFIX; ?>_locate" title="<?php echo $locate_title; ?>"><?php echo $locate_text; ?></a>
			<?php endif; ?>
		</form>
		<?php
	}

	/**
	 * Get plugin templates unless they are overridden in the theme
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	string	$template	Template file path
	 * @return	array	$template
	 */
	public static function template_include( $template )
	{
		$filename = false;

		// archive
		if ( is_post_type_archive(self::$settings['post_types'][0]['slug']) )
		{
			$filename = 'archive-' . self::$settings['post_types'][0]['slug'] . '.php';
		}
		// taxonomy
		elseif ( is_tax(self::$settings['taxonomies'][0]['slug']) )
		{
			$filename = 'taxonomy-' . self::$settings['taxonomies'][0]['slug'] . '.php';
		}
		// single
		elseif ( is_singular(self::$settings['post_types'][0]['slug']) )
		{
			$filename = 'single-' . self::$settings['post_types'][0]['slug'] . '.php';
		}

		if ( $filename )
		{
			do_action( self::PREFIX . '/head' );

			$new_template = locate_template( array($filename) );
			if ( $new_template )
			{
				return $new_template;
			}
			elseif ( is_file(self::$settings['path'] . 'views/' . $filename) )
			{
				return self::$settings['path'] . 'views/' . $filename;
			}
		}

		return $template;
	}

	/**
	 * Create a map from location posts
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	mixed	$post	An array of posts or a single post location to use as markers
	 * @param	array	$args	An array of arguments to customize the map
	 *							@param	mixed	$center string: "fit", array( 'lat' => 0, 'lng' => 0, 'zoom' => 0 ), default: false will use global defaults
	 * @return	void
	 */
	public static function create_map( $posts, $args=array() )
	{
		$defaults = array(
			'center' => false,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		$attrs = '';
		if ( $center )
		{
			$attrs .= " data-center='";
			if ( is_array($center) )
			{
				#$attrs .= "{";
				/** /foreach ( $center as $key => $value )
				{
					#$attrs .= ' data-' . $key . '="' . $value . '"';
					$attrs .= '"' . $key . '":"' . $value . '",';
				}/**/
				#$attrs .= "}";
				$attrs .= json_encode($center);
			}
			elseif ( 'fit' == $center )
			{
				$attrs .= $center;
			}
			$attrs .= "'";
		}

		echo '<div class="' . self::$settings['map_class'] . '"' . $attrs . '>';
		if ( is_array($posts) )
		{
			foreach ( $posts as $post )
			{
				self::marker($post);
			}
		}
		else
		{
			self::marker($posts);
		}
		echo '</div>';
	}

	/**
	 * Create a marker for the map
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	mixed	$post	An array of posts or a single post location to use as markers
	 * @return	void
	 */
	public static function marker( $post )
	{
		$location = get_field('location', $post->ID);

		if (! empty($location) ) :
			$marker = self::get_marker_type($post->ID);
			$title  = get_post_field('post_title', $post->ID);

			$template = apply_filters( self::PREFIX . '/get_template', 'location-marker.php' );
			if ( $template ) include( $template );
		endif;
	}

	/**
	 * Get the marker url for the post
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	mixed	$post	An array of posts or a single post location to use as markers
	 * @return	void
	 */
	public static function get_marker_type( $post_id )
	{
		$marker_selected = get_field('marker', $post_id);
		$markers         = apply_filters( self::PREFIX . '/markers', self::$settings['markers'] );

		if (! $marker_selected )
		{
			$marker_selected = apply_filters( self::PREFIX . '/markers/default', $markers[0]['slug'], $markers );
		}

		foreach ( $markers as $marker )
		{
			if ( $marker_selected == $marker['slug'] ) return $marker['url'];
		}

		return $markers[0]['url'];
	}

	/**
	 * Create a list from location posts
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	mixed	$post	An array of posts or a single post location to use as markers
	 * @return	void
	 */
	public static function create_list( $posts )
	{
		echo '<div class="' . self::$settings['list_class'] . '">';
		if ( is_array($posts) )
		{
			foreach ( $posts as $post )
			{
				self::list_item($post);
			}
		}
		else
		{
			self::list_item($posts);
		}
		echo '</div>';
	}

	/**
	 * Create a map from location posts
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @param	mixed	$post	An array of posts or a single post location to use as markers
	 * @return	void
	 */
	public static function list_item( $post )
	{
		$location = get_field('location', $post->ID);

		if (! empty($location) ) :
			$marker = self::get_marker_type($post->ID);
			$title  = get_post_field('post_title', $post->ID);

			$template = apply_filters( self::PREFIX . '/get_template', 'location-list.php' );
			include( $template );
		endif;
	}

	/**
	 * Load template files for the plugin.
	 *
	 * Tries to load from theme first to allow customization in the theme.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @params	string $filename Name of the template file to load
	 * @return	void
	 */
	public static function get_template( $filename )
	{
		$new_template = locate_template( array( $filename ) );
		if ( $new_template )
		{
			return $new_template;
		}
		return self::$settings['path'] . 'views/' . $filename;
	}

	/**
	 * Install plugin options
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function install()
	{
		if (! get_option( self::PREFIX . '/installed' ) )
		{
			/**
			 * Add default status terms
			 * /
			$taxonomy = 'message_status';
			$terms    = array(
				'read'    => 'Read',
				'archive' => 'Archived',
				'private' => 'Private',
			);
			foreach ( $terms as $key => $term )
			{
				if (! term_exists( $key, $taxonomy ) )
				{
					wp_insert_term(
						$term,
						$taxonomy,
						array( 'slug' => $key )
					);
				}
			}
			/**/

			#update_option( self::PREFIX . '/installed', current_time('timestamp') );
		}
	}

	/**
	 * Search locations
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function search( $latitude, $longitude, $distance=false, $filter=false )
	{
		global $wpdb;

		#$latitude  = '32.2454451';
		#$longitude = '-110.9153907';

		// Earth's radius
		if ( 'kilometers' == strtolower( apply_filters( self::PREFIX . '/defaults/earth_radius', 'miles' ) ) )
		{
			$radius = 6371;
		}
		else
		{
			$radius = 3959;
		}

		// Distance of search radius by default
		if (! is_numeric($distance) ) $distance = apply_filters( self::PREFIX . '/defaults/search_distance', 25 );

		$query = "SELECT p.*,
				( 
					%d 
					* acos( 
						cos( radians(%s) ) 
						* cos( radians( pm1.meta_value ) ) 
						* cos( radians( pm2.meta_value ) - radians(%s) ) 
						+ sin( radians(%s) )
						* sin( radians( pm1.meta_value ) )
					) 
				) AS distance
			FROM $wpdb->posts p  
			LEFT JOIN $wpdb->postmeta pm1 
				ON p.ID = pm1.post_id AND pm1.meta_key = 'location_lat'
			LEFT JOIN $wpdb->postmeta pm2 
				ON p.ID = pm2.post_id AND pm2.meta_key = 'location_lng'
			WHERE p.post_type = '%s'
				AND p.post_status = 'publish'
			HAVING distance < %d
			ORDER BY distance ASC";

		/** / $posts = $wpdb->get_results( $wpdb->prepare(
			$query,
			$radius,
			$latitude,
			$longitude,
			$latitude,
			self::$settings['post_types'][0]['slug'],
			$distance
		) ); /**/

		self::$settings['lat'] = $latitude;
		self::$settings['lng'] = $longitude;
		self::$settings['dist'] = $distance;

		#$fields = apply_filters_ref_array( 'posts_fields',	  array( $fields, &$this ) );
		add_filter( 'posts_fields',  array(__CLASS__, 'search_posts_fields') );
		add_filter( 'posts_where',   array(__CLASS__, 'search_posts_where') );
		add_filter( 'posts_groupby', array(__CLASS__, 'search_posts_groupby') );
		add_filter( 'posts_orderby', array(__CLASS__, 'search_posts_orderby') );

		$args = array(
			'posts_per_page' => -1,
			'post_type'      => self::$settings['post_types'][0]['slug'],
			'meta_query'     => array(
				array(
					'key'        => 'location_lat',
					'value'      => '',
					'compare'    => '!=',
				),
				array(
					'key'        => 'location_lng',
					'value'      => '',
					'compare'    => '!=',
				),
			),
		);

/**/
		if ( $filter )//self::$settings['taxonomies'][0]['slug']
		{
            $args['tax_query'] = array(
                array(
	                'taxonomy'  => self::$settings['taxonomies'][0]['slug'],
	                'field'     => 'slug',
	                'terms'     => $filter,
                ),
            );
		}
/** /
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($args);
echo "</pre>\n";
#exit;
/**/
		$posts = new WP_Query( $args );

/** /
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($posts);
echo "</pre>\n";
#exit;
/**/

		return $posts->posts;
	}

		public static function search_posts_fields( $fields )
		{
			global $wpdb;

			// Earth's radius
			if ( 'kilometers' == strtolower( apply_filters( self::PREFIX . '/defaults/earth_radius', 'miles' ) ) )
			{
				$radius = 6371;
			}
			else
			{
				$radius = 3959;
			}

			$query = ",
					( 
						%d 
						* acos( 
							cos( radians(%s) ) 
							* cos( radians( wp_postmeta.meta_value ) ) 
							* cos( radians( mt1.meta_value ) - radians(%s) ) 
							+ sin( radians(%s) )
							* sin( radians( wp_postmeta.meta_value ) )
						) 
					) AS distance";

			$fields .= $wpdb->prepare(
				$query,
				$radius,
				self::$settings['lat'],
				self::$settings['lng'],
				self::$settings['lat']
			);

			return $fields;
		}

		public static function search_posts_where( $where )
		{
			global $wpdb;

			$where .= $wpdb->prepare( " 
			HAVING distance < %d
			", self::$settings['dist'] );

			return $where;
		}

		public static function search_posts_groupby( $groupby )
		{
			return "";
		}

		public static function search_posts_orderby( $orderby )
		{
			return "distance ASC";
		}

	/**
	 * AJAX update locations from a provided lat/lng
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function update_locations()
	{
		// defaults for options
		$defaults = array(
			'lat'      => '',
			'lng'      => '',
			'distance' => false,
			'filter'   => false,
		);
		$options = array_merge($defaults, $_POST);

		$options['status'] = 0;

		// Make sure lat and lng were provided
		if (! $options['lat'] || ! $options['lng'] )
		{
			echo json_encode($options);
			die;
		}

		$posts = self::search( $options['lat'], $options['lng'], $options['distance'], $options['filter'] );

		#if ( $posts )
		#{
			ob_start();
			self::create_map( $posts );
			$options['new_map']  = ob_get_clean();
			ob_start();
			self::create_list( $posts );
			$options['new_list'] = ob_get_clean();
			$options['status']   = 1;

			$options['posts']    = $posts;
		#}

		echo json_encode($options);
		die;
	}

	/**
	 * Add support for locations
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function head()
	{
		do_action( self::PREFIX . '/enqueue_styles' );
		do_action( self::PREFIX . '/enqueue_scripts' );
	}

	/**
	 * Load styles
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function enqueue_styles()
	{
		wp_register_style( self::PREFIX, self::$settings['dir'] . 'assets/css/public.css', array(), self::VERSION, 'all' );

		wp_enqueue_style( self::PREFIX );
	}

	/**
	 * Load scripts
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function enqueue_scripts()
	{
		wp_register_script( self::PREFIX . '/googleapis', '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array(), self::VERSION, true );
		// modernizr only geolocation
		#wp_register_script( self::PREFIX . '/modernizr', get_stylesheet_directory_uri() . '/assets/js/libs/modernizr.custom.min.js', array(), '2.5.3', false );
		// geoPosition.js normalizes the varied apis of older browsers (IE 6,7,8; Blackberry; Palm OS) with the newer standardized W3c api
		wp_register_script( self::PREFIX . '/geoposition', self::$settings['dir'] . 'assets/js/libs/geoPosition.js', array(), '2.5.3', false );

		$filter = false;
		if ( is_tax(self::$settings['taxonomies'][0]['slug']) )
		{
			$term = get_queried_object();
			$filter = $term->slug;
		}

		wp_register_script( self::PREFIX, self::$settings['dir'] . 'assets/js/public.js', array( 'jquery', self::PREFIX . '/googleapis', self::PREFIX . '/geoposition' ), self::VERSION, true );
			$args = array(
				'spinner'    => admin_url( 'images/spinner.gif' ),
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'map_class'  => self::$settings['map_class'],
				'list_class' => self::$settings['list_class'],
				'actions'    => array(
					'search'     => 'acf_locations_update',
				),
				'strings'    => array(
					'search_error' => __("Unable to parse that address", 'acf_locations'),
					'search_empty' => __("No nearby results found", 'acf_locations'),
				),
				'defaults'   => array(
					'center_lat'   => false,
					'center_lng'   => false,
					'zoom'         => 11,
					'single_zoom'  => 16,
					'marker_user'  => self::$settings['marker_user'],
					'load_spinner' => self::$settings['load_spinner'],
					'load_position_at_start' => true,
				),
				'filter'     => $filter,
			);
			$args['defaults'] = apply_filters( self::PREFIX . '/map/defaults', $args['defaults'] );
			wp_localize_script( self::PREFIX, self::PREFIX, $args );

		wp_enqueue_script( self::PREFIX );
	}

	/**
	 * Add the custom post types used by application
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function add_post_types()
	{
		if ( self::$settings['post_types'] ) : foreach ( self::$settings['post_types'] as $post_type ) :

			$post_type = apply_filters( self::PREFIX . '/post_type', $post_type );
			register_post_type( $post_type['slug'], $post_type['args'] );

		endforeach; endif;
	}

	/**
	 * Add the custom post types used by application
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function add_taxonomies()
	{
		if ( self::$settings['taxonomies'] ) : foreach ( self::$settings['taxonomies'] as $taxonomy ) :

			$taxonomy = apply_filters( self::PREFIX . '/taxonomy', $taxonomy );
			register_taxonomy( $taxonomy['slug'], array( self::$settings['post_types'][0]['slug'] ), $taxonomy['args'] );

		endforeach; endif;
	}

	/**
	 * Register a field group for the messenger form
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function register_field_group()
	{
		if ( function_exists("register_field_group") )
		{
			$args = array (
				'id' => 'acf_location',
				'title' => 'Location',
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => self::$settings['post_types'][0]['slug'],
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'normal',
					'layout' => apply_filters(self::PREFIX . '/field_group/layout', (is_admin() ? 'default' : 'no_box')),
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 0,
			);

			$marker_choices = array();
			$markers = apply_filters(self::PREFIX . '/markers', self::$settings['markers']);
			foreach ( $markers as $marker )
			{
				$marker_choices[$marker['slug']] = $marker['title'];
			}

			$args['fields'] = array (
				array (
					'key' => 'field_535802a90df37',
					'label' => apply_filters(self::PREFIX . '/fields/address/label', 'Address'),
					'name' => 'address',
					'type' => 'text',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'formatting' => 'html',
					'maxlength' => '',
				),
				array (
					'key' => 'field_535802d40df38',
					'label' => apply_filters(self::PREFIX . '/fields/phone/label', 'Phone'),
					'name' => 'phone',
					'type' => 'text',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'formatting' => 'html',
					'maxlength' => '',
				),
				array (
					'key' => 'field_535802e20df39',
					'label' => apply_filters(self::PREFIX . '/fields/contact/label', 'Contact Person'),
					'name' => 'contact',
					'type' => 'text',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'formatting' => 'html',
					'maxlength' => '',
				),
				array (
					'key' => 'field_535802f70df3a',
					'label' => apply_filters(self::PREFIX . '/fields/website/label', 'Website'),
					'name' => 'website',
					'type' => 'text',
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'formatting' => 'html',
					'maxlength' => '',
				),
				array (
					'key' => 'field_535803090df3b',
					'label' => apply_filters(self::PREFIX . '/fields/marker/label', 'Marker'),
					'name' => 'marker',
					'type' => 'select',
					'choices' => $marker_choices,
					'default_value' => apply_filters(self::PREFIX . '/markers/default', $markers[0], $markers ),
					'allow_null' => 1,
					'multiple' => 0,
				),
				array (
					'key' => 'field_535803640df3c',
					'label' => apply_filters(self::PREFIX . '/fields/location/label', 'Location'),
					'name' => 'location',
					'type' => 'google_map',
					'center_lat' => apply_filters(self::PREFIX . '/fields/location/center_lat', ''),
					'center_lng' => apply_filters(self::PREFIX . '/fields/location/center_lng', ''),
					'zoom' => apply_filters(self::PREFIX . '/fields/location/zoom', ''),
					'height' => apply_filters(self::PREFIX . '/fields/location/height', ''),
				),
			);

			$args['fields'] = apply_filters(self::PREFIX . '/fields', $args['fields']);

			register_field_group( $args );
		}

	}
}

endif;