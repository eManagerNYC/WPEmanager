<?php

/**
 * Creates a form for custom post type using the settings array
 */
function eman_flowchart( $post_type )
{
	$img_path    = '/assets/img/chart_' . $post_type . '.png';
	if ( file_exists(get_template_directory() . $img_path) )
	{
		$chart_url   = get_template_directory_uri() . $img_path;
		$header  = '<a href=\'' . $chart_url . '\' class=\'fullscreen btn btn-primary btn-sm\' data-fullscreen-target=\'modal-flowchart-image\' target=\'_blank\'><span class=\'fa fa-arrows-alt\' aria-hidden=\'true\'></span> Full Screen</a>';
		$content = '<a href="' . $chart_url . '" title="Flow chart" target="_blank" style="display:block;" text-align:center;"><img id="modal-flowchart-image" src="' . $chart_url . '" alt="Flow chart" /></a>';

		echo do_shortcode('[popup text="" color="default" class="modal-flowchart" icon_before="sitemap" size="lg" header="' . $header . '" animate="true"]' . $content . '[/popup]');
	}
}