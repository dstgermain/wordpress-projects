<header class="banner navbar navbar-default navbar-static-top" role="banner">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>"></a>
      <div class="store-info">
        <address class="address">
          <?php echo get_option('street_address'); ?><br/>
          <?php echo get_option('city_address'); ?>, <?php echo get_option('state_address'); ?> <?php echo get_option('zip_address'); ?>
        </address>
        <div class="phone-numbers">
          <?php echo get_option('phone_1'); ?><br/>
          <?php echo get_option('phone_2'); ?>
        </div>
        <?php $search = "";
        if ( get_query_var('s') ) {
          $search = get_query_var('s');
        } ?>
        <form role="search" method="get" class="search-form form-inline"
              action="/">
          <label class="sr-only">Search for:</label>

          <div class="input-group">
            <input type="search" value="<?php echo $search; ?>" name="s" class="search-field form-control" placeholder="Search.." data-type="s">
						<span class="input-group-btn">
                              <button type="submit" class="search-submit btn btn-default">Search</button>
						</span>
          </div>
        </form>
      </div>
    </div>

    <nav class="collapse navbar-collapse" role="navigation">
      <?php
        if (has_nav_menu('primary_navigation')) :
          wp_nav_menu(array('theme_location' => 'primary_navigation', 'walker' => new Roots_Nav_Walker(), 'menu_class' => 'nav navbar-nav'));
        endif;
      ?>
    </nav>
  </div>
</header>
