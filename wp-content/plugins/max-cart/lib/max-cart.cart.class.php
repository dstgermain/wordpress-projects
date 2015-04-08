<?php
session_start();
if ( ! class_exists( 'maxCartView' ) ) {
	class maxCartView extends maxCart {
		public function __construct() {
			include_once 'third_party_lib/UPS-Shipping-Rate-Class/ups.rate.class.php';

			add_action( 'wp_ajax_maxcart_get_shipping', array( $this, 'maxcart_get_shipping' ) );
			add_action( 'wp_ajax_nopriv_maxcart_get_shipping', array( $this, 'maxcart_get_shipping' ) );
		}

		public function maxcart_get_shipping() {
			$response = array();
			$response['success'] = false;

			if ( ! isset( $_POST ) && ! isset( $_POST['_wpnonce'] ) ) {
				echo json_encode( $response );
				die();
			}

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'maxcart' ) ) {
				echo json_encode( $response );
				die();
			}

			if ( ! isset( $_SESSION["maxcart_cart"] ) || !isset( $_POST['zipcode'] ) ) {
				echo json_encode( $response );
				die();
			}

			$width = 0;
			$length = 0;
			$height = 0;
			$weight = 0;

			$weight_only = false;

			foreach ( $_SESSION["maxcart_cart"] as $product ) {
				if ( isset($product['id']) ) {
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

			$_SESSION["zipcode"] = $_POST['zipcode'];

			$objUpsRate = new UpsShippingQuote();

			$strDestinationZip = $_POST['zipcode'];
			$strMethodShortName = 'GND';
			$strPackageLength = !$weight_only ? $length : 0;
			$strPackageWidth = !$weight_only ? $width : 0;
			$strPackageHeight = !$weight_only ? $height : 0;
			$strPackageWeight = $weight;
			$boolReturnPriceOnly = true;

			$result = $objUpsRate->GetShippingRate(
				$strDestinationZip,
				$strMethodShortName,
				$strPackageLength,
				$strPackageWidth,
				$strPackageHeight,
				$strPackageWeight,
				$boolReturnPriceOnly
			);


			echo json_encode($result);

			die();
		}
	}

	new maxCartView();
}

