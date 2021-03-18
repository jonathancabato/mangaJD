<?php

namespace ActiveTools\Admin;

use ActiveTools;
use ActiveTools\CarbonFields\ThemeOptionSerializedDatastore;
use ActiveTools\GuardDog;
use Carbon_Fields\Field;
use ActiveTools\Utility\Encryptor;
use ActiveTools\Utility\Logger;

class ContentProtectionAdminPage extends AdminPage {
    
    public function __construct( AdminPage $parent ) {
        
        $this->slug = 'at-cp';
        $this->short_slug = 'content_protection';
        $this->id = 'at_cp';
        $this->page_title = 'Active Tools - Content Protection';
        $this->menu_title = 'Content Protection';
        
        $this->notices = [
            'at_cp_success' => [
                'level' => 'success',
                'message' => 'Congratulations! You have succeeded'
            ],
            'at_cp_failure' => [
                'level' => 'error',
                'message' => 'Error: Something happened'
            ],
        ];
        
        parent::__construct( $parent );
    }
    
    protected function register_hooks() {
        
        if ( is_admin() ) {
            add_filter( 'carbon_fields_before_field_save', array( $this, 'before_field_save' ), PHP_INT_MAX, 1 );
        }
        
        parent::register_hooks();
    }
    
    
    public function register_carbon_fields() {
    
        parent::register_carbon_fields();
        
        if ( ! $this->is_active ) {
            return;
        }
    
        // MyCred Rate Limiting
        $this->container->add_tab( __( 'MyCred' ), [
            Field::make( 'text', 'at_cp_mc_cpr_l', __( 'Chapter Purchase Rate Limiter' ) )
                 ->set_attribute( 'type', 'number' )
                 ->set_attribute( 'step', '1' )
                 ->set_attribute( 'min', '0' )
                 ->set_default_value( 0 )
                 ->set_help_text( 'This will limit purchases to 1 every X minutes. Set to 0 to disable.'),
        ] );
        
        $this->container->add_tab( __( 'Bad Experience' ), [
            Field::make( 'html', 'at_cp_be_html', __( '' ) )
                 ->set_html( '<p><strong>Give suspicious and malicious users a bad experience!</strong></p>' ),
            Field::make( 'multiselect', 'at_cp_be_ur', __( 'User Roles' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->set_options( array( $this, 'get_all_user_roles_visitors' ) )
                 ->set_help_text( 'Users with these roles will receive a bad experience'),
            Field::make( 'text', 'at_cp_be_rs', __( 'Random Seed' ) )
                 ->set_attribute( 'type', 'number' )
                 ->set_attribute( 'step', '1' )
                 ->set_attribute( 'min', '0' )
                 ->set_attribute( 'max', '999999999' )
                 ->set_default_value( 0 )
                 ->set_help_text( 'Set to 0 to not use a random seed. A random seed will force the outcome to be the same for all users (the same word will swap in the same chapter for everyone). Not using a random seed will make the experience unique to each user.' ),
            Field::make( 'text', 'at_cp_be_s_pg_i_r', __( 'Swap paragraphs within protected content' ) )
                 ->set_attribute( 'type', 'number' )
                 ->set_attribute( 'step', '1' )
                 ->set_attribute( 'min', '0' )
                 ->set_attribute( 'max', '100' )
                 ->set_default_value( 0 )
                 ->set_help_text( 'This is the % chance a swap will occur for each paragraph in the protected content' ),
            Field::make( 'text', 'at_cp_be_s_w_i_r', __( 'Swap words with other text' ) )
                 ->set_attribute( 'type', 'number' )
                 ->set_attribute( 'step', '1' )
                 ->set_attribute( 'min', '0' )
                 ->set_attribute( 'max', '100' )
                 ->set_default_value( 0 )
                 ->set_help_text( 'This is the % chance a swap will occur for each word in the protected content' ),
            Field::make( 'complex', 'at_cp_be_s_w_i', __( 'Words to swap' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->setup_labels( ['plural_name' => 'Word', 'singular_name' => 'Words'] )
                 ->set_classes( ['at_complex'])
                 ->set_collapsed( true )
                 ->add_fields( 'search_and_replace', array(
                     Field::make( 'text', 'search', __( 'Search For Word' ) )
                          ->set_width( 50 )
                          ->set_required( true ),
                     Field::make( 'text', 'replace', __( 'Replace With Text' ) )
                          ->set_width( 50 )
                          ->set_required( true ),
                 ) )->set_header_template( '
                    <% if (search) { %>
                        "<%- search %>"
                    <% } else { %>
                        ""
                    <% }%>
                    <% if (replace) { %>
                         -> "<%- replace %>"
                    <% } %>
                ')
                 ->set_collapsed( true )
                 ->set_help_text( 'The search word will not work if it has spaces in it. You can match other special characters (such as dashes), however. It\'s case in-sensitive, Meaning that if the word given was "dog", then "dog", "Dog", and "dOg" will get matched and replaced within the content. Can also be phrases or unconventional text.' ),
            ] );
        
        $this->container->add_tab( __( 'Überwachen' ), [
            Field::make( 'multiselect', 'at_uw_r', __( 'Überwachen Roles' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->set_options( array( $this, 'get_all_user_roles' ) )
                 ->set_help_text( 'These roles are allowed to access the Überwachen page and use its functions. Administrators and Schutzstaffel users always receive access'),
            Field::make( 'multiselect', 'at_uw_p_ur', __( 'Protected User Roles' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->set_options( array( $this, 'get_all_user_roles' ) )
                 ->set_help_text( 'Users with these roles can not be given a bad experience, marked as not trusted, or have their purchase rate changed via the Überwachen page. Überwachen Role users are always exempt. Note that these fields can be changed via other pages regardless of this setting.'),
            Field::make( 'multiselect', 'at_uw_mc_t', __( 'myCred Point Types' ) )
                 ->set_datastore( new ThemeOptionSerializedDatastore() )
                 ->set_options( array( $this, 'get_mycred_types' ) )
                 ->set_help_text( 'Select the myCred point types to monitor. If none are chosen then all point types will be shown (less performant)'),
        ] );
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
    
        global $wp_roles;
        
        if ( $field->get_name() == '_at_cp_uw_r' ) {
            $uberwachen_roles = $field->get_value();
            
            foreach ( $wp_roles->roles as $wp_role_id => $wp_role ) {
                if ( in_array( $wp_role_id, $uberwachen_roles ) ) {
                    $wp_roles->add_cap( $wp_role_id, 'uberwachen' );
                } else {
                    if ( $wp_role_id != 'administrator' && $wp_role_id != 'schutzstaffel' ) {
                        $wp_roles->remove_cap( $wp_role_id, 'uberwachen' );
                    }
                }
            }
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
    
    public function get_all_user_roles_visitors() {
        return $this->get_all_user_roles( true );
    }
    public function get_all_user_roles( $all_visitors = false ) {
        global $wp_roles;
        
        if ( $all_visitors ) {
            $roles = [ '_all' => 'All Visitors' ];
        } else {
            $roles = [];
        }
        
        foreach( $wp_roles->roles as $id => $role ) {
            $roles[$id] = $role['name'];
        }
        
        return $roles;
    }
    
    public function get_mycred_types() {
        return get_option( 'mycred_types', [] );
    }
}
