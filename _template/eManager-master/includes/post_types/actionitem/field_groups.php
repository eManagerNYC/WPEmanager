<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_actionitem',
		'title' => 'Action Items',
		'fields' => array (
			array (
				'key' => 'field_53b1ba0d8291c',
				'label' => 'Date Required',
				'name' => 'date_required',
				'type' => 'date_picker',
				'required' => 1,
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
			),
			array (
				'key' => 'field_54352647bfcba',
				'label' => 'Importance',
				'name' => 'importance',
				'type' => 'select',
				'choices' => array (
					'Low' => 'Low',
					'Medium' => 'Medium',
					'High' => 'High',
				),
				'default_value' => 'Medium',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_53b1ba246e1b3',
				'label' => 'Attachments',
				'name' => 'attachments',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_53b1ba246e1b4',
						'label' => 'Attachment(s)',
						'name' => 'attachments',
						'type' => 'file',
						'column_width' => '',
						'save_format' => 'object',
						'library' => 'uploadedTo',
					),
				),
				'row_min' => '',
				'row_limit' => '',
				'layout' => 'table',
				'button_label' => 'Add Row',
			),
			array (
				'key' => 'field_5445048bc7303',
				'label' => 'Responsible Company',
				'name' => 'responsible_company',
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
				'key' => 'field_544504a6c7304',
				'label' => 'Status',
				'name' => 'status',
				'type' => 'select',
				'choices' => array (
					'Open' => 'Open',
					'On Hold' => 'On Hold',
					'In Review' => 'In Review',
					'Closed' => 'Closed',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_544504c1c7305',
				'label' => 'Visibility',
				'name' => 'visibility',
				'type' => 'radio',
				'choices' => array (
					'Private (Only me, the author)' => 'private',
					'Public (check all that apply)' => 'public',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_544504fdc7306',
				'label' => 'Visibility Company',
				'name' => 'visibility_company',
				'type' => 'post_object',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_544504c1c7305',
							'operator' => '==',
							'value' => 'Public (check all that apply)',
						),
					),
					'allorany' => 'all',
				),
				'post_type' => array (
					0 => 'em_companies',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_actionitem',
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