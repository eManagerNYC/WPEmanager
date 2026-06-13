<h2>Review History</h2>

<?php
$current_user = wp_get_current_user();
global $submitted_stamp;
$reviews = new WP_Query( array(
	'post_type' => 'em_reviews',
	#'fields' => 'id=>parent',
	'posts_per_page' => -1,
	'order' => 'DESC',
	'orderby'=> 'date',
	'meta_query' => array(
		array(
			'key' => 'reviewed_id',
			'value' => $post->ID,
			'compare' => '=',
		)
	)
) );

if ( $reviews->posts ) :
	foreach ( $reviews->posts as $review ) :
		if (
			// do not display reviews on NOCs older than the submission date unless user is editor
			in_array('editor', $current_user->roles) ||
			in_array('administrator', $current_user->roles) ||
			('em_noc' != get_post_type()) ||
			!isset($submitted_stamp) ||
			!$submitted_stamp ||
			($submitted_stamp <= get_the_time('U', $review->ID))
		) : ?>
		   <div class="review">
				<?php
				$terms = wp_get_post_terms( $review->ID, 'em_status' );
				$status = $status_slug = false;
				if ( ! empty($terms[0]) ) :
					$status	     = $terms[0]->name;
					$status_slug = $terms[0]->slug;
				endif;

				if ( 'em_invoice' == $post->post_type )
				{
					$status = str_replace(array('Manager','Superintendent','Submitted','executed','Executed'), array('Accounting','Project Manager','Submitted for Payment','Paid','Paid'), $status);
				}

				if ( 'recommend' == $status_slug || 'executed' == $status_slug ) :
					$field	 = get_field_object('direction', $review->ID);
					$status .= ': '. eman_field_value($field, $review);
				endif;

				$send_to_id = get_post_meta($review->ID, 'send_to', true);
				$send_to	= get_user_by('id', $send_to_id);

				$pullback	= get_post_meta( $review->ID, 'pullback', true );
				$bic		= get_post_meta( $review->ID, 'bic', true );
				$confirm	= get_post_meta( $review->ID, 'confirm', true ); ?>

				<h4 class="reviewed-status">
					<?php if ( $bic ) : ?>
						BIC update:
					<?php elseif ( $pullback ) : ?>
						Pulled back:
					<?php elseif ( $confirm ) : ?>
						Confirmed:
					<?php endif;
					if ( $status ) :
						echo $status;
					endif; ?>
				</h4>

				<?php eman_the_field( 'superintendent_signature', $review->ID ); ?>
				<?php eman_the_field( 'signature', $review->ID ); ?>

				<p class="reviewed-by">
					<?php if ( $send_to ) : ?>
						Assigned to <strong><?php echo eman_users_name($send_to); ?></strong>
					<?php endif; ?>
					by <?php echo get_the_author_meta( 'display_name', $review->post_author ); ?>
				</p>

				<p class="reviewed-time">
					<?php echo get_the_time( get_option('date_format') . ', ' . get_option('time_format'), $review->ID ); ?>
				</p>
			</div>
		<?php endif;
	endforeach;
endif;