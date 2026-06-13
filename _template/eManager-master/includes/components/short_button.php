<?php


/**
 * Button
 *
 * @param array $atts Standard WordPress shortcode attributes
 * @param string $content The enclosed content
 * @return string $output Content to output for shortcode
 */
add_shortcode( 'button', 'eman_short_button' );
function eman_short_button( $atts, $content=null )
{
    $output = '';

    $default = array(
        'link'              => '',
        'color'             => 'default',
        'target'            => '_self',
        'size'              => '',
        'class'             => '',
        'title'             => '',
        'icon_before'       => '',
        'icon_after'        => '',
        'block'             => 'false',
        'addon'             => '',
    );
    extract( shortcode_atts($default, $atts) );

    $final_class = 'btn-shortcode';

    if ( $class ) {
        $final_class .= ' '.$class;
    }

    if ( $block == 'true' ) {
        $block = true;
    } else {
        $block = false;
    }

	$output = eman_button( array(
		'text'        => $content,
		'url'         => $link,
		'color'       => $color,
		'target'      => $target,
		'size'        => $size,
		'classes'     => $final_class,
		'title'       => $title,
		'icon_before' => $icon_before,
		'icon_after'  => $icon_after,
		'addon'       => $addon,
		'block'       => $block,
	) );

    return $output;
}

/**
 * Button
 *
 * As of framework v2.2, the button markup matches
 * the Bootstrap standard "btn" structure.
 *
 * @since 2.0.0
 *
 * @param string $text Text to show in button
 * @param string $color Color class of button
 * @param string $url URL where the button points to
 * @param string $target Anchor tag's target, _self, _blank, or lightbox
 * @param string $size Size of button - small, medium, default, or large
 * @param string $classes CSS classes to attach onto button
 * @param string $title Title for anchor tag
 * @param string $icon_before Optional fontawesome icon before text
 * @param string $icon_after Optional fontawesome icon after text
 * @param string $addon Anything to add onto the anchor tag
 * @param bool $block Whether the button displays as block (true) or inline (false)
 * @return $output string HTML to output for button
 */
function eman_button( $args )
{
	$args = wp_parse_args( $args, array(
		'text'        => '',
		'url'         => '',
		'color'       => 'default',
		'target'      => '_self',
		'size'        => null,
		'classes'     => null,
		'title'       => null,
		'icon_before' => null,
		'icon_after'  => null,
		'addon'       => null,
		'block'       => false,
	) );
	extract( $args );

	// Classes for button
	$final_classes = 'btn';

	if ( ! $color ) {
		$color = 'default';
	}

	$final_classes = eman_get_button_class( $color, $size, $block );

	if ( $classes ) {
		$final_classes .= ' '.$classes;
	}

	// Title param
	if ( ! $title ) {
		$title = strip_tags( $text );
	}

	// Add icon before text?
	if ( $icon_before ) {
		$text = '<span class="fa fa-'.$icon_before.'"></span> '.$text;
	}

	// Add icon after text?
	if ( $icon_after ) {
		$text .= ' <span class="fa fa-'.$icon_after.'"></span>';
	}

	// Finalize button
	if ( $target == 'lightbox' )
	{
		// Button linking to lightbox
		$args = array(
			'item' 	=> $text,
			'link' 	=> $url,
			'title' => $title,
			'class' => $final_classes,
			'addon'	=> $addon
		);

		$button = eman_get_link_to_lightbox( $args );
	}
	else
	{
		// Standard button
		$button = sprintf( '<a href="%s" title="%s" class="%s" %s%s>%s</a>', $url, $title, $final_classes, ($target ? " target=\"$target\"" : ''), ($addon ? " $addon" : ''), $text );
	}

	// Return final button
	return $button;
}

/**
 * Get class for buttons.
 *
 * @since 2.4.0
 *
 * @param string $color Color of button
 * @param string $size Size of button
 * @param bool $block Whether the button displays as block (true) or inline (false)
 * @return string $class HTML Class to be outputted into button <a> markup
 */
function eman_get_button_class( $color = '', $size = '', $block = false )
{
	$class = 'btn';

	// Color
	if ( ! $color ) {
		$color = 'default';
	}

	if ( in_array( $color, array( 'default', 'primary', 'info', 'success', 'warning', 'danger' ) ) ) {
		$class .= sprintf( ' btn-%s', $color );
	} else {
		$class .= sprintf( ' %s', $color );
	}

	// Size
	switch ( $size ) {
		case 'mini' :
			$size = 'xs';
			break;
		case 'small' :
			$size = 'sm';
			break;
		case 'large' :
			$size = 'lg';
			break;
	}

	if ( in_array( $size, array( 'xs', 'sm', 'lg' ) ) ) {
		$class .= sprintf( ' btn-%s', $size );
	}

	// Block
	if ( $block ) {
		$class .= ' btn-block';
	}

    return $class;
}

/**
 * Take a piece of markup and wrap it in a link to a lightbox.
 *
 * @since 2.3.0
 *
 * @param $args array Arguments for lightbox link
 * @return $output string Final HTML to output
 */
function eman_get_link_to_lightbox( $args ) {

	$defaults = array(
		'item' 		=> "Link to lightbox", // HTML Markup to be wrapped in link
		'link' 		=> '',				   // Source for media in lightbox
		'title' 	=> '', 				   // Title for link
		'type'		=> '', 				   // Type of lightbox link - image, iframe, ajax, inline - leave blank for auto detection
		'class' 	=> '', 				   // Additional CSS classes to add
		'props'		=> array(), 		   // Additional properties for anchor tag, i.e. array( 'data-something' => 'whatever' )
		'addon'		=> '', 				   // Optional addon for anchor tag, i.e. data-something="whatever"
		'gallery' 	=> false 			   // Whether this is part of a gallery
	);
	$args = wp_parse_args( $args, $defaults );

	// Item markup to wrap link around
	$item = $args['item'];

	// Start building link properties
	$props = array(
		'href'	=> $args['link'],
		'title'	=> $args['title'],
		'class'	=> ''
	);

	// Fix for youtu.be links
	if ( strpos( $props['href'], 'http://youtu.be/' ) !== false ) {
		$props['href'] = str_replace( 'http://youtu.be/', 'http://youtube.com/watch?v=', $props['href'] );
	}

	// Lightbox type
	$types = array( 'image', 'iframe', 'inline', 'ajax' );
	$type = $args['type'];

	if ( ! in_array( $type, $types ) ) {

		// Auto lightbox type detection
		if ( strpos( $props['href'], 'youtube.com' ) !== false || strpos( $props['href'], 'vimeo.com' ) !== false || strpos( $props['href'], 'maps.google.com' ) !== false ) {

			$type = 'iframe';

		} else if ( strpos( $props['href'], '#' ) === 0 ) {

			$type = 'inline';

		} else {

			$parsed_url = parse_url( $props['href'] );
			$filetype = wp_check_filetype( $parsed_url['path'] );

			// Link to image file?
			if ( substr( $filetype['type'], 0, 5 ) == 'image' ) {
				$type = 'image';
			}
		}

	}

	// CSS classes
	$class = array( 'eman-lightbox', "mfp-{$type}" );

	if ( 'iframe' == $type ) {
		$class[] = 'lightbox-iframe'; // Enables framework's separate JS for iframe video handling in non-galleries
	}

	$user_class = $args['class'];
	if ( ! is_array( $args['class'] ) ) {
		$user_class = explode(' ', $args['class'] );
	}

	$class = array_merge( $class, $user_class );
	$props['class'] = implode( ' ', $class );

	// Add user any additional properties passed in
	if ( is_array( $args['props'] ) ) {
		$props = array_merge( $props, $args['props'] );
	}

	// Use properties array to build anchor tag
	$output = '<a ';
	foreach ( $props as $key => $value ) {
		$output .= "{$key}=\"{$value}\" ";
	}
	$output = rtrim( $output, ' ' );

	// Manual addon
	if ( $args['addon'] ) {
		$output .= ' '.$args['addon'];
	}

	// Finish link
	$output .= sprintf( '>%s</a>', $item );

	return $output;
}