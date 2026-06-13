<?php //This part is required for WordPress to recognize it as a page template
/*
Template Name: DCR Reports
*/
/**
 * Grab current user
 */
$current_user = wp_get_current_user();

if ( ! eman_check_role('turner') ) {
	wp_redirect( home_url('/') );
	die;
}

/**
 * ACF needs this, form processing
 */
if ( function_exists('acf_form_head') ) { acf_form_head(); }
$pagename = 'Superintendent Daily Report';
wp_enqueue_script('date-picker', get_template_directory_uri(). '/assets/js/date-picker.js', array('jquery-ui-datepicker'), '1.0', true);

/**
 * Set up date info
 */
$today = null;
if ( ! empty($_REQUEST['datepicker']) ) {
	$today       = $_REQUEST['datepicker'];
	$y           = substr($today, 0, 4);
	$m           = substr($today, -4, 2);
	$d           = substr($today, -2);
} else {
	$y           = date_i18n('Y');
	$m           = date_i18n('m');
	$d           = date_i18n('d');
	$today       = $y . $m . $d;
}

$m_name = null;
if ( ! empty($_REQUEST['monthpick']) ) {
	$m           = sprintf('%02d', $_REQUEST['monthpick']);
	$y           = ( ! empty($_REQUEST['yearpick']) ? $_REQUEST['yearpick'] : date_i18n('Y') );
	$begin       = "{$y}{$m}01";
	$last_day    = date_i18n('t', $begin);
	$end         = "{$y}{$m}{$last_day}";
	$begin_stamp = strtotime($begin);
	$end_stamp   = strtotime($end);

	$m_name      = date_i18n('F', $begin_stamp);
}

$date = '';

/**
 * Default City, State
 */
$default_city   = 'New_York';
$default_state  = 'NY';
	
/**
 * Set up unix datestamps
 */
$current_stamp  = strtotime($today);
$yesterday_stamp = strtotime('-1 day', $current_stamp);
$tomorrow_stamp = strtotime('+1 day', $current_stamp);

/**
 * Get dates for yesterday and tomorrow to use for links
 */
$yesterday      = date_i18n('Ymd', $yesterday_stamp);
$tomorrow       = date_i18n('Ymd', $tomorrow_stamp);

get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php #do_action( 'before_content' ); ?>

		<div id="main" role="main">
			<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?>>

				<?php /** / if ( 'hide' != get_post_meta( $post->ID, '_tb_title', true ) ) : ?>
					<header class="entry-header">
						<h1 class="entry-title">
							<?php the_title(); ?>
						</h1>
					</header>
				<?php endif; /**/ ?>

				<div class="entry-content">

				<div class="entry-content">
					<div class="m-7of12 cf" style="border-right:1px solid #efefef;">
<?php
						$month_list = array(
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
						$years = array( date_i18n('Y') );
						$count = 1;
						$total = 5;
						while ( $count <= $total ) :
							$years[] = date_i18n( 'Y', strtotime('-' . $count . ' year') );
							$count++;
						endwhile;
?>
						<form action="" method="get">
							<h3>Month</h3>
							<p class="m-6of12 first" style="margin:0;">
								<select name="monthpick" id="monthpick">
								<?php foreach ( $month_list as $month_id => $month_name ) : ?>
									<option value="<?php echo $month_id; ?>"<?php if ( $month_id == $m ) : ?> selected<?php endif; ?>><?php echo $month_name; ?></option>
								<?php endforeach; ?>
								</select>
							</p>
							<p class="m-6of12 last" style="float:right; margin:0;">
								<select name="yearpick" id="yearpick">
								<?php foreach ( $years as $year ) : ?>
									<option value="<?php echo $year; ?>"<?php if ( $year == $y ) : ?> selected<?php endif; ?>><?php echo $year; ?></option>
								<?php endforeach; ?>
								</select>
							</p>
							<p class="m-all">
								<button class="btn btn-default btn-xs" title="Download report of monthly records">
									Submit
								</button>
							</p>
						</form>
					</div>

					<div id="table-export-day" class="m-3of12 cf" style="border-right:1px solid #efefef; margin-bottom:0; padding-bottom:1.5rem;">
						<form class="form-inline" role="form" action="" method="get">
							<h3>Day</h3>
		                    <div class="form-group">
			                    <input class="m-all" type="text" id="datepicker" name="datepicker" placeholder="Date" value="" size="15">
		                    </div>
		                    <button type="submit" class="btn btn-default btn-xs">Submit</button>
		                </form>
					</div>
<?php /**/ ?>
					<div id="table-export-all" class="m-2of12 cf">
						<form class="form-inline" role="form" action="" method="get">
							<h3 style="margin-bottom:2.75rem;">Cumulative Summary</h3>
							<input type="hidden" name="cumulative" value="1">
		                    <button type="submit" class="btn btn-default btn-xs">All to-date</button>
		                </form>
					</div>
	                <br>

					<hr>
<?php /**/ ?>

					<div class="clearfix">
						<div class="reports-header" style="clear:both;">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/img/turner_logo.png" alt="Turner Construction" style="margin-bottom:0; width:20%;">
							<h3 style="margin-bottom:0;">Project: <?php echo get_option('options_proj_name'); ?></h3>
							<p>Address: <?php echo get_option('options_proj_address'); ?>, <?php echo get_option('options_proj_city'); ?>, <?php echo get_option('options_proj_state'); ?>, <?php echo get_option('options_proj_zipcode'); ?></p>
						</div>
<?php /** / ?>
						<h2>
<?php
							if ( ! $m_name ) :
								echo "Day: $m/$d/$y";
							else :
								echo "Month: $m_name $y";
							endif;
?>
						</h2>
<?php /**/ ?>
						<p class="print-page"><a href="javascript:window.print()" title="Print this page" class="btn blue" target="_self">
							<span class="fa fa-print"></span> PRINT
						</a></p>
						<?php if (  ! $m_name ) : ?>
						<p class="daily-nav">
							<a class="btn btn-primary pull-left" href="<?php echo add_query_arg( 'datepicker', $yesterday ); ?>">&lsaquo; Previous Day</a>
							<a class="btn btn-primary pull-right" href="<?php echo add_query_arg( 'datepicker', $tomorrow ); ?>">Next Day &rsaquo;</a>
						</p>
						<?php endif; ?>
					</div>

<?php /** /
					$query = new WP_Query([
						'post_type'  => 'em_observation',
						'order'      => 'ASC',
						'orderby'    => 'datetime',
						'meta_query' => [
							[
								'key'      => 'datetime',
								'value'    => [$current_stamp, $tomorrow_stamp],
								'compare'  => 'BETWEEN',
								'type'     => 'NUMERIC',
							],
						],
					]);
					if ( $query->have_posts() ) :
?>
						<h3>Superintendent Observations</h3>
						<ul class="list-group">
<?php
							while ( $query->have_posts() ) : $query->the_post();
								$date_pretty = date_i18n('h:i a', eman_get_field('datetime'));
?>
								<li class="list-group-item">
									<span class="badge alert-info pull-right"><?php echo get_the_author(); ?></span>
									<h4 class="list-group-item-heading"><?php echo $date_pretty; ?></h4>
									<hr>
									<p class="list-group-item-text"><?php echo eman_get_field('f_notes'); ?></p>
									<?php if ( $attachments = eman_get_field('attachments') ) : ?>
									<ul>
										<?php foreach ( $attachments as $attachment ) : ?>
										<li>
											<div><?php echo emanager_post::display_backup($attachment['file']); ?></div>
										</li>
										<?php endforeach; ?>
									</ul>
									<?php endif; ?>
								</li>
							<?php endwhile; ?>
						</ul>
					<?php endif; wp_reset_postdata(); /**/ ?>






<?php if ( ! empty($_REQUEST['cumulative']) ) : ?>

					<h3>DCR Cumulative Summary To Date</h3><?php /** / ?>(as of <?php echo date_i18n('g:i a, M j, Y', $summary['date']); ?>)<?php /**/ ?>

					<?php if ( $summary = get_all_dcr_summary() ) : ?>
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th class="count" scope="col">#</th>
									<th class="company" scope="col">Company</th>
									<th class="incidents" scope="col">Incidents</th>
									<th class="minority" scope="col">Minority Days</th>
									<th class="female" scope="col">Female Days</th>
									<th class="workers" scope="col">Total Manpower</th>
									<th class="workers" scope="col">Total Man-hours</th>
									<th class="average" scope="col">Average Daily Manpower</th>
								</tr>
							</thead>
							<tbody>
<?php
							$count = 1;
							foreach ( $summary['companies'] as $id => $company ) :
?>
								<tr>
									<th scope="row"><?php echo $count; ?></th>
									<td><?php echo $company['name']; ?></td>
									<td><?php echo $company['incidents']; ?></td>
									<td><?php echo $company['minority'] . ' (' . ($company['days'] ? number_format($company['minority']/$company['days']*100) : 0) . '%)'; ?></td>
									<td><?php echo $company['female'] . ' (' . ($company['days'] ? number_format($company['female']/$company['days']*100) : 0) . '%)'; ?></td>
									<td><?php echo $company['days']; ?></td>
									<td><?php echo $company['hours']; ?></td>
									<td><?php echo $company['average']; /*?> / <?php echo $company['total_dcrs']; ?> <?php echo _n( 'day', 'days', $company['total_dcrs'] );*/ ?></td>
								</tr>
<?php
								$count++;
							endforeach;
?>
							</tbody>
							<tfoot style="border-top: 2px solid black">
								<tr>
									<td></td>
									<th>Totals</th>
									<th><?php echo number_format($summary['totals']['incidents']); ?></th>
									<th><?php echo number_format($summary['totals']['minority'], 2) . ' (' . ($summary['totals']['days'] ? number_format($summary['totals']['minority']/$summary['totals']['days']*100) : 0) . '%)'; ?></th>
									<th><?php echo number_format($summary['totals']['female'], 2) . ' (' . ($summary['totals']['days'] ? number_format($summary['totals']['female']/$summary['totals']['days']*100) : 0) . '%)'; ?></th>
									<th><?php echo number_format($summary['totals']['days'], 2); ?></th>
									<th><?php echo number_format($summary['totals']['hours'], 2); ?></th>
									<th><?php echo number_format($summary['totals']['averages']); /*?> / <?php echo $summary['totals']['total_dcrs']; ?> <?php echo _n( 'day', 'days', $summary['totals']['total_dcrs'] );*/ ?></th>
								</tr>
							</tfoot>
						</table>
					</div>
					<?php endif; ?>




<?php else: ?>

					<h3>DCR <?php echo ( ! $m_name ? 'Daily' : 'Monthly' ); ?> Summary Report for: <?php echo ( ! $m_name ? "$m/$d/$y" : "$m_name $y" ); ?></h3>
<?php
					if ( ! $m_name ) :
						$meta_query = [
							[
								'key'      => 'work_date',
								'value'    => $today,
								'compare'  => '=',
							],
						];
					else :
						$meta_query = [
							'relation' => 'AND',
							[
								'key'      => 'work_date',
								'value'    => $begin,
								'compare'  => '>=',
								'type'     => 'DATE',
							],
							[
								'key'      => 'work_date',
								'value'    => $end,
								'compare'  => '<=',
								'type'     => 'DATE',
							],
						];
					endif;
					$query = new WP_Query([
						'posts_per_page' => -1,
						'post_type'  => 'em_dcr',
						'order'      => 'ASC',
						'orderby'    => 'meta_value',
						'meta_key'   => 'company',
						'meta_query' => $meta_query,
					]);

					$cpt = eman_post_types('em_dcr');
					$current_company = null;

					$results       = [
						'totals'     => [
							'incidents' => 0,
							'minority'  => 0,
							'female'    => 0,
							'days'      => 0,
							'hours'     => 0,
							'incidents' => 0,
							'averages'  => 0,
						],
						'companies'  => [],
					];

					$results = create_dcr_summary( $query );
					if ( $results ) :
?>
					<div class="table-responsive">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th class="count" scope="col">#</th>
									<th class="company" scope="col">Company</th>
									<th class="incidents" scope="col">Incidents</th>
									<th class="minority" scope="col">Minority Days</th>
									<th class="female" scope="col">Female Days</th>
									<th class="workers" scope="col">Total Manpower</th>
									<th class="workers" scope="col">Total Man-hours</th>
									<?php if ( $m_name ) : ?>
									<th class="average" scope="col">Average Daily Manpower</th>
									<?php endif; ?>
								</tr>
							</thead>
							<tbody>
<?php
							$count = 1;
							foreach ( $results['companies'] as $id => $company ) :
?>
								<tr>
									<th scope="row"><?php echo $count; ?></th>
									<td><?php echo $company['name']; ?></td>
									<td><?php echo $company['incidents']; ?></td>
									<td><?php echo $company['minority'] . ' (' . ($company['days'] ? number_format($company['minority']/$company['days']*100) : 0) . '%)'; ?></td>
									<td><?php echo $company['female'] . ' (' . ($company['days'] ? number_format($company['female']/$company['days']*100) : 0) . '%)'; ?></td>
									<td><?php echo $company['days']; ?></td>
									<td><?php echo $company['hours']; ?></td>
									<?php if ( $m_name ) : ?>
									<td><?php echo $company['average']; /*?> / <?php echo $company['total_dcrs']; ?> <?php echo _n( 'day', 'days', $company['total_dcrs'] );*/ ?></td>
									<?php endif; ?>
								</tr>
<?php
								$count++;
							endforeach;
?>
							</tbody>
							<tfoot style="border-top: 2px solid black">
								<tr>
									<td></td>
									<th>Totals</th>
									<th><?php echo $results['totals']['incidents']; ?></th>
									<th><?php echo $results['totals']['minority'] . ' (' . ($company['days'] ? number_format($results['totals']['minority']/$results['totals']['days']*100) : 0) . '%)'; ?></th>
									<th><?php echo $results['totals']['female'] . ' (' . ($company['days'] ? number_format($results['totals']['female']/$results['totals']['days']*100) : 0) . '%)'; ?></th>
									<th><?php echo $results['totals']['days']; ?></th>
									<th><?php echo $results['totals']['hours']; ?></th>
									<?php if ( $m_name ) : ?>
									<th><?php echo $results['totals']['averages']; /*?> / <?php echo $results['totals']['total_dcrs']; ?> <?php echo _n( 'day', 'days', $results['totals']['total_dcrs'] );*/ ?></th>
									<?php endif; ?>
								</tr>
							</tfoot>
						</table>
					</div>

					<?php else: ?>
						<p class="empty-results">No items match your request</p>
					<?php endif; ?>

					<?php /** /if ( $summary = get_all_dcr_summary() ) : ?>
						<h3>Cumulative Summary to Date (as of <?php echo date_i18n('g:i a, M j, Y', $summary['date']); ?>)</h3>
						<table>
							<tr>
								<th style="width:20%">Incidents</th>
								<td><?php echo number_format($summary['totals']['incidents']); ?></td>
							</tr>
							<tr>
								<th>Minority Days</th>
								<td><?php echo number_format($summary['totals']['minority'], 2) . ' (' . ($company['days'] ? number_format($summary['totals']['minority']/$summary['totals']['days']*100) : 0) . '%)'; ?></td>
							</tr>
							<tr>
								<th>Female Days</th>
								<td><?php echo number_format($summary['totals']['female'], 2) . ' (' . ($company['days'] ? number_format($summary['totals']['female']/$summary['totals']['days']*100) : 0) . '%)'; ?></td>
							</tr>
							<tr>
								<th>Total Man-days</th>
								<td><?php echo number_format($summary['totals']['days'], 2); ?></td>
							</tr>
							<tr>
								<th>Total Man-hours</th>
								<td><?php echo number_format($summary['totals']['hours'], 2); ?></td>
							</tr>
						</table>
					<?php endif;/**/ ?>

<?php /**/
					if ( ! empty($_REQUEST['datepicker']) && shortcode_exists('weather_history') ) :
?>
						<div id="report-weather">
						<h3>Weather</h3>
						<div class="panel panel-default">
							<div class="panel-body">
<?php
							if ( ! ( $proj_city = str_replace(' ', '_', eman_get_field('proj_city', 'option')) ) ) :
								$proj_city = $default_city;
							endif;
							if ( ! ( $proj_state = eman_get_field('proj_state', 'option') ) ) :
								$proj_state = $default_state;
							endif;

							echo do_shortcode("[weather_history city=\"$proj_city\" state=\"$proj_state\" d=\"$d\" m=\"$m\" y=\"$y\"]");
?>
							</div>
						</div>
						</div>
<?php
					endif;
/**/ ?>





<?php endif; ?>

<?php /** /
					$query = new WP_Query([
						'post_type'  => 'em_photos',
						'order'      => 'ASC',
						'orderby'    => 'title',
						'meta_query' => [
							[
								'key'      => 'date_taken',
								'value'    => $today,
								'compare'  => 'LIKE',
								'type'     => 'NUMERIC',
							],
						],
					]);
					if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
						$image_ids = eman_get_field('photos', false, false);
						echo do_shortcode('[gallery ids="' . implode(',', $image_ids) . '" link="file"]');
					endwhile; endif; wp_reset_postdata();
/**/ ?>

					<?php the_content(); ?>

				</div><!-- .entry-content -->
			</article><!-- #post-<?php the_ID(); ?> -->

		</div>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer();
