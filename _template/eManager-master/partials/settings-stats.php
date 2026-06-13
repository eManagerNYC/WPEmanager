<?php
/**
 * The template used for display stats for settings
 */

/* ticket plan
same as pco, but with ticket total
*/

ob_start(); ?>
<!-- PCO (start) -->
<div class="panel panel-default">
	<div class="panel-heading">PCO Information</div>
	<div class="panel-body">
		Submitted: $<?php echo do_shortcode('[pco_total status="submitted"]'); ?><br>
		Recommended: $<?php echo do_shortcode('[pco_total status="recommend"]'); ?><br>
		Executed: $<?php echo  do_shortcode('[pco_total status="executed"]'); ?><br>
	</div>
</div>
<!-- PCO (end) -->
<?php $pco = ob_get_clean();

ob_start(); ?>
<!-- PCO (start) -->
<div class="panel panel-default">
	<div class="panel-heading">PCO Information</div>
	<div class="panel-body">
		Draft: $<?php echo do_shortcode('[pco_total status="draft"]'); ?><br>
		Revised: $<?php echo do_shortcode('[pco_total status="revise"]'); ?><br>
		Manager Review: $<?php echo do_shortcode('[pco_total status="manager"]'); ?><br>
		Ready to Submit: $<?php echo do_shortcode('[pco_total status="ready"]'); ?><br>
		Submitted: $<?php echo do_shortcode('[pco_total status="submitted"]'); ?><br>
		Recommended: $<?php echo do_shortcode('[pco_total status="recommend"]'); ?><br>
		Executed: $<?php echo do_shortcode('[pco_total status="executed"]'); ?><br>
	</div>
</div>
<!-- PCO (end) -->
<?php $pco_turner = ob_get_clean();

ob_start(); ?>
<!-- ticket (start) -->
<div class="panel panel-default">
	<div class="panel-heading">Ticket Information</div>
	<div class="panel-body">
		coming soon
	</div>
</div>
<!-- ticket (end) -->
<?php $ticket_turner = ob_get_clean();

ob_start(); ?>
<!-- dcr (start) -->
<div class="panel panel-default">
	<div class="panel-heading">DCR Labor Breakdown</div>
	<?php // echo do_shortcode('[proj_months]'); ?>
	<div class="panel-body">
		<table class="table table-striped table-dcr-summary">
			<tr>
				<th>Month</th>
				<th>Man hours</th>
			</tr>
<?php
			$hours = ('Man-hours' == get_option('options_dcr_labor_breakdown')) ? 1 : 8;
			$calendar_summary = dcr_calendar_summary();
			foreach ($calendar_summary as $year => $months) {
				$start = false;
				foreach ($months as $month => $companies) {
					if ('total' != $month) {
						ob_start();

						$month = str_pad($month, 2, '0', STR_PAD_LEFT);
						echo '<tr>';
						echo '<td>'.date('M Y', strtotime("$year-$month-01")).'</td>';
						echo "<td><table>";
						$total = 0;
						foreach ($companies as $company => $count) {
							if ( !$start && $count) {
								$start = true;
							}
							$count = $count * $hours;
							$total += $count;
							echo "<tr><td>$company</td><td>$count</td></tr>";
						}
						echo "<tr><td>Total</td><td>$total</td></tr>";
						echo '</table>';
						echo '</td>';
						echo '</tr>';

						$row = ob_get_clean();
						if ($start) {
							echo $row;
						}
					}
				}
			}
?>
		</table>
	</div>
</div>
<?php $dcr_labor = ob_get_clean();

ob_start(); ?>
<div class="panel panel-default">
	<div class="panel-heading">DCR Labor Breakdown</div>
	<?php // echo do_shortcode('[proj_months]'); ?>
	<div class="panel-body">
		<table class="table table-striped table-dcr-minorities">
<?php
			$minority_summary = dcr_minority_summary();
			foreach ($minority_summary as $company => $months) {
				echo "<tr><th colspan=\"2\">$company</th></tr>";
				echo '<tr><td>';
?>
				<table class="table table-striped">
					<tr>
						<td></td>
						<th colspan="2">Minority</td>
						<th colspan="2">Majority</td>
					</tr>
					<tr>
						<th>Month</td>
						<th>M</th>
						<th>F</th>
						<th>M</th>
						<th>F</th>
					</tr>
<?php
					$labels = array('male_minority', 'female_minority', 'male_non-minority', 'female_non-minority');
					foreach ($months as $month => $columns) {
						$month = str_pad($month, 2, '0', STR_PAD_LEFT);
						echo '<tr>';
						echo '<td>'.date('M Y', strtotime("$month-01")).'</td>';
						foreach ($labels as $label) {
							echo '<td>';
							if ( !empty($columns[$label])) {
								echo $columns[$label] * $hours;
							} else echo '0';
							echo '</td>';
						}
						echo '</tr>';
					}
					echo '</table></td></tr>';
			}
?>
		</table>
	</div>
</div>
<!-- dcr (end) -->
<?php $dcr_man = ob_get_clean(); // add manhours totals here per month

// Output the full shabang
?>
<hr />
<div class="">
	<?php if ( eman_check_role('turner') ) : ?>
		<div class="m-4of12">
			<?php echo $pco_turner; ?>
			<br>
			<?php echo $ticket_turner; ?>
		</div>
		<div class="m-4of12">
			<?php echo $dcr_labor; ?>
		</div>
		<div class="m-4of12">
			<?php echo $dcr_man; ?>
		</div>

	<?php elseif ( eman_check_role('owner') ) : ?>
		<div class="m-6of12">
			<?php echo $pco; ?>
		</div>
		<div class="m-6of12">
			<?php echo $dcr; ?>
		</div>

	<?php elseif ( eman_check_role('sub') ) : ?>
		<div class="m-6of12">
			<?php echo $ticket_sub; ?>
		</div>
		<div class="m-6of12">
			<?php /* echo $dcr; */// Should onlt show company ?>
		</div>

	<?php endif; ?>
</div>
