<div class="post-content_item">
	<div class="summary-heading">
	<i class="fas fa-trophy"></i>
	<div class="summary-heading-wrapper">

	<h5>
			<?php echo esc_html__( 'Rank', 'madara' ); ?>
		</h5>
		<p>
		<?php 
		
		if(method_exists($wp_manga_functions, 'print_ranking_views')){
			$wp_manga_functions->print_ranking_rank( $manga_id );
		} else {
			?>
			<?php echo sprintf( _n( ' %1s ', ' %1s ', $views, 'madara' ), $rank, $views ); ?>
			<?php
		}
		
		 ?>
		</p>
			</div>
	</div>
</div>
