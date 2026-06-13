<?php

/**
 * Get status
 */
$status = emanager_post::status($post, 'slug');


/**
 * Current post type settings
 */
$cpt      = eman_post_types(get_post_type());


$group_id = $cpt['form'];
?>


<h2><?php the_title(); ?></h2>


<?php if ( 'settings' != $cpt['type'] ) : ?>
<div class="statuses">
	<?php
	$charge_slugs = array();
	if ( 'em_tickets' == get_post_type() && 'approved' == $status ) :
		$taxonomy     = 'em_charge';
		$terms        = wp_get_post_terms( $post->ID, $taxonomy );
		foreach ( $terms as $term ) :
			$charge_slugs[] = $term->slug;
		endforeach; ?>
		<div class="submission-status charge-status" style="padding-left:0.5em;">
			<p>
			<?php if ( eman_check_role('turner') || eman_check_role('sub') ) : ?>
				<input type="checkbox" id="charge_billed" name="charge_billed" value="billed"<?php if ( in_array('billed', $charge_slugs) ) echo ' checked="checked"'; ?> /> <label>Billed</label><br />
			<?php endif; ?>
			<?php if ( eman_check_role('turner') ) : ?>
				<input type="checkbox" id="charge_paid" name="charge_paid" value="paid"<?php if ( in_array('paid', $charge_slugs) ) echo ' checked="checked"'; ?> /> <label>Paid</label>
			<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( in_array(get_post_type(), array('em_tickets','em_noc','em_dcr','em_issue','em_letter', 'em_rfi', 'em_invoice', 'em_request')) ) : ?>
		<div class="submission-status current-status">
			<span class="label">Status:</span> <?php echo emanager_post::status($post, 'simple'); ?> <span class="charge"><?php if ( in_array('paid', $charge_slugs) ) : ?>(Paid)<?php elseif ( in_array('billed', $charge_slugs) ) : ?>(Billed)<?php endif; ?></span>
		</div>
		<?php $bic_username = ( $bic = emanager_bic::get_bic($post) ) ? emanager_bic::get_bic($post, 'display_name') : 'Unassigned'; ?>
		<div class="submission-status">
			<span class="label">BIC:</span> <?php echo $bic_username; echo ( ! empty($bic->company) ) ? ', ' . get_the_title($bic->company) : ''; ?>
		</div>
		<div class="submission-status">
			<?php $author = get_user_by('id', $post->post_author); ?>
			<span class="label">Author:</span> <?php echo eman_users_name($author);#get_the_author(); ?>, <?php echo emanager_post::user_company( $post->post_author ); ?>
		</div>
		<div class="submission-status">
			<span class="label">Date:</span> <?php echo get_the_date(); ?>
		</div>
	<?php endif; ?>

	<?php if ( 'em_noc' == get_post_type() ) : ?>
		<div class="submission-status">
			<span class="label">Request #:</span> <?php echo eman_get_field('request_number'); ?>
		</div>
	<?php endif; ?>

	<?php if ( 'em_letter' == get_post_type() ) : ?>
		<div class="submission-status">
			<span class="label">Days Outstanding:</span> <?php echo emanager_post::dateDiff(get_the_date(), date('Y-m-d')); ?>
		</div>
	<?php endif; ?>

	<?php if ( 'em_tickets' == get_post_type() ) : ?>
		<div class="submission-status">
			<span class="label">Ticket #:</span> <?php echo eman_get_field('request_number'); ?>
			<?php if ( eman_check_role('turner') ) : ?>
				<hr />
				<center>
					<a href="<?php echo site_url('/pco-tracker/?pcosearch=' . eman_get_field('pco_number')); ?>" class="btn btn-sm btn-info">
						Track <span class="fa fa-location-arrow"></span>
					</a>
				</center>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( 'em_issue' == get_post_type() && $display_pco = eman_get_field('display_pco') ) :
		if ( "No" != $display_pco ) : ?>
		<div class="submission-status">
			<span class="label">PCO #:</span> 
			<?php if ( eman_check_role('turner') ) : ?>
				<a href="<?php echo site_url('/pco-tracker/?pcosearch=' . eman_get_field('pco_number')); ?>"><?php eman_the_field('pco_number'); ?></a>
			<?php else : ?>
				<?php eman_the_field('pco_number'); ?>
			<?php endif; ?>
		</div>
	<?php endif;
	endif; ?>
</div>
<?php endif; ?>


<?php
#$final_segments = eman_form_fields( $cpt['form'], $post->ID );
$final_segments = eman_form_fields( acf_limit_field_groups($cpt['form'], false), $post->ID );

/**
 * Show all the fields
 */
?>
<div class="preview_segment preview_segment-<?php echo $post->ID; ?> preview_group-<?php echo ( is_array($group_id) ? implode('-', $group_id) : $group_id ); ?>">
	<?php 
	$labor_total = $material_total = $equipment_total = 0;
	$count = 0;

	foreach ( $final_segments as $field ) :
		$show = true;

		/**
		 * Make sure there is equipment before showing
		 */
		if (
			('equipment' == $field['name'] || 'equipment_markup' == $field['name'] || 'Equipment Breakdown' == $field['label']) &&
			( empty($final_segments['equipment']['value'][0]['name']) || 'null' == $final_segments['equipment']['value'][0]['name'] )
		) {
			$show = false;
		}

		/**
		 * Make sure there is a material before showing
		 */
		if (
			( in_array( $field['name'], array('materials', 'material', 'material_markup') ) || 'Material Delivered to Site' == $field['label']) &&
			( empty($final_segments['materials']['value'][0]['name']) || 'null' == $final_segments['materials']['value'][0]['name'] )
		) {
			$show = false;
		}

		if ( $show && 'bic' != $field['name'] && ( ! empty($field['value']) || 'message' == $field['type'] ) ) :
			// Fix message field id
			if ( 'message' == $field['type'] ) {
				$field['id'] .= str_replace('field_', '', $field['key']); 
			}
?>
			<?php if ( 'global_markup' == $field['name'] ) : ?>
				<hr class="divider divider-after" />
			<?php endif; ?>
			<div id="<?php echo $field['id']; ?>" class="preview_field <?php echo ('message' == $field['type'] ? 'message_field ' : ''); ?><?php echo ($count%2 ? 'even' : 'odd'); ?><?php if ( ! empty($field['name']) ) echo ' ' . $field['name']; ?> clearfix">
				<?php if ( 'message' != $field['type'] ) : ?>
					<div class="field_title">
						<strong><?php echo $field['label']; ?></strong>
					</div>
				<?php endif; ?>
				<div class="<?php echo ('message' == $field['type'] ? 'field_message' : 'field_value'); ?>">
					<?php
					if ( 'laborbd' == $field['name'] ) :
						$breakdown_type = $field['value'];
					endif;

					if ( 'classification_breakdown' == $field['name'] ) :
						$summary         = emanager_summary::classification_breakdown($field, $post);
						$labor_total     = $summary['totals']['rows'];
						emanager_summary::table_view($summary);

					elseif ( 'employee_breakdown' == $field['name'] ) :
						$summary         = emanager_summary::employee_breakdown($field, $post);
						$labor_total     = $summary['totals']['rows'];
						emanager_summary::table_view($summary);

					elseif ( 'materials' == $field['name'] || 'material' == $field['name'] ) :
						$summary         = emanager_summary::materials($field, $post);
						$material_total  = $summary['totals']['rows'];
						emanager_summary::table_view($summary);

					elseif ( 'equipment' == $field['name'] ) :
						$summary         = emanager_summary::equipment($field, $post);
						$equipment_total = $summary['totals']['rows'];
						emanager_summary::table_view($summary);

					elseif ( 'labor_markup' == $field['name'] || 'material_markup' == $field['name'] || 'equipment_markup' == $field['name'] ) :
						if ( 'labor_markup' == $field['name'] ) :
							$total = $labor_total;
						elseif ( 'material_markup' == $field['name'] ) :
							$total = $material_total;
						elseif ( 'equipment_markup' == $field['name'] ) :
							$total = $equipment_total;
						endif;

						if ( $field['value'] ) :
							$summary         = emanager_summary::markup($field['value'], $total);
							$markup_total    = $summary['total']; ?>
							<ul class="list-group">
								<?php foreach ( $summary['rows'] as $row ) : ?>
									<li class="list-group-item"><?php echo $row['description']; ?>: <?php echo $row['value']; ?> <span class="pull-right"><?php echo $row['amount']; ?></span></li>
								<?php endforeach; ?>
								<li class="list-group-item list-group-item-info">Markup Subtotal: <span class="pull-right"><?php echo ($markup_total ? '$' . eman_number_format($total + $markup_total) : "&mdash;"); ?></span></li>
							</ul>
						<?php endif;

						if ( 'labor_markup' == $field['name'] ) :
							$labor_total     += $markup_total;
						elseif ( 'material_markup' == $field['name'] ) :
							$material_total  += $markup_total;
						elseif ( 'equipment_markup' == $field['name'] ) :
							$equipment_total += $markup_total;
						endif;

					elseif ( 'global_markup' == $field['name'] ) :
						$final_total = $total = $overall_total = $labor_total + $material_total + $equipment_total;

						$markup = $field['value'][0]['value']; ?>

						<ul class="list-group">
						<?php if ( $field['value'] ) : ?>
							<li class="list-group-item">Subtotal: <span class="pull-right"><?php echo '$' . eman_number_format( $total ); ?></span></li>
							<?php $markup_total = 0;
							foreach ( $field['value'] as $markup ) :
								if ( is_numeric($markup['value']) && 0 < $markup['value'] ) :
									$markup_amount = round($total * ($markup['value']/100));
									$markup_total += $markup_amount; ?>
									<li class="list-group-item">
										<?php echo ucwords( str_replace(array('_','-'), ' ', $markup['description']) ); ?>: <?php echo $markup['value']; ?>% 
										<span class="pull-right">$<?php echo eman_number_format($markup_amount); ?></span>
									</li>
								<?php endif;
							endforeach; ?>

							<li class="list-group-item list-group-item-info">
								Markup Subtotal: <span class="pull-right"><?php echo ($markup_total ? '$' . eman_number_format($total + $markup_total) : "&mdash;"); ?></span>
							</li>
						<?php endif; ?>
						</ul>

						<?php $final_total = $total + $markup_total; ?>

							</div>
						</div>
							<div class="final_total clearfix">
								<div class="group-total clearfix">
									<ul class="list-group">
										<li class="list-group-item list-group-item-info">
											Total:
											<span class="pull-right">$<?php echo eman_number_format($final_total); ?></span>
										</li>
									</ul>
							

					<?php elseif ( 'contractors_and_estimate' == $field['name'] ) : ?>
						<div class="panel panel-default"><table class="table">
							<?php $total = $row_count = 0;
							foreach ( $field['value'] as $row ) :
								if ( ! $row_count ) :
									$item_count=0; ?>
									<tr>
									<?php foreach ( $row as $key => $item ) : ?>
										<th<?php echo ( ! $item_count ? ' class="first-item"' : '' ); ?>>
											<?php echo ucwords( str_replace(array('_','-'), ' ', $key) ); ?>
										</th>
									<?php $item_count++; endforeach; ?>
									</tr>
								<?php endif; ?>
	
								<tr>
									<?php $item_count=0;
									foreach ( $row as $key => $item ) : ?>
										<td<?php echo ( ! $item_count ? ' class="first-item"' : '' ); ?>>
											<?php if ( 'subcontractor' == $key ) :
												echo $item->post_title;
											elseif ( 'estimated_value' == $key ) :
												echo '$' . eman_number_format( $item );
												$total += $item;
											elseif ( is_object($item) ) :
												echo $item->post_title;
											else :
												echo $item;
											endif; ?>
										</td>
									<?php $item_count++; endforeach; ?>
								</tr>
							<?php $row_count++; endforeach; ?>
							<tr class="summary-total-row">
								<td class="first-label">
									<span class="label label-default">Total:</span>
								</td>
								<td>
									<span class="total"><strong>$<?php echo eman_number_format($total); ?></strong></span>
								</td>
								<td></td>
								<td></td>
							</tr>
						</table></div>

					<?php elseif ( 'scope' == $field['name'] ) : ?>
						<div class="panel panel-default">
							<?php if ( ! empty($field['value']) ) :
								// Loop first, to make sure there is something to show
								$output = '';
								$has_location = false;
								foreach ( $field['value'] as $row ) :
									// Make sure this row has some info
									$row_empty = true;
									if ( is_array( $row ) ) {
										foreach ( $row as $item ) {
											if ( ! empty( $item ) ) {
												$row_empty = false;
											}
										}
									}

									// If the row is not empty, show it
									if ( ! $row_empty ) :
										$has_location = true;
										ob_start();
?>
									<tr>
										<td class="task"><?php echo ( $row['task'] ? $row['task'] : "&mdash;" ); ?></td>
										<td class="location">
<?php 
											$building = ( $row['building'] ? get_the_title( $row['building'] ) : null );
											$floor    = ( $row['floor'] ? get_the_title( $row['floor'] ) : null );
											$scope    = $row['scope'];
											echo ( $building ? "$building" : '' ) . ( $floor ? " > $floor" : '' ) . ( $scope ? ": $scope" : '' );
?>
										</td>
									</tr>
<?php
										$output .= ob_get_clean();
									endif;
								endforeach;
							endif;

							if ( $has_location ) :
?>
								<table class="table">
									<tr>
										<th class="task">Task</th>
										<th class="location">Location</th>
									</tr>
									<?php echo $output; ?>
								</table>
							<?php else : ?>
								<div class="panel-heading">No locations</div>
							<?php endif; ?>
						</div>
						<?php

					elseif ( 'backup' == $field['name'] || 'attachments' == $field['name'] ) : ?>
						<ul>
						<?php foreach ( $field['value'] as $row ) : ?>
							<?php foreach ( $field['sub_fields'] as $sub_field ) :
								$sub_field['value'] = $row[ $sub_field['name'] ]; ?>
								<li><?php echo emanager_post::display_backup($sub_field['value']); ?></li>
							<?php endforeach; ?>
						<?php endforeach; ?>
						</ul>

					<?php elseif ( 'message' == $field['type'] ) :
						echo $field['message'];

					else :
						echo eman_field_value($field, $post);

					endif; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php $count++; endforeach; ?>
</div>


<?php 
/**
 * Add extra meta data about submitter
 */
?>
<div id="submitted-by" class="horizontal-field clearfix">
	<div class="field_title"><strong>Submitted by</strong></div>
	<div class="field_value"><?php echo eman_users_name( $post->post_author ); ?></div>
</div>
<div id="submitted-company" class="horizontal-field clearfix">
	<div class="field_title"><strong>Submitter company</strong></div>
	<div class="field_value"><?php echo emanager_post::user_company( $post->post_author ); ?></div>
</div>
<?php /** / ?><div id="submitted-date" class="horizontal-field clearfix">
	<div class="field_title"><strong>Submitted on</strong></div>
		<div class="field_value"><?php echo get_the_date(); ?></div>
</div><?php /**/


/**
 * Add disclaimer to tickets for submitted and super review.
 */
if ( 'em_tickets' == get_post_type() && (has_term('submitted', 'em_status', $post) || has_term('superintendent', 'em_status', $post)) ) : ?>
    <div class="preview_field odd clearfix"><div class="field_message"><hr class="divider divider-before" /></div></div>
    <div class="ticket-disclaimer">
        Verified time and material/equipment only. Pending final notification of cost and contractual obligation.
    </div>
<?php
endif;
