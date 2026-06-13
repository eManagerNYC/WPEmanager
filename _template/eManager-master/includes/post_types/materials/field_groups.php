<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_materials',
		'title' => 'Materials',
		'fields' => array (
			array (
				'key' => 'field_52a5591ad96ca',
				'label' => 'Company Name',
				'name' => 'company',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_companies',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a55997d96cc',
				'label' => 'Unit of Measure',
				'name' => 'measure',
				'type' => 'text',
				'instructions' => '',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_52a55978d96cb',
				'label' => 'Rate (per Unit of Measure)',
				'name' => 'price',
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
				'key' => 'field_52a55978d96cd',
				'label' => 'Material Rate Sheet',
				'name' => 'ratesheet_material',
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
					'value' => 'em_materials',
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