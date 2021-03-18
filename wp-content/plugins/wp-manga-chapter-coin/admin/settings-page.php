<?php
// This is the secret key for API authentication. You configured it in the settings menu of the license manager plugin.
if (!defined('MANGA_BOOTH_SPECIAL_SECRET_KEY')) {
    define('MANGA_BOOTH_SPECIAL_SECRET_KEY', '5a7e6075d997c3.19308574');
}

// This is the URL where API query request will be sent to. This should be the URL of the site where you have installed the main license manager plugin. Get this value from the integration help page.
if (!defined('MANGA_BOOTH_LICENSE_SERVER_URL')) {
    define('MANGA_BOOTH_LICENSE_SERVER_URL', 'https://mangabooth.com');
}

// This is a value that will be recorded in the license manager data so you can identify licenses for this item/product.
define('WP_MANGA_CHAPTER_COIN_ITEM_REFERENCE', 'WP Manga - Chapter Coin');

define('WP_MANGA_CHAPTER_COIN_LICENSE_KEY', 'wp_manga_chapter_coin_license_key');

add_action('admin_menu', 'wp_manga_chapter_coin_license_menu');

function wp_manga_chapter_coin_license_menu()
{
    add_options_page('WP Manga Chapter Coin - License Activation Menu', 'WP Manga Chapter Coin License', 'manage_options', MANGA_CHAPTER_COIN_TEXT_DOMAIN, 'wp_manga_chapter_coin_license_management_page');
}

function wp_manga_chapter_coin_license_management_page()
{
    echo '<div class="wrap">';
    echo '<h2>'.esc_html__( 'WP Manga - Chapter Coin License Management', MANGA_CHAPTER_COIN_TEXT_DOMAIN ).'</h2>';

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST[WP_MANGA_CHAPTER_COIN_LICENSE_KEY];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => MANGA_BOOTH_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(WP_MANGA_CHAPTER_COIN_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, MANGA_BOOTH_LICENSE_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)) {
            echo "<br /><span style='color: red'>Unexpected Error! The query returned with an error.</span>";
        } else {
			// License data.
			$license_data = json_decode(wp_remote_retrieve_body($response));
			
			// TODO - Do something with it.

			if (isset($license_data->result)) {
				if ($license_data->result == 'success') {//Success was returned for the license activation

					echo '<br />The following message was returned from the server: ' . '<span style="color: blue">'.$license_data->message.'</span>';

					//Save the license key in the options table
					update_option(WP_MANGA_CHAPTER_COIN_LICENSE_KEY, $license_key);
				} else {
					//Show error to the user. Probably entered incorrect license key.

					echo '<br />The following message was returned from the server: ' . '<span style="color: red">'.$license_data->message.'</span>';
				}
			} else {
				if(isset($response['response'])){
					echo "<br /><span style='color: red'>There are some errors occur contacting to server: Code - " . $response['response']['code'] . " || Message: " . $response['response']['message'] . ", please contact to plugin's author to get support for this situation</span>";
				} else {
					echo "<br /><span style='color: red'>There are some errors occur contacting to server, please contact to plugin's author to get support for this situation.</span>";
				}
			}
		}
    }
    /*** End of license activation ***/

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST[WP_MANGA_CHAPTER_COIN_LICENSE_KEY];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => MANGA_BOOTH_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(WP_MANGA_CHAPTER_COIN_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, MANGA_BOOTH_LICENSE_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)) {
            echo "Unexpected Error! The query returned with an error.";
        }

        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));

        // TODO - Do something with it.

        if (isset($license_data->result)) {
            if ($license_data->result == 'success') {//Success was returned for the license activation

                echo '<br />The following message was returned from the server: ' . '<span style="color: blue">'.$license_data->message.'</span>';

                //Remove the licensse key from the options table. It will need to be activated again.
                update_option(WP_MANGA_CHAPTER_COIN_LICENSE_KEY, '');
            } else {
                //Show error to the user. Probably entered incorrect license key.

                echo '<br />The following message was returned from the server: ' . '<span style="color: red">'.$license_data->message.'</span>';
            }
        } else {
            echo "<br />There're some errors occur when activate license from server, please contact to plugin's author to get support for this situation.";
        }
    }
    /*** End of sample license deactivation ***/

    ?>
    <p><?php esc_html_e( 'Please enter the license key for this product to activate it. You were given a license key when you purchased this item.', 'wp-manga-chapter-coin' ); ?></p>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="<?php echo WP_MANGA_CHAPTER_COIN_LICENSE_KEY;  ?>"><?php esc_html_e('License Key', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></label></th>
                <td>
                    <input class="regular-text" type="text" id="<?php echo WP_MANGA_CHAPTER_COIN_LICENSE_KEY;  ?>"
                           name="<?php echo WP_MANGA_CHAPTER_COIN_LICENSE_KEY;  ?>"
                           value="<?php echo get_option(WP_MANGA_CHAPTER_COIN_LICENSE_KEY); ?>">
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="Activate" class="button-primary"/>
            <input type="submit" name="deactivate_license" value="Deactivate" class="button"/>
        </p>
    </form>
    <?php

    echo '</div>';
}

function wp_manga_chapter_coin_admin_notice__warning()
{
    $class = 'notice notice-warning is-dismissible';
    $message = sprintf(__('WP Manga - Chapter Coin Plugin have not activated, you should activate this plugin to use it,  %1$sactivate.%2$s ', MANGA_CHAPTER_COIN_TEXT_DOMAIN), '<a href="' . admin_url('options-general.php?page=wp-manga-chapter-coin') . '">', '</a>');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
}