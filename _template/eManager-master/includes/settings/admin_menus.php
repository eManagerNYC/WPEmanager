<?php

add_action( 'admin_menu', 'em_changes_admin' );
function em_changes_admin()
{
	$page_title = 'Change Management';
	$menu_title = 'Change Management';
	$capability = 'edit_others_posts';
	$menu_slug  = 'emanager_change';
	$function   = false;
	$icon_url   = false;
	$position   = 28;
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}

add_action( 'admin_menu', 'em_field_admin' );
function em_field_admin()
{
	$page_title = 'Field Work';
	$menu_title = 'Field Work';
	$capability = 'edit_others_posts';
	$menu_slug  = 'emanager_field';
	$function   = false;
	$icon_url   = false;
	$position   = 29;
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}

add_action( 'admin_menu', 'em_settings_admin' );
function em_settings_admin()
{
	$page_title = 'eManager Settings';
	$menu_title = 'eManager Settings';
	$capability = 'edit_others_posts';
	$menu_slug  = 'emanager_settings';
	$function   = false;
	$icon_url   = false;
	$position   = 27;
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}