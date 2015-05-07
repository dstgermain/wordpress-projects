<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/25/15
 * Time: 6:49 PM
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

class maxCartCompany extends maxCart {
	public $company_id = 0;
	public $company_name = '';
	public $company_logo = '';
	public $company_products = array();
	public $breadcrumbs = '';
	public $company_categories = array();

	public function __construct() {
		self::set_company_id();
		self::set_company_name();
		self::set_company_products();
		self::set_breadcrumbs();
		self::set_categories();
	}

	private function set_company_id() {
		global $post;

		$this->company_id = $post->ID;
	}

	private function set_categories() {
		$this->company_categories = get_the_terms( $this->company_id, parent::MAX_CART_COMPANY_CATEGORIES );
	}

	private function set_company_name() {
		$this->company_name = get_the_title( $this->company_id );
	}

	private function set_company_products() {
		$args = array(
			'post_type' => parent::MAX_CART_PRODUCT,
			'meta_query' => array(
				array(
					'key'     => parent::P_COMPANY_KEY,
					'value'   => $this->company_id
				)
			),
			'orderby'   => 'meta_value_num',
			'meta_key'  => parent::P_PRICE_KEY,
			'order'     => 'ASC',
			'posts_per_page' => 12
		);

		$this->company_products = new WP_Query($args);
	}

	private function set_breadcrumbs() {
		$this->breadcrumbs = sprintf('<li><a href="%s">%s</a> >&nbsp;</li><li>%s</li>',
			'/companies', 'All Companies', $this->company_name);
	}
}
