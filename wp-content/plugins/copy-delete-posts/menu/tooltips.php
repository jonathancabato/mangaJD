<?php
/**
 * Copy & Delete Posts – default menu.
 *
 * @package CDP
 * @subpackage TooltipsPrepare
 * @author CopyDeletePosts
 * @since 1.0.0
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/** –– **\
 * There is constant tooltip content for dynamic load.
 * @since 1.0.0
 */
function cdp_tooltip_content($profiles = array()) {

  $isYoast = false; $isUSM = false; $isWoo = false;
  if (is_plugin_active('woocommerce/woocommerce.php')) $isWoo = true;
  if (is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')) $isYoast = true;
  if (
    is_plugin_active('Ultimate-Premium-Plugin/usm_premium_icons.php') ||
    is_plugin_active('ultimate-social-media-icons/ultimate_social_media_icons.php') ||
    is_plugin_active('ultimate-social-media-plus/ultimate-social-media-plus.php') ||
    is_plugin_active('ultimate-social-media-plus/ultimate_social_media_plus.php')
  ) $isUSM = true;

  $globals = get_option('_cdp_globals');

  $isMulti = is_multisite() != true ? ' disabled="disabled"' : '';

  // Ask for pro features
  $areWePro = areWePro();

?>
  <div class="cdp-tooltip-content">

    <div class="cdp-tooltip-before-options cdp-checkboxes">
      <div class="cdp-tooltip-before">

        <div class="cdp-button cdp-tooltip-btn-copy cdp-low-round cdp-copy-now-btn-tooltip cdp-center" data-cdp-btn="copy-quick">Copy now!</div>

        <div class="cdp-modal-copy-times-tooltip">
          <div class="cdp-modal-copy-times-content-tooltip">

            <div class="cdp-cf" style="line-height: 32px; margin-top: 13px; margin-bottom: 12px;">

              <div class="cdp-left">
                <input class="cdp-input-border cdp-input-dynamic cdp-number-field-styled cdp-modal-input-times-tooltip" style="width: 42px !important; padding: 0 1px;" value="1" placeholder="1" min="1" max="10000" type="number" name="tooltip-times-first">
              </div>

              <div class="cdp-left">&nbsp;time(s)</div>

              <div class="cdp-left">&nbsp;to&nbsp;</div>
              <div class="cdp-left" style="max-height: 32px;">
                <div class="cdp-inline cdp-tooltip-premium-spc-2 <?php echo (($isMulti != '')?' cdp-tooltip-premium-spc-3':' cdp-tooltip-premium-spc-4'); ?>">
                  <select<?php echo $isMulti; ?> class="cdp-input-dynamic cdp-tooltip-top cdp-premiu cdp-tooltip-select cdp-select cdp-sel-separat cdp-m-l-9-d" style="max-width: 98px !important" name="tooltip-which-site-first">
                    <option value="-1">this site</option>
                    <?php if ($areWePro && function_exists('cdpp_get_sites')) echo cdpp_get_sites(true); ?>
                  </select>
                </div>
              </div>

            </div>

            <div class="cdp-cf" style="line-height: 32px; margin-top: 3px; margin-bottom: 5px;">
              <div class="cdp-left">Settings:&nbsp;&nbsp;</div>

              <div class="cdp-left" style="line-height: 10px;">
                <select class="cdp-input-dynamic cdp-select cdp-tooltip-select cdp-sel-separat cdp-m-l-9-d cdp-sizes-profile-tooltip" name="tooltip-which-profile-first">
                  <?php $gepres = get_option('_cdp_preselections', array()); if (array_key_exists(get_current_user_id(), $gepres)) $preSelProf = $gepres[get_current_user_id()]; else $preSelProf = 0; ?>
                  <option value="custom"<?php echo (array_key_exists($preSelProf, $profiles) && !$profiles[$preSelProf])?' selected':''?> disabled>–– Select ––</option>
                  <?php
                  if ($profiles != false && $areWePro) {
                  foreach ($profiles as $profile => $vals):
                  $isSel = ($preSelProf == $profile);
                  ?>
                  <option value="<?php echo htmlspecialchars($profile); ?>"<?php echo ($isSel)?' selected':''?>><?php echo ucfirst(htmlspecialchars($vals['names']['display'])); ?></option>
                  <?php endforeach; } else { ?>
                  <option value="default">Default</option>
                  <?php } ?>
                </select>
              </div>

              <div class="cdp-left cdp-relative">
                <span class="cdp-info-icon cdp-tooltip-info-intt" style="top: calc(50% + 8px); margin-left: 9px;"></span>
              </div>
            </div>

          </div>
        </div>

        <div class="cdp-center cdp-f-s-12 cdp-padding-5-h">
          Or <span class="cdp-green cdp-pointer cdp-clickable cdp-tooltip-before-button">define it for this case</span>
        </div>
        <div class="cdp-center cdp-below-tooltip-before">
          <!-- Define the possible settings <span class="cdp-green">on the plugin page</span>.<br /> -->
          <span class="cdp-error-span-tooltip">Making more than 50 copies will take some time. – depending on your server.</span>
        </div>
      </div>
    </div>

    <div class="cdp-tooltip-full-options cdp-checkboxes" style="display: none; min-height: 360px; min-width: 602px; padding: 10px;">
      <div class="cdp-cf">
        <div class="cdp-left">
          <h2 class="cdp-f-s-16 cdp-f-w-semi-bold" style="margin: 0; line-height: 40px;">Elements to copy:</h2>
        </div>
        <div class="cdp-right" style="width: calc(100% - 200px); text-align: right;">
          <div class="cdp-cf cdp-inline" style="line-height: 40px">
            <div class="cdp-left cdp-f-s-16">Use as basis settings</div>
            <select class="cdp-left cdp-modal-select cdp-ow-border cdp-input-dynamic cdp-modal-input-profiles-r cdp-select cdp-m-l-9-d" name="tooltip-which-profile-second">
              <option value="custom" selected disabled>–– Select ––</option>
              <option value="clear">Clean slate</option>
              <optgroup label="–– Profiles ––"></optgroup>
              <option value="custom_named" disabled>Custom</option>
              <?php
              if ($profiles != false && $areWePro) {
                foreach ($profiles as $profile => $vals):
                  $isSel = ($preSelProf == $profile);
                  ?>
                  <option value="<?php echo htmlspecialchars($profile); ?>"<?php echo ($isSel)?' selected':''?>><?php echo ucfirst(htmlspecialchars($vals['names']['display'])); ?></option>
                <?php endforeach; } else { ?>
                  <option value="default">Default</option>
                <?php } ?>
            </select>
          </div>
        </div>
      </div>

      <div class="cdp-modal-checkboxes">
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="title">
          <span>Title</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="date">
          <span>Date</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="status">
          <span>Status</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="slug">
          <span>Slug</span>
        </label>
      </div>
      <div class="cdp-modal-checkboxes">
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="excerpt">
          <span>Excerpt</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="content">
          <span>Content</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="f_image">
          <span>Feat. image</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="template">
          <span>Template</span>
        </label>
      </div>
      <div class="cdp-modal-checkboxes">
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="format">
          <span>Format</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="author">
          <span>Author</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="password">
          <span>Password</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="children">
          <span>Children</span>
        </label>
      </div>
      <div class="cdp-modal-checkboxes">
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="comments">
          <span>Comments</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="menu_order">
          <span>Menu order</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="attachments">
          <span>Attachments</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="category">
          <span>Categories</span>
        </label>
      </div>
      <div class="cdp-modal-checkboxes">
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="post_tag">
          <span>Tags</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="taxonomy">
          <span>Taxonomies</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="nav_menu">
          <span>Nav Menus</span>
        </label>
        <label>
          <input class="cdp-input-dynamic" type="checkbox" name="link_category">
          <span>Link cats</span>
        </label>
      </div>
      <?php if ($isYoast || $isUSM || $isWoo): ?>
      <div class="cdp-modal-checkboxes cdp-modal-checkboxes-three">
        <label class="cdp-relative"><span class="cdp-premium-icon" style="margin-left: 0"></span><b style="padding-left: 21px;" class="cdp-f-s-15 cdp-f-w-medium">Plugin options:</b></label>
        <?php if ($isWoo): ?>
        <label class="cdp-woo">
          <div class="cdp-inline cdp-tooltip-premium-spc">
            <input class="cdp-input-dynamic" type="checkbox" name="woo">
            <span>Woo Settings</span>
          </div>
        </label>
        <?php endif; ?>
        <?php if ($isYoast): ?>
        <label class="cdp-yoast">
          <div class="cdp-inline cdp-tooltip-premium-spc">
            <input class="cdp-input-dynamic" type="checkbox" name="yoast">
            <span>Yoast Settings</span>
          </div>
        </label>
        <?php endif; ?>
        <?php if ($isUSM): ?>
        <label>
          <div class="cdp-inline cdp-tooltip-premium-spc">
            <input class="cdp-input-dynamic" type="checkbox" name="usmplugin">
            <span>USM Settings</span>
          </div>
        </label>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div class="cdp-relative">
        <div class="cdp-modal-copy-times cdp-tooltip-c-t cdp-f-s-15">
          <div class="cdp-modal-copy-times-content">
            <?php if ($areWePro && function_exists('cdpp_change_post_type')) cdpp_change_post_type(); ?>
            <div class="cdp-cf cdp-inline">
              <div class="cdp-left" style="line-height: 40px;">Copy&nbsp;</div>
              <div class="cdp-left" style="line-height: 40px;">
                <input class="cdp-modal-input-times cdp-input-border" name="tooltip-which-times-second" style="border-width: 1px !important;" placeholder="1" type="number" value="1">
              </div>
              <div class="cdp-left" style="line-height: 40px;">
                &nbsp;time(s)
              </div>
              <div class="cdp-left" style="line-height: 40px;">&nbsp;to</div>
              <div class="cdp-left">
                <div class="cdp-tooltip-premium-spc-2 cdp-inline<?php echo (($isMulti != '')?' cdp-tooltip-premium-spc-3':' cdp-tooltip-premium-spc-4'); ?>">
                  <select<?php echo $isMulti; ?> class="cdp-input-dynamic cdp-modal-select cdp-modal-select-2 cdp-ow-border cdp-modal-input-site cdp-select cdp-m-l-9-d" name="tooltip-which-site-second">
                    <option value="-1">this site</option>
                    <?php if ($areWePro && function_exists('cdpp_get_sites')) echo cdpp_get_sites(true); ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="cdp-center">
        <span class="cdp-error-span-tooltip">Making more than 50 copies will take some time. – depending on your server.</span>
      </div>

      <div class="cdp-center cdp-padding" style="min-width: 420px; padding-bottom: 10px;">
        <button class="cdp-button cdp-tooltip-btn-copy cdp-f-s-15 cdp-f-s-regular" data-cdp-btn="copy-custom" style="height:44px; width:211px;padding:0 20px;line-height: 44px;border-radius: 3px;">Copy it!</button>
        <?php if (isset($globals) && array_key_exists('afterCopy', $globals) && $globals['afterCopy'] == '3'): ?>
        <button class="cdp-button cdp-tooltip-btn-copy cdp-f-s-15 cdp-f-s-regular" data-cdp-btn="copy-custom-link" style="height:44px; width:292px;padding:0 20px;line-height: 44px;border-radius: 3px;margin-left: 15px !important;">Copy and jump to editing</button>
        <?php endif; ?>
      </div>

    </div>

  </div>
<?php
}
/** –– **/
