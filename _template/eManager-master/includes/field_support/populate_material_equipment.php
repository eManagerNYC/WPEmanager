<?php

/**
 * Materials & Equipment repeaters
 */
add_action( 'wp_ajax_populate_material_equipment', 'eman_ajax_populate_material_equipment' );
function eman_ajax_populate_material_equipment()
{
	if ( empty($_POST['post_id']) ) {
		return false;
	}

	$output = array( 'status' => 0 );

	if ( $post = get_post($_POST['post_id']) )
	{
		$output = array( 'status' => 1 );
		if ( 'em_equipment' == $post->post_type )
		{
			$output['usage']        = 1;
			$output['measure']      = $post->duration;
			$output['rental_price'] = $post->rate;
		}
		elseif ( 'em_materials' == $post->post_type )
		{
			$output['amount_used']  = 1;
			$output['measure']      = $post->measure;
			$output['price']        = $post->price;
		}
	}

	echo json_encode($output);
	die;
}