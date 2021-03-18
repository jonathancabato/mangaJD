<?php

if($genres != '') {?>
<div class="post-content_item">
	<div class="summary-heading">
		<div class="summary-heading-wrapper categories">
			<!-- <h5>
				<?php echo esc_html__( 'Categories', 'madara' ); ?>
			</h5> -->
			<p class="search-genre"><?php echo wp_kses_post( $genres ); ?></p>
			<p>
				
				<?php foreach($terms as $term){ ?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="genre-link">
					<?php if(is_plugin_active( 'advanced-category-and-custom-taxonomy-image/wp-advanced-taxonomy-image.php' ) === true){ ?>
						
						<?php if(get_taxonomy_image($term->term_id) !== 'Please Upload Image First!'){ ?>
						
							<img src="<?= get_taxonomy_image($term->term_id) ?>" class="wp-manga-genre-image" alt="">
						<?php }else{ ?>
						<img src="<?= home_url() .'/wp-content/uploads/2021/02/fist.svg' ?>" class="wp-manga-genre-image" alt="">
						<?php } ?>
					<?php } ?>
					<?php echo esc_html( $term->name ); ?>
					<?php  ?>
					</a>
					
				<?php } ?>
			</p>
		</div>
	</div>
</div>

<?php }