<?php


/**
 * Populate buildings with top level locations
 */
add_filter( 'acf/load_field/name=building', 'eman_load_field_building');
function eman_load_field_building( $field )
{
	$field['choices'] = array( 'null' => "- Select -" );

	$locations = new WP_Query( array(
		'post_type'      => 'em_locations',
		'posts_per_page' => -1,
		'meta_key'       => 'area_name',
		#'meta_value'     => 'null',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query' => array(
			array(
				'key'     => 'parent',
				'value'   => 'null',
				'compare' => '=',
			)
		)
	) );

	if ( $locations->have_posts() ) : while ( $locations->have_posts() ) : $locations->the_post();
		$field['choices'][ get_the_ID() ] = get_the_title();
	endwhile; endif; wp_reset_query();

	return $field;
}

/**
* Populate floors with second level locations after the building is selected
*/
add_filter( 'wp_ajax_load_field_floor', 'eman_load_field_floor');
function eman_load_field_floor()
{
	if ( empty($_POST['post_id']) ) {
		return false;
	}

	$locations = new WP_Query( array(
		'post_type'      => 'em_locations',
		'posts_per_page' => -1,
		'meta_key'       => 'area_name',
		#'meta_value'     => 'null',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query' => array(
			array(
				'key'     => 'parent',
				'value'   => $_POST['post_id'],
				'compare' => '=',
			)
		)
	) );

	$ticket_id = ( isset($_POST['ticket_id']) ) ? $_POST['ticket_id'] : 0;
	$key       = ( isset($_POST['key']) )       ? $_POST['key']       : 0;
	$selected_floor = 0;
	if ( $ticket_id && $key )
	{
		$scope = eman_get_field('scope', $ticket_id);
		if ( ! empty($scope[$key-1]['floor']) ) {
			$selected_floor = $scope[$key-1]['floor'];
		}
	}

	$default = '<option value="null">- Select -</option>';
	$options = '';
	if ( $locations->have_posts() ) : while ( $locations->have_posts() ) : $locations->the_post();
		$options .= '<option value="' . get_the_ID() . '"' . ($selected_floor==get_the_ID() ? ' selected="selected"' : '') . '>' . get_post_meta(get_the_ID(), 'area_name', true) . '</option>';
	endwhile; endif; wp_reset_query();

	if ( $options ) {
		echo $default . $options;
	}
	die;
}

/**
* Search locations or rooms, the third+ level locations
*/
add_filter( 'wp_ajax_load_field_location', 'eman_load_field_location');
function eman_load_field_location()
{
	if ( empty($_POST['post_id']) ) {
		return false;
	}

	$ticket_id = ( isset($_POST['ticket_id']) ) ? $_POST['ticket_id'] : 0;
	$key       = ( isset($_POST['key']) ) ? $_POST['key'] : 0;
	$selected_location = 0;
	/**/
	if ( $ticket_id && $key )
	{
		$scope = eman_get_field('scope', $ticket_id);

		if ( ! empty($scope[$key-1]['location']) ) {
			$selected_location = $scope[$key-1]['location'];
		}
	}
	/**/

	$default = '<option value="null">- Select -</option>';
	$options = eman_posts_children( $_POST['post_id'], $selected_location );

	if ( $options ) {
		echo $default . $options;
	}
	die;
}

function eman_posts_children( $parent_id, $selected_location )
{
	static $ancestors = array();
	$output = '';

	$posts = new WP_Query( array(
		'post_type'      => 'em_locations',
		'posts_per_page' => -1,
		'meta_key'       => 'area_name',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query' => array(
			array(
				'key'     => 'parent',
				'value'   => $parent_id,
				'compare' => '=',
			)
		)
	) );

	if ( $parent_id != $_POST['post_id'] )
	{
		if ( $posts->have_posts() ) {
			$ancestors[] = $parent_id;
		}
	}

	if ( $posts->have_posts() ) : while ( $posts->have_posts() ) : $posts->the_post();

		if ( $parent_id == $_POST['post_id'] ) {
			$ancestors = array();
		}

		$output .= '<option value="' . get_the_ID() . '"' . ($selected_location==get_the_ID() ? ' selected="selected"' : '') . '>';
		if ( $ancestors && is_array($ancestors) ) : foreach ( $ancestors as $ancestor_id ) :
			$output .= get_post_meta($ancestor_id, 'area_name', true) . ' > ';
		endforeach; endif;

		$output .= get_post_meta(get_the_ID(), 'area_name', true);
		$output .= '</option>';

		if ( $gchildren = eman_posts_children( get_the_ID(), $selected_location ) ) {
			$output .= $gchildren;
		}

	endwhile; endif; wp_reset_query();

	return $output;
}