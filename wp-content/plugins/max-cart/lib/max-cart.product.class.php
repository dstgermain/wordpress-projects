<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/14/15
 * Time: 6:55 PM
 */

class maxCartProduct extends maxCart {
	public $product_title       = '';
	public $product_url         = 0;
	public $product_price       = '';
	public $product_company     = array();
	public $product_stock       = false; // returns false if no stock is set.
	public $product_categories  = '';
	public $product_variations  = array();
	public $product_gallery     = array(); // array of keys
	public $product_gallery_sizes = array();
	public $product_sku         = '';
	public $product_id          = 0;

	public function __construct(){
		self::get_product_meta();
	}

	private function get_product_meta() {
		global $post;

		$this->product_title = $post->post_title;

		$meta = get_post_meta( $post->ID );
		$this->product_url     = $post->guid;
		$this->product_price   = isset( $meta[parent::P_PRICE_KEY] ) && !empty( $meta[parent::P_PRICE_KEY] ) ? $meta[parent::P_PRICE_KEY][0] : 'free';
		$this->product_company = isset( $meta[parent::P_COMPANY_KEY] ) && !empty( $meta[parent::P_COMPANY_KEY][0] ) ? get_post($meta[parent::P_COMPANY_KEY][0]) : array();
		$this->product_stock   = isset( $meta[parent::P_STOCK_KEY] ) && !empty( $meta[parent::P_STOCK_KEY] ) && $meta[parent::P_STOCK_KEY][0] !== '' ? intval($meta[parent::P_STOCK_KEY][0]) : false ;

		$this->product_categories = '<li><a href="/products">All Products</a> >&nbsp;</li>' . get_the_term_list($post->ID, parent::MAX_CART_CATEGORY, '<li>', ' >&nbsp;</li><li>', ' >&nbsp;</li>') . '<li>' . $this->product_title . '</li>';
		$this->product_variations = get_the_term_list($post->ID, parent::MAX_CART_VARIATION, '<li>', ' >&nbsp;</li><li>', '</li>');


		$this->product_gallery = isset( $meta[parent::P_GALLERY_KEY] ) ? unserialize( $meta[parent::P_GALLERY_KEY][0] ) : array();

		$this->get_product_sizes($this->product_gallery);

		$this->product_sku = isset( $meta[parent::P_SKU_KEY] ) ? $meta[parent::P_SKU_KEY][0] : $post->ID;
		$this->product_id = $post->ID;
	}

	private function get_product_sizes($gallery_keys = array()) {
		foreach ( $gallery_keys as $gallery_key ) {
			array_push($this->product_gallery_sizes, array(
				'thumbnail' => wp_get_attachment_image_src( $gallery_key, 'thumbnail', false ),
				'medium'    => wp_get_attachment_image_src( $gallery_key, 'medium', false ),
				'large'     => wp_get_attachment_image_src( $gallery_key, 'large', false ),
				'full'      => wp_get_attachment_image_src( $gallery_key, 'full', false )
			));
		}
	}
}
