<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_ssid',
		'title' => 'SSID',
		'fields' => array (
			array (
				'key' => 'field_53a441a1223a6',
				'label' => 'SSI#',
				'name' => 'ssi_number',
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
				'key' => 'field_53a441b7223a7',
				'label' => 'PCO#',
				'name' => 'pco_number',
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
				'key' => 'field_53a441ca223a8',
				'label' => 'SSI Package',
				'name' => 'ssi_package',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_ssi',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_53a44212223a9',
				'label' => 'Issue Date',
				'name' => 'issue_date',
				'type' => 'date_picker',
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
			),
			array (
				'key' => 'field_53a44261223aa',
				'label' => 'Package Direction',
				'name' => 'package_direction',
				'type' => 'radio',
				'instructions' => 'Price Only
	Price & Proceed',
				'choices' => array (
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_53a44281223ab',
				'label' => 'Pricing Direction',
				'name' => 'turner_pricing_direction',
				'type' => 'checkbox',
				'choices' => array (
					'Lump Sum Proposal' => 'Lump Sum Proposal',
					'T&M' => 'T&M',
					'T&M Not to Exceed' => 'T&M Not to Exceed',
				),
				'default_value' => '',
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_53a442ba1fadb',
				'label' => 'Proposal Due Date',
				'name' => 'proposal_due_date',
				'type' => 'date_picker',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_53a44281223ab',
							'operator' => '==',
							'value' => 'Lump Sum Proposal',
						),
					),
					'allorany' => 'all',
				),
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
			),
			array (
				'key' => 'field_53a442d91fadc',
				'label' => 'Not to Exceed Value',
				'name' => 'not_to_exceed',
				'type' => 'number',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_53a44281223ab',
							'operator' => '==',
							'value' => 'T&M Not to Exceed',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '$',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
			),
			array (
				'key' => 'field_53a443021fadd',
				'label' => 'Issue to (Contracts)',
				'name' => 'issue_to_contracts',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_contracts',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 1,
			),
			array (
				'key' => 'field_53a443cc1fade',
				'label' => 'Additional Information',
				'name' => 'additional_information',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => 4,
				'formatting' => 'br',
			),
			array (
				'key' => 'field_53a443dc1fadf',
				'label' => 'Backup',
				'name' => 'backup',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_53a443f41fae0',
						'label' => 'File',
						'name' => 'file',
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
				'key' => 'field_53a444031fae1',
				'label' => 'Signature',
				'name' => 'signature',
				'type' => 'signature',
				'required' => 0,#(is_admin() ? 0 : 1),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_ssid',
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