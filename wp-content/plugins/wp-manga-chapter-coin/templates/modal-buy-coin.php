<div class="modal fade" id="frm-wp-manga-buy-coin" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="hidden" name="wp-manga-coin-nonce" value="<?php echo wp_create_nonce('wp-manga-coin-nonce');?>"/>
				<input type="hidden" name="wp-manga-chapter" value=""/>
				<h3>
					<?php echo esc_html__( 'Premium Chapter', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
				</h3>
				<?php 
				if(get_current_user_id()){?>
				<p class="message-sufficient"><?php echo wp_kses( __('You are about to spend coins to unlock this chapter', MANGA_CHAPTER_COIN_TEXT_DOMAIN ), array('span'=> array('class' => 1))); ?></p>
				<p class="message-lack-of-coin hidden"><?php echo wp_kses(__('You do not have enough Coins to buy this chapter', MANGA_CHAPTER_COIN_TEXT_DOMAIN ),array('a'=>array('href'=>1, 'class'=>1))); ?></p>
				<?php } else {?>
				<p class="message-login"><?php echo wp_kses(__('You are required to login first', MANGA_CHAPTER_COIN_TEXT_DOMAIN ), array('a'=>array('href'=>1, 'class'=>1))); ?></p>
				<?php }?>
				<div class = "" id="choosePrice">
				<!-- <a href="javascript:void(0)"class="button button-primary button-large modal-choose-btn" data-coin="100"><i class="fas fa-coins"></i>100</a>
				<a href="javascript:void(0)"class="button button-primary button-large modal-choose-btn"  data-coin="200"><i class="fas fa-coins"></i>200</a>
				<a href="javascript:void(0)"class="button button-primary button-large modal-choose-btn" data-coin="300"><i class="fas fa-coins"></i>300</a> -->
				</div>
			</div>
			<div class="modal-footer">
				<?php 
				if(get_current_user_id()){?>
				<!-- <a href="javascript:void(0)"class="button button-primary button-large btn-choose-price"><i class="fas fa-coins"></i>Choose your price</a> -->
				<button class="button button-primary button-large wp-submit btn-agree"><?php echo esc_html__( 'Buy it', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?> <i class="fas fa-spinner fa-spin"></i></button>
				<a href="javascript:void(0)"class="button button-primary button-large modal-premium-btn"><i class="fas fa-coins"></i>Buy Coins</a>
				
				<?php } ?>
					
				<button class="button button-secondary button-large btn-cancel"><?php echo esc_html__( 'Cancel', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>