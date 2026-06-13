<?php
/*
Plugin Name: eManager table functions
Description: Used to more uniformly build tables for viewing and export
Version: 1.0.0
Author: Jake Snyder
*/

if ( ! class_exists('emanager_table') ) :

class emanager_table
{
	/**
	 * Settings
	 *
	 * @var 	string
	 */
	public $settings = array();

	/**
	 * Manage post type and its settings
	 *
	 * @var 	string
	 */
	public $post_type;
	public $post_type_object;
	public $post_type_settings;

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function __construct()
	{
		$this->settings = array(
			'head'    => '',
			'rows'    => array(),
			'count'   => 0,
			'export'  => array(
				'delimiter' => ",",
				'nl'        => "\r\n",
			),
			'colspan' => 0,
			'unsortable' => [],#array( 'noc_total' ), // A lit of columns that aren't sortable at this time
			'exclude_cols' => array(
				'signature'
			),
		);
	}

	/**
	 * Build a table of posts
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function table( $posts, $args=false )
	{
		$defaults = array(
			'type' => 'view',
			'cols' => 'default',
			'page' => 1,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		#if ( ! $posts ) global $posts;
		if ( ! $posts )
		{
			if ( 'export' == $type ) {
				return $this->empty_export();
			} else {
				return $this->empty_view();
			}
		}
		else
		{
			$this->post_type          = $posts[0]->post_type;
			$this->post_type_object   = get_post_type_object( $this->post_type );
			$this->post_type_settings = eman_post_types( $this->post_type_object );

			$head = $content = '';
			foreach ( $posts as $post ) : if ( eman_can_view($post) ) :

				$title_head = $this->post_type_object->labels->name;

				#$fields = get_fields($post->ID);
				$cpt    = eman_post_types($post->post_type);
				$fields = eman_form_fields($cpt['form'], $post->ID);

				if ( 'em_invoice' == $this->post_type && 'export' == $type )
				{
					$columns = array(
						array(
							'title' => 'Scan Status',
							'name'  => '',
							'value' => '',
						),
						array(
							'title' => 'Index Status',
							'name'  => '',
							'value' => '',
						),
						array(
							'title' => 'Remove Specials Characters',
							'name'  => '',
							'value' => '1',
						),
						array(
							'title' => 'Expense Type',
							'name'  => '',
							'value' => 'PA',
						),
						array(
							'title' => 'Company Code',
							'name'  => '',
							'value' => '3000',
						),
						array(
							'title' => 'Currency',
							'name'  => '',
							'value' => 'USD',
						),
						array(
							'title' => 'Vendor Number',
							'name'  => 'vendor',
							'value' => '',
						),
						array(
							'title' => 'Reference Number',
							'name'  => 'invoice_id',
							'value' => '',
						),
						array(
							'title' => 'Document Date',
							'name'  => 'date',
							'value' => '',
						),
						array(
							'title' => 'Posting Date',
							'name'  => '',
							'value' => date_i18n( 'n/j/y' ),
						),
						array(
							'title' => 'Amount',
							'name'  => 'amount',
							'value' => '',
						),
						array(
							'title' => 'Header Text',
							'name'  => 'job_wbse',
							'value' => '',
						),
						array(
							'title' => 'Credit Memo',
							'name'  => '',
							'value' => 'No',
						),
						array(
							'title' => 'Tax Code',
							'name'  => '',
							'value' => '',
						),
						array(
							'title' => 'Total Tax Amount',
							'name'  => 'tax',
							'value' => '',
						),
						array(
							'title' => 'Profit Center',
							'name'  => '',
							'value' => '300221',
						),
						array(
							'title' => 'Project Number',
							'name'  => 'job_id',
							'value' => '',
						),
						array(
							'title' => 'Alternate Payee',
							'name'  => '',
							'value' => '',
						),
					);
					foreach ( $columns as $column )
					{
						if ( 'vendor' == $column['name'] ) {
							$company = get_metadata('post', $post->ID, 'company', true);
							$content = get_metadata('post', $company, 'sap', true);
						} elseif ( 'date' == $column['name'] ) {
							$date    = get_metadata('post', $post->ID, $column['name'], true);
							$content = ( $date ) ? date_i18n( 'n/j/y', strtotime($date) ) : '-';
						} elseif ( 'job_wbse' == $column['name'] ) {
							$job_id  = get_metadata('post', $post->ID, 'job_id', true);
							$wbse    = get_metadata('post', $post->ID, 'wbse', true);
							$content = $job_id .'.'. ltrim($wbse,'.');
						} elseif ( 'Tax Code' == $column['title'] ) {
							$tax     = get_metadata('post', $post->ID, 'tax', true);
							if ( $tax ) {
								$content = 'I1';
							} else {
								$content = 'I0';
							}
						} else {
							$content = ($column['name'] ? get_metadata('post', $post->ID, $column['name'], true) : $column['value']);
							if ( 'job_id' == $column['name'] ) {
								$content = mb_substr($content, 0, 6);
							}
						}
						$this->col( array(
							'content' => $content,
							'type'    => $type,
							'head'    => $column['title'],
							'class'   => $column['name'],
						) );
					}
					if ( 1 < $args['page'] ) { 
						$this->settings['head'] = '';
					}
				}
				elseif ( 'em_dcr' == $this->post_type && 'export' == $type )
				{
					// Get worker totals
					$total_workers = 0;
					$total_male_non = 0;
					$total_male_minority = 0;
					$total_female_non = 0;
					$total_female_minority = 0;

					$field_name  = 'classification_breakdown';
					$label      .= ' Detail';
					$summary     = emanager_summary::classification_breakdown($fields[$field_name], $post);
					if ( $summary && ! empty($summary['table']['rows']) ) {
						foreach ( $summary['table']['rows'] as $row ) {
							$total_male_minority += intval($row['male_minority']);
                            $total_male_non += intval($row['male_non-minority']);
                            $total_female_minority += intval($row['female_minority']);
                            $total_female_non += intval($row['female_non-minority']);
						}
						$total_workers += ($total_male_non + $total_male_minority + $total_female_non + $total_female_minority);
					}
					$labor_total = $total = $summary['totals']['rows'];
					$classification_breakdown = emanager_summary::table_export($summary);


					$field_name      = 'equipment';
					$label          .= ' Detail';
					$summary         = emanager_summary::equipment($fields[$field_name], $post);
					$equipment_total = $total = $summary['totals']['rows'];
					$equipment       = emanager_summary::table_export($summary);


					$field_name  = 'backup';
					$backup = '';
					if ( is_array($fields[$field_name]['value']) ) {
						foreach ( $fields[$field_name]['value'] as $file ) {
							foreach ( $fields[$field_name]['sub_fields'] as $sub_field ) {
								$value .= $file[ $sub_field['name'] ] . "\n\r";
							}
						}
						$backup = rtrim($value, "\n\r");
					}


					$columns = array(
/*
						array(
							'title' => 'ID',
							'name'  => 'ID',
							'value' => '',
						),
*/
						array(
							'title' => 'Company',
							'name'  => 'company',
							'value' => '',
						),
						array(
							'title' => 'Work Date',
							'name'  => 'work_date',
							'value' => '',
						),
						array(
							'title' => 'Shift',
							'name'  => 'shift',
							'value' => '',
						),
						array(
							'title' => 'Total Headcount',
							'name'  => '',
							'value' => $total_workers,
						),
						array(
							'title' => 'Minority Headcount',
							'name'  => '',
							'value' => $total_male_minority + $total_female_minority,
						),
						array(
							'title' => 'Female Headcount',
							'name'  => '',
							'value' => $total_female_non + $total_female_minority,
						),
						array(
							'title' => 'Description',
							'name'  => 'other_notes',
							'value' => '',
						),
						array(
							'title' => 'Incidents?',
							'name'  => 'incidents_on_site',
							'value' => '',
						),
						array(
							'title' => 'Equipment Detail',
							'name'  => '',
							'value' => $equipment,
						),
						array(
							'title' => 'Labor Detail',
							'name'  => '',
							'value' => $classification_breakdown,
						),
						array(
							'title' => 'Backup',
							'name'  => '',
							'value' => $backup,
						),
					);


					foreach ( $columns as $column )
					{
						if ( 'date' == $column['name'] ) {
							$date    = get_metadata('post', $post->ID, $column['name'], true);
							$content = ( $date ) ? date_i18n( 'n/j/y', strtotime($date) ) : '-';
						} elseif ( 'ID' == $column['name'] ) {
							$content = $post->ID;
						} elseif ( 'company' == $column['name'] ) {
							$company = get_metadata('post', $post->ID, 'company', true);
							$content = get_the_title($company);
						} else {
							$content = ($column['name'] ? get_metadata('post', $post->ID, $column['name'], true) : $column['value']);
							if ( 'job_id' == $column['name'] ) {
								$content = mb_substr($content, 0, 6);
							}
						}
						$this->col( array(
							'content' => $content,
							'type'    => $type,
							'head'    => $column['title'],
							'class'   => $column['name'],
						) );
					}
					if ( 1 < $args['page'] ) { 
						$this->settings['head'] = '';
					}
				}
				else
				{
					if ( 'all' == $cols )
					{
						$this->col( array(
							'content' => $post->ID,
							'type'    => $type,
							'head'    => 'ID',
							'class'   => 'post_id',
						) );
					}

					if ( 'all' == $cols || 'author' == $cols )
					{
						$this->col( array(
							'content' => eman_users_name($post->post_author),
							'type'    => $type,
							'head'    => 'Submitter',
							'class'   => 'author',
						) );
						$this->col( array(
							'content' => emanager_post::user_company($post->post_author),
							'type'    => $type,
							'head'    => 'Submitter Company',
							'class'   => 'author_company',
						) );
					}

					if ( ! empty($this->post_type_settings['table_cols']) )
					{
						foreach ( $this->post_type_settings['table_cols'] as $key => $value )
						{
							// Unset the specified field from the bigger field collection
							unset($fields[$key]);
	/**/
							if ( 'em_noc' == $this->post_type && 'noc' != eman_pco_or_noc($post) && ('noc_number' == $key || 'pco_number' == $key) ) {
								continue;
							}
	/**/
							if ( 'title' == $key ) {
								$content = '<a href="'.get_permalink($post->ID).'" title="'.esc_attr($post->post_title).'"><strong>'.apply_filters('the_title', $post->post_title).'</strong></a>';
							} elseif ( 'date' == $key ) {
								$content = date_i18n( 'm/d/Y', strtotime($post->post_date) );
							} elseif ( 'ID' == $key ) {
								$content = $post->ID;
							} elseif ( 'bic_company' == $key ) {
								$user = emanager_bic::get_bic( $post, 'ID' );
								$content = emanager_post::user_company( $user );
							} else {
								$content = $this->field_value( $key, $post );
								if ( 'status' == $key && 'em_tickets' == $post->post_type )
								{
									$charge = '';
									if ( 'Approved' == $content )
									{
										$charge_slugs = array();
										$charges      = array();
										$taxonomy     = 'em_charge';
										$terms        = wp_get_post_terms( $post->ID, $taxonomy );
										foreach ( $terms as $term ) {
											$charges[$term->slug] = $term->name;
										}
										if ( $charges )
										{
											if ( array_key_exists('paid', $charges) ) {
												$charge = $charges['paid'];
											} elseif ( array_key_exists('billed', $charges) ) {
												$charge = $charges['billed'];
											}
										}
									}
	
									if ( 'export' == $type )
									{
										$this->col( array(
											'content' => ($charge ? $charge : "N/A"),
											'type'    => $type,
											'head'    => 'Charge',
											'class'   => 'charge',
										) );
									} else {
										if ( $charge ) {
											$charge = " ($charge)";
										}
										$content .= $charge;
									}
								}
							}
	
							$this->col( array(
								'content' => $content,
								'type'    => $type,
								'head'    => $value,
								'class'   => $key,
							) );
						}
					}

					/**
					 * Get ALL fields
					 */
					if ( 'all' == $cols )
					{
						if ( is_array($fields) )
						{
							$labor_total = $material_total = $equipment_total = 0;
	
							foreach ( $fields as $field )
							{
								// If there is no key, it is excluded, or it has already been displayed
								if ( ! $field['name'] || in_array($field['name'], $this->settings['exclude_cols']) || array_key_exists($field['name'], $this->post_type_settings['table_cols']) ) {
									continue;
								}
	
								$label = $field['label'];
								$final_total = $total = false;
								$value = '';
	
								if ( 'classification_breakdown' == $field['name'] )
								{
									$label          .= ' Detail';
									$summary         = emanager_summary::classification_breakdown($field, $post);
									$labor_total     = $total = $summary['totals']['rows'];
									$value           = emanager_summary::table_export($summary);
								}
								elseif ( 'employee_breakdown' == $field['name'] )
								{
									$label          .= ' Detail';
									$summary         = emanager_summary::employee_breakdown($field, $post);
									$labor_total     = $total = $summary['totals']['rows'];
									$value           = emanager_summary::table_export($summary);
								}
								elseif ( 'materials' == $field['name'] )
								{
									$label          .= ' Detail';
									$summary         = emanager_summary::materials($field, $post);
									$material_total  = $total = $summary['totals']['rows'];
									$value           = emanager_summary::table_export($summary);
								}
								elseif ( 'equipment' == $field['name'] )
								{
									$label          .= ' Detail';
									$summary         = emanager_summary::equipment($field, $post);
									$equipment_total = $total = $summary['totals']['rows'];
									$value           = emanager_summary::table_export($summary);
								}
								elseif ( 'labor_markup' == $field['name'] || 'material_markup' == $field['name'] || 'equipment_markup' == $field['name'] )
								{
									$label          .= ' Detail';
	
									if ( 'labor_markup' == $field['name'] ) :
										$current_total = $labor_total;
									elseif ( 'material_markup' == $field['name'] ) :
										$current_total = $material_total;
									elseif ( 'equipment_markup' == $field['name'] ) :
										$current_total = $equipment_total;
									endif;
	
									if ( $field['value'] ) :
										$summary         = emanager_summary::markup($field['value'], $current_total);
										$markup_total    = $summary['total'];
										foreach ( $summary['rows'] as $row ) {
											$value .= $row['description'] . ': ' . $row['value'] . ' = ' . $row['amount'] . "\n\r";
										}
										$value = rtrim($value, "\n\r");
										$total = ( $markup_total ? $current_total + $markup_total : 0 );
									endif;
	
									if ( 'labor_markup' == $field['name'] ) :
										$labor_total     += $markup_total;
									elseif ( 'material_markup' == $field['name'] ) :
										$material_total  += $markup_total;
									elseif ( 'equipment_markup' == $field['name'] ) :
										$equipment_total += $markup_total;
									endif;
								}
								elseif ( 'global_markup' == $field['name'] )
								{
									$label          .= ' Detail';
	
									$total = $labor_total + $material_total + $equipment_total;
	
									$this->col( array(
										'content' => '$' . eman_number_format( $total ),
										'type'    => $type,
										'head'    => 'Subtotal',
										'class'   => $field['name'],
									) );
	
									$markup_total = 0;
									if ( is_array($field['value']) )
									{
										foreach ( $field['value'] as $markup )
										{
											if ( is_numeric($markup['value']) && 0 < $markup['value'] )
											{
												$markup_amount = round($total * ($markup['value']/100));
												$markup_total += $markup_amount;
												#echo ucwords( str_replace(array('_','-'), ' ', $markup['description']) ) . ': ' . $markup['value'] . '% = $' . eman_number_format( $markup_amount ) . '<br />';
												$value .= ucwords( str_replace(array('_','-'), ' ', $markup['description']) ) . ': ' . $markup['value'] . '% = $' . eman_number_format( $markup_amount ) . "\n\r";
											}
										}
										$value = rtrim($value, "\n\r");
									}
	
									$final_total = $total = $total + $markup_total;
								}
								elseif ( 'contractors_and_estimate' == $field['name'] )
								{
									$label = "C&E Total";
	
									$total = $row_count = 0;
									if ( is_array($field['value']) )
									{
										foreach ( $field['value'] as $row )
										{
											foreach ( $row as $key => $item )
											{
												$value .= ucwords( str_replace(array('_','-'), ' ', $key) ) . ': ';
												if ( 'subcontractor' == $key ) {
													$value .= $item->post_title . ', ';
												} elseif ( 'estimated_value' == $key ) {
													$value .= '$' . eman_number_format( $item ) . ', ';
													$total += $item;
												} elseif ( is_object($item) ) {
													$value .= $item->post_title . ', ';
												} else {
													$value .= ($item ? $item : 'n/a') . ', ';
												}
											}
											$value  = rtrim($value, ", ");
											$value .= "\n\r";
											$row_count++;
										}
										$value = rtrim($value, "\n\r");
									}
								}
								elseif ( 'scope' == $field['name'] )
								{
									if ( ! empty($field['value']) )
									{
										$has_location = false;
										$output = '';
										foreach ( $field['value'] as $row )
										{
											$location = false;
											if ( $row['location'] ) {
												$location = 'location';
											} elseif ( $row['floor'] ) {
												$location = 'floor';
											} elseif ( $row['building'] ) {
												$location = 'building';
											}
	
											foreach ( $field['sub_fields'] as $sub_field )
											{
												if ( $location == $sub_field['name'] )
												{
													$sub_field['value'] = $row[ $sub_field['name'] ];
													if ( $sub_field['value'] ) {
														$has_location = true;
													}
													$value .= 'Location: ' . get_the_title($sub_field['value']) . ', ';
												}
												if ( 'scope' == $sub_field['name'] )
												{
													$sub_field['value'] = $row[ $sub_field['name'] ];
													$value .= 'Scope: ' . eman_field_value($sub_field, $post) . ', ';
												}
											}
											$value = rtrim($value, ", ");
											$value .= "\n\r";
										}
										$value = rtrim($value, "\n\r");
									}
								}
								elseif ( 'backup' == $field['name'] )
								{
									if ( is_array($field['value']) )
									{
										foreach ( $field['value'] as $file ) {
											foreach ( $field['sub_fields'] as $sub_field ) {
												$value .= $file[ $sub_field['name'] ] . "\n\r";
											}
										}
										$value = rtrim($value, "\n\r");
									}
								}
								else
								{
									$value = eman_field_value($field, $post);
								}
	
								$this->col( array(
									'content' => $value,
									'type'    => $type,
									'head'    => $label,
									'class'   => $field['name'],
								) );
	
								if ( false !== $total )
								{
									$this->col( array(
										'content' => '$' . eman_number_format( $total ),
										'type'    => $type,
										'head'    => $field['label'] . ' Subtotal',
										'class'   => $field['name'] . '_total',
									) );
								}
	
								if ( false !== $final_total )
								{
									$this->col( array(
										'content' => '$' . eman_number_format( $final_total ),
										'type'    => $type,
										'head'    => 'Total',
										'class'   => 'total',
									) );
								}
							}
						}
	
						/**
						 * Add the post date
						 */
	                    if ( 'em_noc' == $post->post_type )
	                    {
	                        // Get the last review that submitted the noc
	                        $reviews = new WP_Query( array(
	                            'post_type'      => 'em_reviews',
	                            'posts_per_page' => 1,
	                            'order'          => 'DESC',
	                            'orderby'        => 'date',
	                            'tax_query'      => array(
	                                array(
	                                    'taxonomy' => 'em_status',
	                                    'field'    => 'slug',
	                                    'terms'    => 'submitted',
	                                )
	                            ),
	                            'meta_query'     => array(
	                                array(
	                                    'key'      => 'reviewed_id',
	                                    'value'    => $post->ID,
	                                    'compare'  => '  = ',
	                                )
	                            )
							) );
							$date = ( is_object($reviews) && ! empty($reviews->post) && ! empty($reviews->post->post_date) ? date_i18n( 'm/d/Y', strtotime($reviews->post->post_date) ) : '-');
	                    } else {
	                        $date = date_i18n( 'm/d/Y', strtotime($post->post_date) );
	                    }
						$this->col( array(
							'content' => $date,
							'type'    => $type,
							'head'    => 'Date',
							'class'   => 'date',
						) );
	
						/**
						 * Add reviews
						 */
						if ( ('em_tickets' == $post->post_type || 'em_noc' == $post->post_type || 'em_dcr' == $post->post_type) )
						{
							$cell = 'n/a';
							$reviews = new WP_Query( array(
								'post_type' => 'em_reviews',
								#'fields' => 'id=>parent',
								'posts_per_page' => -1,
								'order' => 'DESC',
								'orderby'=> 'date',
								'meta_query' => array(
									array(
										'key' => 'reviewed_id',
										'value' => $post->ID,
										'compare' => '=',
									)
								)
							) );
	
							if ( ! empty($reviews->posts) )
							{
								$cell = '';
								foreach ( $reviews->posts as $review )
								{
									$terms  = wp_get_post_terms( $review->ID, 'em_status' );
									$status = $status_slug = false;
									if ( ! empty($terms[0]) ) :
										$status      = $terms[0]->name;
										$status_slug = $terms[0]->slug;
									endif;
	
									if ( 'recommend' == $status_slug || 'executed' == $status_slug ) :
										$field = get_field_object('direction', $review->ID);
									endif;
	
									$send_to = ( in_array($status_slug, array('manager','superintendent','ready')) ) ? eman_get_field('send_to',$review->ID) : false;
									if ( $status ) $cell .= $status . ( !empty($send_to['display_name']) ? ': '. $send_to['display_name'] : '');
									$cell .= ', Reviewed by: ' . eman_users_name($review->post_author);
									$cell .= ' on ' . get_the_time( get_option('date_format') .', '. get_option('time_format'), $review->ID );
									$cell .= "\n\r";
								}
							}
	
							$this->col( array(
								'content' => $cell,
								'type'    => $type,
								'head'    => 'Reviews',
								'class'   => 'reviews',
							) );
						}
					}
				}

				$this->settings['count']++;
			endif; endforeach;

			if ( 'export' == $type ) {
				return $this->table_export();
			} else {
				return $this->table_view();
			}
		}
	}

	/**
	 * Build a table for export
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function empty_export()
	{
		echo $this->row_export("No items match your request");
	}

	/**
	 * Build a table for viewing on screen
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function empty_view()
	{
		?>
		<div class="table-responsive">
		<table class="table table-bordered">
			<tr><td><p>No items match your request</p></td></tr>
		</table>
		</div>
		<?php
	}

	/**
	 * Build a table for export
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function table_export()
	{
		$content = '';
		foreach ( $this->settings['rows'] as $row ) {
			$content .= $this->row_export($row);
		}
		echo ($this->settings['head'] ? rtrim($this->settings['head'],',') . $this->settings['export']['nl'] : '') . $content;
	}

	/**
	 * Build a table for viewing on screen
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function table_view()
	{
		$content = '';
		foreach ( $this->settings['rows'] as $row ) {
			$content .= $this->row_view($row);
		}
		?>
		<div class="table-responsive">
		<table class="table table-bordered">
		<?php if ( $content ) :
			if ( $this->settings['head'] ) : ?>
			<thead>
				<?php echo $this->settings['head']; ?>
			</thead>
			<?php endif; ?>
			<tbody>
				<?php echo $content; ?>
			</tbody>
		<?php else : ?>
			<tr><td colspan="<?php echo $this->settings['colspan']; ?>"><p>There is nothing to display yet.</p></td></tr>
		<?php endif; ?>
		</table>
		</div>
		<?php
		do_action( 'patch/page_navi' );
	}

	/**
	 * Create a row for export
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function row_export( $content )
	{
		return $content . $this->settings['export']['nl'];
	}

	/**
	 * Create a row for on screen viewing
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function row_view( $content )
	{
		return '<tr>' . $content . '</tr>';
	}

	/**
	 * Create a column
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function col( $args=false )
	{
		$defaults = array(
			'content' => false,
			'type'    => 'view',
			'head'    => '',
			'class'   => false,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		if ( ! $this->settings['count'] )
		{
			$this->settings['colspan']++;
			$this->settings['head'] .= ('export' == $type ? $this->col_head_export($head) : $this->col_head_view($head, $class));
		}

		if ( empty($this->settings['rows'][$this->settings['count']]) ) {
			$this->settings['rows'][$this->settings['count']] = '';
		}

		$this->settings['rows'][$this->settings['count']] .= ('export' == $type ? $this->col_export($content) : $this->col_view($content, $class));
	}

	/**
	 * Add items to the column head for export
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function col_head_export( $content )
	{
		return $this->col_export( ucwords( str_replace(array('_','-'), ' ', $content) ) );
	}

	/**
	 * Add items to the column head for on screen viewing
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function col_head_view( $content, $class=false )
	{
		$output  = '<th' . ($class ? ' class="' . $class . '"' : '') . '>';
		$output .= self::sort_link( $content, $class );
		$output .= '</th>';

		return $output;
	}

	public function sort_link( $content, $class )
	{
		if ( in_array($class, $this->settings['unsortable']) ) return $content;

		$output = '';
		$url = apply_filters( 'emanager_table_filters/sort_url', $class );
		if ( $url )
		{
			$output .= '<a href="' . $url . '" title="Sort by this column">';
			$output .= $content;
			if ( ! empty($_GET['sort']) && $class == $_GET['sort'] )
			{
				if ( ! empty($_GET['order']) && 'asc' == $_GET['order'] ) {
					$output .= ' <span class="fa fa-sort-asc" aria-hidden="true"></span>';
				} else {
					$output .= ' <span class="fa fa-sort-desc" aria-hidden="true"></span>';
				}
			}
			else
			{
				$output .= ' <span class="fa fa-sort" aria-hidden="true"></span>';
			}
			$output .= '</a>';

			return $output;
		}

		return $content;
	}

	/**
	 * Create a column for export
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function col_export( $content )
	{
		return '"' . strip_tags( str_replace('"', '""', $content) ) . '"' . $this->settings['export']['delimiter']; //htmlspecialchars_decode()
	}

	/**
	 * Create a column for on screen viewing
	 *
	 * @author  Jake Snyder
	 * @return	string Modified content
	 */
	public function col_view( $content, $class=false )
	{
		return '<td' . ($class ? ' class="' . $class . '"' : '') . '>' . $content . '</td>';
	}


	/**
	 * Process special fields
	 *
	 * @author  Jake Snyder
	 * @return	void
	 * /
	public function field_value( $key, $post )
	{
		$field = get_field_object($key, $post->ID);
		return ($field ? eman_field_value($field, $post) : '');
	}
	
	/**
	 * Process special fields
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function field_value( $key, $post )
	{
		if ( ! $key || ! $post ) { return false; }

		$post_id   = $post->ID;
		$field_key = $key;
#		$field_key = get_field_reference( $key, $post_id );
#echo ' $field_key = '. $field_key ."<br>\n";
#		if ( ! $field_key ) { return false; }
		$field     = get_field_object( $field_key, $post_id );

		return ($field ? eman_field_value($field, $post) : '');
	}

	/**
	 * Process special fields
	 *
	 * @author  Jake Snyder
	 * @return	void
	 * /
	public function field_value( $key, $post )
	{
		#if ( ! $key || ! $post ) { return false; }

		$post_id   = $post->ID;
		#$field_key = get_field_reference( $key, $post_id );

		#if ( ! $field_key ) { return false; }
		$field     = get_field_object( $key, $post_id );

		return eman_field_value($field, $post);#($field ? eman_field_value($field, $post) : '');
	}
	/**/
}

endif;
