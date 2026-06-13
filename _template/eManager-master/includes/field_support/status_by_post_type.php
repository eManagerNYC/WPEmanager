<?php

function eman_get_filters( $post_type )
{
	$fields = false;

	if ( 'em_tickets' == $post_type )
	{
		// Choices for status select
		$ps = get_terms( 'em_status', 'orderby=name' ); //&hide_empty=0&fields=names
		$status_choices = array( 'null' => '- Status -' );
        $acceptable_choices = array('draft', 'executed', 'revise', 'superintendent', 'manager', 'approved');
        $remove = array('ready','recommend','submitted','void');
		foreach ( $ps as $p )
		{
            if ( in_array($p->slug, $acceptable_choices) ) {
                $status_choices[$p->slug] = $p->name;
            }
		}

		// Choices for companies select
		$ps = get_posts( $args = array(
			'post_type'      => 'em_companies',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'fields'         => 'post_name=>post_title'
		) ); wp_reset_postdata();
		$company_choices = array(
			'null' => '- Company -'//,'turner' => 'Turner'
		);
		foreach ( $ps as $p )
		{
			$company_choices[$p->ID] = $p->post_title;
		}

        asort($company_choices);

		// Set up the fields
		$fields = array(
			array(
				'key' => 'pco_number',
				'label' => 'PCO#',
				'name' => 'filter-pco_number',
				'_name' => 'pco_number',
				'type' => 'text',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-pco_number',
				'class' => 'pco_number',
				'placeholder' => 'PCO#',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'default_value' => 0,
				'allow_null' => 1,
				'value' => '',
			),
			array(
				'key' => 'status',
				'label' => 'Status',
				'name' => 'filter-status',
				'_name' => 'status',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-status',
				'class' => 'status select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $status_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
			array(
				'key' => 'charge',
				'label' => 'Charge',
				'name' => 'filter-charge',
				'_name' => 'charge',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-charge',
				'class' => 'charge select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => array( 'null' => '- Charge -', 'billed' => 'Billed', 'paid' => 'Paid' ),
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
		);
		if ( ! eman_check_role('sub') )
		{
			$fields[] = array(
				'key' => 'company',
				'label' => 'Company',
				'name' => 'filter-company',
				'_name' => 'company',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-company',
				'class' => 'company select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $company_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			);
		}

	}
	elseif ( 'em_noc' == $post_type )
	{
		// Choices for status select
		$ps = get_terms( 'em_status', 'orderby=name' ); //&hide_empty=0&fields=names
		$status_choices = array( 'null' => '- Status -' );
        $acceptable_choices = array('submitted', 'recommend', 'executed');
		foreach ( $ps as $p )
		{
            if ( in_array($p->slug, $acceptable_choices) ) {
                $status_choices[ $p->slug ] = $p->name;
            }
		}

		// Set up the fields
		$fields = array(
			array(
				'key' => 'status',
				'label' => 'Status',
				'name' => 'filter-status',
				'_name' => 'status',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-status',
				'class' => 'status select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $status_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
			array(
				'key' => 'importance',
				'label' => 'Importance',
				'name' => 'filter-importance',
				'_name' => 'importance',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-importance',
				'class' => 'importance select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => array (
					'' => '- Importance -',
					'High' => 'High',
					'Medium' => 'Medium',
					'Low' => 'Low',
				),
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
/** /
			array(
				'key' => 'value_from',
				'label' => '',
				'name' => 'filter-value_from',
				'_name' => 'value_from',
				'type' => 'text',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-value_from',
				'class' => 'value_from',
				'placeholder' => 'From',
				'prepend' => '$',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'default_value' => 0,
				'allow_null' => 1,
				'value' => '',
			),
			array(
				'key' => 'value_to',
				'label' => '',
				'name' => 'filter-value_to',
				'_name' => 'value_to',
				'type' => 'text',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-value_to',
				'class' => 'value_to',
				'placeholder' => 'To',
				'prepend' => '$',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'default_value' => 0,
				'allow_null' => 1,
				'value' => '',
			),
/**/
		);

	}
	elseif ( 'em_noc_pco' == $post_type ) 
	{
		// Choices for status select
		$ps = get_terms( 'em_status', 'orderby=name' ); //&hide_empty=0&fields=names
		$status_choices = array( '' => '- Status -' );
        $acceptable_choices = array('draft','revise','manager','ready');
		foreach ( $ps as $p )
		{
            if (in_array($p->slug, $acceptable_choices)) {
                $status_choices[ $p->slug ] = $p->name;
            }
		}

		// Get turner users
		$users = get_users( array( 'role' => 'editor' ) );
		/* for multiple roles see this: http://wordpress.stackexchange.com/questions/39315/get-multiple-roles-with-get-users */
		$user_choices = array();
		foreach ( $users as $user )
		{
			$name = eman_users_name( $user );
			if ( $name ) $user_choices[ $user->ID ] = $name;
		}
        asort($user_choices);

		// Get reiewers
		$reviewer_choices  = array('' => '- BIC -') + $user_choices;

		// Get reiewers
		$requester_choices = array('' => '- Requester -') + $user_choices;

        $location_choices = array( '' => '- Location -' );
        $locations = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'em_locations',
            'posts_per_page' => -1,
        ));
        foreach ($locations as $location) {
            $location_choices['location-'.$location->ID] = $location->post_title;
        }
        asort($location_choices);

		// Set up the fields
		$fields = array(
			array(
				'key' => 'status',
				'label' => 'Status',
				'name' => 'filter-status',
				'_name' => 'status',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-status',
				'class' => 'status select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $status_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
			array(
				'key' => 'requester',
				'label' => 'Requester',
				'name' => 'filter-requester',
				'_name' => 'requester',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-requester',
				'class' => 'requester select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $requester_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
			array(
				'key' => 'bic_user',
				'label' => 'BIC',
				'name' => 'filter-bic_user',
				'_name' => 'bic_user',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-bic_user',
				'class' => 'bic_user select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $reviewer_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
			array(
				'key' => 'importance',
				'label' => 'Importance',
				'name' => 'filter-importance',
				'_name' => 'importance',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-importance',
				'class' => 'importance select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => array (
					'' => '- Importance -',
					'High' => 'High',
					'Medium' => 'Medium',
					'Low' => 'Low',
				),
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
			array(
				'key' => 'location',
				'label' => 'Location',
				'name' => 'filter-location',
				'_name' => 'location',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-location',
				'class' => 'location select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $location_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			)
		);

	}
	elseif ( 'em_dcr' == $post_type )
	{
		// Choices for status select
		$ps = get_terms( 'em_status', 'orderby=name' ); //&hide_empty=0&fields=names
		$status_choices = array( '' => '- Status -' );
        $acceptable_choices = array('draft','revise','superintendent','approved','approve','void');
		foreach ( $ps as $p )
		{
            if (in_array($p->slug, $acceptable_choices)) {
                $status_choices[ $p->slug ] = $p->name;
            }
		}

		// Choices for companies select
		$ps = get_posts( $args = array(
			'post_type'      => 'em_companies',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'fields'         => 'post_name=>post_title'
		) ); wp_reset_postdata();
		$company_choices = array(
			'' => '- Company -',
//			'turner' => 'Turner',
		);
		foreach ( $ps as $p )
		{
			$company_choices[ sanitize_title($p->post_title) ] = $p->post_title;
		}
        asort($company_choices);

		// Set up the fields
		$fields = array(
			array(
				'key' => 'status',
				'label' => 'Status',
				'name' => 'filter-status',
				'_name' => 'status',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-status',
				'class' => 'status select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $status_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
/** /
			array(
				'key' => 'work_date',
				'label' => 'Date',
				'name' => 'filter-work_date',
				'_name' => 'work_date',
                'type' => 'date_picker',
                'order_no' => 1,
                'instructions' => '',
                'required' => 0,
                'id' => 'filter-work_date',
                'class' => 'work_date',
                'conditional_logic' => array(
                    'status' => 0,
                    'allorany' => 'all',
                    'rules' => 0,
                ),
                'default_value' => 0,
                'allow_null' => 0,
                'multiple' => 0,
                'value' => '',
                'date_format' => 'yymmdd',
                'display_format' => get_option('date_format'),
                'first_day' => 1,

			)
/**/
		);
		if ( ! eman_check_role('sub') )
		{
			$fields[] = array(
				'key' => 'company',
				'label' => 'Company',
				'name' => 'filter-company',
				'_name' => 'company',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-company',
				'class' => 'company select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $company_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			);
		}

	}
	elseif ( 'em_letter' == $post_type )
	{
		// Choices for status select
		$ps = get_terms( 'em_status', 'orderby=name' ); //&hide_empty=0&fields=names
		$status_choices = array( '' => '- Status -' );
        $acceptable_choices = array('pending','recommend','approved','rejected');
		foreach ( $ps as $p )
		{
            if (in_array($p->slug, $acceptable_choices)) {
                $status_choices[ $p->slug ] = $p->name;
            }
		}

		// Set up the fields
		$fields = array(
			array(
				'key' => 'status',
				'label' => 'Status',
				'name' => 'filter-status',
				'_name' => 'status',
				'type' => 'select',
				'order_no' => 1,
				'instructions' => '',
				'required' => 0,
				'id' => 'filter-status',
				'class' => 'status select',
				'conditional_logic' => array(
					'status' => 0,
					'allorany' => 'all',
					'rules' => 0,
				),
				'choices' => $status_choices,
				'default_value' => 0,
				'allow_null' => 0,
				'multiple' => 0,
				'value' => '',
			),
		);
	}

	return $fields;
}
