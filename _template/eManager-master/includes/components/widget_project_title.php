<?php

/**
 * Show the project title
 */

if ( ! class_exists('Eman_Project_Title') ) :

class Eman_Project_Title extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'Eman_Project_Title',
			'description' => 'Adds Project Title'
		);

		parent::__construct(
			'Eman_Project_Title',
			'eManager Project Title',
			$widget_ops
		);
	}

	/**
	 * Widget form creation
	 */
	public function form( $instance )
	{
?>
		<p>Project Title</p>
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

		echo $before_widget;

		if ( is_user_logged_in() )
		{
			if ( $proj_name = eman_get_field('proj_name', 'options') )
			{
?>
				<h3 class="inline project_name" style="margin:0;">
					<?php printf(__('Project: %s', 'emanager'), $proj_name); ?>
				</h3>
<?php
			}
		}

		echo $after_widget;
	}
}

// register widget
add_action( 'widgets_init', 'eman_widget_project_title' );
function eman_widget_project_title()
{
	return register_widget('Eman_Project_Title');
}

endif;