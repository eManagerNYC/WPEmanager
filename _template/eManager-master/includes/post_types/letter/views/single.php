<?php
$letter_number    = eman_get_field('letter_number');
$noc_number       = eman_get_field('noc_number');
$pco_number       = eman_get_field('pco_number');
$sub_number       = eman_get_field('sub_number');
$revision_number  = eman_get_field('revision');
$letter_type      = eman_get_field('letter_type');
$value            = eman_get_field('value');
$submitted_review = emanager_post::latest_review($post); 
?>

	<h2 class="letter-title" style="text-align:center; width:100%;">
		<?php if ( 'AL' === $letter_type ) : ?>
			Approval Letter
		<?php elseif ( 'COR' === $letter_type ) : ?>
			Change Order Request
		<?php elseif ( 'NOC' === $letter_type ) : ?>
			Notice Of Change
		<?php else : ?>
			Letter
		<?php endif; ?>
	</h2>

	<div class="meta-overview cf">
		<div class="left-meta cf">
		<dl>
			<dt>Letter #:</dt>
				<dd><?php echo $letter_number; ?></dd>
			<dt>PCO#:</dt>
				<dd><?php echo ($pco_number ? $pco_number : "&mdash;"); ?></dd>
			<dt>Contractor#:</dt>
				<dd><?php echo ($sub_number ? $sub_number : "&mdash;"); ?></dd>
			<dt>Revision Number:</dt>
				<dd><?php echo ($revision_number ? $revision_number : "&mdash;"); ?></dd>
			<dt>Status:</dt>
				<dd><?php echo emanager_post::status($post); ?></dd>
			<dt>Priority:</dt>
				<dd><?php eman_the_field('importance'); ?></dd>
		</dl>
		</div>
		<div class="right-meta clearfix">
		<dl>
			<dt>Title:</dt>
				<dd><?php the_title(); ?></dd>
			<dt>Value:</dt>
				<dd><?php echo ($value ? '$' . eman_number_format($value) : "&mdash;"); ?></dd>
			<dt>Submitted:</dt>
				<dd><?php 
					global $submitted_stamp;
/** /
					// Get the last review that submitted the letter
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
/**/
					#if ( $reviews && ! empty($reviews->post->post_date) ) {
					#	$submitted_stamp = strtotime($reviews->post->post_date);
					#	echo date_i18n( get_option('date_format'), $submitted_stamp );
					if ( $submitted_review && ! empty($submitted_review->post_date) ) {
						echo date_i18n( get_option('date_format'), strtotime($submitted_review->post_date) );
					} else {
						echo date_i18n( get_option('date_format'), strtotime($post->post_date) );
					}
?>
				</dd>
			<?php /** / ?><dt>NOC#:</dt>
				<dd><?php echo ($noc_number ? $noc_number : "&mdash;"); ?></dd><?php /**/ ?>
			<dt>Requested by:</dt>
				<dd><?php echo get_the_author_meta( 'display_name', $post->post_author ); ?></dd>
			<dt><?php /*Request */ ?>Reviewed by:</dt>
				<dd><?php echo get_the_author_meta( 'display_name', $submitted_review->post_author ); ?></dd>
			<?php if ( $bic = emanager_bic::get_bic($post, 'display_name') ) : ?>
				<dt>BIC:</dt>
					<dd><?php echo $bic; ?></dd>
			<?php endif; ?>
		</dl>
		</div>
	</div>

	<div class="letter-introduction">
		<p>This letter presents the following information associated with: <strong><?php the_title(); ?></strong></p>
		<?php /** / ?><p>We have reviewed the proposals from the listes subcontractors affected by this change and find them fair and reasonable. Please see the attached supporting documentation for a complete breakdown of the work. The following is a summary of these costs:</p><?php /**/ ?>
	</div>

	<?php
	/**
	 * Set up the subcontractors table, so we can get a total
	 */
	ob_start();
	$itemized = eman_get_field('itemized');
	if ( is_array($itemized) ) : ?>
		<table class="breakdown">
			<?php
			$total = 0;
			$row_count=0;
			foreach ( $itemized as $row ) :
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
				<?php $item_count=0;
				foreach ( $row as $key => $item ) : ?>
					<td<?php echo ( ! $item_count ? ' class="first-item"' : '' ); ?>>
						<?php if ( 'contract' == $key ) :
							echo $item->post_title;
						elseif ( 'amount_proposed' == $key ) :
							echo '$' . $item;
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
					<span class="label">Total:</span>
				</td>
				<td>
					<span class="total"><strong>$<?php echo eman_number_format($total); ?></strong></span>
				</td>
				<td></td>
				<td></td>
			</tr>';
		</table>
	<?php endif;
	$itemized_table = ob_get_clean(); ?>

		<div class="letter-body">
			<?php /** / ?><p>Additional default Comments to Owner (defined by project, to be default included in all letters and then editable)</p><?php /**/ ?>

			<?php if ( $description = eman_get_field('description') ) : ?>
			<div class="field_title"><strong>Additional comments</strong>:</div>
			<div id="acf-field-description" class="preview_field clearfix">
				<div class="field_value"><?php echo $description; ?></div>
			</div>
			<?php endif; ?>

			<?php /** / ?><p>Please return one (1) signed copy of this letter indicating your approval to [option 1] increase/decrease our contract by Proposed Amount (COR Proposed Amount in Words). [option 2] allocate Proposed Amount (COR Proposed Amount in Words ) against ‘funding source names’ (concatenate if multiple funding sources). (GMP or Cost Plus…) This approval will also authorize us to issue Subcontract Change Order(s) as outlined in the above table.</p><?php /**/ ?>

			<?php /** / ?><p>Unless previously authorized via Notice of Change (NOC) or other communication, this approval authorizes us to proceed with the work associated with this change.</p><?php /**/ ?>

			<p>Please feel free to contact me if you have any questions regarding this change.</p>
			<p>Sincerely,</p>
			<p><strong>Turner Construction Co.</strong></p>
		</div>

<?php
		$backups     = eman_get_field('backup');
		$file_exists = false;
		if ( ! is_array($backups) ) { $backups = array( array( 'file' => false ) ); }
		$count = 0;
		ob_start();
		foreach ( $backups as $backup ) :
			if ( ! empty($backup['file']) ) :
				$file_exists = true;
				$count++;
?>
				<li><?php echo emanager_post::display_backup( $backup['file'] ); ?></li>
<?php
			endif;
		endforeach;
		$content = ob_get_clean();
?>
		<div id="acf-field-backup" class="preview_field clearfix">
			<div class="field_title"><strong>View Attachment<?php echo (1<$count ? 's' : ''); ?></strong></div>
			<div class="field_value">
<?php

				if ( $file_exists ) :
?>
					<ol><?php echo $content; ?></ol>
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

		<?php $field = get_field_object('field_52b00dc4c2e782', $post->ID); ?>
		<div id="<?php echo $field['id']; ?>" class="preview_field <?php echo $field['name']; ?> clearfix">
			<div class="field_title"><strong><?php echo $field['label']; ?></strong>:</div>
			<div class="field_value">
				<?php echo eman_field_value($field, $post); ?>
			</div>
		</div>

<?php
		get_template_part('partials/single/summary-action');

		$status_array = wp_get_post_terms( get_the_id(), 'em_status' );
		$current_status = ( $status_array ) ? end($status_array) : null;
		if ( current_user_can('manage_options') && ( ! empty($current_status->name) && 'closed' != strtolower($current_status->name) ) ) :
?>
		<button class="btn btn-danger btn-lg contact-owner-send" id="letter_close" style="margin-top: 2em;" data-post_id="<?php echo get_the_id(); ?>">Close Out This Letter</button>
		<?php endif; ?>