<?php

/**
 *
 */

$classname = 'eman_submenu';

if ( ! class_exists($classname) ) :

class eman_submenu
{
	/**
	 * Class prefix
	 *
	 * @var 	string
	 */
	const PREFIX = __CLASS__;

	/**
	 * Settings
	 *
	 * @since 	1.0.0
	 * @var 	string
	 */
	protected static $settings = array();

	/**
	 * Holds the current menu items
	 *
	 * @var 	array
	 */
	protected $menuitems = array();

	/**
	 * Class construct
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function __construct()
	{
		self::$settings = array(
			'defaults' => array(
				'menuitem' => array(
					'url'     => '',
					'title'   => '',
					'text'    => '',
					'class'   => '',
				),
			),
			'strings' => array(
				'menuitem' => '<li%s><a href="%s" title="%s">%s</a></li>',
			),
		);
	}

	/**
	 * init
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function init()
	{
		add_action( self::PREFIX . '/add',        array($this, 'add') );
		add_action( self::PREFIX . '/add_item',   array($this, 'add_item') );
		add_action( self::PREFIX . '/show',       array($this, 'show') );
		add_action( self::PREFIX . '/show_item',  array($this, 'show_item') );
	}

	/**
	 * add
	 *
	 * Add multiple items to the submenu
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	protected function args( $args )
	{
		global $wp;

		$args = wp_parse_args( $args, self::$settings['defaults']['menuitem'] );
		if ( empty($args['title']) ) {
			$args['title'] = $args['text'];
		}
		if ( false === strpos($args['class'], 'page_item') ) {
			$args['class'] .= ' page_item';
		}

		$args['url']   = esc_url_raw($args['url']);
		$args['title'] = esc_attr($args['title']);
		$args['text']  = esc_html($args['text']);

		$current_url = home_url( $_SERVER['REQUEST_URI'] );
		if ( false === strpos($args['class'], 'current_page_item') && $args['url'] == $current_url ) {
			$args['class'] .= ' current_page_item';
		}

		$args['class'] = esc_attr( trim($args['class']) );

		return $args;
	}

	/**
	 * add
	 *
	 * Add multiple items to the submenu
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function add( $menuitems )
	{
		if ( is_array($menuitems) )
		{
			foreach ( $menuitems as $menuitem ) {
				do_action( self::PREFIX . '/add_item', $menuitem );
			}
		}
	}

	/**
	 * add_item
	 *
	 * Add an item to the submenu
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function add_item( $args )
	{
		$args = $this->args( $args );

		if ( $args['text'] && $args['url'] ) {
			$this->menuitems[] = $args;
		}
	}

	/**
	 * show
	 *
	 * Output the submenu to the front end if there is anything to show
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function show()
	{
		if ( is_array($this->menuitems) ) :
?>
			<div id="subnav">
				<div id="inner-subnav" class="wrap cf">
					<nav id="sub-nav" role="navigation">
						<ul>
						<?php foreach( $this->menuitems as $menuitem ) :
							do_action( self::PREFIX . '/show_item', $menuitem );
						endforeach; ?>
						</ul>
					</nav>
				</div>
			</div>
<?php
		endif;
	}

	/**
	 * show_item
	 *
	 * Output a single submenu item
	 *
	 * @author  Jake Snyder
	 * @return	void
	 */
	public function show_item( $args )
	{
		$args = $this->args( $args );

		$class = ($args['class'] ? ' class="' . $args['class'] . '"' : '');
		printf( self::$settings['strings']['menuitem'], $class, $args['url'], $args['title'], $args['text'] );
	}
}

$$classname = new $classname;
add_action( 'init', array($$classname, 'init') );

endif;