<?php

/**
 * Set up custom post type
 */
$post_type = eman_archive_info('post_type');
$cpt       = eman_archive_info('cpt');


add_filter( 'wp_title', 'eman_archive_wp_title', 10, 2 );
function eman_archive_wp_title( $title, $sep )
{
	return eman_archive_info('plural_title');
}


/**
 * Owners have no access to settings, Subs have limited access
 */
if ( ! eman_can_view($post_type) ) {
	wp_redirect( home_url('/') );
	die;
}


/**
 * Set up an export if requested
 */
#emanager_table_export::setup_export();
do_action('emanager_table_export/setup_export');


/**
 * Change post type
 */
add_filter( 'acf/create_object/post/type', 'custom_set_post_type' );
function custom_set_post_type()
{
	return get_query_var('post_type');
}


/**
 * Autofill from previous
 */
get_template_part('partials/archive/function', 'autofill');


/**
 * ACF form head
 */
if ( function_exists('acf_form_head') ) { # get_query_var('add') &&
	acf_form_head();
}

get_header(); ?>

<div id="content">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<div class="archive-nav" class="cf">
			<?php if ( $obj = eman_archive_info('post_type_object') ) : ?>
				<?php
				if ( get_query_var('add') ) :
					echo eman_button( array(
						'text'        => 'Back to list',
						'url'         =>  home_url('/' . $obj->has_archive . '/'),
						'color'       => 'default',
						'classes'     => 'back-list',
						'icon_before' => 'arrow-left',
					) );
				else :
					echo eman_button( array(
						'text'        => 'Back to dashboard',
						'url'         =>  home_url(),
						'color'       => 'default',
						'classes'     => 'back-list',
						'icon_before' => 'arrow-left',
					) );
				endif;

				if ( ! get_query_var('add') && $cpt ) :
					if ( 
						(
							(eman_check_role('turner') || eman_check_role('sub'))
							&& ('em_noc' != $post_type || eman_archive_info('is_pco')) 
						)
						|| ( 'em_issue' == $post_type )
					) :
						$label = str_replace('NOC', 'PCO', $obj->labels->add_new_item);
						echo eman_button( array(
							'text'        => $label,
							'url'         =>  home_url( ('settings' == $cpt['type'] ? '/settings' : '') . '/' . str_replace('em_', '', $post_type) . '/add/'),
							'color'       => 'success',
							'classes'     => 'add-new',
							'icon_before' => 'plus-circle',
						) );
					endif;
				endif;
			endif; ?>
			</div>

			<div id="<?php echo $post_type ?>" class="em_tables">

				<?php
				/**
				 * The settings menu
				 */
				if ( 'settings' == $post_type || 'settings'  == $cpt['type'] ) : ?>
					<?php echo do_shortcode('[settings_nav]'); ?>
				<?php endif; ?>

				<?php if ( eman_archive_info('cpt') && get_query_var('add') ) :
		
					require_once( get_template_directory() . '/partials/archive/add.php' );
		
				elseif ( eman_archive_info('cpt') ) :

					require_once( get_template_directory() . '/partials/archive/table.php' );

				endif; ?>

			</div>

		</div>

		<?php #get_sidebar(); ?>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer();