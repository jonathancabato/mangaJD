<?php

/**
 * The file that defines the core plugin class
 *
 * @link       https://tcj.rocks
 * @since      1.0.0
 *
 * @package    ActiveTools
 * @subpackage ActiveTools/includes
 */

namespace ActiveTools;

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    ActiveTools
 * @subpackage ActiveTools/includes
 * @author     Randall Bezant <rbezant@tcj.rocks>
 */
class Plugin
{

    protected string $plugin_name;

    protected string $version;

    public function __construct()
    {
        $this->version = \ActiveTools\VERSION;
        $this->plugin_name = 'active-tools';

    }

    public function boot() {

        // Protection against visitors
        GuardDog::getInstance();

        // Cron Tasks
        new Cron();

        // WP-Admin Pages & Options
        $adminPage = new Admin\MainAdminPage();
        new Admin\SiteOptionsAdminPage( $adminPage );
        new Admin\ContentProtectionAdminPage( $adminPage );
        new Admin\GuardDogAdminPage( $adminPage );
        new Admin\PostMetaOptions();
        new Admin\UserMetaOptions();

        // Uberwachen Admin page
        add_action( 'admin_menu', [ $this, 'maybe_init_uberwachen_page'] );
        add_filter( 'set-screen-option', [ $this, 'set_screen_option' ], 10, 3 );
        add_action('wp_ajax_at_uberwachen_set_user_bad_xp', [$this, 'ajax_at_uberwachen_set_user_bad_xp'] );
        add_action('wp_ajax_at_uberwachen_set_user_trust', [$this, 'ajax_at_uberwachen_set_user_trust'] );
        add_action('wp_ajax_at_uberwachen_set_user_purchase_rate', [$this, 'ajax_at_uberwachen_set_user_purchase_rate'] );
        add_action('wp_ajax_at_uberwachen_get_user_points_history', [$this, 'ajax_at_uberwachen_get_user_points_history'] );

        // Roles
        add_action( 'init', [$this, 'role_management'] );

        // Post protection
        ContentProtection::getInstance();

        // Clean up some menu junk
        add_action( 'admin_bar_menu', [ $this, 'admin_bar_management' ], PHP_INT_MAX );
        add_action( 'admin_menu', [ $this, 'admin_menu_management' ], PHP_INT_MAX );
        add_action( 'after_setup_theme', [ $this, 'maybe_hide_admin_bar' ], PHP_INT_MAX );

        new Frontend\Setup();
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_version()
    {
        return $this->version;
    }

    public function maybe_init_uberwachen_page() {

        if ( ! defined( '\AIO_WP_SECURITY_VERSION' ) || ! version_compare( \AIO_WP_SECURITY_VERSION, '4.4', '>=') ) {
            return;
        }

        new Admin\UberwachenPage();
    }

    public function set_screen_option( $keep, $option, $value ) {

        if ( 'at_uberwachen_per_page' == $option ) return $value;

        return $keep;
    }

    public function role_management() {

        $admin_role = get_role( 'administrator' );

        if ( ! $admin_role->has_cap( 'uberwachen' ) ) {
            $admin_role->add_cap( 'uberwachen', true );
        }

        // remove_role( 'schutzstaffel' );

        if ( get_role( 'schutzstaffel' ) === null ) {
            add_role(
                'schutzstaffel',
                'Schutzstaffel',
                array(
                    'read' => true,
                    'uberwachen' => true,
                ),
            );

            return;
        }

        $schutzstaffel_role = get_role( 'schutzstaffel' );

        $schutzstaffel_role->add_cap( 'uberwachen', true );
    }

    function ajax_at_uberwachen_set_user_bad_xp() {

        check_ajax_referer( 'at-uberwachen-set-user-bad-xp', 'security' );

        if ( ! is_user_logged_in() || ! current_user_can( 'uberwachen' )  ) {
            wp_send_json_error('Unauthorized Request');
        }

        if ( empty( $_POST['user_id'] ) || empty( $_POST['bad_xp']) || !in_array( $_POST['bad_xp'], ['yes', 'no'] ) ) {
            wp_send_json_error('Invalid Request.');
        }

        $user_id = absint( $_POST['user_id'] );
        $user = get_user_by( 'ID', $user_id );

        $protected_roles = [];

        foreach ( get_option( '_at_uw_p_ur', [] ) as $role ) {
            $protected_roles[] = $role['value'];
        }

        // Abort if user is protected from Überwachen.
        if ( user_can( $user, 'uberwachen' ) || array_intersect( $protected_roles, $user->roles ) ) {
            wp_send_json_error('This user is protected from being edited via Überwachen.');
        }

        update_user_meta( $user_id, '_at_cp_be_e', $_POST['bad_xp'] );

        wp_send_json_success();
    }

    function ajax_at_uberwachen_set_user_trust() {

        check_ajax_referer( 'at-uberwachen-set-user-trust', 'security' );

        if ( ! is_user_logged_in() || ! current_user_can( 'uberwachen' )  ) {
            wp_send_json_error('Unauthorized Request');
        }

        if ( empty( $_POST['user_id'] ) || ! isset( $_POST['trust_level'] ) || ! in_array( $_POST['trust_level'], ['10', '0', '-5', '-10'] ) ) {
            wp_send_json_error('Invalid Request.');
        }

        $user_id = absint( $_POST['user_id'] );
        $user = get_user_by( 'ID', $user_id );

        $protected_roles = [];

        foreach ( get_option( '_at_uw_p_ur', [] ) as $role ) {
            $protected_roles[] = $role['value'];
        }

        // Abort if user is protected from Überwachen.
        if ( user_can( $user, 'uberwachen' ) || array_intersect( $protected_roles, $user->roles ) ) {
            wp_send_json_error('This user is protected from being edited via Überwachen.');
        }

        update_user_meta( $user_id, '_at_uw_ut', $_POST['trust_level'] );

        wp_send_json_success();
    }

    function ajax_at_uberwachen_set_user_purchase_rate() {

        check_ajax_referer( 'at-uberwachen-set-user-purchase-rate', 'security' );

        if ( ! is_user_logged_in() || ! current_user_can( 'uberwachen' )  ) {
            wp_send_json_error('Unauthorized Request');
        }

        if ( empty( $_POST['user_id'] ) || ! isset( $_POST['purchase_rate'] ) ) {
            wp_send_json_error('Invalid Request.');
        }

        if ( $_POST['purchase_rate'] != '-1' && ! preg_match( '/^-?[\d]+$/', $_POST['purchase_rate'] ) ) {
            wp_send_json_error('Invalid Request.');
        }
        $purchase_rate = filter_var( $_POST['purchase_rate'], FILTER_VALIDATE_INT );

        $user_id = absint( $_POST['user_id'] );
        $user = get_user_by( 'ID', $user_id );

        $protected_roles = [];

        foreach ( get_option( '_at_uw_p_ur', [] ) as $role ) {
            $protected_roles[] = $role['value'];
        }

        // Abort if user is protected from Überwachen.
        if ( user_can( $user, 'uberwachen' ) || array_intersect( $protected_roles, $user->roles ) ) {
            wp_send_json_error('This user is protected from being edited via Überwachen.');
        }

        update_user_meta( $user_id, '_at_cp_mc_cpr_l', $purchase_rate );

        wp_send_json_success();
    }
    function ajax_at_uberwachen_get_user_points_history() {

        check_ajax_referer( 'at-uberwachen-get-user-points-history', 'security' );

        if ( ! is_user_logged_in() || ! current_user_can( 'uberwachen' )  ) {
            wp_send_json_error('Unauthorized Request');
        }

        if ( empty( $_POST['user_id'] ) ) {
            wp_send_json_error('Invalid Request.');
        }

        $user_id = absint( $_POST['user_id'] );
        $user = get_user_by( 'ID', $user_id );

        global $wpdb;

        $query = "
            SELECT *
            FROM {$wpdb->prefix}myCRED_log
            WHERE user_id = {$user_id}
            ORDER BY time DESC;
        ";

        $results = $wpdb->get_results( $query, ARRAY_A  );

        if ( empty( $results ) ) {
            wp_send_json_success( '<div class="no-results">This user has no transaction history</div>' );
        }

        $rate_limit = get_user_meta( $user_id, '_at_cp_mc_cpr_l', true );
        if ( $rate_limit === '' || $rate_limit === '-1' ) {
            $rate_limit = filter_var( get_option( '_at_cp_mc_cpr_l', 0 ), FILTER_VALIDATE_INT );
        }

        $rate_limit = filter_var( $rate_limit, FILTER_VALIDATE_INT );

        $hours = floor($rate_limit / 60);
        $minutes = floor(( $rate_limit ) % 60);

        $rate_limit_text = '<span style="font-style:italic;">With no Content Purchase Rate Limit</span>';

        if ( $rate_limit ) {
            $rate_limit_text = '<span style="font-style:italic;">With a Content Purchase Rate Limit of<span><h2 style="margin: 10px 0 8px;">';

            if ( $hours ) {
                $rate_limit_text .= $hours . 'h ';
            }
            if ( $minutes ) {
                $rate_limit_text .= $minutes . 'm';
            }

            $rate_limit_text .= '</h2>';
        }

        $rate_limit = $rate_limit * 60;

        $datetime = new \DateTime();

        ob_start();
        ?>
        <div class="nice-scroll" style="overflow-y: auto;overflow-x: hidden;max-height: 400px;min-height: 200px;height: 50vh;">
            <div style="text-align:center;">
                <h1 style="margin: 10px 0 12px;"><?php esc_html_e( $user->user_login ); ?></h1>
                <?php echo $rate_limit_text ; ?>
            </div>
            <table>
                <thead>
                <th class="item-date">Date (<?php echo str_replace( 'America/', '', get_option('timezone_string') ); ?> Time)</th>
                <th class="item-date-delta">Δ Time</th>
                <th class="item-points">Points</th>
                <th class="item-entry">Entry</th>
                </thead>
                <tbody>
                <?php for( $i = 0; $i < count( $results ); $i++ ) : ?>
                    <tr>
                        <td class="item-date"><?php esc_html_e( $datetime->setTimestamp( $results[$i]['time'] )->format( 'Y-m-d H:i:s' ) ); ?></td>
                        <?php

                        $delta = 0;
                        if ( $i < count( $results ) - 1 ) {
                            $delta = absint( $results[$i]['time'] ) - absint( $results[ $i + 1 ]['time'] );
                            $hours = floor($delta / 3600);
                            $minutes = floor(($delta / 60) % 60);
                            $seconds = $delta % 60;

                            $delta_time = '';
                            if ( $hours ) {
                                $delta_time = $hours . 'h ';
                            }
                            if ( $minutes ) {
                                $delta_time .= $minutes . 'm ';
                            }
                            if ( $seconds && ! $hours ) {
                                $delta_time .= $seconds . 's ';
                            }
                        } else {
                            $delta_time = '-';
                        }

                        $delta_class = '';

                        if ( $results[$i]['ref'] == 'buy_content' && $delta && $delta >= $rate_limit ) {

                            $lvl = 60 - min( 60, $delta - $rate_limit );

                            $delta_class = ' at-uberwachen-color-danger_' . round( absint( $lvl * 1.66667 ) / 5 ) * 5;
                        }
                        ?>
                        <td class="item-date-delta<?php echo $delta_class; ?>"><?php esc_html_e( $delta_time ); ?></td>
                        <td class="item-points"><?php esc_html_e( $results[$i]['creds'] ); ?></td>

                        <?php if ( $results[$i]['ref'] == 'buy_content' ): ?>
                            <?php
                            $post = get_post( absint( $results[$i]['ref_id'] ) );
                            ?>
                            <td class="item-entry">Purchased <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank"><?php esc_html_e( $post->post_title ); ?></a></td>
                        <?php else : ?>
                            <td class="item-entry">Credits Purchase</td>
                        <?php endif; ?>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            <div style="font-style:italic;padding-top:10px;opacity:0.8;"><b>Δ Time Coloring</b>: The color will go from green to red the closer it is to the user's <b>current</b> rate limit, if the transaction occurred within 60 seconds of the rate limit. Green is used for beyond 60 seconds, and Black is used when Δ Time is less than the rate limit.<p>Note that the rate limit could have been something else at the time of that transaction. For example, Black colorings are likely due to having had a lower rate limit than the current rate limit.</p></div>
        </div>
        <?php
        $output = ob_get_clean();

        wp_send_json_success( $output );
    }

    public function admin_bar_management( \WP_Admin_Bar $admin_bar ) {

        $ss = current_user_can( 'schutzstaffel' );
        $uw = current_user_can( 'uberwachen' );

        $admin_bar->remove_menu('boldgrid-adminbar-icon' );
        $admin_bar->remove_menu('updraft_admin_node' );
        $admin_bar->remove_menu('booter' );
        $admin_bar->remove_menu('pfp' );
        $admin_bar->remove_menu('reseller-adminbar-icon' );
        $admin_bar->remove_menu('wp-logo' );

        if ( $ss && !is_admin() ) {
            $admin_bar->remove_menu('site-name' );
        }
        if ( $ss && is_admin() ) {
            $admin_bar->remove_menu('view-site' );
        }
        if ( ! current_user_can( 'administrator' ) ) {
            $admin_bar->remove_menu('top-secondary' );
        }


        if ( ( !$ss && $uw ) ||  ( $ss && ! is_admin() ) ) {
            $admin_bar->add_menu( array(
                'id'    => 'uberwachen-top-menu-item',
                'parent' => null,
                'group'  => null,
                'title' => '<span class="dashicons dashicons-visibility" style="font-family: dashicons !important;width: 22px;font-size: 22px;line-height: 31px;background: none;"></span> Überwachen',
                'href'  => admin_url('admin.php?page=at-uberwachen'),
                'meta' => [
                    'title' => 'Überwachen',
                ]
            ) );
        }
    }

    public function admin_menu_management() {
        if ( ! current_user_can( 'administrator' ) ) {
            remove_menu_page( 'index.php' );
            remove_menu_page( 'profile.php' );
        }
    }

    public function maybe_hide_admin_bar() {
        if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'schutzstaffel' ) ) {
            show_admin_bar(false);
        }
    }
}
