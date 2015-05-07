<?php
/*
Template Name: Home Template
*/
function limit_text( $text, $limit ) {
	if ( str_word_count( $text, 0 ) > $limit ) {
		$words = str_word_count( $text, 2 );
		$pos   = array_keys( $words );
		$text  = substr( $text, 0, $pos[ $limit ] ) . '...';
	}

	return $text;
}

function get_col_count( $count ) {
	return ( 12 / $count );
}

?>
<div class="home-page clearfix">
	<?php while ( have_posts() ) : the_post(); ?>
		<div class="row">
			<div class="col-md-12">
				<div class="row facebook-feed-group">
					<div class="col-sm-8">
						<div class="row welcome">
							<div class="col-sm-12">
								<h2>Welcome</h2>
								<hr/>
								<?php $about_page = get_page_by_path( 'about' ); $excerpt = str_replace('[wds id="1"]','',$about_page->post_content); ?>
								<img src="<?php the_cfc_field( 'home_img', 'home-page-image' ); ?>"
								     class="img-thumbnail" align="left"/>
								<?php echo limit_text( $excerpt, 140 ); ?>
								<a href="<?php echo $about_page->guid; ?>">Continue Reading &raquo;</a>
							</div>
						</div>
					</div>
					<div class="col-sm-4 facebook-feed">
						<div class="feed-title"><i class="fa fa-facebook-official fa-lg"></i> Latest from Facebook</div>
						<?php echo do_shortcode( '[custom-facebook-feed]' ); ?>
					</div>
				</div>
				<div class="row categories clearfix">
					<?php $col = get_col_count( count( get_cfc_meta( 'home_categories' ) ) ) ?>
					<?php foreach ( get_cfc_meta( 'home_categories' ) as $key => $value ) : ?>
						<div class="col-xs-<?php echo $col; ?>">
							<a class="img-rounded"
							   href="<?php the_cfc_field( 'home_categories', 'category-url', false, $key ); ?>">
								<div class="category-name">
									<?php the_cfc_field( 'home_categories', 'category-text', false, $key ); ?>
								</div>
								<div class="category-image">
									<img src="<?php the_cfc_field( 'home_categories', 'category-image', false, $key ); ?>"
									     alt=""/>
								</div>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="row">
					<div class="col-md-10 col-md-offset-1">
						<div class="row brands clearfix">
							<div class="col-md-10 col-md-offset-1">
								<div class="row">
									<?php $col = get_col_count( count( get_cfc_meta( 'grill_brands' ) ) ); ?>
									<?php foreach ( get_cfc_meta( 'grill_brands' ) as $key => $value ) : ?>
										<div class="col-xs-<?php echo $col; ?>">
											<a href="<?php echo the_cfc_field( 'grill_brands', 'brand-url', false, $key ); ?>"><img src="<?php echo the_cfc_field( 'grill_brands', 'brand-logo', false, $key ); ?>" alt="" class="img-responsive"/></a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
</div>
