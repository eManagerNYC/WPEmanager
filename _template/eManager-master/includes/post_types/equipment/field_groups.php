<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_equipment',
		'title' => 'Equipment',
		'fields' => array (
			array (
				'key' => 'field_52a55a6a1b0ed',
				'label' => 'Company',
				'name' => 'company',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_companies',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a55ab41b0ef',
				'label' => 'Unit of Measure',
				'name' => 'duration',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_52a55a981b0ee',
				'label' => 'Rate (per Unit of Measure)',
				'name' => 'rate',
				'type' => 'number',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
			),
			array (
				'key' => 'field_52a55a981b0fg',
				'label' => 'Equipment Rate Sheet',
				'name' => 'ratesheet_equipment',
				'type' => 'file',
				'instructions' => 'Upload PDF',
				'save_format' => 'url',
				'library' => 'uploadedTo',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_equipment',
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