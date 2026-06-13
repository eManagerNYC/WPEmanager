<?php get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<article <?php post_class( 'cf' ); ?>>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>
				</header>

				<section class="entry-content" itemprop="articleBody">
					<?php do_action( 'sewn/messenger/compose_form' ); ?>
				</section>

			</article>

			<?php endwhile; else : ?>
				<?php get_template_part( 'partials/content', 'missing' ); ?>
			<?php endif; ?>

		</div>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer();