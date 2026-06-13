<!doctype html>
<html dir="ltr" lang="en-US">

<head>
	<title><?php wp_title(''); ?></title>
	<?php do_action('sewn/seo/meta/description'); ?>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="HandheldFriendly" content="True" />
	<meta name="MobileOptimized" content="320" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="revisit-after" content="15 days" />
	<meta name="rating" content="general" />
	<meta name="distribution" content="global" />
	<?php do_action('sewn/seo/meta/classification'); ?>
	<meta name="author" content="<?php bloginfo('name'); ?>" />
	<meta name="creator" content="<?php bloginfo('name'); ?>" />
	<meta name="publisher" content="<?php bloginfo('name'); ?>" />
	<?php do_action('sewn/seo/meta/site_name'); ?>
	<?php do_action('sewn/seo/meta/og:title'); ?>
	<?php do_action('sewn/seo/meta/og:image'); ?>
	<?php do_action('sewn/seo/meta/og:type'); ?>

	<link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/apple-touch-icon.png">
	<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon.png">
	<!--[if IE]><link rel="shortcut icon" href="<?php echo bloginfo('template_directory'); ?>/assets/img/favicon.ico"><![endif]-->
	<meta name="msapplication-TileColor" content="#e7c12a">
	<meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/assets/img/mstile-310x310.png">

	<?php wp_head(); ?>
</head>
<?php
$body_class = '';
if ( false !== strpos($_SERVER['REQUEST_URI'], '/profile/') ) {
	$body_class = 'profile';
}
?>
<body <?php body_class($body_class); ?>>

	<div id="container">

		<header id="header" role="banner">  

			<div id="inner-header" class="wrap cf">

				<h1 id="logo" class="m-6of12">
					<a href="<?php echo home_url(); ?>" title="Home">
						<?php /** / echo get_option('blogname'); /**/ ?>
						<?php /**/ ?><img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo.png" alt="<?php echo get_option('blogname'); ?>" /><?php /**/ ?>
					</a>
				</h1>

				<?php if ( is_user_logged_in() ) :
					$current_user = wp_get_current_user(); ?>
					<div class="header-links m-6of12 lastcol">
						<?php
						echo eman_button( array(
							'text'        => '',
							'title'       => 'Send a message',
							'url'         => home_url('/message-compose/'),
							'color'       => 'blue',
							'icon_before' => 'envelope',
						) );
						echo eman_button( array(
							'text'        => '',
							'title'       => 'Print this page',
							'url'         => 'javascript:window.print()',
							'color'       => 'blue',
							'icon_before' => 'print',
						) );
						?>
						<div class="user-nav btn-group pull-right">
							<a class="btn btn-warning" data-toggle="dropdown" href="#">
								<span class="fa fa-user" aria-hidden="true"></span>
								<?php echo eman_users_name($current_user); ?>
							</a>
							<a class="btn btn-warning dropdown-toggle" data-toggle="dropdown" href="#">
								<span class="fa fa-caret-down" aria-hidden="true"></span>
							</a>
							<ul class="dropdown-menu">
								<?php if ( current_user_can('manage_options') ) : ?>
									<li><a href="<?php echo admin_url(); ?>" title="Access admin area"><span class="fa fa-gears" aria-hidden="true"></span> Backend</a></li>
								<?php endif; ?>
								<li><a href="<?php echo home_url('/profile/'); ?>" title="Edit your profile"><span class="fa fa-pencil" aria-hidden="true"></span> Profile</a></li>
								<li><a href="<?php echo wp_logout_url(); ?>"><span class="fa fa-external-link" aria-hidden="true"></span> Logout</a></li>
							</ul>
						</div>
					</div>
					<span id="mobile-nav">
						<span class="fa fa-navicon" aria-hidden="true"></span>
						<span class="screen-reader-text">MENU</span>
					</span>
				<?php endif; ?>

			</div>

		</header>

		<?php if ( is_user_logged_in() ) : ?>
		<div id="mainnav">

			<div id="inner-mainnav" class="wrap cf">

				<div id="side-drawer">
					<nav id="main-nav" role="navigation">
					<?php wp_nav_menu(array(
						'container' => false,						// remove nav container
						'menu' => 'Main Navigation',				// nav name
						'menu_id' => '',						 	// adding custom nav id
						'menu_class' => '',							// adding custom nav class
						'theme_location' => 'main-nav',				// where it's located in the theme
						'before' => '',								// before the menu
						'after' => '',								// after the menu
						'link_before' => '',						// before each link
						'link_after' => '',							// after each link
						'depth' => 2								// limit the depth of the nav
					)); ?>
					</nav>
				</div>

			</div>

		</div>
		<?php endif; ?>

		<?php do_action( 'eman_submenu/show' ); ?>

		<?php do_action( 'sewn/notifications/show' ); ?>
