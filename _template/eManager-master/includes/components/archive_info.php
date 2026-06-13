<?php
/**
 * Title for Archives, switches for PCO vs NOC
 */
function eman_archive_info( $return='single_title' )
{
	static $cpt, $is_pco, $post_type, $post_type_object, $post_title_singular, $post_title_plural;

	if ( ! $post_type ) {        $post_type           = get_query_var('post_type'); }
	if ( ! $post_type_object ) { $post_type_object    = get_post_type_object( $post_type ); }
	if ( ! $cpt ) {              $cpt                 = eman_post_types($post_type); }
	if ( ! $is_pco ) {           $is_pco              = ('em_noc' == $post_type && (get_query_var('pco') || get_query_var('add')) ? true : false); }
	if ( is_object($post_type_object) && ( ! $post_title_singular || ! $post_title_plural) ) {
		$post_title_singular = $post_type_object->labels->singular_name;
		$post_title_plural   = $post_type_object->labels->name;
		if ( $is_pco ) {
			$post_title_singular = "PCO";
			$post_title_plural   = "PCOs";
		}
	}

	if ( 'plural_title' == $return ) {
		return $post_title_plural;
	} elseif ( 'post_type' == $return ) {
		return $post_type;
	} elseif ( 'post_type_object' == $return ) {
		return $post_type_object;
	} elseif ( 'cpt' == $return ) {
		return $cpt;
	} elseif ( 'is_pco' == $return ) {
		return $is_pco;
	} else {
		return $post_title_singular;
	}
}