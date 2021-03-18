<?php
/**
 * Copy & Delete Posts – default menu.
 *
 * @package CDP
 * @subpackage CopyModal
 * @author CopyDeletePosts
 * @since 1.0.0
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/** –– **\
 * There is constant modal html form using thickbox.
 * @since 1.0.0
 */
function cdp_modal($screen = '', $profiles = array()) {
  if (!function_exists('is_plugin_active')) require_once(ABSPATH.'wp-admin/includes/plugin.php');

  $isYoast = false; $isUSM = false; $isWoo = false;
  if (is_plugin_active('woocommerce/woocommerce.php')) $isWoo = true;
  if (is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')) $isYoast = true;
  if (
    is_plugin_active('Ultimate-Premium-Plugin/usm_premium_icons.php') ||
    is_plugin_active('ultimate-social-media-icons/ultimate_social_media_icons.php') ||
    is_plugin_active('ultimate-social-media-plus/ultimate-social-media-plus.php') ||
    is_plugin_active('ultimate-social-media-plus/ultimate_social_media_plus.php')
  ) $isUSM = true;

  $isMulti = is_multisite() != true ? ' disabled="disabled"' : '';

  // Ask for pro features
  $areWePro = areWePro();
  $globals = get_option('_cdp_globals');
?>
  <div id="cdp-copy-modal-global" class="cdp-modal cdp-copy-modal" style="display:none;">

    <div class="cdp-modal-content" style="padding-bottom: 15px; max-height: 90vh;">

      <div class="cdp-modal-times"></div>

      <div class="cdp-cf cdp-cp-pad" style="margin-top: 50px; padding-top: 0; padding-bottom: 10px;">
        <div class="cdp-left">
          <h2 class="cdp-f-s-16 cdp-f-w-semi-bold" style="margin: 0; line-height: 40px;">Elements to copy:</h2>
        </div>
        <div class="cdp-right" style="width: calc(100% - 200px) !important; text-align: right !important;">
          <div class="cdp-cf cdp-inline" style="line-height: 40px">
            <div class="cdp-left cdp-f-s-16">Use as basis settings</div>
            <?php $gepres = get_option('_cdp_preselections', array()); if (array_key_exists(get_current_user_id(), $gepres)) $preSelProf = $gepres[get_current_user_id()]; else $preSelProf = 0; ?>
            <select class="cdp-left cdp-modal-select cdp-ow-border cdp-input-dynamic cdp-modal-input-profiles-r cdp-select cdp-m-l-9-d" name="tooltip-which-profile-second">
              <option value="custom"<?php echo (array_key_exists($preSelProf, $profiles) && !$profiles[$preSelProf])?' selected':''?> disabled>–– Select ––</option>
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

      <div class="cdp-cp-pad">
        <div class="cdp-modal-checkboxes cdp-checkboxes">
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="title">
            <span>Title</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="date">
            <span>Date</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="status">
            <span>Status</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="slug">
            <span>Slug</span>
          </label>
        </div>
        <div class="cdp-modal-checkboxes cdp-checkboxes">
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="excerpt">
            <span>Excerpt</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="content">
            <span>Content</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="f_image">
            <span>Feat. image</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="template">
            <span>Template</span>
          </label>
        </div>
        <div class="cdp-modal-checkboxes cdp-checkboxes">
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="format">
            <span>Format</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="author">
            <span>Author</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="password">
            <span>Password</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="children">
            <span>Children</span>
          </label>
        </div>
        <div class="cdp-modal-checkboxes cdp-checkboxes">
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="comments">
            <span>Comments</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="menu_order">
            <span>Menu order</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="attachments">
            <span>Attachments</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="category">
            <span>Categories</span>
          </label>
        </div>
        <div class="cdp-modal-checkboxes cdp-checkboxes">
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="post_tag">
            <span>Tags</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="taxonomy">
            <span>Taxonomies</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="nav_menu">
            <span>Navigation Menus</span>
          </label>
          <label>
            <input class="cdp-modal-option-r cdp-input-dynamic" type="checkbox" name="link_category">
            <span>Link categories</span>
          </label>
        </div>
        <?php if ($isYoast || $isUSM || $isWoo): ?>
          <div class="cdp-modal-checkboxes cdp-checkboxes cdp-modal-checkboxes-three">
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
      </div>

      <div class="cdp-modal-copy-times cdp-f-s-15">
        <div class="cdp-modal-copy-times-content">
          <?php if ($areWePro && function_exists('cdpp_change_post_type')) cdpp_change_post_type(); ?>
          <div class="cdp-cf cdp-inline">
            <div class="cdp-left" style="line-height: 40px;">Copy&nbsp;</div>
            <div class="cdp-left" style="line-height: 40px;">
              <input class="cdp-modal-input-times cdp-input-border" style="border-width: 1px !important;" placeholder="1" type="number" value="1">
            </div>
            <div class="cdp-left" style="line-height: 40px;">
              &nbsp;time(s)
            </div>
            <div class="cdp-left" style="line-height: 40px;">&nbsp;to</div>
            <div class="cdp-left">
              <div class="cdp-inline cdp-tooltip-premium-spc-2 <?php echo (($isMulti != '')?' cdp-tooltip-premium-spc-3':' cdp-tooltip-premium-spc-4'); ?>">
                <select class="cdp-input-dynamic cdp-modal-select cdp-modal-select-2 cdp-ow-border cdp-modal-input-site cdp-select cdp-m-l-9-d" name="tooltip-which-site-second" <?php echo $isMulti; ?>>
                  <option value="-1">this site</option>
                  <?php if ($areWePro && function_exists('cdpp_get_sites')) echo cdpp_get_sites(true); ?>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="cdp-center">
        <span class="cdp-error-span-tooltip">Making more than 50 copies will take some time. – depending on your server.</span>
      </div>

      <div class="cdp-center cdp-p-25-h">
        <button class="cdp-button cdp-copy-modal-button cdp-f-s-15 cdp-f-w-regular" data-cdp-btn="copy-custom" style="height:44px; width:211px;padding:0 20px;line-height: 44px;">Copy it!</button>
        <?php if (isset($globals) && array_key_exists('afterCopy', $globals) && $globals['afterCopy'] == '3'): ?>
        <button class="cdp-button cdp-copy-modal-button cdp-p-right-h cdp-f-s-15 cdp-f-w-regular" data-cdp-btn="copy-custom-link" style="height:44px; width:292px;padding:0 20px;line-height: 44px;margin-left: 15px !important;">Copy and jump to editing</button>
        <?php endif; ?>
      </div>
    </div>

  </div>
<?php
}
/** –– **/
