<?php
/**
 * Created by PhpStorm.
 * User: dstgermain
 * Date: 3/22/15
 * Time: 8:29 PM
 */
$maxCartCompanyArchive = new maxCartCompanyArchive;
?>
<div class="max-product-wrapper col-sm-12">
	<ul class="max-product-breadcrumbs list-inline">
		<?php echo $maxCartCompanyArchive->breadcrumbs; ?>
	</ul>
	<ul class="alphabetical-navigation">
		<?php foreach( $maxCartCompanyArchive->alphabet as $letter ) : ?>
			<li><a href="#<?php echo $letter; ?>" <?php echo !in_array($letter, $maxCartCompanyArchive->alpha_list) ? 'disabled' : ''; ?>><?php echo $letter; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<div class="row product-listing">
		<?php foreach( $maxCartCompanyArchive->listings_chunk as $chunk ) : ?>
			<div class="col-sm-4">
				<ul class="company-list">
					<?php foreach( $chunk as $item ) : ?>
						<?php echo $item; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
</div>
