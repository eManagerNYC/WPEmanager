<?php

if ( function_exists("register_field_group") )
{
    if ( ! ($tier1 = get_option('options_location_tier_1')) ) {
        $tier1 = 'Building';
    }
    if ( ! ($tier2 = get_option('options_location_tier_2')) ) {
        $tier2 = 'Floor';
    }
    if ( ! ($tier3 = get_option('options_location_tier_3')) ) {
        $tier3 = 'Subarea';
    }

	register_field_group(array (
		'id' => 'acf_issue-item',
		'title' => 'Issues Item',
		'fields' => array (
			array (
				'key' => 'field_533da63ab07cc',
				'label' => 'Issue Type',
				'name' => 'issue_type',
				'type' => 'taxonomy',
				'taxonomy' => 'em_punchlist',
				'field_type' => 'select',
				'allow_null' => 1,
				'load_save_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
			array (
				'key' => 'field_533da797b07ce',
				'label' => 'Contract',
				'name' => 'contract',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_contracts',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_533da63ab07cc',
							'operator' => '==',
							'value' => '69',
						),
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_issue',
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

	register_field_group(array (
		'id' => 'acf_issue-owner',
		'title' => 'Issues Owner',
		'fields' => array (
			array (
				'key' => 'field_533da7b5b07cf',
				'label' => 'Location',
				'name' => 'scope',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_533da7b59c2e',
						'label' => $tier1,
						'name' => 'building',
						'type' => 'select',
						'column_width' => '15',
						'choices' => array (
						),
						'default_value' => '',
						'allow_null' => 0,
						'multiple' => 0,
					),
					array (
						'key' => 'field_533da7b59c2f',
						'label' => $tier2,
						'name' => 'floor',
						'type' => 'select',
						'column_width' => '15',
						'choices' => array (
						),
						'default_value' => '',
						'allow_null' => 0,
						'multiple' => 0,
					),
					array (
						'key' => 'field_533da7b59c30',
						'label' => $tier3,
						'name' => 'location',
						'type' => 'select',
						'column_width' => '15',
						'choices' => array (
						),
						'default_value' => '',
						'allow_null' => 0,
						'multiple' => 0,
					),
					array (
						'key' => 'field_533da7b59c31',
						'label' => 'Description',
						'name' => 'description',
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
				'row_min' => 1,
				'row_limit' => '',
				'layout' => 'table',
				'button_label' => 'Add Row',
			),
			array (
				'key' => 'field_53a2eb64d9d9d',
				'label' => 'Equipment ID#',
				'name' => 'equipment_id',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'em_buildingequipment',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_533da63ab07cc',
							'operator' => '==',
							'value' => '69',
						),
					),
				),
				'default_value' => array (
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_533da754b07cd',
				'label' => 'Issue Description',
				'name' => 'description',
				'type' => 'textarea',
				'required' => (is_admin() ? 0 : 1),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_53a2ecdfc4bb9',
				'label' => 'Attachment(s)',
				'name' => 'attachments',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_53a2ecfec4bba',
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
			array (
				'key' => 'field_53a2ec40d9d9e',
				'label' => 'BIC',
				'name' => 'turner_responsible',
				'type' => 'user',
				'role' => array (
					0 => 'all',
				),
				'field_type' => 'select',
				'allow_null' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'user_type',
					'operator' => '==',
					'value' => 'company',
				),
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_issue',
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

	register_field_group(array (
		'id' => 'acf_issue-turner',
		'title' => 'Issues (Turner Fields)',
		'fields' => array (
			array (
				'key' => 'field_53cd4e08175bc',
				'label' => 'Type of Work',
				'name' => 'type_of_work',
				'type' => 'radio',
				'choices' => array (
					'Contract' => 'Contract',
					'Change Order' => 'Change Order',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'Contract',
				'layout' => 'horizontal',
			),
			array (
				'key' => 'field_53cd4e2d175bd',
				'label' => 'Display PCO',
				'name' => 'display_pco',
				'type' => 'radio',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_53cd4e08175bc',
							'operator' => '==',
							'value' => 'Change Order',
						),
					),
					'allorany' => 'all',
				),
				'choices' => array (
					'Yes' => 'Yes',
					'No' => 'No',
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => 'Yes',
				'layout' => 'horizontal',
			),
			array (
				'key' => 'field_53cd4e4f175be',
				'label' => 'PCO Number',
				'name' => 'pco_number',
				'type' => 'number',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_53cd4e08175bc',
							'operator' => '==',
							'value' => 'Change Order',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
			),
			array (
				'key' => 'field_53cd4fdb8c80e',
				'label' => 'Internal Notes',
				'name' => 'internal_notes',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => 4,
				'formatting' => 'br',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'user_type',
					'operator' => '==',
					'value' => 'administrator',
				),
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_issue',
				),
			),
			array (
				array (
					'param' => 'user_type',
					'operator' => '==',
					'value' => 'editor',
				),
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_issue',
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 1,
	));

	register_field_group(array (
		'id' => 'acf_issue-status',
		'title' => 'Change Status',
		'fields' => array (
			array (
				'key' => 'field_53a44050a1123',
				'label' => 'Status',
				'name' => 'status',
				'type' => 'select',
				'choices' => array (),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'em_issue',
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
		'menu_order' => 1021,
	));

	register_field_group(array (
		'id' => 'acf_issue-signature',
		'title' => 'Issues Signature',
		'fields' => array (
			array (
				'key' => 'field_533da63ab2e76',
				'label' => 'Divider',
				'name' => '',
				'type' => 'message',
				'message' => '<hr class="divider divider-before" />',
			),
			array (
				'key' => 'field_533da63ab2e78',
				'label' => 'Signature',
				'name' => 'signature',
				'type' => 'signature',
				'required' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_issue2',
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
		'menu_order' => 1025,
	));
}