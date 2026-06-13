<?php //This part is required for WordPress to recognize it as a page template
/*
Template Name: Superintendent Daily Report
*/
/**
 * Grab current user
 */
$current_user = wp_get_current_user();
/**
 * ACF needs this, form processing
 */
if ( function_exists('acf_form_head') ) { acf_form_head(); }
get_header();
$pagename = 'Superintendent Daily Report';
wp_enqueue_script('date-picker', get_template_directory_uri(). '/assets/js/date-picker.js', array('jquery-ui-datepicker'), '1.0', true);

$y = date('Y');
$m = date('n');
$d = date('d');

?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<?php do_action( 'before_content' ); ?>

		<div id="main" role="main">

						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php if ( 'hide' != get_post_meta( $post->ID, '_tb_title', true ) ) : ?>
							<header class="entry-header">
								<h1 class="entry-title">
									<?php the_title(); ?>
								</h1>
							</header><!-- .entry-header -->
						<?php endif; ?>
						<div class="entry-content">
							<?php 					
							  
							  echo '<form class="form-inline" role="form" action="" method="post">
							                    <div class="form-group">
							                    <label  class="sr-only" for="datepicker">Date</label>
							                    <input type="text" id="datepicker" name="datepicker" placeholder="Date" value="" size="15"/>
							                    </div>
							                    <button type="submit" class="btn btn-default">Submit</button>
							                </form>
							                <hr>';
							   if( isset($_POST['datepicker']) )
							   {
							   	$datep = $_POST['datepicker'];
							   	$y = substr($datep, 0, 4);
								$m   = substr($datep, -4, 2);
								$d   = substr($datep, -2);

								echo '<h2>'.$y.'-'.$m.'-'.$d.'</h2>';

							   } else {
							   	echo '<h2>'.$y.'-'.$m.'-'.$d.'</h2>';
							   }

							   if( isset($_POST['datepicker']) )
							   {
							  	$tomorrow = $_POST['datepicker'] + 1;
							   } else {
							   	$tomorrow = $y.$m.($d + 1);
							   }
							   echo '<form class="form-inline pull-right" role="form" action="" method="post">
									    <input type="hidden" id="datepicker" name="datepicker" value="'.$tomorrow.'" />
									    <button type="submit" class="btn btn-primary">Tomorrow ></button>
									</form>';

							   if( isset($_POST['datepicker']) )
							   {
							  	$yesterday = $_POST['datepicker'] - 1;
							   } else {
							   	$yesterday = $y.$m.($d - 1);
							   }
							   echo '<form class="form-inline" role="form" action="" method="post">
									    <input type="hidden" id="datepicker" name="datepicker" value="'.$yesterday.'" />
									    <button type="submit" class="btn btn-primary">< Yesterday</button>
									</form>';
							  echo '<hr>';

							  if( isset($_POST['datepicker']) )
							  {
							    $date_unix = strtotime($_POST['datepicker']);
							    $args = array(
							      'post_type' => 'em_observation',
							      'order' => 'ASC',
							      'orderby' => 'datetime',
							      'meta_query' => array(
							        array(
							          'key' => 'datetime',
							          'value' => array($date_unix, $date_unix+86399),
							          'compare' => 'BETWEEN',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							  } else {
							    $date = $y.'-'.$m.'-'.$d;
							    $date_unix = strtotime($date);
							    $date_tom = $y.'-'.$m.'-'.(intval($d)+1);
							    $date_tom_unix = strtotime($date_tom)-1;
							    $args = array(
							      'post_type' => 'em_observation',
							      'order' => 'ASC',
							      'orderby' => 'datetime',
							      'meta_query' => array(
							        array(
							          'key' => 'datetime',
							          'value' => array($date_unix, $date_tom_unix),
							          'compare' => 'BETWEEN',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							  }
							  $the_query = new WP_Query( $args );
							  if ($the_query->have_posts()) {
							  	echo '<h3>Superintendent Observations</h3>';
							    echo '<ul class="list-group">';
							    while ($the_query->have_posts()) {
							      $the_query->the_post();

							      $date_pretty = date('h:i a', eman_get_field('datetime'));

							      echo '<li class="list-group-item">';
							      echo '<span class="badge alert-info pull-right">'.get_the_author().'</span>';
							      echo '<h4 class="list-group-item-heading">'.$date_pretty.'</h4><hr>';
							      echo '<p class="list-group-item-text">'.eman_get_field('f_notes').'</p>';
							     
							      $attachments = eman_get_field('attachments');
									if($attachments)
									{
										echo '<ul>';

										foreach($attachments as $attachment)
										{
											echo '<li><div>'.emanager_post::display_backup($attachment['file']).'</div></li>';
										}

										echo '</ul>';
									}

							      echo '</li>';
							    }
							    echo '</ul>';
							  }
							  wp_reset_postdata();


							  if( isset($_POST['datepicker']) )
							  {
							    $date = $_POST['datepicker'];
							    $args = array(
							      'post_type' => 'em_dcr',
							      'order' => 'ASC',
							      'orderby' => 'title',
							      'meta_query' => array(
							        array(
							          'key' => 'work_date',
							          'value' => $date,
							          'compare' => 'LIKE',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							  } else {
							    $date = $y.$m.$d;
							    $args = array(
							      'post_type' => 'em_dcr',
							      'order' => 'ASC',
							      'orderby' => 'title',
							      'meta_query' => array(
							        array(
							          'key' => 'work_date',
							          'value' => $date,
							          'compare' => 'LIKE',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							  }
							  $the_query = new WP_Query( $args );

							  if ($the_query->have_posts()) {
							  	echo '<h3>Contractor Reports</h3>';
							    echo '<ul class="list-group">';
							    while ($the_query->have_posts()) {
							      $the_query->the_post();

							      echo '<li class="list-group-item">';
							      $status = emanager_post::status($post, 'slug');
							      $postid = get_the_ID();
							      $title = get_the_title();
							      $permalink = get_permalink($post);
							      $company = emanager_post::company( $post );
							      echo '<h3><a href="'.$permalink.'">'.$company.'</a></h3>';
							      if ($status != 'approved'){
							      	echo '<p style="color: red">(Not Approved by Super)</p>';
							      } else {
							    	echo '</p>';
							  	  }
							      $incidents = eman_get_field('incidents_on_site');
							      if ($incidents === 'Yes'){
							      	echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Incident Occured</div>';
							      }
							      $scope = eman_get_field('scope');
									if($scope)
									{
										echo '<ol>';

										foreach($scope as $sc)
										{
											echo '<li>' . get_the_title($sc['building']) .' '; 
											if($sc['floor'] != '') {
												echo get_the_title($sc['floor']);
											} 
											if($sc['location'] != '') {
												echo get_the_title($sc['location']);
											}
											echo ' - ' . $sc['scope'] .'</li>';
										}

										echo '</ol>';
									}
								  $other_notes = eman_get_field('other_notes');
									if($other_notes) {
								   echo $other_notes;
								  }	

								  $count = trim(substr($title, strrpos($title, ',') + 1));
								  echo '<h4>Manhours: '. $count .'</h4>';
								  $attachments = eman_get_field('backup');
									if($attachments)
									{
										echo '<ul>';

										foreach($attachments as $attachment)
										{
											echo '<li><div>'.emanager_post::display_backup($attachment['file']).'</div></li>';
										}

										echo '</ul>';
									}		      
							      echo '</li>';

							    }
							    echo '</ul>';
							  }
							  wp_reset_postdata();
							  echo '<h3>Weather</h3>';
							  echo '<div class="panel panel-default">
  									<div class="panel-body">';

							  $proj_city = str_replace(' ', '_', eman_get_field('proj_city', 'option'));
							  if ( ! $proj_city ) $proj_city = 'New_York';
							  $proj_state = eman_get_field('proj_state', 'option');
							  if ( ! $proj_city ) $proj_city = 'NY';

							  if( isset($_POST['datepicker']) ) {
							    $date_weather = strtotime($_POST['datepicker']);
							    if (date('Ymd') == date('Ymd', $date_weather) && shortcode_exists( 'fw' )) {
							      echo do_shortcode('[fw city="' . $proj_city . '" state="' . $proj_state . '" days="1"]');
							    } elseif (date('Ymd') !== date('Ymd', $date_weather) && shortcode_exists( 'hw' )) {
							      echo do_shortcode('[hw city="' . $proj_city . '" state="' . $proj_state . '" d="'.date('d', $date_weather).'" m="'.date('m', $date_weather).'" y="'.date('Y', $date_weather).'" icon="42"]');
							    } else {
							      echo '';
							    }
							  } else {
							    if (shortcode_exists( 'fw' )) {
							      echo do_shortcode('[fw city="' . $proj_city . '" state="' . $proj_state . '" days="1"]');
							    }
							  }
							  
							  echo '</div></div>';

							if( isset($_POST['datepicker']) )
							  {
							    $date = $_POST['datepicker'];
							    $args = array(
							      'post_type' => 'em_photos',
							      'order' => 'ASC',
							      'orderby' => 'title',
							      'meta_query' => array(
							        array(
							          'key' => 'date_taken',
							          'value' => $date,
							          'compare' => 'LIKE',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							  } else {
							    $date = $y.$m.$d;
							    $args = array(
							      'post_type' => 'em_photos',
							      'order' => 'ASC',
							      'orderby' => 'title',
							      'meta_query' => array(
							        array(
							          'key' => 'date_taken',
							          'value' => $date,
							          'compare' => 'LIKE',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							  }
							  $the_query = new WP_Query( $args );

							  if ($the_query->have_posts()) {
							    while ($the_query->have_posts()) {
							      $the_query->the_post();

							      $image_ids = eman_get_field('photos', false, false);

								  $shortcode = '[gallery ids="' . implode(',', $image_ids) . '" link="file"]';

								  echo do_shortcode( $shortcode );
							    }
							  }
							  wp_reset_postdata();
						
							the_content(); 
							?>
							<div class="clear"></div>
							<?php #wp_link_pages( array( 'before' => '<div class="page-link">' . themeblvd_get_local('pages').': ', 'after' => '</div>' ) ); ?>
							<?php #edit_post_link( themeblvd_get_local( 'edit_page' ), '<p class="edit-link clearfix">', '</p>' ); ?>
						</div><!-- .entry-content -->
					</article><!-- #post-<?php the_ID(); ?> -->

		</div>

		<?php #get_sidebar(); ?>

		<?php do_action( 'after_content' ); ?>

	</div>

</div>

<?php get_footer(); ?>