<?php
/*
Plugin Name: Advanced Custom Fields: Signature
Plugin URI: http://Jupitercow.com/
Description: Use to collect a hand-drawn signature from users.
Version: 1.0.1
Author: jcow
Author URI: http://Jupitercow.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


class acf_field_signature_plugin
{
	/*
	*  Construct
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	*/

	function __construct()
	{
		// set text domain
		/*
		$domain = 'acf-signature';
		$mofile = trailingslashit(dirname(__File__)) . 'lang/' . $domain . '-' . get_locale() . '.mo';
		load_textdomain( $domain, $mofile );
		*/


		// version 4+
		add_action('acf/register_fields', array($this, 'register_fields'));
	}

	/*
	*  register_fields
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	*/

	function register_fields()
	{
		include_once('signature-v4.php');
	}

}

new acf_field_signature_plugin();

?>
