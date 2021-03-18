<?php

if($alternative != '') {?>

<div class="post-content_item madara_tagline">
	<div class="summary-content">
		<?php echo wp_kses_post( $alternative ); ?>
	</div>
</div>

<?php }