<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 4/16/15
 * Time: 8:08 PM
 */

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

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

		$this->send_confirmation_email($form['email'], $order_id);

		return array(
			'error' => false
		);
	}

	public function send_confirmation_email($to, $order_id) {
		require_once( WP_PLUGIN_DIR . '/max-cart/lib/third_party_lib/PHPMailer-master/PHPMailerAutoload.php' );

		$order_info = get_post($order_id);
		$order_meta = get_post_meta($order_id);

		$mail = new PHPMailer;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.sendgrid.net';                   // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'dstgermain772';                  // SMTP username
		$mail->Password = 'ilmc9263341!';                         // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted
		$mail->Port = 465;                                    //Set the SMTP port number - 587 for authenticated TLS
		$mail->setFrom('bbqbarn@tiac.net', 'Purchase Confirmation');     //Set who the message is to be sent from
		$mail->addAddress($to);  //Set an alternative reply-to address
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = 'BBQ Barn Checkout Confirmation';

		$mail->Body    = '<html><body>'.
		                 '<table width="100%" cellpadding="50" cellspacing="0" border="0" style="background:#4d0000;"><tr><td align="center">'.
		                 '<table width="550" cellpadding="10" cellspacing="0" border="0" style="border:1px solid #900;background:#ffffff;"><tr><td valign="middle">'.
		                 '<img src="' . get_option('siteurl') . '/wp-content/themes/roots-master/assets/img/logo1.png" width="200" style="width:200px;height:auto;"/></td>'.
		                 '<td style="font-family: Arial, sans-serif;font-size:12px;" align="right">'.
		                 get_option('street_address'). '<br/>'.
		                 get_option('city_address') . ', ' . get_option('state_address') . ' ' . get_option('zip_address') . '<br/>'.
		                 get_option('phone_1'). '<br/>'.
		                 get_option('phone_2').
		                 '</td>'.
		                 '</tr><tr><tr><td colspan="2"><hr style="border-bottom: 1px solid #900;margin-top:0;margin-bottom:0;"></td></tr><td colspan="2">'.

		                 '<h1 style="font-family: Arial, sans-serif;font-size:20px;margin-top: 5px;">Hello ' . $order_meta['_maxcart_order_firstname'][0] . ',</h1>'.
		                 '<p style="font-family: Arial, sans-serif;font-size:14px;">Thank you for your order from The BBQ Barn, here are the details for your records.</p>'.
		                 '<p style="font-family: Arial, sans-serif;font-size:14px;">Order Number: ' . $order_info->post_title . '</p>'.
		                 '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom: 1px solid #990000;">'.
		                 '<thead>'.
		                 '<tr><td style="border-bottom: 1px solid #900;font-weight:600;font-family: Arial, sans-serif;font-size:14px;">Item Name</td><td style="border-bottom: 1px solid #900;font-weight:600;font-family: Arial, sans-serif;font-size:14px;">Item QTY</td><td style="border-bottom: 1px solid #900;font-weight:600;font-family: Arial, sans-serif;font-size:14px;">Item Price</td><td style="border-bottom: 1px solid #900;font-weight:600;font-family: Arial, sans-serif;font-size:14px;">Item Total</td></tr>'.
		                 '</thead>'.
		                 '<tbody>';

		$items = unserialize($order_meta['_maxcart_order_items'][0]);

		if (!is_array($items)) {
			$items = array($items);
		}
		foreach($items as $item) {
			$item_post = get_post($item['id']);
			$item_meta = get_post_meta($item['id']);

			$mail->Body  .= '<tr><td style="border-bottom:1px solid #f0f0f0;padding-top:10px;padding-bottom:5px;font-family: Arial, sans-serif;font-size:14px;">'. $item_post->post_title . '</td>'.
		                    '<td style="border-bottom:1px solid #f0f0f0;padding-top:10px;padding-bottom:5px;font-family: Arial, sans-serif;font-size:14px;">'. $item['qty'] .'</td>'.
			                '<td style="border-bottom:1px solid #f0f0f0;padding-top:10px;padding-bottom:5px;font-family: Arial, sans-serif;font-size:14px;">$' . $item_meta['_maxcart_product_price'][0] . '</td>'.
			                '<td style="border-bottom:1px solid #f0f0f0;padding-top:10px;padding-bottom:5px;font-family: Arial, sans-serif;font-size:14px;">$' . (floatval($item_meta['_maxcart_product_price'][0]) * floatval($item['qty'])) . '</td></tr>';
		}

		$mail->Body      .= '</tbody>'.
		                 '</table>'.
		                 '<p style="font-family: Arial, sans-serif;font-size:14px;">Item total: $' . $order_meta['_maxcart_order_items_total'][0] . '</p>'.
		                 '<p style="font-family: Arial, sans-serif;font-size:14px;">Shipping total: $' . $order_meta['_maxcart_order_shipping_total'][0] . '</p>'.
		                 '<hr style="border-bottom: 1px solid #900;margin-top:5px;margin-bottom:5px;">'.
		                 '<p style="font-family: Arial, sans-serif;font-size:14px;font-weight:600;">Order total: $' . (floatval($order_meta['_maxcart_order_items_total'][0]) + floatval($order_meta['_maxcart_order_shipping_total'][0])) . '</p>'.
		                 '</td></tr><tr><td colspan="2"><small>*Purchases will not be processed until paypal payment confirmation.</small></td></tr></table>'.
		                 '</td></tr></table></body></html>';

		$mail->send();
	}
}
