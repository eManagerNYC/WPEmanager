<div class="dashboard_line dashboard_line_1">
<?php
	eman_dash_tile( "content=$pcorequests&flip=1" );
	eman_dash_tile( "content=$nocs&size=large&flip=1" );
	eman_dash_tile( "content=$directives&flip=1" );
	eman_dash_tile( "content=$tickets&flip=1" );
	eman_dash_tile( "content=$letters&flip=1" );
?>
</div>

<div class="dashboard_line dashboard_line_2">
<?php
	eman_dash_tile( "content=$dcrs&flip=1" );
	eman_dash_tile( "content=$observations&flip=1" );
	eman_dash_tile( "content=$photos&flip=1" );
	eman_dash_tile( array('content' => '<div class="dashboard_weather ' . eman_dash_color() . '"><div>' . $weather . '</div></div>', 'size' => 'large') );
	eman_dash_tile( "content=$settings" );
?>
</div>

<div class="dashboard_line dashboard_line_3">
<?php
	eman_dash_tile( "content=$rfis&flip=1" );
	eman_dash_tile( "content=$inspections&flip=1" );
	eman_dash_tile( "content=$issues&flip=1" );
	if ( class_exists('Eman_Supplier_Invoice') ) {
		eman_dash_tile( "content=$invoices&flip=1" );
	} else {
		eman_dash_tile();
	}

	eman_dash_tile( "content=$actionitems&flip=1" );
	if ( current_user_can('manage_options') ) :
		eman_dash_tile( "content=$meetings&flip=1" );
	else :
		eman_dash_tile();
	endif;
?>
</div>

<?php if ( class_exists('Eman_File_Manager') ) : ?>
<div class="dashboard_line dashboard_line_4 dashboard_line_folders">
<?php
	eman_dash_tile( "content=$doc_project" );
	eman_dash_tile( "content=$doc_turner" );
	eman_dash_tile( "content=$doc_sub" );
	eman_dash_tile( "content=$doc_consultant" );
	eman_dash_tile( "content=$doc_owner" );
	eman_dash_tile( "content=$doc_companies" );
?>
</div>
<?php endif;
