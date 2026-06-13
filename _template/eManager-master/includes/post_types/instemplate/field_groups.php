<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_inspection-templates',
		'title' => 'Inspection Templates',
		'fields' => array (
			array (
				'key' => 'field_53a2f1ce68196',
				'label' => 'Inspection Type',
				'name' => 'inspection_type',
				'type' => 'taxonomy',
				'taxonomy' => 'category',
				'field_type' => 'select',
				'allow_null' => 0,
				'load_save_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
			array (
				'key' => 'field_533da8c589ad9',
				'label' => 'Questions',
				'name' => 'questions',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_53a3189792ee2',
						'label' => 'Question',
						'name' => 'question',
						'type' => 'text',
						'column_width' => '',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'html',
						'maxlength' => '',
					),
				),
				'row_min' => '',
				'row_limit' => '',
				'layout' => 'table',
				'button_label' => 'Add Row',
			),
			array (
				'key' => 'field_53a2f1ef68197',
				'label' => 'Box Types',
				'name' => 'box_types',
				'type' => 'taxonomy',
				'taxonomy' => 'em_clboxes',
				'field_type' => 'checkbox',
				'allow_null' => 0,
				'load_save_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_instemplate',
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