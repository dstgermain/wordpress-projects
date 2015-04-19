<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/14/15
 * Time: 7:02 PM
 */

the_post();
$maxCartProduct = new maxCartProduct();
?>
<div class="max-product-wrapper col-sm-12">
	<ul class="max-product-breadcrumbs list-inline"><?php echo $maxCartProduct->product_categories; ?></ul>
	<div class="row">
		<div class="col-sm-5 col-sm-offset-1">
			<div class="max-product-main">
				<?php if ( isset( $maxCartProduct->product_gallery_sizes[0] ) && !empty($maxCartProduct->product_gallery_sizes[0]['full']) ) : ?>

					<div class="max-product-main-image">
						<?php $first = true; $index = 0; foreach ($maxCartProduct->product_gallery_sizes as $img_array ) : ?>
							<a class="fancybox <?php echo $first ? 'active': ''; $first = false; ?>"
							   rel="group"
							   data-gallery-id="<?php echo $index; $index++; ?>"
							   href="<?php echo $img_array['full'][0]; ?>">
								<img src="<?php echo $img_array['large'][0]; ?>"
								     alt="<?php echo get_the_title(); ?>"
								     class="img-responsive"/>
							</a>
						<?php endforeach; ?>
					</div>

				<?php else : ?>
					<div class="max-product-main-image missing">
						<i class="fa fa-question-circle fa-4x"></i>
					</div>
				<?php endif; ?>

			</div>

			<div class="max-product-images">

				<?php if ( count( $maxCartProduct->product_gallery_sizes ) > 1 ) : $first = true; $index = 0; ?>
					<div class="max-product-gallery list-inline row">
						<?php foreach ($maxCartProduct->product_gallery_sizes as $img_array ) : ?>
							<div class="col-xs-4 col-sm-3">
								<img src="<?php echo $img_array['thumbnail'][0]; ?>"
								     alt="<?php echo get_the_title(); ?>"
								     data-gallery-id="<?php echo $index; $index++; ?>"
								     class="img-responsive js-max-gallery-image <?php echo $first ? 'active': ''; $first = false; ?>"/>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
			<!-- end product-images -->
		</div>

		<div class="max-product-info col-sm-4">
			<h1><?php echo get_the_title(); ?></h1>

			<?php if ($maxCartProduct->product_company && isset( $maxCartProduct->product_company->post_title ) ) : ?>
				<h4>by: <a href="<?php echo $maxCartProduct->product_company->guid; ?>"><?php echo $maxCartProduct->product_company->post_title;?></a></h4>
			<?php endif; ?>

			<h4 class="max-product-price"><?php echo '$' . $maxCartProduct->product_price; ?>
				<?php if ($maxCartProduct->product_stock) : ?>
					<small class="max-product-stock">
						in stock
						<?php if ($maxCartProduct->product_stock < 20) : ?>
							(only <?php echo $maxCartProduct->product_stock; ?> left!)
						<?php endif; ?>
					</small>
				<?php endif; ?>
			</h4>
			<?php if ($maxCartProduct->product_stock !== 0) : ?>
			<div class="max-select-group">
				<label for="product-qty">QTY</label>
				<select name="" id="product-qty" class="js-max-select hidden">

					<?php if ($maxCartProduct->product_stock) : ?>

						<?php for ($i = 1; $i <= $maxCartProduct->product_stock && $i <= 10; $i++) : ?>
							<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
						<?php endfor; ?>

					<?php else : ?>

						<?php for ($i = 1; $i <= 10; $i++) : ?>
							<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
						<?php endfor; ?>

					<?php endif; ?>

				</select>
			</div>
				<input type="hidden" id="product-id" value="<?php echo $maxCartProduct->product_id;?>"/>
				<input type="hidden" id="product-url" value="<?php echo $maxCartProduct->product_url;?>"/>
				<input type="hidden" id="product-name" value="<?php echo get_the_title();?>"/>
				<input type="hidden" id="product-price" value="<?php echo $maxCartProduct->product_price;?>"/>
				<input type="hidden" id="product-sku" value="<?php echo $maxCartProduct->product_sku;?>"/>
				<input type="hidden" id="product-thumbnail" value="<?php echo $maxCartProduct->product_gallery_sizes[0] ? $maxCartProduct->product_gallery_sizes[0]['thumbnail'][0] : '';?>"/>
				<button class="js-max-product-add max-product-btn max-success" data-bind="click: _add, attr:{disabled: processing}"><span class="fa fa-spinner fa-spin"></span>add to cart</button>
				<div data-bind="fadeVisible: error" class="error-message">
					<div class="text-danger bg-danger" data-bind="text: error_message"></div>
				</div>
				<?php wp_nonce_field( 'add_product_to_cart', 'verify_product_add_to_cart' ); ?>
			<?php else : ?>
				<span class="text-danger"> Out of Stock.</span>
			<?php endif; ?>
		</div><!--	end product-info-->

	</div><!-- end product-wrapper -->
</div>

<div class="max-product-wrapper col-sm-10 col-sm-offset-1">
	<div class="max-product-description">
		<?php the_content(); ?>
	</div><!-- end product-description -->
</div><!-- end product-wrapper -->
