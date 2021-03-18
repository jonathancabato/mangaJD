<?php

use ActiveTools\Utility\IPTools;

if ( ! defined( 'WPINC' ) ) {
    die;
}
$all_users = get_users();
$filter_users = [];
$filter_ips = [];
$filter_countries = [];
$filter_trust_levels = [];
$filter_bad_xp = '';

if ( !empty( $_REQUEST['filter_users'] ) ) {
    $filter_users = filter_var(explode(',', $_REQUEST['filter_users']), FILTER_VALIDATE_INT, array(
        'flags'   => FILTER_REQUIRE_ARRAY,
        'options' => array('min_range' => 1)
    ));
}

if ( !empty( $_REQUEST['filter_ips'] ) ) {
    foreach( explode(',', $_REQUEST['filter_ips'] ) as $ip ) {
        if ( IPTools::ip_is_valid( $ip ) ) {
            $filter_ips[] = $ip;
        }
    }
}
if ( !empty( $_REQUEST['filter_countries'] ) ) {
    foreach( explode(',', $_REQUEST['filter_countries'] ) as $country ) {
        if ( isset( $this->list_table->country_codes[$country] ) ) {
            $filter_countries[] = $country;
        }
    }
}
if ( isset( $_REQUEST['filter_trust_levels'] ) && $_REQUEST['filter_trust_levels'] !== '' ) {
    $filter_trust_levels = filter_var(explode(',', $_REQUEST['filter_trust_levels']), FILTER_VALIDATE_INT, array(
        'flags'   => FILTER_REQUIRE_ARRAY,
        'options' => array()
    ));
}

if ( ! empty( $_REQUEST['filter_bad_xp'] ) || in_array( $_REQUEST['filter_bad_xp'], ['enabled','disabled'] ) ) {
    $filter_bad_xp = $_REQUEST['filter_bad_xp'];
}

/**
 * @var $this \ActiveTools\Admin\UberwachenPage
 */
?>
<div class="wrap"><h1>Überwachen - Login Activity</h1></div>
<div class="wrap">
    <div id="at-uberwachen-list-table" style="margin-top:20px;">
        <div id="at-uberwachen-post-body">
            <form id="at-uberwachen-list-form" method="post">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <input type="hidden" id="prior_search_value" name="prior_search_value" value="<?php esc_attr_e( $this->list_table->get_search_key() ); ?>" />
                <?php
                $this->list_table->display();
                ?>
            </form>
            <div class="at-uberwachen-ajax-loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
        </div>
    </div>

    <div id="at-uberwachen-dialog-filter-options" title="Column Filters" style="display:none;">
        <form action="/wp-admin/admin.php?page=qdl-zip_code-data" id="at-uberwachen-filter-options" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <label for="filter_users_select" class="break">Filter By Users:</label>
            <input type="hidden" name="filter_users" id="filter_users" <?php echo ( count ( $filter_users ) ? 'value="' . implode(',', $filter_users) . '"' : '' ); ?> />
            <select id="filter_users_select" multiple>
                <?php
                foreach ( $all_users as $user ) {
                    echo '<option value="' . $user->ID . '" ' . ( in_array((int)$user->ID, $filter_users) ? 'selected="selected"' : '' ) . '>' . $user->user_login . '</option>';
                }
                ?>
            </select>
            <label for="filter_ips_select" class="break">Filter By IPs:</label>
            <input type="hidden" name="filter_ips" id="filter_ips" <?php echo ( count( $filter_ips ) ? 'value="' . implode(',', $filter_ips) . '"' : '' ); ?> />
            <select id="filter_ips_select" multiple>
                <?php
                foreach ( $filter_ips as $ip ) {
                    echo '<option value="' . $ip . '" selected="selected">' . $ip . '</option>';
                }
                ?>
            </select>
            <label for="filter_countries_select" class="break">Filter By Countries:</label>
            <input type="hidden" name="filter_countries" id="filter_countries" <?php echo ( count ($filter_countries ) ? 'value="' . implode(',', $filter_countries) . '"' : '' ); ?> />
            <select id="filter_countries_select" multiple>
                <?php
                foreach ( $this->list_table->country_codes as $code => $name ) {
                    echo '<option value="' . $code . '" ' . ( in_array($code, $filter_countries) ? 'selected="selected"' : '' ) . '>' . $name . '</option>';
                }
                ?>
            </select>
            <label for="filter_trust_levels_select" class="break">Filter By Trust Level:</label>
            <input type="hidden" name="filter_trust_levels" id="filter_trust_levels" <?php echo ( count( $filter_trust_levels ) ? 'value="' . implode(',', $filter_trust_levels) . '"' : '' ); ?> />
            <select id="filter_trust_levels_select" multiple>
                <option value="10" <?php echo ( in_array(10, $filter_trust_levels) ? 'selected="selected"' : '' ) ?>>Trusted</option>
                <option value="0" <?php echo ( in_array(0, $filter_trust_levels) ? 'selected="selected"' : '' ) ?>>Neutral</option>
                <option value="-5" <?php echo ( in_array(-5, $filter_trust_levels) ? 'selected="selected"' : '' ) ?>>Suspicious</option>
                <option value="-10" <?php echo ( in_array(-10, $filter_trust_levels) ? 'selected="selected"' : '' ) ?>>BAD</option>
            </select>
            <label for="filter_bad_xp_select" class="break">Filter By Bad XP:</label>
            <input type="hidden" name="filter_bad_xp" id="filter_bad_xp" value="<?php esc_attr_e($filter_bad_xp); ?>" />
            <select id="filter_bad_xp_select">
                <option value="">Filter Disabled</option>
                <option value="enabled" <?php echo ( $filter_bad_xp == 'enabled' ? 'selected="selected"' : '' ) ?>>Only show active Bad XP</option>
                <option value="disabled" <?php echo ( $filter_bad_xp == 'disabled' ? 'selected="selected"' : '' ) ?>>Only show inactive Bad XP</option>
            </select>
        </form>
    </div>

    <div id="at-uberwachen-dialog-user-points-history" title="User Points History" style="display:none;">
        <div class="content" style="height:100%;"></div>
        <div class="at-uberwachen-ajax-loader"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
    </div>
</div>
<script>
    var at_uberwachen_params = {
        ajax_url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
        _wp_nonces: {
            _wp_nonce_set_user_bad_xp: '<?php echo wp_create_nonce( 'at-uberwachen-set-user-bad-xp' ) ?>',
            _wp_nonce_set_user_trust: '<?php echo wp_create_nonce( 'at-uberwachen-set-user-trust' ) ?>',
            _wp_nonce_set_user_purchase_rate: '<?php echo wp_create_nonce( 'at-uberwachen-set-user-purchase-rate' ) ?>',
            _wp_nonce_get_user_points_history: '<?php echo wp_create_nonce( 'at-uberwachen-get-user-points-history' ) ?>',
        }
    };
</script>
