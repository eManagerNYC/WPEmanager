<?php
$empty_table     = "No items at this time";
$current_user_id = get_current_user_id();
$bic_items       = array();
$bic_type        = get_query_var('bic_type');
$paged           = ( get_query_var('page') ) ? get_query_var('page') : 1;

$args = array(
	'meta_query'     => array(
		array(
			'key'          => 'bic_user',
			'value'        => $current_user_id,
		),
	),
	'paged'          => $paged,
	'posts_per_page' => -1,
);

$table = $content = '';

get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

			<h2>Ball In Court</h2>

			<?php
			/**
			 * Set up the tabs
			 */
			$tabs = array();
			if ( eman_check_role('turner') ) :
				$tabs = array(
					'all'     => "All",
					'noc'     => "PCO/NOC",
					'pcod'    => "PCO Directives",
					'tickets' => "Tickets",
					'letter'  => "Letters",
					'rfi'     => "RFIs",
					'dcr'     => "DCR",
					'issue'   => "Issues",
				);
			elseif ( eman_check_role('sub') ) :
				$tabs = array(
					'all'     => "All",
					'pcod'    => "PCO Directives",
					'tickets' => "Tickets",
					'dcr'     => "DCR",
					'issue'   => "Issues",
				);
			elseif ( eman_check_role('owner') ) :
				$tabs = array(
					'all'     => "All",
					'noc'     => "PCO/NOC",
					'letter'  => "Letters",
					'rfi'     => "RFIs",
					'issue'   => "Issues",
				);
			endif;
			$tabs = apply_filters( 'eman/bic/items', $tabs );

			if ( $tabs ) :
				/**
				 * Set up the buttons for types
				 */
?>
				<div class="bic-tabs">
<?php 
				$post_types = array();
				foreach ( $tabs as $key => $value ) {
					$badge = '';
					if ( 'all' !== $key ) {
						$post_types[] = "em_{$key}";
					}
				}
				foreach ( $tabs as $key => $value ) :
					$badge = '';
					if ( 'all' === $key ) {
						$args['post_type'] = $post_types;
					} else {
						$args['post_type'] = "em_{$key}";
					}
					$items = new WP_Query( $args );
					$total = $items->found_posts;
					$badge = ( $total ) ? ' <span class="badge">' . $total . '</span>' : '';
					$items = null;
					
					echo do_shortcode('[button link="' . home_url('/bic/' . $key . '/') . '"' . ($key===$bic_type || ('all'===$key && ! $bic_type) ? ' color="success"' : '') . ']' . $value . $badge . '[/button]');
				endforeach;
?>
				</div>
<?php
				/**
				 * Add the posts if a type is set and correct
				 */
				global $wp_query;
				$items = false;
				if ( ! $bic_type || array_key_exists($bic_type, $tabs) ) :
					
					if ( ! $bic_type || 'all' === $bic_type ) {
						$args['post_type'] = $post_types;
					} else {
						$args['post_type'] = "em_{$bic_type}";
					}
					$items = new WP_Query( $args );
				endif;
/**/
				if ( ! empty($items->posts) ) :
					foreach ( $items->posts as $item ) :
						#if ( $current_user_id == emanager_bic::get_bic( $item, 'ID' ) ) :
							$bic_items[] = $item;
						#endif;
					endforeach;
				endif;
/**/
				ob_start();
				$emanager_table = new emanager_table();
				$emanager_table->table( $bic_items );
				do_action( 'patch/page_navi', $items );
				$table          = ob_get_clean();
				$content        = ($table ? $table : $empty_table);
			endif; ?>
			<div class="em_tables">
				<?php echo $content; ?>
			</div>

		</div>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer(); ?>