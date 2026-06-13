<?php

/**
 * Create the settings dropdown menu
 */
add_shortcode( 'settings_nav', 'eman_settings_nav' );
function eman_settings_nav( $atts )
{
	$settings_permalink = home_url('/settings/'); ?>
	<div class="settings-nav btn-group pull-right">
		<a class="btn btn-primary" data-toggle="dropdown" href="<?php echo $settings_permalink; ?>">
			<span class="fa fa-gears" aria-hidden="true"></span>
			Settings
		</a>
		<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
			<span class="fa fa-caret-down" aria-hidden="true"></span>
		</a>
		<ul class="dropdown-menu">
		<?php foreach ( eman_post_types('all') as $slug => $cpt ) :
			if ( emanager_post::is_settings($slug) && eman_can_view($slug) ) : ?>
				<li>
					<a href="<?php echo $settings_permalink . str_replace('em_', '', $slug); ?>/">
						<span class="icon-fixed-width fa fa-<?php echo $cpt['icon']; ?>" aria-hidden="true"></span> 
						<?php echo get_post_type_object( $slug )->labels->name; ?>
					</a>
				</li>
			<?php endif;
		endforeach; ?>
		</ul>
	</div>
	<?php
}