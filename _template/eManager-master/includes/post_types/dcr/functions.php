<?php

/**
 * Create custom post titles for specific post types
 */
add_action( 'acf/save_post', 'eman_dcr_save', 20 );
function eman_dcr_save( $post_id )
{
	$post_type = get_post_type($post_id);
	if ( 'em_dcr' == $post_type )
	{
		$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : false;

		/**
		 * Update dcr titles
		 */
		$post_data = array(
			'ID' => $post_id,
		);
		$fields = array();
		foreach ( $_POST['fields'] as $field_key => $field_value )
		{
			$field = get_field_object($field_key, $post_id);
			$fields[$field['name']] = $field_value;
		}

		$dcr_labor_breakdown = get_option('options_dcr_labor_breakdown');
		$labor_unit_hours = 'hours';
		$labor_unit_days  = 'workers';
		$man_hours = ( 'Man-hours' == $dcr_labor_breakdown ) ? true : false;

		$count = 0;
		if ( 'employee' == $fields['laborbd'] && ! empty($fields['employee_breakdown']) )
		{
			$count = count($fields['employee_breakdown']);
		}
		elseif ( ! empty($fields['classification_breakdown']) )
		{
			foreach ( $fields['classification_breakdown'] as $row ) :
				$labor        = array_shift($row);
				if ( $man_hours )
					$mancount = array_shift($row);
				$notes        = array_pop($row);
				$pco_number   = array_pop($row);

				foreach ( $row as $item )
				{
					if ( $item && is_numeric($item) ) {
						$count += $item;
					}
				}
			endforeach;
		}
		$post       = get_post($post_id);
		$author_id  = $post->post_author;
		#$company    = get_user_meta($author_id, 'company', true);
		#$company    = emanager_post::user_company( $post->post_author );
		$company_id  = get_post_meta($post->ID, 'company', true);
		$company     = get_the_title($company_id);
		#$company_id = $company[0];#get_the_title($company_id) 

		$post_data['post_title'] = date('Y-m-d', strtotime($fields['work_date'])) . ', ' . $company . ', ' . $count . ' ' . ($man_hours ? $labor_unit_hours : $labor_unit_days);
		$post_data['post_name']  = sanitize_title($post_data['post_title']);

		wp_update_post( $post_data );

		// If incident, send messages
		if ( 'Yes' == $fields['incidents_on_site'] )
		{
			$users = eman_get_field('incident_notification', 'options');

			$args = array(
				'subject'    => "Incident on site",
				'message'    => 'View DCR <a href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . "</a>\n\r\n\r",
				'to'         => false,
				'private'    => true,
			);

			foreach ( $users as $user )
			{
				$args['to'] = $user;
				do_action( 'sewn/messenger/add_message', $args );
			}
		}
	}
}


function dcr_manhour_total($post_id) {
	$manhours = 0;

	$hours = ('Man-hours' == get_option('options_dcr_labor_breakdown')) ? 1 : 8;

	if (
		($rows = eman_get_field('classification_breakdown', $post_id)) && 
		is_array($rows)
	) {

		foreach ($rows as $row) {
			$types = array('male_minority', 'male_non-minority', 'female_minority', 'female_non-minority');
			foreach ($types as $type) {
				if ( !empty($row[$type])) {
					$manhours += ($row[$type] * $hours);
				}
			}
		}
	}
	return $manhours;
}

function dcr_minority_summary() {
	$types = array('male_minority', 'male_non-minority', 'female_minority', 'female_non-minority');
	$dcrs = get_posts(array(
		'post_type' => 'em_dcr',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	));
	$data = array();
	foreach ($dcrs as $dcr) {
		$month = date('Y-m', strtotime($dcr->post_date));
		$company_id  = get_post_meta($dcr->ID, 'company', true);
		$company     = get_the_title($company_id);
		if ( !isset($data[$company])) {
			$data[$company] = array();
		}
		if ( !isset($data[$company][$month])) {
			$data[$company][$month] = array_fill_keys($types, 0);
		}
		if (
			($rows = eman_get_field('classification_breakdown', $dcr->ID)) && 
			is_array($rows)
		) {
			foreach ($rows as $row) {
				foreach ($types as $type) {
					if ( !empty($row[$type])) {
						$data[$company][$month][$type] += $row[$type];
					}
				}
			}
		}
	}
	return $data;
}
function dcr_calendar_summary() {
	$types = array('male_minority', 'male_non-minority', 'female_minority', 'female_non-minority');
	$dcrs = get_posts(array(
		'post_type' => 'em_dcr',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	));
	$data = array();
	foreach ($dcrs as $dcr) {
		$year = date('Y', strtotime($dcr->post_date));
		$month = date('n', strtotime($dcr->post_date));
		$company_id  = get_post_meta($dcr->ID, 'company', true);
		$company     = get_the_title($company_id);
		if ( !isset($data[$year])) {
			$data[$year] = array_fill(1, 12, array());
			$data[$year]['total'] = array();
		}
		if ( !isset($data[$year]['total'][$company])) {
			$data[$year]['total'][$company] = 0;
		}
		if ( !isset($data[$year][$month][$company])) {
			$data[$year][$month][$company] = 0;
		}
		if (
			($rows = eman_get_field('classification_breakdown', $dcr->ID)) && 
			is_array($rows)
		) {
			foreach ($rows as $row) {
				foreach ($types as $type) {
					if ( !empty($row[$type])) {
						$data[$year][$month][$company] += $row[$type];
						$data[$year]['total'][$company] += $row[$type];
					}
				}
			}
		}
	}
	return $data;
}




/*
 * returns lists of employees, labor types, equipment, materials indexed by company
 */
add_action('wp_ajax_company_assoc', 'company_assoc');
function company_assoc()
{
    $assoc = array(
		'em_labortypes' => array(),
		'em_employees'  => array(),
		'em_equipment'  => array(),
		'em_materials'  => array(),
	);

	foreach ( $assoc as $posttype => &$list )
	{
		$posts = get_posts( array(
			'post_type'      => $posttype,
			'post_status'    => 'publish',
			'posts_per_page' => -1
		) );

		foreach ( $posts as $post )
		{
			$company = eman_get_field('company', $post->ID);
			if ( $company && is_object($company) )
			{
				if ( ! isset($list[$company->ID]) ) {
					$list[$company->ID] = array();
				}

				do {
					if ( 'em_labortypes' == $posttype )
					{
						if ( -1 == eman_get_field('active_labortype', $post->ID) ) {
							break;
						}
						if (
							($validfrom = eman_get_field('valid_from', $post->ID)) &&
							(current_time('Ymd') < $validfrom)
						) {
							break;
						}
						if (
							($validto = eman_get_field('valid_to', $post->ID)) &&
							(current_time('Ymd') > $validto)
						) {
							break;
						}
					}
					array_push($list[$company->ID], $post->ID);
				} while ( false );
			}
		}
	}

    echo json_encode($assoc);
    die; // retrieved via ajax
}
