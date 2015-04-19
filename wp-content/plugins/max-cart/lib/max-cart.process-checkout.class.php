<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 4/16/15
 * Time: 8:08 PM
 */

class maxCartProcessCheckout extends maxCart {
	public function generate_random_id() {
		$num = "0123456789";
		$id_array = array(); //remember to declare $pass as an array
		$num_length = strlen($num) - 1; //put the length -1 in cache

		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $num_length);
			$id_array[] = $num[$n];
		}

		$id = implode($id_array); //turn the array into a string

		$post_exists = $this->check_order_number($id);

		if (!$post_exists) {
			return $id;
		} else {
			$this->generate_random_id();
		}
	}

	private function check_order_number($id) {
		$post_exists = get_post( $id );
		return $post_exists ? true : false;
	}

	private function get_client_ip() {
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} else if ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}

		return $ipaddress;
	}

	public function process_order($order_id, $form) {
		// add the post meta to the order
		if ( !isset( $form['first_name'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your first name.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_firstname', wp_strip_all_tags($form['first_name']));

		if ( !isset( $form['last_name'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your last name.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_lastname', wp_strip_all_tags($form['last_name']));

		if ( !isset( $form['phone'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your phone number.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_phone', wp_strip_all_tags($form['phone']));

		if ( !isset( $form['email'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your email address.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_email', wp_strip_all_tags($form['email']));

		if ( !isset( $form['address1'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your street address.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_address1', wp_strip_all_tags($form['address1']));

		if ( isset( $form['address2'] ) ) {
			add_post_meta($order_id, '_maxcart_order_address2', wp_strip_all_tags($form['address2']));
		}

		if ( isset( $form['address3'] ) ) {
			add_post_meta($order_id, '_maxcart_order_address3', wp_strip_all_tags($form['address3']));
		}

		if ( !isset( $form['zip'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your shipping ZIP CODE.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_zip', wp_strip_all_tags($form['zip']));

		if ( !isset( $form['city'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your shipping city.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_city', wp_strip_all_tags($form['city']));

		if ( !isset( $form['state'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your shipping state.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_state', wp_strip_all_tags($form['state']));

		if ( !isset( $form['country'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Please enter your shipping Country.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_country', wp_strip_all_tags($form['country']));

		if ( isset( $form['additional'] ) ) {
			add_post_meta( $order_id, '_maxcart_order_additional', wp_strip_all_tags($form['additional']) );
		}

		if ( !isset( $_SESSION['maxcart_cart'] ) ||
		     !isset( $_SESSION['maxcart_cart']['items'] ) ||
		     !isset( $_SESSION['maxcart_cart']['items_total'] ) ||
		     !isset( $_SESSION['maxcart_cart']['shipping_total'] ) ) {
			wp_delete_post( $order_id, true );
			return array(
				'error' => true,
				'error_message' => 'Something went wrong, we couldn\'t retrieve your items and cart totals. Please try again later.'
			);
		}
		add_post_meta($order_id, '_maxcart_order_items_total', wp_strip_all_tags($_SESSION['maxcart_cart']['items_total']));
		add_post_meta($order_id, '_maxcart_order_shipping_total', wp_strip_all_tags($_SESSION['maxcart_cart']['shipping_total']));

		$items_array = array();
		foreach ( $_SESSION['maxcart_cart']['items'] as $product ) {
			$product_stock = get_post_meta( intval($product['id']), parent::P_STOCK_KEY, true );

			if ($product_stock === '0') {
				wp_delete_post( $order_id, true );
				return array(
					'error' => true,
					'error_message' => 'Product is out of stock: ' . $product['name']
				);
			}

			if ( $product_stock && isset( $product_stock ) && $product_stock !== '' ) {
				$updated_stock = intval( $product_stock ) - intval( $product['qty'] );
				update_post_meta(intval($product['id']), parent::P_STOCK_KEY, $updated_stock);
			}

			array_push($items_array, array(
				'id' => intval($product['id']),
				'qty' => intval($product['qty'])
			));
		}

		add_post_meta($order_id, '_maxcart_order_userip', $this->get_client_ip());

		add_post_meta($order_id, '_maxcart_order_items', $items_array);

		if (isset($form['status'])) {
			add_post_meta($order_id, '_maxcart_order_approved', $form['status']);
		} else {
			add_post_meta($order_id, '_maxcart_order_approved', 'Pending');
		}

		$this->send_confirmation_email(null, 'HEY');

		return array(
			'error' => false
		);
	}

	public function send_confirmation_email($to, $response) {
		$to      = 'dst.germain48@gmail.com';
		$subject = "This is subject";
		$message = "<b>This is HTML message.</b>";
		$message .= "<h1>This is headline.</h1>";
		$header = "From:abc@somedomain.com \r\n";
		$header .= "Cc:afgh@somedomain.com \r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/html\r\n";
		$retval = mail ($to,$subject,$message,$header);
		if( $retval == true )
		{
			echo "Message sent successfully...";
		}
		else
		{
			echo "Message could not be sent...";
		}
	}
}
