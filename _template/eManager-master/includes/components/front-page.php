<?php

/**
 * These functions are only used on the front page.
 */

function eman_dash_link( $title, $url, $icon=false )
{
	?>
	<a href="<?php echo esc_url_raw($url); ?>" title="<?php esc_attr_e($title); ?>" class="btn btn-default">
		<?php eman_dash_content($title, $icon); ?>
	</a>
	<?php
}

function eman_dash_content( $title, $icon=false, $small=true )
{
	if ( $icon ) : ?><span class="fa fa-<?php esc_attr_e($icon); ?>"<?php echo ( ! $small ? ' style="font-size: 80px;"' : ''); ?> aria-hidden="true"></span><?php echo ( ! $small ? '<br />' : ' '); ?><?php endif;
	esc_html_e($title);
}

function eman_dash_flipper( $title, $id=false, $icon=false, $buttons=false )
{
	$color = eman_dash_color();
	ob_start(); ?>
	<div class="flipper" id="<?php echo ( $id ) ? esc_attr_e($id) : sanitize_title($title); ?>">
		<div class="front <?php echo $color; ?>">
			<h2 class="dashboard_title">
				<?php eman_dash_content($title, $icon, false); ?>
			</h2>
		</div>
		<div class="back <?php echo $color; ?>">
			<?php foreach ( $buttons as $button ) : ?>
				<br /><?php eman_dash_link($button['title'], $button['url'], $button['icon']); ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php return ob_get_clean();
}

function eman_dash_noflip( $title, $id=false, $buttons=false )
{
	ob_start(); ?>
	<div class="noflip <?php echo eman_dash_color(); ?>" id="<?php echo ($id ? esc_attr_e($id) : sanitize_title($title)); ?>">
		<?php foreach ( $buttons as $button ) : ?>
			<br /><div class="margin: 1px">
				<?php eman_dash_link($button['title'], $button['url'], $button['icon']); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php return ob_get_clean();
}

function eman_dash_icon_only( $title, $url, $icon=false )
{
	ob_start(); ?>
	<div class="noflip <?php echo eman_dash_color(); ?>" id="<?php echo sanitize_title($title); ?>">
		<h2 class="dashboard_title">
			<a href="<?php echo esc_url_raw($url); ?>" title="<?php esc_attr_e($title); ?>">
				<?php eman_dash_content($title, $icon, false); ?>
			</a>
		</h2>
	</div>
	<?php return ob_get_clean();
}

function eman_dash_tile( $args=array() )
{
	$defaults = array(
		'content' => '',
		'size'    => 'small',
		'flip'    => false
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	$empty_content = '<div class="noflip ' . eman_dash_color() . '"><br /><div class="margin: 1px">&nbsp;</div></div>';

	ob_start(); ?>
	<div class="dashboard_tile <?php echo ($content ? 'dashboard_tile_content' : 'dashboard_tile_empty') . ('large' == $size ? ' m-4of12' : ' m-2of12') . ($flip ? ' flip-container' : ''); ?>">
		<?php echo ($content ? $content : $empty_content); ?>
	</div>
	<?php echo ob_get_clean();
}
/** /
function eman_dash_color()
{
	if ( ! empty($GLOBALS['eman_info']['dashboard_colors']) )
	{
		$colors = $GLOBALS['eman_info']['dashboard_colors'];
		return $colors[array_rand($colors, 1)];
	}
}
/**/
function eman_dash_color()
{
	$dash_colors = eman_info('dashboard_colors');
	if ( ! empty($dash_colors) && is_array($dash_colors) ) {
		return 'dash-' . $dash_colors[array_rand($dash_colors, 1)];
	}
}