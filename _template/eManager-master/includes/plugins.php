<?php

/**
 * Plugins list
 *
 * classname => relative url to plugin file
 */
add_action( 'after_setup_theme', 'eman_load_plugins' );
function eman_load_plugins()
{
	$eman_plugins = array(
		#'acf'                               => 'advanced-custom-fields/acf.php',
		#'Acf_Create_Object'                 => 'acf-create-object/acf-create-object.php',
		#'Acf_Edit_Title_Content'            => 'acf-edit-title-content/acf-edit-title-content.php',
		#'acf_field_date_time_picker_plugin' => 'acf-field-date-time-picker/acf-date_time_picker.php',
		#'Acf_Notify_Admin'                  => 'acf-notify-admin/acf-notify-admin.php',
		#'acf_options_page_plugin'           => 'acf-options-page/acf-options-page.php',
		#'acf_register_repeater_field'       => 'acf-repeater/acf-repeater.php',
		#'acf_field_signature_plugin'        => 'acf-signature-field/acf-signature-field.php',
		#'Comment_Image'                     => 'comment-images/comment-images.php',
		#'purge_transients'                  => 'purge-transients/purge-transients.php',
		#'sewn_avatars'                      => 'sewn-avatars/sewn-avatars.php',
		#'sewn_profiles'                     => 'sewn-customize-profiles/sewn-customize-profiles.php',
		#'Sewn_Email_Templates'              => 'sewn-email-templates/sewn-email-templates.php',
		#'sewn_maps'                         => 'sewn-maps/sewn-maps.php',
		#'Sewn_Login'                        => 'sewn-template-login/sewn-template-login.php',
		#'Sewn_Register'                     => 'sewn-template-register/sewn-template-register.php',
		#'Sewn_Subscriptions'                => 'sewn-subscriptions/sewn-subscriptions.php',
		#'Sewn_Messenger'                    => 'sewn-messenger/sewn-messenger.php',
		#'sewn_remove_pingbacks'             => 'sewn-remove-pingbacks/sewn-remove-pingbacks.php',
		#'Sewn_Notifications'                => 'sewn-notifications/sewn-notifications.php',
		#'sewn_remove_feeds'                 => 'sewn-remove-feeds/sewn-remove-feeds.php',
	);

	if ( is_array($eman_plugins) )
	{
		foreach ( $eman_plugins as $classname => $url )
		{
			// If the class does NOT exist (already loaded in plugins)
			if ( ! class_exists($classname) ) {
				require_once( get_template_directory() . '/includes/plugins/' . $url );
			}
			
		}
	}
}