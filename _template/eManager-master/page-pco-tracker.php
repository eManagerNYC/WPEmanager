<?php //This part is required for WordPress to recognize it as a page template
/*
Template Name: PCO Tracker
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
$pagename = 'PCO Tracker';

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
							  
							  echo '<form class="form-inline" role="form" action="" method="get">
							                    <div class="form-group">
							                    <label  class="sr-only" for="pcosearch">PCO Search</label>
							                    <input type="text" id="pcosearch" name="pcosearch" placeholder="PCO Number" value="" size="15"/>
							                    </div>
							                    <button type="submit" class="btn btn-default">Submit</button>
							                </form>';
							  
							  echo '<hr>';

							  $pcoargs = $tkargs = array();
							  $pcovalue = 0;
							  if( isset($_GET['pcosearch']) )
							  {
							    $pco = $_GET['pcosearch'];
							    $pcoargs = array(
							      'post_type' => 'em_noc',
							      'order' => 'ASC',
							      'orderby' => 'title',
							      'meta_query' => array(
							        array(
							          'key' => 'pco_number',
							          'value' => $pco,
							          'compare' => '=',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							    $tkargs = array(
							      'post_type' => 'em_tickets',
							      'order' => 'ASC',
							      'orderby' => 'title',
							      'meta_query' => array(
							        array(
							          'key' => 'pco_number',
							          'value' => $pco,
							          'compare' => '=',
							          'type' => 'NUMERIC'
							        )
							      )
							    );
							    echo '<h2>#'.$pco.'</h2>';
							  } 
							  
							  $the_query = new WP_Query( $pcoargs );
							  if ($the_query->have_posts()) {
							  	echo '<h3>PCOs</h3>';
							     echo '<ul class="list-group">';
							    while ($the_query->have_posts()) {
							      $the_query->the_post();

							      echo '<li class="list-group-item">';
							      $status = emanager_post::status($post, 'slug');
							      $poststatus = emanager_post::status($post, 'slug');
      							  $postid = get_the_ID();
							      $title = get_the_title();
							      $permalink = get_permalink($post);
							      $author = get_the_author();
							      $company = emanager_post::company( $post );
							      $contractors_and_estimate = eman_get_field('contractors_and_estimate', $post->ID);
							      echo '<h4><a href="'.$permalink.'">'.$title.'</a><br><small>'.$author.'</small></h4>';
							      
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
								  $description = eman_get_field('description');
									if($description) {
								   echo $description;
								  }	

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
									if ( is_array($contractors_and_estimate) )
							          {
							            $total = 0;
							            $row_count=0;
							            foreach ( $contractors_and_estimate as $row )
							            {
							              $item_count=0;
							              foreach ( $row as $key2 => $item )
							              {
							                if ( 'estimated_value' == $key2 ) {
							                  $total += $item;
							                }
							                $item_count++;
							              }
							              $row_count++;
							            }
							          }
							        echo '<strong>Value: '. eman_number_format($total) .'</strong>';
							        $pcovalue += $total;
							      echo '</li>';

							    }

							    echo '</ul>';
							  }
							  wp_reset_postdata();

							  echo '<h2>Total NOC Value <span class="label label-primary">$'.eman_number_format($pcovalue).'</span></h2><hr>';	

							  $the_query = new WP_Query( $tkargs );
							  if ($the_query->have_posts()) {
							  	echo '<h3>Tickets</h3>';
							     echo '<ul class="list-group">';
							    while ($the_query->have_posts()) {
							      $the_query->the_post();

							      echo '<li class="list-group-item">';
							      $status = emanager_post::status($post, 'slug');
							      $postid = get_the_ID();
							      $title = get_the_title();
							      $permalink = get_permalink($post);
							      $company = emanager_post::company( $post );
							      echo '<h4><a href="'.$permalink.'">'.$title.'</a><br><small>'.$company.'</small></h3>';
							      
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
								  $description = eman_get_field('description');
									if($description) {
								   echo $description;
								  }	

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
									// subtract ticket value from total
									// echo '<strong>Value: '. eman_number_format($total) .'</strong>';
							        // $pcovalue -= $total;
							      echo '</li>';

							    }

							    echo '</ul>';
							  }
							  wp_reset_postdata();	
							echo '<h2>Total Remaining Value <span class="label label-primary">$'.eman_number_format($pcovalue).'</span></h2><hr>';					
							the_content(); 
							?>
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