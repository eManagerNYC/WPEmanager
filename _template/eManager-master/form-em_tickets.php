<?php

/**
 * change post type
 */
add_filter( 'acf/create_object/post/type', function() {
	return 'em_tickets';
} );

if ( function_exists('acf_form_head') ) { acf_form_head(); }

get_header();
?>

	<div id="sidebar_layout" class="clearfix">
		<div class="sidebar_layout-inner">
			<div class="row-fluid grid-protection">

				<?php get_sidebar( 'left' ); ?>

				<!-- CONTENT (start) -->

				<div id="content" class="<?php echo themeblvd_get_column_class('content'); ?> clearfix" role="main">
					<div class="inner">
						<?php themeblvd_content_top(); ?>

<?php
							if ( function_exists('acf_form') ) {
								acf_form( array(
									'post_id' => 'new_post',
									'field_groups' => array('acf_post-title-content', 11)
								) );
							}
?>

							<?php themeblvd_page_footer(); ?>

						<?php themeblvd_content_bottom(); ?>
					</div><!-- .inner (end) -->
				</div><!-- #content (end) -->

				<!-- CONTENT (end) -->

				<?php get_sidebar( 'right' ); ?>

			</div><!-- .grid-protection (end) -->
		</div><!-- .sidebar_layout-inner (end) -->
	</div><!-- .#sidebar_layout (end) -->

<?php get_footer(); ?>