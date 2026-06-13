<?php

/**
 * Extra Shortcodes
 */

add_shortcode( 'eman_info', '' );
function eman_info_short( $atts )
{
	extract( shortcode_atts(array(
		'show' => 'company',
		'esc' => 'html',
	), $atts) );
	return eman_info( $show, $esc );
}

add_shortcode( 'bloginfo',         'eman_short_bloginfo' );
add_shortcode( 'site_name',        'eman_short_bloginfo' );
add_shortcode( 'sitename',         'eman_short_bloginfo' );
add_shortcode( 'site_title',       'eman_short_bloginfo' );
add_shortcode( 'sitetitle',        'eman_short_bloginfo' );
function eman_short_bloginfo( $atts )
{
	extract( shortcode_atts(array(
		'show' => 'name',
	), $atts) );
	return get_bloginfo( $show );
}


add_shortcode( 'site_desc',        'eman_short_sitedesc' );
add_shortcode( 'sitedesc',         'eman_short_sitedesc' );
add_shortcode( 'site_description', 'eman_short_sitedesc' );
add_shortcode( 'sitedescription',  'eman_short_sitedesc' );
function eman_short_sitedesc()
{
	return get_bloginfo('description');
}


add_shortcode( 'site_url',         'eman_short_siteurl' );
add_shortcode( 'siteurl',          'eman_short_siteurl' );
function eman_short_siteurl()
{
	return home_url();
}


add_shortcode( 'wp_version',       'eman_short_wp_version' );
add_shortcode( 'wpversion',        'eman_short_wp_version' );
function eman_short_wp_version()
{
	return get_bloginfo('version');
}


add_shortcode( 'date',             'eman_short_date_i18n' );
add_shortcode( 'date_i18n',        'eman_short_date_i18n' );
function eman_short_date_i18n( $atts )
{
	extract( shortcode_atts( array(
		'format' => 'l jS \of F Y',
		'timestamp' => 'now'
	), $atts ) );
	return date_i18n( $format, strtotime($timestamp ) );
}


add_shortcode( 'time',             'eman_short_time' );
function eman_short_time( $atts )
{
	extract( shortcode_atts( array(
		'format' => 'h:i:s A',
		'timestamp' => 'now'
	), $atts ) );
	return date_i18n( $format, strtotime($timestamp ) );
}


add_shortcode( 'year',             'eman_short_year' );
function eman_short_year( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$year = date_i18n('Y', strtotime('+'.intval($plus).' years' ) );
	} elseif ( ! empty($minus ) ) {
		$year = date_i18n('Y', strtotime('-'.intval($minus).' years' ) );
	} else {
		$year = date_i18n('Y', strtotime($timestamp ) );
	}
	return $year;
}


add_shortcode( 'month',            'eman_short_month' );
function eman_short_month( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$month = date_i18n('m', strtotime('+'.intval($plus).' months' ) );
	} elseif ( ! empty($minus) ) {
		$month = date_i18n('m', strtotime('-'.intval($minus).' months' ) );
	} else {
		$month = date_i18n('m', strtotime($timestamp ) );
	}
	return $month;
}


add_shortcode( 'month_name',       'eman_short_monthname' );
add_shortcode( 'monthname',        'eman_short_monthname' );
function eman_short_monthname( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$month_name = date_i18n('F', strtotime('+'.intval($plus).' months' ) );
	} elseif ( ! empty($minus) ) {
		$month_name = date_i18n('F', strtotime('-'.intval($minus).' months' ) );
	} else {
		$month_name = date_i18n('F', strtotime($timestamp ) );
	}
	return $month_name;
}


add_shortcode( 'day',              'eman_short_day' );
function eman_short_day( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$day = date_i18n('d', strtotime('+'.intval($plus).' days' ) );
	} elseif ( ! empty($minus) ) {
		$day = date_i18n('d', strtotime('-'.intval($minus).' days' ) );
	} else {
		$day = date_i18n('d', strtotime($timestamp ) );
	}
	return $day;
}


add_shortcode( 'weekday',          'eman_short_weekday' );
add_shortcode( 'week_day',         'eman_short_weekday' );
function eman_short_weekday( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$weekday = date_i18n('l', strtotime('+'.intval($plus).' days' ) );
	} elseif ( ! empty($minus) ) {
		$weekday = date_i18n('l', strtotime('-'.intval($minus).' days' ) );
	} else {
		$weekday = date_i18n('l', strtotime($timestamp ) );
	}
	return $weekday;
}


add_shortcode( 'hours',            'eman_short_hours' );
add_shortcode( 'hour',             'eman_short_hours' );
function eman_short_hours( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$hours = date_i18n('H', strtotime('+'.intval($plus).' hours' ) );
	} elseif ( ! empty($minus) ) {
		$hours = date_i18n('H', strtotime('-'.intval($minus).' hours' ) );
	} else {
		$hours = date_i18n('H', strtotime($timestamp ) );
	}
	return $hours;
}


add_shortcode( 'minutes',          'eman_short_minutes' );
add_shortcode( 'minute',           'eman_short_minutes' );
function eman_short_minutes( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$minutes = date_i18n('i', strtotime('+'.intval($plus).' minutes' ) );
	} elseif ( ! empty($minus) ) {
		$minutes = date_i18n('i', strtotime('-'.intval($minus).' minutes' ) );
	} else {
		$minutes = date_i18n('i', strtotime($timestamp ) );
	}
	return $minutes;
}


add_shortcode( 'seconds',          'eman_short_seconds' );
add_shortcode( 'second',           'eman_short_seconds' );
function eman_short_seconds( $atts )
{
	extract( shortcode_atts( array(
		'plus' => 0,
		'minus' => 0,
		'timestamp' => 'now'
	), $atts ) );
	if ( ! empty($plus) ) {
		$seconds = date_i18n('s', strtotime('+'.intval($plus).' seconds' ) );
	} elseif ( ! empty($minus) ) {
		$seconds = date_i18n('s', strtotime('-'.intval($minus).' seconds' ) );
	} else {
		$seconds = date_i18n('s', strtotime($timestamp ) );
	}
	return $seconds;
}


// add 'Support' and 'Donate' links to plugin meta row
add_filter( 'plugin_row_meta',     'eman_short_unqprfx_plugin_meta', 10, 2 );
function eman_short_unqprfx_plugin_meta( $links, $file )
{
	if ( false !== strpos( $file, 'extra-shortcodes.php' ) ) {
		$links = array_merge( $links, array( '<a href="http://turneremanager.com" title="Need help?">Support</a>' ), 'emanager' );
	}
	return $links;
}


/**
 * eManager 1.0 Shortcodes
 */

// Project Information

add_shortcode( 'turner_logo',      'eman_short_turner_logo' );
function eman_short_turner_logo()
{
	return '<img src="'. get_stylesheet_directory_uri() . '/assets/images/turner.gif">';
}


add_shortcode( 'contract_info',    'eman_short_contract_info' );
function eman_short_contract_info()
{
	return '<strong>' . do_shortcode('[proj_name]') . '<br>Contract No. ' . do_shortcode('[contract_number]') . '</strong><br>' . do_shortcode('[proj_address]');
}


add_shortcode( 'echocid',          'eman_short_echocid' );
function eman_short_echocid()
{
	global $current_user;
	return $current_user->ID;
}


add_shortcode( 'echocun',          'eman_short_echocun' );
function eman_short_echocun()
{
	global $current_user;
	return $current_user->user_login;
}


add_shortcode( 'logout',           'eman_short_logout' );
function eman_short_logout( $atts )
{
	extract( shortcode_atts( array(
		'redirect' => '',
	), $atts ) );
	return wp_logout_url( $redirect );
}


add_shortcode( 'permalink',        'eman_short_permalink' );
function eman_short_permalink( $atts )
{
	extract( shortcode_atts( array(
		'content' => '',
	), $atts ) );
	return get_permalink($content);
}


add_shortcode( 'welcome', 'eman_short_welcome' );
function eman_short_welcome()
{
	$userid    = get_current_user_id();
	$user_info = get_userdata($userid);
	return 'Welcome ' . $user_info->display_name;
}


add_shortcode( 'em_username', 'eman_short_username' );
function eman_short_username()
{
	$userid    = get_current_user_id();
	$user_info = get_userdata($userid);
	return ( is_object($user_info) ) ? $user_info->display_name : '';
}


add_shortcode( 'em_phone', 'eman_short_phone_number' );
function eman_short_phone_number()
{
	$userid    = get_current_user_id();
	$user_info = get_userdata($userid);
	return $user_info->phone_number;
}


add_shortcode( 'em_company', 'eman_short_company' );
function eman_short_company()
{
	$userid    = get_current_user_id();
	$user_info = get_userdata($userid);
	return get_user_meta($userid, 'company', true);
}


add_shortcode( 'mycompany', 'usermeta_company' );
function usermeta_company()
{
	return get_the_author_meta('company', $current_author->ID);
}


/**
 * eManager 2.0 Shortcodes
 */

add_shortcode( 'proj_name', 'eman_project_name' );
function eman_project_name( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('proj_name', 'option');
	}
}


add_shortcode( 'proj_address', 'eman_project_address' );
function eman_project_address( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('proj_address', 'option') ."<br>". eman_get_field('proj_city_state', 'option') ." ". eman_get_field('proj_zipcode', 'option');
	}
}


add_shortcode( 'proj_phone', 'eman_project_phone' );
function eman_project_phone( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('proj_phone', 'option');
	}
}


add_shortcode( 'proj_fax', 'eman_project_fax' );
function eman_project_fax( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('proj_fax', 'option');
	}
}


add_shortcode( 'proj_start', 'eman_start_date' );
function eman_start_date( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('proj_start', 'option');
	}
}


add_shortcode( 'business_unit', 'eman_project_bu' );
function eman_project_bu( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('business_unit', 'option');
	}
}


add_shortcode( 'contract_type', 'eman_contract_type' );
function eman_contract_type( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('contract_type', 'option');
	}
}


add_shortcode('contract_number', 'eman_short_contract_number');
function eman_short_contract_number()
{
	if ( function_exists('get_field') ) {
		return eman_get_field('contract_number', 'option');
	}
}


add_shortcode( 'construction_type', 'eman_short_construction_type' );
function eman_short_construction_type( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('construction_type', 'option');
	}
}


add_shortcode( 'project_id', 'eman_short_project_id' );
function eman_short_project_id( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('project_id', 'option');
	}
}


add_shortcode( 'project_phase', 'eman_short_project_phase' );
function eman_short_project_phase( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('phase_of_project', 'option');
	}
}


add_shortcode( 'building_type', 'eman_short_building_type' );
function eman_short_building_type( $atts )
{
	if ( function_exists('get_field') ) {
		return eman_get_field('building_type', 'option');
	}
}


/**
 * eManager Observations Module with contributions from J. Evangelista and R. Carmosino
 */

add_shortcode( 'observations', 'query_observations');
function query_observations( $atts )
{
	extract( shortcode_atts( array(
		'y' => date('Y'),
		'm' => date('n'),
		'd' => date('j')
	), $atts, 'observations' ) );

	wp_enqueue_script('date-picker', get_template_directory_uri(). '/assets/js/date-picker.js', array('jquery-ui-datepicker'), '1.0', true);

	$output = '';

	if ( isset($_POST['datepicker']) )
	{
		$date_unix = strtotime($_POST['datepicker']);
		$args = array(
			'post_type' => 'em_observation',
			'order' => 'ASC',
			'orderby' => 'datetime',
			'meta_query' => array(
				array(
					'key' => 'datetime',
					'value' => array($date_unix, $date_unix+86399),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC'
				)
			)
		);
	}
	else
	{
		$date = $y.'-'.$m.'-'.$d;
		$date_unix = strtotime($date);
		$date_tom = $y.'-'.$m.'-'.(intval($d)+1);
		$date_tom_unix = strtotime($date_tom)-1;
		$args = array(
			'post_type' => 'em_observation',
			'order' => 'ASC',
			'orderby' => 'datetime',
			'meta_query' => array(
				array(
					'key' => 'datetime',
					'value' => array($date_unix, $date_tom_unix),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC'
				)
			)
		);
	}
	$the_query = new WP_Query( $args );

	if ( current_user_can('turner') || current_user_can('administrator') ) {
		$output .= '<p style="text-align: right"><a href="/observation/add/" class="btn btn-info" role="button">Add Observation</a></p>';
	}
	$output .= '<form class="form-inline" role="form" action="" method="post"><div class="form-group">
    <label for="datepicker">Date</label> <input type="text" id="datepicker" name="datepicker" value="" size="35"/></div>
    <button type="submit" class="btn btn-default">Submit</button></form>';
	if ( $the_query->have_posts() )
	{
		$output .= '<ul class="list-group">';
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();

			$date_pretty = date('h:i a', eman_get_field('datetime'));

			$output .= '<li class="list-group-item">';
			/*
      $location = eman_get_field('location');
      if( $location ){
        $output .= '<span class="badge alert-warning">'.$location.'</span>';
      }
      */
			$output .= '<span class="badge alert-info pull-right">'.get_the_author().'</span>';
			$output .= '<h4 class="list-group-item-heading">'.$date_pretty.'</h4><hr>';
			// Company array
			/*
      $rows = eman_get_field('companys');
      if($rows)
      {
        $output .= '<ol>';
        foreach($rows as $row)
        {
          $output .= '<li><b>' . $row['company'] . '</b> - ' . $row['headcount'] .' count - '. $row['scope'] .'</li>';
        }
        $output .= '</ol>';
      }
      */
			$output .= '<p class="list-group-item-text">'.eman_get_field('f_notes').'</p>';

			// Attachments
			$attachments = eman_get_field('attachments');
			if 	($attachments)
			{
				foreach
				($attachments as $attachment)
				{
					$output .= '<a href="'.$backup['file'].'">'.explode('/', $backup['file']).'</a>';
				}
			}

			$output .= '</li>';
		}
		$output .= '</ul>';
	}
	wp_reset_postdata();

	$output .= '<script>
            $(document).ready(function() {
                $(\'#datepicker\').datepicker({
                    dateFormat : \'yy-mm-dd\'
                });
              });
          </script>';

	if ( isset($_POST['datepicker']) )
	{
		$date_weather = strtotime($_POST['datepicker']);
		if (date('Ymd') == date('Ymd', $date_weather) && shortcode_exists( 'weather' )) {
			$output .= '<div class="panel panel-default"><div class="panel-heading">Weather</div><div class="panel-body">'.do_shortcode('[weather city="New_York" state="NY" days="1"]').'</div></div>';
		} elseif (date('Ymd') !== date('Ymd', $date_weather) && shortcode_exists( 'weather_history' )) {
			$output .= '<div class="panel panel-default"><div class="panel-heading">Weather</div><div class="panel-body">'.do_shortcode('[weather_history city="New_York" state="NY" d="'.date('d', $date_weather).'" m="'.date('m', $date_weather).'" y="'.date('Y', $date_weather).'" icon="42"]').'</div></div>';
		} else {
			$output .='';
		}
	}
	else
	{
		if ( shortcode_exists('weather') ) {
			$output .= '<div class="panel panel-default"><div class="panel-heading">Weather</div><div class="panel-body">'.do_shortcode('[weather city="New_York" state="NY" days="1"]').'</div></div>';
		}
	}
	return $output;
}


/**
 * eManager Superintendent Daily View M. Emma
 */

add_shortcode( 'supt_daily', 'query_suptdailyview');
function query_suptdailyview( $atts )
{
	extract( shortcode_atts( array(
		'y' => date('Y'),
		'm' => date('n'),
		'd' => date('d')
	), $atts, 'supt_daily' ) );

	wp_enqueue_script('date-picker', get_template_directory_uri(). '/assets/js/date-picker.js', array('jquery-ui-datepicker'), '1.0', true);

	$output = '';

	$output .= '<form class="form-inline" role="form" action="" method="post">
                    <div class="form-group">
                    <label  class="sr-only" for="datepicker">Date</label>
                    <input type="text" id="datepicker" name="datepicker" placeholder="Date" value="" size="15"/>
                    </div>
                    <button type="submit" class="btn btn-default">Submit</button>
                </form>
                <hr>';

	if ( isset($_POST['datepicker']) )
	{
		$date_unix = strtotime($_POST['datepicker']);
		$args = array(
			'post_type' => 'em_observation',
			'order' => 'ASC',
			'orderby' => 'datetime',
			'meta_query' => array(
				array(
					'key' => 'datetime',
					'value' => array($date_unix, $date_unix+86399),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC'
				)
			)
		);
	}
	else
	{
		$date = $y.'-'.$m.'-'.$d;
		$date_unix = strtotime($date);
		$date_tom = $y.'-'.$m.'-'.(intval($d)+1);
		$date_tom_unix = strtotime($date_tom)-1;
		$args = array(
			'post_type' => 'em_observation',
			'order' => 'ASC',
			'orderby' => 'datetime',
			'meta_query' => array(
				array(
					'key' => 'datetime',
					'value' => array($date_unix, $date_tom_unix),
					'compare' => 'BETWEEN',
					'type' => 'NUMERIC'
				)
			)
		);
	}
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() )
	{
		$output .= '<h3>Superintendent Observations</h3>';
		$output .= '<ul class="list-group">';
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();

			$date_pretty = date('h:i a', eman_get_field('datetime'));

			$output .= '<li class="list-group-item">';
			$output .= '<span class="badge alert-info pull-right">'.get_the_author().'</span>';
			$output .= '<h4 class="list-group-item-heading">'.$date_pretty.'</h4><hr>';
			$output .= '<p class="list-group-item-text">'.eman_get_field('f_notes').'</p>';

			// Attachments
			$attachments = eman_get_field('attachments');
			if ( $attachments )
			{
				foreach ( $attachments as $attachment ) {
					$output .= '<a href="'.$backup['file'].'">'.explode('/', $backup['file']).'</a>';
				}
			}

			$output .= '</li>';
		}
		$output .= '</ul>';
	}
	wp_reset_postdata();

	if ( isset($_POST['datepicker']) )
	{
		$date = $_POST['datepicker'];
		$args = array(
			'post_type' => 'em_dcr',
			'order' => 'ASC',
			'orderby' => 'company',
			'meta_query' => array(
				array(
					'key' => 'work_date',
					'value' => $date,
					'compare' => 'LIKE',
					'type' => 'NUMERIC'
				)
			)
		);
	}
	else
	{
		$date = $y.$m.$d;
		$args = array(
			'post_type' => 'em_dcr',
			'order' => 'ASC',
			'orderby' => 'company',
			'meta_query' => array(
				array(
					'key' => 'work_date',
					'value' => $date,
					'compare' => 'LIKE',
					'type' => 'NUMERIC'
				)
			)
		);
	}
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() )
	{
		$output .= '<h3>Contractor Reports</h3>';
		$output .= '<ul class="list-group">';
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();

			$output .= '<li class="list-group-item">';
			$postid = get_the_ID();
			$title = get_the_title();
			$permalink = get_permalink($postid);
			$output .= '<p class="list-group-item-text"><strong><a href="'.$permalink.'">' .$title.'</a></strong></p>';

			$output .= '</li>';
		}
		$output .= '</ul>';
	}
	wp_reset_postdata();

	$proj_city = str_replace(' ', '_', eman_get_field('proj_city', 'option'));
	if ( ! $proj_city ) {
		$proj_city = 'New_York';
	}
	$proj_state = eman_get_field('proj_state', 'option');
	if ( ! $proj_city ) {
		$proj_city = 'NY';
	}

	if ( isset($_POST['datepicker']) )
	{
		$date_weather = strtotime($_POST['datepicker']);
		if ( date('Ymd') == date('Ymd', $date_weather) && shortcode_exists('weather') ) {
			$output .= '<div class="panel panel-default"><div class="panel-heading">Weather</div><div class="panel-body">'.do_shortcode('[weather city="' . $proj_city . '" state="' . $proj_state . '" days="1"]').'</div></div>';
		} elseif ( date('Ymd') !== date('Ymd', $date_weather) && shortcode_exists('weather_history') ) {
			$output .= '<div class="panel panel-default"><div class="panel-heading">Weather</div><div class="panel-body">'.do_shortcode('[weather_history city="' . $proj_city . '" state="' . $proj_state . '" d="'.date('d', $date_weather).'" m="'.date('m', $date_weather).'" y="'.date('Y', $date_weather).'" icon="42"]').'</div></div>';
		} else {
			$output .= '';
		}
	}
	else
	{
		if ( shortcode_exists('weather') ) {
			$output .= '<div class="panel panel-default"><div class="panel-heading">Weather</div><div class="panel-body">'.do_shortcode('[weather city="' . $proj_city . '" state="' . $proj_state . '" days="1"]').'</div></div>';
		}
	}
	return $output;
}


/**
 * eManager PCO Totals
 */

add_shortcode( 'pco_total', 'pco_total' );
function pco_total( $atts )
{
	extract( shortcode_atts( array(
		'status' => 'manager'
	), $atts, 'pco_total' ) );

	global $post; // required
	$sum = 0;

	$output = '';

	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'em_noc',
		'perm'=>'readable',
		'tax_query' => array(
			array(
				'taxonomy' => 'em_status',
				'terms' => $status,
				'field' => 'slug',
				'include_children' => true,
				'operator' => 'IN'
			)
		),
	);
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() )
	{
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();
			$poststatus = emanager_post::status($post, 'slug');
			$contractors_and_estimate = eman_get_field('contractors_and_estimate', $post->ID);
			if ( is_array($contractors_and_estimate) && ($status === $poststatus) )
			{
				$total = 0;
				$row_count=0;
				foreach ( $contractors_and_estimate as $row )
				{
					$item_count=0;
					foreach ( $row as $key2 => $item )
					{
						if ( 'estimated_value' == $key2 ) {
							$total += $item;
						}
						$item_count++;
					}
					$row_count++;
				}
			}
			$sum += $total;
		}
	}
	wp_reset_postdata();

	return eman_number_format($sum);
}

// break out for by user basis
add_shortcode( 'pco_bictotal', 'pco_bictotal' );
function pco_bictotal( $atts )
{
	extract( shortcode_atts( array(
		'status' => 'manager'
	), $atts, 'pco_bictotal' ) );

	global $post; // required
	// $sum = 0;

	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'em_noc',
		// 'perm'=>'readable',
		'tax_query' => array(
			array(
				'taxonomy' => 'em_status',
				'terms' => $status,
				'field' => 'slug',
				'include_children' => true,
				'operator' => 'IN'
			)
		),
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() )
	{
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();
			$poststatus = emanager_post::status($post, 'slug');
			$contractors_and_estimate = eman_get_field('contractors_and_estimate', $post->ID);
			$bic = emanager_bic::get_bic($post, 'display_name');
			$output ='';
			if ( $status === $poststatus )
			{
				$output .= '<div>' . $bic . ' | $';
				if ( is_array($contractors_and_estimate) )
				{
					$total = 0;
					$row_count=0;
					foreach ( $contractors_and_estimate as $row )
					{
						$item_count=0;
						foreach ( $row as $key2 => $item )
						{
							if ( 'estimated_value' == $key2 ) {
								$total += $item;
							}
							$item_count++;
						}
						$row_count++;
					}
				}
				$output .= eman_number_format($total).'</div>';
			}
		}
	}
	return $output;
}


/**
 * eManager DCR Totals
 */

add_shortcode( 'dcr_total', 'dcr_total' );
function dcr_total( $atts )
{
	extract( shortcode_atts( array(
		'status' => 'approved',
		'month' => 'nov',
		'year' => '2014'
	), $atts, 'dcr_total' ) );

	global $post; // required
	$sum = 0;

	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'em_dcr',
		'perm'=>'readable',
		'tax_query' => array(
			array(
				'taxonomy' => 'em_status',
				'terms' => $status,
				'field' => 'slug',
				'include_children' => true,
				'operator' => 'IN'
			)
		),
	);

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() )
	{
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();
			$post_id = $post->ID;
			$dcr_labor_breakdown = get_option('options_dcr_labor_breakdown');
			$labor_unit_hours    = 'hours';
			$labor_unit_days     = 'workers';
			$man_hours           = ( 'Man-hours' == $dcr_labor_breakdown ) ? true : false;

			$row_count = $man_total = $mancount = 0;
			foreach ( $field['value'] as $row )
			{

				// Set up the row data
				$row_output = array();
				$row_total  = 0;
				$object     = array_shift($row);
				if ( is_object($object) )
				{
					$row_output['title'] = $object->post_title;

					if ( 'em_dcr' == $post->post_type )
					{
						if ( $man_hours )
						{
							$mancount                = array_shift($row);
							$row_output['mancount']  = $mancount;
							$man_total              += $mancount;
						}

						$notes      = array_pop($row);
						$pco_number = array_pop($row);

						$row_total = 0;
						foreach ( $row as $key => $amount )
						{
							$row_output[$key]  = $amount;
							$row_total        += $amount;
						}

						$row_output['pco']   = $pco_number;
						$row_output['notes'] = $notes;
						$row_output['total'] = $row_total . ' ' . ($man_hours ? $labor_unit_hours : $labor_unit_days);

						$output['table']['rows'][]  = $row_output;
						$output['totals']['rows']  += $row_total;
					}
					else
					{
						$count        = array_shift($row);
						$row_output[] = "&times; $count";

						foreach ( $row as $key => $amount )
						{
							$rate          = get_post_meta($object->ID, $key, true);
							$row_output[]  = "$amount hrs @ $$rate/hr";
							$row_total    += $amount * $rate * $count;
						}

						$row_output[] = '$' . $row_total ;

						$output['table']['rows'][]  = $row_output;
						$output['totals']['rows']  += $row_total;
					}
				}

				$row_count++;
			}

			if ( 'em_dcr' == $post->post_type )
			{
				if ( $man_hours ) {
					$output['totals']['table'] = "$labor_unit_hours $labor_unit_days";
				} else {
					$output['totals']['table'] = "$labor_unit_days";
				}
			}
			else
			{
				$output['totals']['table'] = $output['totals']['rows'];
			}
			print_r($output);
			// $sum += $output;
		}
	}
	wp_reset_postdata();

	return eman_number_format($sum);
}


/**
 * eManager Ticket Totals
 */

add_shortcode( 'ticket_total', 'ticket_total' );
function ticket_total( $atts )
{
	extract( shortcode_atts( array(
		'status' => 'draft'
	), $atts, 'ticket_total' ) );

	global $post; // required
	$sum = 0;

	$output = '';

	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'em_tickets',
		'perm'=>'readable',
		'tax_query' => array(
			array(
				'taxonomy' => 'em_status',
				'terms' => $status,
				'field' => 'slug',
				'include_children' => true,
				'operator' => 'IN'
			)
		),
	);
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() )
	{
		while ( $the_query->have_posts() )
		{
			$the_query->the_post();
			$final_segments = eman_form_fields( $cpt['form'], $post->ID );
			$poststatus = emanager_post::status($post, 'slug');

			$labor_total = $material_total = $equipment_total = 0;

			foreach ( $fields as $field )
			{
				// If there is no key, it is excluded, or it has already been displayed
				if ( ! $field['name'] || in_array($field['name'], $this->settings['exclude_cols']) || array_key_exists($field['name'], $this->post_type_settings['table_cols']) ) {
					continue;
				}

				$label = $field['label'];
				$final_total = $total = false;
				$value = '';

				if ( 'classification_breakdown' == $field['name'] )
				{
					$label          .= ' Detail';
					$summary         = emanager_summary::classification_breakdown($field, $post);
					$labor_total     = $total = $summary['totals']['rows'];
				}
				elseif ( 'employee_breakdown' == $field['name'] )
				{
					$label          .= ' Detail';
					$summary         = emanager_summary::employee_breakdown($field, $post);
					$labor_total     = $total = $summary['totals']['rows'];
				}
				elseif ( 'materials' == $field['name'] )
				{
					$label          .= ' Detail';
					$summary         = emanager_summary::materials($field, $post);
					$material_total  = $total = $summary['totals']['rows'];
				}
				elseif ( 'equipment' == $field['name'] )
				{
					$label          .= ' Detail';
					$summary         = emanager_summary::equipment($field, $post);
					$equipment_total = $total = $summary['totals']['rows'];
				}
				elseif ( 'labor_markup' == $field['name'] || 'material_markup' == $field['name'] || 'equipment_markup' == $field['name'] )
				{
					$label          .= ' Detail';

					if ( 'labor_markup' == $field['name'] ) {
						$current_total = $labor_total;
					} elseif ( 'material_markup' == $field['name'] ) {
						$current_total = $material_total;
					} elseif ( 'equipment_markup' == $field['name'] ) {
						$current_total = $equipment_total;
					}

					if ( $field['value'] )
					{
						$summary         = emanager_summary::markup($field['value'], $current_total);
						$markup_total    = $summary['total'];
						foreach ( $summary['rows'] as $row ) {
							$value .= $row['description'] . ': ' . $row['value'] . ' = ' . $row['amount'] . "\n\r";
						}
						$value = rtrim($value, "\n\r");
						$total = ( $markup_total ? $current_total + $markup_total : 0 );
					}

					if ( 'labor_markup' == $field['name'] ) {
						$labor_total     += $markup_total;
					} elseif ( 'material_markup' == $field['name'] ) {
						$material_total  += $markup_total;
					} elseif ( 'equipment_markup' == $field['name'] ) {
						$equipment_total += $markup_total;
					}
				}
				elseif ( 'global_markup' == $field['name'] )
				{
					$total = $labor_total + $material_total + $equipment_total;

					$markup_total = 0;
					if ( is_array($field['value']) )
					{
						foreach ( $field['value'] as $markup )
						{
							if ( is_numeric($markup['value']) && 0 < $markup['value'] ) {
								$markup_amount = round($total * ($markup['value']/100));
								$markup_total += $markup_amount;
							}
						}
					}
				}
			}
			$final_total = $total = $total + $markup_total;

			$sum += $final_total;
		}
	}
	wp_reset_postdata();

	return eman_number_format($sum);
}


/**
 * eManager User List by Role
 */

add_shortcode( 'users', 'user_directory' );
function user_directory( $atts )
{
	extract( shortcode_atts( array(
		'role' => 'turner'
	), $atts, 'user_directory' ) );

	// prepare arguments
	$args  = array(
		'role' => $role,
		// order results by display_name
		'orderby' => 'display_name',
	);

	$output = '<div class="panel-group" id="accordion">';
	// Create the WP_User_Query object
	$wp_user_query = new WP_User_Query($args);
	// Get the results
	$users = $wp_user_query->get_results();
	// Check for results
	if ( ! empty($users) )
	{
		// loop trough each author
		foreach ( $users as $user )
		{
			// get all the user's data
			$user_info = get_userdata($user->ID);

			$phone       = $user_info->phone;
			$showphone   = $phone       ? '<strong>Phone</strong>: <a href="tel:' . $user_info->phone . '">' . $user_info->phone . '</a><br>' : '';

			$credentials = $user_info->credentials;
			$showcreds   = $credentials ? '<strong>Credentials</strong>: ' . $user_info->credentials . '<br>' : '';

			ob_start();
?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?php echo $user->ID; ?>">
							<?php echo $user_info->first_name; ?> <?php echo $user_info->last_name; ?> 
							<small><?php echo get_the_title($user_info->company); ?></small>
						</a>
					</h4>
				</div>
				<div id="collapse-<?php echo $user->ID; ?>" class="panel-collapse collapse">
					<div class="panel-body">
						<p>
							<strong>Email</strong>: <a href="mailto:<?php echo $user_info->user_email; ?>">
								<?php echo $user_info->user_email; ?>
							</a><br />
							<?php echo $showphone; ?>
							<?php echo $showcreds; ?>
						</p>
					</div>
				</div>
			</div>
<?php
			$output .= ob_get_clean();
		}

	}
	else
	{
		$output .= 'No Users found';
	}
	$output .= '</div>';

	wp_reset_postdata();

	return $output;
}