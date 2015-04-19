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
		<?php get_template_part( 'templates/page', 'header' ); ?>
		<div class="row welcome">
			<div class="col-sm-6 col-md-5">
				<img src="<?php the_cfc_field( 'home_img', 'home-page-image' ); ?>"
				     class="img-responsive img-thumbnail"/>
			</div>
			<div class="col-sm-6 col-md-7">
				<?php $about_page = get_page_by_path( 'about' ); ?>
				<?php echo limit_text( $about_page->post_content, 155 ); ?>
				<a href="<?php echo $about_page->guid; ?>">Continue Reading &raquo;</a>
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
	<?php endwhile; ?>
</div>
