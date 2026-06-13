<?php

/**
 * ACF needs this, form processing
 */
if ( function_exists('acf_form_head') ) { acf_form_head(); }

/**
 * Test url to make sure the user is in the right place
 */
$orientation_step    = get_query_var('add');
$orientation_post_id = get_query_var('orientation_post');
$orientation_post    = get_post($orientation_post_id);
if ( ! $orientation_step ) $orientation_step = 1;

/** /
if ( ! $orientation_step || ! is_numeric($orientation_step) ) {
	wp_safe_redirect( get_permalink() . '1/' );
}

if ( 1 < $orientation_step && (! $orientation_post || 'em_orientation' != get_post_type($orientation_post)) ) {
	wp_safe_redirect( get_permalink() . '1/' );
}

if ( 1 < $orientation_step && (! $orientation_post || 'em_orientation' != get_post_type($orientation_post)) ) {
	wp_safe_redirect( get_permalink() . '1/' );
}
/**/

get_header('orientation'); ?>

<div id="content">

	<div class="wrap">

		<?php #do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<div id="content" class="content clearfix" role="main">
				<div class="entry-content inner">

<?php 
					while ( have_posts() ) : the_post();
?>
						<h1><?php the_title(); ?></h1>
						<?php if ( 5 != $orientation_step ) : ?>
						<form action="<?php the_permalink(); ?>" id="post" class="acf-form form-archive form-orientation form-<?php echo $post_type; ?>" method="post">
<?php
						endif;
							/**
							 * Show a form
							 */
							if ( function_exists('acf_form') ) :
								if ( 1 == $orientation_step ) :
									$id = /* ( is_local() ) ? 1035 : */ 'acf_orientation-background';
									acf_form( array(
										'field_groups' => array($id),
										'form'         => false,
										'post_id'      => 'new_post',
										'submit_value' => 'Submit',
									) );
?>
									<input type="hidden" name="post_status" value="publish" />
									<input type="hidden" name="post_type" value="em_orientation" />
<?php
								elseif ( 2 == $orientation_step ) :
									$id = /* ( is_local() ) ? 1048 : */ 'acf_orientation-photo';
									acf_form( array(
										'field_groups' => array($id),
										'form'         => false,
										'post_id'      => $orientation_post->ID,
										'submit_value' => 'Submit',
									) );

								elseif ( 3 == $orientation_step ) :
									$id = /* ( is_local() ) ? 1032 : */ 'acf_orientation-policies';
									acf_form( array(
										'field_groups' => array($id),
										'form'         => false,
										'post_id'      => $orientation_post->ID,
										'submit_value' => 'Submit',
									) );

								elseif ( 4 == $orientation_step ) :
									$id = /* ( is_local() ) ? 1055 : */ 'acf_orientation-certification';
									acf_form( array(
										'field_groups' => array($id),
										'form'         => false,
										'post_id'      => $orientation_post->ID,
										'submit_value' => 'Submit',
									) );

								elseif ( 5 == $orientation_step ) :
?>
									<h3>Orientation Complete</h3>
									<p>Next step instructions.</p>
									<p><a class="btn btn-warning" href="/orientation/1/" title="Restart Orientation">Restart Orientation</a></p>
<?php
								endif;
									
/**/
/** /
								$id = ( false != strpos(get_option('siteurl'), 'dev') ) ? 1032 : 3896;
								acf_form( array(
									'field_groups' => array($id,'acf_issue-signature'),
									'form'         => false,
									'post_id'      => 'new_post',
									'submit_value' => 'Submit',
								) );
/**/
							endif;
							if ( 5 != $orientation_step ) :
?>
							<input type="hidden" name="post_status" value="publish" />
							<input type="hidden" name="post_type" value="em_orientation" />
							<div class="field field-submit clearfix">
								<button class="btn btn-primary btn-lg">Submit</button>
							</div>
						</form>
<?php
							endif;
					endwhile;
?>

				</div><!-- .inner (end) -->
			</div><!-- #content (end) -->

		</div>

		<?php #get_sidebar(); ?>

		<?php #do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer();