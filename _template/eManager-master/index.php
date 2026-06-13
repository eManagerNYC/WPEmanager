<?php

get_header();

if ( is_user_logged_in() ) :

	get_template_part( 'partials/index/dashboard' );

else :

	get_template_part( 'partials/index/external' );

endif;

get_footer();