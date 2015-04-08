<?php
/**
 * Created by PhpStorm.
 * User: Dan St. Germain
 * Date: 3/1/15
 * Time: 8:38 AM
 */

class maxCartCompanyArchive extends maxCart {
	public $breadcrumbs = '<li>All Companies</li>';
	public $companies = null;
    public $listings = array();
	public $listings_chunk = array();
	public $alpha_list = array();
	public $alphabet = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

	public function __construct() {
		/** TODO: Get company archive */
		self::set_companies();
		self::set_alpha();
	}

	private function set_companies() {
		$args = array(
			'post_type' => self::MAX_CART_COMPANY,
			'orderby'   => 'title',
			'order'     => 'ASC',
			'posts_per_page' => 12
		);

		$this->companies = new WP_Query( $args );
	}

	private function set_alpha() {
		$group = null;

		if ( $this->companies->have_posts() ) {
			foreach( $this->companies->posts as $post ) {
				$alpha = $post->post_title[0];

				if ( is_numeric($alpha) ) {
					$alpha = '#';
				}

				if ( $group !== $alpha ) {
					array_push( $this->alpha_list, $alpha );
					array_push( $this->listings, '<li class="company-list-header" id="' . $alpha . '">' . $alpha . '</li>' );
				}

				array_push( $this->listings, '<li><a href="' . $post->guid . '">' . $post->post_title . '</a></li>' );

				$group = $alpha;
			}
		}

		self::chunk_list();
	}

	private function chunk_list() {
		$this->listings_chunk = array_chunk( $this->listings, ceil( count( $this->listings ) / 3 ) );
	}
}
