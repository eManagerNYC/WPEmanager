<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<h3 class="entry-title single-title" itemprop="headline"><?php the_title(); ?></h3>

	<section class="em_issues">
		<div class="col-md-6">
			<p><strong>Issue ID</strong>: <?php // add id ?></p>
			<p><strong>Type</strong>: <?php echo eman_get_field('issue_type'); ?></p>
			<p><strong>Contract</strong>: <?php echo get_field_object('contract'); ?></p>
		</div>
		<div class="col-md-6">
			<strong>Author</strong>: <?php the_author(); ?></p>
			<strong>Date Created</strong>: <?php the_date(); ?></p>
			<?php if ( in_array(get_post_type(), array('em_issue')) ) : ?>
				<div class="submission-status">
					Status: <strong><?php echo emanager_post::status($post, 'simple'); ?></strong>
				</div>
				<?php if ( $bic = emanager_bic::get_bic($post, 'display_name') ) : ?>
					<div class="submission-status">
						BIC User: <strong><?php echo $bic; ?></strong>
						BIC Company: <strong><?php /* echo emanager_bic::get_bic($post, 'display_company'); */ ?></strong>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="col-md-12">
			<div class="panel panel-default">
				<?php if ( ! empty($field['value']) ) :
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
				endif; ?>

				<?php if ( $has_location ) : ?>
					<table class="table">
						<tr>
							<th class="location">Location</th>
							<th class="scope">Scope</th>
						</tr>
						<?php echo $output; ?>
					</table>
				<?php else : ?>
					<?php echo '<div class="panel-heading">No locations</div>'; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="col-md-12">
			<p><strong>Issue Description</strong>: <br> <?php eman_the_field('description'); ?></p>

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
<?php /** / ?>
			<div id="acf-field-backup" class="preview_field clearfix">
				<div class="field_title"><strong>View Attachments:</strong></div>
				<div class="field_value">
					<?php $backups = eman_get_field('backup');
					if ( $backups ) : ?>
					<ol>
						<?php foreach ( $backups as $backup ) :
							$file = $backup['file'];
							$file_parts = explode('/', $file); ?>
							<li><a href="<?php echo $backup['file']; ?>"><?php echo end( $file_parts ); ?></a></li>
						<?php endforeach; ?>
					</ol>
					<?php endif; ?>
				</div>
			</div>
<?php /**/ ?>
			<?php if ( eman_get_field('display_pco') === 'Yes' ) : ?>
				<?php echo '<div><p><strong>PCO Number</strong>:'.eman_get_field('pco_number').'</p></div>'; ?>
			<?php endif; ?>
		</div>
	</section>

</article>