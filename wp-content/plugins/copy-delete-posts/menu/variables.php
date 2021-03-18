<?php
/**
 * Copy & Delete Posts – default menu.
 *
 * @package CDP
 * @subpackage SendingVariables
 * @author CopyDeletePosts
 * @since 1.0.0
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/** –– **\
 * There are constant (but dynamic per blog) variables.
 * @since 1.0.0
 */
function cdp_vars($hideTT = false, $cdp_plug_url = 'x', $post_id = false, $parent = false, $notify = false) {

  // $minutes = ((defined('CDP_SCRIPT_DEBUG'))?CDP_SCRIPT_DEBUG:false);
  $shouldShowNotify = false;
  $daysfrom = 0;
  $totalDays = 0;
  $textRev = '';
  if ($notify == true) {
    $reviews = get_option('_cdp_review', false);

    if ($reviews != false) {
      $now = time();
      $isMinutes = false; // Use below as minutes not hours
      $howMuchDaysMinutes = 30; // days/minutes number
      $remindDaysMins = 14; // days/minutes reminder
      $day = ((!$isMinutes)?60*60*24:60);
      $days = (60 * ((!$isMinutes)?(60*24):(1)) * $howMuchDaysMinutes);
      $reminds = (60 * ((!$isMinutes)?(60*24):(1)) * $remindDaysMins);
      $totalDays = floor(($now - $reviews['installed']) / $day);

      if (array_key_exists(get_current_user_id(), $reviews['users'])) {
        if (array_key_exists('dismiss', $reviews['users'][get_current_user_id()])) {
          if ($reviews['users'][get_current_user_id()]['dismiss'] !== true) {
            $dismissedon = $now - intval($reviews['users'][get_current_user_id()]['dismiss']);
            $daysfrom = floor($dismissedon / $reminds);
            if ($daysfrom >= $remindDaysMins) {
              $shouldShowNotify = true;
              $textRev = 'Because you wanted us to remind about it later, we did. ';
            }
          }
        }
      } else {
        $daysfrom = floor(($now - $reviews['installed']) / $day);
        if ($daysfrom >= $howMuchDaysMinutes) $shouldShowNotify = true;
      }

    }
  }

  ?>

  <?php if ($shouldShowNotify == true) { ?>
  <div id="cdp-review-notice" class="notice is-dismissible notice-success" style="margin-top:10px; overflow: hidden;">
    <p>We noticed you've been using the Copy & Delete Posts plugin for over <?php echo $totalDays; ?> <?php echo ((!$isMinutes)?'days':'minutes'); ?>. <?php echo $textRev; ?>Could you please do us a BIG favor and give us a nice review?</p>
		<p class="actions">
			<a id="cdp-review-review" href="https://bit.ly/2VeAf2E" target="_blank" class="button button-primary cdp-review-button cdp-tooltip-top" title="It will open WordPress review's page and dismiss this banner forever!">Yes, sounds fair!</a>
			<a id="cdp-review-already" href="#" style="margin-left:10px" class="cdp-review-button cdp-tooltip-top" title="Thank you, it will dismiss this banner forerver!">I already did!</a>
			<a id="cdp-review-later" href="#" style="margin-left:10px" class="cdp-review-button cdp-tooltip-top" title="It will show the banner again in 14 days">Remind me later</a>
			<a id="cdp-review-no" href="#" style="margin-left:10px" class="cdp-review-button cdp-tooltip-top" title="It will dismiss this banner forever!">No, not good enough<span id="cdp-review-sad"> :(</span></a>
		</p>
		<button type="button" class="notice-dismiss">
      <span class="screen-reader-text">Dismiss this notice.</span>
    </button>
  </div>
  <?php } ?>

  <script>
    if (typeof ajaxurl === 'undefined') ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
  </script>
  <div class="cdp-copy-alert-success" style="top: -28px; opacity: 0; display: none;">
    <img src="<?php echo $cdp_plug_url ?>/assets/imgs/copy.png" alt="Successfull copy image">
  </div>
  <div class="cdp-copy-loader-overlay" style="opacity: 0">
    <div class="cdp-text-overlay">
      <h1 style="color: white; font-size: 25px;">Please wait, copying in progress...</h1>
      <p>
        If you’re making a lot of copies it can take a while
        <br>(up to 5 minutes if you’re on a slow server).
      </p>
      <span>Average time is 8 copies per second.</span>
    </div>
    <div class="cdp-spinner"></div>
  </div>
  <input type="text" hidden id="cdp-purl" style="display: none; visibility: hidden;" value="<?php echo $cdp_plug_url ?>">
  <?php if ($hideTT == true): ?>
  <input type="text" hidden id="cdp-hideTT" style="display: none; visibility: hidden;" value="true">
  <?php endif; ?>

  <?php if ($post_id != false): ?>
  <input type="text" hidden id="cdp-current-post-id" style="display: none; visibility: hidden;" value="<?php echo $post_id ?>">
  <?php endif;?>

  <?php if ($parent != false): ?>
  <input type="text" hidden id="cdp-original-post" style="display: none; visibility: hidden;" data-cdp-parent="<?php echo $parent['title'] ?>" data-cdp-parent-url="<?php echo $parent['link'] ?>">
  <?php endif;?>

  <?php
}
/** –– **/
