(function ($) {
    'use strict';

    $( window ).load( function () {

        /* Quick hack to "remember" the last active Carbon Fields tab */
        let $tabItems = $( '.cf-container__tabs-item' );

        let curTabItem = getCookie( 'at-admn-config-curr-tab' );

        if ( curTabItem ) {
            $tabItems.each(function() {
                if ( $(this).html() === curTabItem ) {
                    $(this).click();
                }
            });
        }

        $tabItems.click( function( e ) {
            setCookie('at-admn-config-curr-tab', $(this).html(), 1 );
        });


        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for(var i = 0; i <ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        function setCookie(cname, cvalue, numhours) {
            var d = new Date();
            d.setTime(d.getTime() + (numhours*60*60*1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
    } );

})( jQuery );
