<?php

namespace ActiveTools\Admin;

use ActiveTools\Utility\IPTools;

class UberwachenListTable extends \WP_List_Table {
    
    public array $country_codes;
    
    private array $always_trusted_roles;
    
    private array $mycred_types;
    private array $mycred_cache;
    
    public function __construct( $args ) {
        
        parent::__construct( $args );
        
        $this->country_codes = $this->get_country_codes();
        $always_trusted_roles = get_option( '_at_uw_p_ur', [] );
        
        $this->always_trusted_roles = [];
        foreach ( $always_trusted_roles as $role ) {
            $this->always_trusted_roles[] = $role['value'];
        }
        
        $this->mycred_types = [];
        
        $mycred_types_option = get_option( '_at_uw_mc_t', [] );
        
        foreach ( $mycred_types_option as $type ) {
            $this->mycred_types[] = $type['value'];
        }
        
        if ( empty( $this->mycred_types ) ) {
            $mycred_types_option = get_option( 'mycred_types', [] );
            
            foreach ( $mycred_types_option as $id => $name ) {
                $this->mycred_types[] = $id;
            }
        }
        
    }
    
    public function get_columns() {
        return array(
            //'cb' => '<input type="checkbox" />',
            'login_date' => 'Login Date',
            'user' => !empty($_REQUEST['filter_users']) ? 'User <span class="filter-icon dashicons dashicons-code-standards" title="This column has filters applied to it."></span>' : 'User',
            'ip' => ( !empty($_REQUEST['filter_ips']) || !empty($_REQUEST['filter_countries']) ) ? 'IP <span class="filter-icon dashicons dashicons-code-standards" title="This column has filters applied to it."></span>' : 'IP',
            'proxy_level' => 'Proxy<br> Level',
            'points' => 'myCRED<br>Points',
            'mycred_activity' => 'Transactions<br> Last 24 hrs',
            'user_trust' => ( isset($_REQUEST['filter_trust_levels']) && $_REQUEST['filter_trust_levels'] !== '' ) ? 'Trust Level <span class="filter-icon dashicons dashicons-code-standards" title="This column has filters applied to it."></span>' : 'Trust Level',
            'bad_experience' => !empty($_REQUEST['filter_bad_xp']) ? 'Bad XP <span class="filter-icon dashicons dashicons-code-standards" title="This column has filters applied to it."></span>' : 'Bad XP',
            'purchase_rate' => 'Buy<br> Rate',
        );
    }
    
    public function get_search_key() {
        return isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
    }
    
    public function prepare_items() {
        
        // Check if a search was performed.
        $search_key = $this->get_search_key();
        
        $this->_column_headers = $this->get_column_info();
        
        // Check and process any actions such as bulk actions.
        $this->handle_table_actions();
        
        // Fetch the table data
        $table_data = $this->fetch_table_data();
        
        // Filter the data in case of a search
        if( $search_key ) {
            $table_data = $this->filter_table_data( $table_data, $search_key );
        }
        
        $table_page = $this->get_pagenum();
        
        $records_per_page = $this->get_items_per_page( 'at_uberwachen_per_page' );
        
        // Provide the ordered data to the List Table
        // We need to manually slice the data based on the current pagination
        $this->items = array_slice( $table_data, ( ( $table_page - 1 ) * $records_per_page ), $records_per_page );
        
        // Set the pagination arguments
        $total_records = count( $table_data );
        $this->set_pagination_args( array (
            'total_items' => $total_records,
            'per_page'    => $records_per_page,
            'total_pages' => ceil( $total_records/$records_per_page )
        ) );
    }
    
    public function filter_table_data( $table_data, $search_key ) {
        return array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
            foreach( $row as $row_val ) {
                if( stripos( $row_val, $search_key ) !== false ) {
                    return true;
                }
            }
            return false;
        } ) );
    }
    
    protected function get_sortable_columns() {
        return array (
            'login_date' => array( 'login_date', true ),
            'proxy_level' => 'proxy_level',
        );
    }
    
    public function no_items() {
        _e( 'No users found.' );
    }
    
    public function get_bulk_actions() {
        return array(
            //'give-bad-experience' => 'Give Bad Experience',
            //'remove-bad-experience' => 'Remove Bad Experience',
            //'passes-inspection' => 'Passes Inspection',
            //'passes-inspection-unset' => 'Unset Passes Inspection',
            //'export-csv' => 'Export Selected to CSV'
        );
    }
    public function handle_table_actions() {
    
    }
    
    protected function url_with_query_args( $args ) {
        
        $query_args = [
            'page'		=>  wp_unslash( $_REQUEST['page'] ),
            'action' => false
        ];
        
        $s = $this->get_search_key();
        
        if ( !empty( $s ) ) {
            $query_args['s'] = $s;
        }
        
        return add_query_arg( array_merge( $args, $query_args ) );
    }
    protected function column_cb( $item ) {
        return sprintf(
            '<label class="screen-reader-text" for="record_' . $item['id'] . '">' . sprintf( __( 'Select %s' ), $item['username'] ) . '</label>'
            . "<input type='checkbox' name='records[]' class='records_cb' id='record_{$item['id']}' value='{$item['id']}' />"
        );
    }
    
    protected function column_user( $item ) {
        
        $actions = [];
        
        if ( ! $this->item_is_in_filter( 'users', $item['user_id'] ) ) {
            $actions['filter_by'] = '<a href="' . esc_url( $this->url_with_query_args( [ 'filter_users' => $item['user_id'] ] ) ) . '"><span class="filter-icon dashicons dashicons-insert"></span> User</a>';
        } else {
            $actions['filter_by'] = '<a style="color:#a00;" href="' . esc_url( $this->url_with_query_args( [ 'filter_users'	=> $this->remove_item_from_filter( 'users', $item['user_id'] ) ] ) ) . '"><span class="filter-icon dashicons dashicons-remove"></span> User</a>';
        }
        
        if ( current_user_can( 'edit_user' ) ) {
            $actions['edit_user'] = '<a href="/wp-admin/user-edit.php?user_id=' . absint( $item['user_id'] ) . '" target="_blank" title="Edit User"><span class="action-icon dashicons dashicons-edit"></span> User</a>';
        }
    
        return '<div class="content"><span>' . $item['user_id'] . ' - ' . $item['user_login'] . '</span>' . $this->row_actions( $actions ) . '</div>';
    }
    
    protected function column_ip( $item ) {
        $location_text = '';
        $img_html = '';
        
        if ( ! empty( $item['city'] ) ) {
            $location_text = $item['city'];
        }
        if ( ! empty( $item['region'] ) ) {
            $location_text = ( empty( $location_text ) ? '' : $location_text . ', ' ) . $item['region'];
        }
        
        $actions = [];
        
        if ( ! $this->item_is_in_filter( 'ips', $item['ip'] ) ) {
            $actions['filter_ip_by'] = '<a href="' . esc_url( $this->url_with_query_args( ['filter_ips'	=> $item['ip']] ) ) . '"><span class="filter-icon dashicons dashicons-insert"></span> IP</a>';
        } else {
            $actions['filter_ip_by'] = '<a style="color:#a00;" href="' . esc_url( $this->url_with_query_args( ['filter_ips'	=> $this->remove_item_from_filter( 'ips', $item['ip'] )] ) ) . '"><span class="filter-icon dashicons dashicons-remove"></span> IP</a>';
        }
        
        
        if ( ! empty( $item['country'] ) && isset( $this->country_codes[$item['country']] ) ) {
            
            if ( ! $this->item_is_in_filter( 'countries', $item['country'] ) ) {
                $actions['filter_country_by'] = '<a href="' . esc_url( $this->url_with_query_args( ['filter_countries'	=> $item['country']] ) ) . '"><span class="filter-icon dashicons dashicons-insert"></span> Country</a>';
            } else {
                $actions['filter_country_by'] = '<a style="color:#a00;" href="' . esc_url( $this->url_with_query_args( ['filter_countries'	=> $this->remove_item_from_filter( 'countries', $item['country'] )] ) ) . '"><span class="filter-icon dashicons dashicons-remove"></span> Country</a>';
            }
            
            $location_text = ( empty( $location_text ) ? '' : $location_text . ', ' ) . $this->country_codes[$item['country']];
            $img_html = '<img class="country-icon" title="' . $location_text . '" alt="' . $location_text . '" height="48" src="https://cdn.ipregistry.co/flags/emojitwo/' . strtolower($item['country']) . '.svg" />';
        }
        
        $actions['ip_quality_score'] = '<a href="https://www.ipqualityscore.com/free-ip-lookup-proxy-vpn-test/lookup/' . $item['ip'] . '" target="_blank" rel="noreferrer noopener"><span class="filter-icon dashicons dashicons-welcome-view-site"></span> IP Quality Score</a></a>';
        
        return '<div class="content">' . $item['ip'] . $img_html . '<span class="ip-location">' . $location_text . '</span></div>' . $this->row_actions( $actions );
    }
    
    protected function column_proxy_level( $item ) {
        
        $class = '';
        
        $lvl = max( 0, floatval( $item['proxy_level'] ) - 0.95 );
        
        $class = 'at-uberwachen-color-danger_' . round( absint( $lvl * 2000 ) / 5 ) * 5;
        
        return '<span class="' . $class . '">' . sprintf( '%0.2f', floatval( $item['proxy_level'] ) ) . '</span>';
    }
    protected function column_points( $item ) {
        
        $points = $this->get_mycred_points( $item['user_id'] );
        
        $actions = [];
        
        if ( $points['total'] ) {
            $actions['mycred_history'] = '<a href="#" data-user-id="' . esc_attr( absint( $item['user_id'] ) ) . '"><span class="action-icon dashicons dashicons-clock"></span> History</a></a>';
        }
        
        return $points['current'] . ' / ' . $points['total'] . $this->row_actions( $actions );
    }
    
    protected function get_mycred_points( $user_id ) {
        
        if ( !empty( $this->mycred_cache[$user_id] ) ) {
            return $this->mycred_cache[$user_id];
        }
        
        $result = [
            'current' => 0,
            'total' => 0
        ];
        foreach( $this->mycred_types as $mycred_type ) {
            $result['current'] += get_user_meta( $user_id, $mycred_type, true ) ?: 0 ;
            $history = get_user_meta( $user_id, $mycred_type . '_history', true ) ?: [] ;
            $result['total'] += isset( $history['buy_content'] ) ? $result['current'] - $history['buy_content']->total : $result['current'];
        }
        
        $this->mycred_cache[$user_id] = $result;
        
        return $result;
    }
    
    protected function column_mycred_activity( $item ) {
        return $this->get_mycred_activity_count( $item['user_id'] );
    }
    
    protected function column_user_trust( $item ) {
        
        $user = get_user_by( 'ID', $item['user_id'] );
        
        $protected = user_can( $user, 'uberwachen' ) || array_intersect( $this->always_trusted_roles, $user->roles );
        
        $trust = $item['user_trust'] ?: 0 ;
        
        $actions = [];
        
        if ( ! $this->item_is_in_filter( 'trust_levels', $trust ) ) {
            $actions['filter_by'] = '<a href="' . esc_url( $this->url_with_query_args( [ 'filter_trust_levels' => $trust ] ) ) . '"><span class="filter-icon dashicons dashicons-insert"></span> Trust Level</a>';
        } else {
            $actions['filter_by'] = '<a style="color:#a00;" href="' . esc_url( $this->url_with_query_args( [ 'filter_trust_levels'	=> $this->remove_item_from_filter( 'trust_levels', $trust ) ] ) ) . '"><span class="filter-icon dashicons dashicons-remove"></span> Trust Level</a>';
        }
        return '
            <select ' . ( $protected ? 'disabled="disabled" title="Protected User"' : '' ) . ' class="user_trust_user_' . esc_attr( $item['user_id'] ) . '" data-user-id="' . esc_attr( $item['user_id'] ) . '">
                <option value="10" ' . ( $trust > 0 ? 'selected="selected"' : '' ) . '>Trusted</option>
                <option value="0" ' . ( $trust == 0 ? 'selected="selected"' : '' ) . '>Neutral</option>
                <option value="-5" ' . ( $trust < 0 && $trust > -6 ? 'selected="selected"' : '' ) . '>Suspicious</option>
                <option value="-10" ' . ( $trust < -5 ? 'selected="selected"' : '' ) . '>BAD</option>
            </select>
        ' . $this->row_actions( $protected ? [] : $actions );
    }
    protected function column_bad_experience( $item ) {
        
        $user = get_user_by( 'ID', $item['user_id'] );
        
        $protected = user_can( $user, 'uberwachen' ) || array_intersect( $this->always_trusted_roles, $user->roles );
        
        $bad_xp = $item['bad_xp'] == 'yes' ?: false;
        
        return '<input type="checkbox" class="bad_xp_user_' . esc_attr( $item['user_id'] ) . '" value="yes" data-user-id="' . esc_attr( $item['user_id'] ) . '" ' . ( $bad_xp ? 'checked="checked"' : '' ) . ' ' . ( $protected ? 'disabled="disabled" title="Protected User"' : '' ) . '/>';
    }
    protected function column_purchase_rate( $item ) {
        $user = get_user_by( 'ID', $item['user_id'] );
        
        $protected = user_can( $user, 'uberwachen' ) || array_intersect( $this->always_trusted_roles, $user->roles );
        
        $rate = get_user_meta( $item['user_id'], '_at_cp_mc_cpr_l', true ) ?: -1 ;
        
        return '<input type="number" class="user_purchase_rate_user_' . esc_attr( $item['user_id'] ) . '" data-user-id="' . esc_attr( $item['user_id'] ) . '" style="width:100%;" min="-1" max="99999" value="' . $rate . '" ' . ( $protected ? 'disabled="disabled" title="Protected User"' : '' ) . ' />';
    }
    protected function column_login_date( $item ) {
        return $item['login_date'];
    }
    protected function column_logout_date( $item ) {
        return $item['logout_date'];
    }
    
    public function fetch_table_data() {
        /** @var $wpdb wpdb */
        global $wpdb;
        
        $where = $this->get_filtered_query_where_string();
        
        $order_by = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'login_date';
        $order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'DESC';
        
        $query = "
            SELECT wps.user_id as user_id, wps.user_login as user_login, wps.login_ip as ip, wps.login_date as login_date,
                wps.logout_date as logout_date, atip.country as country, atip.region as region, atip.city as city, atip.proxy_level as proxy_level,
                um_ut.meta_value as user_trust,
                um_bxp.meta_value as bad_xp
            FROM {$wpdb->prefix}aiowps_login_activity as wps
            LEFT JOIN {$wpdb->prefix}at_ip_data as atip ON wps.login_ip = atip.ip
            LEFT JOIN {$wpdb->prefix}usermeta as um_ut ON um_ut.user_id = wps.user_id AND um_ut.meta_key = '_at_uw_ut'
            LEFT JOIN {$wpdb->prefix}usermeta as um_bxp ON um_bxp.user_id = wps.user_id AND um_bxp.meta_key = '_at_cp_be_e'
            {$where}
            ORDER BY $order_by $order
        ";
        // at_dbg($query);
        return $wpdb->get_results( $query, ARRAY_A  );
    }
    
    protected function get_mycred_activity_count( $user_id ) {
        global $wpdb;
        
        $user_id = absint($user_id);
        
        $query = "
            SELECT count(*)
            FROM {$wpdb->prefix}myCRED_log
            WHERE user_id = {$user_id} AND time >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))
        ";
        
        return $wpdb->get_row( $query, ARRAY_N  )[0];
    }
    
    protected function get_filtered_query_where_string() {
        
        $where = '';
        
        $filter_users = [];
        
        if ( !empty( $_REQUEST['filter_users'] ) ) {
            $filter_users = filter_var(explode(',', $_REQUEST['filter_users']), FILTER_VALIDATE_INT, array(
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array('min_range' => 1)
            ));
        }
        
        if( !empty( $filter_users ) ) {
            $where .= ' AND wps.user_id IN(' . implode(',', $filter_users) .')';
        }
        
        $filter_ips = [];
        
        if ( !empty( $_REQUEST['filter_ips'] ) ) {
            $filter_ips = array_map('sanitize_text_field', explode(',', $_REQUEST['filter_ips']));
        }
        
        if( !empty( $filter_ips ) ) {
            $has_entry = false;
            
            $ip_where = ' AND wps.login_ip IN(';
            foreach( $filter_ips as $ip ) {
                if ( $has_entry = IPTools::ip_is_valid( $ip ) ) {
                    $ip_where .= "'$ip',";
                }
            }
            if ( $has_entry ) {
                $where .= substr( $ip_where, 0, -1 ) . ')';
            }
        }
        
        $filter_countries = [];
        
        if ( !empty( $_REQUEST['filter_countries'] ) ) {
            $filter_countries = array_map('sanitize_text_field', explode(',', $_REQUEST['filter_countries']));
        }
        
        if( !empty( $filter_countries ) ) {
            $has_entry = false;
            
            $country_where = ' AND atip.country IN(';
            foreach( $filter_countries as $country ) {
                if ( $has_entry = isset( $this->country_codes[$country] ) ) {
                    $country_where .= "'$country',";
                }
            }
            if ( $has_entry ) {
                $where .= substr( $country_where, 0, -1 ) . ',NULL)';
            }
        }
        
        $filter_trust_levels = [];
        
        if ( isset( $_REQUEST['filter_trust_levels'] ) && $_REQUEST['filter_trust_levels'] != '' ) {
            $filter_trust_levels = filter_var(explode(',', $_REQUEST['filter_trust_levels']), FILTER_VALIDATE_INT, array(
                'flags'   => FILTER_REQUIRE_ARRAY,
                'options' => array()
            ));
        }
        
        if( !empty( $filter_trust_levels ) ) {
            $where .= ' AND COALESCE(um_ut.meta_value,0) IN(' . implode(',', $filter_trust_levels) .')';
        }
        
        if ( isset( $_REQUEST['filter_bad_xp'] ) && in_array( $_REQUEST['filter_bad_xp'], ['enabled','disabled'] ) ) {
            if ( $_REQUEST['filter_bad_xp'] == 'enabled' ) {
                $where .= ' AND COALESCE(um_bxp.meta_value,\'\') = \'yes\'';
            } else {
                $where .= ' AND COALESCE(um_bxp.meta_value,\'\') != \'yes\'';
            }
            
        }
        
        if( !empty( $where ) ) {
            $where = ' WHERE ' . substr( $where, 5);
        }
        
        return $where;
    }
    
    protected function get_default_primary_column_name() {
        return 'id';
    }
    
    protected function item_is_in_filter( $filter_name, $item_to_filter ) {
        if ( ! empty( $_REQUEST['filter_' . $filter_name ] ) ) {
            foreach( explode(',', $_REQUEST['filter_' . $filter_name ] ) as $filtered_item ) {
                if ( trim( $filtered_item ) == $item_to_filter ) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    protected function remove_item_from_filter( $filter_name, $item_to_remove ) {
        
        $valid_items = [];
        
        if ( ! empty( $_REQUEST['filter_' . $filter_name ] ) ) {
            foreach( explode(',', $_REQUEST['filter_' . $filter_name ] ) as $filtered_item ) {
                if ( trim( $filtered_item ) != $item_to_remove ) {
                    $valid_items[] = trim( $filtered_item );
                }
            }
        }
        
        return implode(',', $valid_items);
    }
    
    protected function get_country_codes() {
        return array(
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BQ' => 'Bonaire, Saint Eustatius and Saba',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CW' => 'Curacao',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CD' => 'Democratic Republic of the Congo',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'TL' => 'East Timor',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'CI' => 'Ivory Coast',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'XK' => 'Kosovo',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'CG' => 'Republic of the Congo',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SX' => 'Sint Maarten',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'KR' => 'South Korea',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );
    }
}
