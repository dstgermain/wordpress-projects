<?php

if ( ! class_exists( 'maxCartView' ) ) {
	class maxCartView extends maxCart {
		// Manual loading of UPS Lib
		private $ups_lib = array(
			'Ups.php',
			'Rate.php',
			'NodeInterface.php',
			'RequestInterface.php',
			'Request.php',
			'ResponseInterface.php',
			'Response.php',
			'Entity/Shipment.php',
			'Entity/Shipper.php',
			'Entity/Address.php',
			'Entity/ShipFrom.php',
			'Entity/ShipTo.php',
			'Entity/ShipmentServiceOptions.php',
			'Entity/CallTagARS.php',
			'Entity/Service.php',
			'Entity/Package.php',
			'Entity/PackagingType.php',
			'Entity/ReferenceNumber.php',
			'Entity/Dimensions.php',
			'Entity/UnitOfMeasurement.php',
			'Entity/PackageWeight.php',
			'Entity/PackageServiceOptions.php',
			'Entity/RateRequest.php',
			'Entity/PickupType.php',
			'Entity/RateResponse.php',
			'Entity/RatedPackage.php',
			'Entity/RatedShipment.php',
			'Entity/BillingWeight.php',
			'Entity/Charges.php'
		);

		public function __construct() {
			foreach ( $this->ups_lib as $file ) {
				include_once 'third_party_lib/php-ups-api/src/Ups/' . $file;
			}

			add_action( 'wp_ajax_maxcart_get_shipping', array( $this, 'maxcart_get_shipping' ) );
			add_action( 'wp_ajax_nopriv_maxcart_get_shipping', array( $this, 'maxcart_get_shipping' ) );

			if ( isset( $_GET['cancel_order'] ) ) {
				$this->cancel_order();
			}

			if ( isset( $_GET['process'] ) && $_GET['process'] === 'paypal_express' ) {
				$this->process_express();
			}

			if ( isset( $_GET["token"] ) && isset( $_GET["PayerID"] ) && isset($_SESSION['maxcart_cart']) && isset($_SESSION['maxcart_cart']['items']) ) {
				$this->do_express_checkout();
			}
		}

		public function maxcart_get_shipping() {

			if ( ! isset( $_POST ) && ! isset( $_POST['_wpnonce'] ) ) {
				echo 'false';
				die();
			}

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'maxcart' ) ) {
				echo 'false poo';
				die();
			}

			if ( ! isset( $_SESSION["maxcart_cart"] ) || ! isset( $_POST['zipcode'] ) ) {
				echo 'false';
				die();
			}

			$width  = 0;
			$length = 0;
			$height = 0;
			$weight = 0;

			$weight_only = false;

			foreach ( $_SESSION["maxcart_cart"]["items"] as $product ) {
				if ( isset( $product['id'] ) ) {
					$id          = intval( $product['id'] );
					$qty         = intval( $product['qty'] );
					$check_width = floatval( get_post_meta( $id, parent::P_WIDTH_KEY, true ) );
					$width       = $width + ( $check_width * $qty );
					$length      = $length + ( floatval( get_post_meta( $id, parent::P_LENGTH_KEY, true ) ) * $qty );
					$height      = $height + ( floatval( get_post_meta( $id, parent::P_HEIGHT_KEY, true ) ) * $qty );
					$weight      = $weight + ( floatval( get_post_meta( $id, parent::P_WEIGHT_KEY, true ) ) * $qty );

					if ( ! $weight_only && $check_width == 0 ) {
						$weight_only = true;
					}
				}
			}

			$rate = new Ups\Rate(
				'0CEB1D6A7A7D70F6',
				'dstgermain48',
				'7720463Ds'
			);

			$height = $weight_only ? $height : 0;
			$width  = $weight_only ? $width : 0;
			$length = $weight_only ? $length : 0;

			try {
				$shipment = new \Ups\Entity\Shipment();

				$shipperAddress = $shipment->getShipper()->getAddress();
				$shipperAddress->setPostalCode( '02134' );

				$address = new \Ups\Entity\Address();
				$address->setPostalCode( '02134' );
				$shipFrom = new \Ups\Entity\ShipFrom();
				$shipFrom->setAddress( $address );

				$shipment->setShipFrom( $shipFrom );

				$shipTo        = $shipment->getShipTo();
				$shipToAddress = $shipTo->getAddress();
				$shipToAddress->setPostalCode( $_POST['zipcode'] );

				$package = new \Ups\Entity\Package();
				$package->getPackagingType()->setCode( \Ups\Entity\PackagingType::PT_PACKAGE );
				$package->getPackageWeight()->setWeight( $weight );

				$dimensions = new \Ups\Entity\Dimensions();
				$dimensions->setHeight( $height );
				$dimensions->setWidth( $width );
				$dimensions->setLength( $length );

				$unit = new \Ups\Entity\UnitOfMeasurement;
				$unit->setCode( \Ups\Entity\UnitOfMeasurement::UOM_IN );

				$dimensions->setUnitOfMeasurement( $unit );
				$package->setDimensions( $dimensions );

				$shipment->addPackage( $package );

				$response = $rate->getRate( $shipment );

				if ( isset( $_SESSION["maxcart_cart"] ) ) {
					$_SESSION["maxcart_cart"]["shipping_total"] = $response->RatedShipment[0]->TotalCharges->MonetaryValue;
				}

				echo $response->RatedShipment[0]->TotalCharges->MonetaryValue;
			} catch ( Exception $e ) {
				echo 'false';
			}

			die();
		}

		public function cancel_order() {
			$order = $_GET['cancel_order'];

			$args     = array(
				'name'             => $order,
				'post_type'        => parent::MAX_CART_ORDER,
				'post_status'      => 'private',
				'posts_per_page'   => 1,
				'caller_get_posts' => 1
			);
			$query    = new WP_Query( $args );
			$order_id = $query->post->ID;

			wp_delete_post( $order_id, true );
		}

		public function process_express() {
			if ( ( ! isset( $_SESSION['maxcart_cart'] ) && ! isset( $_SESSION['maxcart_cart']['items'] ) ) || ! isset( $_POST['shipping_cost'] ) ) {
				return false;
			}

			$paypal_use_sandbox = true;
			$paypal_email       = 'info_api1.danstgermain.com';
			$paypal_password    = 'DRD8JFMNCXNC6VAM';
			$paypal_api_key     = 'Azblg4YAMFzmrInpK.67yO5C34C4ArM29RpuckJkh5SSH5yxDhpo.s2x';
			$paypalmode         = '';

			if ( $paypal_use_sandbox ) {
				$paypalmode = '.sandbox';
			}

			//Grand total including all tax, insurance, shipping cost and discount
			$grand_total = ( $_SESSION['maxcart_cart']['items_total'] + $_POST['shipping_cost'] );

			//Parameters for SetExpressCheckout, which will be sent to PayPal
			$p_data = '&METHOD=SetExpressCheckout' .
			          '&RETURNURL=' . urlencode( 'http://' . $_SERVER['HTTP_HOST'] . '/thankyou' ) .
			          '&CANCELURL=' . urlencode( 'http://' . $_SERVER['HTTP_HOST'] . '/cart' ) .
			          '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode( "SALE" );

			$item_num = 0;
			foreach ( $_SESSION['maxcart_cart']['items'] as $product ) {
				$p_data .= '&L_PAYMENTREQUEST_0_NAME' . $item_num . '=' . urlencode( $product['name'] ) .
				           '&L_PAYMENTREQUEST_0_NUMBER' . $item_num . '=' . urlencode( $product['sku'] ) .
				           '&L_PAYMENTREQUEST_0_AMT' . $item_num . '=' . urlencode( $product['price'] ) .
				           '&L_PAYMENTREQUEST_0_QTY' . $item_num . '=' . urlencode( $product['qty'] );

				$item_num ++;
			}

			$p_data .= '&PAYMENTREQUEST_0_SHIPTOZIP=' . $_POST['zipcode'] .
			           '&NOSHIPPING=0' . //set 1 to hide buyer's shipping address, in-case products that does not require shipping
			           '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode( $_SESSION['maxcart_cart']['items_total'] ) .
			           '&PAYMENTREQUEST_0_HANDLINGAMT=' . urlencode( $_POST['shipping_cost'] ) .
			           '&PAYMENTREQUEST_0_AMT=' . urlencode( $grand_total ) .
			           '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( 'USD' ) .
			           '&LOCALECODE=US' . //PayPal pages to match the language on your website.
			           '&ALLOWNOTE=1';


			//We need to execute the "SetExpressCheckOut" method to obtain paypal token
			$paypal               = new MyPayPal();
			$httpParsedResponseAr = $paypal->PPHttpPost( 'SetExpressCheckout', $p_data, $paypal_email, $paypal_password, $paypal_api_key, $paypalmode );

			//Respond according to message we receive from Paypal
			if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
				//Redirect user to PayPal store with Token received.
				$paypalurl           = 'https://www' . $paypalmode . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $httpParsedResponseAr["TOKEN"];
				$_SESSION['p_token'] = $httpParsedResponseAr["TOKEN"];
				echo '<script>window.location = "' . $paypalurl . '";</script>';
			}
		}

		public function do_express_checkout() {
			//we will be using these two variables to execute the "DoExpressCheckoutPayment"
			//Note: we haven't received any payment yet.
			$paypal_use_sandbox = true;
			$paypal_email       = 'info_api1.danstgermain.com';
			$paypal_password    = 'DRD8JFMNCXNC6VAM';
			$paypal_api_key     = 'Azblg4YAMFzmrInpK.67yO5C34C4ArM29RpuckJkh5SSH5yxDhpo.s2x';
			$paypalmode         = '';

			if ( $paypal_use_sandbox ) {
				$paypalmode = '.sandbox';
			}

			$token    = $_GET["token"];
			$payer_id = $_GET["PayerID"];

			if (urldecode($_SESSION['p_token']) !== $_GET["token"]) {
				return false;
			}

			//Grand total including all tax, insurance, shipping cost and discount
			$grand_total = ( $_SESSION['maxcart_cart']['items_total'] + $_POST['shipping_cost'] );

			$p_data = '&TOKEN=' . urlencode( $token ) .
			          '&PAYERID=' . urlencode( $payer_id ) .
			          '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode( "SALE" );

			$item_num = 0;

			foreach ( $_SESSION['maxcart_cart']['items'] as $product ) {
				$p_data .= '&L_PAYMENTREQUEST_0_NAME' . $item_num . '=' . urlencode( $product['name'] ) .
				           '&L_PAYMENTREQUEST_0_NUMBER' . $item_num . '=' . urlencode( $product['sku'] ) .
				           '&L_PAYMENTREQUEST_0_AMT' . $item_num . '=' . urlencode( $product['price'] ) .
				           '&L_PAYMENTREQUEST_0_QTY' . $item_num . '=' . urlencode( $product['qty'] );

				$item_num ++;
			}

			$p_data .= '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode( $_SESSION['maxcart_cart']['items_total'] ) .
			           '&PAYMENTREQUEST_0_HANDLINGAMT=' . urlencode( $_POST['shipping_cost'] ) .
			           '&PAYMENTREQUEST_0_AMT=' . urlencode( $grand_total ) .
			           '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( 'USD' ) .
			           '&LOCALECODE=US';

			//We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
			$paypal               = new MyPayPal();
			$httpParsedResponseAr = $paypal->PPHttpPost( 'DoExpressCheckoutPayment', $p_data, $paypal_email, $paypal_password, $paypal_api_key, $paypalmode );

			//Check if everything went ok..
			if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {
				$order_status = $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"];

				// we can retrive transection details using either GetTransactionDetails or GetExpressCheckoutDetails
				// GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
				$p_data               = '&TOKEN=' . urlencode( $token );
				$paypal               = new MyPayPal();
				$httpParsedResponseAr = $paypal->PPHttpPost( 'GetExpressCheckoutDetails', $p_data, $paypal_email, $paypal_password, $paypal_api_key, $paypalmode );

				if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] ) ) {

					$process_checkout = new maxCartProcessCheckout();

					$id = $process_checkout->generate_random_id();

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

					$pp_info = array();

					$pp_info['first_name'] = isset($httpParsedResponseAr["FIRSTNAME"]) ? urldecode($httpParsedResponseAr["FIRSTNAME"]) : 'First Name Not Available';
					$pp_info['last_name'] = isset($httpParsedResponseAr["LASTNAME"]) ? urldecode($httpParsedResponseAr["LASTNAME"]) : 'Last Name Not Available';
					$pp_info['phone'] = isset($httpParsedResponseAr["PHONE"]) ? urldecode($httpParsedResponseAr["PHONE"]) : 'Phone Not Available';
					$pp_info['email'] = isset($httpParsedResponseAr["EMAIL"]) ? urldecode($httpParsedResponseAr["EMAIL"]) : 'Email Not Available';
					$pp_info['address1'] = isset($httpParsedResponseAr["SHIPTOSTREET"]) ? urldecode($httpParsedResponseAr["SHIPTOSTREET"]) : 'Street Not Available';
					$pp_info['zip'] = isset($httpParsedResponseAr["SHIPTOZIP"]) ? urldecode($httpParsedResponseAr["SHIPTOZIP"]) : 'ZIP CODE Not Available';
					$pp_info['city'] = isset($httpParsedResponseAr["SHIPTOCITY"]) ? urldecode($httpParsedResponseAr["SHIPTOCITY"]) : 'City Not Available';
					$pp_info['state'] = isset($httpParsedResponseAr["SHIPTOSTATE"]) ? urldecode($httpParsedResponseAr["SHIPTOSTATE"]) : 'ZIP CODE Not Available';
					$pp_info['country'] = isset($httpParsedResponseAr["SHIPTOCOUNTRYCODE"]) ? urldecode($httpParsedResponseAr["SHIPTOCOUNTRYCODE"]) : 'ZIP CODE Not Available';
					$pp_info['status'] = $order_status ? $order_status : 'Pending';

					$response = $process_checkout->process_order($order_id, $pp_info);

					global $exp_cart;

					if ($response['error']) {
						$exp_cart = $response['error_message'];
					} else {
						$exp_cart = 'Success! We have received your payment and we\'ll Ship your items as soon as we can.';
						$_SESSION['maxcart_cart'] = null;
					}

					if ( 'Pending' === $order_status ) {
						$exp_cart .= '<div style="color:red">Transaction Complete, but payment is still pending! ' .
						             'You need to manually authorize this payment in your <a target="_new" href="http://www.paypal.com">Paypal Account</a></div>';
					}
				} else {
					global $exp_cart;
					$exp_cart = 'Something has gone wrong. Please call us to complete your order, or check with PayPal about any issue with your account.';
				}

			} else {
				global $exp_cart;
				$exp_cart = 'Something has gone wrong. Please call us to complete your order, or check with PayPal about any issue with your account.';
			}
		}
	}

	class MyPayPal {

		function PPHttpPost( $methodName_, $nvpStr_, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode ) {
			// Set up your API credentials, PayPal end point, and API version.
			$API_UserName  = urlencode( $PayPalApiUsername );
			$API_Password  = urlencode( $PayPalApiPassword );
			$API_Signature = urlencode( $PayPalApiSignature );

			$API_Endpoint = "https://api-3t" . $PayPalMode . ".paypal.com/nvp";
			$version      = urlencode( '109.0' );

			// Set the curl parameters.
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $API_Endpoint );
			curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

			// Turn off the server and peer verification (TrustManager Concept).
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );

			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POST, 1 );

			// Set the API operation, version, and API signature in the request.
			$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

			// Set the request as a POST FIELD for curl.
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );

			// Get response from the server.
			$httpResponse = curl_exec( $ch );

			if ( ! $httpResponse ) {
				exit( "$methodName_ failed: " . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
			}

			// Extract the response details.
			$httpResponseAr = explode( "&", $httpResponse );

			$httpParsedResponseAr = array();
			foreach ( $httpResponseAr as $i => $value ) {
				$tmpAr = explode( "=", $value );
				if ( sizeof( $tmpAr ) > 1 ) {
					$httpParsedResponseAr[ $tmpAr[0] ] = $tmpAr[1];
				}
			}

			if ( ( 0 == sizeof( $httpParsedResponseAr ) ) || ! array_key_exists( 'ACK', $httpParsedResponseAr ) ) {
				exit( "Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint." );
			}

			return $httpParsedResponseAr;
		}

	}

	new maxCartView();
}

