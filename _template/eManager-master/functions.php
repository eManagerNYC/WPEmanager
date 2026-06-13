<?php

/**
 * Set up the theme
 */
if ( ! defined('DISALLOW_FILE_EDIT') ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

function eman_info( $key, $esc='html' )
{
	static $version  = null;
	static $params   = null;
	$info            = array(
		'creator'            => 'Matthew M. Emma',
		'creatorurl'         => null,
		'partner'            => 'John F. Glatt',
		'partnerurl'         => null,
		'angel'              => 'Mark J. Pulsfort',
		'angelurl'           => null,
		'company'            => 'eManager',
		'companyurl'         => 'http://emanagertcco.com/',
		'parentcompany'      => 'Turner Construction Co.',
		'parentcompanyurl'   => 'http://www.turnerconstruction.com/',
		'sitename'           => get_option('blogname'),
		'dashboard_colors'   => array( 'babyblue', 'navy', 'orange', 'verde' ),
		'company_post_types' => array( 'em_employees', 'em_equipment', 'em_labortypes', 'em_materials' ),
	);
	static $raw_items = array('dashboard_colors', 'company_post_types');
	static $url_items = array('creatorurl', 'partnerurl', 'angelurl', 'companyurl', 'parentcompanyurl');

	// Dynamic items
	if ( 'version' == $key )
	{
		if ( null === $version )
		{
			$theme = wp_get_theme();
			$version = $theme->Version;
		}
		return $version;
	}
	elseif ( 'support_params' == $key )
	{
		if ( null === $params )
		{
			$user    = wp_get_current_user();
			$subject = 'eManager Support';
			$params = array(
				'referred' => str_replace(array('https://','http://'), '', home_url()),
				'view'     => get_permalink(),
				'subject'  => $subject,
				'user'     => eman_users_name( $user ),
				'email'    => $user->user_email,
			);
			foreach ( $params as &$param ) {
				$param = urlencode($param);
			}
		}
		return $params;
	}

	// If empty, nothing left to do
	if ( empty($info[$key]) ) { return false; }

	// Raw content return
	if ( in_array($key, $raw_items) ) {
		return $info[$key];
	}

	// URL content return
	if ( in_array($key, $url_items) ) {
		return esc_url_raw($info[$key]);
	}

	// Create an anchor link if entry has a url
	if ( 'link' == $esc ) {
		if ( ! empty($info["{$key}url"]) ) {
			return '<a href="' . esc_url_raw($info["{$key}url"]) . '" title="Visit ' . esc_attr($info[$key]) . '" targe="_blank">' . esc_html($info[$key]) . '</a>';
		} else {
			return esc_html($info[$key]);
		}
	// URL escape
	} elseif ( 'url' == $esc ) {
		return esc_url_raw($info[$key]);
	// attribute escape
	} elseif ( 'attr' == $esc ) {
		return esc_attr($info[$key]);
	// Raw escape
	} elseif ( false == $esc ) {
		return $info[$key];
	// HTML escape
	} else {
		return esc_html($info[$key]);
	}
}

function alphabetize_by_companies( $a, $b ) {
    return strcmp( $a['name'], $b['name'] );
}

function get_all_dcr_summary() {
	$transient_key = 'dcr/summary/overall';
	if ( false === ( $summary = get_transient($transient_key) ) ) {
		$query = new WP_Query([
			'posts_per_page' => -1,
			'post_type'  => 'em_dcr',
			'order'      => 'ASC',
			'orderby'    => 'meta_value',
			'meta_key'   => 'company',
			'meta_query' => $meta_query,
		]);
		$summary = create_dcr_summary( $query );
		set_transient( $transient_key, $summary, 24 * HOUR_IN_SECONDS );
	}

	return $summary;
}

function create_dcr_summary( $query ) {
	$current_company = null;
	$summary       = [
		'date'       => current_time('timestamp'),
		'totals'     => [
			'incidents' => 0,
			'minority'  => 0,
			'female'    => 0,
			'days'      => 0,
			'hours'     => 0,
			'incidents' => 0,
		],
		'companies'  => [],
	];

	if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
		$company_id = emanager_post::company( get_the_ID(), true );

		// Add company first time
		if ( empty($summary['companies'][$company_id]) ) {
			$summary['companies'][$company_id] = [
				'average'    => 0,
				'days'       => 0,
				'female'     => 0,
				'hours'      => 0,
				'incidents'  => 0,
				'minority'   => 0,
				'name'       => get_the_title($company_id),
				'total_dcrs' => 0,
				'workers'    => 0,
			];
		}

		$summary['companies'][$company_id]['total_dcrs']++;

		// Get info
		$breakdown = eman_get_field( 'classification_breakdown' );
		$incidents = eman_get_field( 'incidents_on_site' );

		if ( $breakdown ) {
			foreach ( $breakdown as $row ) {
				$fnon     = $row['female_non-minority'];
				$mnon     = $row['male_non-minority'];
				$fmin     = $row['female_minority'];
				$mmin     = $row['male_minority'];

				$minority = ($fmin ? $fmin : 0) + ($mmin ? $mmin : 0);
				$female   = ($fmin ? $fmin : 0) + ($fnon ? $fnon : 0);
				$total    = ($fmin ? $fmin : 0) + ($fnon ? $fnon : 0) + ($mmin ? $mmin : 0) + ($mnon ? $mnon : 0);
				$hours    = ($total * 8);

				// Add minorities
				$summary['companies'][$company_id]['minority']  += $minority;
				$summary['totals']['minority']                  += $minority;
				// Add females
				$summary['companies'][$company_id]['female']    += $female;
				$summary['totals']['female']                    += $female;
				// Add total
				$summary['companies'][$company_id]['days']      += $total;
				$summary['companies'][$company_id]['hours']     += $hours;
				$summary['totals']['days']                      += $total;
				$summary['totals']['hours']                     += $hours;
			}
		}

		if ( 'Yes' === $incidents ) {
			$summary['companies'][$company_id]['incidents']++;
			$summary['totals']['incidents']++;
		}

	endwhile;
	usort($summary['companies'], 'alphabetize_by_companies');
	endif;
	wp_reset_postdata();

	// Calculate monthly averages
	foreach ( $summary['companies'] as $company_id => $company ) {
		$average = empty($company['days']) ? 0 : ceil($company['days'] / $company['total_dcrs']);
		$summary['companies'][$company_id]['average'] = $average;
		$summary['totals']['averages']   += $average;
		$summary['totals']['total_dcrs'] += $company['total_dcrs'];
	}

	return $summary;
}

function is_local()
{
	return ( false != strpos(get_option('siteurl'), 'dev') || false != strpos(get_option('siteurl'), '192') ) ? true : false;
}


/**
 * Retrieve the eManager url.
 *
 * @author Jake Snyder
 * @param  string $path   Optional. Path relative to the eManager url. Default empty.
 * @return string eManager url link with optional path appended.
 */
function emanager_url( $path = '' )
{
	if ( $url = trailingslashit(eman_info('companyurl')) ) {
		return $url . (is_string($path) ? ltrim($path,'/') : '');
	}
}


/**
 * Load basic modules
 *
 * Comment out modules that are not desired for the current site.
 */

// Admin
require_once( 'admin/admin.php' );
require_once( 'admin/login.php' );
require_once( 'admin/tinymce.php' );
require_once( 'admin/upgrades.php' );
#require_once( 'admin/dashboard-widget.php' ); 			// A basic example to show instructions
#require_once( 'admin/recently-updated-content.php' ); 	// Shows recently updated content. REQUIRES customization
require_once( 'admin/required_plugins.php' );           // Manage requirements

// Front end
require_once( 'includes/mobile.php' );
#require_once( 'includes/nice-search.php' );			// Clean search urls
#require_once( 'includes/assets-rewrites.php' );		// Rewrite theme assets to /assets and plugins to /plugins. DOES NOT WORK ON NGINX SERVERS LIKE WPENGINE
require_once( 'includes/settings/enqueue.php' );
#require_once( 'includes/pdf-creator/pdf-creator.php' );


/**
 * Load emanager modules
 */

// Install settings, generally each install runs once
require_once( 'includes/install.php' );
require_once( 'includes/acf-wrapper.php' );

// Add the rest of the functionality
#require_once( 'includes/plugins.php' );
require_once( 'includes/post_types.php' );
require_once( 'includes/settings.php' );
require_once( 'includes/classes.php' );
require_once( 'includes/save.php' );
require_once( 'includes/field_support.php' );
require_once( 'includes/components.php' );


/**
 * Theme updates
 */
require_once( 'includes/updates/updates.php' );



if ( ! function_exists('mergePdfFiles') ) :
function mergePdfFiles( $files_array, $output_name )
{
/** /
echo ' $output_name = '. $output_name ."<br>\n";
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($files_array);
echo "</pre>\n";
#exit;
/**/
	if ( is_array( $files_array ) )
	{
		$cmd    = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$output_name " . implode(" ", $files_array);
#echo ' $cmd = '. $cmd ."<br>\n";
		$result = shell_exec($cmd);
		return $result;
	}
	return false;
}
endif;



/** /
if ( ! function_exists('mergePDFFiles') && class_exists('mPDF') ) :
function mergePDFFiles( $filenames, $outFile )
{
    if ( is_array( $filenames ) )
    {
        $filesTotal    = sizeof( $filenames );
        $fileNumber    = 1;

		$margin_left   = 6.35;
		$margin_right  = 6.35;
		$margin_top    = 6.35;
		$margin_bottom = 6.35;
		$mpdf          = new mPDF( 'utf-8', 'Letter', '', '', $margin_left, $margin_right, $margin_top, $margin_bottom );

		$mpdf->SetImportUse();

		if ( ! file_exists($outFile) )
		{
			$handle = fopen($outFile, 'w');
			fclose($handle);
		}

		foreach ( $filenames as $fileName )
		{
			if ( file_exists( $fileName ) )
			{
				$pagesInFile = $mpdf->SetSourceFile( $fileName );
				for ( $i = 1; $i <= $pagesInFile; $i++ )
				{
					$tplId = $mpdf->ImportPage($i);
					$mpdf->UseTemplate($tplId);
					if ( ($fileNumber < $filesTotal) || ($i != $pagesInFile) )
					{
						$mpdf->WriteHTML('<pagebreak />');
					}
				}
			}
			$fileNumber++;
		}
		$mpdf->Output($outFile);
	}
}
endif;



/**
 * Customize Site
 */

add_filter( 'wp_list_pages', 'eman_nav_update', 10, 2 );
function eman_nav_update( $output, $r )
{
	// Switch Dashboard out with icon
	$output = str_replace( '>Dashboard</a>', '><span class="fa fa-home" aria-hidden="true"></span> Home</a>', $output );

	// Add messages indicator to Inbox
	ob_start();
	do_action( 'sewn/messenger/new_message_indicator', array('show_0' => false) );
	$indicator = ob_get_clean();
	$output = str_replace(">Inbox</a>", ">Inbox $indicator</a>", $output);
/** /
	// Add messages indicator to BIC
	ob_start();
	do_action( 'sewn/messenger/new_message_indicator', array('show_0' => false) );
	$indicator = ob_get_clean();
	$output = str_replace(">Inbox</a>", ">Inbox $indicator</a>", $output);
/**/
	return $output;
}


/**
 * Remove the content field from the title/content plugin
 */
add_filter( 'acf/edit_title_content/content/add', '__return_false' );
if ( false !== strpos($_SERVER['REQUEST_URI'], '/profile/') ) {
	add_filter( "sewn/register/username/add", '__return_false' );
}


/**
 * Add header image to emails
 */
add_filter( "sewn/email_templates/headerimage", 'eman_email_headerimage' );
function eman_email_headerimage()
{
	return get_template_directory_uri() . '/assets/img/email_header.jpg';
}

add_filter( "sewn/email_templates/headerimage_alt", 'eman_email_headerimage_alt' );
function eman_email_headerimage_alt()
{
	return 'eManager';
}

/**
 * Add company user column
 */
add_filter( 'manage_users_columns', 'pippin_add_user_id_column' );
function pippin_add_user_id_column( $columns )
{
	$columns['company'] = 'Company';
	return $columns;
}
	add_action( 'manage_users_custom_column', 'pippin_show_user_id_column_content', 10, 3 );
	function pippin_show_user_id_column_content( $value, $column_name, $user_id )
	{
		if ( 'company' === $column_name )
		{
			$user = get_userdata( $user_id );
			if ( ! empty($user->company) ) {
				return get_the_title($user->company);
			}
		}
		return $value;
	}