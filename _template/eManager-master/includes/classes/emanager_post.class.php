<?php

	/**
	 * Use to reset the member slug, they were really weird for some reason
	 * /
	if ( is_admin() ) :
		$args = array(
			'post_type'      => 'em_companies',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
		);
		$items = get_posts($args);
		if ( is_array($items) )
		{
			foreach( $items as $item )
			{
				#echo $item->post_name . ', ';
				$post_update = array(
					'ID' => $item->ID,
					'post_name' => sanitize_title($item->post_title)
				);
				wp_update_post($post_update);
			}
		}
	endif;
	/**/

if ( ! class_exists('emanager_post') ) :

class emanager_post
{
	/**
	 * Get the current status of a post, based on the em_status taxonomy
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function status( $post, $key=false )
	{
		if ( is_numeric($post) ) {
			$post = get_post($post);
		}
		if ( ! is_object($post) ) {
			return false;
		}

		$taxonomy    = 'em_status';#( 'em_issue' == $post->post_type ) ? 'em_status_issue' : 'em_status';
		$terms       = wp_get_post_terms( $post->ID, $taxonomy );
		$status_slug = ( empty($terms[0]) ) ? "draft" : $terms[0]->slug;

		$status      = ( empty($terms[0]) ) ? "Draft" : $terms[0]->name;

		// Filter the status name
		if ( 'em_invoice' == $post->post_type )
		{
			$status = str_replace(array('Manager','Superintendent','Submitted','executed','Executed'), array('Accounting','Project Manager','Submitted for Payment','Paid','Paid'), $status);
		}

		// if just the simple status is requested, return it now and be done.
		// Part of BIC transition
		if ( 'simple' == $key ) {
			return $status;
		}

		// if just the key/slug is requested, return it now and be done.
		if ( $key ) {
			return $status_slug;
		}

		/**
		 * Add reviewer
		 */
		if ( 'em_noc' == $post->post_type )
		{
			if ( 'manager' == $status_slug )
			{
				$turner_responsible = eman_get_field( 'turner_responsible', $post->ID );
				$status .= ': ' . $turner_responsible['display_name'];
			}
			elseif ( 'ready' == $status_slug )
			{
				$latest_review = emanager_post::latest_review($post->ID);

				$send_to       = eman_get_field( 'send_to', $latest_review->ID );
				$display_name  = 'N/A';
				if ( is_array($send_to) )
				{
					$display_name = $send_to['display_name'];
				}
				elseif ( ! empty($latest_review->send_to) )
				{
					$user = get_user_by('id', $latest_review->send_to);
					if ( ! empty($user->display_name) ) $display_name = $user->display_name;
				}

				$status       .= ': ' . $display_name;
			}
			elseif ( 'submitted' == $status_slug )
			{
				$latest_review = emanager_post::latest_review($post->ID);
				$terms         = wp_get_post_terms( $latest_review->ID, 'em_status' );
				if ( ! empty($terms[0]->slug) && 'recommend' == $terms[0]->slug )
					$status    = $terms[0]->name;
			}
		}
		elseif ( 'em_tickets' == $post->post_type )
		{
			if ( 'superintendent' == $status_slug )
			{
				$turner_responsible = eman_get_field( 'turner_responsible', $post->ID );
				$status .= ': ' . $turner_responsible['display_name'];
			}
			elseif ( 'manager' == $status_slug)
			{
				$latest_review = emanager_post::latest_review($post->ID);
				$send_to = eman_get_field( 'send_to', $latest_review->ID );
				$status .= ': ' . $send_to['display_name'];
			}
		}
		elseif ( 'revise' == $status_slug )
		{
			$status .= ': ' . get_the_author_meta( 'display_name', $post->post_author );
		}

		return $status;
	}

	/**
	 * Is user the author of the post
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function is_author( $post, $user_id=false )
	{
		if ( is_numeric($post) ) $post = get_post($post);
		if ( ! is_object($post) ) return false;
		if ( ! $user_id ) $user_id = get_current_user_id();

		return ( $post->post_author == $user_id ) ? true : false;
	}

	/**
	 * Does current user share same company as post author?
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function same_company_as_author( $post )
	{
		if ( is_numeric($post) ) $post = get_post($post);
		if ( ! is_object($post) ) return false;
		if ( ! $user_id ) $user_id = get_current_user_id();

		$author_company  = get_user_meta($post->post_author, 'company', true);
		if ( ! is_array($author_company) ) $author_company = array($author_company);

		$current_company = get_user_meta($user_id, 'company', true);
		if ( ! is_array($current_company) ) $current_company = array($current_company);

		foreach ( $author_company as $company_id )
		{
			if ( in_array($company_id, $current_company) ) return true;
		}
		return false;
	}

	/**
	 * Does current user share same company as post?
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function same_company_as_post( $post, $user_id=false )
	{
		if ( is_numeric($post) ) $post = get_post($post);
		if ( ! is_object($post) ) return false;
		if ( ! $user_id ) $user_id = get_current_user_id();

		$post_company = get_post_meta($post->ID, 'company', true);
		if ( ! $post_company ) {
			$post_company = get_user_meta($post->post_author, 'company', true);
		}
		if ( ! is_array($post_company) ) { $post_company = array($post_company); }

		$current_company = get_user_meta($user_id, 'company', true);
		if ( ! is_array($current_company) ) { $current_company = array($current_company); }

		foreach ( $post_company as $company_id ) {
			if ( in_array($company_id, $current_company) ) return true;
		}
		return false;
	}

	/**
	 * Is the post a setting (employee, material, etc)
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function is_settings( $post )
	{
		if ( is_numeric($post) ) $post = get_post($post);

		// Test a post_type generally
		if ( is_string($post) && get_post_type_object($post) ) {
			$post_type = $post;

		// Test a post specifically
		} elseif ( is_object($post) ) {
			$post_type = $post->post_type;

		} else {
			return false;
		}

		$cpt = ( $settings = eman_post_types($post_type) ) ? $settings : array();
		if ( ! empty($cpt['type']) && 'settings' == $cpt['type'] ) return true;

		return false;
	}

	/**
	 * Get the last review of the post
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public static function latest_review( $post )
	{
		if ( is_object($post) ) $post_id = $post->ID;
		elseif ( is_numeric($post) ) $post_id = $post;
		else return false;

		$terms = get_terms( 'em_status', array('fields' => 'ids') );

		$reviews = new WP_Query( array(
			'post_type' => 'em_reviews',
			#'fields' => 'id=>parent',
			'posts_per_page' => 1,
			'order'     => 'DESC',
			'orderby'   => 'date',
			'tax_query'     => array(
				array(
					'taxonomy' => 'em_status',
					'field'    => 'id',
					'terms'    => $terms,
					'compare'  => 'NOT IN',
				)
			),
			'meta_query'    => array(
				array(
					'key'      => 'reviewed_id',
					'value'    => $post_id,
					'compare'  => '=',
				)
			),
		) );

		if ( ! empty($reviews->post) ) { return $reviews->post; }
	}

	/**
	 * Get the company of the post
	 *
	 * @author  Jake Snyder
	 * @return	string Company title
	 */
	public static function company( $post, $id=false )
	{
		if ( is_numeric($post) ) $post = get_post($post);
		if ( ! is_object($post) ) return false;

		$company = '';
		if ( $post->company ) {
			$company = $post->company;
		} elseif ( $company_metas = get_post_meta($post->ID, 'company') ) {
			foreach ( $company_metas as $company_meta ) {
				if ( $company_meta ) {
					$company = $company_meta;
				}
			}
		} elseif ( $company_id = get_post_meta($post->ID, 'company_id', true) ) {
			$company = $company_id;
		}

		if ( $id ) return $company;

		if ( $company ) return get_the_title( $company );

		return false;
	}

	/**
	 * Get user's company title
	 *
	 * @author  Jake Snyder
	 * @return	string Company title
	 */
	public static function user_company( $user_id=false )
	{
		if ( ! $user_id ) $user_id = get_current_user_id();

		$output = "";#Undefined";
		/** /if ( eman_check_role('turner', $user_id)  )
		{
			$output = "Turner";
		}
		elseif ( eman_check_role('owner', $user_id) )
		{
			$output = "Owner";
		}
		else/**/
		if ( $company_id = emanager_post::user_company_id( $user_id ) )
		{
			$output = get_the_title( $company_id );
		}

		return $output;
	}

	/**
	 * Get user's company id
	 *
	 * @author  Jake Snyder
	 * @return	int Company id
	 */
	public static function user_company_id( $user_id=false )
	{
		if ( ! $user_id ) $user_id = get_current_user_id();

		$output = 0;
		if ( $company = get_user_meta($user_id, 'company', true) )
		{
			$output = ( is_array($company) ) ? $company[0] : $company;
		}

		return ( 'null' == $output ) ? false : $output;
	}

	public static function display_backup( $attachment )
	{
		$backup   = wp_check_filetype($attachment);
		$fileinfo = pathinfo($attachment);
		$filename = $fileinfo['filename'];

		// Image
		if ( 'jpg' === $backup['ext'] || 'png' === $backup['ext'] || 'gif' === $backup['ext'] )
		{
			$content = '<center>
		        <a href="' . $attachment . '" class="btn btn-primary">Download</a><br />
		    	<a href="' . $attachment . '" title="Backup"><img src="' . $attachment . '" style="height:auto;width:100%;" /></a>
		    </center>';
			echo eman_modal( array(
				'text'        => $filename,
				'color'       => 'primary',
				'size'        => 'lg',
				'icon_before' => 'file-image-o',
				'animage'     => 'true',
			), $content );
		    /** /echo do_shortcode('[popup text="' . $filename . '" title="' . $filename . '" color="primary" size="lg" icon_before="file-image-o" animate="true"]<center>
		        <a href="' . $attachment . '" class="btn btn-primary">Download</a><br />
		    	<a href=' . $attachment . ' title="Backup"><img src=' . $attachment . ' style="height:auto;width:100%;" /></a>
		    </center>[/popup]');/**/
		}
		// PDF
		elseif ( 'pdf' === $backup['ext'] )
		{
			$advanced_pdf = eman_get_field('pdf_advanced', 'option');
			if ( 'Yes' == $advanced_pdf )
			{
				$content = do_shortcode('[bpdf url=' . $attachment . ']');
				echo eman_modal( array(
					'text'        => $filename,
					'color'       => 'danger',
					'footer'      => 'false',
					'header'      => 'PDF Viewer',
					'size'        => 'lg',
					'icon_before' => 'file-pdf-o',
					'animage'     => 'true',
				), $content );
				#echo do_shortcode('[popup text="' . $filename . '" title="' . $filename . '" color="danger" size="lg" icon_before="file-pdf-o" animate="true"][/popup]');
			}
			else
			{
				echo eman_button( array(
					'text'        => $filename,
					'icon_before' => 'paperclip',
					'url'         => $attachment,
					'color'       => 'success',
				) );
				#echo '<a href="' . $attachment . '" class="btn btn-success"><i class="fa fa-paperclip"></i> ' . $filename . '</a>';
			}
		}
		// Everything else
		elseif ( file_exists($attachment) )
		{
			echo eman_button( array(
				'text'        => $filename,
				'icon_before' => 'paperclip',
				'url'         => $attachment,
				'color'       => 'success',
			) );
			#echo '<a href="' . $attachment . '" class="btn btn-success"><i class="fa fa-paperclip"></i> ' . $filename . '</a>';
		}
		else
		{
			echo '';
		}
	}

	public static function dateDiff( $start, $end )
	{
		$start_ts = strtotime($start);
		$end_ts   = strtotime($end);
		$diff     = $end_ts - $start_ts;
		return round($diff / 86400);
	}
}

endif;