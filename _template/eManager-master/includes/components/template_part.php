<?php
/**
 * Load views from the post_type if available
 */
function eman_template_part( $slug, $name=null )
{
	$post_type = str_replace('em_', '', get_post_type());
	$file_path = "includes/post_types/$post_type/views/$slug";
	$templates = array();
	$name      = (string) $name;
	if ( $name ) {
		$templates[] = "$file_path-$name.php";
	}
	$templates[] = "$file_path.php";

	return locate_template($templates, true, false);
}