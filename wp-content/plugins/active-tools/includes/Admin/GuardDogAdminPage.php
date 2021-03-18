<?php

namespace ActiveTools\Admin;

use ActiveTools;
use ActiveTools\CarbonFields\ThemeOptionSerializedDatastore;
use ActiveTools\GuardDog;
use Carbon_Fields\Field;
use ActiveTools\Utility\Encryptor;
use ActiveTools\Utility\Logger;

class GuardDogAdminPage extends AdminPage {
    
    public function __construct( AdminPage $parent ) {
        
        $this->slug = 'at-guard-dog';
        $this->short_slug = 'guard_dog';
        $this->id = 'at_guard_dog';
        $this->page_title = 'Active Tools - Guard Dog';
        $this->menu_title = 'Guard Dog';
        
        $this->notices = [
            'at_gd_success' => [
                'level' => 'success',
                'message' => 'Congratulations! You have succeeded'
            ],
            'at_gd_failure' => [
                'level' => 'error',
                'message' => 'Error: Something happened'
            ],
        ];
        
        parent::__construct( $parent );
    }
    
    protected function register_hooks() {
        
        if ( is_admin() ) {
            add_filter( 'carbon_fields_before_field_save', array( $this, 'before_field_save' ), 10, 1 );
        }
        
        parent::register_hooks();
    }
    
    
    public function register_carbon_fields() {
    
        parent::register_carbon_fields();
        
        if ( ! $this->is_active ) {
            return;
        }
        
        $this->container->add_tab( __( 'Proxy/VPN Guard' ), [
            Field::make( 'html', 'at_gd_ip_proxy_html', __( '' ) )
                 ->set_html( array( $this, 'render_proxy_tab_top_html' ) ),
            Field::make( 'checkbox', 'at_gd_pb_gb', __( 'Block Proxy/VPN visitors site-wide' ) )
                ->set_option_value( 'yes' )
                ->set_help_text( 'Block visitors above a specified risk factor before the page gets rendered.'),
            Field::make( 'checkbox', 'at_gd_pb_gb_ra', __( 'Also block REST API' ) )
                ->set_option_value( 'yes' )
                ->set_help_text( 'Note that 3rd party services relying on the REST API will score very high in risk factor and likely be blocked. If a 3rd party service needs to use the REST API, it\'s best to keep this option disabled.'),
            Field::make( 'select', 'at_gd_pb_ds', __( 'Proxy/VPN Detection Service' ) )
                 ->set_options( ['getipintel_free' => 'Getipintel.net - Free', 'getipintel_custom' => 'Getipintel.net - Custom/Paid'] )
                 ->set_help_text( 'A 3rd party service is required in order to accurately evaluate if an IP is a Proxy or VPN.'),
            Field::make( 'text', 'at_gd_pb_gb_ds_gipi_c', __( 'Getipintel.net Custom/Paid Subdomain' ) )
                 ->set_help_text( 'Enter just the subdomain, without .getipintel.net or any other parts of its url.')
                 ->set_conditional_logic( array(
                     'relation' => 'AND',
                     array(
                         'field' => 'at_gd_pb_ds',
                         'value' => 'getipintel_custom',
                     )
                 ) ),
            Field::make( 'text', 'at_gd_pb_gb_rf', __( 'Maximum Allowed Risk Factor' ) )
                ->set_attribute( 'type', 'number' )
                ->set_attribute( 'step', '0.001' )
                ->set_attribute( 'min', '0.4' )
                ->set_attribute( 'max', '1.0' )
                ->set_default_value( 0.95 )
                ->set_help_text( 'Set a value between 0.4 (extremely paranoid) and 1.0 (allows everyone)'),
            Field::make( 'select', 'at_gd_pb_gb_rt', __( 'Type of response if proxy-blocked' ) )
                ->set_options(['empty_403' => 'Empty 403', 'empty_404' => 'Empty 404', 'template_404' => 'Website\'s Error 403 Template', 'page' => 'Wordpress Page', 'message' => 'Custom Message']),
            Field::make( 'select', 'at_gd_pb_gb_p', __( 'Page to display when blocked' ) )
                ->set_options( array( $this, 'get_all_pages_as_options' ) )
                ->set_conditional_logic( array(
                    'relation' => 'AND',
                    array(
                        'field' => 'at_gd_pb_gb_rt',
                        'value' => 'page',
                    )
                ) ),
            Field::make( 'rich_text', 'at_gd_pb_gb_m', __( 'High-Risk Visitor Blocked Message' ) )
                ->set_help_text( 'This message will be displayed to high-risk visitors instead of the actual website.')
                ->set_attribute( 'placeholder', 'Example: <p>Oops! Your IP address has been flagged as suspicious.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' )
                ->set_default_value( '<p>Oops! Your IP address has been flagged as suspicious.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' )
                ->set_conditional_logic( array(
                    'relation' => 'AND',
                    array(
                        'field' => 'at_gd_pb_gb_rt',
                        'value' => 'message',
                    )
                ) ),
            ] );
            /*
            Field::make( 'checkbox', 'at_gd_pb_pg', __( 'Allow blocking content from high-risk visitors on a per-page basis' ) )
                 ->set_option_value( 'yes' )
                 ->set_help_text( 'Allows the content editor to enable blocking on a per-page or per-post basis. The website still gets displayed but a message is displayed instead of the page or post content.')
            ,
            Field::make( 'checkbox', 'at_gd_pb_pg_ra', __( 'Also block REST API & Feeds' ) )
                 ->set_option_value( 'yes' )
                 ->set_help_text( 'Note that 3rd party services relying on the REST API will score very high in risk factor and likely be blocked. If a 3rd party service needs post content, it\s best to keep this disabled.')
                 ->set_conditional_logic( array(
                     'relation' => 'AND',
                     array(
                         'field' => 'at_gd_pb_pg',
                         'value' => true,
                     )
                 ) ),
            Field::make( 'text', 'at_gd_pb_pg_rf', __( 'Maximum Allowed Risk Factor per-page' ) )
                 ->set_attribute( 'type', 'number' )
                 ->set_attribute( 'step', '0.001' )
                 ->set_attribute( 'min', '0.4' )
                 ->set_attribute( 'max', '1.0' )
                 ->set_default_value( 0.95 )
                 ->set_help_text( 'Set a value between 0.4 (extremely paranoid) and 1.0 (allows everyone)')
                 ->set_conditional_logic( array(
                     'relation' => 'AND',
                     array(
                         'field' => 'at_gd_pb_pg',
                         'value' => true,
                     )
                 ) ),
            Field::make( 'rich_text', 'at_gd_pb_pg_m', __( 'High-Risk Visitor Blocked Message' ) )
                 ->set_help_text( 'This message will be displayed to high-risk visitors in the page content.')
                 ->set_attribute( 'placeholder', 'Example: <p>Sorry, due to malicious users and for legal reasons, but we don\'t allow viewership behind VPNs or Proxies.</p><p>If you are behind a VPN or Proxy, please disable it to continue reading this material.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' )
                 ->set_default_value( 'Sorry, due to malicious users and for legal reasons, but we don\'t allow viewership behind VPNs or Proxies. If you are behind a VPN or Proxy, please disable it to continue reading this material. If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) )
                 ->set_conditional_logic( array(
                     'relation' => 'AND',
                     array(
                         'field' => 'at_gd_pb_pg',
                         'value' => true,
                     )
                 ) ),
        ] );
        */
        $this->container->add_tab( __( 'Feeds & REST API' ), [
            Field::make( 'checkbox', 'at_gd_fd_d', __( 'Disable feeds on your website' ) )
                 ->set_option_value( 'yes' )
                 ->set_help_text( 'Enabling this option will remove all feeds including rdf, rss, rss3, atom, rss2 comments, and atom comments.'),
            Field::make( 'checkbox', 'at_gd_xmlrpc_d', __( 'Disable xmlrpc' ) )
                 ->set_option_value( 'yes' )
                 ->set_help_text( 'XML-RPC is a method used to view and update posts remotely. Safe to disable if 3rd party services don\'t use it.'),
            Field::make( 'text', 'at_gd_ra_ep_pf', __( 'Custom Default REST API Prefix' ) )
                 ->set_default_value( substr( md5( rand(0, 10 ) ), 0, 10 ) )
                 ->set_help_text( 'Set to something obscure (letters, numbers, and dashes only) to deter most brute-force requests to the REST API. You must go to Dashboard > Settings > Permalinks and hit Save after changing this value. Leave empty to use the default, "wp-json"'),
            Field::make( 'checkbox', 'at_gd_ra_d', __( 'Disable all REST API Endpoints' ) )
                 ->set_option_value( 'yes' )
                 ->set_help_text( 'Enabling this option will disable all REST API endpoints except for the ones specified below.'),
            Field::make( 'multiselect', 'at_gd_ra_ep_wl', __( 'Whitelisted REST API Endpoints' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->set_options( array( $this, 'rest_endpoints_as_options' ) )
                 ->set_help_text( 'Please note that whitelisting an endpoint also whitelists all child endpoints.'),
        ] );
    
        $this->container->add_tab( __( 'Country Blacklist' ), [
            Field::make( 'html', 'at_gd_country_blacklist_html', '' )
                 ->set_html( 'Blacklisted countries will be blocked site-wide before the page gets rendered.' ),
            Field::make( 'multiselect', 'at_gd_co_bl', __( 'Select countries to block' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->set_options( array( $this, 'country_codes_as_options' ) ),
            Field::make( 'rich_text', 'at_gd_co_bl_m', __( 'Message displayed to blocked visitors' ) )
                 ->set_help_text( 'This message will be displayed to blocked visitors instead of the actual website.')
                 ->set_attribute( 'placeholder', 'Example: <p>Oops! Your country is being blocked by our website.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' )
                 ->set_default_value( '<p>Oops! Your country is being blocked by our website.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' ),
        ] );
        
        $this->container->add_tab( __( 'IP Blacklist' ), [
            Field::make( 'html', 'at_gd_ip_blacklist_html', __( '' ) )
                ->set_html( 'Blacklisted IPs will be blocked from accessing any aspect of the website.' ),
            Field::make( 'rich_text', 'at_gd_ip_bl_m', __( 'Message displayed to blocked visitors' ) )
                 ->set_help_text( 'This message will be displayed to blocked visitors instead of the actual website.')
                 ->set_attribute( 'placeholder', 'Example: <p>Oops! Your IP address has been blacklisted on our website.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' )
                 ->set_default_value( '<p>Oops! Your IP address has been blacklisted on our website.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' ),
            Field::make( 'complex', 'at_gd_ip_bl', __( 'Blacklisted IPs' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->setup_labels( ['plural_name' => 'IPs', 'singular_name' => 'IP'] )
                 ->set_classes( ['at_complex'])
                 ->set_collapsed( true )
                 ->add_fields( 'ip', array(
                     Field::make( 'text', 'ip', __( 'IP Address' ) )
                         ->set_width( 50 )
                          ->set_help_text( ''),
                     Field::make( 'text', 'note', __( 'Note' ) )
                          ->set_width( 50 )
                          ->set_help_text( ''),
                 ) )->set_header_template( '
                    <% if (ip) { %>
                        <%- ip %>
                    <% } else { %>
                        IP Address
                    <% }%>
                    <% if (note) { %>
                         - <%- note %>
                    <% } %>
                ')
                ->set_collapsed( true ),
        ] );
        
        $this->container->add_tab( __( 'IP WhiteList' ), [
            Field::make( 'html', 'at_gd_ip_whitelist_html', '' )
                ->set_html( 'WhiteListed IPs will bypass any IP-related restrictions that are enforced by Active Tools' ),
            Field::make( 'complex', 'at_gd_ip_wl', __( 'Whitelisted IPs' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->setup_labels( ['plural_name' => 'IPs', 'singular_name' => 'IP'] )
                 ->set_classes( ['at_complex'])
                 ->set_collapsed( true )
                 ->add_fields( 'ip', array(
                     Field::make( 'text', 'ip', __( 'IP Address' ) )
                         ->set_width( 50 )
                          ->set_help_text( ''),
                     Field::make( 'text', 'note', __( 'Note' ) )
                          ->set_width( 50 )
                          ->set_help_text( ''),
                 ) )->set_header_template( '
                    <% if (ip) { %>
                        <%- ip %>
                    <% } else { %>
                        IP Address
                    <% }%>
                    <% if (note) { %>
                         - <%- note %>
                    <% } %>
                ')
                ->set_collapsed( true ),
        ] );
    
    }
    
    public function render_proxy_tab_top_html() {
        
        ob_start();
        ?>
        Risk factor is how much the plugin thinks a visitor IP is fraudulent or using a VPN/Proxy.
        <ul style="list-style: circle inside; padding-left:20px;">
            <li>Anything below 0.90 is considered "low risk".</li>
            <li>Anything between 0.95 and 0.99 should be looked at.
            <li>Anything above 0.99 is most certainly a proxy.</li>
        </ul>
        <?php
        
        return ob_get_clean();
    }
    
    public function admin_init() {
        parent::admin_init();
    }
    
    public function enqueue_scripts_styles() {
        parent::enqueue_scripts_styles();
    }
    
    
    /**
     * @param $field Field\Field
     *
     * @return Field\Field
     */
    function before_field_save( $field ) {
        
        // Sanitize float value
        if ( $field->get_name() == '_at_gd_pb_gb_rf' || $field->get_name() == '_at_gd_pb_pg_rf' ) {
            
            $field->set_value( max( 0.4, min( 1.0, floatval( $field->get_value() ) ) ) );
            
            return $field;
        }
        
        // Match basic url slug
        if ( $field->get_name() == '_at_gd_ra_ep_pf' ) {
            $field->set_value( preg_replace('/[^a-zA-Z\d\-_]/', '', $field->get_value() ) );
            
            return $field;
        }
        
        return $field;
    }
    
    /**
     * Redirects to the configuration page and displays a notice
     *
     * @param $notice string Notice message slug
     */
    private function redirect_notice( $notice ) {
        wp_redirect( add_query_arg( 'render_notice', $notice, $this->get_admin_page_url() ) );
        exit;
    }
    
    public function disable_feeds() {
        wp_die( __( 'No feed available, please visit <a href="'. esc_url( home_url( '/' ) ) .'">Active Translations</a> normally to view content.' ) );
    }
    
    public function country_codes_as_options() {
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
    
    // This is a bad hack to get all endpoints. Sorry!
    public function rest_endpoints_as_options() {
        
        $rest_output = file_get_contents( rest_url() . '?at_gd_gb_bp_k=' . ActiveTools\GD_GLOBAL_BYPASS_KEY );
        
        if ( empty( $rest_output ) ) {
            return [];
        }
        
        $rest_data = json_decode( $rest_output, true );
        
        if ( empty( $rest_data['routes'] ) ) {
            return [];
        }
        
        $routes = [];
        
        foreach( $rest_data['routes'] as $route_path => $route_data ) {
            $routes[$route_path] = preg_replace( '/\(\?P.*\+\){1}/', '[URL_PARAM]', $route_path );
        }
        
        
        return $routes;
    }
}
