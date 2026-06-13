<?php

if ( ! class_exists('emanager_summary') ) :

add_action( 'init', array('emanager_summary', 'init') );

class emanager_summary
{
	/**
	 * Class prefix
	 *
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function init()
	{
		
	}

	/**
	 * classification_breakdown
	 *
	 * @author  Jake Snyder
	 * @param	array	$field	The acf field array
	 * @param	obj		$post	The wp post object
	 * @return	array	$output	The table data
	 */
	public static function classification_breakdown( $field, $post )
	{
		if ( ! is_array($field['value']) ) {
			return false;
		}

		$output = array(
			'table'   => array(
				'titles'  => array(),
				'rows'    => array(),
			),
			'markups' => array(),
			'totals'  => array(
				'rows'    => 0,
				'table'   => 0,
				'markups' => 0,
			),
		);

		$dcr_labor_breakdown = get_option('options_dcr_labor_breakdown');
		$labor_unit_hours    = 'hours';
		$labor_unit_days     = 'man-days';
		$man_hours           = ( 'Man-hours' == $dcr_labor_breakdown ) ? true : false;

		$row_count = $man_total = $mancount = 0;
		foreach ( $field['value'] as $row ) {
			// Set up the title row
			if ( ! $row_count ) {
				foreach ( $row as $key => $item ) {
					$output['table']['titles'][$key] = ucwords( str_replace(array('_','-'), ' ', $key) );
				}
				$output['table']['titles']['total'] = "Total";
			}

			// Set up the row data
			$row_output = [];
			$row_total  = 0;

			if ( 'em_dcr' == $post->post_type ) {
				$row_output['title'] = array_shift($row);
				if ( is_numeric($row_output['title']) ) {
					$type_post = get_post($row_output['title']);
					if ( $type_post ) {
						$row_output['title'] = $type_post->post_title;
					}
				}

				if ( $man_hours ) {
					$mancount                = array_shift($row);
					$row_output['mancount']  = $mancount;
					$man_total              += $mancount;
				}

				$notes      = array_pop($row);
				$scope_no   = array_pop($row);
				$pco_number = array_pop($row);

				$row_total = 0;
				foreach ( $row as $key => $amount ) {
					$row_output[$key]  = $amount;
					$row_total        += $amount;
				}

				$row_output['pco']      = $pco_number;
				$row_output['scope_no'] = $scope_no;
				$row_output['notes']    = $notes;
				$row_output['total']    = $row_total . ' ' . ($man_hours ? $labor_unit_hours : $labor_unit_days);

				$output['table']['rows'][]  = $row_output;
				$output['totals']['rows']  += $row_total;
			} else {
				$type = array_shift($row);
				if ( ! is_object($type) ) {
					$type = (object) [
						'ID' => 0,
						'post_title' => 'Unspecified',
					];
				}
				$row_output['title'] = $type->post_title;

				$count        = array_shift($row);
				$row_output[] = "&times; $count";

				foreach ( $row as $key => $amount ) {
					if ( 'scope_no' == $key ) {
						$row_output[]  = ( $amount ) ? "$amount" : '';
					} else {
						$rate          = get_post_meta($type->ID, $key, true);
						if ( ! $rate ) { $rate = 0; }
						$row_output[]  = "$amount hrs @ $$rate/hr";
						$row_total    += $amount * $rate * $count;
					}
				}

				$row_output[] = '$' . eman_number_format( $row_total );

				$output['table']['rows'][]  = $row_output;
				$output['totals']['rows']  += $row_total;
			}
		}

		$row_count++;

		if ( 'em_dcr' == $post->post_type ) {
			if ( $man_hours ) {
				$output['totals']['table'] = "{$output['totals']['rows']} $labor_unit_hours, $man_total $labor_unit_days";
			} else {
				$output['totals']['table'] = "{$output['totals']['rows']} $labor_unit_days";
			}
		} else {
			$output['totals']['table'] = '$' . eman_number_format( $output['totals']['rows'] );
		}

		return $output;
	}

	/**
	 * employee_breakdown
	 *
	 * @author  Jake Snyder
	 * @param	array	$field	The acf field array
	 * @param	obj		$post	The wp post object
	 * @return	array	$output	The table data
	 */
	public static function employee_breakdown( $field, $post )
	{
		if ( ! is_array($field['value']) ) {
			return false;
		}

		$output = array(
			'table'   => array(
				'titles'  => array(),
				'rows'    => array(),
			),
			'markups' => array(),
			'totals'  => array(
				'rows'    => 0,
				'table'   => 0,
				'markups' => 0,
			),
		);

		$row_count = 0;
		foreach ( $field['value'] as $row )
		{
			// Set up the title row
			if ( ! $row_count )
			{
				foreach ( $row as $key => $item ) {
					$output['table']['titles'][$key] = ucwords( str_replace(array('_','-'), ' ', $key) );
				}
				if ( 'em_dcr' != $post->post_type ) {
					$output['table']['titles']['total'] = "Total";
				}
			}

			// Set up the row data
			$row_output = array();
			$row_total  = 0;
			$full_row   = $row;
			$object     = array_shift($row);
			if ( is_object($object) ) {
				$row_output['title'] = $object->post_title;

				if ( is_numeric($row_output['title']) ) {
					$type_post = get_post($row_output['title']);
					if ( $type_post ) {
						$row_output['title'] = $type_post->post_title;
					}
				}

				if ( 'em_dcr' == $post->post_type ) {
					$row_output['days_worked'] = $row['days_worked'];
					$row_output['pco']         = $row['pco'];
					$row_output['notes']       = $row['notes'];
					$row_output['scope_no']    = ( $row['scope_no'] ) ? $row['scope_no'] : '';

					$output['table']['rows'][]  = $row_output;
					$output['totals']['rows']  += $row['days_worked'];
				} else {
					$labor = eman_get_field('classification', $object->ID);

					if ( ! is_object($labor) ) { continue; }

					foreach ( $row as $key => $amount ) {
						if ( 'scope_no' == $key ) {
							$row_output[]  = ( $amount ) ? "$amount" : '';
						} else {
							$rate          = get_post_meta($labor->ID, $key, true);
							$row_output[]  = "$amount hrs @ $$rate/hr";
							$row_total    += $amount * $rate;
						}
					}

					$row_output[] = '$' . eman_number_format( $row_total );

					$output['table']['rows'][]  = $row_output;
					$output['totals']['rows']  += $row_total;
				}
			} else {
				$output['table']['rows'][]  = $full_row;
			}

			$row_count++;
		}

		if ( 'em_dcr' == $post->post_type ) {
			$output['totals']['table'] =  "{$output['totals']['rows']} man-days";
		} else {
			$output['totals']['table'] = '$' . eman_number_format( $output['totals']['rows'] );
		}

		return $output;
	}

	/**
	 * materials
	 *
	 * @author  Jake Snyder
	 * @param	array	$field	The acf field array
	 * @param	obj		$post	The wp post object
	 * @return	array	$output	The table data
	 */
	public static function materials( $field, $post )
	{
		if ( ! is_array($field['value']) ) {
			return false;
		}

		$output = array(
			'table'   => array(
				'titles'  => array(),
				'rows'    => array(),
			),
			'markups' => array(),
			'totals'  => array(
				'rows'    => 0,
				'table'   => 0,
				'markups' => 0,
			),
		);

		$row_count = 0;
		foreach ( $field['value'] as $row )
		{
			// Set up the title row
			if ( ! $row_count )
			{
				foreach ( $row as $key => $item ) {
					$output['table']['titles'][$key] = ucwords( str_replace(array('_','-'), ' ', $key) );
				}
				if ( 'em_dcr' != $post->post_type ) {
					$output['table']['titles']['total'] = "Total";
				}
				unset($output['table']['titles']['measure']);
				unset($output['table']['titles']['price']);
			}

			// Set up the row data
			$row_output = array();
			$row_total  = 0;
			$full_row   = $row;
			$object     = array_shift($row);
			if ( is_object($object) )
			{
				// If the price is empty, get it from the database
				if ( empty($row['price']) ) {
					$row['price']   = get_post_meta($object->ID, 'price', true);
				}
				// If unit is empty, get it from the database
				if ( empty($row['measure']) ) {
					$row['measure'] = get_post_meta($object->ID, 'measure', true);
				}

				$row_output[]  = $object->post_title;
				$row_output[]  = $row['amount_used'] . ' ' . $row['measure']. ('em_dcr' != $post->post_type ? ' &times; $' . $row['price'] . ' / ' . $row['measure'] : '');
				$row_output[]  = $row['scope_no'];
				$row_total    += $row['amount_used'] * $row['price'];

				if ( 'em_dcr' == $post->post_type ) {
					$row_output['notes'] = $row['notes'];
				} else {
					$row_output['total'] = '$' . eman_number_format( $row_total );
				}

				$output['table']['rows'][]  = $row_output;
				$output['totals']['rows']  += $row_total;
			} else {
				$output['table']['rows'][]  = $full_row;
			}

		}

		if ( 'em_dcr' != $post->post_type ) {
			$output['totals']['table'] = '$' . eman_number_format( $output['totals']['rows'] );
		}

		return $output;
	}

	/**
	 * equipment
	 *
	 * @author  Jake Snyder
	 * @param	array	$field	The acf field array
	 * @param	obj		$post	The wp post object
	 * @return	array	$output	The table data
	 */
	public static function equipment( $field, $post )
	{
		if ( ! is_array($field['value']) ) {
			return false;
		}

		$output = array(
			'table'   => array(
				'titles'  => array(),
				'rows'    => array(),
			),
			'markups' => array(),
			'totals'  => array(
				'rows'    => 0,
				'table'   => 0,
				'markups' => 0,
			),
		);

		$row_count = 0;
		foreach ( $field['value'] as $row ) {
			// Set up the title row
			if ( ! $row_count ) {
				foreach ( $row as $key => $item ) {
					$output['table']['titles'][$key] = ucwords( str_replace(array('_','-'), ' ', $key) );
				}
				if ( 'em_dcr' != $post->post_type ) {
					$output['table']['titles']['total'] = "Total";
				}
				unset($output['table']['titles']['measure']);
				unset($output['table']['titles']['rental_price']);
			}

			// Set up the row data
			$row_output = array();
			$row_total  = 0;
			$full_row   = $row;
			$object     = array_shift($row);

			if ( is_object($object) ) {
				// If the price is empty, get it from the database
				if ( empty($row['rental_price']) ) {
					$row['rental_price']   = get_post_meta($object->ID, 'rate', true);
				}
				// If unit is empty, get it from the database
				if ( empty($row['measure']) ) {
					$row['measure'] = get_post_meta($object->ID, 'duration', true);
				}

				$row_output[]  = $object->post_title;
				$row_output[]  = $row['usage'] . ' ' . $row['measure']. ('em_dcr' != $post->post_type ? ' &times; $' . $row['rental_price'] . ' / ' . $row['measure'] : '');
				$row_output[]  = $row['scope_no'];
				$row_total    += $row['usage'] * $row['rental_price'];

				if ( 'em_dcr' == $post->post_type ) {
					$row_output['notes'] = $row['notes'];
				} else {
					$row_output['total'] = '$' . eman_number_format( $row_total );
				}

				$output['table']['rows'][]  = $row_output;
				$output['totals']['rows']  += $row_total;
			} else {
				$output['table']['rows'][]  = $full_row;
			}

		}

		if ( 'em_dcr' != $post->post_type ) {
			$output['totals']['table'] = '$' . eman_number_format( $output['totals']['rows'] );
		}

		return $output;
	}

	/**
	 * markup
	 *
	 * @author  Jake Snyder
	 * @param	array	$field	The acf field array
	 * @param	obj		$post	The wp post object
	 * @return	array	$output	The table data
	 */
	public static function markup( $markups, $total )
	{
		$output = array(
			'rows' => array(),
			'total' => 0,
		);

		foreach ( $markups as $markup )
		{
			if ( is_numeric($markup['value']) && 0 < $markup['value'] )
			{
				$markup_amount    = round( $total * ($markup['value']/100) );
				$output['total'] += $markup_amount;
				$output_row = array(
					'description' => ucwords( str_replace(array('_','-'), ' ', $markup['description']) ),
					'value'       => $markup['value'] . '%',
					'amount'      => '$' . eman_number_format( $markup_amount ),
				);
				$output['rows'][] = $output_row;
			}
		}

		return $output;
	}

	/**
	 * table_view
	 *
	 * @author  Jake Snyder
	 * @param	array	$summary	Table information: titles, rows, totals
	 * @return	void
	 */
	public static function table_view( $summary )
	{
?>
		<div class="panel panel-default"><table class="table">
			<tr>
				<?php $item_count=0; foreach ( $summary['table']['titles'] as $key => $title ) : ?>
				<th class="<?php echo ( ! $item_count ? 'first-item ' : '') . ('total' == $key ? 'last-item ' : '') . $key; ?>"><?php echo $title; ?></th>
				<?php $item_count++; endforeach; ?>
			</tr>

			<?php foreach ( $summary['table']['rows'] as $cols ) : ?>
			<tr>
				<?php $item_count=0; foreach ( $cols as $key => $col ) : ?>
				<td class="<?php echo ( ! $item_count ? 'first-item ' : '') . (in_array($key, array('total','notes')) ? 'last-item ' : '') . $key; ?>">
					<?php echo $col; ?>
				</td>
				<?php $item_count++; endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</table></div>

		<?php if ( $summary['totals']['table'] ) : ?>
		<div class="group-total clearfix">
			<div class="field_value">
				<ul class="list-group">
					<li class="list-group-item list-group-item-info">Subtotal: <span class="pull-right"><?php echo $summary['totals']['table']; ?></span></li>
				</ul>
			</div>
		</div>
<?php
		endif;

		return $summary['totals']['table'];
	}

	/**
	 * table_export
	 *
	 * @author  Jake Snyder
	 * @param	array	$summary	Table information: titles, rows, totals
	 * @return	void
	 */
	public static function table_export( $summary )
	{
		$output = '';
		if (is_array($summary['table']['titles']))
		{
			$titles = array_values($summary['table']['titles']);
			if ( is_array($summary['table']['rows']) )
			{
				foreach ( $summary['table']['rows'] as $cols )
				{
					#$row = array_shift($cols) . ': ';
					$row = '';
					$count = 0;
					foreach ( $cols as $key => $value ) {
						$row .= $titles[$count] . '=' . $value . ', ';
						$count++;
					}
		
					$output .= rtrim($row, ', ') . "\r\n";
				}
				$output = rtrim($output, "\r\n");
			}
		}

		return $output;
	}
}

endif;
