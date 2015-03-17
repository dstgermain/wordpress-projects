<?php
/**
 * Created by PhpStorm.
 * User: Dan St. Germain
 * Date: 3/1/15
 * Time: 8:38 AM
 */

class maxCart {
	// post_type constants
	const MAX_CART_PRODUCT = 'maxcart_product';
	const MAX_CART_COMPANY = 'maxcart_company';
    const MAX_CART_ORDER = 'maxcart_order';
	const MAX_CART_PERSIST = 'maxcart_persist';
    const MAX_CART_WISHLIST = 'maxcart_wishlist';

	// product taxonomy
	const MAX_CART_CATEGORY = 'maxcart_category';
	const MAX_CART_VARIATION = 'maxcart_variation';

	// product_meta constants
	const P_GALLERY_KEY = '_maxcart_product_gallery';
	const P_WEIGHT_KEY = '_maxcart_product_weight';
	const P_LENGTH_KEY = '_maxcart_product_length';
	const P_WIDTH_KEY = '_maxcart_product_width';
	const P_HEIGHT_KEY = '_maxcart_product_height';
	const P_FLATRATE_KEY = '_maxcart_product_flatrate';

	const P_COMPANY_KEY = '_maxcart_company_id';
	const P_PRICE_KEY = '_maxcart_product_price';
	const P_TAX_EXEMPT_KEY = '_maxcart_product_taxexempt';
	const P_TAX_KEY = '_maxcart_product_tax';
	const P_SKU_KEY = '_maxcart_product_sku';
	const P_STOCK_KEY = '_maxcart_product_stock';

	//

	public function __construct() {
		// adding Product post_type
		add_action( 'init', array( $this, 'product_init' ) );
		add_filter( 'post_updated_messages', array( $this, 'product_updated_messages' ) );
		add_filter( 'single_template', array( $this, 'get_product_template' ) );

		// adding Company post_type
		add_action( 'init', array( $this, 'product_company_init' ) );
		add_filter( 'post_updated_messages', array( $this, 'product_company_updated_messages' ) );

		// adding Category Taxonomy
		add_action( 'init', array( $this, 'create_categories_init' ), 0 );

		// adding Variation Taxonomy
		add_action( 'init', array( $this, 'create_variations_init' ), 0 );

		// adding Orders post_type
		add_action( 'init', array( $this, 'order_init' ) );
		add_filter( 'post_updated_messages', array( $this, 'order_updated_messages' ) );

		// adding Persist post_type
		add_action( 'init', array( $this, 'persist_init' ) );

		// adding Wishlist post_type
		add_action( 'init', array( $this, 'wishlist_init' ) );

		// clean menu
		add_action( 'admin_menu', array( $this, 'clean_submenu' ) );

		// init rewrites
		register_activation_hook( __FILE__, array( $this, 'my_rewrite_flush' ) );

		add_action( 'admin_enqueue_scripts', array($this, 'add_admin_scripts') );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_product_scripts' ) );
	}

	function my_rewrite_flush() {
		my_cpt_init();
		flush_rewrite_rules();
	}


	/**
	 * Register Product post_type ex. Blender X.
	 *
	 * @author Daniel St. Germain
	 */
	public function product_init() {
		$labels = array(
			'name'               => _x( 'Products', 'post type general name', 'max_cart_textdomain' ),
			'singular_name'      => _x( 'Product', 'post type singular name', 'max_cart_textdomain' ),
			'menu_name'          => _x( 'Max Cart', 'admin menu', 'max_cart_textdomain' ), // Menu Name (all maxCart post_types get added to this menu)
			'name_admin_bar'     => _x( 'Product', 'add new on admin bar', 'max_cart_textdomain' ),
			'add_new'            => _x( 'Add Product', 'book', 'max_cart_textdomain' ),
			'add_new_item'       => __( 'Add New Product', 'max_cart_textdomain' ),
			'new_item'           => __( 'New Product', 'max_cart_textdomain' ),
			'edit_item'          => __( 'Edit Product', 'max_cart_textdomain' ),
			'view_item'          => __( 'View Product', 'max_cart_textdomain' ),
			'all_items'          => __( 'Products', 'max_cart_textdomain' ),
			'search_items'       => __( 'Search Products', 'max_cart_textdomain' ),
			'parent_item_colon'  => __( 'Parent Products:', 'max_cart_textdomain' ),
			'not_found'          => __( 'No Products found.', 'max_cart_textdomain' ),
			'not_found_in_trash' => __( 'No Products found in Trash.', 'max_cart_textdomain' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'products' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-cart',
			'supports'           => array( 'title', 'editor' )
		);

		register_post_type( self::MAX_CART_PRODUCT, $args );
	}

	/**
	 * Product update messages.
	 * @author Daniel St. Germain
	 *
	 * @param array $messages.
	 * @return array.
	 */
	public function product_updated_messages( /* array */ $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages[self::MAX_CART_PRODUCT] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Product updated.', 'max_cart_textdomain' ),
			2  => __( 'Custom field updated.', 'max_cart_textdomain' ),
			3  => __( 'Custom field deleted.', 'max_cart_textdomain' ),
			4  => __( 'Product updated.', 'max_cart_textdomain' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Product restored to revision from %s', 'max_cart_textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Product published.', 'max_cart_textdomain' ),
			7  => __( 'Product saved.', 'max_cart_textdomain' ),
			8  => __( 'Product submitted.', 'max_cart_textdomain' ),
			9  => sprintf(
				__( 'Product scheduled for: <strong>%1$s</strong>.', 'max_cart_textdomain' ),
				date_i18n( __( 'M j, Y @ G:i', 'max_cart_textdomain' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Product draft updated.', 'max_cart_textdomain' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View Product', 'max_cart_textdomain' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview Product', 'max_cart_textdomain' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	public function get_product_template($single_template) {
		global $post;

		if ($post->post_type === self::MAX_CART_PRODUCT) {
			$single_template = ABSPATH . 'wp-content/plugins/max-cart/templates/product-single.php';
		}
		return $single_template;
	}

	/**
	 * Register Product Company post_type ex. Company X Components.
	 *
	 * @author Daniel St. Germain
	 */
	public function product_company_init() {
		$labels = array(
			'name'               => _x( 'Product Companies', 'post type general name', 'max_cart_textdomain' ),
			'singular_name'      => _x( 'Product Company', 'post type singular name', 'max_cart_textdomain' ),
			'menu_name'          => _x( 'Product Companies', 'admin menu', 'max_cart_textdomain' ),
			'name_admin_bar'     => _x( 'Company', 'add new on admin bar', 'max_cart_textdomain' ),
			'add_new'            => _x( 'Add Company', 'book', 'max_cart_textdomain' ),
			'add_new_item'       => __( 'Add New Company', 'max_cart_textdomain' ),
			'new_item'           => __( 'New Company', 'max_cart_textdomain' ),
			'edit_item'          => __( 'Edit Company', 'max_cart_textdomain' ),
			'view_item'          => __( 'View Company', 'max_cart_textdomain' ),
			'all_items'          => __( 'Product Companies', 'max_cart_textdomain' ),
			'search_items'       => __( 'Search Companies', 'max_cart_textdomain' ),
			'parent_item_colon'  => __( 'Parent Companies:', 'max_cart_textdomain' ),
			'not_found'          => __( 'No Companies found.', 'max_cart_textdomain' ),
			'not_found_in_trash' => __( 'No Companies found in Trash.', 'max_cart_textdomain' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=maxcart_product',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'companies' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' )
		);

		register_post_type( self::MAX_CART_COMPANY, $args );
	}

	/**
	 * Product Company update messages.
	 * @author Daniel St. Germain
	 *
	 * @param array $messages.
	 * @return array.
	 */
	public function product_company_updated_messages( /* array */ $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages[self::MAX_CART_COMPANY] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Company updated.', 'max_cart_textdomain' ),
			2  => __( 'Custom field updated.', 'max_cart_textdomain' ),
			3  => __( 'Custom field deleted.', 'max_cart_textdomain' ),
			4  => __( 'Company updated.', 'max_cart_textdomain' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Company restored to revision from %s', 'max_cart_textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Company published.', 'max_cart_textdomain' ),
			7  => __( 'Company saved.', 'max_cart_textdomain' ),
			8  => __( 'Company submitted.', 'max_cart_textdomain' ),
			9  => sprintf(
				__( 'Company scheduled for: <strong>%s</strong>.', 'max_cart_textdomain' ),
				date_i18n( __( 'M j, Y @ G:i', 'max_cart_textdomain' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Company draft updated.', 'max_cart_textdomain' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View company', 'max_cart_textdomain' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview company', 'max_cart_textdomain' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	/**
	 * Product categories taxonomy
	 * @author Daniel St. Germain
	 */
	function create_categories_init() {
		$labels = array(
			'name'              => _x( 'Product Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Product Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Categories' ),
			'all_items'         => __( 'All Categories' ),
			'parent_item'       => __( 'Parent Category' ),
			'parent_item_colon' => __( 'Parent Category:' ),
			'edit_item'         => __( 'Edit Category' ),
			'update_item'       => __( 'Update Category' ),
			'add_new_item'      => __( 'Add New Category' ),
			'new_item_name'     => __( 'New Category Name' ),
			'menu_name'         => __( 'Product Categories' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'category' ),
		);

		register_taxonomy( self::MAX_CART_CATEGORY, array( self::MAX_CART_PRODUCT ), $args );
	}

	/**
	 * Product variations taxonomy
	 * @author Daniel St. Germain
	 */
	function create_variations_init() {
		$labels = array(
			'name'              => _x( 'Product Variations', 'taxonomy general name' ),
			'singular_name'     => _x( 'Product Variation', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Variations' ),
			'all_items'         => __( 'All Variations' ),
			'parent_item'       => __( 'Parent Variation' ),
			'parent_item_colon' => __( 'Parent Variation:' ),
			'edit_item'         => __( 'Edit Variation' ),
			'update_item'       => __( 'Update Variation' ),
			'add_new_item'      => __( 'Add New Variation' ),
			'new_item_name'     => __( 'New Category Variation' ),
			'menu_name'         => __( 'Product Variations' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
		);

		register_taxonomy( self::MAX_CART_VARIATION, array( self::MAX_CART_PRODUCT ), $args );
	}

	/**
	 * Orders post_type.
	 * These are auto created when an order is made. The order is linked to the user table, if the user is registered.
	 *
	 * @author Daniel St. Germain
	 */
	public function order_init() {
		$labels = array(
			'name'               => _x( 'Orders', 'post type general name', 'max_cart_textdomain' ),
			'singular_name'      => _x( 'Orders', 'post type singular name', 'max_cart_textdomain' ),
			'menu_name'          => _x( 'Orders', 'admin menu', 'max_cart_textdomain' ),
			'name_admin_bar'     => _x( 'Order', 'add new on admin bar', 'max_cart_textdomain' ),
			'add_new'            => _x( 'Add Order', 'book', 'max_cart_textdomain' ),
			'add_new_item'       => __( 'Add New Order', 'max_cart_textdomain' ),
			'new_item'           => __( 'New Order', 'max_cart_textdomain' ),
			'edit_item'          => __( 'Edit Order', 'max_cart_textdomain' ),
			'view_item'          => __( 'View Order', 'max_cart_textdomain' ),
			'all_items'          => __( 'Orders & Tracking', 'max_cart_textdomain' ),
			'search_items'       => __( 'Search Orders', 'max_cart_textdomain' ),
			'parent_item_colon'  => __( 'Parent Orders:', 'max_cart_textdomain' ),
			'not_found'          => __( 'No Orders found.', 'max_cart_textdomain' ),
			'not_found_in_trash' => __( 'No Orders found in Trash.', 'max_cart_textdomain' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=maxcart_product',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'order-history' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'author' )
		);

		register_post_type( self::MAX_CART_ORDER, $args );
	}

	/**
	 * Product Orders update messages.
	 * @author Daniel St. Germain
	 *
	 * @param array $messages.
	 * @return array.
	 */
	public function order_updated_messages( /* array */ $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages[self::MAX_CART_ORDER] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Order updated.', 'max_cart_textdomain' ),
			2  => __( 'Custom field updated.', 'max_cart_textdomain' ),
			3  => __( 'Custom field deleted.', 'max_cart_textdomain' ),
			4  => __( 'Order updated.', 'max_cart_textdomain' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Category restored to revision from %s', 'max_cart_textdomain' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Order published.', 'max_cart_textdomain' ),
			7  => __( 'Order saved.', 'max_cart_textdomain' ),
			8  => __( 'Order submitted.', 'max_cart_textdomain' ),
			9  => sprintf(
				__( 'Category scheduled for: <strong>%1$s</strong>.', 'max_cart_textdomain' ),
				date_i18n( __( 'M j, Y @ G:i', 'max_cart_textdomain' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Order draft updated.', 'max_cart_textdomain' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View Order', 'max_cart_textdomain' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview Order', 'max_cart_textdomain' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	/**
	 * Persistent Cart.
	 * These are auto created when an item is added to a cart if a user is logged in. An array is stored in a custom field with product numbers.
	 *
	 * @author Daniel St. Germain
	 */
	public function persist_init() {
		$labels = array(
			'name'               => _x( 'Persist', 'post type general name', 'max_cart_textdomain' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( self::MAX_CART_PERSIST, $args );
	}

	/**
	 * Wish List posts.
	 * These are auto created by the user when they add an item to their wish list.
	 *
	 * @author Daniel St. Germain
	 */
	public function wishlist_init() {
		$labels = array(
			'name'               => _x( 'Wish List', 'post type general name', 'max_cart_textdomain' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( self::MAX_CART_WISHLIST, $args );
	}

	public function clean_submenu() {
		if(current_user_can('activate_plugins')) {
			global $submenu;
			unset($submenu['edit.php?post_type=maxcart_product'][10]); // Removes 'Add New'.
		}
	}

	public function add_product_scripts() {
		wp_enqueue_script( 'maxcart-product', '/wp-content/plugins/max-cart/resources/js/maxcart-product.js' );

		wp_register_style( 'maxcart-styles', '/wp-content/plugins/max-cart/resources/css/maxcart-main.min.css');
		wp_enqueue_style( 'maxcart-styles' );
	}

	public function add_admin_scripts() {
		wp_register_style( 'maxcart-admin-css', '/wp-content/plugins/max-cart/resources/admin/css/maxcart-admin.min.css' );
		wp_enqueue_style( 'maxcart-admin-css' );

		wp_enqueue_script( 'maxcart-admin', '/wp-content/plugins/max-cart/resources/admin/js/maxcart-admin.js', null, '0.0.1', true );
	}

}

$maxCart = new maxCart;
