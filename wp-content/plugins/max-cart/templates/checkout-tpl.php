<?php
$paypal_use_sandbox = true;
$paypal_email = 'info@danstgermain.com';
$paypal_currency_code = 'USD';
$paypal_lc = 'US';
$paypal_weight_unit = 'lbs';

if ( $paypal_use_sandbox ) {
	$paypal_request = "https://www.sandbox.paypal.com/cgi-bin/webscr";
} else {
	$paypal_request = "https://www.paypal.com/cgi-bin/webscr";
}
?>
<?php wp_nonce_field( 'maxcart', 'verify_maxcart' ); ?>
<h2>Checkout</h2>
<hr>
<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="business" value="info@danstgermain.com">
	<input type="hidden" name="item_name" value="Item Name">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="amount" value="0.00">
	<input type="image" src="http://www.paypal.com/en_US/i/btn/x-click-but01.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>
<div class="cart-area" data-bind="css: {'processing': processing()}">
	<div class="max-checkout">
		<form name="max-checkout-form" action="<?php echo $paypal_request; ?>" autocomplete="off" class="js-form-validation">
			<h4 class="margin-bottom_15">Customer Information</h4>
			<div class="row">
				<div class="col-sm-6 form-group">
					<label for="first_name">First Name</label>
					<input id="first_name" name="first_name" class="form-control" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
				<div class="col-sm-6 form-group">
					<label for="last_name">Last Name</label>
					<input id="last_name" name="last_name" class="form-control" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6 form-group">
					<label for="phone">Phone Number</label>
					<input id="phone" name="phone" class="form-control" value="" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
				<div class="col-sm-6 form-group">
					<label for="email">Email</label>
					<input id="email" name="email" class="form-control" value="" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
			</div>
			<hr/>
			<h4 class="margin-bottom_15">Shipping Details</h4>
			<div class="form-group">
				<label for="address1">Address</label>
				<input id="address1" name="address1" class="form-control margin-bottom_15" value="" type="text" data-bind="attr: {disabled: processing()}" required/>
				<input id="address2" name="address2" class="form-control margin-bottom_15" value="" type="text" data-bind="attr: {disabled: processing()}"/>
				<input id="address3" name="address3" class="form-control margin-bottom_15" value="" type="text" data-bind="attr: {disabled: processing()}"/>
			</div>
			<div class="row">
				<div class="col-sm-6 form-group">
					<label for="zip">Zipcode</label>
					<input id="zip" name="zip" class="form-control" value="" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
				<div class="col-sm-6 form-group">
					<label for="city">City</label>
					<input id="city" name="city" class="form-control" value="" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6 form-group">
					<label for="state">State</label>
					<input id="state" name="state" class="form-control" value="" type="text" data-bind="attr: {disabled: processing()}" required/>
				</div>
				<div class="col-sm-6 form-group">
					<label for="country">Country</label>
					<input id="country" name="country" class="form-control" type="text" value="United States" disabled required/>
				</div>
			</div>
			<div class="form-group">
				<label for="additional">Additional Information</label>
				<textarea name="additional" id="additional" class="form-control" data-bind="attr: {disabled: processing()}"></textarea>
			</div>
			<hr/>
			<div class="row">
				<div class="col-sm-8">
					<table class="table table-stripped">
						<thead>
						<tr>
							<th>Product(s)</th>
							<th>Price</th>
							<th class="text-center">QTY</th>
							<th>Total</th>
						</tr>
						</thead>
						<tbody data-bind="foreach: items">
						<tr>
							<td>
								<a data-bind="text: name, attr: { href: url }"></a>
							</td>
							<td data-bind="currency: price"></td>
							<td data-bind="text: qty" class="text-center"></td>
							<td data-bind="currency: item_total"></td>
						</tr>
						</tbody>
						<tbody data-bind="ifnot: hide_cart">
						<tr>
							<td colspan="4" class="text-center">Your cart is empty! <a href="/products">Go back to products.</a> </td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="col-sm-4">
					<h4 class="margin-bottom_15">Cart Totals</h4>
					<table class="table">
						<tr>
							<td>Items Total:</td>
							<td data-bind="text: items_total" class="text-right"></td>
						</tr>
						<tr>
							<td>Shipping total:</td>
							<td data-bind="currency: shipping_rate" class="text-right"></td>
						</tr>
						<tr>
							<td>Cart total:</td>
							<td class="text-right" data-bind="currency: cart_total()"></td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="cmd" value="_cart">
			<input name="upload" id="upload" type="hidden" value="1" />

			<!-- TODO: generate order number -->
			<input name="custom" id="custom" type="hidden" value="1324" />
			<input name="business" id="business" type="hidden" value="<?php echo str_replace( '"', '&quot;', $paypal_email ); ?>" />
			<input name="currency_code" id="currency_code" type="hidden" value="<?php echo $paypal_currency_code; ?>" />
			<input name="handling_cart" id="handling_cart" type="hidden" data-bind="value: shipping_rate" />
			<!--		<input name="discount_amount_cart" id="discount_amount_cart" type="hidden" value="" />-->

			<!-- TODO: Get Shipping weight -->
			<input name="weight_cart" id="weight_cart" type="hidden" value="1" />
			<input name="no_shipping" id="no_shipping" type="hidden" value="1" />
			<input name="amount" id="amount" type="hidden" data-bind="value: items_total" />
			<input name="lc" id="lc" type="hidden" value="<?php echo $paypal_lc; ?>" />
			<input name="rm" id="rm" type="hidden" value="2" />
			<input name="notify_url" id="notify_url" type="hidden" value="<?php echo WP_PLUGIN_DIR; ?>/max-cart/lib/paypal_complete.php" />
			<input type="hidden" name="return" value="/thank_you?ec_page=checkout_success&order_id=<?php //$this->order_id ?>" />
			<input type="hidden" name="cancel_return" value="/cart?cancel_order=<?php //$this->order_id ?>" />

			<div class="products" data-bind="foreach: items">
				<input data-bind="
				attr: {
					name: 'item_name_' + ($index() + 1),
					id: 'item_name_' + ($index() + 1)
				},
				value: name" type="hidden" />
				<input data-bind="
				attr: {
					name: 'amount_' + ($index() + 1),
					id: 'amount_' + ($index() + 1)
				},
				value: price" type="hidden" />
				<input data-bind="
				attr: {
					name: 'quantity_' + ($index() + 1),
					id: 'quantity_' + ($index() + 1)
				},
				value: qty" type="hidden" />
				<input data-bind="
				attr: {
					name: 'shipping_' + ($index() + 1),
					id: 'shipping_' + ($index() + 1)
				}" value="0.00" type="hidden" />
				<input data-bind="
				attr: {
					name: 'shipping2_' + ($index() + 1),
					id: 'shipping2_' + ($index() + 1)
				}" value="0.00" type="hidden" />
			</div>
		</form>
		<div data-bind="if: shipping_rate">
			<button class="btn btn-success margin-bottom_60 js-submit-checkout-form">Checkout with Paypal</button>
		</div>
		<div data-bind="ifnot: shipping_rate">
			<button class="btn btn-success margin-bottom_60 js-calculate-shipping">Calculate Shipping</button>
		</div>
	</div>
</div>
