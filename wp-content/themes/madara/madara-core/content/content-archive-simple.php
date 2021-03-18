<?php 

use App\Madara;

$wp_query           = madara_get_global_wp_query();
$wp_manga           = madara_get_global_wp_manga();
$wp_manga_setting   = madara_get_global_wp_manga_setting();
$wp_manga_functions = madara_get_global_wp_manga_functions();

$manga_id = get_the_ID();
?>
<div class="item-summary">
	<div class="post-title font-title">
		<h3 class="h5">
			<?php do_action('madara_before_archive_item_title', $manga_id, 'simple');?>
			
			<?php madara_manga_title_badges_html( $manga_id, 1 ); ?>
			<a href="<?php esc_url(the_permalink()); ?>"><?php the_title(); ?></a>
			
			<div class="meta-item rating">
				<?php
					$wp_manga_functions->manga_rating_display( $manga_id );
				?>
			</div>
			
			<?php do_action('madara_after_archive_item_title', $manga_id, 'simple');?>
		</h3>
	</div>
	
	<div class="list-chapter">
		<?php
			$wp_manga_functions->manga_meta( $manga_id );
		?>
	</div>
	<?php do_action('madara_after_archive_item', $manga_id, 'simple');?>
</div>