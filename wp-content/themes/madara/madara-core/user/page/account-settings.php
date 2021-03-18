<?php
	if ( ! is_user_logged_in() ) {
		return;
	}

	$account_resp = isset( $_POST['account-form-submit'] )  ? madara_update_user_settings() : null;

	$user    = wp_get_current_user();
	if($user) $user_id = $user->ID;

?>
<form method="post">

	<?php if( $account_resp !== null ){ ?>
		<?php if ( ! is_wp_error( $account_resp ) ) { ?>
	        <div class="alert alert-success alert-dismissable">
	            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	            <strong><?php esc_html_e( 'Success!', 'madara' ); ?></strong> <?php esc_html_e( 'Update successfully', 'madara' ); ?>
	        </div>
		<?php } else { ?>
	        <div class="alert alert-danger alert-dismissable">
	            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	            <strong><?php esc_html_e( 'Failed!', 'madara' ); ?></strong> <?php echo esc_html( $account_resp->get_error_message() ); ?>
	        </div>
		<?php } ?>
	<?php } ?>

    <input type="hidden" value="<?php echo esc_attr( $user_id ); ?>" name="userID">
	<?php wp_nonce_field( '_wp_manga_save_user_settings' ); ?>
    <div class="tab-group-item">
        <div class="tab-item">
            <div class="choose-avatar">
				<div class="loading-overlay">
					<div class="loading-icon">
						<i class="fas fa-spinner fa-spin"></i>
					</div>
				</div>
				<div class="c-user-avatar">
					<?php echo get_avatar( $user_id, 195 ); ?>
				</div>
            </div>
			<?php 
			global $wp_manga_setting;
			
			$user_can_upload_avatar = $wp_manga_setting->get_manga_option('user_can_upload_avatar', '1');
			if($user_can_upload_avatar == '1'){?>
            <div class="form form-choose-avatar">
                <div class="select-flie">
                    <!--Update Avatar -->
                    <form action="#">
						<?php esc_html_e( 'Only for .jpg .png or .gif file', 'madara' ); ?>
                        <label class="select-avata">
							<input type="file" name="wp-manga-user-avatar">
							<span class="file-name"></span>
						</label>
							
                        <input type="submit" value="<?php esc_html_e( 'Upload', 'madara' ); ?>" name="wp-manga-upload-avatar" id="wp-manga-upload-avatar">
                    </form>

                </div>
            </div>
			<?php } ?>
        </div>
        <div class="tab-item">

            <div class="settings-title">
                <h3>
					<?php esc_html_e( 'Change Your Display name', 'madara' ); ?>
                </h3>
            </div>
            <div class="form-group row">
                <label for="name-input" class="col-md-3"><?php esc_html_e( 'Current Display name', 'madara' ); ?></label>
                <div class="col-md-9">
					<?php if ( isset( $user->data->display_name ) ) { ?>
                        <span class="show"><?php echo esc_html( $user->data->display_name ); ?></span>
					<?php } ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="name-input" class="col-md-3"><?php esc_html_e( 'New Display name', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="text" value="" name="user-new-name">
                </div>
            </div>
            <div class="form-group row">
                <label for="name-input-submit" class="col-md-3"><?php esc_html_e( 'Submit', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="submit" value="<?php esc_html_e( 'Submit', 'madara' ); ?>" id="name-input-submit" name="account-form-submit">
                </div>
            </div>

        </div>
        <div class="tab-item">
            <div class="settings-title">
                <h3>
					<?php esc_html_e( 'Change Your email address', 'madara' ); ?>
                </h3>
            </div>
            <div class="form-group row">
                <label for="email-input" class="col-md-3"><?php esc_html_e( 'Your email', 'madara' ); ?></label>
                <div class="col-md-9">
					<?php if ( isset( $user->data->user_email ) ) { ?>
                        <span class="show"><?php echo esc_html( $user->data->user_email ); ?></span>
					<?php } ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="email-input" class="col-md-3"><?php esc_html_e( 'New email address', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="email" value="" id="email-input" name="user-new-email">
                </div>
            </div>
            <div class="form-group row">
                <label for="email-input-submit" class="col-md-3"><?php esc_html_e( 'Submit', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="submit" value="<?php esc_html_e( 'Submit', 'madara' ); ?>" id="email-input-submit" name="account-form-submit">
                </div>
            </div>
        </div>
        <div class="tab-item">
            <div class="settings-title">
                <h3>
					<?php esc_html_e( 'Change Your Password', 'madara' ); ?>
                </h3>
            </div>

            <div class="form-group row">
                <label for="currrent-password-input" class="col-md-3"><?php esc_html_e( 'Current Password', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="password" value="" id="currrent-password-input" name="user-current-password">
                </div>
            </div>
            <div class="form-group row">
                <label for="new-password-input" class="col-md-3"><?php esc_html_e( 'New Password', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="password" value="" id="new-password-input" name="user-new-password">
                </div>
            </div>
            <div class="form-group row">
                <label for="comfirm-password-input" class="col-md-3"><?php esc_html_e( 'Comfirm Password', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="password" value="" id="comfirm-password-input" name="user-new-password-confirm">
                </div>
            </div>
            <div class="form-group row">
                <label for="password-input-submit" class="col-md-3"><?php esc_html_e( 'Submit', 'madara' ); ?></label>
                <div class="col-md-9">
                    <input class="form-control" type="submit" value="<?php esc_html_e( 'Submit', 'madara' ); ?>" id="password-input-submit" name="account-form-submit">
                </div>
            </div>
        </div>
    </div>
</form>
