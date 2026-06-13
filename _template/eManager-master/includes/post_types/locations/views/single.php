<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<h3 class="entry-title single-title" itemprop="headline">loc: <?php the_title(); ?></h3>

	<div class="meta-overview cf">
		<div class="left-meta cf">
		<dl>
			<dt>Submitted</dt>
				<dd><?php 
					global $submitted_stamp;
					// Get the last review that submitted the letter
					$reviews = new WP_Query( array(
						'post_type' => 'em_reviews',
						'posts_per_page' => 1,
						'order' => 'DESC',
						'orderby'=> 'date',
						'tax_query'     => array(
							array(
								'taxonomy' => 'em_status',
								'field'    => 'slug',
								'terms'    => 'submitted',
							)
						),
						'meta_query' => array(
							array(
								'key' => 'reviewed_id',
								'value' => get_the_ID(),
								'compare' => '=',
							)
						)
					) );
					$submitted_stamp = strtotime($reviews->post->post_date);
					echo date_i18n(get_option('date_format'), $submitted_stamp) ?>
				</dd>
			<dt>Submitted by</dt>
				<dd><?php echo get_the_author_meta( 'display_name', $reviews->post->post_author ); ?></dd>
		</dl>
		</div>
	</div>

	<section class="em_location">
		<?php
		$plan   = eman_get_field('plan');
		$model  = eman_get_field('model');
		$width  = '100%';
		$height = '100%';
		if ( $plan && $model ) : ?>
			<ul class="nav nav-tabs" role="tablist" id="myTab">
				<li class="active"><a href="#model" role="tab" data-toggle="tab">Model</a></li>
				<li><a href="#pdf" role="tab" data-toggle="tab">PDF</a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="model">
					<?php echo do_shortcode('[model url=' . $model . '  width=' . $width . ' height=' . $height . ']'); ?>
				</div>
				<div class="tab-pane" id="pdf">
					<?php echo do_shortcode('[viewerjs ' . $plan . ']'); ?>
				</div>
			</div>

			<script>
			// <![CDATA[
				$(function(){ $('#myTab a:last').tab('show'); });
			// ]]>
			</script>
			<?php
		elseif ( $plan ) :
			echo do_shortcode('[viewerjs '.$plan.']');
		elseif ($model) :
			echo do_shortcode('[model url=' . $model . '  width=' . $width . ' height=' . $height . ']');
		endif; ?>
	</section>
</article>

<?php get_template_part('partials/single/summary-action');