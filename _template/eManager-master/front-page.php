<?php

if ( ! is_user_logged_in() && function_exists('acf_form_head') ) :
	acf_form_head();
endif;

get_header();

if ( is_user_logged_in() ) :

	get_template_part( 'partials/front-page/dashboard' );

else :

	get_template_part( 'partials/front-page/external' );

endif;

get_footer();
