<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 4/9/15
 * Time: 1:38 PM
 */

if ( ! class_exists('maxCartOrders') ) {
	define(ADMIN_URL, admin_url()); // Helper

	class maxCartOrders extends maxCart {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'maxcart_orders' ) );
		}

		public function maxcart_orders() {
			add_submenu_page( 'edit.php?post_type=maxcart_product', 'Orders', 'Orders', 'edit_posts', 'maxcart-orders', array( $this, 'maxcart_orders_page' ) );
		}

		public function maxcart_orders_page() {
			if($_GET['order']) {
				$this->maxcart_order();
			} else {
				$this->maxcart_orders_list();
			}
		}

		private function maxcart_order() {
			$args = array(
				'name' => $_GET['order'],
				'post_type' => parent::MAX_CART_ORDER,
				'post_status' => 'private',
				'posts_per_page' => 1,
				'caller_get_posts'=> 1
			);
			$query = new WP_Query($args);
			$order_id = $query->post->ID;

			$meta = get_post_meta( $order_id );

			if ($order_id) { ?>
				<h2>Order Number: <strong><?php echo $_GET['order']; ?></strong></h2>
				<h2>Payment Status: <strong><?php echo $meta['_maxcart_order_approved'][0]; ?></strong></h2>
				<?php if (isset($meta['_maxcart_order_pending_reason'])) :?>
					<p><?php echo $meta['_maxcart_order_pending_reason'][0]; ?></p>
				<?php endif; ?>
				<table class="widefat">
					<thead>
					<tr>
						<th colspan="4"><strong>Customer Information</strong></th>
					</tr>
					</thead>
					<tr>
						<td width="175">First Name:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_firstname'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Last Name:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_lastname'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Email:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_email'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Phone Number:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_phone'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Address Line 1:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_address1'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Address Line 2:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_address2'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Address Line 3:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_address3'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">City:</td>
						<td><?php echo $meta['_maxcart_order_city'][0]; ?></td>
						<td width="175">State:</td>
						<td><?php echo $meta['_maxcart_order_state'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Zipcode:</td>
						<td><?php echo $meta['_maxcart_order_zip'][0]; ?></td>
						<td width="175">Country:</td>
						<td><?php echo $meta['_maxcart_order_country'][0]; ?></td>
					</tr>
					<tr>
						<td width="175">Notes:</td>
						<td colspan="3"><?php echo $meta['_maxcart_order_additional'][0]; ?></td>
					</tr>
				</table>
				<br/><br/>
				<table class="widefat">
					<thead>
					<tr>
						<th colspan="5"><strong>Order Information</strong></th>
					</tr>
					<tr>
						<td>Product Number</td>
						<td>Product Name</td>
						<td>QTY</td>
						<td>Price</td>
						<td>Total</td>
					</tr>
					</thead>
					<tbody>
					<?php $products = unserialize($meta['_maxcart_order_items'][0]); ?>
					<?php foreach ( $products as $product ) : ?>
					<?php $prod = get_post( intval( $product['id'] ) ); $prod_meta = get_post_meta( intval( $product['id'] ) ); ?>
					<tr>
						<td><?php echo $prod_meta['_maxcart_product_sku'][0]; ?></td>
						<td><?php echo $prod->post_title; ?></td>
						<td><?php echo $product['qty'] ?></td>
						<td><?php echo $prod_meta['_maxcart_product_price'][0]; ?></td>
						<td><?php echo floatval( $prod_meta['_maxcart_product_price'][0] ) * intval( $product['qty'] ); ?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<br/><br/>
				<strong>Items total:</strong> <?php echo $meta['_maxcart_order_items_total'][0]; ?>
				<br/>
				<strong>Shipping total:</strong> <?php echo $meta['_maxcart_order_shipping_total'][0]; ?>
				<hr/>
				<strong>Invoice Total:</strong> <?php echo floatval( $meta['_maxcart_order_shipping_total'][0] ) + floatval( $meta['_maxcart_order_items_total'][0] ); ?>
<!--				<p><input name="update_order" type="submit" class="button button-primary button-large" id="update_order" value="Update"></p>-->
			<?php } else {
				echo 'Order ' . $order_id . ' was not found.';
			}
		}

		private function maxcart_orders_list() {
			$args        = array(
				'posts_per_page' => 20,
				'offset'         => 0,
				'orderby'        => 'post_date',
				'order'          => 'DESC',
				'post_type'      => maxCart::MAX_CART_ORDER,
				'post_status'    => 'private'
			);
			$posts_array = get_posts( $args );
			?>
			<div class="wrap">
				<div id="icon-users" class="icon32"></div>
				<h2>Orders</h2>

				<p>Below is a list of all orders.</p>
				<small>Please double check PayPal before shipping items.</small>

				<table class="widefat">
					<thead>
					<tr>
						<th>Order Number</th>
						<th>Date</th>
						<th>Customer</th>
						<th>Email</th>
						<th>Items (QTY &times; SKU)</th>
						<th>Purchase Total</th>
						<th>PayPal Approval</th>
<!--						<th>Delete</th>-->
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $posts_array as $order ) : $meta = get_post_meta( $order->ID ); ?>
						<tr valign="middle">
							<td><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>&order=<?php echo $order->post_title; ?>"><?php echo $order->post_title; ?></a></td>
							<td><?php echo $order->post_date; ?></td>
							<td><?php echo $meta['_maxcart_order_firstname'][0] . ' ' . $meta['_maxcart_order_lastname'][0]; ?></td>
							<td><?php echo $meta['_maxcart_order_email'][0]; ?></td>
							<td>
								<?php
								$products = unserialize( $meta['_maxcart_order_items'][0] );
								if ($products) {
									foreach ( $products as $product ) {
										// TODO: add amount ( 2 X SKU )
										$sku = get_post_meta( intval( $product['id'] ), maxCart::P_SKU_KEY, true );
										echo $product['qty'] . ' &times; <a href="' . get_permalink( intval( $product['id'] ) ) . '" target="_blank">' . $sku . '</a>, ';
									}
								}
								?>
							</td>
							<td>
								$<?php echo floatval( $meta['_maxcart_order_items_total'][0] ) + floatval( $meta['_maxcart_order_shipping_total'][0] ); ?></td>
							<td>
								<?php echo $meta['_maxcart_order_approved'][0]; ?>
							</td>
<!--							<td><input name="delete_order" type="submit" class="button button-primary button-large" id="delete_order" value="x"></td>-->
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php
		}
	}

	new maxCartOrders();
}
