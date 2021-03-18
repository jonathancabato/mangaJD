<?php

if($authors != '') {?>
<div class="post-content_item madara_author">
	<div class="summary-heading" style="display: flex; align-items: center; justify-content: flex-start; width: 250px">
		<h5>
			<p><?php echo esc_html__( 'Author: ', 'madara' );?> </p> <span> <?php echo wp_kses_post( $authors );  ?></span>
		</h5>
	</div>
</div>
<?php }?>