<div id="content">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<div id="welcome" style="text-align: left">

				<?php if ( $welcome_title = eman_get_field('welcome_title', 'options') ) : ?><h2><?php echo $welcome_title; ?></h2><?php endif; ?>
				<div class="row">
					<div class="m-2of12"><center>
						<a href="<?php echo emanager_url(); ?>" title="Visit the eManager home">
							<span class="fa fa-globe fa-5x" aria-hidden="true"></span><br />
							<strong><?php echo str_replace(array('http://','https://'), '', emanager_url()); ?></strong>
						</a>
					</center></div>
					<div class="m-10of12">
						<?php if ( $welcome_content = eman_get_field('welcome_content', 'options') ) : ?>
							<p><span class="dropcap"><?php echo $welcome_content[0]; $welcome_content[0]=''; ?></span><?php echo $welcome_content; ?></p>
						<?php endif; ?>
					</div>
				</div>

				<?php
				$tab1_title = 'Login';
				ob_start();
				get_template_part('partials/front-page/external', 'login');
				$tab1       = ob_get_clean();
				$tab2_title = 'Register';
				ob_start();
				get_template_part('partials/front-page/external', 'register');
				$tab2       = ob_get_clean();
				echo do_shortcode('[tabs style="framed" tab_1="'.$tab1_title.'" tab_2="'.$tab2_title.'"][tab_1][raw]' . $tab1 . '[/raw][/tab_1][tab_2][raw]' . $tab2 . '[/raw][/tab_2][/tabs]');
				?>

			</div>

		</div>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>