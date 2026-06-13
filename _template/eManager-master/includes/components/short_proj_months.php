<?php
/**
 * Project Months
 *
 * dcr manhours total plan
 *
 * $projectstartdate = x;
 * find month of project start date
 * calculate months from then until now
 * list months
 * associate manhour count with month
 */
add_shortcode( 'proj_months', 'eman_project_months' );
function eman_project_months()
{
    // start date
    $startdate  = eman_get_field('proj_start', 'options');
    $startstamp = strtotime($startdate);
    $startyear  = date("Y", $startstamp);
    $startmonth = date("m", $startstamp);

    // now date
    $nowdate    = date('Ymd'); 
    $nowstamp   = strtotime($nowdate);
    $nowyear    = date("Y", $nowstamp);
    $nowmonth   = date("m", $nowstamp);

	if ( $startstamp < $nowstamp ) {
	   $past   = $startdate;
	   $future = $nowdate;
	} else {
	   $past   = $nowdate;
	   $future = $startdate;
	}

	$months = array();
	for ( $i=$past; $past<=$future; $i++ )
	{
	   $timestamp = strtotime($past . '-1');
	   $months[]  = date('F Y', $timestamp);
	   $past      = date('Y-m', strtotime('+1 month', $timestamp));  
	}

	ob_start();
?>
	<table class="table table-striped">
		<tr>
			<th>Period</th>
			<th>Manhours</th>
		</tr>
		<?php foreach ( $months as $month ) : ?>
			<tr>
				<td><?php echo $month; ?></td>
				<td><?php // this is where we want to add do_shortcode('[manhours month='.$month.']') ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php
	$output = ob_get_clean();

	return $output;
}