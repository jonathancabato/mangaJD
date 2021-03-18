<div class="post-content_item">
	<div class="summary-heading">
	<i class="fas fa-chart-bar"></i>
	<div class="summary-heading-wrapper">

		<!-- <h5>
			<?php echo esc_html__( 'Status', 'madara' ); ?>
		</h5> -->
		<p>
		<?php
			echo wp_kses_post( $status );
		?>
		</p>
	</div>
	</div>
</div>