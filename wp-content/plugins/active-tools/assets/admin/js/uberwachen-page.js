(function( $ ) {
    'use strict';

    $( document ).ready(function() {

        let updatingInputs = false;

        let filter_options = [
            { name: 'users', placeholder: 'Select Users.', taggable: false, multiple: true },
            { name: 'ips', placeholder: 'Can be typed in. Press Enter after each entry.', taggable: true, multiple: true},
            { name: 'countries', placeholder: 'Select Countries.', taggable: false, multiple: true},
            { name: 'trust_levels', placeholder: 'Select Trust Levels.', taggable: false, multiple: true},
            { name: 'bad_xp', placeholder: 'Select if Enabled or Disabled.', taggable: false, multiple: false}
        ];

        for ( let i = 0; i < filter_options.length; i++ ) {
            filter_options_setup( filter_options[i] );
        }

        function filter_options_setup( filter_option ) {
            var $filter_select = $( '#filter_' + filter_option.name + '_select' );

            $filter_select.select2({
                multiple: filter_option.multiple,
                placeholder: filter_option.placeholder,
                width: '100%',
                tags: filter_option.taggable,
                allowClear: true
            });

            if ( ! $( '#filter_' + filter_option.name ).val() ) {
                $filter_select.val(null).trigger('change')
            }

            $( document ).on( "change", "#filter_" + filter_option.name + "_select", function( e ) {
                $( '#filter_' + filter_option.name ).val( $filter_select.val() );
            });
        }

        var $add_btn_container = $('#at-uberwachen-list-form .tablenav.top');

        $add_btn_container.html('' +
            '<div class="topnav-btn"><a href="#" class="at-uberwachen-filter-options button-secondary" ><span class="dashicons dashicons-admin-settings"></span>Filters</a></div>' +
            '<div class="search-box"><label class="screen-reader-text" for="at-uberwachen-find-search-input">Find:</label>' +
            '<input type="search" id="at-uberwachen-find-search-input" placeholder="Search..." name="s" value="">' +
            '<input type="submit" id="search-submit" class="button" value="Find"></div>' +
            $add_btn_container.html());

        $('#at-uberwachen-find-search-input').val( $('#prior_search_value' ).val() );

        const filter_dialog =  $( "#at-uberwachen-dialog-filter-options" ).dialog({
            resizable: false,
            height: "auto",
            width: "auto",
            modal: true,
            autoOpen: false,
            draggable: false,
            show: {
                effect: "fade",
                duration: 200
            },
            buttons: [
                {
                    text: "Apply Filters",
                    click: function () {
                        submit_filter_options();
                    }
                },
                {
                    text: "Cancel",
                    click: function () {
                        filter_dialog.dialog( "close" );
                    }
                },
            ],
            close: function() {

            },
            open: function() {

            }
        });

        $( document ).on( "click", "a.at-uberwachen-filter-options", function( e ) {
            e.preventDefault();
            filter_dialog.dialog( "open" );
        });

        const points_history_dialog =  $( "#at-uberwachen-dialog-user-points-history" ).dialog({
            resizable: false,
            height: "auto",
            width: "auto",
            modal: true,
            autoOpen: false,
            draggable: false,
            show: {
                effect: "fade",
                duration: 200
            },
            buttons: [
                {
                    text: "Close",
                    click: function () {
                        points_history_dialog.dialog( "close" );
                    }
                },
            ],
            close: function() {

            },
            open: function() {

            }
        });

        $( document ).on( "click", ".mycred_history > a", function( e ) {
            e.preventDefault();
            points_history_dialog.dialog( "open" );

            clear_user_points_history();
            ajax_get_user_points_history( $(this).attr('data-user-id') );
        });

        $( document ).on( "change", ".column-bad_experience input[type=checkbox]", function( e ) {

            if ( updatingInputs ) {
                return;
            }

            let $this = $(this);

            let isChecked = $this.is(':checked');

            if ( confirm( "Are you sure you want to " + ( isChecked ? "give this user a bad experience?" : "remove this user's bad experience?" ) ) ) {
                ajax_set_bad_experience( $this.attr('data-user-id'), isChecked );
            } else {
                $this.prop('checked', !isChecked);
            }
        });

        $( document ).on( "change", ".column-user_trust select", function( e ) {

            if ( updatingInputs ) {
                return;
            }
            ajax_set_user_trust( $(this).attr('data-user-id'), $(this).val() );
        });

        let purchaseTimeOut = null;

        $( document ).on( "change", ".column-purchase_rate input[type=number]", function( e ) {

            if ( updatingInputs ) {
                return;
            }

            const $this = $(this);

            clearTimeout( purchaseTimeOut );
            purchaseTimeOut = setTimeout( function() {
                ajax_set_user_purchase_rate( $this.attr('data-user-id'), $this.val() );
            }, 500 );

        });

        function ajax_set_user_purchase_rate( userID, purchaseRate ) {

            if ( userID == null || purchaseRate == null) {
                popNotice( 'Invalid Action' );
                return;
            }

            row_item_updating();

            let payload = {
                action: 'at_uberwachen_set_user_purchase_rate',
                security: window.at_uberwachen_params._wp_nonces._wp_nonce_set_user_purchase_rate,
                user_id: userID,
                purchase_rate: purchaseRate
            };

            const req = $.post(window.at_uberwachen_params.ajax_url, payload, function (response) {

                if (response.success === false) {
                    popNotice('Error: ' + response.data);
                    row_item_updating( false );
                    return;
                }

                if (response.success === true) {
                    $('.column-purchase_rate input[type=number].user_purchase_rate_user_' + userID ).val( purchaseRate );
                }

                row_item_updating( false );

            });

            // If failed request
            req.fail(function(xhr, status) {
                handle_failed_request( xhr, status );
                row_item_updating( false );
            });
        }
        function ajax_set_user_trust( userID, trustLevel ) {

            if ( userID == null || trustLevel == null) {
                popNotice( 'Invalid Action' );
                return;
            }

            row_item_updating();

            let payload = {
                action: 'at_uberwachen_set_user_trust',
                security: window.at_uberwachen_params._wp_nonces._wp_nonce_set_user_trust,
                user_id: userID,
                trust_level: trustLevel
            };

            const req = $.post(window.at_uberwachen_params.ajax_url, payload, function (response) {

                if (response.success === false) {
                    popNotice('Error: ' + response.data);
                    row_item_updating( false );
                    return;
                }

                if (response.success === true) {
                    $('.column-user_trust select.user_trust_user_' + userID ).val( trustLevel );
                }

                row_item_updating( false );

            });

            // If failed request
            req.fail(function(xhr, status) {
                handle_failed_request( xhr, status );
                row_item_updating( false );
            });
        }

        function ajax_set_bad_experience( userID, isChecked ) {

            if ( userID == null || isChecked == null) {
                popNotice( 'Invalid Action' );
                return;
            }

            row_item_updating();

            let payload = {
                action: 'at_uberwachen_set_user_bad_xp',
                security: window.at_uberwachen_params._wp_nonces._wp_nonce_set_user_bad_xp,
                user_id: userID,
                bad_xp: isChecked ? 'yes' : 'no'
            };

            const req = $.post(window.at_uberwachen_params.ajax_url, payload, function (response) {

                if (response.success === false) {
                    popNotice('Error: ' + response.data);
                    row_item_updating( false );
                    return;
                }

                if (response.success === true) {
                    $('.column-bad_experience input[type=checkbox].bad_xp_user_' + userID ).prop('checked', isChecked );
                }

                row_item_updating( false );

            });

            // If failed request
            req.fail(function(xhr, status) {
                handle_failed_request( xhr, status );
                row_item_updating( false );
            });
        }

        function clear_user_points_history() {
            $('#at-uberwachen-dialog-user-points-history .content').html(' ');
        }

        function ajax_get_user_points_history( userID ) {

            if ( userID == null ) {
                popNotice( 'Invalid Action' );
                points_history_dialog.dialog( "close" );
                return;
            }

            const ajaxLoader = $('#at-uberwachen-dialog-user-points-history .at-uberwachen-ajax-loader');

            set_ajax_loader_active( ajaxLoader, 200 );

            let payload = {
                action: 'at_uberwachen_get_user_points_history',
                security: window.at_uberwachen_params._wp_nonces._wp_nonce_get_user_points_history,
                user_id: userID
            };

            const req = $.post(window.at_uberwachen_params.ajax_url, payload, function (response) {

                if (response.success === false) {
                    popNotice('Error: ' + response.data);
                    points_history_dialog.dialog( "close" );
                    set_ajax_loader_inactive( ajaxLoader );
                    return;
                }

                if (response.success === true) {
                    $('#at-uberwachen-dialog-user-points-history .content').html( response.data );
                } else {
                    points_history_dialog.dialog( "close" );
                }
                set_ajax_loader_inactive( ajaxLoader );

            });

            // If failed request
            req.fail(function(xhr, status) {
                handle_failed_request( xhr, status );
                points_history_dialog.dialog( "close" );
                set_ajax_loader_inactive( ajaxLoader );
            });
        }

        let ajaxLoaderTimer = null;
        let ajaxLoading = false;
        function set_ajax_loader_active( $ajaxLoader = $('#at-uberwachen-post-body .at-uberwachen-ajax-loader'), minTime = 600 ) {
            ajaxLoading = true;
            $ajaxLoader.addClass('active');

            ajaxLoaderTimer = setTimeout( function() {
                if ( ! ajaxLoading ) {
                    $ajaxLoader.removeClass('active');
                } else {
                    ajaxLoaderTimer = null;
                }
            }, minTime );

        }
        function set_ajax_loader_inactive( $ajaxLoader = $('#at-uberwachen-post-body .at-uberwachen-ajax-loader') ) {

            ajaxLoading = false;
            if ( ajaxLoaderTimer !== null ) {
                return;
            }
            $ajaxLoader.removeClass('active');
        }

        function row_item_updating( updating = true ) {

            if ( updating ) {
                set_ajax_loader_active();
            } else {
                set_ajax_loader_inactive();
            }
            updatingInputs = updating;
        }
        function handle_failed_request( xhr, status) {

            if (xhr.status === 400) {
                popNotice('Error: 400 - Bad Request');
            }

            if (xhr.status === 403) {
                popNotice('Error: 403 - Forbidden');
            }

            if (xhr.status === 404) {
                popNotice('Error: 404 - Not found');
            }

            if (xhr.status === 500) {
                popNotice('Error: 500 - Internal Server Error');
            }
        }
        function submit_filter_options() {
            $( '#at-uberwachen-filter-options' ).submit();
        }

        function popNotice( message ) {
            alert( message );
        }
    });
})( jQuery );
