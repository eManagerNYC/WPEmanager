<?php

/**
 * Support the ajax update
 */
add_action( 'wp_ajax_labortypes_by_company', 'eman_ajax_labortypes_by_company' );
function eman_ajax_labortypes_by_company()
{
	if ( empty($_POST['company_id']) ) {
		return false;
	}

	$labortypes = new WP_Query( array(
		'post_type'  => 'em_labortypes',
		'meta_key'   => 'company',
		'meta_value' => $_POST['company_id']
	) );

	$output  = '<option value="null">- Select -</option>';

	if ( $labortypes->have_posts() ) : while ( $labortypes->have_posts() ) : $labortypes->the_post(); 
		$output .= '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
	endwhile; endif; wp_reset_query();

	echo $output;
	die;
}