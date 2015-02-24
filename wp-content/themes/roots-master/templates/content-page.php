<div class="row">
	<div class="col-sm-9 padding-top_15 padding-bottom_15">
		<?php the_content(); ?>
		<?php if ( get_cfc_meta( 'contact-hours' ) ) : ?>
			<div class="row padding-top_15">
				<div class="col-sm-6">
					<table class="table hours">
						<thead>
						<tr>
							<th colspan="2">Hours</th>
						</tr>
						</thead>
						<thead>
						<?php foreach( get_cfc_meta( 'contact-hours' ) as $key => $value ){ ?>
						<tr>
							<td><?php the_cfc_field( 'contact-hours','contact-hours', false, $key ); ?></td>
							<td><?php the_cfc_field( 'contact-hours','contact-time', false, $key ); ?></td>
						</tr>
						<?php }  ?>
						</thead>
					</table>
				</div>
				<div class="col-sm-6">
					<div class="google-map">
						<iframe width="580" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=42+Portsmouth+Ave+%23+A,+Exeter,+NH&amp;aq=&amp;sll=42.081917,-72.883301&amp;sspn=8.754587,19.753418&amp;vpsrc=6&amp;ie=UTF8&amp;hq=&amp;hnear=42+Portsmouth+Ave,+Exeter,+New+Hampshire+03833&amp;ll=42.983611,-70.939429&amp;spn=0.016859,0.038581&amp;t=m&amp;z=14&amp;output=embed"></iframe>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div class="col-sm-3 aside">
		<h3><?php the_cfc_field('sidebar-title', 'sidebar-title'); ?></h3>
		<?php the_cfc_field('sidebar-content', 'sidebar-content'); ?>
	</div>
</div>

<?php wp_link_pages(array('before' => '<nav class="pagination">', 'after' => '</nav>')); ?>
