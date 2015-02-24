<header class="banner navbar navbar-default navbar-static-top" role="banner">
	<div class="container">
		<div class="row">
			<div class="col-sm-3 hidden-xs aside">
				<img src="/wp-content/themes/roots-master/assets/img/logo.svg" class="header-logo"/>
				Our Office's
			</div>
			<div class="col-sm-9">
				<div class="navbar-header">
					<img src="/wp-content/themes/roots-master/assets/img/logo.svg" class="header-logo"/>
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<nav class="collapse navbar-collapse" role="navigation">
					<?php
					if (has_nav_menu('primary_navigation')) :
						wp_nav_menu(array('theme_location' => 'primary_navigation', 'walker' => new Roots_Nav_Walker(), 'menu_class' => 'nav navbar-nav'));
					endif;
					?>
				</nav>
				<address class="pull-right">
					<strong>Location</strong><br>
					<a href="https://www.google.com/maps/place/Pawlak+%26+St+Germain:+Michael+St.Germain+DMD/@42.9828929,-70.940132,17z/data=!4m7!1m4!3m3!1s0x89e2eee576c44afb:0xa4ffb19819bf450!2s42+Portsmouth+Ave,+Exeter,+NH+03833!3b1!3m1!1s0x89e2eeef7de15c2d:0x9ae0b986f343cf36">42 Portsmouth Ave #A<br>
					Exeter, NH 03833</a><br>
					<strong>Phone</strong><br>
					<a href="tel:1-603-778-8101">603-778-8101</a>
				</address>
			</div>
		</div>
	</div>
</header>
