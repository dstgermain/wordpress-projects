<?php

/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 4/9/15
 * Time: 3:54 PM
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

if ( ! class_exists( 'maxCartCheckout' ) ) {
	class maxCartCheckout extends maxCart {
		public function __construct() {
			add_action( 'wp_ajax_maxcart_checkout_process', array( $this, 'maxcart_checkout_process' ) );
			add_action( 'wp_ajax_nopriv_maxcart_checkout_process', array( $this, 'maxcart_checkout_process' ) );
		}

		public function maxcart_checkout_process() {
			if ( ! isset( $_POST ) && ! isset( $_POST['_wpnonce'] ) ) {
				echo json_encode(array(
					'error' => true,
					'error_message' => 'Something went wrong, please try again later.'
				));
				die();
			}

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'maxcart' ) ) {
				echo json_encode(array(
					'error' => true,
					'error_message' => 'Something went wrong, please try again later.'
				));
				die();
			}

			if ( ! isset( $_POST['form'] ) ) {
				echo json_encode(array(
					'error' => true,
					'error_message' => 'Something went wrong, please try again later.'
				));
				die();
			}

			$process_checkout = new maxCartProcessCheckout();

			$id = $process_checkout->generate_random_id();

			$form = array();
			parse_str($_POST['form'], $form);

			// Create post object
			$my_post = array(
				'post_content'   => '',
			    'post_name'      => $id,
				'post_title'     => $id,
				'post_status'    => 'private',
				'post_type'      => parent::MAX_CART_ORDER,
				'post_author'    => 1,
				'ping_status'    => 'closed',
				'guid'           => false,
				'comment_status' => 'closed'
			);

            // Insert the post into the database
			wp_insert_post( $my_post );

			$args = array(
				'name' => $id,
				'post_type' => parent::MAX_CART_ORDER,
				'post_status' => 'private',
				'posts_per_page' => 1,
				'caller_get_posts'=> 1
			);
			$query = new WP_Query($args);
			$order_id = $query->post->ID;

			$response = $process_checkout->process_order($order_id, $form);

			$response['order_id'] = $id;

			if (!$response['error']) {
				$_SESSION['maxcart_cart'] = null;
			}

			echo json_encode($response);

			die();
		}
	}

	new maxCartCheckout();
}
