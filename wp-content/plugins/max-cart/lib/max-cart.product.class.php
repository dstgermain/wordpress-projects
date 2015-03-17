<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/14/15
 * Time: 6:55 PM
 */

class maxCartProduct extends maxCart {
	public $product_price       = '';
	public $product_company     = array();
	public $product_stock       = false; // returns false if no stock is set.
	public $product_categories  = '';
	public $product_variations  = array();
	public $product_gallery     = array(); // array of keys
	public $product_gallery_sizes = array();

	public function __construct(){
		self::get_product_meta();
	}

	private function get_product_meta() {
		global $post;

		$meta = get_post_meta( $post->ID );

		$this->product_price   = isset( $meta[parent::P_PRICE_KEY] ) ? $meta[parent::P_PRICE_KEY][0] : 'free';
		$this->product_company = isset( $meta[parent::P_COMPANY_KEY] ) ? get_post($meta[parent::P_COMPANY_KEY][0]) : array();
		$this->product_stock   = isset( $meta[parent::P_STOCK_KEY] ) ? intval($meta[parent::P_STOCK_KEY][0]) : false ;

		$this->product_categories = get_the_term_list($post->ID, parent::MAX_CART_CATEGORY, '<li>', ' >&nbsp;</li><li>', '</li>');
		$this->product_variations = get_the_term_list($post->ID, parent::MAX_CART_VARIATION, '<li>', ' >&nbsp;</li><li>', '</li>');


		$this->product_gallery = isset( $meta[parent::P_GALLERY_KEY] ) ? unserialize( $meta[parent::P_GALLERY_KEY][0] ) : array();

		$this->get_product_sizes($this->product_gallery);
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
