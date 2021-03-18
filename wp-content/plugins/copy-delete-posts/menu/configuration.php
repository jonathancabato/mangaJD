<?php
/**
 * Copy & Delete Posts – default menu.
 *
 * @package CDP
 * @subpackage Configuration
 * @author CopyDeletePosts
 * @since 1.0.0
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/** –– **\
 * Adding assets.
 * @since 1.0.0
 */
  add_action('admin_enqueue_scripts', function() {
    if (cdp_check_permissions(wp_get_current_user()) == false) return;
  	$current_screen = get_current_screen();

  	if (!is_object($current_screen)) return;
  	if (function_exists('wp_doing_ajax') && wp_doing_ajax()) return;

  	wp_enqueue_script('cdp');
  	wp_enqueue_script('jquery-ui-draggable');
  	wp_enqueue_script('jquery-ui-droppable');
  	wp_enqueue_script('jquery-ui-sortable');
  	wp_enqueue_style('cdp-css');
  });
/** –– **/

/** –– **\
 * Main plugin configuration menu.
 * @since 1.0.0
 */
function cdp_configuration() {
  if (cdp_check_permissions(wp_get_current_user()) == false) return;

  global $cdp_plug_url;
  $current_user = wp_get_current_user();
  $user_id = get_current_user_id();
  $no_intro = (get_option('_cdp_no_intro')) ? get_option('_cdp_no_intro') : array();
  $has_intro = !in_array($user_id, $no_intro) || false;
  $profiles = get_option('_cdp_profiles');

  $defaults = get_option('_cdp_profiles');
  if ($defaults && array_key_exists('default', $defaults)) $defaults = $defaults['default'];
  else $defaults = cdp_default_options();

  $globals = get_option('_cdp_globals');
  $roles = get_editable_roles();

  $isCoolInstalled = '';
  if (get_option('_cdp_cool_installation', false)) {
    delete_option('_cdp_cool_installation');
    $isCoolInstalled = ' cdp_is_cool_installed';
  }

  $names_format = false;
  $after_copy = false;
  $post_converter = false;
  $gos = cdp_default_global_options();
  if (isset($defaults['names']))
    if (isset($defaults['names']['format'])) $names_format = intval($defaults['names']['format']);

  if (isset($globals)) {
    if (isset($globals['afterCopy'])) $after_copy = $globals['afterCopy'];
    if (isset($globals['postConverter'])) $post_converter = $globals['postConverter'];
    if (isset($globals['others'])) $gos = $globals['others'];
  }

  if (!array_key_exists('cdp-display-bulk', $gos)) $gos = cdp_default_global_options();

  // Ask for pro features
  $areWePro = areWePro();

  if (!$has_intro) {
    $intro = ' style="display: none;"';
    $content = '';
  } else {
    $intro = '';
    $content = ' style="display: none; opacity: 0;"';
  }

  $isYoast = false; $isUSM = false; $isWoo = false;
  if (is_plugin_active('woocommerce/woocommerce.php')) $isWoo = true;
  if (is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')) $isYoast = true;
  if (
    is_plugin_active('Ultimate-Premium-Plugin/usm_premium_icons.php') ||
    is_plugin_active('ultimate-social-media-icons/ultimate_social_media_icons.php') ||
    is_plugin_active('ultimate-social-media-plus/ultimate-social-media-plus.php') ||
    is_plugin_active('ultimate-social-media-plus/ultimate_social_media_plus.php')
  ) $isUSM = true;

  ?>

  <style>
    #wpcontent {padding-left: 2px !important;}
    /* #wpbody {overflow-y: scroll;overflow-x: auto;max-height: calc(100vh - 32px);} */
    #wpfooter { display: none; padding-bottom: 30vh; }
    #wpfooter #footer-left { display: none; }
    #wpfooter #footer-upgrade { display: none; }
  </style>
  <?php if ($areWePro && function_exists('cdpp_profile_manager_html')) cdpp_profile_manager_html(); ?>
  <?php if ($areWePro && function_exists('cdpp_delete_redi_modal')) cdpp_delete_redi_modal(); ?>
  <div class="cdp-preloader-c<?php echo $isCoolInstalled ?>">
    <div class="cdp-center">Loading...</div>
    <div class="cdp-preloader"></div>
  </div>
  <div class="cdp-container cdp-main-menu-cont" style="display: none;">
    <div class="cdp-content">
      <div class="cdp-cf">
        <div class="cdp-left">
          <!-- <h1 class="cdp-f-s-30 cdp-f-w-light cdp-welcome-title-after">Welcome<?php echo ' ' . $current_user->user_login . ','; ?> to Copy & Delete Posts!</h1> -->
          <h1 class="cdp-f-s-30 cdp-f-w-medium cdp-welcome-title-after">Welcome to Copy & Delete Posts!</h1>
        </div>
        <div class="cdp-right">
          <div class="cdp-s-i-a cdp-welcome-title-after cdp-text-right cdp-green"<?php echo $content ?>><a class="cdp-pointer" id="cdp-show-into-again"><span class="cdp-green cdp-f-s-16">Show intro</span></a></div>
        </div>
      </div>

      <div class="cdp-intro"<?php echo $intro ?>>
        <div class="cdp-box cdp-white-bg cdp-shadow">
          <div class="cdp-font-heading cdp-f-s-21 cdp-f-w-regular">You can now easily copy posts & pages in various areas:</div>
          <div class="cdp-cf">
            <div class="cdp-showcase-3 cdp-left">
              <div class="cdp-font-title cdp-f-s-22 cdp-f-w-bold cdp-center">List of posts/pages</div>
              <div class="cfg-img-sc-3 cdp-intro-image cdp-intro-img-1" alt="lists">
                <img src="<?php echo $cdp_plug_url; ?>/assets/imgs/intro_1.gif" class="cdp-no-vis cfg-img-sc-3" alt="lists">
              </div>
            </div>
            <div class="cdp-showcase-3 cdp-left">
              <div class="cdp-font-title cdp-f-s-22 cdp-f-w-bold cdp-center">Edit screen</div>
              <div class="cfg-img-sc-3 cdp-intro-image cdp-intro-img-2" alt="edit">
                <img src="<?php echo $cdp_plug_url; ?>/assets/imgs/intro_2.gif" class="cdp-no-vis cfg-img-sc-3" alt="edit">
              </div>
            </div>
            <div class="cdp-showcase-3 cdp-left">
              <div class="cdp-font-title cdp-f-s-22 cdp-f-w-bold cdp-center">Admin bar</div>
              <div class="cfg-img-sc-3 cdp-intro-image cdp-intro-img-3" alt="adminbar">
                <img src="<?php echo $cdp_plug_url; ?>/assets/imgs/intro_3.gif" class="cdp-no-vis cfg-img-sc-3 cfg-img-sc-3-special" alt="adminbar">
              </div>
            </div>
          </div>
          <div class="cdp-center cdp-font-footer">…and you can also <b>delete duplicate</b> posts easily, see below :)</div>
          <div class="cdp-center cdp-intro-options">
            <button class="cdp-intro-button cdp-f-s-21 cdp-f-w-bold">Got it, close intro!</button>
            <div class="cdp-ff-b1 cdp-checkboxes cdp-hide" style="margin-top: 5px;">
              <label for="cdp-never-intro">
                <input type="checkbox" checked id="cdp-never-intro" style="margin-top: -3px !important"/>
                Don't show this intro – never again!
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="cdp-cf cdp-profile-bar">
        <div class="cdp-left cdp-lh-48 cdp-f-s-20">
          <div class="cdp-cf">
            <div class="cdp-left cdp-f-w-light">
              Below are your
            </div>
            <select class="cdp-left cdp-profile-selected cdp-select-styled cdp-select cdp-select-padding cdp-ow-border cdp-f-s-19 cdp-color-p-i<?php echo ((!$areWePro)?' cdp-premium-in-select':''); ?>">
              <?php
              $preSelProf = get_option('_cdp_preselections')[intval(get_current_user_id())];
              if ($profiles != false && $areWePro) {
              foreach ($profiles as $profile => $vals):
                $isSel = ($preSelProf == $profile);
                ?>
                <option value="<?php echo htmlspecialchars($profile); ?>"<?php echo ($isSel)?' selected':''?>><?php echo ucfirst(htmlspecialchars($vals['names']['display'])); ?></option>
              <?php endforeach; } else { ?>
                <option value="default" selected>Default</option>
                <option value="premium" disabled>Add new</option>
              <?php } ?>
            </select>
            <div class="cdp-left cdp-f-w-light">
              settings
            </div>
          </div>
        </div>
        <div class="cdp-right cdp-lh-48 cdp-relative">
          <div>
            <span class="cdp-tooltip-premium" style="padding: 25px 0">
              <span class="cdp-manager-btns cdp-green cdp-hover cdp-pointer cdp-f-w-light cdp-f-s-16" style="padding-right: 33px;">+ Add / manage / import / export settings</span>
              <span class="cdp-premium-icon cdp-big-icon" style="right: 3px;"></span>
            </span>
          </div>
        </div>
      </div>
      <div class="cdp-collapsibles" style="padding-top: 5px;">

        <!-- SETTINGS PROFILE SECTION -->
        <div class="cdp-collapsible" data-cdp-group="mains">
          <div class="cdp-collapsible-title">
            <div class="cdp-cf">
              <div class="cdp-left cdp-ff-b1">Which <b class="cdp-ff-b4">elements</b> shall be copied?</div>
              <div class="cdp-right"><i class="cdp-arrow cdp-arrow-left"></i></div>
            </div>
          </div>
          <div class="cdp-collapsible-content cdp-nm cdp-np">
            <div style="overflow-x: auto; max-width: 100%;">
              <table class="cdp-table">
                <thead class="cdp-thead cdp-f-s-18">
                  <tr>
                    <th></th>
                    <th><b>If checked</b> copies will...</th>
                    <th><b>If <u class="cdp-f-w-bold">un</u>checked</b> copies will...</th>
                  </tr>
                </thead>
                <tbody class="cdp-ff-b1 cdp-f-s-18 cdp-tbody-of-settings">
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['title']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="title" type="checkbox" /><span>Title <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the title as defined in the <a href="#" class="cdp-go-to-names-chapter"><span class="cdp-green">next section</span></a>.</td>
                    <td>…be titled “Untitled”.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['date']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="date" type="checkbox" /><span>Date <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the same creation date & time as the original.</td>
                    <td>…get the date & time at time of copying. </td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['status']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="status" type="checkbox" /><span>Status <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get status of original article (which can be “published” or “deleted” etc.)</td>
                    <td>…get the status “Draft”.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['slug']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="slug" type="checkbox" /><span>Slug <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the same <a href="https://kinsta.com/knowledgebase/wordpress-slug/" target="_blank"><span class="cdp-green">slug</span></a> of the original. (However after publishing it will give it automatically a new slug because 2 pages cannot be on the same URL).</td>
                    <td>…get a blank slug, unless the page is published, then it will generate it automatically.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['excerpt']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="excerpt" type="checkbox" /><span>Excerpt <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the custom <a href="https://wordpress.org/support/article/excerpt/" target="_blank"><span class="cdp-green">excerpt</span></a> (post/page summary) of the original (if the original had any).</td>
                    <td>…get an empty <i>custom</i> excerpt (and default to taking the first 55 words of the post).</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['content']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="content" type="checkbox" /><span>Content <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get all the content (text, images, videos and other elements/blocks) from the original.</td>
                    <td>…get no content (be completely blank).</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['f_image']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="f_image" type="checkbox" /><span>Featured image <span class="cdp-info-icon"></span></span></label></td>
                    <td>…it will set the same <a href="https://firstsiteguide.com/wordpress-featured-image/" target="_blank"><span class="cdp-green">featured image</span></a> as the original.</td>
                    <td>…get no featured image.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['template']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="template" type="checkbox" /><span>Template <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the same page <a href="https://wpapprentice.com/blog/wordpress-theme-vs-template/" target="_blank"><span class="cdp-green">template</span></a> as original.</td>
                    <td>…get the default page template.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['format']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="format" type="checkbox" /><span>Format <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the same <a href="https://wordpress.org/support/article/post-formats/" target="_blank"><span class="cdp-green">post format</span></a> as original.</td>
                    <td>…get the standard post format.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['author']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="author" type="checkbox" /><span>Author <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the same author as original.</td>
                    <td>…get the user that is duplicating as an author.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['password']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="password" type="checkbox" /><span>Password <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the same <a href="https://wordpress.org/support/article/using-password-protection/" target="_blank"><span class="cdp-green">password</span></a> as original.</td>
                    <td>…get no password.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['attachments']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="attachments" type="checkbox" /><span>Attachments <span class="cdp-info-icon"></span></span></label></td>
                    <td>…create new <a href="https://wordpress.org/support/article/using-image-and-file-attachments/#attachment-to-a-post" target="_blank"><span class="cdp-green">attachments</span></a> (duplicates in Media Library) as well. <i>Recommended only for Multisites.</i></td>
                    <td>…get existing attachments from the original.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['children']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="children" type="checkbox" /><span>Children <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get their <a href="https://phppot.com/wordpress/how-to-create-a-child-page-in-wordpress/" target="_blank"><span class="cdp-green">child pages</span></a> copied as well, with all current settings applied to child-duplicates (if the page is a parent).</td>
                    <td>…not get their child pages copied along (if the page is a parent).</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['comments']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="comments" type="checkbox" /><span>Comments <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get all comments from the original.</td>
                    <td>…get no comments from the original.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['menu_order']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="menu_order" type="checkbox" /><span>Menu order <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get the <a href="https://wordpress.stackexchange.com/questions/25202/how-to-change-order-of-menu-items" target="_blank"><span class="cdp-green">menu order</span></a> from the original.</td>
                    <td>…get the menu order set to default (0).</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['category']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="category" type="checkbox" /><span>Categories <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get all categories from the original post.</td>
                    <td>…be Uncategorized, no categories will be copied.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['post_tag']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="post_tag" type="checkbox" /><span>Tags <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get all tags of the original post.</td>
                    <td>…be without any tags.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['taxonomy']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="taxonomy" type="checkbox" /><span>Taxonomies <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get all custom taxonomy from the original.</td>
                    <td>…be without custom taxonomy.</td>
                  </tr>
                  <tr>
                    <td>
                      <label>
                        <div class="cdp-cf">
                          <div class="cdp-left">
                            <input <?php echo $defaults['nav_menu']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="nav_menu" type="checkbox" />
                          </div>
                          <div class="cdp-left cdp-relative" style="width: calc(100% - 45px)">
                            <span>Navigation Menus <span class="cdp-info-icon" style="top: calc(50% + 4px) !important;"></span> </span>
                          </div>
                        </div>
                      </label>
                    </td>
                    <td>…get this private taxonomy from the original.</td>
                    <td>…be without private taxonomy.</td>
                  </tr>
                  <tr>
                    <td><label class="cdp-relative"><input <?php echo $defaults['link_category']=='true'?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="link_category" type="checkbox" /><span>Link categories <span class="cdp-info-icon"></span></span></label></td>
                    <td>…get this private taxonomy from the original.</td>
                    <td>…be without private taxonomy.</td>
                  </tr>

                  <tr<?php echo (!$isWoo)?' style="display: none;"':'' ?>>
                    <td class="cdp-tooltip-premium">
                      <label>
                        <div class="cdp-cf">
                          <div class="cdp-left">
                            <input <?php echo (array_key_exists('woo', $defaults) && $defaults['woo']=='true' && $areWePro == true)?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="woo" type="checkbox" />
                          </div>
                          <div class="cdp-left cdp-relative" style="width: calc(100% - 45px)">
                            <span>WooCommerce Settings<span class="cdp-premium-icon cdp-big-icon" style="top: calc(50% + 2px) !important;"></span> </span>
                          </div>
                        </div>
                      </label>
                    </td>
                    <td>…the same settings from the <a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><span class="cdp-green">WooCommerce plugin</span></a> as the original.</td>
                    <td>…get empty settings.</td>
                  </tr>

                  <tr<?php echo (!$isUSM)?' style="display: none;"':'' ?>>
                    <td class="cdp-tooltip-premium">
                      <label>
                        <div class="cdp-cf">
                          <div class="cdp-left">
                            <input <?php echo ($defaults['usmplugin']=='true' && $areWePro == true)?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="usmplugin" type="checkbox" />
                          </div>
                          <div class="cdp-left cdp-relative" style="width: calc(100% - 45px)">
                            <span>Ultimate Social Media Settings<span class="cdp-premium-icon cdp-big-icon" style="top: calc(50% + 2px) !important;"></span> </span>
                          </div>
                        </div>
                      </label>
                    </td>
                    <td>…the same settings from the <a href="https://www.ultimatelysocial.com/usm-premium/" target="_blank"><span class="cdp-green">Ultimate Social Media plugin</span></a> as the original.</td>
                    <td>…get empty settings.</td>
                  </tr>

                  <tr<?php echo (!$isYoast)?' style="display: none;"':'' ?>>
                    <td class="cdp-tooltip-premium">
                      <label>
                        <div class="cdp-cf">
                          <div class="cdp-left">
                            <input <?php echo ($defaults['yoast']=='true' && $areWePro == true)?'checked ':''; ?>class="cdp-data-set" data-cdp-opt="yoast" type="checkbox" />
                          </div>
                          <div class="cdp-left cdp-relative" style="width: calc(100% - 45px)">
                            <span>Yoast SEO Settings <span class="cdp-premium-icon cdp-big-icon" style="top: calc(50% + 2px) !important;"></span> </span>
                          </div>
                        </div>
                      </label>
                    </td>
                    <td>…the same settings from the <a href="https://wordpress.org/plugins/wordpress-seo/" target="_blank"><span class="cdp-green">Yoast SEO plugin</span></a> as the original.</td>
                    <td>…get empty settings.</td>
                  </tr>

                </tbody>
              </table>
            <div class="cdp-pad-lin cdp-gray cdp-f-s-18 cdp-lh-24 cdp-center" style="padding-top: 40px; padding-bottom: 20px;">
              <i>Do you know anything else you want to have copied (e.g. data added by a different plugin)? <br />
                Please <a href="mailto:hi@copy-delete-posts.com" target="_blank"><span class="cdp-green">tell us about it</span></a>, we always want to further improve this plugin! :) </i>
            </div>
            <div class="cdp-center cdp-padding cdp-p-35-b">
              <button class="cdp-button cdp-save-options">Save</button>
              <div class="cdp-padding cdp-f-s-17">
                <a href="#" class="cdp-close-chapter">Close section</a>
              </div>
            </div>
          </div>
        </div>
        </div>

        <!-- OTHER SETTINGS PROFILE SECTION -->
        <div class="cdp-collapsible" data-cdp-group="mains">
          <div class="cdp-collapsible-title cdp-name-section-cnx">
            <div class="cdp-cf">
              <div class="cdp-left cdp-ff-b1">What <b class="cdp-ff-b4">name(s)</b> should the copies have?</div>
              <div class="cdp-right"><i class="cdp-arrow cdp-arrow-left"></i></div>
            </div>
          </div>
          <div class="cdp-collapsible-content cdp-np cdp-drags-cont">
          <div class="cdp-pad-lin cdp-f-s-18 cdp-f-w-light">
            Build your preferred naming logic for the copies:
          </div>
          <div class="cdp-green-bg cdp-pad-lin" style="padding-bottom: 20px;">
            <div class="cdp-cf cdp-center">
              <div class="cdp-left cdp-names-input cdp-f-s-16">Prefix</div>
              <div class="cdp-left cdp-names-divider cdp-nlh"></div>
              <div class="cdp-left cdp-names-input cdp-f-s-16">Suffix</div>
            </div>
            <div class="cdp-cf cdp-center">
              <div class="cdp-left cdp-names-input">
                <div strip-br="true" class="cdp-magic-input cdp-shadow cdp-sorts cdp-names-real-input cdp-names-prefix" wrap="off" contenteditable="true" style="margin-right: 0">
                  <?php echo (isset($defaults['names']) && isset($defaults['names']['prefix']))?$defaults['names']['prefix']:''; ?>
                </div>
              </div>
              <div class="cdp-left cdp-names-divider cdp-f-s-19 cdp-f-w-light">
                <span class="cdp-tooltip-top cdp-togglable-name-b" title="Change to blank">(Name of original)</span>
              </div>
              <div class="cdp-left cdp-names-input">
                <div strip-br="true" class="cdp-magic-input cdp-shadow cdp-sorts cdp-names-real-input cdp-names-suffix" wrap="off" contenteditable="true" style="margin-left: 0">
                  <?php echo (isset($defaults['names']) && isset($defaults['names']['suffix']))?$defaults['names']['suffix']:''; ?>
                </div>
              </div>
            </div>
            <div class="cdp-curr-cont">
              <span class="cdp-f-s-18">Example based on current selections:</span>
              <span class="cdp-f-s-16 cdp-padding-10-h">
                <span class="cdp-example-name cdp-f-w-bold">(Name of original)</span>
              </span>
            </div>
          </div>
          <div class="cdp-pad-lin cdp-f-s-18">
            <div class="cdp-padding-23-h">Drag & drop the automatic elements into the Prefix/Suffix fields to add them.</div>
            <div class="">
              <div class="cdp-cf cdp-padding-10-h">
                <div class="cdp-left">
                  <div class="cdp-name-box cdp-drag-name cdp-name-clickable" oncontextmenu="return false;" data-cdp-val="0">Counter</div>
                </div>
                <div class="cdp-left cdp-names-text-info">Adds an <b class="cdp-f-w-semi-bold">incremental counter</b> (starting with “2”)</div>
              </div>
              <div class="cdp-cf cdp-padding-10-h">
                <div class="cdp-left" style="margin-top: 6px;">
                  <div class="cdp-name-box cdp-drag-name cdp-name-clickable" oncontextmenu="return false;" data-cdp-val="2">CurrentDate</div>
                </div>
                <div class="cdp-left cdp-names-text-info">
                  <div class="cdp-cf" style="line-height: 49px !important;">
                    <div class="cdp-left">
                      Adds the <b class="cdp-f-w-semi-bold">current date</b> in
                    </div>
                    <select class="cdp-left cdp-select-styled cdp-date-picked cdp-select cdp-dd-p-43 cdp-select-padding cdp-ow-border cdp-f-s-19 cdp-select-black cdp-option-premium" name="cdp-date-option">
                      <option value="1"<?php echo ($names_format == 1 || $names_format == false || (!$areWePro && $names_format == 3))?' selected':''; ?>>mm/dd/yyyy</option>
                      <option value="2"<?php echo ($names_format == 2)?' selected':''; ?>>dd/mm/yyyy</option>
                      <option value="3"<?php echo (($areWePro && $names_format == 3)?' selected':''); ?>>Custom</option>
                    </select>
                    <?php if ($areWePro && function_exists('cdpp_custom_date')) cdpp_custom_date($names_format, $defaults); ?>
                    <div class="cdp-left" style="padding-left: 15px;">format.</div>
                  </div>
                  <?php if ($areWePro && function_exists('cdpp_custom_date_info')) cdpp_custom_date_info(); ?>
                </div>
              </div>
              <div class="cdp-cf cdp-padding-10-h" style="padding-bottom: 0; margin-top: 6px;">
                <div class="cdp-left">
                  <div class="cdp-name-box cdp-drag-name cdp-name-clickable" oncontextmenu="return false;" data-cdp-val="1">CurrentTime</div>
                </div>
                <div class="cdp-left cdp-names-text-info">Adds the <b class="cdp-f-w-semi-bold">current time</b> in hh:mm:ss format</div>
              </div>
            </div>
            <div class="cdp-padding-23-h">
              <p class="cdp-f-s-18">You can also type tailored text into the fields above.</p>
              <p class="cdp-f-s-18">If you’re not of the drag & droppy-type, you can also enter shortcodes [Counter], [CurrentDate] and [CurrentTime].</p>
              <p class="cdp-f-s-18">If you make multiple copies in one go, use the Counter-option as otherwise some copies will have the same name.</p>
            </div>
            <div class="cdp-center">
              <button class="cdp-button cdp-save-options">Save</button>
              <div class="cdp-padding cdp-f-s-17">
                <a href="#" class="cdp-close-chapter">Close section</a>
              </div>
            </div>
          </div>
        </div>
        </div>

        <!-- GLOBAL SECTION -->
        <div class="cdp-collapsible" data-cdp-group="mains">
          <div class="cdp-collapsible-title">
            <div class="cdp-cf">
              <div class="cdp-left cdp-ff-b1"><b class="cdp-ff-b4">Other</b> options</div>
              <div class="cdp-right"><i class="cdp-arrow cdp-arrow-left"></i></div>
            </div>
          </div>
          <div class="cdp-collapsible-content cdp-oth-section cdp-np cdp-special-cb-p">
            <div class="cdp-pad-lin">
              <div><h2 class="cdp-f-s-18"><b class="cdp-f-w-bold">Navigation after copying</b></h2></div>
              <div class="cdp-padding-15-h">
                <div class="cdp-con-cen">
                  <select class="cdp-other-options cdp-select cdp-select-centered cdp-sel-separat cdp-select-large cdp-dd-p-40 cdp-c-x-a-v" name="after_copy">
                    <option value="1"<?php echo ($after_copy == '1' || $after_copy == false)?' selected':''; ?>>Leave me where I was</option>
                    <option value="2"<?php echo ($after_copy == '2')?' selected':''; ?>>Take me to the edit-page of the created copy</option>
                    <option value="3"<?php echo ($after_copy == '3')?' selected':''; ?>>Decide on a case-by-case basis (adds new button on copy screen)</option>
                  </select>
                </div>
                <div class="cdp-if-edit-page-selected cdp-con-cen cdp-f-s-18 cdp-f-w-light cdp-p-25-40" style="display: none;">If you created multiple copies in one go, you’ll be taken to the first copy. </div>
              </div>
              <div>
                <h2>
                  <b class="cdp-relative cdp-f-s-18 cdp-f-w-bold cdp-tooltip-premium" data-top="5" style="padding-right: 30px;">Pages vs. Posts converter <span class="cdp-premium-icon cdp-big-icon"></span></b>
                </h2>
              </div>
              <div class="cdp-f-s-18 cdp-f-w-light">
                <p class="cdp-f-s-18 cdp-f-w-light">By default, the type of what you copy does not change, i.e. if you copy a post the new version will also be a post, and the same for pages.</p>
                <p class="cdp-padding-15-h cdp-f-s-18 cdp-f-w-light">If you want to make a page out of a post, or vice versa, then you can do this on a <b class="cdp-f-w-bold">case by case basis</b> if you select the option “Define it specifically for this case” in the copy-tooltip, and then select this option on the following screen in the tooltip.</p>
                <p class="cdp-f-s-18 cdp-f-w-light">However, if you want it as a <b class="cdp-f-w-bold">default setting option</b>, then please select it below: </p>
              </div>
              <div class="cdp-con-cen">
                <div class="cdp-tooltip-premium" style="width: 663px; margin: 0 auto; height: 60px;" data-top="-10">
                  <select class="cdp-other-options cdp-select cdp-select-centered cdp-sel-separat cdp-select-large cdp-c-x-a-v" name="post_converter">
                    <option value="1"<?php echo ($post_converter == '1' || $post_converter == false)?' selected':''; ?>>Copies will be the same type as the original</option>
                    <option value="2"<?php echo ($post_converter == '2')?' selected':''; ?>>ALWAYS change the type when copied (posts will become pages, pages will become posts)</option>
                  </select>
                </div>
              </div>
              <div class=""><h2><b class="cdp-f-s-18 cdp-f-w-bold">User level permissions</b></h2></div>
              <div class="cdp-f-s-18 cdp-p-15-25 cdp-f-w-light">Which user role(s) should be able to copy & delete? <i style="color: gray">– The role also must have access to the dashboard.</i></div>
              <div class="cdp-p-25-40 cdp-f-s-18 cdp-f-w-light">
                <?php
                $isSaved = false;

                if (isset($globals)) $isSaved = true;
                foreach ($roles as $role => $value) {
                  $checked = '';
                  $rn = sanitize_text_field($role);
                  $role = sanitize_text_field($value['name']);
                  $d = ($role == 'Administrator')?' disabled checked="checked"':'';

                  if ($isSaved && $role != 'Administrator')
                    if (isset($globals['roles'][$rn]) && $globals['roles'][$rn] == 'true')
                      $checked = ' checked';

                  echo('<label for="cdp-roles-'.$rn.'"><input class="cdp-other-roles" id="cdp-roles-'.$rn.'"'.$checked.' type="checkbox"'.$d.' name="'.$rn.'">'.$role.'</label>');
                }
                ?>
              </div>
              <div class=""><h2><b class="cdp-f-s-18 cdp-f-w-bold">Content types which can be copied</b></h2></div>
              <div class="cdp-p-25-40 cdp-f-s-18 cdp-f-w-light">
                <label for="cdp-o-pages"><input <?php echo ($gos['cdp-content-pages'] == 'true')?'checked ':''; ?>id="cdp-o-pages" type="checkbox" class="cdp-other-inputs" name="cdp-content-pages">Pages</label>
                <label for="cdp-o-posts"><input <?php echo ($gos['cdp-content-posts'] == 'true')?'checked ':''; ?>id="cdp-o-posts" type="checkbox" class="cdp-other-inputs" name="cdp-content-posts">Posts</label>
                <label for="cdp-o-custom"><input <?php echo ($gos['cdp-content-custom'] == 'true')?'checked ':''; ?>id="cdp-o-custom" type="checkbox" class="cdp-other-inputs" name="cdp-content-custom">Custom posts types</label>
              </div>
              <div class=""><h2><b class="cdp-f-s-18 cdp-f-w-bold">Display copy option on...</b></h2></div>
              <div class="cdp-p-25-40 cdp-f-s-18 cdp-f-w-light">
                <label for="cdp-o-postspages"><input <?php echo ($gos['cdp-display-posts'] == 'true')?'checked ':''; ?>id="cdp-o-postspages" type="checkbox" class="cdp-other-inputs" name="cdp-display-posts">Posts/pages lists</label>
                <label for="cdp-o-edit"><input <?php echo ($gos['cdp-display-edit'] == 'true')?'checked ':''; ?>id="cdp-o-edit" type="checkbox" class="cdp-other-inputs" name="cdp-display-edit">Edit screens</label>
                <label for="cdp-o-admin"><input <?php echo ($gos['cdp-display-admin'] == 'true')?'checked ':''; ?>id="cdp-o-admin" type="checkbox" class="cdp-other-inputs" name="cdp-display-admin">Admin bar</label>
                <label for="cdp-o-bulk"><input <?php echo ($gos['cdp-display-bulk'] == 'true')?'checked ':''; ?>id="cdp-o-bulk" type="checkbox" class="cdp-other-inputs" name="cdp-display-bulk">Bulk actions menu</label>
                <label for="cdp-o-gutenberg"><input <?php echo ($gos['cdp-display-gutenberg'] == 'true')?'checked ':''; ?>id="cdp-o-gutenberg" type="checkbox" class="cdp-other-inputs" name="cdp-display-gutenberg">Gutenberg editor</label>
              </div>
              <div class=""><h2><b class="cdp-f-s-18 cdp-f-w-bold">Show reference to original item?</b></h2></div>
              <div class="cdp-f-s-18 cdp-f-w-light cdp-p-15-25">If checked, you will see a reference to the original post/page (on the copied version).</div>
              <div class="cdp-p-25-40 cdp-f-s-18 cdp-f-w-light">
                <label for="cdp-o-posts2"><input <?php echo ($gos['cdp-references-post'] == 'true')?'checked ':''; ?>id="cdp-o-posts2" type="checkbox" class="cdp-other-inputs" name="cdp-references-post">Posts/pages lists</label>
                <label for="cdp-o-edits2"><input <?php echo ($gos['cdp-references-edit'] == 'true')?'checked ':''; ?>id="cdp-o-edits2" type="checkbox" class="cdp-other-inputs" name="cdp-references-edit">Edit screens</label>
              </div>
              <div><h2><b class="cdp-f-s-18 cdp-f-w-bold">Additional features</b></h2></div>
              <div class="cdp-p-25-40 cdp-f-s-18 cdp-f-w-light">
                <label for="cdp-o-premium-hide-tooltip">
                  <?php if (!isset($gos['cdp-premium-hide-tooltip'])) $gos['cdp-premium-hide-tooltip'] = false; ?>
                  <input id="cdp-o-premium-hide-tooltip"<?php echo ((!$areWePro)?' disabled="true"':''); ?> <?php echo ($areWePro && $gos['cdp-premium-hide-tooltip'] == 'true')?'checked ':''; ?> type="checkbox" class="cdp-other-inputs" name="cdp-premium-hide-tooltip" />
                  <span class="cdp-relative cdp-tooltip-premium" data-top="5">Hide copy tooltip on hover and only show the button <span class="cdp-premium-icon cdp-big-icon" style="right: -30px"></span></span>
                </label>
                <label for="cdp-o-premium-import">
                  <?php if (!isset($gos['cdp-premium-import'])) $gos['cdp-premium-import'] = false; ?>
                  <input id="cdp-o-premium-import"<?php echo ((!$areWePro)?' disabled="true"':''); ?> <?php echo ($areWePro && $gos['cdp-premium-import'] == 'true')?'checked ':''; ?> type="checkbox" class="cdp-other-inputs" name="cdp-premium-import" />
                  <span class="cdp-relative cdp-tooltip-premium" data-top="5">Show post export & import buttons <span class="cdp-premium-icon cdp-big-icon" style="right: -30px"></span></span>
                </label>
              </div>
              <div class="cdp-center cdp-padding-15-h">
                <button class="cdp-button cdp-save-options">Save</button>
                <div class="cdp-padding cdp-f-s-17">
                  <a href="#" class="cdp-close-chapter">Close section</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="cdp-f-s-20 cdp-p-hh">
          ...and after your copy frenzy, you may need to…
        </div>

        <!-- DELETE SECTION -->
        <div class="cdp-collapsible" data-cdp-group="mains">
          <div class="cdp-d-xclicked cdp-collapsible-title">
            <div class="cdp-cf">
              <div class="cdp-left cdp-ff-b1"><b class="cdp-ff-b4">Delete duplicate posts/pages</b></div>
              <div class="cdp-right"><i class="cdp-arrow cdp-arrow-left"></i></div>
            </div>
          </div>
          <div class="cdp-collapsible-content cdp-d-section cdp-np">

            <div class="cdp-padding">
              <div class="cdp-backup-alert cdp-f-s-20 cdp-f-w-light">
                Before you delete anything here (which cannot be undone!) we <b class="cdp-f-w-bold">strongly suggest</b><br />
                that you create a backup, for example with <a href="https://wordpress.org/plugins/wp-clone-by-wp-academy/" target="_blank">this plugin</a>
              </div>
              <div class="cdp-cf cdp-tab-list">
                <div class="cdp-left cdp-tab-element cdp-tab-active" data-box="cdp-tabox-manual">
                  <span>Manual Cleanup</span>
                </div>
                <div class="cdp-left cdp-tab-element cdp-tooltip-premium" data-top="-4" data-box="cdp-tabox-automatic"<?php echo ((!$areWePro)?' data-disabled="true"':''); ?>>
                  <span class="cdp-relative">Automatic Cleanup <span class="cdp-premium-icon cdp-big-icon"></span></span>
                </div>
                <div class="cdp-left cdp-tab-element" data-box="cdp-tabox-redirects">
                  <span class="cdp-relative">Redirection <span class="cdp-premium-icon cdp-big-icon"></span></span>
                </div>
              </div>
              <div class="cdp-cont-d-box-tabed" id="cdp-tabox-redirects">
                <?php if ($areWePro && function_exists('cdpp_automated_redirects')) { ?>
                <?php cdpp_automated_redirects($cdp_plug_url); ?>
                <?php } else { ?>
                <div class="cdp-con-cen">
                  <div class="cdp-center cdp-padding" style="padding-top: 50px; padding-bottom: 30px;">
                    <img src="<?php echo $cdp_plug_url; ?>/assets/imgs/redirections.png" alt="">
                  </div>
                  <div class="cdp-lh-24 cdp-black-all" style="max-width: 82%; margin: 0 auto;">
                    <div class="cdp-f-s-19 cdp-f-w-regular cdp-padding">
                      As part of the <span class="cdp-green">premium plugin</span> you can enable redirects, so that the URLs of your deleted posts/pages automatically take visitors to the version which you decided to keep.
                    </div>
                    <div class="cdp-f-s-19 cdp-f-w-regular cdp-padding">
                      This isn’t just good for your visitors, but also for SEO: the “link juice” from your old (deleted) articles will be forwarded to the versions you keep, helping you to rank higher in search engines.
                    </div>
                    <div class="cdp-f-s-19 cdp-f-w-regular cdp-padding">
                      And: you can also use this feature for any other redirections you may need
                      (not only redirects from deleted posts/pages)!
                    </div>
                  </div>
                  <br />
                  <div class="cdp-center cdp-padding-15-h" style="padding-bottom: 60px;">
                    <a href="https://sellcodes.com/CylMIdJD" target="_blank">
                      <button class="cdp-button cdp-f-s-21 cdp-f-w-medium" style="width: 465px; height: 70px; border-radius: 35px;">Go premium now</button>
                    </a>
                  </div>
                </div>
                <?php } ?>
              </div>
              <div class="cdp-cont-d-box-tabed" id="cdp-tabox-automatic">
                <?php if ($areWePro && function_exists('cdpp_automated_deletion')) { ?>
                <?php cdpp_automated_deletion($cdp_plug_url); ?>
                <?php } ?>
              </div>
              <div class="cdp-cont-d-box-tabed" id="cdp-tabox-manual">

                <!-- ABOVE DELETION TABLE -->
                <div class="cdp-d-pad-sp" style="padding-top: 20px">
                  <div class="cdp-special-cb-p">
                    <div class="cdp-d-header cdp-f-s-19">
                      Scan for duplicates in...
                    </div>
                    <div class="cdp-p-25-40 cdp-f-s-18">
                      <label><input type="checkbox" name="cdp-d-a-posts" checked class="cdp-d-option cdp-d-first-chapter-cb" />Posts</label>
                      <label><input type="checkbox" name="cdp-d-a-pages" checked class="cdp-d-option cdp-d-first-chapter-cb" />Pages</label>
                      <label><input type="checkbox" name="cdp-d-a-customs" checked class="cdp-d-option cdp-d-first-chapter-cb" />Custom posts</label>
                    </div>
                  </div>
                  <div class="cdp-special-cb-p">
                    <div class="cdp-d-header cdp-f-s-19">
                      Count them as duplicates if they are identical with respect to <u class="cdp-f-w-bold">all</u> of the below...
                    </div>
                    <div>
                      <div class="cdp-p-25-t cdp-cf">
                        <div class="cdp-left cdp-f-s-18">
                          <label style="margin-right: 5px;"><input type="checkbox" checked name="cdp-d-b-title" class="cdp-d-option"/>Title</label>
                        </div>
                        <div class="cdp-left" style="margin-top: 1px; margin-left: 5px; font-size: 13px;">
                          <a href="#" class="cdp-show-more-d-title cdp-f-s-16" style="line-height: 43px;">(show more options)</a>
                        </div>
                        <div class="cdp-left cdp-f-s-18" style="margin-left: 50px;">
                          <label><input type="checkbox" name="cdp-d-b-slug" class="cdp-d-option" />Similar slug <span class="cdp-tooltip-top" title="Slugs are never 100% identical (i.e. Wordpress adds a counter automatically to ensure they are unique). The rule to only have them at least 85% identical does the job fine (you can see after the scan which posts are considered identical).">(x ≥ 85%)</span></label>
                        </div>
                      </div>
                      <div class="cdp-p-20-h cdp-more-d-title" style="display: none; padding-left: 37px;">
                        <div class="cdp-f-s-17 cdp-p-20-b cdp-lh-24">
                          Do you want to consider different titles still to be identical if a) the copied posts/pages<br />were created by this plugin and b) they were not modified thereafter?
                        </div>
                        <div class="cdp-cf">
                          <label class="cdp-left cdp-f-s-18"><input type="radio" class="cdp-d-option cdp-radio" value="0" name="cdp-radio-btn-dtitles" checked>No</label>
                          <label class="cdp-left cdp-f-s-18"><input type="radio" class="cdp-d-option cdp-radio" value="1" name="cdp-radio-btn-dtitles">Yes</label>
                          <span class="cdp-green cdp-f-s-17 cdp-tooltip-top cdp-left" title="The copies you created may have been given different titles automatically (according to the rules in <a href='#' class='cdp-go-to-names-chapter'>this section</a>) and therefore would not count as duplicates as they have different titles.<br /><br />To remedy this, you can select “Yes” here so that those posts/pages also get considered as duplicates." style="line-height: 44px;">When does “yes” make sense here?</span>
                        </div>
                      </div>
                      <!-- <div class="cdp-padding-15-h cdp-f-s-18">
                      </div> -->
                      <div class="cdp-cf cdp-p-40-b">
                        <label class="cdp-left cdp-f-s-18" style="margin-right: 21px;">
                          <input type="checkbox" name="cdp-d-c-excerpt" class="cdp-d-option" />Excerpt (<span class="cdp-no-empty-text"><b>including</b> empty</span>)
                        </label>
                        <label class="cdp-left cdp-f-s-18" style="margin-right: 90px !important;"><input type="checkbox" name="cdp-d-c-count" class="cdp-d-option"/>Word count</label>
                        <div class="cdp-left cdp-f-s-17" style="line-height: 43px;">
                          ...need others? <a href="mailto:hi@copy-delete-posts.com" target="_blank">Suggest them!</a>
                        </div>
                      </div>
                    </div>
                    <div class="cdp-d-option-select-parent cdp-padding-15-h cdp-center cdp-ntp">
                      <div class="cdp-inline cdp-cf">
                        <select class="cdp-left cdp-d-option-select cdp-pad-49-list cdp-select-large cdp-max-600 cdp-select cdp-select-centered cdp-sel-separat" name="cdp-d-sels-diftyp">
                          <option value="1">Only count pages/posts of the same type as duplicates</option>
                          <option value="2">Also count pages/posts of different types as duplicates</option>
                        </select>
                        <span class="cdp-left cdp-green">
                          <div style="margin-left: 15px; line-height: 51px;">
                            <span class="cdp-tooltip-top" title='Select the “same type”-option if the pages/posts have to be of the same type (i.e. post / page / specific custom post category) in order to count as duplicates. If you select “cross-type” then pages/posts of different types will also be considered as duplicates.'>Huh?</span>
                          </div>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="">
                    <div class="cdp-d-header cdp-f-s-19">
                      Which version do you want to keep?
                    </div>
                    <div class="cdp-p-30-h cdp-center">
                      <select class="cdp-d-option-select cdp-pad-49-list cdp-select-large cdp-select cdp-select-centered cdp-sel-separat" name="cdp-d-d-sel-which">
                        <option value="1">Keep the oldest duplicate (typically the original)</option>
                        <option value="2">Keep the newest duplicate (typically the last copy you made)</option>
                        <option value="3">Delete ALL duplicates, don’t keep any (Be careful!)</option>
                      </select>
                    </div>
                  </div>
                  <div class="cdp-relative cdp-f-s-19">
                    <span class="cdp-tooltip-premium" data-top="0">
                      <b>Filter results (optional)</b>: Only list them, if they<span id="cdpp-switch-mf"> […]</span> <span class="cdp-premium-icon cdp-big-icon"></span>
                    </span>
                  </div>
                  <?php if ($areWePro && function_exists('cdpp_more_filters')) cdpp_more_filters(); ?>
                  <br />
                  <div class="cdp-center cdp-p-30-h">
                    <button class="cdp-button cdp-delete-btn cdp-d-search cdp-rl-round cdp-f-w-bold" type="button" name="button">Scan for duplicates now!<br /><small class="cdp-sm-d cdp-f-s-17 cdp-f-w-medium">(at this point nothing gets deleted)</small></button>
                  </div>
                </div>

                <div class="cdp-padding-15-h">
                  <div class="cdp-cf cdp-d-pad-sp cdp-not-yet-search" style="display: none; padding-bottom: 30px;">
                    <div class="cdp-left cdp-f-s-19 cdp-f-w-bold" style="line-height: 41px;">
                      Scan has found [<span id="cdp-d-table-pagi-ilosc-wynikow" class="cdp-f-w-bold"></span>] duplicates
                    </div>
                    <div class="cdp-right">
                      <input type="text" class="cdp-d-searchbox-c" name="cdp-d-searchbox" placeholder="Search...">
                    </div>
                    <div class="cdp-right cdp-f-s-19" style="padding-right: 45px;">
                      <div class="cdp-cf" style="line-height: 41px;">
                        <div class="cdp-left">
                          Show
                        </div>
                        <select class="cdp-left cdp-select cdp-ow-border cdp-per-page-select-show cdp-width-166">
                          <option value="5">5</option>
                          <option value="10">10</option>
                          <option value="25" selected>25</option>
                          <option value="40">40</option>
                          <option value="50">50</option>
                          <option value="60">60</option>
                          <option value="75">75</option>
                          <option value="100">100</option>
                        </select>
                        <div class="cdp-left">
                          per page
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- DELETION TABLE -->
                  <div class="cdp-table-cont cdp-not-yet-search" style="display: none;">
                    <table id="cdp-d-table" class="cdp-delete-table">
                      <thead>
                        <tr class="cdp-f-s-19 cdp-f-w-medium">
                          <th><label><input type="checkbox" class="cdp-d-checkbox-all"/></label></th>
                          <th>Title</th>
                          <th>Slug/URL</th>
                          <th>Type</th>
                          <th>Date created</th>
                          <th># of words</th>
                        </tr>
                      </thead>
                      <thead>
                        <tr data-ignore="true"><td class="cdp-h-tbe" colspan="6"></td></tr>
                      </thead>
                      <tbody id="cdp-d-table-tbody"></tbody>
                      <tfoot>
                        <tr data-ignore="true"><td class="cdp-h-tbe" colspan="6"></td></tr>
                      </tfoot>
                    </table>
                  </div>

                  <!-- BELOW DELETION TABLE -->
                  <div class="cdp-d-pad-sp">
                    <div class="cdp-cf cdp-not-yet-search" style="display: none;">
                      <div class="cdp-d-sel-all-con cdp-left cdp-f-w-light cdp-f-s-17">
                        <u class="cdp-d-select-all cdp-f-w-light">Select all</u> (also from other pages)
                      </div>
                      <div class="cdp-center cdp-d-pagi-cent cdp-left">
                        <div id="cdp-d-table-pagi" class="cdp-pagination"></div>
                      </div>
                    </div>
                    <div class="cdp-center cdp-p-10-h cdp-not-yet-search" style="display: none;">
                      <div class="cdp-delete-info cdp-f-w-light cdp-f-s-19">
                        You selected <b class="cdp-t-d-ct cdp-f-w-light">0</b> pages/posts to be deleted
                      </div>
                    </div>
                    <div class="cdp-p-10-h cdp-not-yet-search" style="display: none;">
                      <div class="cdp-d-header-2 cdp-f-s-19 cdp-f-w-light">
                        Steps to deletion:
                      </div>
                      <div class="cdp-margin-left cdp-f-s-19">
                        <div class="cdp-p-10-h">
                          <div class="cdp-cf cdp-low-margin-bot" style="line-height: 28px;">
                            <div class="cdp-left cdp-blue-circle">1</div>
                            <div class="cdp-left">&nbsp;Make sure you created a backup with, e.g. with <a href="https://wordpress.org/plugins/wp-clone-by-wp-academy/" target="_blank">this plugin</a>.</div>
                          </div>
                          <div class="cdp-cf cdp-low-margin-bot" style="line-height: 28px;">
                            <div class="cdp-left cdp-blue-circle">2</div>
                            <div class="cdp-left">&nbsp;Select all the posts & pages which should be deleted (by ticking the checkboxes in the table above).</div>
                          </div>
                          <div class="cdp-cf cdp-low-margin-bot" style="line-height: 28px;">
                            <div class="cdp-left cdp-blue-circle">3</div>
                            <div class="cdp-left">&nbsp;Check if you need these features (optional):</div>
                          </div>
                        </div>
                        <div class="cdp-margin-left-25 cdp-p-20-h cdp-nbp" style="padding-top: 0px">
                          <table>
                            <tbody>
                              <tr>
                                <td class="cdp-vtop-pad">Automatic redirection</td>
                                <td>
                                  <div class="cdp-relative">
                                    <span class="cdp-tooltip-premium" style="padding: 25px 0;">
                                      <select class="cdp-p-redirections cdp-select cdp-ow-border cdp-dis-en-opt" name="cdp-redirections">
                                        <option value="0">Disabled</option>
                                        <option value="1">Enabled</option>
                                      </select>
                                    </span>
                                    <div class="cdp-premium-icon cdp-big-icon" style="margin-left: 17px;"></div>
                                  </div>
                                  <div class="cdp-d-tp-pad cdp-f-s-17 cdp-lh-24">Enable this if you want to redirect the urls from your deleted posts/pages to the main one you decided to keep.</div>
                                </td>
                              </tr>
                              <tr>
                                <td class="cdp-vtop-pad">Deletion throttling</td>
                                <td>
                                  <div class="cdp-cf">
                                    <select class="cdp-left cdp-d-throttling cdp-select cdp-ow-border cdp-dis-en-opt" name="cdp-throttling">
                                      <option value="0">Disabled</option>
                                      <option value="1">Enabled</option>
                                    </select>
                                    <div class="cdp-left cdp-inline cdp-cf cdp-d-throttling-count-p" style="display: none; line-height: 41px;">
                                      <div class="cdp-left">
                                        <span style="padding: 0px 15px;">Delete</span>
                                      </div>
                                      <div class="cdp-left">
                                        <input type="number" class="cdp-d-throttling-count cdp-number-field-styled" name="cdp-throttling-count" min="1" max="10240" placeholder="50">
                                      </div>
                                      <div class="cdp-left">
                                        <span style="padding: 0px 15px;">per minute</span>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="cdp-d-tp-pad cdp-f-s-17 cdp-lh-24">
                                    Enable this if you want to have your posts/pages getting deleted in batches (instead of all at once).<br />This reduces the risk of timeouts if you have a lot to delete.<br />
                                    <span class="cdp-even-more-th-info" style="display: none">
                                      If it’s necessary the process will dynamically slow down - depending on your server’s resource consumption. For example, if you’re using another plugin which is running a background process and it takes a lot of resources (+50%), our plugin will wait/slow down until the other process is complete.
                                    </span>
                                  </div>
                                </td>
                              </tr>
                              <tr>
                                <td class="cdp-vtop-pad" style="padding-top: 4px">Move post(s) to trash?</td>
                                <td>
                                  <div class="">
                                    <span class="cdp-tooltip-premium" style="padding: 25px 0">
                                      <label class="cdp-relative" style="padding-right: 25px;"><input type="checkbox" class="cdp-p-just-trash-them" /> Yes, keep deleted posts in trash! <span class="cdp-premium-icon cdp-big-icon"></span></label>
                                    </span>
                                    <div class="cdp-d-tp-pad cdp-f-s-17 cdp-lh-24">Select this option to move deleted posts to trash (instead of deleting them permanently right away).</div>
                                  </div>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="">
                      <div class="cdp-not-yet-search" style="display: none;">
                        <hr class="cdp-hr">
                        <div class="cdp-center cdp-padding-15-h cdp-f-s-19">
                          <label><input type="checkbox" class="cdp-d-just-check-it"> I completed <u>all</u> steps, it’s ok!</label>
                        </div>
                        <div class="cdp-center cdp-p-10-h">
                          <button type="button" class="cdp-button cdp-red-bg cdp-d-real-delete cdp-f-s-19" name="button">Delete selected pages/posts!</button>
                        </div>
                        <div class="cdp-center cdp-padding-15-h cdp-f-s-19">
                          You will be notified when the deletion process ends via <span class="cdp-green">Admin Bar Menu</span>.
                        </div>
                      </div>
                      <div class="cdp-padding cdp-f-s-17 cdp-center">
                        <a href="#" class="cdp-close-chapter">Close section</a>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

          </div>
        </div>

        <?php if (function_exists('cdpp_license_status')) cdpp_license_status(); ?>
        <?php if (function_exists('cdpp_license')) cdpp_license(); ?>
      </div>

      <div class="cdp-f-s-20 cdp-p-hh cdp-center cdp-relative">
        <b>Questions?</b> We're happy to help in the <a href="https://wordpress.org/support/plugin/copy-delete-posts/" target="_blank" style="text-decoration: none;">Support Forum</a>. <span class="cdp-info-icon cdp-tooltip-top" title="Your account on Wordpress.org (where you open a new support thread) is different to the one you login to your WordPress dashboard (where you are now). If you don't have a WordPress.org account yet, please sign up at the top right on here. It only takes a minute :) Thank you!"></span>
      </div>

      <jdiv class="label_e50 _bottom_ea7 notranslate" id="cdp_jvlabelWrap-fake" style="background: linear-gradient(95deg, rgb(47, 50, 74) 20%, rgb(66, 72, 103) 80%);right: 30px;bottom: 0px;width: 310px;">
    		<jdiv class="hoverl_bc6"></jdiv>
    		<jdiv class="text_468 _noAd_b4d contentTransitionWrap_c73" style="font-size: 15px;font-family: Arial, Arial;font-style: normal;color: rgb(240, 241, 241);position: absolute;top: 8px;line-height: 13px;">
    			<span><?php _e('Connect with support (click to load)', 'copy-delete-posts'); ?></span><br>
    			<span style="color: #eee;font-size: 10px;">
    				<?php _e('This will establish connection to the chat servers', 'copy-delete-posts'); ?>
    			</span>
    		</jdiv>
    		<jdiv class="leafCont_180">
    			<jdiv class="leaf_2cc _bottom_afb">
    				<jdiv class="cssLeaf_464"></jdiv>
    			</jdiv>
    		</jdiv>
    	</jdiv>

    </div>
  </div>

  <?php
}
/** –– **/

?>
