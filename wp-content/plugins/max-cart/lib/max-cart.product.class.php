<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/14/15
 * Time: 6:55 PM
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

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

		$this->product_variations = get_the_term_list($post->ID, parent::MAX_CART_VARIATION, '<li>', ' >&nbsp;</li><li>', '</li>');

		$this->set_product_breadcrumbs();

		$this->product_gallery = isset( $meta[parent::P_GALLERY_KEY] ) ? unserialize( $meta[parent::P_GALLERY_KEY][0] ) : array();

		$this->get_product_sizes($this->product_gallery);

		$this->product_sku = isset( $meta[parent::P_SKU_KEY] ) ? $meta[parent::P_SKU_KEY][0] : $post->ID;
		$this->product_id = $post->ID;
	}

	private function set_product_breadcrumbs() {
		global $post;
		$terms = wp_get_object_terms( $post->ID, parent::MAX_CART_CATEGORY, array( 'order' => 'ASC', 'orderby' => 'term_order' ) );

		$this->product_categories .= '<li><a href="/products">All Products</a> >&nbsp;</li>';

		$grills = false;

		$parts_array = array(
			'burner',
			'burner head',
			'cooking grid',
			'gaslight part',
			'heat shielding',
			'Kings Kooker & hot plate',
			'repair part',
			'valve',
			'venturi',
			'warming rack'
		);
		$part = false;

		for ($i = 0; $i < count($terms); $i++) {
			if ($terms[$i]->name === 'Grills') {
				$grills = $terms[$i];
			}

			if (in_array($terms[$i]->name, $parts_array)) {
				$part = true;
			}

			if ($terms[$i]->name === 'parts') {
				unset($terms[$i]);
			}
		}

		if ($grills) {
			$this->product_categories .= '<li><a href="/product-category/' . $grills->slug . '">' . $grills->name . '</a> >&nbsp;</li>';
			$this->product_categories .= '<li><a href="' . $this->product_company->guid . '">' .$this->product_company->post_title . '</a> >&nbsp;</li>';

			foreach ($terms as $term) {
				if ($term->name !== 'Grills') {
				    $this->product_categories .= '<li><a href="/product-category/' . $term->slug . '">' . $term->name . '</a> >&nbsp;</li>';
				}
			}
		} else if ($part) {
			$parts_term = get_term_by( 'name', 'parts', maxCart::MAX_CART_CATEGORY );
			$this->product_categories .= '<li><a href="/product-category/' . $parts_term->slug . '">' . $parts_term->name . '</a>  >&nbsp;</li>';
			foreach ($terms as $term) {
				$this->product_categories .= '<li><a href="/product-category/' . $term->slug . '">' . $term->name . '</a> >&nbsp;</li>';
			}
		} else {
			foreach ($terms as $term) {
				$this->product_categories .= '<li><a href="/product-category/' . $term->slug . '">' . $term->name . '</a> >&nbsp;</li>';
				if ($term->name === 'Grills') {
					$this->product_categories .= '<li><a href="' . $this->product_company->guid . '">' .$this->product_company->post_title . '</a> >&nbsp;</li>';
				}
			}
		}

		$this->product_categories .= '<li>' . $this->product_title . '</li>';
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
