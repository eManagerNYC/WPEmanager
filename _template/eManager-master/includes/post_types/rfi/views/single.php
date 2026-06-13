<?php
$rfi_number    = eman_get_field('rfi_number');
$pco_number       = eman_get_field('pco_number');
$rfi_type      = eman_get_field('rfi_type');
$submitted_review = emanager_post::latest_review($post); 
?>

	<h2 class="rfi-title"><center>
		<h2>Request For Information</h2>
		<p><strong><?php the_title(); ?></strong></p>
	</center></h2>

	<div class="rfi-meta clearfix">
		<div class="left-meta clearfix">
		<dl>
			<dt>rfi #</dt>
				<dd><?php echo $rfi_number; ?></dd>
			<dt>Submitted</dt>
				<dd><?php 
					global $submitted_stamp;
					// Get the last review that submitted the rfi
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
					$submitted_stamp = strtotime($reviews->post->post_date);
					echo date_i18n(get_option('date_format'), $submitted_stamp) ?>
				</dd>
			<dt>Status</dt>
				<dd><?php echo emanager_post::status($post); ?></dd>
			<dt>Priority</dt>
				<dd><?php eman_the_field('importance'); ?></dd>
		</dl>
		</div>
		<div class="right-meta clearfix">
			<p class="rfi-date-due">
				<strong>Date Submitted</strong> <?php get_the_date(); ?>
			</p>
			<p class="rfi-date-due">
				<strong>Due Date</strong> <?php eman_the_field('date_due'); ?>
			</p>
			<p class="rfi-requested-by">
				Requested by <?php echo get_the_author_meta( 'display_name', $post->post_author ); ?>
			</p>
			<p class="rfi-reviewed-by">
				Request Reviewed by <?php echo get_the_author_meta( 'display_name', $submitted_review->post_author ); ?>
			</p>
			<?php if ( $bic = emanager_bic::get_bic($post, 'display_name') ) : ?>
			<p class="bic">
				<strong>BIC:</strong> <?php echo $bic; ?>
			</p>
			<?php endif; ?>
		</div>
	</div>
		<div class="rfi-body">
			<h3>Question</h3>

			<div id="acf-field-question" class="preview_field clearfix">
				<div class="field_value"><?php eman_the_field('question'); ?></div>
			</div>

			<?php 
			$csi_division = eman_get_field('csi_division');
			if ( $csi_division ) : ?>
			<div id="acf-field-csi_division" class="preview_field clearfix">
				<div class="field_value"><?php echo $csi_division ?></div>
			</div>
			<?php endif; ?>

			<?php 
			$associated_with = eman_get_field('associated_with');
			if ( $associated_with ) : ?>
			<div id="acf-field-associated_with" class="preview_field clearfix">
				<div class="field_value"><?php echo $associated_with ?></div>
			</div>
			<?php endif; ?>

			<?php 
			$scope = eman_get_field('scope');
			if ( $scope ) : ?>
			<div id="acf-field-scope" class="preview_field clearfix">
				<div class="field_value"><?php echo $scope ?></div>
			</div>
			<?php endif; ?>

			<?php 
			$drawing_number = eman_get_field('drawing_number');
			if ( $drawing_number ) : ?>
			<div id="acf-field-drawing_number" class="preview_field clearfix">
				<div class="field_value"><?php echo $drawing_number ?></div>
			</div>
			<?php endif; ?>

			<?php 
			$specification = eman_get_field('specification');
			if ( $specification ) : ?>
			<div id="acf-field-specification" class="preview_field clearfix">
				<div class="field_value"><?php echo $specification ?></div>
			</div>
			<?php endif; ?>


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
