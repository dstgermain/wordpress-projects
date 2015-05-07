<?php
/*
Template Name: Custom Template
*/
?>
<div class="row">
  <div class="col-sm-10 col-sm-offset-1">
    <?php while (have_posts()) : the_post(); ?>
      <?php get_template_part('templates/page', 'header'); ?>
      <div class="row page-content">
        <div class="col-sm-12 margin-top_15">
          <div class="red-bg"><?php get_template_part('templates/content', 'page'); ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>
