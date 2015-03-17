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
require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.product.class.php' );

if (is_admin()) {
	require_once( WP_PLUGIN_DIR . '/max-cart/lib/max-cart.product-admin.class.php' );

}


