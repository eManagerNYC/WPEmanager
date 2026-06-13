<?php

$settings_permalink = home_url('/settings/');
$current_user = wp_get_current_user();

add_filter( 'acf/load_value/name=email', 'custom_load_value_user_email', 10, 3 );
	function custom_load_value_user_email( $value, $post_id, $field )
	{
		$current_user = wp_get_current_user();
		return $current_user->user_email;
	}

if ( function_exists('acf_form_head') ) { acf_form_head(); }

get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<?php do_shortcode('[settings_nav]'); ?><br />

			<div id="profile">
				<?php 
				do_action( 'sewn/register/the_form', array(
					'form_type' => 'profile',
					'field_groups' => array('acf_custom-register','acf_sewn_subscriptions'),
				) );
				/** /
				if ( function_exists('acf_form') ) {
					acf_form( array(
						'post_id'         => 'user_' . $current_user->ID,
						'field_groups'    => array('acf_custom-profile', 'acf_sewn_subscriptions'),
						'return'          => add_query_arg('action', 'profile', get_permalink()),
						'submit_value'    => 'Save'
					) );
				}
				/**/ ?>
			</div>

		</div>

		<?php #get_sidebar(); ?>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer(); ?>