<?php

$classname = 'emanager_table_export';
if ( ! class_exists($classname) ) :

class emanager_table_export
{
	/**
	 * Class prefix
	 *
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Construct
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function __construct()
	{
		add_action( self::PREFIX . '/setup_export', array($this, 'setup_export') );
		add_action( 'wp_ajax_eman_csv_export_all', array($this, 'setup_export_ajax') );
		add_action( 'wp_ajax_eman_csv_export_month', array($this, 'setup_export_ajax') );
	}




	/**
	 * setup_export
	 *
	 * Reads request variables and builds an export CSV when requested.
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function setup_export()
	{
		if ( empty($_REQUEST['action']) || 'csv' != $_REQUEST['action'] ) return;

		global $query_string;

		$posts = array();
		if ( ! empty($_REQUEST['type']) && 'all' == $_REQUEST['type'] )
		{
			$posts = get_posts( $query_string . '&posts_per_page=-1' );
		}
		elseif ( ! empty($_REQUEST['month']) || ! empty($_REQUEST['year']) )
		{
			$y = ( ! empty($_REQUEST['year']) && is_numeric($_REQUEST['year']) ) ? $_REQUEST['year'] : date('Y');
			$m = ( ! empty($_REQUEST['month']) && is_numeric($_REQUEST['month']) && 0 < $_REQUEST['month'] && 13 > $_REQUEST['month'] ) ? $_REQUEST['month'] : date('n');
			$new_wp_query = new WP_Query( wp_parse_args($query_string, array(
				'date_query' => array(
					array(
						'after'     => array(
							'year'  => $y,
							'month' => $m,
							'day'   => 1,
						),
						'before'    => array(
							'year'  => $y,
							'month' => $m,
							'day'   => date_i18n('t'),
						),
						'inclusive' => true,
					),
				),
				'posts_per_page' => -1,
			)) );

			if ( $new_wp_query ) {
				$posts = $new_wp_query->posts;
			}
			
		}
		elseif ( ! empty($_REQUEST['type']) && 'month' == $_REQUEST['type'] )
		{
			$y = date_i18n('Y');
			$m = date_i18n('n');
			$new_wp_query = new WP_Query( wp_parse_args($query_string, array(
				'date_query' => array(
					array(
						'after'     => array(
							'year'  => $y,
							'month' => $m,
							'day'   => 1,
						),
						'before'    => array(
							'year'  => $y,
							'month' => $m,
							'day'   => date_i18n('t'),
						),
						'inclusive' => true,
					),
				),
				'posts_per_page' => -1,
			)) );
			if ( $new_wp_query ) {
				$posts = $new_wp_query->posts;
			}
		}
		else
		{
			$posts = $GLOBALS['posts'];
		}

		$args = array(
			'type'  => 'export',
			'cols'  => 'all',
		);

		$filename = post_type_archive_title(NULL, false) . '_' . date_i18n('Y_m_d-g_h_A', current_time('timestamp'));
		$fileType = "csv";

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$filename.$fileType");

		#echo "<pre>\n";
		$table_class = new emanager_table();
		$table_class->table($posts, $args);
		#echo "</pre>\n";

		die;
	}

	/**
	 * Reads request variables and builds an export CSV when requested.
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function setup_export_ajax()
	{
		if ( empty($_REQUEST['post_type']) )    { die('No post type provided'); }
		if ( empty($_REQUEST['query_string']) ) { die('No query string provided'); }

		// User object
		$current_user   = wp_get_current_user();

		// Settings
		$post_type      = $_REQUEST['post_type'];
		$query_string   = $_REQUEST['query_string'];
		wp_parse_str($query_string, $query_array);
		$posts_per_page = 10;#get_option('posts_per_page');
		$company        = get_post($current_user->company);
		$upload_dir     = wp_upload_dir();
		$base_dir       = $upload_dir['basedir'];
		$company_dir    = "{$upload_dir['basedir']}/Companies/{$company->post_title}/";
		$company_url    = "{$upload_dir['baseurl']}/Companies/{$company->post_title}/";
		$temp_dir       = "{$upload_dir['basedir']}/tmp/";
		$file_name      = '';
		$files          = array();
		$page           = 1;
		$user_meta_key  = $post_type . '_export_progress';
		$table_settings = array(
			'type'  => 'export',
			'cols'  => 'all',
		);

		// If no company, nowhere to save
		if ( ! $company ) { die('User could not be determined'); }

		// If no temp directory yet, create it
		if ( ! file_exists($temp_dir) ) {
			// If the directory can't be created, return
			if ( ! wp_mkdir_p( $temp_dir ) ) {
				die('Cannot create temporary directory');
			}
		}

		// Reset on first click
		if ( ! empty($_REQUEST['new']) ) {
			delete_metadata( 'user', $current_user->ID, $user_meta_key );
		}

		// Retrieve option for file if one exists, and parse it
		if ( $progress = get_metadata('user', $current_user->ID, $user_meta_key, true) )
		{
			if ( ! empty($progress['filename']) ) {
				$file_name = $progress['filename'];
			}
			if ( ! empty($progress['files']) ) {
				$files     = $progress['files'];
			}
			if ( ! empty($progress['page']) ) {
				$page      = $progress['page'];
			}
		}

		// Try to get posts using page variable
		#$posts = get_posts( $query_string . "&paged={$page}&posts_per_page={$posts_per_page}" );

		$query_args = array_merge( $query_array, array(
			'order'          => 'ASC',
			'orderby'        => 'date',
			'paged'          => $page,
			'posts_per_page' => $posts_per_page,
		) );

		if ( ! empty($_REQUEST['month']) || ! empty($_REQUEST['year']) )
		{
			$y = ( ! empty($_REQUEST['year']) && is_numeric($_REQUEST['year']) ) ? esc_sql($_REQUEST['year']) : date('Y');
			$m = ( ! empty($_REQUEST['month']) && is_numeric($_REQUEST['month']) && 0 < $_REQUEST['month'] && 13 > $_REQUEST['month'] ) ? esc_sql($_REQUEST['month']) : date('n');

			if ( 1 == $page ) {
				$file_extra = "__$y-$m";
			}

			if ( 'em_dcr' == $post_type )
			{
				$meta_date_key          = 'work_date';
				$query_args['meta_key'] = $meta_date_key;
				$query_args['orderby']  = 'meta_value';
				$query_args['meta_query'] = array(
					array(
						'key'     => $meta_date_key,
						'value'   => "{$y}-{$m}-01",
						'compare' => '>=',
						'type'    => 'DATE',
					),
					array(
						'key'     => $meta_date_key,
						'value'   => "{$y}-{$m}-" . date_i18n('t'),
						'compare' => '<=',
						'type'    => 'DATE',
					),
				);
			}
			else
			{
				$query_args['date_query'] = array(
					array(
						'after'     => array(
							'year'  => $y,
							'month' => $m,
							'day'   => 1,
						),
						'before'    => array(
							'year'  => $y,
							'month' => $m,
							'day'   => date_i18n('t'),
						),
						'inclusive' => true,
					),
				);
			}
		}

		if ( ! empty($_REQUEST['start']) && ! empty($_REQUEST['end']) )
		{
			$s = esc_sql($_REQUEST['start']);
			$e = esc_sql($_REQUEST['end']);

			if ( 1 == $page ) {
				$file_extra = "__$s-$e";
			}

			if ( 'em_dcr' == $post_type )
			{
				$meta_date_key          = 'work_date';
				$query_args['meta_key'] = $meta_date_key;
				$query_args['orderby']  = 'meta_value';
				$query_args['meta_query'] = array(
					array(
						'key'     => $meta_date_key,
						'value'   => $s,
						'compare' => '>=',
						'type'    => 'DATE',
					),
					array(
						'key'     => $meta_date_key,
						'value'   => $e,
						'compare' => '<=',
						'type'    => 'DATE',
					),
				);
			}
			else
			{
				list($s_year,$s_month,$s_day) = explode('-', $s);
				list($e_year,$e_month,$e_day) = explode('-', $e);
				$query_args['date_query'] = array(
					array(
						'after'     => array(
							'year'      => $s_year,
							'month'     => $s_month,
							'day'       => $s_day,
						),
						'before'    => array(
							'year'      => $e_year,
							'month'     => $e_month,
							'day'       => $e_day,
						),
						'inclusive' => true,
					),
				);
			}
		}

		if ( 'em_invoice' == $post_type )
		{
			// make sure only approved items export
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'em_status',
					'field'    => 'slug',
					'terms'    => 'approved',
				),
			);
		}


		$posts = new WP_Query( $query_args );


		// Or create filename and current page
		if ( ! $file_name ) {
			$file_name = "{$current_user->user_login}__" . str_replace('em_', '', $post_type) . "__" . date_i18n('Y-m-d_H-i-s') . $file_extra . '.csv';
		}

		// Temporary file
		$file = $temp_dir . $file_name;


		// If no posts
		if ( ! $posts->have_posts() )
		{
			$url = false;
			// Make sure the temp file is moved to company folder
			if ( file_exists($file) ) {
				$url = $company_url . $file_name;
				rename( $file, $company_dir . $file_name );
			}

			// Delete the database option
			delete_metadata( 'user', $current_user->ID, $user_meta_key );
			wp_cache_delete($current_user->ID, 'user_meta');

			// Make a zip of the pdf files and csv for download
			if ( 'em_invoice' == $post_type )
			{
				// Build the zip
				$zip_name = str_replace( '.csv', '.zip', $file_name );
				$zip_url  = $company_url . $zip_name;
				$zip_path = $company_dir . $zip_name;
				$zip      = new ZipArchive;
				if ( file_exists($zip_path) ) {
					$zip->open( $zip_path, ZipArchive::OVERWRITE );
				} else {
					$zip->open( $zip_path, ZipArchive::CREATE );
				}

				// Add the csv to the zip
				if ( file_exists($company_dir . $file_name) ) {
					$zip->addFromString( basename($company_dir . $file_name),  file_get_contents($company_dir . $file_name) );
				}

				// Add the pdfs to the zip
#				$files = array_unique($files);
				$fc=1;
				foreach ( $files as $file ) {
					$fc_string = str_pad($fc, 3, '0', STR_PAD_LEFT);
					$zip->addFromString( "{$fc_string}_" . basename($file),  file_get_contents($file) );
					$fc++;
				}

				$zip->close();

				// Set zip as the download
				$url = $zip_url;
			}

			// Call it a day
			echo json_encode( array(
				'status' => 0,
				'url'    => $url,
			) );
			die;
		}
		else
		{
			$mode = (1 == $page ? 'w' : 'a');
			// Try to open existing file
			$handle = fopen($file, $mode);// or die("Cannot open file: $file");
			// Otherwise try to create it
			if ( ! $handle ) {
				$handle = fopen($file, 'w') or die("Cannot create file: $file");
			}

			// Add the files for invoices
			if ( 'em_invoice' == $post_type )
			{
				foreach ( $posts->posts as $post )
				{
					$pdfs_array    = array();
					$invoice_id    = get_metadata( 'post', $post->ID, 'file', true );
					$invoice_path  = get_attached_file( $invoice_id );#$invoice_url   = wp_get_attachment_url( $invoice_id );
					if ( $invoice_path )
					{
						// Add the invoice
						$pdfs_array[] = $invoice_path;

						// Add the packing slip
						$slip_id       = get_metadata( 'post', $post->ID, 'file2', true );
						$slip_path     = get_attached_file( $slip_id );
						if ( $slip_path ) {
							$pdfs_array[] = $slip_path;
						}

						// Create the new file, merged pdf
						$new_file      = str_replace('.pdf', '_2.pdf', $invoice_path);
						mergePdfFiles( $pdfs_array, $new_file );

						// Change over to our new file
						$file_url = $new_file;
						$files[]  = $file_url;
					}
/** /
					if ( $invoice_path && function_exists('mergePDFFiles') )
					{
						$margin_left   = 6.35;
						$margin_right  = 6.35;
						$margin_top    = 6.35;
						$margin_bottom = 6.35;
						$mpdf          = new mPDF('utf-8', 'Letter', '', '', $margin_left, $margin_right, $margin_top, $margin_bottom);
						$mpdf->SetImportUse();
echo ' 2 $invoice_path = '. $invoice_path ."<br>\n";
						// Add the invoice to new pdf
						$pagecount     = $mpdf->SetSourceFile( $invoice_path );
						$tplId = $mpdf->ImportPage(1);
						$mpdf->UseTemplate($tplId);
#						$mpdf->WriteHTML();

						// Add the packaging slip
						$slip_id       = get_metadata( 'post', $post->ID, 'file2', true );
						$slip_path     = get_attached_file( $slip_id );
echo ' 1 $slip_path = '. $slip_path ."<br>\n";
						if ( $slip_path )
						{
							$mpdf->AddPage();
echo ' test ' . "<br />\n";
							$pagecount     = $mpdf->SetSourceFile( $slip_path );
echo ' $pagecount = '. $pagecount ."<br>\n";
							$tplId = $mpdf->ImportPage(1);
echo ' $tplId = '. $tplId ."<br>\n";
							$mpdf->UseTemplate($tplId);
#							$mpdf->WriteHTML();
						}
echo ' 2 $slip_path = '. $slip_path ."<br>\n";

						$new_file      = str_replace('.pdf', '_2.pdf', $invoice_path);
						$mpdf->Output($new_file, '', 'F');

						$file_url = $new_file;
						$files[]  = $file_url;
					}
/**/
				}
			}

			// Build the new rows
			ob_start();
			$table_class = new emanager_table();
			$table_settings['page'] = $page;
			$table_class->table($posts->posts, $table_settings);
			$rows = ob_get_clean();
			fwrite($handle, $rows);
			fclose($handle);

			// Update progress in the user's meta
			update_metadata( 'user', $current_user->ID, $user_meta_key, array(
				'filename' => $file_name,
				'files'    => $files,
				'page'     => $page+1,
			) );

			// Return this segment
			echo json_encode( array(
				'count'    => $posts->post_count,
				'total'    => $posts->found_posts,
				'pages'    => $posts->max_num_pages,
				'status'   => 1,
				'filename' => $file_name,
				'page'     => $page,
			) );
			die;
		}
	}
}

$$classname = new $classname;

endif;