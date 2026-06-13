<?php

/**
 * Connect to Central Button
 */

if ( ! class_exists('Eman_Connect_Button') ) :

class Eman_Connect_Button extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'Eman_Connect_Button',
			'description' => 'Adds Parent Connect Button'
		);

		parent::__construct(
			'Eman_Connect_Button',
			'eManager Parent Connect',
			$widget_ops
		);
	}

	/**
	 * Widget form creation
	 */
	public function form( $instance )
	{
		// Check values
		$textarea = ! empty($instance['textarea']) ? esc_textarea($instance['textarea']) : '';
?>
		<p>Connect Link</p>
<?php
	}

	/**
	 * Update widget
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function widget( $args, $instance )
	{
		extract( $args );

		$emspd = 'emanagerspd.com';

		if ( is_user_logged_in() )
		{
			// Set up link depending on site
			if ( false !== strpos($_SERVER['HTTP_HOST'], $emspd) )
			{
				$url   = 'https://central.emanagerspd.com';
				$title = 'CE';
				$icon  = 'external-link';
				$text  = 'CE';
			}
			else
			{
				$url   = emanager_url();
				$title = 'eManager';
				$icon  = 'globe';
				$text  = '';
			}

			echo $before_widget;

			echo eman_button( array(
				'text'        => $text,
				'title'       => $title,
				'url'         => $url,
				'color'       => 'primary',
				'target'      => '_blank',
				'icon_before' => $icon,
			) );

			echo $after_widget;
		}
	}
}

// register widget
add_action( 'widgets_init', 'eman_widget_connect_button' );
function eman_widget_connect_button()
{
	return register_widget('Eman_Connect_Button');
}

endif;