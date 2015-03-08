<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/1/15
 * Time: 10:30 PM
 */

class maxCartProductAdmin extends maxCart {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_product_side_metaboxes' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_product_main_metaboxes' ) );
	}

	/**
	 * Adds function for product price metabox callback
	 * @author Daniel St. Germain
	 */
	public function add_product_side_metaboxes() {
		add_meta_box( 'product-company', 'Company', array(
			$this,
			'display_product_company_metabox'
		), maxCart::MAX_CART_PRODUCT, 'side', 'core' );
		add_meta_box( 'product-price', 'Price', array(
			$this,
			'display_product_price_metabox'
		), maxCart::MAX_CART_PRODUCT, 'side', 'core' );
		add_meta_box( 'product-stock', 'Stock', array(
			$this,
			'display_product_stock_metabox'
		), maxCart::MAX_CART_PRODUCT, 'side', 'core' );
	}

	/**
	 * Adds function for displaying product companies metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_company_metabox() {

		$args = array(
			'post_type' => maxCart::MAX_CART_COMPANY,
			'orderby' => 'title',
			'order'   => 'DESC',
		);

		$companies = new WP_Query( $args );

		if ($companies-have_posts()) :

			echo '<p><small>Choose the Products Company from the dropdown below. This field can be left blank.</small></p>';

			echo '<select name="companyID" id="companyID">';
			echo '<option value="">-- select a company --</option>';

			foreach ($companies->posts as $company) :
				echo '<option value="' . $company->ID . '">' . $company->post_title . '</option>';
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
		?>
		<table>
			<tr>
				<td><label for="productPrice">Price:</label></td>
				<td><input type="text" name="productPrice"/></td>
			</tr>
			<tr>
				<td colspan="2"><p><small>If this product is taxable add the taxable value (currency) below.</small></p></td>
			</tr>
			<tr>
				<td><label for="productPrice">Tax Exempt:</label></td>
				<td><input type="checkbox" name="productTaxExempt" data-maxcart-show="#tax-price" checked/></td>
			</tr>
			<tr id="tax-price" class="hidden">
				<td><label for="productPrice">Taxable Price:</label></td>
				<td><input type="text" name="productTax"/></td>
			</tr>
		</table>
        <?php
	}

	/**
	 * Adds function for displaying product stock metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_stock_metabox() {
		?>
		<table>
			<tr>
				<td><label for="productSku">SKU:</label></td>
				<td><input type="text" name="productSku"/></td>
			</tr>
			<tr>
				<td colspan="2"><p><small>If the product has limited stock enter the limit below. Leave blank for an unlimited amount.</small></p></td>
			</tr>
			<tr>
				<td><label for="productStock">Stock:</label></td>
				<td><input type="text" name="productStock"/></td>
			</tr>
		</table>
	<?php
	}

	public function add_product_main_metaboxes() {
		add_meta_box( 'product-gallery', 'Gallery', array(
			$this,
			'display_product_gallery_metabox'
		), maxCart::MAX_CART_PRODUCT, 'normal', 'core' );
		add_meta_box( 'product-delivery', 'Delivery', array(
			$this,
			'display_product_delivery_metabox'
		), maxCart::MAX_CART_PRODUCT, 'normal', 'core' );
	}

	/**
	 * Adds function for displaying gallery metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_gallery_metabox() {
		?>

	<?php
	}

	/**
	 * Adds function for displaying delivery metabox
	 * @author Daniel St. Germain
	 */
	public function display_product_delivery_metabox() {
		?>

	<?php
	}


}

$maxCartProduct = new maxCartProductAdmin;
