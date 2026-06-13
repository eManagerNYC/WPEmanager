<?php global $post, $count; ?>
<li class="message cf clearfix <?php echo ( ! has_term('read', 'message_status') ) ? 'unread' : 'read'; ?> <?php echo ( 0 == $count%2 ) ? 'odd' : 'even'; ?>" data-id="<?php the_ID(); ?>">
	<span class="status-from">
		<span class="status">
			<?php if ( ! has_term('read','message_status') ) echo '<span class="new-indicator badge badge-new">New</span>'; ?> 
		</span>
		<span class="from">
			From: <span class="user"><?php echo get_the_author_meta( 'display_name', $post->post_author ); ?></span>
		</span>
	</span>
	<span class="subject-read">
		<a class="message-subject read-message" href="<?php do_action( 'sewn/messenger/permalink' ); ?>" data-nonce="<?php echo wp_create_nonce('read'); ?>">
			<?php #eman_the_field('subject', $post->ID); 
			echo get_post_meta($post->ID, 'subject', true); ?> 
		</a>
		<a class="read-message btn btn-default btn-xs" href="<?php do_action( 'sewn/messenger/permalink' ); ?>" data-nonce="<?php echo wp_create_nonce('read'); ?>">
			view
			<i class="fa fa-arrow-right"></i>
		</a>
	</span>
	<span class="classification-date">
		<span class="classification">
			<?php if ( has_term('private', 'message_status') ) :
				$term = get_term_by( 'slug', 'private', 'message_status' );
				echo '<span class="badge badge-private">' . $term->name . '</span>';
			endif; ?> 
		</span>
		<span class="date">
			<?php echo get_the_date() . ' ' . get_the_time(); ?> 
		</span>
	</span>
</li>
<?php $count++; ?>