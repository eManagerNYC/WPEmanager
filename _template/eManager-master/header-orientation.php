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

			</div>

		</header>

		<?php do_action( 'sewn/notifications/show' ); ?>
