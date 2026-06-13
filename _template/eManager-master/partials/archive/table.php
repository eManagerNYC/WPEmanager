<?php
$post_type = eman_archive_info('post_type');
$cpt       = eman_archive_info('cpt');
?>
<h2 class="m-9of12 first cf"><span class="modal-flowchart-container"><?php eman_flowchart($post_type); ?></span> <?php echo eman_archive_info('plural_title'); ?></h2>

<div class="tab-pane active" id="<?php echo $post_type ?>_records">
<?php
	// Filters
	do_action( 'emanager_table_filters/filters', array( 'post_type' => $post_type ) );

	// Table
	$emanager_table = new emanager_table();
	echo $emanager_table->table( $posts );
	// Total items
	echo '<div class="pager">' . $wp_query->post_count . ' of ' . $wp_query->found_posts . ' items</div>';
?>

	<div id="table-export" class="m-all cf">
		<h3 style="text-align: center;">Export</h3>
		<div id="table-export-basic" class="m-2of12 m-first" style="border-right:1px solid #efefef;">
			<p style="text-align: left;">
<?php
				echo eman_button( array(
					'text'        => 'Export This Page',
					'title'       => 'Download CSV export of current page',
					'url'         => add_query_arg( 'action', 'csv' ),
					'icon_before' => 'download',
					'size'        => 'mini',
					'target'      => '_blank',
				) );
?>
			</p>
			<p style="text-align: left;">
<?php
				echo eman_button( array(
					'classes'     => 'table-export-all',
					'text'        => 'Export All',
					'title'       => 'Download CSV export of ALL records',
					'url'         => add_query_arg( array('action' => 'csv', 'type' => 'all') ),
					'icon_before' => 'download',
					'size'        => 'mini',
					'target'      => '_blank',
				) );
?>
			</p>
		</div>

		<div id="table-export-month" class="m-5of12 cf" style="border-right:1px solid #efefef;">
<?php
			$months = array(
				1  => 'January',
				2  => 'February',
				3  => 'March',
				4  => 'April',
				5  => 'May',
				6  => 'June',
				7  => 'July',
				8  => 'August',
				9  => 'September',
				10 => 'October',
				11 => 'November',
				12 => 'December',
			);
			$years = array( date('Y') );
			$count = 1;
			$total = 5;
			while ( $count <= $total ) :
				$years[] = date( 'Y', strtotime('-' . $count . ' year') );
				$count++;
			endwhile;
?>
			<form action="<?php echo add_query_arg( 'action', 'csv' ); ?>" method="get">
				<input type="hidden" name="action" value="csv" />
				<p class="m-6of12 first" style="margin:0;">
					<select name="export_month" id="export_month">
					<?php foreach ( $months as $month_id => $month_name ) : ?>
						<option value="<?php echo $month_id; ?>"<?php if ( $month_id == date('n') ) : ?> selected<?php endif; ?>><?php echo $month_name; ?></option>
					<?php endforeach; ?>
					</select>
				</p>
				<p class="m-6of12 last" style="float:right; margin:0;">
					<select name="export_year" id="export_year">
					<?php foreach ( $years as $year ) : ?>
						<option value="<?php echo $year; ?>"<?php if ( $year == date('Y') ) : ?> selected<?php endif; ?>><?php echo $year; ?></option>
					<?php endforeach; ?>
					</select>
				</p>
				<p class="m-all">
					<button class="btn btn-default btn-xs table-export-month" title="Download CSV export of monthly records">
						<span class="fa fa-download"></span> 
						Export by Month
					</button>
				</p>
			</form>
		</div>

		<div id="table-export-range" class="m-5of12 last">
			<form action="<?php echo add_query_arg( 'action', 'csv' ); ?>" method="get">
				<input type="hidden" name="action" value="csv" />
				<p class="m-6of12 first" style="margin:0;">
					<input type="text" name="export_date_start" id="export_date_start" placeholder="Start Date" />
					<input type="hidden" name="export_date_start_value" id="export_date_start_value" />
				</p>
				<p class="m-6of12 last" style="float:right; margin:0;">
					<input type="text" name="export_date_end" id="export_date_end" placeholder="End Date" />
					<input type="hidden" name="export_date_end_value" id="export_date_end_value" />
				</p>
				<p class="m-all">
					<button class="btn btn-default btn-xs table-export-range" title="Download CSV export of date range">
						<span class="fa fa-download"></span> 
						Export Date Range
					</button>
				</p>
			</form>
		</div>
	</div>

</div>