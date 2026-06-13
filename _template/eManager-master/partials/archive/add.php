<?php

$post_type = eman_archive_info('post_type');
$cpt       = eman_archive_info('cpt');

echo display_cpt_form($post_type, 'Add ' . eman_archive_info('single_title'), $cpt);


/**
 * filter labor types on add employee form
 */
if ( ('em_employees' == $post_type) && current_user_can('subcontractor') )
{
	$applicable_labortypes = array();
	$query = get_posts(array(
		'numberposts' => -1,
		'post_type'   => 'em_labortypes',
		'meta_key'    => 'company',
		'meta_value'  => get_user_meta(get_current_user_id(), 'company', true),
	));
	foreach ( $query as $lt ) {
		$applicable_labortypes[] = $lt->ID;
	}
	?>
	<script type="text/javascript">
	// <![CDATA[
		document.getElementById('acf-field-classification').setAttribute('data-allowValues', '<?php echo json_encode($applicable_labortypes); ?>');
	// ]]>
	</script>
	<?php
}


/**
 * subforms
 */
?>
<div id="emanager_subform">
	<div id="labortype_subform" class="subform">
<?php
		echo eman_modal( array(
			'text' => '',
			'header' => 'Add Labor Type',
			'footer' => 'false',
			'icon_before' => 'plus',
			'btn_size' => 'mini',
		), display_cpt_form('em_labortypes') );
?>
	</div>
	<div id="employee_subform" class="subform">
<?php
		echo eman_modal( array(
			'text' => '',
			'header' => 'Add Employee',
			'footer' => 'false',
			'icon_before' => 'plus',
			'btn_size' => 'mini',
		), display_cpt_form('em_employees') );
?>
	</div>
	<div id="material_subform" class="subform">
<?php
		echo eman_modal( array(
			'text' => '',
			'header' => 'Add Material',
			'footer' => 'false',
			'icon_before' => 'plus',
			'btn_size' => 'mini',
		), display_cpt_form('em_materials') );
?>
	</div>
	<div id="equipment_subform" class="subform">
<?php
		echo eman_modal( array(
			'text' => '',
			'header' => 'Add Equipment',
			'footer' => 'false',
			'icon_before' => 'plus',
			'btn_size' => 'mini',
		), display_cpt_form('em_equipment') );
?>
	</div>
</div>