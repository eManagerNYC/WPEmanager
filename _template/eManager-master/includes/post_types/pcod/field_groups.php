<?php

if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_pcod',
		'title' => 'PCO Directives',
		'fields' => array (
			array (
				'key' => 'field_53a1ab26932ee',
				'label' => 'NOC#',
				'name' => 'pcod_noc_number',
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
				'key' => 'field_53a1aba5932ef',
				'label' => 'PCO#',
				'name' => 'pcod_pco_number',
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
				'key' => 'field_53a1abb8932f0',
				'label' => 'Issue To',
				'name' => 'pcod_issueto',
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
				'key' => 'field_53a1abcf932f1',
				'label' => 'Scope Description',
				'name' => 'pcod_description',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_53a1abe2932f2',
				'label' => 'Backup',
				'name' => 'pcod_backup',
				'type' => 'repeater',
				'sub_fields' => array (
					array (
						'key' => 'field_53a1ac61effff',
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
				'key' => 'field_53a1abfb932f3',
				'label' => 'Sign & Confirm',
				'name' => '',
				'type' => 'message',
				'message' => '<hr class="divider divider-before" />
								<h2>Sign & Confirm</h2>
								<hr class="divider divider-after" />',
			),
			array (
				'key' => 'field_53a1abfb932f4',
				'label' => 'Signature',
				'name' => 'pcod_signature',
				'type' => 'signature',
				'required' => 0,#(is_admin() ? 0 : 1),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_pcod',
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