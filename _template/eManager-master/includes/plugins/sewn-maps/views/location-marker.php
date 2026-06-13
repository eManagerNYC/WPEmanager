<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>" data-marker="<?php echo $marker; ?>">
	<h4><a href="<?php echo get_permalink($post->ID); ?>" title="<?php esc_attr_e($title); ?>"><?php esc_html_e($title); ?></a></h4>
	<p class="address"><?php echo $location['address']; ?></p>
	<?php if (! empty($post->phone)   && $phone   = $post->phone )   : ?><p class="phone"><?php esc_html_e($phone); ?></p><?php endif; ?>
	<?php if (! empty($post->email)   && $email   = $post->email )   : ?><p class="email"><a href="mailto:<?php esc_attr_e($email); ?>" title="Email <?php esc_attr_e($title); ?>"><?php esc_html_e($email); ?></a></p><?php endif; ?>
	<?php if (! empty($post->contact) && $contact = $post->contact ) : ?><p class="contact"><?php esc_html_e($contact); ?></p><?php endif; ?>
	<?php if (! empty($post->website) && $website = $post->website ) : ?><p class="website"><a href="<?php esc_attr_e($website); ?>" title="Visit <?php esc_attr_e($title); ?>"><?php esc_html_e($website); ?></a></p><?php endif; ?>
	<p class="get-directions"><a href="https://maps.google.com/maps?saddr=&daddr=<?php echo urlencode($location['address']); ?>" title="<?php _e("Get directions", 'acf_locations'); ?>"><?php _e("Get directions", 'acf_locations'); ?></a></p>
</div>