<?php
/**
 * Current post type settings
 */
$cpt = eman_post_types(get_post_type());
?>

<h2><?php the_title(); ?></h2>

<?php if ( in_array(get_post_type(), array('em_tickets','em_noc','em_dcr')) ) : ?>
	<div class="submission-status">
		Current Status = <span><?php echo emanager_post::status($post); ?></span>
	</div>
<?php endif; ?>

<form id="post" class="acf-form form-archive form-<?php echo get_post_type(); ?>" method="post">
<?php if ( $cpt['form'] || $cpt['title_label'] ) :
	$field_groups = array();
	// Add company field for turner
	#if ( 'settings' == $cpt['type'] && HAS_ROLE_TURNER && in_array($posttype, $emanager_sub_settings_cpts) ) $field_groups[] = 'acf_post-company';
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
			'field_groups' => $field_groups,
			'form' => false
		) );
	}
endif; ?>
<div class="field field-submit clearfix">
	<?php /** / ?><input type="submit" value="Update"><?php /**/ ?>
	<button class="btn btn-primary btn-lg">Update</button>
	<?php if ( ! empty($_GET['edit']) ) : ?>
		<a href="<?php the_permalink(); ?>" class="btn btn-default btn-lg">Cancel</a>
	<?php endif; ?>
</div>
</form>