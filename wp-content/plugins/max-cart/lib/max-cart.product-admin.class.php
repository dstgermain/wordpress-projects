<?php

if (strpos($_SERVER['REQUEST_URI'], basename(__FILE__)) !== false) {
	die();
}

/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/1/15
 * Time: 10:30 PM
 */
class maxCartProductAdmin extends maxCart {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_product_side_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_product_side_metaboxes' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_product_main_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_product_main_metaboxes' ) );
	}

	private $main_meta_keys = array(
		parent::P_WEIGHT_KEY   => 'productWeight',
		parent::P_LENGTH_KEY   => 'productLength',
		parent::P_WIDTH_KEY    => 'productWidth',
		parent::P_HEIGHT_KEY   => 'productHeight',
		parent::P_FLATRATE_KEY => 'productFlatRate'
	);

	private $side_meta_keys = array(
		parent::P_COMPANY_KEY    => 'companyID',
		parent::P_PRICE_KEY      => 'productPrice',
		parent::P_INSTORE_KEY    => 'productInStore',
		parent::P_SKU_KEY        => 'productSku',
		parent::P_STOCK_KEY      => 'productStock'
	);

	/**
	 * Adds function for product price metabox callback
	 * @author Daniel St. Germain
	 */
	public function add_product_side_metaboxes() {
		add_meta_box( 'product-company', 'Company', array(
			$this,
			'display_product_company_metabox'
		), parent::MAX_CART_PRODUCT, 'side', 'core' );
		add_meta_box( 'product-price', 'Price', array(
			$this,
			'display_product_price_metabox'
		), parent::MAX_CART_PRODUCT, 'side', 'core' );
		add_meta_box( 'product-stock', 'Stock', array(
			$this,
			'display_product_stock_metabox'
		), parent::MAX_CART_PRODUCT, 'side', 'core' );
	}

	/**
	 * Adds function for displaying product companies metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_company_metabox() {
		global $post;

		$args = array(
			'post_type' => parent::MAX_CART_COMPANY,
			'orderby'   => 'title',
			'order'     => 'DESC',
		);

		$companies = new WP_Query( $args );

		if ( $companies->have_posts() ) :

			$saved_company = get_post_meta($post->ID, parent::P_COMPANY_KEY, true);

			echo '<p><small>Choose the Products Company from the dropdown below. This field can be left blank.</small></p>';

			echo '<select name="companyID" id="companyID">';
			echo '<option value="">-- select a company --</option>';

			foreach ( $companies->posts as $company ) :
				$is_selected = false;
				if ( $company->ID === intval( $saved_company ) ) {
					$is_selected = true;
				};
				echo '<option value="' . $company->ID . '" ' . ( $is_selected ? 'selected' : '' ) . '>' . $company->post_title . '</option>';
			endforeach;

			echo '</select>';

		else :
			echo '<span>No Companies Available</span>';
		endif;
	}

	/**
	 * Adds function for displaying product price & taxes metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_price_metabox() {
		global $post;

		$price = get_post_meta($post->ID, parent::P_PRICE_KEY, true);
		$instore = get_post_meta($post->ID, parent::P_INSTORE_KEY, true) === 'on' ? 'checked': '';

		?>
		<table>
			<tr>
				<td><label for="productPrice">Price:</label></td>
				<td><input type="text" name="productPrice" value="<?php echo $price; ?>"/></td>
			</tr>
			<tr>
				<td><label for="productInStore">Only Available in Store</label></td>
				<td><input type="checkbox" name="productInStore" <?php echo $instore ?>/></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Adds function for displaying product stock metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_stock_metabox() {
		global $post;

		$sku = get_post_meta($post->ID, parent::P_SKU_KEY, true);
		$stock = get_post_meta($post->ID, parent::P_STOCK_KEY, true);

		?>
		<table>
			<tr>
				<td><label for="productSku">SKU:</label></td>
				<td><input type="text" name="productSku" value="<?php echo $sku; ?>"/></td>
			</tr>
			<tr>
				<td colspan="2">
					<p>
						<small>If the product has limited stock enter the limit below. Leave blank for an unlimited
							amount.
						</small>
					</p>
				</td>
			</tr>
			<tr>
				<td><label for="productStock">Stock:</label></td>
				<td><input type="text" name="productStock" value="<?php echo $stock; ?>"/></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Save sidebar metabox content
	 * @author Daniel St. Germain
	 */
	public function save_product_side_metaboxes() {
		global $post;

		// product_meta_box_nonce is located in the display_product_gallery_metabox() function.
		if ( !wp_verify_nonce($_POST['product_meta_box_nonce'], 'maxcart_product_metabox_nonce') ) {
			return $post->ID;
		}

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post->ID;
		}

		if ( !current_user_can('edit_post') ) {
			return $post->ID;
		}

		foreach ( $this->side_meta_keys as $meta_key => $post_key ) {
			if ( isset( $_POST[$post_key] ) ) {
				$sanitized = sanitize_text_field( $_POST[$post_key] );
				update_post_meta( $post->ID, $meta_key, $sanitized );
			}
		}
	}

	/**
	 * add main metaboxes
	 * @author Daniel St. Germain
	 */
	public function add_product_main_metaboxes() {
//		add_meta_box( 'product-variation', 'Variations', array(
//			$this,
//			'display_product_variation_metabox'
//		), parent::MAX_CART_PRODUCT, 'normal', 'core' );

		add_meta_box( 'product-gallery', 'Gallery', array(
			$this,
			'display_product_gallery_metabox'
		), parent::MAX_CART_PRODUCT, 'normal', 'core' );

		add_meta_box( 'product-delivery', __('Delivery'), array(
			$this,
			'display_product_delivery_metabox'
		), parent::MAX_CART_PRODUCT, 'normal', 'core' );

	}

	/**
	 * Display variation metabox
	 * @author Daniel St. Germain
	 */
	function display_product_variation_metabox() {
		?>

	<?php
	}

	/**
	 * Display gallery metabox
	 * @author Daniel St. Germain
	 */
	function display_product_gallery_metabox() {
		global $post;
		if (isset($post->ID)) {
			$gallery_info = get_post_meta( $post->ID, parent::P_GALLERY_KEY, true );
			if ( $gallery_info ) {
				$ids = implode( ',', get_post_meta( $post->ID, parent::P_GALLERY_KEY, true ) );
			}
		}

		wp_nonce_field('maxcart_product_metabox_nonce', 'product_meta_box_nonce');

		echo do_shortcode( '[gallery ids="' . $ids . '"]' ); ?>

		<input id="productGalleryIds" class="js-maxcart-product-ids" type="hidden" name="productGalleryIds" value="<?php echo $ids; ?>" />
		<input id="manageGallery" class="button js-maxcart-manage-gallery" title="Manage gallery" type="button" value="Manage gallery" />

		<?php
	}

	/**
	 * Adds function for displaying delivery metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_delivery_metabox() {
		global $post;

		$weight = get_post_meta($post->ID, parent::P_WEIGHT_KEY, true);
		$length = get_post_meta($post->ID, parent::P_LENGTH_KEY, true);
		$width = get_post_meta($post->ID, parent::P_WIDTH_KEY, true);
		$height = get_post_meta($post->ID, parent::P_HEIGHT_KEY, true);
		$flatrate = get_post_meta($post->ID, parent::P_FLATRATE_KEY, true);

		?>
		<table class="full-width">
			<tr>
				<td colspan="2"><p><strong><?php echo __( 'Shipping based on weight.', 'max_cart_textdomain' );?></strong></p></td>
			</tr>
			<tr>
				<td style="width:172px;"><label><?php echo __( 'Weight (lbs)', 'max_cart_textdomain' );?></label></td>
				<td><input type="text" name="productWeight" id="productWeight" value="<?php echo $weight; ?>"/></td>
			</tr>
<!--			<tr>-->
<!--				<td style="width:172px;"><label>--><?php //echo __( 'Dimensions (inches)', 'max_cart_textdomain' );?><!--</label></td>-->
<!--				<td>-->
<!--					<input type="text" name="productLength" id="productLength" placeholder="L" class="product-dimension" value="--><?php //echo $length; ?><!--"/>-->
<!--					<strong> X </strong>-->
<!--					<input type="text" name="productWidth" id="productWidth" placeholder="W" class="product-dimension" value="--><?php //echo $width; ?><!--"/>-->
<!--					<strong> X </strong>-->
<!--					<input type="text" name="productHeight" id="productHeight" placeholder="H" class="product-dimension" value="--><?php //echo $height; ?><!--"/>-->
<!--				</td>-->
<!--			</tr>-->
			<tr>
<!--				<td colspan="2"><p><strong>--><?php //echo __( 'Flat Rate Shipping.', 'max_cart_textdomain' );?><!--</strong></p></td>-->
			</tr>
			<tr>
<!--				<td style="width:172px;"><label>--><?php //echo __( 'Flat Rate', 'max_cart_textdomain' );?><!--</label></td>-->
<!--				<td><input type="text" name="productFlatRate" id="productFlatRate" value="--><?php //echo $flatrate; ?><!--"/></td>-->
			</tr>
		</table>
	<?php
	}

	/**
	 * saving main product metaBoxes
	 * @return int
	 */
	public function save_product_main_metaboxes() {
		global $post;

		if ( !wp_verify_nonce($_POST['product_meta_box_nonce'], 'maxcart_product_metabox_nonce') ) {
			return $post->ID;
		}

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post->ID;
		}

		if (!current_user_can('edit_post')) {
			return $post->ID;
		}

		// Gallery is a special case.
		$gallery = isset( $_POST['productGalleryIds'] ) ? explode( ',', sanitize_text_field( $_POST['productGalleryIds'] ) ) : null;
		if ($gallery) {
			update_post_meta( $post->ID, parent::P_GALLERY_KEY, $gallery );
		}

		foreach ( $this->main_meta_keys as $meta_key => $post_key ) {
			if ( isset( $_POST[$post_key] ) ) {
				$sanitized = sanitize_text_field( $_POST[$post_key] );
				update_post_meta( $post->ID, $meta_key, $sanitized );
			}
		}
	}


}

$maxCartProductAdmin = new maxCartProductAdmin;
