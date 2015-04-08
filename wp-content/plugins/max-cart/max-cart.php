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

require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.class.php' );

function max_cart_setup() {
	$includes = array(
		'cart',
		'product',
		'product-archive',
		'company',
		'company-archive',
		'quick-cart',
		'ajax-filters',
		'filters'
	);

	foreach ($includes as $include) {
		require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.' . $include . '.class.php' );
	}
}

add_action('init', 'max_cart_setup');

if (is_admin()) {
	require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.product-admin.class.php' );
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


