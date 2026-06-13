<?php
/*
Plugin Name: Kanban
Plugin URI: http://turneremanager.com
Description: Kanban to collaborate
Author: Matthew M. Emma
Version: 1.0
Author URI: http://www.turneremanager.com
*/
/*
* Use in shortcode like this: [kanban type="em_issues"]
*/
$Kanban = new Kanban();

class Kanban {

  public function __construct() {
    add_action( 'wp_enqueue_scripts', array($this, 'kanbanscripts'), 10, 0  );
    add_shortcode('kanban', array($this, 'kanban_shortcode'));
  }
  public function kanbanscripts() {
    wp_register_script( 'kanban', get_template_directory_uri() . '/includes/plugins/kanban/assets/kanban.js', array(), null, false );
    wp_enqueue_script('kanban');

    wp_register_style( 'kanban', get_template_directory_uri().'/includes/plugins/kanban/assets/kanban.css');
    wp_enqueue_style('kanban');
  }
  private function kanban_query( $posttype, $term ) {
    $args = array (
        'post_type'              => $posttype,
        'category_name'          => $term,
        'posts_per_page'         => -1,
      );
      $html = '';
      $html .='<div class="panel panel-primary kanban-col" id="card_container">
                  <div class="panel-heading">
                      '.$term.'
                      <i class="fa fa-2x fa-plus-circle pull-right"></i>
                  </div>
                  <div class="panel-body">';
      
      $kanban = new WP_Query( $args );
      // The Loop
      if ( $kanban->have_posts() ) {
        while ( $kanban->have_posts() ) {
          $kanban->the_post();
          $html .='<article class="kanban-entry grab" id="post-<?php the_ID(); ?>" <?php post_class(); ?> draggable="true">
                                <div class="kanban-entry-inner">
                                    <div class="kanban-label">
                                        <h2 class="entry-title">'. the_title().'</h2>
                                        <div class="entry-content">
                                            '.the_content().'
                                        </div>
                                    </div>
                                </div>
                            </article>';
        }
      } 

      $html .='</div>
                <div class="panel-footer">
                    <a href="#">Export</a>
                </div>
            </div>';
      // Restore original Post Data
      return $html;
      wp_reset_postdata();
  }
  public function kanban_shortcode( $atts ) {
    extract( shortcode_atts( array(
      'type' => 'em_activites',
      'stax' => 'em_status'
    ), $atts, 'kanban' ) );
    $kbn_terms = get_terms( $stax, array( 'hide_empty' => false ) );;
    $html = '<div class="container-fluid">
        <div id="sortableKanbanBoards" class="row">';
    if ($kbn_terms) {
      foreach ($kbn_terms as $term) {
        $html .= $this->kanban_query($type, $term->name);
      }
    }
    $html .= '</div></div>';
    return $html; //.'<br>'.$html;
  }
}