<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_observation',
		'title' => 'Field Notes',
		'fields' => array (
			array (
				'key' => 'field_53e0e9ad5bdb7',
				'label' => 'Date & Time',
				'name' => 'datetime',
				'type' => 'date_time_picker',
				'required' => 1,
				'show_date' => 'true',
				'date_format' => 'm/d/y',
				'time_format' => 'h:mm tt',
				'show_week_number' => 'false',
				'picker' => 'select',
				'save_as_timestamp' => 'true',
				'get_as_timestamp' => 'true',
			),
			/*
    array (
			'key' => 'field_53f37a635204d',
			'label' => 'Tags',
			'name' => 'tags',
			'type' => 'radio',
			'choices' => array (
				'Tagged' => 'Tagged',
				'Untagged' => 'Untagged',
			),
			'other_choice' => 0,
			'save_other_choice' => 0,
			'default_value' => 'Untagged',
			'layout' => 'horizontal',
		),
		array (
			'key' => 'field_53f37aca5204e',
			'label' => 'Location',
			'name' => 'location',
			'type' => 'post_object',
			'conditional_logic' => array (
				'status' => 1,
				'rules' => array (
					array (
						'field' => 'field_53f37a635204d',
						'operator' => '==',
						'value' => 'Tagged',
					),
				),
				'allorany' => 'all',
			),
			'post_type' => array (
				0 => 'em_locations',
			),
			'taxonomy' => array (
				0 => 'all',
			),
			'allow_null' => 0,
			'multiple' => 0,
		),
		array (
			'key' => 'field_53f37ad95204f',
			'label' => 'Company(s)',
			'name' => 'companys',
			'type' => 'repeater',
			'conditional_logic' => array (
				'status' => 1,
				'rules' => array (
					array (
						'field' => 'field_53f37a635204d',
						'operator' => '==',
						'value' => 'Tagged',
					),
				),
				'allorany' => 'all',
			),
			'sub_fields' => array (
				array (
					'key' => 'field_53f37b2309bef',
					'label' => 'Company',
					'name' => 'company',
					'type' => 'post_object',
					'column_width' => 30,
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
					'key' => 'field_53f37b3109bf0',
					'label' => 'Headcount',
					'name' => 'headcount',
					'type' => 'number',
					'column_width' => 10,
					'default_value' => 0,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'min' => '',
					'max' => '',
					'step' => '',
				),
				array (
					'key' => 'field_53f37b5609bf1',
					'label' => 'Scope',
					'name' => 'scope',
					'type' => 'text',
					'column_width' => 60,
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
		*/
			array (
				'key' => 'field_53e0eba250ca7',
				'label' => 'Field Notes',
				'name' => 'f_notes',
				'type' => 'wysiwyg',
				'required' => 1,
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
			array (
				'key' => 'field_53f37b7709bf2',
				'label' => 'Attachment(s)',
				'name' => 'attachments',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_53f37b8f09bf3',
						'label' => 'File',
						'name' => 'file',
						'type' => 'file',
						'column_width' => '',
						'save_format' => 'url',
						'library' => 'uploadedTo',
					),
				),
				'row_min' => '1',
				'row_limit' => '',
				'layout' => 'table',
				'button_label' => 'Add File',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_observation',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}