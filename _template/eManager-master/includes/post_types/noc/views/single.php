<?php
$noc_numbers      = eman_noc_numbers( $post );
$noc_number       = $noc_numbers['noc'];# eman_get_field('noc_number');
$pco_number       = $noc_numbers['pco'];# eman_get_field('pco_number');
$submitted_review = emanager_post::latest_review($post);
$noc_title = 'Notification of Change (NOC)';
$name      = 'NOC';
if ( false !== strpos( $_SERVER['SERVER_NAME'], 'nyuk') ) {
	$noc_title = 'Potential Change Order (PCO)';
	$name      = 'PCO';
}
?>
	<h2 class="noc-title"><?php echo $noc_title; ?></h2>

	<div class="meta-overview cf">
		<div class="left-meta cf">
			<dl>
				<dt>NOC#</dt>
					<dd><?php echo $noc_number; ?></dd>
				<dt>Submitted</dt>
					<dd>
<?php 
						global $submitted_stamp;
						// Get the last review that submitted the noc
						$reviews = new WP_Query( array(
							'post_type' => 'em_reviews',
							'posts_per_page' => 1,
							'order' => 'DESC',
							'orderby'=> 'date',
							'tax_query'     => array(
								array(
									'taxonomy' => 'em_status',
									'field'    => 'slug',
									'terms'    => 'submitted',
								)
							),
							'meta_query' => array(
								array(
									'key' => 'reviewed_id',
									'value' => get_the_ID(),
									'compare' => '=',
								)
							)
						) );
						if ( ! empty($reviews->post) ) :
							$submitted_stamp = strtotime($reviews->post->post_date);
							echo date_i18n( get_option('date_format'), $submitted_stamp );
						endif;
?>
					</dd>
				<dt>Status</dt>
					<dd><?php echo emanager_post::status($post); ?></dd>
				<dt>Priority</dt>
					<dd><?php eman_the_field('importance'); ?></dd>
			</dl>
		</div>
		<div class="right-meta clearfix">
			<dl>
				<dt>PCO#</dt>
					<dd><?php echo $pco_number; ?></dd>
				<dt>PCO Requested</dt>
					<dd><?php echo get_the_author_meta( 'display_name', $post->post_author ); ?></dd>
				<dt>Reviewed</dt>
					<dd><?php echo get_the_author_meta( 'display_name', $submitted_review->post_author ); ?></dd>
				<?php if ( $bic = emanager_bic::get_bic($post, 'display_name') ) : ?>
				<dt>BIC</dt>
					<dd><?php echo $bic; ?></dd>
				<?php endif; ?>
			</dl>
		</div>
	</div>

<?php
	/**
	 * Set up the subcontractors table, so we can get a total
	 */
	ob_start();
	$contractors_and_estimate = eman_get_field('contractors_and_estimate');
	if ( is_array($contractors_and_estimate) ) :
?>
	<div class="responsive-table panel panel-default">
		<table class="table breakdown">
			<?php $total = 0;
			$row_count=0;
			foreach ( $contractors_and_estimate as $row ) :
				if ( ! $row_count ) :
					$item_count=0; ?>
					<tr>
					<?php foreach ( $row as $key => $item ) : ?>
						<th<?php echo ( ! $item_count ? ' class="first-item"' : ''); ?>>
							<?php echo ucwords( str_replace(array('_','-'), ' ', $key) ); ?>
						</th>
					<?php $item_count++; endforeach; ?>
					</tr>
				<?php endif; ?>
	
				<tr>
<?php
					$item_count=0;
					foreach ( $row as $key => $item ) :
?>
						<td<?php echo ( ! $item_count ? ' class="first-item"' : ''); ?>>
							<?php if ( 'subcontractor' == $key ) :
								echo $item->post_title;
							elseif ( 'estimated_value' == $key ) :
								echo '$' . $item;
								$total += $item;
							elseif ( is_object($item) ) :
								echo $item->post_title;
							else :
								echo $item;
							endif; ?>
						</td>
<?php
						$item_count++;
					endforeach;
?>
				</tr>
			<?php $row_count++; endforeach; ?>
			<tr class="summary-total-row">
				<td class="first-label">
					<span class="label">Total:</span>
				</td>
				<td>
					<span class="total"><strong>$<?php echo eman_number_format($total); ?></strong></span>
				</td>
				<td></td>
				<td></td>
			</tr>
		</table>
	</div>
<?php
	endif;
	$subcontractors_table = ob_get_clean();
?>

	<div class="noc-introduction">
		<p>This <?php echo $name; ?> is our formal notice regarding the following potential change to the project:</p>

		<h3 class="noc-subject"><?php the_title(); ?></h3>

		<p>We have assigned PCO Number <?php eman_the_field('pco_number'); ?> to track all future correspondence regarding this potential change in scope.</p>
			
		<p>A preliminary review indicates that there will be a cost impact to the project of approximately $<?php echo eman_number_format( $total ); ?>. This estimate is subject to change when we receive final pricing from our subcontractors.</p>
	</div>

	<div class="noc-content">
		<p>
			<?php $schedule_impact = eman_get_field('schedule_impact'); ?>
			<?php if ( 'Yes' == $schedule_impact ) : ?>
				In addition, there will be a possible delay of <?php eman_the_field('impact_duration'); ?> days to the project schedule. We will keep you in formed as we continue to refine the details of this change.
			<?php elseif ( 'Cannot Determine' == $schedule_impact ) : ?>
				We cannot determine at this time the effect on the contract completion date, or other work under the contract and will advise when a full analysis has been made.
			<?php endif; ?>
		</p>
	
		<p>
			<?php $direct_to_proceed = eman_get_field('direct_to_proceed'); ?>
			<?php if ( 'No' == $direct_to_proceed ) : ?>
				Execution of this NOC is required in order to relea se work. Please confirm your direction regarding this change by signing and dating one of the options below.
			<?php elseif ( 'Writing' == $direct_to_proceed ) : ?>
				As directed in writing by <?php eman_the_field('by_whom'); ?>, we are proceeding at once to procure materials and/or perform the work in order to complete this change at the earliest possible time. In the event that you do not approv e of such action, please advise immediately so that we may stop this effort and minimize the cost impact. Please confirm your direction regarding this change by signing and dating one of the options below.
			<?php elseif ( 'Verbal' == $direct_to_proceed ) : ?>
				As directed verbally by <?php eman_the_field('by_whom'); ?>, we are proceeding at once to procure materials and/or perform the work in order to complete this change at the earliest possible time. In the event that you do not approv e of such action, please advise immediately so that we may stop this effort and minimize the cost impact. Please confirm your direction regarding this change by signing and dating one of the options below.
			<?php endif; ?>
		</p>
	</div>

	<div class="preview_segment">

		<div id="acf-field-scope_estimate" class="preview_field clearfix">
			<div class="field_message">
				<hr class="divider divider-before">
					<h2>Scope &amp; Cost Estimate</h2>
				<hr class="divider divider-after">
			</div>
		</div>

		<div id="acf-field-description" class="preview_field clearfix">
			<div class="field_title"><strong>Description of Scope:</strong></div>
			<div class="field_value"><?php eman_the_field('description'); ?></div>
		</div>
		<div id="acf-field-location" class="preview_field clearfix">
			<div class="field_title"><strong>Location(s):</strong></div>
			<div class="field_value">
				<div class="panel panel-default">
<?php 
					$field = get_field_object('scope');
					#echo eman_field_value($field, $post);

					if ( ! empty($field['value']) ) :
						$has_location = false;
						$output = '';
						foreach ( $field['value'] as $row ) :
							$location = false;
							if ( $row['location'] ) :
								$location = 'location';
							elseif ( $row['floor'] ) :
								$location = 'floor';
							elseif ( $row['building'] ) :
								$location = 'building';
							endif;
							$output .= '<tr>';
								foreach ( $field['sub_fields'] as $sub_field ) :
									if ( $location == $sub_field['name'] ) :
										$sub_field['value'] = $row[ $sub_field['name'] ];
										if ( $sub_field['value'] ) $has_location = true;
										$output .= '<td class="' . $sub_field['name'] . '">' . get_the_title($sub_field['value']) . '</td>';
									endif;
									if ( 'scope' == $sub_field['name'] ) :
										$sub_field['value'] = $row[ $sub_field['name'] ];
										$output .= '<td class="' . $sub_field['name'] . '">' . eman_field_value($sub_field, $post) . '</td>';
									endif;
								endforeach;
							$output .= '</tr>';
						endforeach;
					endif;

					if ( $has_location ) :
?>
						<table class="table">
							<tr>
								<th class="location">Location</th>
								<th class="scope">Scope</th>
							</tr>
							<?php echo $output; ?>
						</table>
<?php
					else :
?>
						<div class="panel-heading">No locations</div>
<?php
					endif;
?>
				</div>
			</div>
		</div>
		<div id="acf-field-sap_reason" class="preview_field clearfix">
			<div class="field_title"><strong>Reason:</strong></div>
			<div class="field_value">
<?php 
				$field = get_field_object('sap_reason');
				echo eman_field_value($field, $post);
?>
			</div>
		</div>
		<div id="acf-field-cor_type" class="preview_field clearfix">
			<div class="field_title"><strong>COR Type:</strong></div>
			<div class="field_value">
<?php 
				$field = get_field_object('cor_type');
				echo eman_field_value($field, $post);
?>
			</div>
		</div>
		<div id="acf-field-start_date" class="preview_field clearfix">
			<div class="field_title"><strong>Estimated Work Start:</strong></div>
			<div class="field_value">
<?php 
				$field = get_field_object('start_date');
				echo eman_field_value($field, $post);
?>
			</div>
		</div>

		<div id="acf-field-contractors_and_estimate" class="preview_field clearfix">
			<?php echo $subcontractors_table; ?>
		</div>

<?php
		$backups     = eman_get_field('backup');
		$file_exists = false;
		if ( ! is_array($backups) ) { $backups = array( array( 'file' => false ) ); }
?>
		<div id="acf-field-backup" class="preview_field clearfix">
			<div class="field_title"><strong>View Attachments:</strong></div>
			<div class="field_value">
<?php
				ob_start();
				foreach ( $backups as $backup ) :
					if ( ! empty($backup['file']) ) :
						$file_exists = true;
?>
						<li><?php echo emanager_post::display_backup( $backup['file'] ); ?></li>
<?php
					endif;
				endforeach;
				$content = ob_get_clean();

				if ( $file_exists ) :
?>
					<ol>
						<?php echo $content; ?>
					</ol>
<?php
				else :
?>
					<div class="panel panel-default"><div class="panel-body" style="text-align:center;">
						No attachments
					</div></div>
<?php
				endif;
?>
			</div>
		</div>

	</div>

<?php
	/**
	 * If current user is a gatekeeper, offer the pullback button to remove this NOC from submitted status
	 */
	$status = emanager_post::status($GLOBALS['post'], 'slug');
	if ( 'submitted' == $status ) :
		// Make sure the current user has access to pullback
		$gatekeepers  = eman_get_field('noc_gatekeeper', 'option');
		$approver_ids = array();
		if ( is_array($gatekeepers) ) :
			foreach ( $gatekeepers as $gatekeeper ) :
				$approver_ids[] = $gatekeeper['ID'];
			endforeach;
		endif;
		if ( in_array(get_post_type(), $approver_ids) ) :
?>
			<a class="pullbak btn btn-warning" href="<?php echo add_query_arg( 'action', 'pullback', get_permalink() ); ?>" title="Revert NOC from submitted status.">Pull Back</a>
<?php
		endif;
	endif;

get_template_part('partials/single/summary-action');