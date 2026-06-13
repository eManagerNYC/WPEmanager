<?php

/**
 * eman_widgetize_before_content
 *
 * Add widget area before content
 *
 * @author Jake Snyder
 * @return void
 */
add_action( 'before_content', 'eman_widgetize_before_content' );
function eman_widgetize_before_content()
{
	if ( is_active_sidebar('ad_above_content') )
	{
?>
		<div class="widgets widgets-before-content">
			<?php dynamic_sidebar('ad_above_content'); ?>
		</div>
<?php
	}
	elseif ( apply_filters( 'eman_widgetize_show_defaults', true ) && apply_filters( 'eman_widgetize_show_before_content', true ) )
	{
?>
		<div class="widgets widgets-before-content">
			<?php the_widget('Eman_Project_Title'); ?>
			<?php the_widget('Eman_Connect_Button'); ?>
		</div>
<?php
	}
}

/**
 * eman_widgetize_after_content
 *
 * Add widget area after content
 *
 * @author Jake Snyder
 * @return void
 */
add_action( 'after_content',  'eman_widgetize_after_content' );
function eman_widgetize_after_content()
{
	if ( is_active_sidebar('ad_below_content') )
	{
?>
		<div class="widgets widgets-after-content">
			<?php dynamic_sidebar('ad_below_content'); ?>
		</div>
<?php
	}
	elseif ( apply_filters( 'eman_widgetize_show_defaults', true ) && apply_filters( 'eman_widgetize_show_after_content', true ) )
	{
?>
		<div class="widgets widgets-after-content">
			<?php the_widget('Eman_Help_Button'); ?>
		</div>
<?php
	}
}

/**
 * eman_widgetize_after_footer
 *
 * Add widget area after footer content
 *
 * @author Jake Snyder
 * @return void
 */
add_action( 'after_footer',   'eman_widgetize_after_footer' );
function eman_widgetize_after_footer()
{
	if ( is_active_sidebar('ad_below_footer') )
	{
?>
		<div class="widgets widgets-footer">
			<?php dynamic_sidebar('ad_below_footer'); ?>
		</div>
<?php
	}
}
