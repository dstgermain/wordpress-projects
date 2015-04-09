<?php
session_start();
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
			foreach($this->ups_lib as $file) {
				include_once 'third_party_lib/php-ups-api/src/Ups/' . $file;
			}

			add_action( 'wp_ajax_maxcart_get_shipping', array( $this, 'maxcart_get_shipping' ) );
			add_action( 'wp_ajax_nopriv_maxcart_get_shipping', array( $this, 'maxcart_get_shipping' ) );
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

			if ( ! isset( $_SESSION["maxcart_cart"] ) || !isset( $_POST['zipcode'] ) ) {
				echo 'false';
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

			$rate = new Ups\Rate(
				'0CEB1D6A7A7D70F6',
				'dstgermain48',
				'7720463Ds'
			);

			$height = $weight_only ? $height : 0;
			$width = $weight_only ? $width : 0;
			$length = $weight_only ? $length : 0;

			try {
				$shipment = new \Ups\Entity\Shipment();

				$shipperAddress = $shipment->getShipper()->getAddress();
				$shipperAddress->setPostalCode('02134');

				$address = new \Ups\Entity\Address();
				$address->setPostalCode('02134');
				$shipFrom = new \Ups\Entity\ShipFrom();
				$shipFrom->setAddress($address);

				$shipment->setShipFrom($shipFrom);

				$shipTo = $shipment->getShipTo();
				$shipToAddress = $shipTo->getAddress();
				$shipToAddress->setPostalCode($_POST['zipcode']);

				$package = new \Ups\Entity\Package();
				$package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
				$package->getPackageWeight()->setWeight($weight);

				$dimensions = new \Ups\Entity\Dimensions();
				$dimensions->setHeight($height);
				$dimensions->setWidth($width);
				$dimensions->setLength($length);

				$unit = new \Ups\Entity\UnitOfMeasurement;
				$unit->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);

				$dimensions->setUnitOfMeasurement($unit);
				$package->setDimensions($dimensions);

				$shipment->addPackage($package);

				$response = $rate->getRate($shipment);

				echo $response->RatedShipment[0]->TotalCharges->MonetaryValue;
			} catch (Exception $e) {
				echo 'false';
			}

			die();
		}
	}

	new maxCartView();
}

