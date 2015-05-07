<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/28/15
 * Time: 11:51 AM
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

if ( ! class_exists( 'maxCartFilters' ) ) {
	class maxCartFilters extends maxCart {
		private $categories = array();
		private $compatibility = array();
		private $companies = array();
		public $filters = array();

		public function __construct() {
			self::set_companies();
			self::set_categories();
			self::set_compatibility();
		}

		private function set_compatibility() {

			$args = array(
				'orderby'      => 'name',
				'order'        => 'ASC',
				'hide_empty'   => true,
				'hierarchical' => true,
				'parent'       => 0,
			);

			$comp_terms = get_terms( maxCart::MAX_CART_PART_COMPATIBILITY, $args );

			foreach ( $comp_terms as $term ) {
				$this->compatibility[] = array(
					'id'   => $term->term_id,
					'name' => $term->name
				);
			 }


			if ( count( $this->compatibility ) &&
			     !get_query_var( maxCart::MAX_CART_PART_COMPATIBILITY ) &&
			     get_query_var( maxCart::MAX_CART_CATEGORY ) !== 'accessory' &&
			     get_query_var( maxCart::MAX_CART_CATEGORY ) !== 'grills' ) {
				$this->filters['compatibility'] = $this->compatibility;
			}
		}

		private function set_categories() {
			$args = array(
				'orderby'      => 'name',
				'order'        => 'ASC',
				'hide_empty'   => true,
				'hierarchical' => true,
				'parent'       => 0,
			);

			if ( get_query_var( maxCart::MAX_CART_CATEGORY ) ) {
				$term = get_term_by( 'slug', get_query_var( maxCart::MAX_CART_CATEGORY ), maxCart::MAX_CART_CATEGORY );

				$args['parent'] = $term->term_id;
			}

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

			if ( $company_query->have_posts() && get_query_var( maxCart::MAX_CART_CATEGORY ) !== 'parts' ) {
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

		public function print_filters() {
			$search = "";
			if ( get_query_var('s') ) {
				$search = get_query_var('s');
			}?>
			<li>
				<div class="max-filters">
					<div class="visible-xs filters-toggle">
						<a href="#" class="js-open-filters">Filters</a>
					</div>
					<div class="max-filters-toggle">
						<?php foreach ( $this->filters as $key => $filter ) { ?>
							<div class="max-filter filter-<?php echo $key; ?>">
								<h5 class="max-filter-title"><?php echo $key === 'compatibility' ? 'Grill Part Brands ' : $key; ?></h5>
								<ul class="max-filters">
									<?php foreach ( $filter as $item ) : ?>
										<li class="js-add-children_<?php echo $item['id']; ?>">
											<input type="checkbox" class="hidden max-checkbox-input"
											       data-type="<?php echo $key; ?>" value="<?php echo $item['id']; ?>"/>
											<label class="js-max-checkbox max-checkbox" data-filter-name="<?php echo $item['name']; ?>">
												<?php echo $item['name']; ?>
											</label>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php } ?>
					</div>
					<?php if ( get_query_var( maxCart::MAX_CART_CATEGORY ) ) : ?>
						<?php $term = get_term_by( 'slug', get_query_var( maxCart::MAX_CART_CATEGORY ),  maxCart::MAX_CART_CATEGORY )?>
						<input type="hidden" data-type="category" value="<?php echo $term->term_id; ?>"/>
				    <?php endif; ?>
					<?php if ($search && $search !== '') : ?>
						<input type="hidden" data-type="s" value="<?php echo $search; ?>"/>
				    <?php endif; ?>
				</div>
			</li>
		<?php
		}
	}
}
