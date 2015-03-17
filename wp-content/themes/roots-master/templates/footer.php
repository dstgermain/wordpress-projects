<footer class="content-info padding-top_30 padding-bottom_30" role="contentinfo">
	<div class="container-fluid text-center">
		<?php
		if (has_nav_menu('primary_navigation')) :
			wp_nav_menu(array('theme_location' => 'primary_navigation', 'walker' => new Roots_Nav_Walker(), 'menu_class' => 'list-inline'));
		endif;
		?>
		<small>&copy;2011 Office's of Dr. Pawlak and St. Germain</small><br>
		<small>Website by <a href="http://www.danstgermain.com" target="_blank">Dan St. Germain</a></small>
	</div>
</footer>
