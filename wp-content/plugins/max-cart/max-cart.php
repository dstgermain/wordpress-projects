<?php
/**
 * Plugin Name: MaxCart
 * Plugin URI: n/a
 * Description: Wordpress Ecommerce Plugin.
 * Version: 0.0.1
 * Author: Daniel St. Germain
 * Author URI: http://URI_Of_The_Plugin_Author
 * Text Domain: max_cart_textdomain
 * Network: true
 * License: GPL2
 * 
 * ===== Copyright 2015 Daniel St. Germain ======
 */

session_start();

require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.class.php' );

function max_cart_setup() {
	$includes = array(
		'process-checkout',
		'cart',
		'product',
		'product-archive',
		'company',
		'company-archive',
		'quick-cart',
		'ajax-filters',
		'filters',
		'checkout',
		'paypal-ipn',
		'store-info'
	);

	foreach ($includes as $include) {
		require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.' . $include . '.class.php' );
	}
}

add_action('init', 'max_cart_setup');

if (is_admin()) {
	require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.product-admin.class.php' );
	require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.order-admin.class.php' );
}

class customTemplates {

	protected $plugin_slug;
	private static $instance;
	protected $templates;

	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new customTemplates();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->templates = array();

		add_filter(
			'page_attributes_dropdown_pages_args',
			array( $this, 'register_project_templates' )
		);

		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);

		add_filter(
			'template_include',
			array( $this, 'view_project_template' )
		);

		$this->templates = array(
			'templates/cart-tpl.php' => 'Cart View',
			'templates/checkout-tpl.php' => 'Checkout Page',
			'templates/thankyou.php' => 'Thank You Page'
		);
	}

	public function register_project_templates( $atts ) {
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		wp_cache_delete( $cache_key, 'themes' );

		$templates = array_merge( $templates, $this->templates );

		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

	public function view_project_template( $template ) {

		global $post;

		if ( ! isset( $this->templates[ get_post_meta(
				$post->ID, '_wp_page_template', true
			) ] )
		) {

			return $template;

		}

		$file = plugin_dir_path( __FILE__ ) . get_post_meta(
				$post->ID, '_wp_page_template', true
			);

		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;

	}
}

add_action( 'plugins_loaded', array( 'customTemplates', 'get_instance' ) );

class maxCartFiltersWidget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'maxcart-filters', // Base ID
			'MaxCart Filters', // Name
			array( 'classname' => 'maxcart-filters', 'description' => __( 'MaxCart Ajax Filters.', 'text_domain' ) ) // Args
		);
	}

	function widget( $args, $instance ) {
		$widget = new maxCartFilters();
		$widget->print_filters();
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		echo 'no options available';
	}
}

function maxcart_register_widgets() {
	register_widget( 'maxCartFiltersWidget' );
}

add_action( 'widgets_init', 'maxcart_register_widgets' );

add_filter('template_include','my_custom_search_template');

function my_custom_search_template($template){
	global $wp_query;
	if (!$wp_query->is_search)
		return $template;

	return WP_PLUGIN_DIR . '/max-cart/templates/product-archive.php';
}
