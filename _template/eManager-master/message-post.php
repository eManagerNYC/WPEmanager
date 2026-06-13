<?php global $post; ?>
<li class="message <?php echo ( ! has_term('read', 'message_status', $post) ) ? 'unread' : 'read'; ?>" data-id="<?php the_ID(); ?>">
	
		<!--
		<div class="author_avatar">

		<?php echo get_avatar( $post->post_author, 20 ); ?>
		</div>

		-->
		
		<div class="meta">
		<?php _e( "From", 'sewn/messenger' ); ?> | <?php $theauthor_id = get_the_author_meta( 'id', $post->post_author ); 
		
		$client_player_badge = eman_get_field('picture', 'user_'. $theauthor_id );
		$client_pro_badge = eman_get_field('my_picture', 'user_'. $theauthor_id );
		
		if( $client_player_badge )
							{
							
							echo '<img class="img-thumbnail avatar_message" src="'.$client_player_badge['url'].'"/>';
							}
							else
							{
								echo '<img class="img-thumbnail avatar_message" src="'.$client_pro_badge['url'].'"/>';
								
							}
		
		
		?>  <a href="<?php echo get_author_posts_url($post->post_author); ?>"><?php the_author_meta( 'display_name', $post->post_author ); ?></a> | <?php echo get_the_date(); ?>
	</div>
	<div class="content-message">
		<?php echo apply_filters( 'the_content', $post->post_content ); ?>
	</div>
</li>