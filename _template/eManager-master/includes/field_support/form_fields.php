<?php

/**
 * Used to sort ACF field keys returned from a post
 */
function eman_sort_by_order_no( $a, $b )
{
	if ( $a['order_no'] == $b['order_no'] )
		return 0;
	else
		return $a['order_no'] < $b['order_no'] ? -1 : 1;
}

/**
 * Get all fields for a post
 *
 * @author  Jake Snyder
 * @param	array	$group_ids	The acf group ids for forms associated with the post
 * @param	int		$post_id	The post id
 * @return  array	Fields
 */
function eman_form_fields( $group_ids, $post_id )
{
	if ( ! is_array($group_ids) ) {
		$group_ids = array($group_ids);
	}

	$segment_fields = array();

	foreach ( $group_ids as $gid )
	{
		$segment_field_keys   = get_post_custom_keys($gid);
		$segment_fields_array = array();
		if ( is_array($segment_field_keys) )
		{
			foreach ( $segment_field_keys as $key => $value )
			{
				if ( ! stristr($value,'field_') ) {
					continue;
				}
		
				$field = get_field_object($value, $post_id);
				if ( ! empty($field['name']) ) {
					$segment_fields_array[$field['name']] = get_field_object($value, $post_id);
				} else {
					$segment_fields_array[$field['key']] = get_field_object($value, $post_id);
				}
			}
		}

		usort($segment_fields_array, 'eman_sort_by_order_no');
		$segment_fields = array_merge($segment_fields, $segment_fields_array);
	}

	// Get all field groups from ACF
	$acf_fields = apply_filters('acf/get_field_groups', array());

	// Loop through the field groups to narrow it down to ours and get the fields
	if ( is_array($acf_fields) )
	{
		foreach( $acf_fields as $acf_field )
		{
			// only add the chosen field groups
			if ( ! in_array($acf_field['id'], $group_ids) ) {
				continue;
			}

			// load options
			$acf_field['options'] = apply_filters('acf/field_group/get_options', array(), $acf_field['id']);

			// load fields
			$fields = apply_filters('acf/field_group/get_fields', array(), $acf_field['id']);

			$segment_fields = array_merge($segment_fields, $fields);
		}
	}

	$final_segments = array();
	foreach ( $segment_fields as $field )
	{
		if ( ! empty($field) )
		{
			if ( 'message' != $field['type'] ) {
				$field['value'] = eman_get_field($field['name'], $post_id);
			}

			if ( ! empty($field['name']) ) {
				$key = $field['name'];
			} else {
				$key = $field['key'];
			}

			$final_segments[$key] = $field;
		}
	}

	return $final_segments;
}