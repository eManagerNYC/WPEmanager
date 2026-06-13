<?php

/**
 * QR Code
 */

define( 'QRCODE_SHORTCODE', 'qrcode' );

add_action( 'init', array('eman_qrcode', 'init') );

class eman_qrcode
{
	public static function init()
	{
		// add shortcode
		global $shortcode_tags;

		if ( isset($shortcode_tags[QRCODE_SHORTCODE]) ) {
			add_action('admin_footer',          array(__CLASS__, 'warning'));
		} else {
			add_shortcode(QRCODE_SHORTCODE,     array(__CLASS__, 'shortcode'));
		}

		// add shortcode support in sidebar text widget
		if ( false === has_filter( 'widget_text', 'do_shortcode' ) ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}

		return;
	}

	public static function shortcode( $atts, $content=null )
	{
	    global $post;
	    $out = '';

	    // parse attributes and set some default values
	    $atts = shortcode_atts( array(
	    	'size'       => '150',
			'level'      => 'l',
			'border'     => 1,
			'background' => ( ! empty($atts['content_color']) ? $atts['content_color'] : '' ),
			'class'      => 'qrcode',
			'title'      => '',
			'text'       => '',
			'tel'        => '',
			'sms'        => '',
			'email'      => '',
			'link'       => '',
			'debug'      => false,
			'post_id'    => $post->ID
		), $atts );

		// check if text came from var or content
		if ( ! is_null($content) ) {
			$atts['text'] = $content;
		}
		$atts['_org_text'] = $atts['text'];

		// if we have a post ID, replace $value with custom field values
		$atts['post_id'] = (int) $atts['post_id'];
		if ( $atts['post_id'] )
		{
			foreach ($atts as $param => $val)
			{
				if ( '$' == substr($val, 0, 1) && '$$' != substr($val, 0, 2) && '_' != substr($param, 0, 1) ) {
					$atts[$param] = get_post_meta($post->ID, str_replace('$', '', $val), true);
				} elseif ( '$$' == substr($val, 0, 2) ) {
					$atts[$param] = substr($val, 1);
				}
			}
		}

		if ( ! empty($atts['tel']) ) {
			$atts['text'] = 'tel:' . $atts['tel'];
		}

		if ( ! empty($atts['link']) ) {
			if ( 'http://' != substr(strtolower($atts['link']), 0, 7) && 'https://' != substr(strtolower($atts['link']), 0, 8) ) {
				$atts['text'] = 'http://' . $atts['link'];
			} else {
				$atts['text'] = $atts['link'];
			}
		}

		if ( ! empty($atts['email']) )
		{
			if ( 'mailto:' != substr(strtolower($atts['email']), 0, 7) ) {
				$atts['text'] = 'mailto:' . $atts['email'];
			} else {
				$atts['text'] = $atts['email'];
			}
		}

		if ( ! empty($atts['sms']) )
		{
			if ( 'smsto:' != substr(strtolower($atts['sms']), 0, 6) ) {
				$atts['text'] = 'smsto:' . $atts['sms'];
			} else {
				$atts['text'] = $atts['sms'];
			}
		}

		// if we have a post ID, replace {post-xx} values with actuall values
		if ( $atts['post_id'] )
		{
			$tmp_author    = get_userdata($post->post_author);
			$tmp_author    = $tmp_author->user_nicename;
			$tmp_permalink = get_permalink($post->ID);

			$tmp = str_replace(
				array('{post->id}', '{post->title}',   '{post->date}',   '{post->author}', '{post->permalink}', '{post->comments}'), 
				array($post->ID,    $post->post_title, $post->post_date, $tmp_author,       $tmp_permalink,     $post->comment_count), 
				$atts['text']
			);
			$atts['text'] = $tmp;
		}

		// background
		if ( 6 != strlen($atts['background']) ) {
			$atts['background'] = 'FFFFFF';
		} else {
			$atts['background'] = urlencode($atts['background']);
		}

		// class
		$atts['class'] = urlencode($atts['class']);

		// size
		$atts['size'] = (int) $atts['size'];

		// border
		$atts['border'] = (int) $atts['border'];

		// level
		switch (strtolower($atts['level'])) {
			case 'l':
				$atts['level'] = 'L';
				break;
			case 'm':
				$atts['level'] = 'M';
				break;
			case 'q':
				$atts['level'] = 'Q';
				break;
			case 'h':
				$atts['level'] = 'H';
				break;
			default:
				$atts['level'] = 'L';
		}

		if ( empty($atts['title']) ) {
			$atts['title'] = $atts['text'];
		} else {
			$atts['title'] = htmlspecialchars($atts['title']);
		}

		$atts['text'] = urlencode($atts['text']);

		// debug?
		$atts['debug'] = (bool) $atts['debug'];
		if ( $atts['debug'] )
		{
			$out .= '<pre>';
			$out .= var_export($atts, true);
			$out .= '</pre>';
		}

		if (strlen($atts['text']) > 4000) {
			$out .= 'Error: maximum length of QR coded text is 4000 characters';
			return $out;
		}

		// build JS function
		$out .= '<img class="' . $atts['class'] . '" src="' . (is_ssl() ? 'https://chart.googleapis.com/chart?' : 'http://chart.apis.google.com/chart?');
		$out .= 'chf=bg,s,' . $atts['background'] . '&amp;chs=' . $atts['size'] . 'x' . $atts['size'] . '&amp;cht=qr&amp;chld=' . $atts['level'] . '|' . $atts['border'] . '&amp;chl=' . $atts['text'] . '" ';
		$out .= 'width="' . $atts['size'] . '" height="' . $atts['size'] . '" alt="' . $atts['title'] . '" title="' . $atts['title'] . '" />';

		return $out;
	}
  
	public function warning()
	{
		?>
		<div id="message" class="error">
			<p><strong>QR code is not active!</strong> The shortcode [gmap] is already in use by another plugin. Contact a developer.</p>
		</div>
		<?php
	}
}

/**
 * functions for easy template support
 */
function get_qrcode( $shortcode )
{
	$out = '';

	if ( false === strpos(strtolower($shortcode), '[qrcode') ) {
		$out .= 'Please use the standard shortcode syntax. Ie: [qrcode]my text[/qrcode].';
	} else {
		$out .= do_shortcode($shortcode);
	}

	return $out;
}

function the_qrcode( $shortcode )
{
	echo get_qrcode($shortcode);
}
