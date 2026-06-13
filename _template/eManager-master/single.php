<?php

/**
 * Redirect back to dashboard if user can't view this
 */
if ( ! eman_can_view($post) ) {
	wp_redirect( home_url('/') );
}


/**
 * Confirm the draft, this will send it on
 */
get_template_part('partials/single/function', 'confirm');


/**
 * If a gatekeeper has requested pullback, pullback the NOC from Submitted to Ready to Submit
 */
get_template_part('partials/single/function', 'pullback');


/**
 * Get status
 */
$status = emanager_post::status($post, 'slug');

$cpt = eman_post_types(get_post_type());


/**
 * ACF needs this, form processing
 */
if ( function_exists('acf_form_head') ) { acf_form_head(); }


get_header(); ?>

<div id="content">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<?php if ( $obj = get_post_type_object(get_post_type()) ) : ?>
			<div class="archive-nav" class="cf">
				<?php /** / ?><a class="back-list btn btn-default" href="/<?php echo $obj->has_archive; ?>/" title="Back to list"><i class="fa fa-arrow-left"></i>  Back to List</a>
				<?php /** / ?><a class="add-new btn btn-success" href="/<?php echo ( 'em_issue' == get_post_type() ) ? $obj->rewrite['slug'] : $obj->has_archive; ?>/add/" title="Add"><i class="fa fa-plus-circle"></i> <?php echo $obj->labels->add_new_item; ?></a><?php /**/ ?>
				<?php 
				$url = site_url( '/' . $obj->has_archive . '/' . ('noc' == get_post_type() && ! in_array($status, array('submitted','executed','recommend')) ? 'pco/' : '' ) );
				echo do_shortcode('[button class="back-list" link="' . $url . '" color="default" title="Back to list" icon_before="arrow-left"]Back to list[/button]');
				if ( 
					( (eman_check_role('turner') || eman_check_role('sub')) )
					|| ( 'em_issue' == get_post_type() )
				) :
					$label = str_replace('NOC', 'PCO', $obj->labels->add_new_item);
					echo do_shortcode('[button class="add-new" link="' . home_url( ('em_issue' == get_post_type() ? $obj->rewrite['slug'] : $obj->has_archive) . '/add/') . '" color="success" icon_before="plus-circle" title="' . $label . '"]' . $label. '[/button]');
				endif; ?>
			</div>
			<?php endif; ?>

			<div id="content" class="content clearfix" role="main">
				<div class="entry-content inner">

					<?php 
					while ( have_posts() ) : the_post();

						/**
						 * Show a form
						 */
						if ( eman_can_edit($post) && ! empty($_REQUEST['edit']) ) :
							get_template_part('partials/single/form');

						/**
						 * Specific for NOCs (not PCO)
						 */
						elseif ( 'em_noc' == get_post_type() ) :
							if ( in_array($status, array('submitted','executed','recommend')) ) :
								eman_template_part('single');
							else :
								get_template_part('partials/single/summary');
								get_template_part('partials/single/summary-action');
							endif;

						/**
						 * Specific for Invoices
						 */
						elseif ( 'em_invoice' == get_post_type() ) :
							get_template_part('partials/single/summary');
							get_template_part('partials/single/summary-action');

						/**
						 * If not a draft, load a single view from post_type if it exists or default to standard summary
						 */
						elseif ( ('settings' != $cpt['type'] && 'draft' == $status) || ! eman_template_part('single') ) ://'settings' != $cpt['type'] && 
							get_template_part('partials/single/summary');
							get_template_part('partials/single/summary-action');

						/**
						 * Specific for NOCs (not PCO)
						 * /
						elseif ( in_array($status, array('submitted','executed','recommend')) ) :
							get_template_part('partials/single/noc');
							get_template_part('partials/single/summary-action');

						/**
						 * if it is a particular post type view
						 * /
						elseif ( 'em_locations' == get_post_type() ) :
							get_template_part('partials/single/location');

						elseif ( 'em_letter' == get_post_type() ) :
							get_template_part('partials/single/letter');

						elseif ( 'em_rfi' == get_post_type() ) :
							get_template_part('partials/single/rfi');

						/**
						 * Get the regular summary
						 * /
						else :
							get_template_part('partials/single/summary');
							get_template_part('partials/single/summary-action');
						/**/
						endif;

					endwhile; ?>

				</div><!-- .inner (end) -->
			</div><!-- #content (end) -->

			<?php if ( in_array(get_post_type(), array('em_tickets','em_noc','em_dcr','em_issue', 'em_letter', 'em_rfi', 'em_invoice')) && 'draft' != $status ) : ?>
				<div id="comments-area" class="clearfix">
					<div class="sixcol first reviews clearfix">
						<?php get_template_part('partials/single/reviews'); ?>
					</div>
					
					<?php if ( ! in_array(get_post_type(), array('em_letter')) ) : ?>
					<div class="sixcol last comments clearfix">
						<?php comments_template( '', true ); ?>
					</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

            <?php echo do_shortcode('[qrcode size="100" link="' . get_permalink() . '"][/qrcode]'); ?>

		</div>

		<?php #get_sidebar(); ?>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer();
