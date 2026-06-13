<?php

/**
 * Help Button
 */

if ( ! class_exists('Eman_Help_Button') ) :

class Eman_Help_Button extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'Eman_Help_Button',
			'description' => 'Adds Help Button'
		);

		parent::__construct(
			'Eman_Help_Button',
			'eManager Help Button',
			$widget_ops
		);
	}

	public function widget( $args, $instance ) // widget sidebar output
	{
		extract($args, EXTR_SKIP);

		$params  = eman_info('support_params');
		$subject = ( ! empty($params['subject']) ? $params['subject'] : '');

        if ( is_user_logged_in() )
        {
	        #if ( false === ($support_buttons = get_transient('support_buttons')) ) :
	        	ob_start();
?>
				<p>Every project has an eManager Specialist, ask the next Turner person you see to see who your specialist is! The Turner eManager team is committed to making the best possible experience for you. If you run into an issue or you just want to talk to us:</p>
				<div style="text-align:center;">
<?php
					$params['subject'] = urlencode($subject . ": Question");
					echo eman_button( array(
						'text'        => 'Get support',
						'url'         => add_query_arg( $params, emanager_url('support/') ),
						'color'       => 'primary',
						'target'      => '_blank',
						'size'        => 'lg',
						'icon_before' => 'medkit',
					) );
					$params['subject'] = urlencode($subject . ": Issue Report");
					echo eman_button( array(
						'text'        => 'Report an issue',
						'url'         => add_query_arg( $params, emanager_url('support/') ),
						'color'       => 'primary',
						'target'      => '_blank',
						'size'        => 'lg',
						'icon_before' => 'bug',
					) );
					$params['subject'] = urlencode("eManager Contact");
					echo eman_button( array(
						'text'        => 'Contact Us',
						'url'         => add_query_arg( $params, emanager_url('contact/') ),
						'color'       => 'primary',
						'target'      => '_blank',
						'size'        => 'lg',
						'icon_before' => 'envelope',
					) );
?>
				</div>
<?php
				$support_buttons = ob_get_clean();
				#set_transient('support_buttons', $support_buttons, WEEK_IN_SECONDS);
			#endif;
?>

<?php
			ob_start();
			echo $support_buttons;

			if ( eman_check_role('turner') ) :
?>
				<div style="text-align:center;">
<?php
				echo eman_button( array(
					'text'        => 'Documentation',
					'url'         => emanager_url('docs/'),
					'color'       => 'warning',
					'classes'     => 'btn-docs',
					'target'      => '_blank',
					'size'        => 'lg',
					'icon_before' => 'book',
				) );
				/** /$authkey = trim( wp_generate_password(18, false) ); ?>
				<div style="margin-top:.75rem; text-align:center;">
					<form role="form" action="https://sop.emanagercloud.com/index.php" method="GET">
						<input type="hidden" id="authkey" name="authkey" value="<?php echo $authkey; ?>" /><button type="submit" class="btn btn-warning btn-lg"><span class="fa fa-book"></span> Book Library</button>
					</form>
				</div>
				<?php endif;/**/
?>
				</div>
<?php
			endif;
/** / ?>

			<div class="divider divider-shadow" style="margin-top:1.5rem;"></div>
			<h3><span class="fa fa-video-camera" aria-hidden="true"></span> Video Help</h3>
			<ol>
				<li><a href="<?php echo emanager_url('register.php'); ?>" target="_blank">Registration Assistance</a></li>
				<li><a href="http://training.emanagercloud.com/settings.php" target="_blank">Populating the Settings</a></li>
			</ol>
			<h3><span class="fa fa-video-camera" aria-hidden="true"></span> FAQ</h3>
			<ol>
				<li><a href="<?php echo emanager_url('qrscan.php'); ?>" target="_blank">Can I scan a QR Code to find a printed record?</a></li>
			</ol>

			<?php if ( current_user_can('manage_options') ) : ?>
				<div class="divider divider-shadow" style="margin-top:1.5rem;"></div>
				<div style="margin-bottom:1.5rem; text-align:center;">
					<?php echo do_shortcode('[button link=' . emanager_url('specialist.php') . ' block=false icon_before=trophy color=success size=lg target=_blank]Specialist Subscribe[/button]'); ?>
					<?php echo do_shortcode('[button link=' . emanager_url('deactivate.php') . ' block=false icon_before=times color=danger size=lg target=_blank]Deactivate Site[/button]'); ?>
				</div>
			<?php endif; ?>
<?php /**/
			$helpcontent = ob_get_clean();
		}
		else
        {
	        ob_start();
?>
			<p>Are you having trouble registering? Please contact support:</p>
			<div style="text-align:center;">
				<?php
				echo eman_button( array(
					'text'        => 'Support',
					'url'         => emanager_url('support/?site=' . urlencode( str_replace(array('https://','http://'), '', home_url())) . '&subject=' . urlencode("Registration Issues") ),
					'color'       => 'primary',
					'target'      => '_blank',
					'size'        => 'lg',
					'icon_before' => 'medkit',
				) ); ?>
			</div>
			<?php /** / ?><p>Are you having trouble registering? We put together a neat video to help you along the way, click the button below:</p>
			<div style="text-align:center;">
				<?php
				echo eman_button( array(
					'text'        => 'Help registering',
					'url'         => '#'.emanager_url('register.php'),
					'color'       => 'primary',
					'target'      => '_blank',
					'size'        => 'lg',
					'icon_before' => 'video-camera',
				) ); ?>
			</div><?php /**/ ?>
<?php
			$helpcontent = ob_get_clean();
        }


		echo $before_widget;

		echo eman_modal( array(
			'text'     => '',
			'color'    => 'danger',
			'header'   => 'Support & Resource',
			'icon_before' => 'question-circle',
			'animate'  => 'true',
		), $helpcontent );

		echo $after_widget;
	}
}

// register widget
add_action( 'widgets_init', 'eman_widget_help_button' );
function eman_widget_help_button()
{
	return register_widget('Eman_Help_Button');
}

endif;