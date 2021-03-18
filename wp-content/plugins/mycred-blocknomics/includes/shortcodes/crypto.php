<?php 
if(is_plugin_active( 'mycred/mycred.php' )){
    if ( ! function_exists( 'mycred_render_buy_form_points_btc' ) ) {
        function mycred_render_buy_form_points_btc( $atts = array(), $content = '' ) {

            $settings     = mycred_get_buycred_settings();

            extract( shortcode_atts( array(
                'button'   => __( 'Buy Now', 'mycred' ),
                'gateway'  => '',
                'ctype'    => MYCRED_DEFAULT_TYPE_KEY,
                'amount'   => '',
                'excluded' => '',
                'maxed'    => '',
                'gift_to'  => '',
                'e_rate'   => '',
                'gift_by'  => __( 'Username', 'mycred' ),
                'inline'   => 0
            ), $atts, MYCRED_SLUG . '_buy_form' ) );

            // If we are not logged in
            if ( ! is_user_logged_in() ) return $content;

            global $post, $buycred_instance, $buycred_sale;

            // Prepare
            $buyer_id     = get_current_user_id();
            $recipient_id = $buyer_id;
            $classes      = array( 'myCRED-buy-form' );
            $amounts      = array();
            $gifting      = false;

            // Make sure we have a gateway we can use
            if ( ( ! empty( $gateway ) && ! mycred_buycred_gateway_is_usable( $gateway ) ) || ( empty( $gateway ) && empty( $buycred_instance->active ) ) )
                return 'No gateway available.';

            // Make sure we are trying to sell a point type that is allowed to be purchased
            if ( ! in_array( $ctype, $settings['types'] ) )
                $ctype = $settings['types'][0];

            $mycred       = mycred( $ctype );
            $setup        = mycred_get_buycred_sale_setup( $ctype );

            $remaining    = mycred_user_can_buycred( $buyer_id, $ctype );

            // We are excluded from this point type
            if ( $remaining === false ) return $excluded;

            // We have reached a max purchase limit
            elseif ( $remaining === 0 ) return $maxed;

            // From this moment on, we need to indicate the shortcode usage for scripts and styles.
            $buycred_sale = true;

            // Amount - This can be one single amount or a comma separated list
            $minimum      = $mycred->number( $setup['min'] );

            if ( ! empty( $amount ) ) {
                foreach ( explode( ',', $amount ) as $value ) {
                    $value     = $mycred->number( abs( trim( $value ) ) );
                    if ( $value < $minimum ) continue;
                    $amounts[] = $value;
                }
            }

            // If we want to gift this to the post author (must be used inside the loop)
            if ( $settings['gifting']['authors'] && $gift_to == 'author' ) {
                $recipient_id = absint( $post->post_author );
                $gifting      = true;
            }

            // If we have nominated a user ID to be the recipient, use it
            elseif ( $settings['gifting']['members'] && absint( $gift_to ) !== 0 ) {
                $recipient_id = absint( $gift_to );
                $gifting      = true;
            }

            // Button Label
            $button_label = $mycred->template_tags_general( $button );

            if ( ! empty( $gateway ) ) {
                $gateway_name = explode( ' ', $buycred_instance->active[ $gateway ]['title'] );
                $button_label = str_replace( '%gateway%', $gateway_name[0], $button_label );
                $classes[]    = $gateway_name[0];
            }

            ob_start();

            if ( ! empty( $buycred_instance->gateway->errors ) ) {

                foreach ( $buycred_instance->gateway->errors as $error )
                    echo '<div class="alert alert-warnng"><p>' . $error . '</p></div>';

            }

    ?>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <form method="post" class="form<?php if ( $inline == 1 ) echo '-inline'; ?> <?php echo implode( ' ', $classes ); ?>" action="">
                <input type="hidden" name="token" value="<?php echo wp_create_nonce( 'mycred-buy-creds' ); ?>" />
                <input type="hidden" name="ctype" value="<?php echo esc_attr( $ctype ); ?>" />
                <?php if( isset($e_rate) && !empty($e_rate)){ 
                    $e_rate=base64_encode($e_rate);
                    ?>
                <input type="hidden" name="er_random" value="<?php echo esc_attr($e_rate); ?>" />
                <?php } ?>			
                <div class="form-group">
                    <label><?php echo $mycred->plural(); ?></label>
    <?php

            // No amount given - user must nominate the amount
            if ( count( $amounts ) == 0 ) {

    ?>
                    <input type="text" name="amount" class="form-control" placeholder="<?php echo $mycred->format_creds( $minimum ); ?>" min="<?php echo $minimum; ?>" value="" />
    <?php

            }

            // One amount - this is the amount a user must buy
            elseif ( count( $amounts ) == 1 ) {

    ?>
                    <p class="form-control-static"><?php echo $mycred->format_creds( $amounts[0] ); ?></p>
                    <input type="hidden" name="amount" value="<?php echo esc_attr( $amounts[0] ); ?>" />
    <?php

            }

            // Multiple amounts - user selects the amount from a dropdown menu
            else {

    ?>

                    <select  name="amount" class="form-control select_crown_points">
    <?php

                    foreach ( $amounts as $amount ) {
                        echo '<option value="' . esc_attr( $amount ) . '" ';

                        // If we enforce a maximum and the nominated amount is higher than we can buy,
                        // disable the option
                        if ( $remaining !== true && $remaining < $amount ) echo ' disabled="disabled"';

                        echo '>' . $mycred->format_creds( $amount ) . '</option>';

                    }
                    
                    
                    
    ?>
                    </select>
                    <ul>
                        <?php
                        $i=1;
                    foreach ( $amounts as $amount ) {
                        $i++;
                    $madara_price =mycredpro_buyred_volume_pricing( $cost, $amount );
                    if ( $remaining !== true && $remaining < $amount ){
                        $disabled = ' disabled="disabled"';
                    }else{
                        $disabled = '';
                    } 

                    echo '<li> <a class="madara_purchase_points" href="#" value="' . esc_attr( $amount ) . '" option="'.$i.'" data-mon-val="' .$madara_price . '" ' . $disabled . '><div class="card card-body mb-2 manga-points-container"><div class="coin-wrapper"><i class="fas fa-crown"></i><p>x' . $mycred->format_creds( $amount ) . '</p></div><p>$'.$madara_price.'</p></a></div></li>';

    }
    ?>
    </ul>
    <?php

            }

            // A recipient is set
            if ( $gifting ) {

                $user = get_userdata( $recipient_id );

    ?>
                    <div class="form-group">
                        <label for="gift_to"><?php _e( 'Recipient', 'mycred' ); ?></label>
                        <p class="form-control-static"><?php echo esc_html( $user->display_name ); ?></p>
                        <input type="hidden" name="<?php if ( $gift_to == 'author' ) echo 'post_id'; else echo 'gift_to'; ?>" value="<?php echo absint( $recipient_id ); ?>" />
                    </div>
    <?php

            }

            // The payment gateway needs to be selected
            if ( empty( $gateway ) && count( $buycred_instance->active ) > 1 ) {

  

            }

            // The gateway is set or we just have one gateway enabled
            else {

                // If no gateway is set, use the first active gateway
                if (  empty( $gateway ) && count( $buycred_instance->active ) > 0 )
                    $gateway = array_keys( $buycred_instance->active )[0];

    ?>
                    <input type="hidden" name="mycred_buy" value="<?php echo esc_attr( $gateway ); ?>" />
    <?php

            }

    ?>
                    </div>

                    <div class="form-group">
                   <?= wp_get_block_api() ?>
                    </div>

            </form>
        </div>
    </div>
    <?php

            $content = ob_get_contents();
            ob_end_clean();

            return $content;

        }
    }
}