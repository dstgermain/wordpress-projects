<?php
session_start();

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
						<table class="maxcart-quickcart-list text-right">
							<tr>
								<td>Total: <strong data-bind="text: items_total"></strong></td>
							</tr>
						</table>

						<a href="/cart" class="btn btn-success checkout-button">Checkout</a>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	public function maxcart_quickcart_session() {
		$response = array();

		if ( isset( $_SESSION["zipcode"] ) ) {
			$response['zipcode'] = $_SESSION["zipcode"];
		}

		if ( isset( $_SESSION["maxcart_cart"] ) ) {
			$response['items'] = $_SESSION['maxcart_cart'];
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
			'item_total' => floatval( $_POST['product_qty'] ) * floatval( $_POST['product_price'] ),
			'thumbnail'  => isset( $_POST['product_thumbnail'] ) ? $_POST['product_thumbnail'] : ''
		);
		$product_exists = false;

		if ( isset( $_SESSION["maxcart_cart"] ) ) {
			$current_products = array();

			foreach ( $_SESSION["maxcart_cart"] as $product ) {
				if ( $product['id'] === $_POST['product_id'] ) {
					$product['qty'] = $product['qty'] + $_POST['product_qty'];
					$product_exists = true;
				}

				$current_products[] = $product;
			}

			if ( ! $product_exists ) {
				$current_products[] = $new_product;
			}

			$_SESSION["maxcart_cart"] = $current_products;
		} else {
			$_SESSION["maxcart_cart"] = array( $new_product );
		}

		$return = array(
			'items' => $_SESSION["maxcart_cart"],
			'zipcode' => isset( $_SESSION["zipcode"] ) ? $_SESSION["zipcode"] : ''
		);

		echo json_encode( $return );

		die();
	}

	public function maxcart_remove_from_cart() {
		if ( isset( $_SESSION["maxcart_cart"] ) && isset( $_POST['product_id'] ) ) {
			$id          = $_POST['product_id'];
			$new_session = [ ];

			foreach ( $_SESSION["maxcart_cart"] as $product ) {
				if ( $product['id'] !== $id ) {
					array_push( $new_session, $product );
				}
			}

			$_SESSION["maxcart_cart"] = $new_session;

			$return = array(
				'items' => $_SESSION["maxcart_cart"],
				'zipcode' => isset( $_SESSION["zipcode"] ) ? $_SESSION["zipcode"] : ''
			);

			echo json_encode( $return );
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

		$current_cart = $_SESSION["maxcart_cart"];
		$updated_cart = array();

		foreach ( $current_cart as $product ) {
			if ( $product['id'] === $_POST['product_id'] ) {
				$product['qty']        = $_POST['product_qty'];
				$product['item_total'] = floatval( $product['price'] ) * floatval( $product['qty'] );

				if ( $product['qty'] !== '0' ) {
					array_push( $updated_cart, $product );
				}
			} else {
				array_push( $updated_cart, $product );
			}
		}

		$_SESSION["maxcart_cart"] = $updated_cart;

		$return = array(
			'items' => $_SESSION["maxcart_cart"],
			'zipcode' => isset( $_SESSION["zipcode"] ) ? $_SESSION["zipcode"] : ''
		);

		echo json_encode( $return );
		die();
	}
}

$maxQuickCart = new maxCartQuickCart;
