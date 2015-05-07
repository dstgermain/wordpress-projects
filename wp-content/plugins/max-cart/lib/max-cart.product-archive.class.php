<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/23/15
 * Time: 9:17 PM
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

class maxCartProductArchive extends maxCart {
	public $breadcrumbs = '';
	public $products = null;

	public function __construct() {
		self::get_product_breadcrumbs();
		self::get_products();
	}

	private function get_products() {
		$args = array(
			'post_type' => parent::MAX_CART_PRODUCT,
			'orderby'   => 'meta_value_num',
			'meta_key'  => parent::P_PRICE_KEY,
			'order'     => 'ASC',
			'posts_per_page' => 12
		);

		if (get_query_var(maxCart::MAX_CART_CATEGORY)) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => maxCart::MAX_CART_CATEGORY,
					'field'    => 'slug',
					'terms'    => get_query_var(maxCart::MAX_CART_CATEGORY),
				)
			);
		}

		if (get_query_var('s')) {
			$args['s'] = get_query_var('s');
		}

		$this->products = new WP_Query($args);
	}

	private function get_product_breadcrumbs() {
		$slug = get_query_var(maxCart::MAX_CART_CATEGORY);

		if ($slug) {
			$term = get_term_by( 'slug', $slug, maxCart::MAX_CART_CATEGORY );
			$this->breadcrumbs = '<li><a href="/products">All Products</a></li>';
			$this->breadcrumbs .= '<li>&nbsp;> ' . $term->name . '</li>';
			$parent = $term->parent;

			if ($parent) {
				while ($parent) {
					$p = get_term_by( 'id', $parent, maxCart::MAX_CART_CATEGORY );
					$this->breadcrumbs = '<li><a href="/product-category/' . $p->slug . '">' . $p->name . '</a> >&nbsp;</li>' . $this->breadcrumbs;
					$parent = $p->parent;
				}
			}
		} else {
			$this->breadcrumbs = '<li>All Products</li>';
		}
	}
}
