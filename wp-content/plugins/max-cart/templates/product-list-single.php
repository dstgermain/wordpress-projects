<div class="col-md-3 col-xs-4 col-xxs-6 margin-bottom_30 product-list-single">
	<div class="row">
		<a href="<?php echo $maxCartProduct->product_url; ?>">
			<div class="col-sm-12 col-xs-12">
				<?php if ( ( isset( $maxCartProduct->product_gallery[0] ) && !empty($maxCartProduct->product_gallery[0]) ) &&
				           isset( $maxCartProduct->product_gallery_sizes[0] ) &&
				           isset( $maxCartProduct->product_gallery_sizes[0]['thumbnail'] ) ) : ?>
					<div class="product-img img-thumbnail">
						<img src="<?php echo $maxCartProduct->product_gallery_sizes[0]['thumbnail'][0]; ?>" alt="<?php echo $maxCartProduct->product_title; ?>" class="img-responsive"/>
					</div>
				<?php elseif ( has_post_thumbnail( $maxCartProduct->product_id ) ) : ?>
					<?php $attr = array(
						'class' => "img-responsive",
						'alt'   => get_the_title(),
					); ?>
				    <div class="product-img img-thumbnail">
					     <?php the_post_thumbnail( 'medium', $attr ); ?>
					</div>
				<?php else: ?>
					<div class="product-img missing">
						<i class="fa fa-question-circle fa-4x"></i>
					</div>
				<?php endif; ?>
			</div>
		</a>
		<div class="col-sm-12 col-xs-12">
			<h4><a href="<?php echo $maxCartProduct->product_url; ?>"><?php echo $maxCartProduct->product_title; ?></a></h4>
			<?php if ($maxCartProduct->product_company) : ?>
				<small>by <a href="<?php echo $maxCartProduct->product_company->guid; ?>"><?php echo $maxCartProduct->product_company->post_title; ?></a></small>
				<br/>
			<?php endif; ?>
			<?php $is_num = $maxCartProduct->product_price === '0'; ?>
			<strong>
				<?php if ($is_num) : ?>
					Please Call
				<?php else: ?>
					$<?php echo $maxCartProduct->product_price; ?>
				<?php endif; ?>
			</strong>
			<?php if ($maxCartProduct->product_stock === 0) : ?>
				<small class="text-danger">(Out of Stock)</small>
			<?php endif; ?>
		</div>
	</div>
</div>
