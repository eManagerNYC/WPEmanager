<div id="pending" style="text-align: left">
	<?php if ( $pending_title = eman_get_field('pending_title', 'options') ) : ?><h2><?php echo $pending_title; ?></h2><?php endif; ?>
	<div class="column grid_fifth_4 last">
		<?php if ( $pending_content = eman_get_field('pending_content', 'options') ) : ?>
			<p><?php echo $pending_content; ?></p>
		<?php endif; ?>
	</div>
</div>