<?php 

do_action( 'eman_submenu/add', array(
	array(
		'text' => 'Inbox',
		'url'  => get_permalink(),
	),
	array(
		'text' => 'Archive',
		'url'  => add_query_arg( 'filter', 'archive', get_permalink() ),
	),
) );

get_header(); ?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" class="m-9of12" role="main">

			<h2>Inbox</h2>

			<div id="inbox" class="tabbable fixed-height tb-tabs-framed clearfix" style="width:100%;">
				<ul class="nav nav-tabs clearfix">
					<?php /** / ?><li>
						<a href="#tabs_sent-tab_1" data-toggle="tab" title="Sent messages">Sent</a>
					</li><?php /**/ ?>
					<li class="active">
						<a href="#tabs_received-tab_2" data-toggle="tab" title="Received messages">Received</a>
					</li>
				</ul>
				<div class="tab-content clearfix">
					<div id="tabs_sent-tab_1" class="tab-pane fade in clearfix">
						<div class="sewn_messenger_inbox">
						<?php
							$post_id  = 'user_' . get_current_user_id();
							$messages = apply_filters( 'sewn/messenger/get_messages', array('post_id' => $post_id, 'filter' => 'sent') );
							do_action( 	'sewn/messenger/message_list', $messages, 'user' );
						?>
						</div>
					</div>
					<div id="tabs_received-tab_2" class="tab-pane active fade in clearfix">
						<div id="em_inbox">
							<?php do_action( 'sewn/messenger/inbox', array() ); ?>
						</div>
					</div>
				</div>
			</div>

		</div>

		<div id="sidebar-inbox" class="m-3of12" role="complementary">

			<a data-toggle="collapse" data-parent="#sidebar-inbox" href="#filter_form">+ Add new filter</a>

			<div id="filter_form" class="panel-collapse collapse">
				<?php do_action( 'sewn/messenger/filters/add_new' ); ?>
			</div>

			<h4>Filters</h4>
			<?php do_action( 'sewn/messenger/filters/list' ); ?>
			<hr>
			<?php do_action( 'sewn/messenger/view_archive_button' ); ?>

		</div>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer();