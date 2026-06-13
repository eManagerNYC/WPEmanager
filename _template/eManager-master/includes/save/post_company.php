<?php

/**
 * Add the user's company to post when a company does not already exist
 */
add_action( 'save_post', 'update_post_company', 999 );
function update_post_company( $post_id )
{

	/**
	 * If a sub contractor, add company
	 */
	if ( eman_check_role('sub') )
	{
		$current_user = wp_get_current_user();
		$company      = $current_user->get('company');
		if ( is_array($company) ) {
			$company = $company[0];
		}

		if ( $company ) {
			update_post_meta( $post_id, 'company', $company );
		}
	}

	/**
	 * Make updates to the company as needed
	 */
	$company = emanager_post::company( $post_id, true );
	if ( ! $company && ! empty($_POST['fields']) )
	{
		foreach ( $_POST['fields'] as $key => $value )
		{
			$field = get_field_object($key, $post_id);
			if ( 'company' == $field['name'] ) {
				$company = $value;
			}
		}
	}

	// If is an issue, update the company to match contract instead of author
	if ( 'em_issue' == get_post_type($post_id) )
	{
		if ( $contract_id = get_post_meta($post_id, 'contract', true) )
		{
			if ( $contract_company_id = get_post_meta($contract_id, 'company', true) ) {
				update_post_meta($post_id, 'company', $contract_company_id);
			}
		}
	}

	if ( ! $company )
	{
		$post_author = get_post_field( 'post_author', $post_id );
		$company     = get_user_meta( $post_author, 'company', true );
		update_post_meta($post_id, 'company', $company);
	}
}
/**/