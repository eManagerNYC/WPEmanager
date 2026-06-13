<?php
if ( function_exists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_custom-profile',
		'title' => 'User Profile',
		'fields' => array (
			array (
				'key' => 'field_52b7b2d3cb8b1',
				'label' => 'Phone',
				'name' => 'phone',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_52aebdaa5991e',
				'label' => 'Company',
				'name' => 'company',
				'type' => 'post_object',
				'required' => 0,
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
				'key' => 'field_52b7b2dfcb8b2',
				'label' => 'Credentials & Awards',
				'name' => 'credentials',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_52b7b2f8cb8b3',
				'label' => 'Terms & Conditions',
				'name' => 'terms',
				'type' => 'true_false',
				'message' => '',
				'default_value' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'ef_user',
					'operator' => '==',
					'value' => 'all',
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
		'menu_order' => 3010,
	));

	register_field_group(array (
		'id' => 'acf_custom-register',
		'title' => 'Register',
		'fields' => array (
			array (
				'key' => 'field_52b76135896a4',
				'label' => 'Phone',
				'name' => 'phone',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_52b7615c896a5',
				'label' => 'Company',
				'name' => 'company',
				'type' => 'post_object',
				'required' => 1,
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
				'key' => 'field_52b761b4896a6',
				'label' => 'Credentials & Awards',
				'name' => 'credentials',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'formatting' => 'br',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'ef_user',
					'operator' => '!=',
					'value' => 'all',
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
		'id' => 'acf_terms',
		'title' => 'Terms',
		'fields' => array (
			array (
				'key' => 'field_52b761dc896a7',
				'label' => 'Terms & Conditions',
				'name' => 'terms',
				'type' => 'true_false',
				'instructions' => 'I agree to the <a href="http://turneremanager.com/terms.php" target="_blank">Terms & Conditions</a> and <a href="http://turneremanager.com/privacy.php" target="_blank">Privacy Policy</a>',
				'required' => 1,
				'message' => '',
				'default_value' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'ef_user',
					'operator' => '!=',
					'value' => 'all',
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