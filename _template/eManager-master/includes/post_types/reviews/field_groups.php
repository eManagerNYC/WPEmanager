<?php

if ( function_exists("register_field_group") )
{


	$additional_directions = array();
	if (
		('Yes' == get_option('options_additional_direction')) && 
		($listcount = (int)get_option('options_direction_list'))
	) {
		for ($i=0; $i<$listcount; $i++) {
			$opt = get_option("options_direction_list_{$i}_direction");
			$additional_directions[sanitize_title($opt).$i] = $opt;
		}
	}

	register_field_group(array (
		'id' => 'acf_reviews-for-owner',
		'title' => 'Review for owner',
		'fields' => array (
			array (
				'key' => 'field_52b4f7b2ed8d6',
				'label' => 'Direction',
				'name' => 'direction',
				'type' => 'radio',
				'required' => (is_admin() ? 0 : 1),
				'choices' => array (
					'proceed' => 'Proceed While Pricing',
					'price' => 'Price Only',
					'no' => 'Do Not Proceed',
				) + $additional_directions,
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
		),
		'location' => array (
/** /
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews',
					'order_no' => 0,
					'group_no' => 0,
				),
				array (
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => '12',
					'order_no' => 1,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_noc',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
/**/
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 1050,
	));

	register_field_group(array (
		'id' => 'acf_reviews-signature-and-send-to',
		'title' => 'Review signature and send to',
		'fields' => array (
/** /
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
/**/
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

	register_field_group(array (
		'id' => 'acf_reviews-signature',
		'title' => 'Review signature',
		'fields' => array (
			array (
				'key' => 'field_52b35db4d6235',
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
					'operator' => '!=',
					'value' => '11',
					'order_no' => 1,
					'group_no' => 0,
				),
				array (
					'param' => 'taxonomy',
					'operator' => '!=',
					'value' => '4',
					'order_no' => 2,
					'group_no' => 0,
				),
				array (
					'param' => 'taxonomy',
					'operator' => '!=',
					'value' => '9',
					'order_no' => 3,
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
		'menu_order' => 1052,
	));

	register_field_group(array (
		'id' => 'reviewed_id',
		'title' => 'Reviewed Post',
		'fields' => array (
			array (
				'key' => 'field_reviewed_post',
				'label' => 'Reviewed Post',
				'name' => 'reviewed_id',
				'type' => 'post_object',
				'required' => (is_admin() ? 0 : 1),
				'post_type' => array (
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 0,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_reviews',
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
		'id' => 'bic_users',
		'title' => 'BIC User',
		'fields' => array (
			array (
				'key' => 'field_bic_user_main',
				'label' => 'BIC User',
				'name' => 'bic_user',
				'type' => 'user',
				'required' => (is_admin() ? 0 : 1),
				'role' => array (
				),
				'field_type' => 'select',
				'allow_null' => 1,
			),
		),
		'location' => array (
/** /
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'em_noc',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
/**/
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
		'id' => 'bic_turner',
		'title' => 'Change BIC: Turner',
		'fields' => array (
			array (
				'key' => 'field_bic_turner',
				'label' => 'Person',
				'name' => 'send_to',
				'type' => 'user',
				'required' => (is_admin() ? 0 : 1),
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
		'id' => 'bic_custom_approvers',
		'title' => 'Change BIC: Turner/Approvers',
		'fields' => array (
			array (
				'key' => 'field_bic_custom_approvers',
				'label' => 'Person',
				'name' => 'send_to',
				'type' => 'select',
				'required' => (is_admin() ? 0 : 1),
				'role' => array (
					0 => 'editor',
					1 => 'administrator',
				),
				'choices' => array (
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
}
