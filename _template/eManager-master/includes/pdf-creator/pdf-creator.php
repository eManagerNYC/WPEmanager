<?php
/*
Plugin Name: PDF Creator
Plugin URI: http://www.Jupitercow.com/
Description: Output PDF files using mPDF.
Version: 9999
Author: jcow
Author URI: http://www.Jupitercow.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

------------------------------------------------------------------------
Copyright 2013 Jupitercow, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

class pdf_creator
{
	/**
	 * The default status for new posts
	 */
	public static $prefix = __CLASS__;

	/**
	 * Current version of plugin
	 */
	public static $version = '0.1';

	public static function init()
	{
		include('libs/mpdf/mpdf.php');
	}

	public static function create_pdf( $content, $filename=false, $output_type='I', $footer=null )
	{
		$margin_left   = apply_filters( self::$prefix . '/margin_left', 6.35 );
		$margin_right  = apply_filters( self::$prefix . '/margin_right', 6.35 );
		$margin_top    = apply_filters( self::$prefix . '/margin_top', 6.35 );
		$margin_bottom = apply_filters( self::$prefix . '/margin_bottom', 6.35 );
		$mpdf = new mPDF('utf-8', 'Letter', '', '', $margin_left, $margin_right, $margin_top, $margin_bottom);
		$mpdf->setDisplayMode('fullpage');
		$mpdf->SetUserRights();
		$mpdf->title2annots = false;

		$user = wp_get_current_user();
		$mpdf->SetAuthor($user->first_name . ' ' . $user->last_name . ' (' . $user->user_login . ')');
		$mpdf->SetCreator('FfAI');

		if ( ! $footer ) {
			$footer = '<div class="pagenumber">{PAGENO} of {nbpg}</div>';
		}
		$mpdf->SetHTMLFooter( $footer );

		// Add the stylesheet
		$style_uri = apply_filters( self::$prefix . '/stylesheet', get_template_directory_uri() . '/style.css' );
		$stylesheet = file_get_contents($style_uri);
		$mpdf->WriteHTML($stylesheet,1);

		$mpdf->WriteHTML($content);

		if ( empty($filename) )
		{
			$filename = sanitize_title( get_option('blogname') );
			if (! empty($GLOBALS['post']->post_name) )
				$filename .= '_' . $GLOBALS['post']->post_name;

			$filename .= '.pdf';
		}

		/**
		 * I = Browser, F = File, D = Force Download
		 */
		$mpdf->Output( $filename, $output_type );
		#$uploads = wp_upload_dir();
		#$mpdf->Output( $uploads['baseurl'] . 'pdfs/' . ffai_application::get_form_post_id() . '.pdf', 'D');
	}
}

pdf_creator::init();