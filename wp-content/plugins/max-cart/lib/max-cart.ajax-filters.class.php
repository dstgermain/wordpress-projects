<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/26/15
 * Time: 7:54 PM
 */
if (!class_exists('maxCartAjaxFilters')) {
	class maxCartAjaxFilters extends maxCart {
		const ENDLESS_PRODUCT_COUNT = 12;

		public function __construct() {
			add_action( 'wp_ajax_maxcart_get_posts', array( $this, 'maxcart_get_posts' ) );
			add_action( 'wp_ajax_nopriv_maxcart_get_posts', array( $this, 'maxcart_get_posts' ) );

			add_action( 'wp_ajax_maxcart_get_categories', array( $this, 'maxcart_get_categories' ) );
			add_action( 'wp_ajax_nopriv_maxcart_get_categories', array( $this, 'maxcart_get_categories' ) );
		}

		public function maxcart_get_categories() {
			$response = array();
			$response['success'] = false;

			if ( ! isset( $_POST ) && ! isset( $_POST['_wpnonce'] ) ) {
				echo json_encode( $response );
				die();
			}

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'maxcart_ajax' ) ) {
				echo json_encode( $response );
				die();
			}

			if ( !isset( $_POST['category'] ) ) {
				echo json_encode( $response );
				die();
			}

			$categories = array();

			$args = array(
				'orderby'      => 'name',
				'order'        => 'ASC',
				'hide_empty'   => true,
				'hierarchical' => true,
				'parent'       => $_POST['category'],
			);

			$cat_terms = get_terms( parent::MAX_CART_CATEGORY, $args);

			foreach($cat_terms as $term) {
				$categories[] = array(
					'id'       => $term->term_id,
					'name'     => $term->name
				);
			}

			$response['categories'] = $categories;
			$response['success'] = true;

			echo json_encode( $response );

			die();
		}

		public function maxcart_get_posts() {
			$response = array();
			$response['success'] = false;

			if ( ! isset( $_POST ) && ! isset( $_POST['_wpnonce'] ) ) {
				echo json_encode( $response );
				die();
			}

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'maxcart_ajax' ) ) {
				echo json_encode( $response );
				die();
			}

			if ( !isset( $_POST['offset'] ) ) {
				echo json_encode( $response );
				die();
			}

			$args = array(
				'post_type'      => parent::MAX_CART_PRODUCT,
				'posts_per_page' => 12,
				'offset'         => $_POST['offset']
			);

			if ( isset( $_POST['orderBy'] ) ) {
				$order_props = explode(':', $_POST['orderBy']);

				if ( $order_props && ( count( $order_props ) > 1 ) ) {
					if ( $order_props[0] === 'price' ) {
						$args['orderby'] = 'meta_value_num';
						$args['meta_key'] = parent::P_PRICE_KEY;
					} else if ( $order_props[0] === 'name' ) {
						$args['orderby'] = 'title';
					}

					if ( $order_props[1] === 'ASC' || $order_props[1] === 'DESC' ) {
						$args['order'] = $order_props[1];
					}
				}
			}

			if ( isset ( $_POST['company'] ) ) {
				$args['meta_query'] = array(
					array(
						'key'          => maxCart::P_COMPANY_KEY,
						'value'        => $_POST['company'],
						'meta_compare' => 'IN'
					)
				);
			}

			if ( isset ( $_POST['category'] ) ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => maxCart::MAX_CART_CATEGORY,
						'field'    => 'id',
						'terms'    => $_POST['category'],
					)
				);
			}

			if ( isset( $_POST['price'] ) ) {
				if ( !isset( $args['meta_query'] ) ) {
					$args['meta_query'] = array();
				}

				$prices = explode('-', $_POST['price']);

				if ( count( $prices ) > 1 ) {
					array_push($args['meta_query'], array(
						'key'     => maxCart::P_PRICE_KEY,
						'value'   => array( intval($prices[0]), intval($prices[1]) ),
							'type'    => 'numeric',
							'compare' => 'BETWEEN',
						));
				}


			}

			$args['posts_per_page'] = self::ENDLESS_PRODUCT_COUNT;

			$the_query = new WP_Query( $args );
			$last_page = false;

			ob_start();
			if ( $the_query->have_posts() ) {
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$maxCartProduct = new maxCartProduct();
					include( WP_PLUGIN_DIR . '/max-cart/templates/product-list-single.php' );
				}
				wp_reset_postdata();
			} else { ?>
				<div class="margin_15 padding_15 text-center bg-warning">No Results</div>
			<?php }
			if (count($the_query->posts) < self::ENDLESS_PRODUCT_COUNT ) {
				$last_page = true;
			}

			$buffer = ob_get_contents();
			ob_end_clean();

			$response['query'] = $args;
			$response['success'] = true;
			$response['last'] = $last_page;
			$response['body'] = $buffer;

			echo json_encode( $response );

			die();
		}
	}
	new maxCartAjaxFilters();
}
