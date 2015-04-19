<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 4/13/15
 * Time: 6:08 PM
 */
if ( ! class_exists('maxCartPayPalIpn') ) {
	class maxCartPayPalIpn extends maxCart {
		public function __construct() {
			add_action('paypal_ipn_for_wordpress_txn_type_cart', array( $this, 'process_paypal_ipn' ), 10, 1);
		}

		public function process_paypal_ipn( $posted ) {
			if (isset($posted['business']) &&
			    $posted['business'] === 'info@danstgermain.com' &&
			    ( $posted['txn_type'] === 'express_checkout' || $posted['txn_type'] === 'cart' ) ) {
				if ( isset( $posted['custom'] ) ) {
					$id = $posted['custom'];

					$args = array(
						'name' => $id,
						'post_type' => parent::MAX_CART_ORDER,
						'post_status' => 'private',
						'posts_per_page' => 1,
						'caller_get_posts'=> 1
					);
					$query = new WP_Query($args);
					$order_id = $query->post->ID;

					$errors = array();

					if ($query) {
						$meta = get_post_meta( $order_id );

						if ($meta['_maxcart_order_approved'][0] === 'Completed') {
							return false;
						}

						// perform some checks on the order to ensure that the payment was correct.
						$total_reported_handling = isset( $posted['mc_handling'] ) ? $posted['mc_handling'] : 0;

						if (isset($meta['_maxcart_order_shipping_total'][0])) {
							if ($meta['_maxcart_order_shipping_total'][0] !== $total_reported_handling) {
								array_push($errors, 'Shipping total miss-match. Reported Shipping: ' . $posted['mc_handling']);
							}
						}

						if (floatval($meta['_maxcart_order_items_total'][0]) + floatval($meta['_maxcart_order_shipping_total'][0]) !== floatval( $posted['payment_gross']) ) {
							array_push($errors, 'Payment Total Missmatch: ' . $posted['payment_gross']);
						}

						if (!count($errors)) {
							update_post_meta( $order_id, '_maxcart_order_approved', $posted['payment_status'] );

							if (isset($posted['pending_reason'])) {
								switch ($posted['pending_reason']) {
									case 'address':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set yo allow you to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.' );
										break;
									case 'authorization':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'You set the payment action to Authorization and have not yet captured funds.' );
										break;
									case 'echeck':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because it was made by an eCheck that has not yet cleared.' );
										break;
									case 'intl':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.' );
										break;
									case 'multi-currency':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'You do not have a balance in the currency sent, and you do not have your profiles\'s Payment Receiving Preferences option set to automatically convert and accept this payment. As a result, you must manually accept or deny this payment.' );
										break;
									case 'order':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'You set the payment action to Order and have not yet captured funds.' );
										break;
									case 'paymentreview':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending while it is reviewed by PayPal for risk.' );
										break;
									case 'regulatory_review':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because PayPal is reviewing it for compliance with government regulations. PayPal will complete this review within 72 hours.' );
										break;
									case 'unilateral':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because it was made to an email address that is not yet registered or confirmed.' );
										break;
									case 'upgrade':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status before you can receive the funds. upgrade can also mean that you have reached the monthly limit for transactions on your account.' );
										break;
									case 'verify':
										update_post_meta( $order_id, '_maxcart_order_pending_reason', 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.' );
										break;
								}
							}

						} else {
							update_post_meta( $order_id, '_maxcart_order_errors', $errors );
						}

						if (isset($posted['memo'])) {
							$current = isset($meta['_maxcart_order_additional'][0]) ? $meta['_maxcart_order_additional'][0] : '';
							update_post_meta( $order_id, '_maxcart_order_additional', $current . ' -- Paypal Memo: ' . $posted['memo'] );
						}
					} else {

					}
				}
			}
		}
	}

	new maxCartPayPalIpn();
}
