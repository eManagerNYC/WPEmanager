<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_locations',
		'title' => 'Locations',
		'fields' => array (
			array (
				'key' => 'field_529fde4be13ab',
				'label' => 'Parent',
				'name' => 'parent',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_locations',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a5f3e8c8d89',
				'label' => 'Area Name',
				'name' => 'area_name',
				'type' => 'text',
				'required' => 1,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_529fded8e13ad',
				'label' => 'Location Plan (PDF, PNG)',
				'name' => 'plan',
				'type' => 'file',
				'save_format' => 'url',
				'library' => 'uploadedTo',
			),
			array (
				'key' => 'field_529fdf45e13ae',
				'label' => 'BIM Model',
				'name' => 'model',
				'type' => 'file',
				'instructions' => 'Upload JSON file',
				'save_format' => 'url',
				'library' => 'uploadedTo',
			),/* Remove support for Sketchfab *//*
			array (
				'key' => 'field_529fdf88e13af',
				'label' => 'Sketchfab ID',
				'name' => 'sketchfab',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),*/
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_locations',
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