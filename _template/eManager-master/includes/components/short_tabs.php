<?php

/**
 * Tabs (from Bootstrap)
 *
 * @param array $atts Standard WordPress shortcode attributes
 * @param string $content The enclosed content
 * @return string $output Content to output for shortcode
 */
add_shortcode( 'tabs', 'eman_shortcode_tabs' );
function eman_shortcode_tabs( $atts, $content = null )
{
	$default = array(
		'style' 		=> 'framed', 		// framed, open
		'nav'			=> 'tabs',			// tabs, pills
		'height' 		=> '' 				// Fixed height for tabs, true or false
	);
	extract( shortcode_atts($default, $atts) );

	$output = '';

	// Since we use the $atts to loop through and
	// display the tabs, we need to remove the other
	// data, now that we've extracted it to other
	// variables.
	if ( isset($atts['style']) )  { unset($atts['style']); }
	if ( isset($atts['nav']) )    { unset($atts['nav']); }
	if ( isset($atts['height']) ) { unset($atts['height']); }

	// Verify style
	if ( ! in_array($style, array('framed','open')) ) { $style = 'framed'; }

	// Verify tabs
	if ( ! in_array($style, array('tabs','pills')) ) { $nav = 'tabs'; }

	// Height
	$height = ( 'true' == $height ) ? 1 : 0;

	$id    = uniqid( 'tabs_' . rand() );
	$num   = count($atts) - 1;
	$i     = 1;
	$tabs  = array();
	$names = array();

	$content = apply_filters( 'eman/the_content', $content );

	// Setup options
	$options = array(
		'setup'  => array(
			'num'	=> $num,
			'style' => $style,
			'nav'	=> $nav,
			'names' => array()
		),
		'height' => $height,
	);

	if ( is_array($atts) && 0 < count($atts) )
	{
		foreach ( $atts as $key => $tab )
		{
			$names['tab_'.$i] = $tab; // for theme framework prior to v2.5
			$tab_content = explode( '[/'.$key.']', $content );
			$tab_content = explode( '['.$key.']', $tab_content[0] );
			$tabs['tab_'.$i] = array(
				'title'   => $tab,
				'type'    => 'raw', // for theme framework prior to v2.5
				'raw'     => $tab_content[1], // for theme framework prior to v2.5
				'content' => array(
					'type'       => 'raw',
					'raw'        => $tab_content[1],
					'raw_format' => 1
				)
			);
			$i++;
		}
	}
	else
	{
		$output .= '<p class="warning">No tabs found</p>';
	}

	if ( ! $output )
	{
		$options['setup']['names'] = $names;
		foreach ( $tabs as $tab_id => $tab ) {
			$options[$tab_id] = $tab;
		}

		$output .= '<div class="element element-tabs element_tabs">';
		$output .= eman_create_tabs( $id, $options );
		$output .= '</div><!-- .element (end) -->';
	}

	return $output;
}

/**
 * Display set of tabs.
 *
 * @since 2.0.0
 *
 * @param array $id unique ID for tab set
 * @param array $options all options for tabs
 * @return string $output HTML output for tabs
 */
function eman_create_tabs( $id, $options )
{
	$nav        = array( 'tabs', 'above' ); // Backup for someone updating who doesn't have this saved yet.
	$navigation = '';
	$content    = '';
	$output     = '';

	// Tabs or pills?
	$nav_type   = $options['setup']['nav'];

    // Backup
    if ( 'tabs' != $nav_type && 'pills' != $nav_type ) {
    	$nav_type = 'tabs';
    }

	// Container classes
	$classes = 'tabbable';

	if ( $options['height'] ) {
		$classes .= ' fixed-height';
	}

	$classes .= ' tb-tabs-'.$options['setup']['style'];

	if ( 'pills' == $nav_type ) {
		$classes .= ' tb-tabs-pills';
	}

	// Navigation
	$i = 0;
	$class = null;
	$navigation .= '<ul class="nav nav-'.$nav_type.'">';
	foreach ( $options['setup']['names'] as $key => $name )
	{
		if ( $i == 0 ) {
			$class = 'active';
		}

		$navigation .= '<li class="'.$class.'"><a href="#'.$id.'-'.$key.'" data-toggle="'.str_replace('s', '', $nav_type).'" title="'.stripslashes($name).'">'.stripslashes($name).'</a></li>';
		$class = null;

		$i++;
	}
	$navigation .= '</ul>';

	// Tab content
	$i = 0;
	$content = '<div class="tab-content">';

	foreach ( $options['setup']['names'] as $key => $name )
	{
		$class = '';
		if ( $i == '0' ) {
			$class = ' active';
		}

		$content .= '<div id="'.$id.'-'.$key.'" class="tab-pane fade'.$class.' in clearfix">';

		switch ( $options[$key]['type'] )
		{
			// External Page
			case 'page' :

				// Get WP internal ID for the page
				$page_id = get_page_by_path($options[$key]['page'], 'page');
				

				// Use WP_Query to retrieve external page. We do it
				// this way to allow certain primary query-dependent
				// items such as galleries to work properly.
				$the_query = new WP_Query( 'page_id='.$page_id );

				// Standard WP loop, even though there should only be
				// a single post (i.e. our external page).
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$content .= get_the_content();
				}

				// Reset Post Data
				wp_reset_postdata();
				break;

			// Raw content textarea
			case 'raw' :

				// Only negate simulated the_content filter if the option exists AND it's
				// been unchecked. This is for legacy purposes, as this feature
				// was added in v2.1.0
				if ( isset( $options[$key]['raw_format'] ) && ! $options[$key]['raw_format'] ) {
					$content .= do_shortcode( stripslashes($options[$key]['raw']) ); // Shortcodes only
				} else {
					$content .= stripslashes($options[$key]['raw']);
				}
				break;

			// Floating Widget Area
			case 'widget' :

				if ( ! empty( $options[$key]['sidebar'] ) )
				{
					$content .= '<div class="widget-area">';
					ob_start();
					dynamic_sidebar( $options[$key]['sidebar'] );
					$content .= ob_get_clean();
					$content .= '</div><!-- .widget-area (end) -->';
				}
				break;

		}
		$content .= '</div><!-- #' . $id . '-'.$key.' (end) -->';
		$i++;
	}
	$content .= '</div><!-- .tab-content (end) -->';

	// Construct final output
	$output  = '<div class="'.$classes.'">';
	$output .= $navigation;
	$output .= $content;
	$output .= '</div><!-- .tabbable (end) -->';

	return $output;
}