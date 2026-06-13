<?php

/**
 * Output formatted field values
 *
 * @author  Jake Snyder
 * @return  string Formatted value
 */
function eman_field_value( $field, $post )
{
	if ( empty($field['type']) ) { return false; }# || empty($field['value'])

	$type      = $field['type'];
	$value     = $field['value'];

	$output = '';

	if ( in_array($field['name'], array('classification_breakdown','employee_breakdown','materials','equipment')) )
	{
		return call_user_func_array( array('emanager_summary', $field['name']), array($field, $post) );
	}
	elseif ( 'website' == $field['name'] )
	{
		return eman_get_field($field['name'], $post->ID);
	}
	elseif ( 'createdby' == $field['name'] )
	{
		$name = eman_users_name($post->post_author);
        return $name;
	}
	elseif ( 'reviewer' == $field['name'] )
	{
		$name = emanager_bic::get_bic( $post, 'display_name' );
		return $name;
	} elseif ( 'bic_company' == $field['name'] )
	{
		$user = emanager_bic::get_bic( $post, 'ID' );
		return  emanager_post::user_company( $user );
	}
	elseif ( 'company' == $field['name'] )
	{
		if ( is_array($value) ) $value = $value[0];
		if ( is_object($value) ) {
			$output = $value->post_title;
		} elseif ( is_numeric($value) ) {
			$output = get_the_title($value);# self::post_company($value);
		} else {
			$output = $value;
		}
	}
	elseif ( 'status' == $field['name'] )
	{
		$status = emanager_post::status($post, 'simple');
		if ( 'Recommended' == $status )
		{
			$reviews = new WP_Query( array(
				'post_type' => 'em_reviews',
				#'fields' => 'id=>parent',
				'posts_per_page' => -1,
				'order' => 'DESC',
				'orderby'=> 'date',
				'tax_query'     => array(
					array(
						'taxonomy' => 'em_status',
						'field'    => 'slug',
						'terms'    => 'recommend',
					)
				),
				'meta_query' => array(
					array(
						'key' => 'reviewed_id',
						'value' => $post->ID,
						'compare' => '=',
					)
				)
			) );
			$status .= ' ('.  ( $reviews->posts ? $reviews->found_posts : 1 ) .')';
		}
		return $status;
	}
	elseif  ( 'noc_total' == $field['name'] )
	{
		$contractors_and_estimate = eman_get_field('contractors_and_estimate', $post->ID);
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
		return eman_number_format($total);
	}
	elseif ( 'signature' == $field['name'] )
	{
        $output = $value . '<div class="signature-date">
                                   Submitted Date: <span class="date">' . get_the_date() . '</span><br />
                            </div>';
                            #Last Modified Date: <span class="date">' . get_the_modified_date() . '</span>
	}
	elseif ( 'contract_company' == $field['name'] )
	{
		$contract_id = $post->contract;
		$output = ( $contract_id ) ? get_the_title($contract_id) : '';
		if ( $output )
		{
			$company_id = get_post_meta($contract_id, 'company', true);
			$company    = get_the_title($company_id);
			$output .= ( $company ) ? " / $company" : '';
		}
	}
	elseif ( 'true_false' == $type )
	{
		$output = ( $value ) ? 'Yes' : 'No';
	}
	elseif ( 'date_picker' == $type )
	{
		$output = ( $value ) ? date_i18n(get_option('date_format'), strtotime($value)) : '';
	}
	elseif ( 'date_modified' == $field['name'] )
	{
		$review = emanager_post::latest_review( $post );
		if ( $review ) {
			$value = $review->post_modified;
		} else {
			$value = $post->post_modified;
		}
		$output = ( $value ) ? date_i18n('m/d/Y', strtotime($value)) : '';
	}
	elseif ( 'post_object' == $type )
	{
		$output = ( is_object($value) ) ? apply_filters( 'the_title', $value->post_title ) : '';
	}
	elseif ( 'taxonomy' == $type )
	{
		$term = get_term($value, $field['taxonomy']);
		$output = ( is_object($term) ) ? $term->name : '';
	}
	elseif ( 'relationship' == $type )
	{
		if ( ! empty($value) )
		{
			$output = '';
			foreach ( $value as $post )
			{
				$output .= $post->post_title . '<br />';
			}
		}
	}
	elseif ( 'user' == $type )
	{
		if ( ! empty($value) )
		{
			$output = eman_users_name( $value ) . '<br />';
		}
	}
	elseif ( 'repeater' == $type )
	{
		foreach ( $value as $row )
		{
			$count=0;
			foreach ( $field['sub_fields'] as $sub_field )
			{
				$sub_field['value'] = $row[$sub_field['name']];
				$value = eman_field_value($sub_field, $post);

				$output .= $value . ($value && ! $count ? ':' : '') . ' ';
				$count++;
			}
		}
	}
	elseif ( 'select' == $type || 'checkbox' == $type || 'radio' == $type )
	{
		$output = '';

		if ( is_array($value) ) $value = $value[0];

		$choices = $field['choices'];
		if ( $value && is_array($choices) && ! empty($choices[$value]) )
		{
			$output = $choices[$value];
		}
	}
	elseif ( 'textarea' == $type )
	{
		$output = '<p>' . preg_replace('|<br />\s*<br />|', '</p><p>', $value) . '</p>';
	}
	elseif ( is_array($value) )
	{
		$output = implode(', ', $value);
	}
	elseif ( is_object($value) )
	{
		$output = $value->post_title;
	}
	elseif ( isset($field['prepend']) && '$' === $field['prepend'] )
	{
		if ( 'em_invoice' == $post->post_type ) {
			$output = '$' . number_format($value, 2);
		} else {
			$output = '$' . eman_number_format( $value );
		}
	}
	elseif ( 'file' == $type && is_string($value) )
	{
		$name   = basename($value);
		$url    = $value;
		$output = "<a href=\"$url\" target=\"_blank\">$name</a>";
	}
	elseif ( $value )
	{
		$output = $value;
	}

	return $output;
}