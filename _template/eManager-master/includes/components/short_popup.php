<?php

/**
 * Popup (from Bootstrap)
 *
 * @param array $atts Standard WordPress shortcode attributes
 * @param string $content Content in shortcode
 * @return string $output Content to output for shortcode
 */
add_shortcode( 'popup', 'eman_modal' );
add_shortcode( 'modal', 'eman_modal' );
function eman_modal( $atts, $content = null )
{
    $default = array(
    	'text' 			=> 'Link Text', // Text for link or button leading to popup
		'title' 		=> '', 			// Title for anchor, will default to "text" option
		'color' 		=> 'default', 	// Color of button, only applies if button style is selected
		'size'			=> '',			// Size of modal,
		'btn_class'		=> '',			// Class of button,
		'btn_size'		=> '',			// Size of button,
		'icon_before'	=> '', 			// Icon before button or link's text
		'icon_after' 	=> '', 			// Icon after button or link's text
		'header' 		=> '', 			// Header text for popup
		'footer' 		=> '', 			// Footer text for popup
		'animate' 		=> 'false',		// Whether popup slides in or not - true, false
		'class'         => '',          // Extra classes for modal
    );
    extract( shortcode_atts( $default, $atts ) );

    // ID for popup
    $popup_id = uniqid( 'popup_'.rand() );

    // Button/Link
    if ( $title ) {
    	$title = $text;
    }

    // Modal size
    if ( in_array($size, array('large','wide')) ) {
    	$size = 'lg';
    }

    // Classes for popup
    $class .= ' modal';
    if ( $animate == 'true' ) { $class .= ' fade'; }
    /**/
	echo eman_button( array(
		'text'        => $text,
		'url'         => "#$popup_id",
		'color'       => $color,
		'target'      => null,
		'size'        => $btn_size,
		'classes'     => $btn_class,
		'title'       => $title,
		'icon_before' => $icon_before,
		'icon_after'  => $icon_after,
		'addon'       => 'data-toggle="modal"',
	) );
	/**/
?>
	<div class="<?php echo esc_attr(trim($class)); ?>" id="<?php echo esc_attr($popup_id); ?>" tabindex="-1" role="dialog" aria-hidden="true">';
		<div class="modal-dialog<?php if ( $size ) { echo ' modal-' . $size; } ?>">
			<div class="modal-content">

				<?php if ( 'false' !== $header ) : ?>
				<div class="modal-header">
					<?php
					echo eman_button( array(
						'text'        => '&times;',
						'url'         => "#",
						'color'       => 'default',
						'target'      => null,
						'size'        => null,
						'classes'     => 'modal-close',
						'title'       => 'Close',
						'icon_before' => null,
						'icon_after'  => null,
						'addon'       => 'data-dismiss="modal"',
					) ); ?>
					<?php if ( $header ) : ?>
						<h3><?php echo $header; ?></h3>
					<?php endif; ?>
				</div><!-- modal-header (end) -->
				<?php endif; ?>

				<div class="modal-body">
					<?php echo apply_filters( 'the_content', $content ); ?>
				</div><!-- .modal-body (end) -->

				<?php if ( 'false' !== $footer ) : ?>
				<div class="modal-footer">
					<?php
					echo eman_button( array(
						'text'        => 'Close',
						'url'         => "#",
						'color'       => 'default',
						'target'      => null,
						'size'        => null,
						'classes'     => 'modal-close',
						'title'       => 'Close',
						'icon_before' => null,
						'icon_after'  => null,
						'addon'       => 'data-dismiss="modal"',
					) ); ?>
					<?php if ( $footer ) : ?>
						<p><?php echo $footer; ?></p>
					<?php endif; ?>
				</div><!-- .modal-footer (end) -->
				<?php endif; ?>

			</div><!-- .modal-content (end) -->
		</div><!-- .modal-dialog (end) -->
	</div><!-- .modal (end) -->
<?php
}