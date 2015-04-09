<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/22/15
 * Time: 8:29 PM
 */
$maxCartProductArchive = new maxCartProductArchive;
?>
<div class="max-product-wrapper col-sm-12">
	<ul class="max-product-breadcrumbs list-inline">
		<?php echo $maxCartProductArchive->breadcrumbs; ?>
	</ul>
	<div class="clearfix max-filters">
		<div class="margin-bottom_15">
			<?php wp_nonce_field( 'maxcart_ajax', 'verify_maxcart_ajax' ); ?>
			<select name="orderBy" id="orderBy" class="js-max-select js-max-orderby hidden" data-type="orderBy">
				<option value="price:ASC">Price Ascending</option>
				<option value="price:DESC">Price Descending</option>
				<option value="name:ASC">A - Z</option>
				<option value="name:DESC">Z - A</option>
			</select>
		</div>
	</div>
	<?php if ( $maxCartProductArchive->products->have_posts() ) : ?>
	<div class="row product-listing">
		<?php while ( $maxCartProductArchive->products->have_posts() ) :
			$maxCartProductArchive->products->the_post(); $maxCartProduct = new maxCartProduct(); ?>

			<?php include( WP_PLUGIN_DIR . '/max-cart/templates/product-list-single.php' ); ?>

		<?php endwhile; ?>
	</div>
	<?php if ( $maxCartProductArchive->products->found_posts > 12 ) : ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<button class="max-load-more js-max-load-more">Load More Results</button>
		</div>
	</div>
	<?php endif; ?>
	<?php endif; ?>
</div>
