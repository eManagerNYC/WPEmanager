<?php get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<article <?php post_class( 'cf' ); ?>>

				<section class="entry-content" itemprop="articleBody">
					<div class="login_form cf">
						<h3 class="login_title">Log In</h3>
						<?php the_content(); ?>
					</div>
				</section>

			</article>

			<?php endwhile; else : ?>
				<?php get_template_part( 'partials/content', 'missing' ); ?>
			<?php endif; ?>

		</div>

		<?php #get_sidebar(); ?>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer(); ?>
