<?php


if ( ! class_exists('emanager_file_manager') ) :

add_action( 'init', array('emanager_file_manager', 'init') );

class emanager_file_manager
{
	/**
	 * Class prefix
	 *
	 * @since 	0.1
	 * @var 	string
	 */
	public static $prefix = __CLASS__;

	/**
	 * Settings
	 *
	 * @since 	0.1
	 * @var 	string
	 */
	public static $settings;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function init()
	{
		// Set up settings
		self::$settings = array(
			'directories_failed' => array(),
			'nonce_key' => self::$prefix . '/reinstall',
			'pages'    => array(
				'documents' => array(
					'page_name'  => 'documents',
					'page_title' => 'Project Documents'
				)
			)
		);

		// Add fake posts for file manager pages
		add_filter( 'the_posts',                  array(__CLASS__, 'add_post') );

		// Add manager to page
		add_filter( 'the_content',                array(__CLASS__, 'add_manager') );

		// Add the connector
		#add_action( 'wp',                         array(__CLASS__, 'connector') );
		add_action( 'wp_ajax_elfinder_connector', array(__CLASS__, 'connector') );

		// Add an options page for reinstallation
		add_action( 'admin_menu',                 array(__CLASS__, 'admin_menu') );

		// Redirect documents when user doesn't have permission or doesn't exist
		add_action( 'template_redirect',          array(__CLASS__, 'template_redirect') );

		// rewrite everything documents/* to documents page
		self::rewrites();

		// Add the connector
		add_action( self::$prefix . '/install',          array(__CLASS__, 'install_once') );

		// Create directory for uploads
		add_action( self::$prefix . '/create_directory', array(__CLASS__, 'create_directory') );

		// Rename directory for uploads
		add_action( self::$prefix . '/rename_directory', array(__CLASS__, 'rename_directory'), 10, 2 );
	}

	/**
	 * Add a options page to reinstall
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function admin_menu()
	{
		$page_title = "File Manager";
		$menu_title = "File Manager";
		$capability = 'manage_options';
		$menu_slug  = self::$prefix;
		$function   = array(__CLASS__, 'settings_page');
		self::$settings['options_page'] = add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		add_action( 'load-' . self::$settings['options_page'], array(__CLASS__, 'settings_head') );
	}

	/**
	 * Process the install request
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function settings_head()
	{
		if ( ! empty($_REQUEST['reinstall']) && ! empty($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], self::$settings['nonce_key']) )
		{
			self::install();
		}
	}

	/**
	 * Settings page for reinstalling
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function settings_page()
	{
		ob_start(); ?>
		<h1>Install File Manager</h1><br />
		<p class="instructions">If there was a problem with the permissions or, for some other reason, File Manager didn't install correctly, reinstall here. This will just make sure that folders exist, it will not overwrite existing.</p>
		<p class="instructions">Usually installation errors are because the permissions of the uploads folder are incorrect.</p>

		<?php
		$args = array(
			'reinstall' => '1',
			'nonce'     => wp_create_nonce(self::$settings['nonce_key'])
		); ?>
		<a href="<?php echo add_query_arg( $args ); ?>"> REINSTALL </a>
		<?php echo ob_get_clean();
	}

	/**
	 * Run the plugin install once, only if the option isn't set and set the option
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function install_once()
	{
		if ( get_option( self::$prefix . '/install' ) ) return true;

		self::install();
	}

	/**
	 * Install the plugin, sets the install option for future as well
	 *
	 * Adds folders to the uploads directory
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function install()
	{
		$roles      = "Roles/";
		$consultant = $roles . "Consultant/";
		$owner      = $roles . "Owner/";
		$sub        = $roles . "Sub/";
		$turner     = $roles . "Turner/";
		$project    = "Project/";
		$company    = "Companies/";

		// Add role folders
		$folders = array(
			// Roles
			$consultant . 'Submittals',
			$consultant . 'Submittals/Sent',
			$consultant . 'Submittals/Returned',
			$consultant . 'Meeting Minutes',
			$consultant . 'Closeout',
			$owner . 'Reports',
			$owner . 'Requisitions',
			$owner . 'CORs',
			$owner . 'ALs',
			$sub . 'Schedules',
			$sub . 'Safety',
			$sub . 'Coordination',
			$sub . 'Minutes',
			$turner . 'Schedule Logistics',
			$turner . 'Contracts-PSAs-POs',
			$turner . 'Reports',
			$turner . 'Insurance Bonding',
			$turner . 'Safety',
			$turner . 'Field',
			$turner . 'Preconstruction',
			// Project
			$project . 'Schedules',
			$project . 'Project Manual',
			$project . 'Blended Set',
			$project . 'Sketches'
		);
		foreach ( $folders as $folder )
		{
			do_action( self::$prefix . '/create_directory', $folder );
		}

		// Add companies directories
		$companies = new WP_Query( array(
			'post_type' => 'em_companies',
			'posts_per_page' => -1
		) );
		if ( $companies->have_posts() ) : while ( $companies->have_posts() ) : $companies->the_post();
			do_action( 'emanager_file_manager/create_directory', $company . $companies->post->post_title );
		endwhile; endif;

		flush_rewrite_rules();
		add_option( self::$prefix . '/install', current_time('timestamp') );
	}

	/**
	 * Creates directory in uplaods
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function create_directory( $directory )
	{
		if ( ! $directory ) return;

		$empty_contents = '<?php // Silence is golden.';
		$filename       = 'index.php';

		$status         = false;
		$upload_dir     = wp_upload_dir();
		$directories    = explode('/', $directory);

		$newdir         = $upload_dir['basedir'] . '/';
		foreach ( $directories as $directory )
		{
			if ( $directory )
			{
				$newdir .= $directory . '/';
				$status = ( ! is_dir($newdir) ) ? mkdir($newdir, 0777) : true;
				file_put_contents( $newdir . $filename, $empty_contents );

				if ( ! $status && ! in_array($directory, self::$settings['directories_failed']) )
				{
					self::add_notice( $directory );
					self::$settings['directories_failed'][] = $directory;
				}
			}
		}
	}

	/**
	 * Rename directory in uplaods
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function rename_directory( $old_name, $new_name )
	{
		if ( ! $old_name || ! $new_name ) return;

		$status         = true;
		$upload_dir     = wp_upload_dir();

		$olddir         = $upload_dir['basedir'] . '/' . $old_name;
		$newdir         = $upload_dir['basedir'] . '/' . $new_name;

		if ( ! is_dir($newdir) )
		{
			$status = rename($olddir, $newdir);
		}

		if ( ! $status ) self::add_notice( $new_name );
	}

	/**
	 * Add notices that install didn't work
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function add_notice( $folder )
	{
		if ( ! $folder ) return;

		$message = "The directory was not created correctly: $folder";
		if ( ! is_admin() )
		{
			if ( class_exists('Sewn_Notifications') ) do_action( 'sewn/notifications/add', $message );
		}
		else
		{
			add_action( 'admin_notices', function ( $message ) use ( $message ) { echo '<div class="error"><p>' . $message . '</p></div>'; } );
		}
	}

	/**
	 * Add an admin notice that install didn't work properly
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function install_admin_notice()
	{
		echo '<div class="updated"><p>' . self::$settings['message'] . '</p></div>';
	}

	/**
	 * Add fake posts for file manager pages
	 */
	public static function add_post( $posts )
	{
		global $wp, $wp_query;

		$request = ( get_query_var('pagename') ) ? get_query_var('pagename') : strtolower($wp->request);

		// Check if the requested page matches our target, and no posts have been retrieved
		if ( ! $posts && array_key_exists($request, self::$settings['pages']) )
		{
			// Add the fake post
			$posts   = array();
			$posts[] = self::create_post( $request );

			$wp_query->is_page     = true;
			$wp_query->is_singular = true;
			$wp_query->is_home     = false;
			$wp_query->is_archive  = false;
			$wp_query->is_category = false;
			//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
			unset($wp_query->query["error"]);
			$wp_query->query_vars["error"]="";
			$wp_query->is_404=false;
		}
		return $posts;
	}

	/**
	 * Create a dynamic post on-the-fly for the register page.
	 *
	 * source: http://scott.sherrillmix.com/blog/blogger/creating-a-better-fake-post-with-a-wordpress-plugin/
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	object $post Dynamically created post
	 */
	public static function create_post( $type )
	{
		$defaults = array(
			'ID'                    => -1,
			'post_author'           => 1,
			'post_date'             => current_time('mysql'),
			'post_date_gmt'         => current_time('mysql', 1),
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_status'           => '',
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'post_password'         => '',
			'post_name'             => '',
			'to_ping'               => '',
			'pinged'                => '',
			'post_modified'         => current_time('mysql'),
			'post_modified_gmt'     => current_time('mysql', 1),
			'post_content_filtered' => '',
			'post_parent'           => 0,
			'guid'                  => 0,
			'menu_order'            => 0,
			'post_type'             => 'page',
			'post_mime_type'        => '',
			'comment_count'         => 0,
			'filter'                => 'raw'
		);
		$settings = array_merge($defaults, self::$settings['pages'][$type]);
		if ( ! $settings['guid'] ) $settings['guid'] = home_url('/' . $settings['page_name'] . '/');

		// Create a fake post.
		$post = new stdClass();
		foreach ( $settings as $key => $setting )
		{
			$post->$key = $setting;
		}

		return $post;   
	}

	/**
	 * Rewrite document subpages to documents page
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function rewrites()
	{
		add_rewrite_rule(
			"documents/?([^/]*)?/?([^/]*)?/?$",
			'index.php?pagename=documents&doc1=$matches[1]&doc2=$matches[2]',
			'top'
		);

		add_filter( 'query_vars', array(__CLASS__, 'register_query_var') );
	}
		public static function register_query_var( $vars )
		{
			$vars[] = 'doc1';
			$vars[] = 'doc2';
			return $vars;
		}
	/**
	 * Directory scan from 
	 * http://www.stevenmcmillan.co.uk/blog/2011/recursive-folder-scan-using-recursivedirectoryiterator/
	 *
	 * @author  Matt Emma
	 * @since	0.1
	 * @return	list of files in directory
	 */
	public static function directoryScan( $dir, $onlyfiles=false, $fullpath=false ) 
	{
		$dlist = Array();
		if ( isset($dir) && is_readable($dir) ) 
		{
			$dir = realpath($dir);
			if ( $onlyfiles ) {
				$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
			} else {
				$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
			}
			
			foreach( $objects as $entry => $object )
			{	
				if ( ! $fullpath ) {
					$entry = str_replace($dir, '', $entry);
				}
				$dlist[] = $entry;
			}
		}
		return $dlist;

	}
	/**
	 * Adds manager to the page
	 *
	 * @author  Jake Snyder
	 * @since	0.2
	 * @return	string $content The post content for page with the manager added
	 */
	public static function add_manager( $content )
	{
		global $wp, $wp_query, $wp_filesystem;

		$request = ( get_query_var('pagename') ) ? get_query_var('pagename') : strtolower($wp->request);

		if ( array_key_exists($request, self::$settings['pages']) && is_main_query() && in_the_loop() )
		{
			// Get the location title
			$folder = self::url_to_folder( $wp->request );

			$location = $participants = '';
			if ( 'Companies' == $folder )
			{
				$location     = "Companies";
				$participants = "Turner";
			}
			elseif ( 'Project' == $folder )
			{
				$location     = 'Project';
				$participants = "Turner, Owner, Owner's Rep, Consultants, Subcontractors";
			}
			elseif ( false !== strpos($folder, 'Companies/') )
			{
				$location     = str_replace('Companies/', '', $folder);
				$participants = "Turner, $location";
			}
			elseif ( false !== strpos($folder, 'Roles/') )
			{
				$location     = str_replace('Roles/', '', $folder);
				$roles = '';
				if ( 'Owner' == $location ) :
					$roles    = ", Owner, Owner's Rep";
				elseif ( 'Sub' == $location ) :
					$location = "Subcontractors";
					$roles    = ", $location";
				elseif ( $location && 'Turner' != $location ) :
					$roles    = ", $location";
				endif;

				$participants = "Turner{$roles}";
			}

			$upload_dir = wp_upload_dir();

			ob_start(); ?>

				<div class="doc-title twelvecol first last clearfix">
					<span class="doc-location fivecol first clearfix"><span>Document Center:</span> <?php echo $location; ?></span>
					<span class="doc-role sevencol last clearfix"><span>Participants:</span> <?php echo $participants; ?></span>
				</div>

			<?php
			$content .= ob_get_clean();
			$content .= '<div id="elfinder" class="clearfix"></div>';
			$content .= '<div class="folder-toc" style="margin: 2px"><a href="#popup_tableofcontents" title="" class="btn btn-info" target="" data-toggle="modal"><span class="fa fa-files-o"></span> </a></p>
						<div class="modal fade" id="popup_tableofcontents" tabindex="-1" role="dialog" aria-hidden="true">
							<div class="modal-dialog"><div class="modal-content">
								<div class="modal-header"><button type="button" class="close" data-dismiss="modal">×</button><h3>Table of Contents</h3></div><!-- modal-header (end) -->
								<div class="modal-body" style="width: 598px; height: 415px; overflow: scroll"><p><table class="table table-striped">';
			/**
			* Attempt using wp_filesystem api  // Maybe add in the future
	 		* http://wordpress.stackexchange.com/questions/160823/use-wp-filesystem-to-list-files-in-directory/160830#160830
			
			$filelist = $wp_filesystem->dirlist( $upload_dir['basedir'] . '/' . $folder );
			$content .= $filelist;
			*/
			$ffs = self::directoryScan($upload_dir['basedir'] . '/' . $folder);
			if ( $ffs )
			{
				asort($ffs);
				foreach ( $ffs as $ff )
				{
					if ( (strpos($ff,'tmb') == false) && (strpos($ff,'quarantine') == false) && (strpos($ff,'/.') == false) && (strpos($ff,'index.php') == false) )
					{
						$path_details = pathinfo($ff);
						if ( ! empty($path_details['extension']) ) {
						    $content .= '<tr><td><a href="'.$upload_dir['baseurl'] . '/' . $folder. $ff .'" target="_blank" alt="'. $ff .'">'. $ff .'</a></td></tr>';
						} else {
							$content .= '<tr><td><b>'. $ff .'</b></td></tr>';
						}
					}
				}
			}

			$content .= '</table></p></div><!-- .modal-body (end) -->
								<div class="modal-footer"><a href="#" class="btn btn-default" data-dismiss="modal">Close</a></div><!-- .modal-footer (end) -->
							</div><!-- .modal-content (end) -->
						</div><!-- .modal-dialog (end) -->
						</div><!-- .modal (end) --></div>';
		}

		return $content;
	}

	/**
	 * Redirect users who don't have permission or documents pages that don't exist
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function template_redirect()
	{
		if ( 'documents' == get_query_var('pagename') )
		{
			if ( 'company' == get_query_var('doc1') && ! get_query_var('doc2') )
			{
				wp_redirect( home_url('/') );
				die;
			}
			elseif ( 'company' == get_query_var('doc1') && $companyname = get_query_var('doc2') )
			{
				$company = get_page_by_path($companyname, OBJECT, 'em_companies');

				if ( ! $company )
				{
					wp_redirect( home_url('/') );
					die;
				}

				$user_company_id = emanager_post::user_company_id();
				if ( ! HAS_ROLE_TURNER && $company->ID != $user_company_id )
				{
					wp_redirect( home_url('/') );
					die;
				}
			}
			elseif ( get_query_var('doc2') )
			{
				wp_redirect( home_url('/') );
				die;
			}
			elseif ( ('turner' == get_query_var('doc1') || 'companies' == get_query_var('doc1')) && ! eman_check_role('turner') )
			{
				wp_redirect( home_url('/') );
				die;
			}
			elseif ( 'consultant' == get_query_var('doc1') && ! ( eman_check_role('owner') || eman_check_role('turner') ) )
			{
				wp_redirect( home_url('/') );
				die;
			}
			elseif ( 'owner' == get_query_var('doc1') && ! (( ! current_user_can('consultant') && eman_check_role('owner')) || eman_check_role('turner') ) )
			{
				wp_redirect( home_url('/') );
				die;
			}
			elseif ( 'sub' == get_query_var('doc1') && ! ( eman_check_role('sub') || eman_check_role('turner') ) )
			{
				wp_redirect( home_url('/') );
				die;
			}
		}
	}

	/**
	 * The connector.php for elfinder
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 * /
	public static function connector()
	{
		if ( empty($_REQUEST['connector']) ) return;
		if ( empty($_REQUEST['nonce']) ) die("failed no nonce");
		if ( ! wp_verify_nonce($_REQUEST['nonce'], 'elfinder') ) die("failed bad nonce");

		global $wp;
		$request = strtolower($wp->request);

		$folder = false;
		if ( 'documents' == $request )
		{
			$folder = 'Project';
		}
		elseif ( 'companies' == get_query_var('doc1') && HAS_ROLE_TURNER )
		{
			$folder = 'Companies';
		}
		elseif ( 'company' == get_query_var('doc1') )
		{
			$companyname = get_query_var('doc2');
			$company     = get_page_by_path($companyname, OBJECT, 'em_companies');

			if ( $company )
			{
				if ( HAS_ROLE_TURNER || $company->ID == emanager_post::user_company_id() )
				{
					$folder = 'Companies/' . $company->post_title;
				}
			}
		}
		elseif ( false !== strpos($request, 'turner') && HAS_ROLE_TURNER )
		{
			$folder = 'Roles/Turner';
		}
		elseif ( false !== strpos($request, 'consultant') && ( HAS_ROLE_OWNER || HAS_ROLE_TURNER ) )
		{
			$folder = 'Roles/Consultant';
		}
		elseif ( false !== strpos($request, 'owner') && (( ! current_user_can('consultant') && HAS_ROLE_OWNER) || HAS_ROLE_TURNER ) )
		{
			$folder = 'Roles/Owner';
		}
		elseif ( false !== strpos($request, 'sub') && ( HAS_ROLE_SUB || HAS_ROLE_TURNER ) )
		{
			$folder = 'Roles/Sub';
		}

		if ( ! $folder ) die("no folder: " . $request);

		require_once( get_template_directory() . '/includes/plugins/elfinder/php/elFinderConnector.class.php' );
		require_once( get_template_directory() . '/includes/plugins/elfinder/php/elFinder.class.php' );
		require_once( get_template_directory() . '/includes/plugins/elfinder/php/elFinderVolumeDriver.class.php' );
		require_once( get_template_directory() . '/includes/plugins/elfinder/php/elFinderVolumeLocalFileSystem.class.php' );

		$upload_dir = wp_upload_dir();

		if ( $upload_dir )
		{
			// run elFinder
			$opts = array(
				'roots' => array(
					array(
						'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
						'path'          => $upload_dir['basedir'] . '/' . $folder .'/', // path to files (REQUIRED)
						'URL'           => $upload_dir['baseurl'] . '/' . $folder .'/',  // $plugin_arr, // URL to files (REQUIRED)
						'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
						'attributes' => array(
							array(
								'pattern' => '/.tmb/',
								'read' => false,
								'write' => false,
								'hidden' => true,
								'locked' => false
							),
							array(
								'pattern' => '/.quarantine/',
								'read' => false,
								'write' => false,
								'hidden' => true,
								'locked' => false
							),
							array(
								'pattern' => '/index.php/',
								'read' => false,
								'write' => false,
								'hidden' => true,
								'locked' => false
							)
						)
					)
				)
			);
			$connector = new elFinderConnector( new elFinder($opts) );
			$connector->run();
		}
	}

	/**
	 * The connector.php for elfinder
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function url_to_folder( $request )
	{
		$folder = '';
		if ( 'documents' == $request )
		{
			$folder = 'Project';
		}
		elseif ( 'documents/companies' == $request && eman_check_role('turner') )# 'companies' == get_query_var('doc1') && HAS_ROLE_TURNER )
		{
			$folder = 'Companies';
		}
		elseif ( 0 === strpos($request, 'documents/company/') )#'company' == get_query_var('doc1') )
		{
			$companyname = untrailingslashit( str_replace('documents/company/', '', $request) );# get_query_var('doc2');
			$company     = get_page_by_path($companyname, OBJECT, 'em_companies');

			if ( $company )
			{
				if ( eman_check_role('turner') || $company->ID == emanager_post::user_company_id() )
				{
					$folder = 'Companies/' . $company->post_title;
				}
			}
		}
		elseif ( false !== strpos($request, 'turner') && eman_check_role('turner') )
		{
			$folder = 'Roles/Turner';
		}
		elseif ( false !== strpos($request, 'consultant') && ( eman_check_role('owner') || eman_check_role('turner') ) )
		{
			$folder = 'Roles/Consultant';
		}
		elseif ( false !== strpos($request, 'owner') && (( ! current_user_can('consultant') && eman_check_role('owner')) || eman_check_role('turner') ) )
		{
			$folder = 'Roles/Owner';
		}
		elseif ( false !== strpos($request, 'sub') && ( eman_check_role('sub') || eman_check_role('turner') ) )
		{
			$folder = 'Roles/Sub';
		}

		return $folder;
	}

	public static function permissions( $folder )
	{
		if ( eman_check_role('turner') )
		{
			if ( 'Companies' == $folder && ! (current_user_can('administrator') || current_user_can('editor')) )
			{
				return array(
					'pattern' => '/./',
					'read'  => true,
					'write' => true,
					'locked' => true
				);
			}
			else
			{
				return array(
					'pattern' => '/./',
					'read'  => true,
					'write' => true,
					'locked' => false
				);
			}
		}

		if ( 'Project' == $folder || 'Roles/Sub' == $folder )
		{
			return array(
				'pattern' => '/./',
				'read'  => true,
				'write' => true,
				'locked' => true
			);
		}
		else //if ( 'Roles/Consultant' == $folder || 'Roles/Owner' == $folder )
		{
			return array(
				'pattern' => '/./',
				'read'  => true,
				'write' => true,
				'locked' => true
			);
		}
	}

	/**
	 * The connector.php for elfinder
	 *
	 * @author  Jake Snyder
	 * @since	0.1
	 * @return	void
	 */
	public static function connector()
	{
		if ( empty($_REQUEST['nonce']) ) die("failed no nonce");
		if ( ! wp_verify_nonce($_REQUEST['nonce'], 'elfinder') ) die("failed bad nonce");
		if ( empty($_REQUEST['folder']) ) die("failed no folder");

		$request = $_REQUEST['folder'];

		$folder = self::url_to_folder( $request );

		if ( ! $folder ) die("no folder: " . $request . " doc1: " . get_query_var('doc1') . " has_turner: " . eman_check_role('turner'));

		require_once( get_template_directory() . '/includes/elfinder/php/elFinderConnector.class.php' );
		require_once( get_template_directory() . '/includes/elfinder/php/elFinder.class.php' );
		require_once( get_template_directory() . '/includes/elfinder/php/elFinderVolumeDriver.class.php' );
		require_once( get_template_directory() . '/includes/elfinder/php/elFinderVolumeLocalFileSystem.class.php' );

		$permissions = self::permissions($folder);

		$upload_dir = wp_upload_dir();

		if ( $upload_dir )
		{
			// run elFinder
			$opts = array(
				'roots' => array(
					array(
						'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
						'path'          => $upload_dir['basedir'] . '/' . $folder .'/', // path to files (REQUIRED)
						'URL'           => $upload_dir['baseurl'] . '/' . $folder .'/',  // $plugin_arr, // URL to files (REQUIRED)
						'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
						#'defaults'   => $permissions,
						'attributes' => array(
							array(
								'pattern' => '/.tmb/',
								'read' => false,
								'write' => false,
								'hidden' => true,
								'locked' => false
							),
							array(
								'pattern' => '/.quarantine/',
								'read' => false,
								'write' => false,
								'hidden' => true,
								'locked' => false
							),
							array(
								'pattern' => '/index.php/',
								'read' => false,
								'write' => false,
								'hidden' => true,
								'locked' => false
							),
							$permissions
						)
					)
				)
			);
			$connector = new elFinderConnector( new elFinder($opts) );
			$connector->run();
		}
	}
}

endif;