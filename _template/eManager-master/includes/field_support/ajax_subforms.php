<?php

add_action( 'wp_ajax_process_subforms', 'ajax_subforms' );
function ajax_subforms()
{
	if ( empty($_POST['fields']) || empty($_POST['post_type']) ) {
		echo json_encode( array( 'error' => "Some data is missing" ) );
		die;
	}

	if ( empty($_POST['post_title']) ) {
		echo json_encode( array( 'error' => "Please provide a title" ) );
		die;
	}

	if ( 'em_employees' != $_POST['post_type'] )
	{
		foreach ( $_POST['fields'] as $k => $v )
		{
			if ( ! $v ) {
				echo json_encode( array( 'error' => "Please fill in all the fields" ) );
				die;
			}
		}
	}

	$output = array();

	$post_id = wp_insert_post( array(
		'post_title'  => $_POST['post_title'],
		'post_type'   => $_POST['post_type'],
		'post_status' => 'publish',
	) );

	if ( ! $post_id ) {
		echo json_encode( array( 'error' => "There was a problem saving, please try again" ) );
		die;
	}

	// allow for custom save
	$post_id = apply_filters( 'acf/pre_save_post', $post_id );

	// save the data
	do_action('acf/save_post', $post_id);
/** /
	foreach ( $_POST['fields'] as $k => $v )
	{
		update_field( $k, $v, $post_id );
	}
/**/
	echo json_encode( array(
		'id' => $post_id,
		'title' => $_POST['post_title'],
	) );
	die;
}
