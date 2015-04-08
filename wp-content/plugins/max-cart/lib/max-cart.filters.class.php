<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/28/15
 * Time: 11:51 AM
 */
if ( ! class_exists( 'maxCartFilters' ) ) {
	class maxCartFilters extends maxCart {
		private $categories = array();
		private $companies = array();
		public $prices = array();
		public $filters = array();

		public function __construct() {
			self::set_companies();
			self::set_categories();
			self::set_prices();
		}

		private function set_categories() {
			$args = array(
				'orderby'      => 'name',
				'order'        => 'ASC',
				'hide_empty'   => true,
				'hierarchical' => true,
				'parent'       => 0,
			);

			$cat_terms = get_terms( parent::MAX_CART_CATEGORY, $args );

			foreach ( $cat_terms as $term ) {
				$this->categories[] = array(
					'id'   => $term->term_id,
					'name' => $term->name
				);
			}

			if ( count( $this->categories ) ) {
				$this->filters['category'] = $this->categories;
			}
		}

		private function set_companies() {
			$args = array(
				'post_type' => self::MAX_CART_COMPANY,
				'orderby'   => 'title',
				'order'     => 'ASC',

			);

			$company_query = new WP_Query( $args );

			if ($company_query->have_posts()) {
				foreach ( $company_query->posts as $company ) {
					$this->companies[] = array(
						'id'   => $company->ID,
						'name' => $company->post_title
					);
				}

				if ( count( $this->companies ) ) {
					$this->filters['company'] = $this->companies;
				}
			}
		}

		private function set_prices() {
			$args = array(
				'post_type' => parent::MAX_CART_PRODUCT,
				'orderby'   => 'meta_value_num',
				'meta_key'  => parent::P_PRICE_KEY,
				'order'     => 'DESC',
				'posts_per_page' => 1
			);

			$query_high = new WP_Query($args);

			if ($query_high->have_posts()) {
				$price_high = get_post_meta( $query_high->post->ID, parent::P_PRICE_KEY, true );

				for ($low = 0; $low < $price_high; $low += 25) {
					$high = $low + 24;
					array_push($this->prices, $low . '-' . $high);
				}
			}
		}

		public function print_filters() { ?>
			<div class="max-filters">
				<?php foreach ( $this->filters as $key => $filter ) : ?>
					<div class="max-filter">
						<h5 class="max-filter-title"><?php echo $key; ?></h5>
						<ul class="max-filters">
							<?php foreach ( $filter as $item ) : ?>
								<li class="js-add-children_<?php echo $item['id']; ?>">
									<input type="checkbox" class="hidden max-checkbox-input" data-type="<?php echo $key; ?>" value="<?php echo $item['id']; ?>"/>
									<label class="js-max-checkbox max-checkbox"><?php echo $item['name']; ?></label>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
				<?php if (count($this->prices) > 1) :?>
					<div class="max-filter">
						<h5 class="max-filter-title">Price</h5>
						<span class="max-current-filter"></span>
						<ul class="max-filters">
							<?php foreach ( $this->prices as $item ) : ?>
								<li>
									<input type="checkbox" class="hidden max-checkbox-input" data-type="price" value="<?php echo $item; ?>"/>
									<label class="js-max-checkbox max-checkbox"><?php echo $item; ?></label>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		<?php
		}
	}
}
