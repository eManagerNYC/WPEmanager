<?php

$settings_permalink = home_url('/directory/');

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
						<div style="margin: 10px">
						<ul class="nav nav-pills" role="tablist" id="em_directory">
						  <li class="active"><a href="#contractors" role="tab" data-toggle="tab">Contractors</a></li>
						  <li><a href="#owner" role="tab" data-toggle="tab">Owner</a></li>
						  <li><a href="#owners_rep" role="tab" data-toggle="tab">Owner's Rep</a></li>
						  <li><a href="#consultants" role="tab" data-toggle="tab">Consultants</a></li>
						  <li><a href="#turner" role="tab" data-toggle="tab">Turner</a></li>
						</ul>
						</div>
						<div class="tab-content" style="margin: 10px">
						  <div class="tab-pane active" id="contractors"><?php echo do_shortcode('[users role="subcontractor"]')?></div>
						  <div class="tab-pane" id="owner"><?php echo do_shortcode('[users role="owner"]')?></div>
						  <div class="tab-pane" id="owners_rep"><?php echo do_shortcode('[users role="owners_rep"]')?></div>
						  <div class="tab-pane" id="consultant"><?php echo do_shortcode('[users role="consultant"]')?></div>
						  <div class="tab-pane" id="turner"><?php echo do_shortcode('[users role="editor"]')?></div>
						</div>

						<script>
						  $(function () {
						    $('#em_directory a:last').tab('show')
						  })
						</script>

						<?php themeblvd_content_bottom(); ?>
					</div><!-- .inner (end) -->
				</div><!-- #content (end) -->

				<!-- CONTENT (end) -->

				<?php get_sidebar( 'right' ); ?>

			</div><!-- .grid-protection (end) -->
		</div><!-- .sidebar_layout-inner (end) -->
	</div><!-- .#sidebar_layout (end) -->

<?php get_footer(); ?>