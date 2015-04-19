<?php

/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/18/15
 * Time: 5:45 PM
 */
class maxCartQuickCart extends maxCart {
	public function __construct() {
		add_action( 'wp_ajax_maxcart_add_to_cart', array( $this, 'maxcart_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_maxcart_add_to_cart', array( $this, 'maxcart_add_to_cart' ) );

		add_action( 'wp_ajax_maxcart_quickcart_session', array( $this, 'maxcart_quickcart_session' ) );
		add_action( 'wp_ajax_nopriv_maxcart_quickcart_session', array( $this, 'maxcart_quickcart_session' ) );

		add_action( 'wp_ajax_maxcart_remove_from_cart', array( $this, 'maxcart_remove_from_cart' ) );
		add_action( 'wp_ajax_nopriv_maxcart_remove_from_cart', array( $this, 'maxcart_remove_from_cart' ) );

		add_action( 'wp_ajax_maxcart_update_item_qty', array( $this, 'maxcart_update_item_qty' ) );
		add_action( 'wp_ajax_nopriv_maxcart_update_item_qty', array( $this, 'maxcart_update_item_qty' ) );

		add_action( 'wp_footer', array( $this, 'maxcart_add_quickcart' ), 100 );
	}

	public function maxcart_add_quickcart() {
		?>
		<div class="container-fluid maxcart-quickcart" data-bind="if: hide_cart">
			<div class="maxcart-quickcart-wrapper">
				<div class="maxcart-quickcart-inner">
					<span class="fa fa-shopping-cart"></span> <strong data-bind="text: item_count"></strong> items
				</div>
				<div>
					<div class="maxcart-quickcart-table">
						<table class="maxcart-quickcart-list" data-bind="foreach: items">
							<tr>
								<td>
									<a href="" data-bind="text: name, attr: { href: url, title: name }"></a>
								</td>
								<td>
									Qty: <span data-bind="text: qty"></span>
								</td>
								<td>
									Price: <span data-bind="currency: price"></span>
								</td>
								<td>
									<button class="remove-item fa fa-times" data-bind="click: $parent._remove"></button>
								</td>
							</tr>
						</table>
						<table class="maxcart-quickcart-list text-right margin-bottom_15">
							<tr>
								<td>Total: <strong data-bind="currency: items_total"></strong></td>
							</tr>
						</table>
						<p><small>Items are up for grabs until checkout.</small></p>
						<a href="/cart" class="btn btn-success checkout-button">Checkout</a>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	public function maxcart_quickcart_session() {
		$response = array();

		if ( isset( $_SESSION["maxcart_cart"] ) ) {
			if ( isset( $_SESSION["maxcart_cart"]['error_message'] ) ) {
				unset( $_SESSION["maxcart_cart"]['error_message'] );
			}

			$response = $_SESSION['maxcart_cart'];
		}

		echo json_encode( $response );

		die();
	}

	public function maxcart_add_to_cart() {
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'add_product_to_cart' ) ) {
			return false;
		}

		if ( ! isset( $_POST['product_id'] ) ||
		     ! isset( $_POST['product_qty'] ) ||
		     ! isset( $_POST['product_name'] ) ||
		     ! isset( $_POST['product_price'] ) ||
		     ! isset( $_POST['product_url'] )
		) {
			return false;
		}

		$new_product    = array(
			'id'         => $_POST['product_id'],
			'qty'        => $_POST['product_qty'],
			'name'       => $_POST['product_name'],
			'price'      => $_POST['product_price'],
			'url'        => $_POST['product_url'],
			'sku'        => $_POST['product_sku'],
			'item_total' => floatval( $_POST['product_qty'] ) * floatval( $_POST['product_price'] ),
			'thumbnail'  => isset( $_POST['product_thumbnail'] ) ? $_POST['product_thumbnail'] : '',
			'weight'     => get_post_meta($_POST['product_id'], maxCart::P_WEIGHT_KEY, true)
		);

		$product_exists = false;
		$items_total = 0;
		$shipping_total = isset($_SESSION["maxcart_cart"]) && isset($_SESSION["maxcart_cart"]["shipping_total"]) ? $_SESSION["maxcart_cart"]["shipping_total"] : 0;
		$product_stock = get_post_meta( intval( $_POST['product_id'] ), maxCart::P_STOCK_KEY, true );

		if ( isset( $_SESSION["maxcart_cart"] ) ) {
			$current_products = array();

			foreach ( $_SESSION["maxcart_cart"]["items"] as $product ) {
				if ( $product['id'] === $_POST['product_id'] ) {
					$product['qty'] = $product['qty'] + $_POST['product_qty'];
					$product['item_total'] = floatval( $product['qty'] ) * floatval( $_POST['product_price'] );
					$product_exists = true;

					if ($product_stock && $product['qty'] > $product_stock) {
						$_SESSION["maxcart_cart"]['error_message'] = 'We currently only have '. $product_stock . ' in stock.';
						echo json_encode($_SESSION["maxcart_cart"]);
						die();
					}
				}

				$items_total = $items_total + $product['item_total'];

				$current_products[] = $product;
			}

			if ( ! $product_exists ) {
				if (($product_stock || $product_stock === '0') && ($_POST['product_qty'] > $product_stock)) {
					$_SESSION["maxcart_cart"]['error_message'] = 'We currently only have '. $product_stock . ' in stock.';
					echo json_encode($_SESSION["maxcart_cart"]);
					die();
				}
				$current_products[] = $new_product;
				$items_total = $items_total + $new_product['item_total'];
			}

			$_SESSION["maxcart_cart"] = array(
				'items' => $current_products,
				'items_total' => $items_total,
				'shipping_total' => $shipping_total
			);
		} else {
			$_SESSION["maxcart_cart"] = array(
				'items' => array($new_product),
				'items_total' => $new_product['item_total'],
				'shipping_total' => $shipping_total
			);
		}

		echo json_encode( $_SESSION["maxcart_cart"] );

		die();
	}

	public function maxcart_remove_from_cart() {
		if ( isset( $_SESSION["maxcart_cart"] ) && isset( $_POST['product_id'] ) ) {
			$id          = $_POST['product_id'];
			$new_session = [ ];
			$items_total = 0;

			$shipping_total = isset($_SESSION["maxcart_cart"]) && isset($_SESSION["maxcart_cart"]["shipping_total"]) ? $_SESSION["maxcart_cart"]["shipping_total"] : 0;

			foreach ( $_SESSION["maxcart_cart"]["items"] as $product ) {
				if ( $product['id'] !== $id ) {
					array_push( $new_session, $product );
				} else {
					$items_total = $items_total + $product['item_total'];
				}
			}

			if ( count( $new_session ) > 0 ) {
				$_SESSION["maxcart_cart"] = array(
					'items' => $new_session,
					'items_total' => $items_total,
					'shipping_total' => $shipping_total
				);
			} else {
				$_SESSION["maxcart_cart"] = array(
					'items' => array(),
					'items_total' => 0.00,
					'shipping_total' => $shipping_total
				);
			}

			echo json_encode( $_SESSION["maxcart_cart"] );
		}
		die();
	}

	public function maxcart_update_item_qty() {
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'maxcart' ) ) {
			return false;
		}

		if ( ! isset( $_POST['product_id'] ) && ! isset( $_POST['product_qty'] ) ) {
			return false;
		}

		$current_cart = $_SESSION["maxcart_cart"]['items'];
		$updated_cart = array();
		$items_total = 0;
		$product_stock = get_post_meta( intval( $_POST['product_id'] ), maxCart::P_STOCK_KEY, true );

		$shipping_total = isset($_SESSION["maxcart_cart"]) && isset($_SESSION["maxcart_cart"]["shipping_total"]) ? $_SESSION["maxcart_cart"]["shipping_total"] : 0;

		foreach ( $current_cart as $product ) {
			if ( $product['id'] === $_POST['product_id'] ) {
				$product['qty']        = $_POST['product_qty'];
				$product['item_total'] = floatval( $product['price'] ) * floatval( $product['qty'] );

				if ($product_stock && $product['qty'] > $product_stock) {
					$_SESSION["maxcart_cart"]['error_message'] = 'We currently only have '. $product_stock . ' "' . $product['name'] . '" in stock.';
					echo json_encode($_SESSION["maxcart_cart"]);
					die();
				}

				if ( $product['qty'] !== '0' ) {
					array_push( $updated_cart, $product );
				}
			} else {
				array_push( $updated_cart, $product );
			}

			$items_total = $items_total + $product['item_total'];
		}

		$_SESSION["maxcart_cart"] = array(
			'items' => $updated_cart,
			'items_total' => $items_total,
			'shipping_total' => $shipping_total
		);

		echo json_encode( $_SESSION["maxcart_cart"] );
		die();
	}
}

$maxQuickCart = new maxCartQuickCart;
