<?php get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<?php do_shortcode('[settings_nav]'); ?><br />

	<?php if ( eman_check_role('turner') || eman_check_role('owner') ) : ?>
	
			<div id="eman-project-summary">
				<span class="label label-info"></span>
				<div class="panel panel-default">
					<div class="panel-heading"><span class="icon fa fa-briefcase" aria-hidden="true"></span> Project Info</div>
					<div class="panel-body">
						<h3><?php eman_the_field('proj_name', 'option'); ?></h3>
						<strong>Address</strong>: <?php eman_the_field('proj_address', 'options'); ?>, <?php eman_the_field('proj_city', 'options'); ?>, <?php eman_the_field('proj_state', 'options'); ?><br />
						<strong>Contract</strong>: <?php eman_the_field('contract_type', 'options'); ?><br />
						<strong>Start Date</strong>: <?php eman_the_field('proj_start', 'options'); ?><br />
						<strong>Business Unit</strong>: <?php eman_the_field('business_unit', 'options'); ?><br />
						<strong>Building Type</strong>: <?php eman_the_field('building_type', 'options'); ?><br />
						<strong>Construction Type</strong>: <?php eman_the_field('construction_type', 'options'); ?>
					</div>
				</div>
			</div>

	<?php endif; ?>

<?php /** / ?>
			<div style="margin-bottom: 10px">
				<ul class="nav nav-pills" role="tablist" id="em_directory">
				<?php if ( eman_check_role('turner') || eman_check_role('owner') ) : ?>
					<li class="active"><a href="#stats" role="tab" data-toggle="tab">Statistics</a></li>
					<li><a href="#contractors" role="tab" data-toggle="tab">Contractors</a></li>
				<?php else : ?>
					<li class="active"><a href="#contractors" role="tab" data-toggle="tab">Contractors</a></li>
				<?php endif; ?>
					<li><a href="#owner" role="tab" data-toggle="tab">Owner</a></li>
					<li><a href="#owners_rep" role="tab" data-toggle="tab">Owner's Rep</a></li>
					<li><a href="#consultant" role="tab" data-toggle="tab">Consultants</a></li>
					<li><a href="#turner" role="tab" data-toggle="tab">Turner</a></li>
				</ul>
			</div>

			<div class="tab-content">
			<?php if ( eman_check_role('turner') || eman_check_role('owner') ) : ?>
				<div class="tab-pane active" id="stats">
					<?php get_template_part( 'partials/settings', 'stats' ); ?>
				</div>
				<div class="tab-pane" id="contractors"><?php echo do_shortcode('[users role="subcontractor"]'); ?></div>
			<?php else : ?>
				<div class="tab-pane active" id="contractors"><?php echo do_shortcode('[users role="subcontractor"]'); ?></div>
			<?php endif; ?>
				<div class="tab-pane" id="owner"><?php echo do_shortcode('[users role="owner"]'); ?></div>
				<div class="tab-pane" id="owners_rep"><?php echo do_shortcode('[users role="owners_rep"]'); ?></div>
				<div class="tab-pane" id="consultant"><?php echo do_shortcode('[users role="consultant"]'); ?></div>
				<div class="tab-pane" id="turner"><?php echo do_shortcode('[users role="editor"]'); ?></div>
			</div>
<?php /**/ ?>
		</div>

		<?php #get_sidebar(); ?>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer(); ?>