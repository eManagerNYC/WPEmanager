<div class="dashboard_line dashboard_line_1">
<?php
	eman_dash_tile( "content=$rfis&flip=1" );
	eman_dash_tile( "content=$nocs&size=large&flip=1" );
	eman_dash_tile( "content=$letters&size=large&flip=1" );
	eman_dash_tile();
?>
</div>

<div class="dashboard_line dashboard_line_2">
<?php
	eman_dash_tile( array( 'size' => 'large') );
	eman_dash_tile( "content=$issues&flip=1" );
	eman_dash_tile( array('content' => '<div class="dashboard_weather ' . eman_dash_color() . '"><div>' . $weather . '</div></div>', 'size' => 'large') );
	eman_dash_tile( "content=$settings" );
?>
</div>

<?php if ( class_exists('Eman_File_Manager') ) : ?>
<div class="dashboard_line dashboard_line_3 dashboard_line_folders">
<?php
	eman_dash_tile( "content=$doc_project" );

	if ( current_user_can('consultant') ) :
		eman_dash_tile( "content=$doc_consultant&size=large" );
	else :
		eman_dash_tile( "content=$doc_owner&size=large" );
	endif;
	if ( current_user_can('consultant') ) :
		eman_dash_tile( "size=large" );
	else :
		eman_dash_tile( "content=$doc_consultant&size=large" );
	endif;
	if ( $user_company_id = emanager_post::user_company_id() ) :
		$content = sprintf($doc_company, basename( get_permalink($user_company_id) ));
		eman_dash_tile( "content=$content" );
	else :
		eman_dash_tile();
	endif;
?>
</div>
<?php endif;
