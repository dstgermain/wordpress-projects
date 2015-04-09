<?php wp_nonce_field( 'maxcart', 'verify_maxcart' ); ?>
<h2>Shopping Cart</h2>
<small>We only ship inside the US.</small>
<hr>
<div class="cart-area" data-bind="css: {'processing': processing()}">
	<table class="max-cart-view table table-stripped">
		<thead>
		<tr>
			<th>Product(s)</th>
			<th>QTY</th>
			<th>Price</th>
			<th>Total</th>
		</tr>
		</thead>
		<tbody data-bind="foreach: items">
		<tr>
			<td>
				<div class="item-image" data-bind="if: $data.thumbnail"><img data-bind="attr: { alt: name, src: thumbnail }"/></div>
				<a data-bind="text: name, attr: { href: url }"></a>
			</td>
			<td>
				<input type="hidden" id="product-id" data-bind="value: id"/>
				<input type="number" name="productQty" class="form-control qty-update js-maxcart-qty-update" id="productQty" maxlength="2" data-bind="value: qty, attr: {disabled: $parent.processing()}"/>
			</td>
			<td data-bind="currency: price"></td>
			<td data-bind="currency: item_total"></td>
		</tr>
		</tbody>
		<tbody data-bind="ifnot: hide_cart">
		<tr>
			<td colspan="4" class="text-center">Your cart is empty! <a href="/products">Go back to products.</a> </td>
		</tr>
		</tbody>
		<tfoot data-bind="if: hide_cart">
		<tr>
			<td colspan="2" class="shipping-group">
				<div class="form-group" data-bind="css: {'has-error' : shipping_error()}">
					<label class="control-label">Shipping:</label>
					<input type="text" class="form-control shipping-estimate" maxlength="5" placeholder="ZIP CODE" data-bind="value: zipcode, attr: {disabled: processing()}"/>
					<button class="btn btn-default js-shipping-estimate">Estimate</button>
					<span class="shipping-estimate-value" data-bind="currency: shipping_rate"></span>
					<div data-bind="if: shipping_error()">
						<div class="bg-danger text-danger">Something has gone wrong. Please double check your ZIP CODE</div>
					</div>
				</div>
			</td>
			<td class="text-right">Total:</td>
			<td data-bind="text: items_total"></td>
		</tr>
		</tfoot>
	</table>
	<a class="btn btn-success pull-right" href="/checkout">Checkout</a>
</div>
