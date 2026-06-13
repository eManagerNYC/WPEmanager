<div class="dashboard_line dashboard_line_1">
<?php
	eman_dash_tile();
	eman_dash_tile( "content=$directives&size=large&flip=1" );
	if ( class_exists('Eman_Supplier_Invoice') ) {
		eman_dash_tile( "content=$invoices&flip=1" );
	} else {
		eman_dash_tile();
	}
	eman_dash_tile( "content=$tickets&size=large&flip=1" );
?>
</div>

<div class="dashboard_line dashboard_line_2">
<?php
	eman_dash_tile( "content=$dcrs&size=large&flip=1" );
	eman_dash_tile( "content=$issues&flip=1" );
	eman_dash_tile( array('content' => '<div class="dashboard_weather ' . eman_dash_color() . '"><div>' . $weather . '</div></div>', 'size' => 'large') );
	eman_dash_tile( "content=$settings" );
?>
</div>

<?php if ( class_exists('Eman_File_Manager') ) : ?>
<div class="dashboard_line dashboard_line_3 dashboard_line_folders">
<?php
	eman_dash_tile( "content=$doc_project" );
	eman_dash_tile( "content=$doc_sub&size=large" );
	eman_dash_tile();
	if ( $user_company_id = emanager_post::user_company_id() ) :
		$content = sprintf($doc_company, basename( get_permalink($user_company_id) ));
		eman_dash_tile( "content=$content&size=large" );
	else :
		eman_dash_tile( "size=large" );
	endif;
?>
</div>
<?php endif;
