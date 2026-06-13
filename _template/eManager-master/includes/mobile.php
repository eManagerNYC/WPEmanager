<?php
/**
 * Mobile and screen based functionality
 */

/**
 * Includes the Mobile_Detect class if it doesn't exist and adds a static instance for use
 *
 * @return  void
 */
function eman_mobile_detect()
{
	static $eman_mobile;
	if ( ! $eman_mobile )
	{
		if ( ! class_exists('Mobile_Detect') ) {
			require_once get_template_directory() . '/includes/Mobile_Detect.php';
		}
		$eman_mobile = new Mobile_Detect;
	}
	return $eman_mobile;
}

/**
 * eman_mobile_classes
 *
 * Used to create mobile classes for cache busting when needed.
 *
 * Outputs:
 *		mobile  = any mobile device (phone or tablet) vs desktop
 *		phone   = phone mobile, not tablet
 *		tablet  = tablet mobile, not phone
 *		desktop = not mobile
 *
 * @return string Space separated list of classes
 */
add_action( 'wp_ajax_eman_mobile_classes', 'eman_mobile_classes' );
add_action( 'wp_ajax_nopriv_eman_mobile_classes', 'eman_mobile_classes' );
function eman_mobile_classes()
{
	$output = '';
	$detect = eman_mobile_detect();
	if ( $detect->isMobile() )
	{
		$output = 'mobile';
		if ( $detect->isTablet() ) {
			$output .= ' tablet';
		} else {
			$output .= ' phone';
		}
	}
	else
	{
		$output = 'desktop';
	}
	echo $output;
	die;
}