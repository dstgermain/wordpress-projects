<?php
/*
Template Name: Custom Template
*/
?>

<?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/page', 'header'); ?>
    <div class="row page-content">
      <div class="col-sm-12">
        <?php get_template_part('templates/content', 'page'); ?>
      </div>
    </div>
<?php endwhile; ?>
