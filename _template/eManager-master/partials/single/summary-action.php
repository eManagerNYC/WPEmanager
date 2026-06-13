<?php

/**
 * Get status
 */
$status = emanager_post::status($post, 'slug');


/**
 * Current post type settings
 */
$cpt = eman_post_types(get_post_type());


if ( ! in_array($status, array('draft','revise')) && in_array(get_post_type(), array('em_letter')) ) : ?>
<div class="comments clearfix">
	<?php comments_template( '', true ); ?>
</div>
<?php endif;


/**
 * If the form is in draft or revision, it will need to be confirmed, and is editabled
 *
 * If it is a setting, it is always editable by author and author company.
 */
if ( eman_can_edit($post) )
{
?>
	<div class="edit-post">
		<?php if ( 'settings' != $cpt['type'] && in_array($status, array('draft','revise')) ) : ?>
			<a href="<?php echo add_query_arg('action', 'confirm', get_permalink()); ?>" title="Confirm item" class="btn btn-primary">Confirm</a>
		<?php endif; ?>
		<a href="<?php echo add_query_arg('edit', 'true', get_permalink()); ?>" title="Edit item" class="btn btn-default">Edit</a>
	</div>
<?php
}


/**
 * Basic form to review post
 */
$group_ids    = array('acf_reviews-signature-and-send-to');
$submit_title = "Process <small>(move to next step/status)</small>";
$button_value = "Submit";
if ( 'em_noc' == get_post_type() )
{
	$submit_title = "Approve NOC Submission";
	$button_value = 'Approve';

	if ( 'ready' == $status ) {
		$group_ids    = array('acf_noc-submit-sendto','acf_noc-numbers','acf_reviews-signature');
		$button_value = "Submit NOC";
	} elseif ( 'manager' == $status ) {
		$group_ids    = array('acf_noc-signature-and-send-to');
	} elseif ( in_array($status, array('submitted','recommend')) ) {
		$group_ids    = array('acf_reviews-for-owner','acf_reviews-signature');
		$button_value = "Recommend";
		$submit_title  = "NOC Direction &amp; Execution";
	} elseif ( 'submit' == $status ) {
		$group_ids    = array('acf_noc-numbers','acf_reviews-signature', 'bic_noc_submit');
		$button_value = "Ready to Submit";
	}
}
elseif ( 'em_tickets' == get_post_type() )// && 'manager' == $status
{
	if ( 'manager' == $status ) {
		$group_ids   = array('acf_reviews-signature');
	} else {
		$group_ids   = array('acf_reviews-signature-and-send-to');
	}
	$submit_title  = "Process";
}
elseif ( 'em_dcr' == get_post_type() )
{
	$group_ids    = array('acf_reviews-signature');
	$submit_title = "Approve DCR";
	$button_value = 'Approve';
}
elseif ( 'em_invoice' == get_post_type() )
{
	$group_ids    = array('acf_reviews-invoice');
	$submit_title = "Submit";
	$button_value = 'Submit';
}
elseif ( 'em_letter' == get_post_type() )
{
	$group_ids    = array('acf_noc-submit-sendto');
	$submit_title = "Submit";
	$button_value = 'Submit';
}
ob_start();
if ( $group_ids ) : ?>
	<form id="post" class="acf-form" action="" method="post">
<?php
		/**
		 * change post type
		 */
		if ( function_exists('acf_form') ) {
			acf_form( array(
				'post_id'      => 'new_post',
				'field_groups' => $group_ids,
				'return'       => add_query_arg( 'review', 'true', get_permalink() ),
				'submit_value' => 'Update',
				'form'         => false
			) );
		}
?>
		<div class="submit-area">
			<input class="hidden" type="hidden" name="step" value="review" />
			<input class="hidden" type="hidden" name="post_type" value="em_reviews" />
			<input type="submit" name="submit" id="submit" value="<?php echo $button_value; ?>" />
			<?php /** /if ( eman_can_edit($post) ) : ?><a class="btn btn-default" href="?edit=true">Edit</a><?php endif;/**/ ?>
			<?php if ( 'em_noc' == get_post_type() && in_array($status, array('submitted','executed','recommend')) ) :
				if ( current_user_can('owner') ) : ?>
					<input type="submit" name="submit" value="Execute" />
				<?php endif;
			elseif ( 'em_invoice' != get_post_type() ) : ?>
				<input type="submit" name="submit" value="Revise" />
				<input type="submit" name="submit" value="Void" />
			<?php endif; ?>
		</div>
		<?php /** / ?>
		<div class="update-area">
			<a class="revise-btn btn" href="<?php echo add_query_arg(array('action'=>'revise','status'=>'superintendent'), get_permalink()); ?>" title="Send back for revision">Revise</a>
			<a class="void-btn btn" href="<?php echo add_query_arg(array('action'=>'void','status'=>'superintendent'), get_permalink()); ?>" title="Void this ticket">Void</a>
		</div>
		<?php /**/ ?>
	</form>
<?php endif;
$submit_form = ob_get_clean();

/**
 * Newer form to change Ball in Court
 */
$group_ids    = array('bic_turner');
$bic_title    = "Change Ball-in-Court <small>(no status impact)</small>";
$button_value = "Update BIC";
if ( 'em_noc' == get_post_type() )
{
	if ( 'manager' == $status ) {
		$group_ids = array('bic_noc_manager');
	} elseif ( 'ready' == $status ) {
		$group_ids = array('bic_noc_ready');
	} elseif ( 'submit' == $status || in_array($status, array('submitted','recommend')) ) {
		$group_ids = array('bic_noc_submit');
	}
}
elseif ( 'em_tickets' == get_post_type() )
{
	$group_ids = array('bic_custom_approvers');
}
elseif ( 'em_dcr' == get_post_type() )
{
	$group_ids = array('bic_custom_approvers');
}
elseif ( 'em_issue' == get_post_type() )
{
	$group_ids = array('bic_noc_submit');
}
elseif ( 'em_letter' == get_post_type() )
{
	$group_ids = array('bic_letter_submit');
	$bic_title = "Modify Change";
}
elseif ( 'em_rfi' == get_post_type() )
{
	$group_ids = array('bic_noc_submit');
}
ob_start();
if ( $group_ids ) : ?>
	<form id="post" class="acf-form" action="" method="post">
<?php
		/**
		 * change post type
		 */
		if ( function_exists('acf_form') ) {
			acf_form( array(
				'post_id'      => 'new_post',
				'field_groups' => $group_ids,
				'return'       => add_query_arg( 'review', 'true', get_permalink() ),
				'submit_value' => 'Update',
				'form'         => false
			) );
		}
?>
		<div class="submit-area">
			<input class="hidden" type="hidden" name="step" value="bic" />
			<input class="hidden" type="hidden" name="post_type" value="em_reviews" />
			<input type="submit" name="submit" id="submit" value="<?php echo $button_value; ?>" />
		</div>
		<?php /** / ?>
		<div class="update-area">
			<a class="revise-btn btn" href="<?php echo add_query_arg(array('action'=>'revise','status'=>'superintendent'), get_permalink()); ?>" title="Send back for revision">Revise</a>
			<a class="void-btn btn" href="<?php echo add_query_arg(array('action'=>'void','status'=>'superintendent'), get_permalink()); ?>" title="Void this ticket">Void</a>
		</div>
		<?php /**/ ?>
	</form>
<?php endif;
$bic_form = ob_get_clean();


/**
 * Newer form to change Issue Status
 */
$group_ids          = array('acf_issue-status');
$issue_status_title = "Change Status";
$button_value       = "Update Status";
if ( 'em_noc' == get_post_type() )
{
	if ( 'manager' == $status ) {
		$group_ids = array('bic_noc_manager');
	} elseif ( 'ready' == $status ) {
		$group_ids = array('bic_noc_ready');
	} elseif ( 'submit' == $status || in_array($status, array('submitted','recommend')) ) {
		$group_ids = array('bic_noc_submit');
	}
}
elseif ( 'em_tickets' == get_post_type() )
{
	$group_ids = array('bic_custom_approvers');
}
elseif ( 'em_dcr' == get_post_type() )
{
	$group_ids = array('bic_custom_approvers');
}
elseif ( 'em_letter' == get_post_type() )
{
	$group_ids = array('bic_letter_submit');
}
elseif ( 'em_rfi' == get_post_type() )
{
	$group_ids = array('bic_custom_approvers');
}
ob_start();
if ( $group_ids ) : ?>
	<form id="post" class="acf-form" action="" method="post">
<?php
		if ( function_exists('acf_form') ) {
			acf_form( array(
				'post_id'      => 'new_post',
				'field_groups' => $group_ids,
				'return'       => add_query_arg('status', 'true', get_permalink()),
				'form'         => false
			) );
		}
?>
		<div class="submit-area">
			<input class="hidden" type="hidden" name="step" value="status" />
			<input class="hidden" type="hidden" name="post_type" value="em_reviews" />
			<input type="submit" name="submit" id="submit" value="<?php echo $button_value; ?>" />
		</div>
	</form>
<?php endif;
$issue_status_form = ob_get_clean();




/**
 * This extra step will happen here for now, but might be better in "can_review" eventually.
 *
 * If the current step requires a special type of reviewer, only allow that type of reviewer to review the post.
 * Even if the current user is BIC, they still have to be in the special reviewer group.
 * And a special reviewer still has to be BIC before they can review.
 */
$approvers = false;
$approver_array = array();

if ( 'em_dcr' == get_post_type() )
{
	if ( 'superintendent' == $status ) {
		$approver_array = eman_get_field('dcr_reviewers', 'option');
	}
}
elseif ( 'em_noc' == get_post_type() )
{
	if ( 'manager' == $status ) {
		$approver_array = eman_get_field('pco_approvers', 'option');
	} elseif ( 'ready' == $status ) {
		$approver_array = eman_get_field('noc_gatekeeper', 'option');
	}
}
elseif ( 'em_tickets' == get_post_type() )
{
	if ( 'manager' == $status ) {
		$approver_array = eman_get_field('ticket_approvers', 'option');
	}
}
// Issues have different criteria
elseif ( 'em_invoice' == get_post_type() )
{
	
}
elseif ( 'em_letter' == get_post_type() )
{
	//bic_letter_submit
}

if ( $approver_array )
{
	foreach ( $approver_array as $approver ) {
		$approvers[] = $approver['ID'];
	}
}

if ( 'executed' != $status && 'closed' != $status ) : ?>
<div class="review-update">
	<?php
	if ( 'em_invoice' == get_post_type() ) :
		// Approved and user is a manager/accountant
		$status = emanager_post::status($post, 'slug');
		$post_approval = array('approved','submitted','executed');
		if ( in_array($status, $post_approval) ) :
			$job_id         = get_metadata('post', get_the_ID(), 'job_id', true);
			$superintendent = get_option('options_invoice_reviewer_' . $job_id);
			$accountant     = get_option('options_invoice_accounting_reviewer');
			$current_user_id = get_current_user_id();
			if ( $superintendent == $current_user_id || $accountant == $current_user_id ) :
				echo $submit_form;
			endif;
		endif;
	endif;

	$issue_format_types = ['em_issue'];

	$letter_format_types = ['em_letter'];

	// If reviewer, show everything
	if ( in_array($post->post_type, $letter_format_types) ) :
		if ( eman_can_review($post) ) :
?>
		<div class="review-bic-only">
			<h2><?php echo $bic_title; ?></h2>
			<?php echo $issue_status_form; ?>
		</div>
<?php
		endif;
	/** /elseif ( in_array($post->post_type, $letter_format_types) && empty($_REQUEST['edit']) && 'draft' != $status ) :
?>
		<div class="review-bic-only">
			<h2><?php echo $bic_title; ?></h2>
			<?php echo $issue_status_form; ?>
		</div>
<?php/**/
	// If reviewer, show everything
	elseif ( ! in_array($post->post_type, $issue_format_types) && eman_can_review($post) && ( ! $approvers || (is_array($approvers) && in_array(get_current_user_id(), $approvers)) ) ) :
		echo do_shortcode('[tabs style="framed" tab_1="' . $submit_title . '" tab_2="' . $bic_title . '"]
			[tab_1][raw]' . $submit_form . '[/raw][/tab_1]
			[tab_2][raw]' . $bic_form . '[/raw][/tab_2]
		[/tabs]');

	elseif ( in_array($post->post_type, $issue_format_types) && empty($_REQUEST['edit']) && 'draft' != $status ) :
		echo do_shortcode('[tabs style="framed" tab_1="' . $issue_status_title . '" tab_2="' . $bic_title . '"]
			[tab_1][raw]' . $issue_status_form . '[/raw][/tab_1]
			[tab_2][raw]' . $bic_form . '[/raw][/tab_2]
		[/tabs]');

	// If Turner, always show BIC
	elseif (
		( $bic_form && eman_check_role('turner') && ! in_array($status, array('draft','revise','approve','approved','executed','void')) ) ||
		( $bic_form && 'em_noc' == get_post_type() && in_array($status, array('submitted','executed','recommend')) && eman_check_role('owner') )
	) : ?>
		<div class="review-bic-only">
			<h2><?php echo $bic_title; ?></h2>
			<?php echo $bic_form; ?>
		</div>
	<?php endif; ?>
</div>
<?php endif;