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
		'id' => 'bic_noc_manager',
		'title' => 'NOC Manager: Change BIC',
		'fields' => array (
			array (
				'key' => 'field_bic_action_noc_manager',
				'label' => 'Action',
				'name' => 'bic_action_noc_manager',
				'type' => 'radio',
				'choices' => array (
					'authorizer' => 'Change PCO Authorizer', // From pco_approvers option
					'turner' => 'Send to Collaborator (before authorizing)', // Turner role
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_52b026ea3fcab_editor',
				'label' => 'Person',
				'name' => 'send_to',
				'type' => 'user',
				'required' => 0,
				'role' => array (
					0 => 'editor',
					1 => 'administrator',
				),
				'field_type' => 'select',
				'allow_null' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_review',
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
		'id' => 'bic_noc_ready',
		'title' => 'NOC Ready: Change BIC',
		'fields' => array (
			array (
				'key' => 'field_bic_action_noc_ready',
				'label' => 'Action',
				'name' => 'bic_action_noc_ready',
				'type' => 'radio',
				'choices' => array (
					'gatekeeper' => 'Assign to Gatekeeper', // From noc_gatekeeper option
					#'authorizer' => 'Assign to PCO Authorizer', // From pco_approvers option
					'turner' => 'Assign to Other Staff (collaborate)', // Turner role
				),
				'other_choice' => 0,
				'save_other_choice' => 0,
				'default_value' => '',
				'layout' => 'vertical',
			),
			array (
				'key' => 'field_52b026ea3fcab_editor',
				'label' => 'Person',
				'name' => 'send_to',
				'type' => 'user',
				'required' => 0,
				'role' => array (
					0 => 'editor',
					1 => 'administrator',
				),
				'field_type' => 'select',
				'allow_null' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_review',
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
		'id' => 'bic_noc_submit',
		'title' => 'NOC Submit: Change BIC',
		'fields' => array (
			array (
				'key' => 'field_bic_role_noc_submit',
				'label' => 'Role',
				'name' => 'bic_action_noc_submit',
				'type' => 'radio',
				'choices' => array (
					'turner' => 'Turner',
					'owner' => 'Owner',
					'owners_rep' => 'Owners Rep',
					'consultant' => 'Consultant',
				),
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52b026ea3fcab_owner',
				'label' => 'Person',
				'name' => 'send_to',
				'type' => 'user',
				'required' => 0,
				'role' => array (
					0 => 'owner',
				),
				'field_type' => 'select',
				'allow_null' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_review',
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
		'id' => 'acf_noc',
		'title' => 'PCO',
		'fields' => array (
			array (
				'key' => 'field_52a88e2c484af',
				'label' => 'Importance',
				'name' => 'importance',
				'type' => 'select',
				'choices' => array (
					'High' => 'High',
					'Medium' => 'Medium',
					'Low' => 'Low',
				),
				'default_value' => 'Low',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a88e77484b1',
				'label' => 'Manager Responsible',
				'name' => 'turner_responsible',
				'type' => 'select',
				'choices' => array (
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
				'required' => (is_admin() ? 0 : 1),
			),
/** /
			array (
				'key' => 'field_52a88e77484b1',
				'label' => 'Manager Responsible',
				'name' => 'turner_responsible',
				'type' => 'user',
				'role' => array (
					0 => 'editor',
					1 => 'administrator',
				),
				'field_type' => 'select',
				'required' => (is_admin() ? 0 : 1),
				'allow_null' => 1,
			),
/**/
			array (
				'key' => 'field_52a88ef5653cc',
				'label' => 'Scope',
				'name' => '',
				'type' => 'message',
				'message' => '<hr class="divider divider-before" />
	<h2>Scope</h2>
	<hr class="divider divider-after" />',
			),
			array (
				'key' => 'field_52eac0nocscope',
				'label' => 'Scope',
				'name' => 'scope',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_52eac327b9c2e',
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
						'key' => 'field_52eac405b9c2f',
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
						'key' => 'field_52eac4c7b9c30',
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
						'key' => 'field_52eac4e0b9c31',
						'label' => 'Scope',
						'name' => 'scope',
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
				'key' => 'field_52a89158653cf',
				'label' => 'Description of Work',
				'name' => 'description',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_52a8917d653d0',
				'label' => 'Start Date',
				'name' => 'start_date',
				'type' => 'date_picker',
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
			),
			array (
				'key' => 'field_52a8919c653d1',
				'label' => 'End Date',
				'name' => 'end_date',
				'type' => 'date_picker',
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
			),
			array (
				'key' => 'field_52a891aa653d2',
				'label' => 'Duration',
				'name' => 'duration',
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
				'key' => 'field_52a891be653d3',
				'label' => 'Direct to Proceed',
				'name' => 'direct_to_proceed',
				'type' => 'select',
				'choices' => array (
					'No' => 'No',
					'Writing' => 'Yes (In Writing)',
					'Verbal' => 'Yes (Verbal Only)',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a89329653d4',
				'label' => 'By Whom?',
				'name' => 'by_whom',
				'type' => 'text',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_52a891be653d3',
							'operator' => '!=',
							'value' => 'No',
						),
					),
					'allorany' => 'all',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_52a8949f653d5',
				'label' => 'Schedule Impact',
				'name' => 'schedule_impact',
				'type' => 'select',
				'choices' => array (
					'Yes' => 'Yes',
					'No' => 'No',
					'Cannot Determine' => 'Cannot Determine',
				),
				'default_value' => 'No',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a89514653d6',
				'label' => 'Impact Duration',
				'name' => 'impact_duration',
				'type' => 'number',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_52a8949f653d5',
							'operator' => '!=',
							'value' => 'No',
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
				'key' => 'field_52a8952c653d7',
				'label' => 'Backup',
				'name' => 'backup',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_52a8953b653d8',
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
				'key' => 'field_52a895a5653d9',
				'label' => 'Work Breakdown',
				'name' => '',
				'type' => 'message',
				'message' => '<hr class="divider divider-before" />
	<h2>Work Breakdown</h2>
	<hr class="divider divider-after" />',
			),
			array (
				'key' => 'field_52a89652653db',
				'label' => 'COR Type',
				'name' => 'cor_type',
				'type' => 'select',
				'choices' => array (
					'External' => 'External',
					'Internal' => 'Internal',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a895fd653da',
				'label' => 'SAP Reason',
				'name' => 'sap_reason',
				'type' => 'select',
				'required' => (is_admin() ? 0 : 1),
				'choices' => array (
					'Allowance' => 'Allowance',
					'Back Charge' => 'Back Charge',
					'Change Condition' => 'Change Condition',
					'Change in Terms & Conditions' => 'Change in Terms & Conditions',
					'Claim' => 'Claim',
					'Code / Regulatory' => 'Code / Regulatory',
					'Consultant Directive' => 'Consultant Directive',
					'Contingency' => 'Contingency',
					'Design Issue' => 'Design Issue',
					'Direct Costs' => 'Direct Costs',
					'Expedite / OT' => 'Expedite / OT',
					'Extend GCs' => 'Extend GCs',
					'GC/GR' => 'GC/GR',
					'Inspection Result' => 'Inspection Result',
					'Owner Directive' => 'Owner Directive',
					'Strike' => 'Strike',
					'Transfer' => 'Transfer',
					'Unbought' => 'Unbought',
					'Unforseen Conditions' => 'Unforseen Conditions',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_52a8974e653df',
				'label' => 'Contractors and Estimate',
				'name' => 'contractors_and_estimate',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_52a8975d653e0',
						'label' => 'Contract',
						'name' => 'contract',
						'type' => 'post_object',
						'column_width' => '',
						'post_type' => array (
							0 => 'em_contracts',
						),
						'taxonomy' => array (
							0 => 'all',
						),
						'allow_null' => 1,
						'multiple' => 0,
					),
					array (
						'key' => 'field_52a897a3653e1',
						'label' => 'Estimated Value',
						'name' => 'estimated_value',
						'type' => 'number',
						'column_width' => '',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array (
						'key' => 'field_52a897b9653e2',
						'label' => 'Funding Source',
						'name' => 'source',
						'type' => 'post_object',
						'column_width' => '',
						'post_type' => array (
							0 => 'em_funding',
						),
						'taxonomy' => array (
							0 => 'all',
						),
						'allow_null' => 1,
						'multiple' => 0,
					),
					array (
						'key' => 'field_52a897df653e3',
						'label' => 'Scope',
						'name' => 'scope',
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
				'button_label' => 'Add Contractor and Estimate',
			),
			array (
				'key' => 'field_52a89827653e4',
				'label' => 'Divider',
				'name' => '',
				'type' => 'message',
				'message' => '<hr class="divider divider-before" />',
			),
			array (
				'key' => 'field_52a89701653dd',
				'label' => 'Special Conditions',
				'name' => 'special_conditions',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_52a89717653de',
				'label' => 'Sign & Confirm',
				'name' => '',
				'type' => 'message',
				'message' => '<hr class="divider divider-before" />
	<h2>Sign & Confirm</h2>
	<hr class="divider divider-after" />',
			),
			array (
				'key' => 'field_52a8984c653e5',
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
					'value' => 'em_noc',
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
		'menu_order' => 1010,
	));

    /*
	 * Autoincrement NOC Number
	 */
    global $wpdb;
    if ( !($maxNOC = $wpdb->get_var("SELECT MAX(CAST(meta_value AS SIGNED)) FROM {$wpdb->prefix}postmeta where meta_key='noc_number' AND meta_value REGEXP '^[0-9]+$'"))) {
        $maxNOC = 0;
    }

	register_field_group(array (
		'id' => 'acf_noc-submit-sendto',
		'title' => 'Send to',
		'fields' => array (
			array (
				'key' => 'field_noc_submit_sendto',
				'label' => 'Send to',
				'name' => 'send_to',
				'type' => 'user',
				'role' => array (
					0 => 'owner',
					1 => 'owners_rep',
					2 => 'consultant',
				),
				'field_type' => 'select',
				'required' => (is_admin() ? 0 : 1),
				'allow_null' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_noc',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 1012,
	));
	register_field_group(array (
		'id' => 'acf_noc-numbers',
		'title' => 'PCO/NOC Numbers',
		'fields' => array (
			array (
				'key' => 'field_52b342acfc4d9',
				'label' => 'PCO Number',
				'name' => 'pco_number',
				'type' => 'text',
				'required' => (is_admin() ? 0 : 1),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_52b342bbfc4da',
				'label' => 'NOC Number',
				'name' => 'noc_number',
				'type' => 'text',
				'required' => (is_admin() ? 0 : 1),
				'default_value' => ++$maxNOC,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_noc',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 1012,
	));

	register_field_group(array (
		'id' => 'acf_noc-signature-and-send-to',
		'title' => 'Review signature and send to',
		'fields' => array (
/**/
			array (
				'key' => 'field_52b026ereview',
				'label' => 'Send to',
				'name' => 'send_to',
				'type' => 'select',
				'choices' => array (
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
				'required' => (is_admin() ? 0 : 1),
			),
/** /
			array (
				'key' => 'field_52b026ea3fcab_editor',
				'label' => 'Send to',
				'name' => 'send_to',
				'type' => 'user',
				'required' => 0,
				'role' => array (
					0 => 'editor',
					1 => 'administrator',
				),
				'field_type' => 'select',
				'allow_null' => 1,
			),
/**/
			array (
				'key' => 'field_52a7696aae46c',
				'label' => 'Signature',
				'name' => 'superintendent_signature',
				'type' => 'signature',
				'required' => 0,#(is_admin() ? 0 : 1),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews2',
					'order_no' => 0,
					'group_no' => 0,
				),
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => '11',
					'order_no' => 1,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews2',
					'order_no' => 0,
					'group_no' => 1,
				),
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => '4',
					'order_no' => 1,
					'group_no' => 1,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews2',
					'order_no' => 0,
					'group_no' => 2,
				),
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => '9',
					'order_no' => 1,
					'group_no' => 2,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 1051,
	));
}
