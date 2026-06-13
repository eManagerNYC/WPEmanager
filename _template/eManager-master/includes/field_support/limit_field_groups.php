<?php

/**
 * Test field group locations on the front end
 */
function acf_limit_field_groups( $field_groups, $run_extras=true )
{
	$output = array();
	$options = array();
	$options['post_type'] = get_query_var('post_type');

	if ( 'em_issue' == $options['post_type'] )
	{
		if ( $field_groups )
		{
			foreach ( $field_groups as $key => $group_slug )
			{
				// load location
				$location = apply_filters('acf/field_group/get_location', array(), $group_slug);

				// vars
				$add_box = true;

				if ( ! empty($location) )
				{
					$add_box = false;

					foreach ( $location as $group_id => $group )
					{
						// start off as true, this way, any rule that doesn't match will change this varaible to false
						$match_group = true;

						if ( is_array($group) )
						{
							foreach ( $group as $rule_id => $rule )
							{
								// Hack for ef_media => now post_type = attachment
								if( $rule['param'] == 'ef_media' )
								{
									$rule['param'] = 'post_type';
									$rule['value'] = 'attachment';
								}

								if ( ! $run_extras )
								{
									// Make sure the current user is the author of the post
									if ( 'user_type' == $rule['param'] && in_array($rule['value'], array('author','company')) ) {
										continue;
									}
								}

								// $match = true / false
								$match = apply_filters( 'acf/location/rule_match/' . $rule['param'] , false, $rule, $options );

								if ( ! $match )
								{
									// Make sure the current user is the author of the post
									if ( 'user_type' == $rule['param'] && 'author' == $rule['value'] )
									{
										if ( $GLOBALS['post']->post_author == get_current_user_id() ) {
											$match = true;
										}
									}

									// Make sure the current user is the same company as the post
									if ( 'user_type' == $rule['param'] && 'company' == $rule['value'] )
									{
										$post_company = $GLOBALS['post']->company;
										$user_comapny = get_user_meta(get_current_user_id(), 'company', true);
										if ( $post_company == $user_comapny ) {
											$match = true;
										}
									}
								}

								if ( ! $match ) {
									$match_group = false;
								}
							}
						}

						// all rules must have matched!
						if ( $match_group )
						{
							$add_box = true;
						}
					}
				}

				// add ID to array	
				if ( $add_box )
				{
					$output[] = $group_slug;
				}
			}
		}

		return $output;
	}
	return $field_groups;
}