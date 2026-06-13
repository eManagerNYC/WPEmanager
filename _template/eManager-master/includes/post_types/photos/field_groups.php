<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_photos',
		'title' => 'Photos',
		'fields' => array (
			array (
				'key' => 'field_53b1b9be042e9',
				'label' => 'Date Taken',
				'name' => 'date_taken',
				'type' => 'date_picker',
				'required' => 1,
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
			),
			array (
				'key' => 'field_53b1b9cd042ea',
				'label' => 'Location',
				'name' => 'location',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_locations',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 1,
			),
			array (
				'key' => 'field_53b1b9e4042eb',
				'label' => 'Photos',
				'name' => 'photos',
				'type' => 'gallery',
				'required' => 1,
				'preview_size' => 'thumbnail',
				'library' => 'uploadedTo',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_photos',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}