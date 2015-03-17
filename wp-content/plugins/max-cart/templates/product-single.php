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
</div>

<div class="col-sm-12">
	<div class="max-product-wrapper row">
		<div class="col-sm-7">
			<div class="max-product-main">

				<?php if ( count( $maxCartProduct->product_gallery_sizes ) ) : ?>
					<div class="vertical-align-table">
						<div class="vertical-align-row">
							<div class="vertical-align-cell">
								<div class="max-product-main-image">
								<img src="<?php echo $maxCartProduct->product_gallery_sizes[0]['large'][0]; ?>"
								     alt="<?php echo get_the_title(); ?>"
								     class="img-responsive js-max-main-image js-max-view-full"
								     data-large="<?php echo $maxCartProduct->product_gallery_sizes[0]['large'][0]; ?>"
								     data-full="<?php echo $maxCartProduct->product_gallery_sizes[0]['full'][0]; ?>" />
								</div>
							</div>
						</div>
					</div>

				<?php else : ?>

				<?php endif; ?>

			</div>

			<div class="max-product-images">

				<?php if ( count( $maxCartProduct->product_gallery_sizes ) > 1 ) : $first = true; ?>
					<div class="max-product-gallery list-inline row">
						<?php foreach ($maxCartProduct->product_gallery_sizes as $img_array ) : ?>
							<div class="col-xs-4 col-sm-3">
								<img src="<?php echo $img_array['thumbnail'][0]; ?>"
								     alt="<?php echo get_the_title(); ?>"
								     data-large="<?php echo $img_array['large'][0]; ?>"
								     data-full="<?php echo $img_array['full'][0]; ?>"
								     class="img-responsive js-max-gallery-image <?php echo $first ? 'active': ''; $first = false; ?>"/>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
			<!-- end product-images -->
		</div>

		<div class="max-product-info col-sm-5">
			<h1><?php echo get_the_title(); ?></h1>

			<?php if ($maxCartProduct->product_company && isset( $maxCartProduct->product_company->post_title ) ) : ?>
				<h3><?php echo $maxCartProduct->product_company->post_title;?></h3>
			<?php endif; ?>

			<h4 class="max-product-price"><?php echo $maxCartProduct->product_price; ?>
				<?php if ($maxCartProduct->product_stock) : ?>
					<small class="max-product-stock">
					in stock
						<?php if ($maxCartProduct->product_stock < 20) : ?>
							(only <?php echo $maxCartProduct->product_stock; ?> left!)
						<?php endif; ?>
			  	  </small>
				<?php endif; ?>
			</h4>

			<label for="maxProductQty">QTY</label>
			<select name="" id="maxProductQty" class="js-max-select">

				<?php if ($maxCartProduct->product_stock) : ?>

					<?php for ($i = 1; $i <= $maxCartProduct->product_stock && $i <= 10; $i++) : ?>
						<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php endfor; ?>

				<?php else : ?>

					<?php for ($i = 1; $i <= 10; $i++) : ?>
						<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php endfor; ?>

				<?php endif; ?>

			</select><br/>
			<button class="js-max-product-add max-product-btn max-success">add to cart</button>
		</div><!--	end product-info-->

	</div><!-- end product-wrapper -->
</div>

<div class="max-product-wrapper col-sm-12">
	<div class="max-product-description">
		<?php the_content(); ?>
	</div><!-- end product-description -->
</div><!-- end product-wrapper -->
