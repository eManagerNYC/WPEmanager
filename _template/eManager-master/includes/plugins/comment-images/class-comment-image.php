<?php
/**
 * Comment Image
 *
 * @package   Comment_Image
 * @author    Tom McFarlin <tom@tommcfarlin.com>
 * @license   GPL-2.0+
 * @link      http://tommcfarlin.com
 * @copyright 2013 - 2014 Tom McFarlin
 */

/**
 * Include dependencies necessary for adding Comment Images to the Media Uplower
 *
 * See also:	http://codex.wordpress.org/Function_Reference/media_sideload_image
 * @since		1.8
 */
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

/**
 * Comment Image
 *
 * @package Comment_Image
 * @author  Tom McFarlin <tom@tommcfarlin.com>
 */
class Comment_Image {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	var $settings = array();

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		} // end if

		return self::$instance;

	} // end get_instance

	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	private function __construct() {

		add_filter( 'Comment_Image/settings/get_path', array($this, 'helpers_get_path'), 1 );
		add_filter( 'Comment_Image/settings/get_dir',  array($this, 'helpers_get_dir'), 1 );

		// Set up settings
		$this->settings = array(
			'path'     => apply_filters( 'Comment_Image/settings/get_path', __FILE__ ),
			'dir'      => apply_filters( 'Comment_Image/settings/get_dir',  __FILE__ ),
		);

		// Load plugin textdomain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Determine if the hosting environment can save files.
		if( $this->can_save_files() ) {

			// We need to update all of the comments thus far
			if( false == get_option( 'update_comment_images' ) || null == get_option( 'update_comment_images' ) ) {
				$this->update_old_comments();
			} // end if

			// Go ahead and enable comment images site wide
			add_option( 'comment_image_toggle_state', 'enabled' );

			// Add comment related stylesheets and JavaScript
			add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );

			// Add the Upload input to the comment form
			add_action( 'comment_form_field_comment' , array( $this, 'add_image_upload_form' ) );
			add_filter( 'wp_insert_comment', array( $this, 'save_comment_image' ) );
			add_filter( 'comments_array', array( $this, 'display_comment_image' ) );

			// Add a note to recent comments that they have Comment Images
			add_filter( 'comment_row_actions', array( $this, 'recent_comment_has_image' ), 20, 2 );

			// Add a column to the Post editor indicating if there are Comment Images
			add_filter( 'manage_posts_columns', array( $this, 'post_has_comment_images' ) );
			add_filter( 'manage_posts_custom_column', array( $this, 'post_comment_images' ), 20, 2 );

			// Add a column to the comment images if there is an image for the given comment
			add_filter( 'manage_edit-comments_columns', array( $this, 'comment_has_image' ) );
			add_filter( 'manage_comments_custom_column', array( $this, 'comment_image' ), 20, 2 );

			// Setup the Project Completion metabox
			add_action( 'add_meta_boxes', array( $this, 'add_comment_image_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_comment_image_display' ) );

		// If not, display a notice.
		} else {

			add_action( 'admin_notices', array( $this, 'save_error_notice' ) );

		} // end if/else

	} // end constructor

	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	 /**
	  * Adds a column to the 'All Posts' page indicating whether or not there are
	  * Comment Images available for this post.
	  *
	  * @param	array	$cols	The columns displayed on the page.
	  * @param	array	$cols	The updated array of columns.
	  * @since	1.8
	  */
	 public function post_has_comment_images( $cols ) {

		 $cols['comment-images'] = __( 'Comment Attachments', 'comment-images' );

		 return $cols;

	 } // end post_has_comment_images

	 /**
	  * Provides a link to the specified post's comments page if the post has comments that contain
	  * images.
	  *
	  * @param	string	$column_name	The name of the column being rendered.
	  * @param	int		$int			The ID of the post being rendered.
	  * @since	1.8
	  */
	 public function post_comment_images( $column_name, $post_id ) {

		 if( 'comment-images' == strtolower( $column_name ) ) {

		 	// Get the comments for the current post.
		 	$args = array(
		 		'post_id' => $post_id
		 	);
		 	$comments = get_comments( $args );

		 	// Look at each of the comments to determine if there's at least one comment image
		 	$has_comment_image = false;
		 	foreach( $comments as $comment ) {

			 	// If the comment meta indicates there's a comment image and we've not yet indicated that it does...
			 	if( 0 != get_comment_meta( $comment->comment_ID, 'comment_image', true ) && ! $has_comment_image ) {

			 		// ..Make a note in the column and link them to the media for that post
					$html = '<a href="edit-comments.php?p=' . $comment->comment_post_ID . '">';
						$html .= __( 'View Post Comment Attachments', 'comment-images' );
					$html .= '</a>';

			 		echo $html;

			 		// Mark that we've discovered at least one comment image
			 		$has_comment_image = true;

			 	} // end if

		 	} // end foreach

		 } // end if

	 } // end post_comment_images

	 /**
	  * Adds a column to the 'Comments' page indicating whether or not there are
	  * Comment Images available.
	  *
	  * @param	array	$columns	The columns displayed on the page.
	  * @param	array	$columns	The updated array of columns.
	  */
	 public function comment_has_image( $columns ) {

		 $columns['comment-image'] = __( 'Comment Attachment', 'comment-images' );

		 return $columns;

	 } // end comment_has_image

	 /**
	  * Renders the actual image for the comment.
	  *
	  * @param	string	The name of the column being rendered.
	  * @param	int		The ID of the comment being rendered.
	  * @since	1.8
	  */
	 public function comment_image( $column_name, $comment_id ) {

		 if( 'comment-image' == strtolower( $column_name ) ) {

			$media_url = $this->file_url( $comment_id );

			if ( $media_url )
			{
				if ( $this->is_image($media_url) )
				{
					$file_output = '<img src="' . $media_url . '" alt="" width="150" />';
				}
				else
				{
					$file_array  = explode('/', $media_url);
					$file_output = ( is_array($file_array) ) ? end($file_array) : 'attachment';
				}
				echo '<a href="' . $media_url . '" target="_blank">' . $file_output . '</a>';
			}

 		 } // end if/else

	 } // end comment_image

	 /**
	  * Determines whether or not the current comment has comment images. If so, adds a new link
	  * to the 'Recent Comments' dashboard.
	  *
	  * @param	array	$options	The array of options for each recent comment
	  * @param	object	$comment	The current recent comment
	  * @return	array	$options	The updated list of options
	  * @since	1.8
	  */
	 public function recent_comment_has_image( $options, $comment ) {

		 if( 0 != ( $comment_image = get_comment_meta( $comment->comment_ID, 'comment_image', true ) ) ) {

			 $html = '<a href="edit-comments.php?p=' . $comment->comment_post_ID . '">';
			 	$html .= __( 'Comment Attachments', 'comment-images' );
			 $html .= '</a>';

			 $options['comment-images'] = $html;

		 } // end if

		 return $options;

	 } // end recent_comment_has_image

	 /**
	  * Loads the plugin text domain for translation
	  */
	 function plugin_textdomain() {
		 load_plugin_textdomain( 'comment-images', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	 } // end plugin_textdomain

	 /**
	  * In previous versions of the plugin, the image were written out after the comments. Now,
	  * they are actually part of the comment content so we need to update all old options.
	  *
	  * Note that this option is not removed on deactivation because it will run *again* if the
	  * user ever re-activates it this duplicating the image.
	  */
	 private function update_old_comments() {

		// Update the option that this has not run
		update_option( 'update_comment_images', false );

		// Iterate through each of the comments...
 		foreach( get_comments() as $comment ) {

			// ...get the comment image meta
			$comment_output = $this->show_file( $comment->comment_ID );

			if ( $comment_output )
			{
				// ...and render it in a paragraph element appended to the comment
				$comment->comment_content .= '<p class="comment-image">';
					$comment->comment_content .= $comment_output;
				$comment->comment_content .= '</p><!-- /.comment-image -->';

				// Now we need to actually update the comment
				wp_update_comment( (array)$comment );
			}

		} // end if

		// Update the fact that this has run so we don't run it again
		update_option( 'update_comment_images', true );

	 } // end update_old_comments

	 /**
	  * Display a WordPress error to the administrator if the hosting environment does not support 'file_get_contents.'
	  */
	 function save_error_notice() {

		 $html = '<div id="comment-image-notice" class="error">';
		 	$html .= '<p>';
		 		$html .= __( '<strong>Comment Attachments Notice:</strong> Unfortunately, your host does not allow uploads from the comment form. This plugin will not work for your host.', 'comment-images' );
		 	$html .= '</p>';
		 $html .= '</div><!-- /#comment-image-notice -->';

		 echo $html;

	 } // end save_error_notice

	 /**
	  * Adds the public stylesheet to the single post page.
	  */
	 function add_styles() {

		if( is_single() || is_page() ) {

			wp_register_style( 'comment-images', $this->settings['dir'] . 'css/plugin.css' );
			wp_enqueue_style( 'comment-images' );

		} // end if

	} // end add_scripts

	/**
	 * Adds the public JavaScript to the single post page.
	 */
	function add_scripts() {

		if( is_single() || is_page() ) {

			wp_register_script( 'comment-images', $this->settings['dir'] . 'js/dev/plugin.js', array( 'jquery' ) );
			wp_enqueue_script( 'comment-images' );

		} // end if

	} // end add_scripts

	/**
	 * Adds the public JavaScript to the single post editor
	 */
	function add_admin_styles() {

		$screen = get_current_screen();
		if( 'post' === $screen->id || 'page' == $screen->id ) {
			wp_enqueue_style( 'comment-images-admin', plugins_url( '/comment-images/css/admin.css' ) );
		} // end if

	} // end add_admin_styles

	/**
	 * Adds the public JavaScript to the single post editor
	 */
	function add_admin_scripts() {

		$screen = get_current_screen();
		if( 'post' === $screen->id || 'page' == $screen->id ) {
			wp_enqueue_script( 'comment-images', plugins_url( '/comment-images/js/admin.min.js' ), array( 'jquery' ) );
		} // end if

	} // end add_admin_scripts

	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @param	$post_id	The ID of the post on which the comment is being added.
	 */
 	function add_image_upload_form( $html ) {

	 	$post_id = $GLOBALS['post']->ID;

	 	// Create the label and the input field for uploading an image
	 	if ( 'enabled' == get_option( 'comment_image_toggle_state' ) || 'enable' == get_post_meta( $post_id, 'comment_images_toggle', true ) ) {

		 	ob_start(); ?>
			<div id="comment-image-wrapper">
				<a data-toggle="collapse" data-parent="#comment-image-wrapper" href="#file-upload">+ Attach Files</a>
				<div id="file-upload" class="panel-collapse collapse">
					<label for="author">Add Attachment</label>
					<input type="file" name="comment_image_<?php echo $post_id; ?>" id="comment_image" />
				</div>
			</div>
			<?php $html .= ob_get_clean();

			global $wp_roles;
		    $all_roles      = $wp_roles->roles;
		    $editable_roles = apply_filters( 'editable_roles', $all_roles );
/** /
			ob_start(); ?>
			<p class="comment-form-sendto">
				<h3>Send to:</h3>
			</p>
			<p class="comment-form-messenger-role">
				<label for="author">Role</label>
				<select name="messenger-role" id="messenger-role">
					<option value="NULL"> - Select - </option>
					<?php
					foreach ( $wp_roles->roles as $key => $role ) :
						echo "<option value=\"$key\">{$role['name']}</option>";
					endforeach; ?>
				</select>
			</p>

			<p class="comment-form-messenger-user">
				<label for="author">Person</label>
				<select name="messenger-user" id="messenger-user" multiple="multiple">
					<?php /** / ?><option value="NULL"> - Select - </option><?php /**-/ ?>
				</select>
			</p>
			<?php $html .= ob_get_clean();

/** /
		 	$html .= '<div id="comment-image-wrapper">';
			 	$html .= '<p id="comment-image-error">';
			 		$html .= __( '<strong>Heads up!</strong> You are attempting to upload an invalid image. If saved, this image will not display with your comment.', 'comment-images' );
			 	$html .= '</p>';
				 $html .= "<label for='comment_image_$post_id'>";
				 	$html .= __( 'Select an image for your comment (GIF, PNG, JPG, JPEG):', 'comment-images' );
				 $html .= "</label>";
				 $html .= "<input type='file' name='comment_image_$post_id' id='comment_image' />";

			 $html .= '</div><!-- #comment-image-wrapper -->';
/**/

		 } // end if

		 return $html;

	} // end add_image_upload_form

	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @param	$comment_id	The ID of the comment to which we're adding the image.
	 */
	function save_comment_image( $comment_id ) {
/** /
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($_POST);
echo "</pre>\n";

/**/

		// The ID of the post on which this comment is being made
		$post_id = $_POST['comment_post_ID'];

		// The key ID of the comment image
		$comment_image_id = "comment_image_$post_id";

		$media_id = false;

		// If the nonce is valid and the user uploaded an image, let's upload it to the server
		if( isset( $_FILES[ $comment_image_id ] ) && ! empty( $_FILES[ $comment_image_id ] ) ) {

			// Store the parts of the file name into an array
			$file_name_parts = explode( '.', $_FILES[ $comment_image_id ]['name'] );

			// If the file is valid, upload the image, and store the path in the comment meta
			#if( $this->is_valid_file_type( $file_name_parts[ count( $file_name_parts ) - 1 ] ) ) {;

				// do the validation and storage stuff
				$media_id = media_handle_sideload( $_FILES[ $comment_image_id ], $post_id );

				// If error storing permanently, unlink
				if ( is_wp_error($media_id) ) {
					@unlink($file_array['tmp_name']);
					//return $media_id;
					$media_id = false;
				}
				else
				{
					add_comment_meta( $comment_id, 'comment_image', $media_id );
				}

			#} // end if

		} // end if
/**/
		if (! empty($_POST['messenger-user']) )
		{
			$users = (array) $_POST['messenger-user'];
			add_comment_meta( $comment_id, 'user', $users );

			$from = wp_get_current_user();

			$private = (! empty($_POST['messenger-status']) ) ? true : false;

			$comment = get_comment($comment_id);
/** /
echo ' $post_id = '. $post_id ."<br>\n";
echo '<pre style="font-size:0.7em;text-align:left;">';
print_r($comment);
print_r($from);
echo "</pre>\n";
exit;
/**/
			$args = array(
				'subject'    => "A new comment on " . get_the_title( $post_id ),
				'message'    => $from->display_name . " added a comment to " . get_the_title( $post_id ) . ": \n\r\n\r " . $comment->comment_content,
				'to'         => false,
				'comment_id' => $comment_id,
				'reply'      => $post_id,
				'attachment' => $media_id,
				'from'       => $from,
				'private'    => $private,
			);

			foreach ( $users as $user )
			{
				$args['to'] = $user;
				do_action( 'sewn/messenger/add_message', $args );
			}
		}
/**/
#exit;
	} // end save_comment_image

	/**
	 * Appends the image below the content of the comment.
	 *
	 * @param	$comment	The content of the comment.
	 */
	function display_comment_image( $comments ) {

		// Make sure that there are comments
		if( count( $comments ) > 0 ) {

			// Loop through each comment...
			foreach( $comments as $comment ) {

				// ...get the comment image meta
				$comment_output = $this->show_file( $comment->comment_ID );

				if ( $comment_output )
				{
					// ...and render it in a paragraph element appended to the comment
					$comment->comment_content .= '<p class="comment-image">';
						$comment->comment_content .= $comment_output;
					$comment->comment_content .= '</p><!-- /.comment-image -->';
				}

			} // end foreach

		} // end if

		return $comments;

	} // end display_comment_image

	function is_image( $file ) {

		$ext = preg_match('/\.([^.]+)$/', $file, $matches) ? strtolower($matches[1]) : false;
	
		$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
	
		if ( in_array($ext, $image_exts) )
			return true;

		return false;
	}

	function file_url( $comment_ID )
	{
		$media_id = get_comment_meta( $comment_ID, 'comment_image', true );

		$output = '';
		if ( $media_id )
		{
			$media_url = false;
			if ( is_numeric($media_id) )
				$media_url   = wp_get_attachment_url( $media_id );
			elseif ( is_array($media_id) ) // Supports old plugin method
				$media_url   = $media_id['url'];

			if ( $media_url ) $output = $media_url;
		}

		return $output;
	}

	function show_file( $comment_ID )
	{
		$media_url = $this->file_url( $comment_ID );

		$output = '';
		if ( $media_url )
		{
			if ( $this->is_image($media_url) )
			{
				$file_output = '<img src="' . $media_url . '" alt="" />';
			}
			else
			{
				$file_array  = explode('/', $media_url);
				$file_output = ( is_array($file_array) ) ? end($file_array) : 'attachment';
			}
			$output .= '<a href="' . $media_url . '" target="_blank">' . $file_output . '</a>';
		}

		return $output;
	}

	/*--------------------------------------------*
	 * Meta Box Functions
	 *---------------------------------------------*/

	 /**
	  * Registers the meta box for displaying the 'Comment Images' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.8
	  */
	 public function add_comment_image_meta_box() {

		 add_meta_box(
		 	'disable_comment_images',
		 	__( 'Comment Attachments', 'comment-images' ),
		 	array( $this, 'comment_images_display' ),
		 	'post',
		 	'side',
		 	'low'
		 );

		 add_meta_box(
		 	'disable_comment_images',
		 	__( 'Comment Attachments', 'comment-images' ),
		 	array( $this, 'comment_images_display' ),
		 	'page',
		 	'side',
		 	'low'
		 );

	 } // end add_project_completion_meta_box

	 /**
	  * Displays the option for disabling the Comment Images upload field.
	  *
	  * @version	1.0
	  * @since 		1.8
	  */
	 public function comment_images_display( $post ) {

		 wp_nonce_field( plugin_basename( __FILE__ ), 'comment_images_display_nonce' );

		 $html = '<p class="comment-image-info">' . __( 'Doing this will only update <strong>this</strong> post.', 'comment-images' ) . '</p>';
		 $html .= '<select name="comment_images_toggle" id="comment_images_toggle" class="comment_images_toggle_select">';
		 	$html .= '<option value="enable" ' . selected( 'enable', get_post_meta( $post->ID, 'comment_images_toggle', true ), false ) . '>' . __( 'Enable comment images for this post.', 'comment-images' ) . '</option>';
		 	$html .= '<option value="disable" ' . selected( 'disable', get_post_meta( $post->ID, 'comment_images_toggle', true ), false ) . '>' . __( 'Disable comment images for this post.', 'comment-images' ) . '</option>';
		 $html .= '</select>';

		 $html .= '<hr />';

		 $comment_image_state = 'disabled';
		 if( '' == get_option( 'comment_image_toggle_state' ) || 'enabled' == get_option( 'comment_image_toggle_state' ) ) {
			 $comment_image_state = 'enabled';
		 } // end if/else

		 $html .= '<p class="comment-image-warning">' . __( 'Doing this will update <strong>all</strong> posts.', 'comment-images' ) . '</p>';
		 if( 'enabled' == $comment_image_state ) {

			 $html .= '<input type="button" class="button" name="comment_image_toggle" id="comment_image_toggle" value="' . __( 'Disable Comments For All Posts', 'comment-images' ) . '"/>';

		 } else {

			 $html .= '<input type="button" class="button" name="comment_image_toggle" id="comment_image_toggle" value="' . __( 'Enable Comments For All Posts', 'comment-images' ) . '"/>';

		 } // end if/else

		 $html .= '<input type="hidden" name="comment_image_toggle_state" id="comment_image_toggle_state" value="' . $comment_image_state . '"/>';
		 $html .= '<input type="hidden" name="comment_image_source" id="comment_image_source" value=""/>';

		 echo $html;

	 } // end comment_images_display

	 /**
	  * Saves the meta data for displaying the 'Comment Images' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.8
	  */
	 public function save_comment_image_display( $post_id ) {

		 // If the user has permission to save the meta data...
		 if( $this->user_can_save( $post_id, 'comment_images_display_nonce' ) ) {

			// Only do this if the source of the request is from the button
			if( isset( $_POST['comment_image_source'] ) && 'button' == $_POST['comment_image_source'] ) {

				if( '' == get_option( 'comment_image_toggle_state' ) || 'enabled' == get_option( 'comment_image_toggle_state' ) ) {

					$this->toggle_all_comment_images( 'disable' );
					update_option( 'comment_image_toggle_state', 'disabled' );

				} elseif ( 'disabled' == get_option( 'comment_image_toggle_state' ) ) {

					$this->toggle_all_comment_images( 'enable' );
					update_option( 'comment_image_toggle_state', 'enabled' );

				} // end if

			// Otherwise, we're doing this for the post-by-post basis with the select box
			} else {

			 	// Delete any existing meta data for the owner
				if( get_post_meta( $post_id, 'comment_images_toggle' ) ) {
					delete_post_meta( $post_id, 'comment_images_toggle' );
				} // end if
				update_post_meta( $post_id, 'comment_images_toggle', $_POST[ 'comment_images_toggle' ] );

			} // end if/else

		 } // end if

	 } // end save_comment_image_display

	/*--------------------------------------------*
	 * Utility Functions
	 *--------------------------------------------*/

	 /**
	  * Loads up all posts and toggles the post meta for each post enabling or disabling comment images
	  * for all posts.
	  *
	  * @param    string    $str_state    Whether or not we are enabling or disabling comment images.
	  */
	 private function toggle_all_comment_images( $str_state ) {

		 // First, create the query to pull back all published posts
		 $args = array(
		 	'post_type'    =>    array( 'post', 'page' ),
		 	'post_status'  =>    array( 'publish', 'private' )
		 );
		 $query = new WP_Query( $args );

		 // Now loop through each post and update its meta data
		 while( $query->have_posts() ) {

			$query->the_post();

			// If post meta exists, delete it, then specify our value
			if( get_post_meta( get_the_ID(), 'comment_images_toggle' ) ) {
				delete_post_meta( get_the_ID(), 'comment_images_toggle' );
			} // end if
			update_post_meta( get_the_ID(), 'comment_images_toggle', $str_state );

		 } // end while
		 wp_reset_postdata();

	 } // end toggle_all_comment_images

	/**
	 * Determines if the specified type if a valid file type to be uploaded.
	 *
	 * @param	$type	The file type attempting to be uploaded.
	 * @return			Whether or not the specified file type is able to be uploaded.
	 */
	private function is_valid_file_type( $type ) {

		return true;
		#$type = strtolower( trim ( $type ) );
		#return $type == __( 'png', 'comment-images' ) || $type == __( 'gif', 'comment-images' ) || $type == __( 'jpg', 'comment-images' ) || $type == __( 'jpeg', 'comment-images' );

	} // end is_valid_file_type

	/**
	 * Determines if the hosting environment allows the users to upload files.
	 *
	 * @return			Whether or not the hosting environment supports the ability to upload files.
	 */
	private function can_save_files() {
		return function_exists( 'file_get_contents' );
	} // end can_save_files

	 /**
	  * Determines whether or not the current user has the ability to save meta data associated with this post.
	  *
	  * @param		int		$post_id	The ID of the post being save
	  * @param		bool				Whether or not the user has the ability to save this post.
	  * @version	1.0
	  * @since		1.8
	  */
	 private function user_can_save( $post_id, $nonce ) {

	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) ) ? true : false;

	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision) && $is_valid_nonce;

	 } // end user_can_save

	/**
	 * Get the plugin path
	 *
	 * Calculates the path (works for plugin / theme folders). These functions are from Elliot Condon's ACF plugin.
	 *
	 * @since	0.1
	 * @return	void
	 */
	public function helpers_get_path( $file )
	{
	   return trailingslashit( dirname($file) );
	}

	/**
	 * Get the plugin directory
	 *
	 * Calculates the directory (works for plugin / theme folders). These functions are from Elliot Condon's ACF plugin.
	 *
	 * @since	0.1
	 * @return	void
	 */
	public function helpers_get_dir( $file )
	{
        $dir = trailingslashit( dirname($file) );
        $count = 0;

        // sanitize for Win32 installs
        $dir = str_replace('\\' ,'/', $dir);

        // if file is in plugins folder
        $wp_plugin_dir = str_replace('\\' ,'/', WP_PLUGIN_DIR); 
        $dir = str_replace($wp_plugin_dir, plugins_url(), $dir, $count);

        if ( $count < 1 )
        {
	       // if file is in wp-content folder
	       $wp_content_dir = str_replace('\\' ,'/', WP_CONTENT_DIR); 
	       $dir = str_replace($wp_content_dir, content_url(), $dir, $count);
        }

        if ( $count < 1 )
        {
	       // if file is in ??? folder
	       $wp_dir = str_replace('\\' ,'/', ABSPATH); 
	       $dir = str_replace($wp_dir, site_url('/'), $dir);
        }

        return $dir;
    }

} // end class

/**
 * Backlog
 *
 *  + Features
 *		- P2 Compatibility
 *		- JetPack compatibility
 *		- Is there a way to re-size the images before uploading?
 *		- User's shouldn't have to enter text to leave a comment.
 *
 *	+ Bugs
 * 		- Warning: file_get_contents() [function.file-get-contents]: Filename cannot be empty in /home/[masked]/public_html/wp-content/plugins/comment-images/plugin.php on line 199
 *		- I actually tested the plugin on my original enquiry and it appears that the images actually get *removed* from the comments when the plugin is disabled.
 */