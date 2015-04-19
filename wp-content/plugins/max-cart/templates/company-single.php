<?php
$maxCartCompany = new maxCartCompany();
the_post();
?>

<div class="max-product-wrapper col-sm-10 col-sm-offset-1">
	<ul class="max-product-breadcrumbs list-inline"><?php echo $maxCartCompany->breadcrumbs; ?></ul>
	<div class="row margin-bottom_60">
		<?php if (has_post_thumbnail( $maxCartCompany->company_id ) ): ?>
		<div class="col-sm-3">
			<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $maxCartCompany->company_id ), 'single-post-thumbnail' ); ?>
			<img src="<?php echo $image[0]; ?>" alt=""/>
		</div>
		<?php endif; ?>
		<div class="col-sm-9">
			<h1><?php echo $maxCartCompany->company_name; ?></h1>
			<p><?php the_content(); ?></p>
		</div>
	</div>
	<hr/>
	<?php if ( $maxCartCompany->company_products->have_posts() ) : ?>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="margin-bottom_15 pull-right">
				<?php wp_nonce_field( 'maxcart_ajax', 'verify_maxcart_ajax' ); ?>
				<input type="hidden" data-type="company" value="<?php echo $maxCartCompany->company_id; ?>"/>
				<select name="orderBy" id="orderBy" class="js-max-select js-max-orderby hidden" data-type="orderBy">
					<option value="price:ASC">Price Ascending</option>
					<option value="price:DESC">Price Descending</option>
					<option value="name:ASC">A - Z</option>
					<option value="name:DESC">Z - A</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row product-listing">
		<?php while ( $maxCartCompany->company_products->have_posts() ) :
			$maxCartCompany->company_products->the_post(); $maxCartProduct = new maxCartProduct(); ?>

			<?php include( WP_PLUGIN_DIR . '/max-cart/templates/product-list-single.php' ); ?>

		<?php endwhile; ?>
	</div>
	<?php if ( $maxCartCompany->company_products->found_posts > 12 ) : ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<button class="max-load-more js-max-load-more">Load More Results</button>
		</div>
	</div>
	<?php endif; ?>
	<?php endif; ?>
</div>

