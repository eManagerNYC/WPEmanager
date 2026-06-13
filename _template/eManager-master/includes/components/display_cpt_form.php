<?php

/**
 * Creates a form for custom post type using the settings array
 */
function display_cpt_form( $post_type, $title=false, $cpt=false )
{
	global $form_post_type;

	$secondary = false;
	if ( ! $cpt ) {
		$cpt = eman_post_types($post_type);
		$secondary = true;
	}

	$form_post_type = $post_type;

	ob_start();
?>
	<?php if ( $title ) : ?><h2><?php echo $title; ?></h2><?php endif; ?>

	<?php if ( ! $secondary && 'em_invoice' != get_post_type() ) : ?>
		<a class="copy-previous-form btn btn-default" href="<?php echo esc_url( add_query_arg( ['autofill' => '1', 'n' => wp_create_nonce('emanAutofillForm')], remove_query_arg('company') ) ); ?>">
			<span class="fa fa-copy" aria-hidden="true"></span>
			Autofill from last entry
		</a>
	<?php endif; ?>

	<form id="post" class="acf-form form-archive form-<?php echo $post_type; ?>" method="post"<?php if ( ! empty($GLOBALS['autofill_id']) ) { echo ' data-ap="' . $GLOBALS['autofill_id'] . '"'; } ?>>
<?php
		if ( $cpt['form'] || $cpt['title_label'] ) :
			$field_groups = array();
			// Add company field for turner
			#if ( 'settings' == $cpt['type'] && HAS_ROLE_TURNER && in_array($post_type, $emanager_sub_settings_cpts) ) $field_groups[] = 'acf_post-company';
			// Set up the settings field groups specific to post type
			if ( ! is_array($cpt['form']) ) {
				$cpt['form'] = array($cpt['form']);
			}

			$cpt['form'] = acf_limit_field_groups( $cpt['form'] );
			// Add title if that is in settings
			if ( ! empty($cpt['title_label']) ) {
				$field_groups[] = 'acf_post-title-content';
			}
			// Merge them
			$field_groups = array_merge($field_groups, $cpt['form']);
	
			if ( function_exists('acf_form') ) {
				acf_form( array(
					'post_id'      => 'new_post',
					'field_groups' => $field_groups,
					'submit_value' => 'Submit',
					'form' => false
				) );
			}
		endif;
?>
		<div class="field field-submit clearfix">
			<button class="btn btn-primary<?php echo ( ! $secondary ? ' btn-lg' : ''); ?>">Save</button>
		</div>
	</form>
<?php
	// ACF is outputing this stupid commented html and it screws everything up in the modals
	return str_replace( '<!-- <div id="poststuff"> -->', '', ob_get_clean() );
}