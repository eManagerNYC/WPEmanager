<?php get_header(); ?>

<div id="content">
	<div class="page-header-bg bannerless">
		<div id="inner-content" class="wrap clearfix">
			
			<header class="page-header">
				<?php if ( function_exists('the_breadcrumbs') ) { ?><p id="breadcrumbs"><?php the_breadcrumbs(); ?></p><?php } ?>
				<h1 class="page-title" itemprop="headline"><?php post_type_archive_title(); ?></h1>
			</header>
			
			<article class="page clearfix" role="article">

				<div id="main" class="ninecol last clearfix" role="main">

					<section class="map cf">
						<?php do_action('acf_locations/search_field');#, array('show_distance'=>true) ?>
						<?php do_action('acf_locations/map', $posts); ?>
					</section>

					<section id="mapp0_poi_list" class="mapp-poi-list list cf">
						<?php do_action('acf_locations/list', $posts); ?>
					</section>

					<br /><br />
					<?php the_content(); ?>
				</div>
				
				<?php get_sidebar('locations'); ?>
				
			</article>
			
		</div> <!-- end #inner-content -->

		<a class="scrolltop" href="#container">SCROLL TO THE TOP &nbsp; <span class="icon-arrow-up"></span></a>
		
	</div> <!-- end .page-header-bg -->
</div> <!-- end #content -->

<?php get_footer(); ?>
