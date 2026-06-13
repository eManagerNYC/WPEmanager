<?php

/**
 * Icons
 */
$add_icon = 'plus-circle';


/**
 * PCO
 */
	$args = array(
		array(
			'url'   => site_url('/noc/pco/'),
			'icon'  => 'sign-in',
			'title' => 'PCO Requests'
		)
	);
	if ( ! eman_check_role('owner') ) :
		$args[] = array(
			'url'   => site_url('/noc/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Request'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$pcorequests = eman_dash_noflip( 'PCOs', '#pcorequest', $args );
#} else {
	$pcorequests = eman_dash_flipper( 'PCOs', '#pcorequest', 'sign-in', $args );
#}


/**
 * NOC
 */
	$name = 'NOC';
	if ( false !== strpos( $_SERVER['SERVER_NAME'], 'nyuk') ) {
		$name = 'PCO';
	}
	$args = array(
		array(
			'url'   => site_url('/noc/'),
			'icon'  => eman_post_types('em_noc', 'icon'),
			'title' => $name . 's'
		)
	);
#if ( eman_mobile_detect()->isiOS() ) {
#	$nocs = eman_dash_noflip( $name . 's', '#nocs', $args );
#} else {
	$nocs = eman_dash_flipper( $name . 's', '#' . strtolower($name) . 's', 'random', $args );
#}

/**
 * PCO Directive
 */
	$args = array(
		array(
			'url'   => site_url('/pcod/'),
			'icon'  => 'sign-out',
			'title' => 'Directives'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/pcod/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Directive'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$directives = eman_dash_noflip( 'Directives', '#pcodirective', $args );
#} else {
	$directives = eman_dash_flipper( 'Directives', '#pcodirective', 'sign-out', $args );
#}


/**
 * Tickets
 */
	$args = array(
		array(
			'url'   => site_url('/tickets/'),
			'icon'  => eman_post_types('em_tickets', 'icon'),
			'title' => 'Tickets'
		)
	);
	if ( ! eman_check_role('owner') ) :
		$args[] = array(
			'url'   => site_url('/tickets/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Ticket'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$tickets = eman_dash_noflip( 'Tickets', '#tickets', $args );
#} else {
	$tickets = eman_dash_flipper( 'Tickets', '#tickets', 'edit', $args );
#}


/**
 * DCR
 */
	$args = array(
		array(
			'url'   => site_url('/dcr/'),
			'icon'  => eman_post_types('em_dcr', 'icon'),
			'title' => 'Daily Reports'
		)
	);
	if ( ! eman_check_role('owner') ) :
		$args[] = array(
			'url'   => site_url('/dcr/add/'),
			'icon'  => $add_icon,
			'title' => 'Add DCR'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$dcrs = eman_dash_noflip( 'DCRs', '#dcr', $args );
#} else {
	$dcrs = eman_dash_flipper( 'DCRs', '#dcr', 'calendar', $args );
#}

/**
 * INVOICE
 */
	$args = array(
		array(
			'url'   => site_url('/invoice/'),
			'icon'  => eman_post_types('em_invoice', 'icon'),
			'title' => 'Invoices'
		),
		array(
			'url'   => site_url('/invoice/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Invoice'
		),
	);
	$invoices = eman_dash_flipper( 'Invoices', '#invoices', 'bar-chart', $args );



/**
 * Requests for Information
 */
	$args = array(
		array(
			'url'   => site_url('/rfis/'),
			'icon'  => eman_post_types('em_rfis', 'icon'),
			'title' => 'RFIs'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/rfi/add/'),
			'icon'  => $add_icon,
			'title' => 'Add RFI'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$rfis = eman_dash_noflip( 'RFIs', '#rfis', $args );
#} else {
	$rfis = eman_dash_flipper( 'RFIs', '#rfis', 'question-circle', $args );
#}

/**
 * AL/COR Letters
 */
	$args = array(
		array(
			'url'   => site_url('/letter/'),
			'icon'  => eman_post_types('em_letter', 'icon'),
			'title' => 'Letters'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/letter/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Letter'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$letters = eman_dash_noflip( 'Letters', '#letters', $args );
#} else {
	$letters = eman_dash_flipper( 'Letters', '#letters', 'archive', $args );
#}


/**
 * Issues
 */
	$args = array(
		array(
			'url'   => site_url('/issues/'),
			'icon'  => eman_post_types('em_issue', 'icon'),
			'title' => 'Issues'
		)
	);
	#if ( ! eman_check_role('owner') ) :
		$args[] = array(
			'url'   => site_url('/issue/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Issue'
		);
	#endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$issues = eman_dash_noflip( 'Issues', '#issues', $args );
#} else {
	$issues = eman_dash_flipper( 'Issues', '#issues', 'list-ol', $args );
#}


/**
 * Inspections
 */
	$args = array(
		array(
			'url'   => site_url('/inspections/'),
			'icon'  => eman_post_types('em_inspections', 'icon'),
			'title' => 'Inspections'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/inspections/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Inspection'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$inspections = eman_dash_noflip( 'Inspections', '#inspections', $args );
#} else {
	$inspections = eman_dash_flipper( 'Inspections', '#inspections', 'check-square-o', $args );
#}


/**
 * Observations
 */
	$args = array(
		array(
			'url'   => site_url('/observations/'),
			'icon'  => eman_post_types('em_observation', 'icon'),
			'title' => 'Observations'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/observation/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Observation'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$observations = eman_dash_noflip( 'Observations', '#observations', $args );
#} else {
	$observations = eman_dash_flipper( 'Observations', '#observations', 'eye', $args );
#}


/**
 * Action Items
 */
	$args = array(
		array(
			'url'   => site_url('/actionitems/'),
			'icon'  => eman_post_types('em_actionitem', 'icon'),
			'title' => 'Action Items'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/actionitem/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Action Item'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$actionitems = eman_dash_noflip( 'Action Items', '#actionitem', $args );
#} else {
	$actionitems = eman_dash_flipper( 'Action Items', '#actionitem', 'flag', $args );
#}


/**
 * Meeting Minutes
 */
	$args = array(
		array(
			'url'   => site_url('/meetings/'),
			'icon'  => eman_post_types('em_meeting', 'icon'),
			'title' => 'Meetings'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/meeting/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Meeting'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$meetings = eman_dash_noflip( 'Meetings', '#meeting', $args );
#} else {
	$meetings = eman_dash_flipper( 'Meetings', '#meeting', 'list-alt', $args );
#}


/**
 * Activities
 */
	$args = array(
		array(
			'url'   => site_url('/activities/'),
			'icon'  => eman_post_types('em_activity', 'icon'),
			'title' => 'Activities'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/activity/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Activity'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$activities = eman_dash_noflip( 'Activities', '#activity', $args );
#} else {
	$activities = eman_dash_flipper( 'Activities', '#activity', 'exchange', $args );
#}


/**
 * Photo Gallery
 */
	$args = array(
		array(
			'url'   => site_url('/photos/'),
			'icon'  => eman_post_types('em_photos', 'icon'),
			'title' => 'Photo Gallery'
		)
	);
	if ( eman_check_role('turner') ) :
		$args[] = array(
			'url'   => site_url('/photos/add/'),
			'icon'  => $add_icon,
			'title' => 'Add Photos'
		);
	endif;
#if ( eman_mobile_detect()->isiOS() ) {
#	$photos = eman_dash_noflip( 'Photos', '#photos', $args );
#} else {
	$photos = eman_dash_flipper( 'Photos', '#photos', 'camera-retro', $args );
#}


/**
 * Weather
 */
$proj_city = str_replace(' ', '_', eman_get_field('proj_city', 'option'));
if ( ! $proj_city ) {
	$proj_city = 'New_York';
}
$proj_state = eman_get_field('proj_state', 'option');
if ( ! $proj_state ) {
	$proj_state = 'NY';
}
$weather        = ( ! shortcode_exists( 'weather' ) ) ? '' : do_shortcode('[weather city="' . $proj_city . '" state="' . $proj_state . '" days="2"]');


$settings       = eman_dash_icon_only( 'Settings', site_url('/settings/'), 'gears' );
$doc_project    = eman_dash_icon_only( 'Project', site_url('/documents/'), 'folder-open' );
$doc_turner     = eman_dash_icon_only( 'Turner', site_url('/documents/turner/'), 'folder-open' );
$doc_owner      = eman_dash_icon_only( 'Owner', site_url('/documents/owner/'), 'folder-open' );
$doc_sub        = eman_dash_icon_only( 'Subcontractors', site_url('/documents/sub/'), 'folder-open' );
$doc_consultant = eman_dash_icon_only( 'Consultants', site_url('/documents/consultant/'), 'folder-open' );
$doc_company    = eman_dash_icon_only( 'Company', site_url('/documents/company/%s'), 'folder-open' );
$doc_companies  = eman_dash_icon_only( 'Companies', site_url('/documents/companies/'), 'folder-open' );
