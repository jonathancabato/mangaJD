<?php

 if( !defined( 'ABSPATH' ) ){
        exit;
    }

    if( !isset( $GLOBALS['wp_manga'] ) ){
        return;
    }

    $wp_manga_chapter_coin_settings = wp_manga_chapter_coin_get_settings();
	
    extract( $wp_manga_chapter_coin_settings );
	
	$default_coin = isset($default_coin) ? $default_coin : 0;
	$free_word = isset($free_word) ? $free_word : esc_html__('Free', MANGA_CHAPTER_COIN_TEXT_DOMAIN);
	$free_color = isset($free_color) ? $free_color : '#999999';
	$free_background = isset($free_background) ? $free_background : '#DCDCDC';
	$unlock_color = isset($unlock_color) ? $unlock_color : '#999999';
	$unlock_background = isset($unlock_background) ? $unlock_background : '#DCDCDC';
	$lock_color = isset($lock_color) ? $lock_color : '#ffffff';
	$lock_background = isset($lock_background) ? $lock_background : '#fe6a10';
	$ranking_background = isset($ranking_background) ? $ranking_background : 'rgba(255, 248, 26, 0.6)';
	$ranking_text_color = isset($ranking_text_color) ? $ranking_text_color : '#333333';
	$muupro_allow_creator_edit = isset($muupro_allow_creator_edit) ? $muupro_allow_creator_edit : 'no';
?>
<div class="section">
<h2 class="title"><?php esc_html_e( 'WP Manga Chapter Coin - Settings', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?></h2>
<table class="form-table">
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Default Coin', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="number" name="wp_manga_chapter_selling[default_coin]" value="<?php echo $default_coin;?>"/>
            </p>
			<p><?php esc_html_e('Set default coin value for all chapters. You can set coin value for each chapter to override this value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( '"Free" Badge - Label', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" name="wp_manga_chapter_selling[free_word]" value="<?php echo $free_word;?>"/>
            </p>
			<p><?php esc_html_e('Text for free chapter\'s badge', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( '"Free" Badge - Text Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $free_color;?>" name="wp_manga_chapter_selling[free_color]" value="<?php echo $free_color;?>"/>
            </p>
			<p><?php esc_html_e('Text color of "Free" badge. Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( '"Free" Badge - Background Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $free_background;?>" name="wp_manga_chapter_selling[free_background]" value="<?php echo $free_background;?>"/>
            </p>
			<p><?php esc_html_e('Background color of "Free" badge. Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Locked Badge - Text Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $lock_color;?>" name="wp_manga_chapter_selling[lock_color]" value="<?php echo $lock_color;?>"/>
            </p>
			<p><?php esc_html_e('Text color of locked premium chapter\'s badge. Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Locked badge - Background Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $lock_background;?>" name="wp_manga_chapter_selling[lock_background]" value="<?php echo $lock_background;?>"/>
            </p>
			<p><?php esc_html_e('Background color of locked premium chapter\'s badge. Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Unlocked Badge - Text Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $unlock_color;?>" name="wp_manga_chapter_selling[unlock_color]" value="<?php echo $unlock_color;?>"/>
            </p>
			<p><?php esc_html_e('Text color of bought/unlocked premium chapter\'s badge. Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Ranking badge - Background Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $ranking_background;?>" name="wp_manga_chapter_selling[ranking_background]" value="<?php echo $ranking_background;?>"/>
            </p>
			<p><?php esc_html_e('Background color of the Ranking Badge (of Top Bought Listing shortcodes). Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Ranking badge - Text Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $ranking_text_color;?>" name="wp_manga_chapter_selling[ranking_text_color]" value="<?php echo $ranking_text_color;?>"/>
            </p>
			<p><?php esc_html_e('Text color of the Ranking Badge (of Top Bought Listing shortcodes). Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Unlocked Badge - Background Color', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <input type="text" style="background-color:<?php echo $unlock_background;?>" name="wp_manga_chapter_selling[unlock_background]" value="<?php echo $unlock_background;?>"/>
            </p>
			<p><?php esc_html_e('Background color of bought/unlocked premium chapter\'s badge. Use hexa value including "#" character or rgba() value', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
	<tr>
        <th scope="row">
            <?php esc_html_e( '[For WP Manga Member Upload PRO] Allow Manga Owner to set coin', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
        </th>
        <td>
            <p>
                <select name="wp_manga_chapter_selling[muupro_allow_creator_edit]" value="<?php echo esc_attr(  $muupro_allow_creator_edit ); ?>">
                    <option value="yes" <?php selected( $muupro_allow_creator_edit, 'yes' ); ?>><?php esc_html_e('Yes', MANGA_CHAPTER_COIN_TEXT_DOMAIN); ?></option>
                    <option value="no" <?php selected( $muupro_allow_creator_edit, 'no' ); ?>><?php esc_html_e('No', MANGA_CHAPTER_COIN_TEXT_DOMAIN); ?></option>
                </select>
            </p>
			<p><?php esc_html_e('Allow Manga Owner to set coin value for all chapters of his manga in Front-end Edit page. Admin and Editor can edit coin as well.', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
        </td>
    </tr>
</table>
</div>