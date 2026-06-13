<form id="search-filter" method="get" class="twelvecol first last cf" action="<?php echo $current_url; ?>">

	<div id="archive-header" class="m-all cf" style="margin-bottom: 0;">
		<div id="filter-submit"  class="m-6of12 lastcol cf" style="margin:0; text-align:right;">
<?php
			echo eman_button([
				'url'         => $current_url,
				'class'       => "clear-all-filters",
				'title'       => "Clear all filters",
				'size'        => "mini",
				'icon_before' => "times",
				'text'        => "Clear all",
			]);
			#echo do_shortcode('[button link="' . $current_url . '" class="clear-all-filters" title="Clear all filters" size="mini" icon_before="times"]Clear all[/button]');
?>
			<button class="btn btn-primary" type="submit">Submit</button>
		</div>
		<div id="search" class="m-6of12 cf" style="margin:0;">
			<?php if ( ! empty($_GET['sort']) ) : ?><input type="hidden" name="sort" value="<?php esc_attr_e($_GET['sort']); ?>" /><?php endif; ?>
			<?php if ( ! empty($_GET['order']) ) : ?><input type="hidden" name="order" value="<?php esc_attr_e($_GET['order']); ?>" /><?php endif; ?>
			<input type="text" class="form-control search-input" name="filter-search" placeholder="Search"<?php if ( $search_term ) : echo ' value="' . esc_attr($search_term) . '"'; endif; ?> />
		</div>
	</div>

	<?php if ( $filters ) : ?>
	<div id="filters" class="m-all cf">
		<div class="wrap cf">
			<div class="filter-label m-1of12 firstcol cf">Filter: </div>
			<?php
			$total        = count($filters);
			$column_width = floor( 11/$total );
			if ( 4 < $column_width ) {
				$column_width = 4;
			}
			$i=0;
			foreach ( $filters as $field ) :
				if ( ! empty($_GET[$field['name']]) ) :
					#add_filter('acf/load_value/name=status', function(){ return $_GET[$field['name']]; } );
					$field['value'] = $_GET[$field['name']];
				endif;
				if ( 'company' == $field['name'] && ! eman_check_role('turner') ) {
					continue;
				} ?>
				<div class="field m-<?php echo $column_width; ?>of12<?php /*if ( ! $i ) echo ' firstcol';*/ if ( $total == $i-1 ) : echo ' lastcol'; endif; ?> cf">
					<?php /* coverted to placeholders * / ?><label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?></label><?php /**/ ?>
					<?php create_field( $field ); ?>
				</div>
			<?php
			$i++;
			endforeach;
			?>
		</div>
	</div>
	<?php endif; ?>

</form>