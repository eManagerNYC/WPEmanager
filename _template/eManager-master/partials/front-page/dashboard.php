<?php require_once( get_template_directory() . '/partials/front-page/dashboard-settings.php' ); ?>

<div id="content">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<div class="dashboard">

			<?php if ( eman_check_role('turner') ) :

				#get_template_part( 'partials/front-page/dashboard', 'turner' );
				require_once( get_template_directory() . '/partials/front-page/dashboard-turner.php' );

			elseif ( eman_check_role('owner') ) :

				require_once( get_template_directory() . '/partials/front-page/dashboard-owner.php' );

			elseif ( eman_check_role('sub') ) :

				require_once( get_template_directory() . '/partials/front-page/dashboard-sub.php' );

			elseif ( eman_check_role('pending') ) :

				require_once( get_template_directory() . '/partials/front-page/dashboard-pending.php' );

			endif; ?>

			</div>

			<div class="em_news">
				<?php eman_rss_marquee(); ?>
			</div>

		</div>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>