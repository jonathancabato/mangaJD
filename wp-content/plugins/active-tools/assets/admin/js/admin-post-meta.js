(function ($) {
    'use strict';

    $( window ).load( function () {

        var $at_po_types = $( 'input[name="carbon_fields_compact_input[_at_po_t][]"]' );

        console.log( $at_po_types );
        $at_po_types.on( 'change', function() {
            if ( ( $(this).val() === 'disabled' || $(this).val() === 'all' ) && this.checked ) {
                set_all_po_types_disabled_except( $(this).val() );
                return;
            }
            if ( ( $(this).val() !== 'disabled' && $(this).val() !== 'all' ) && this.checked ) {
                $at_po_types.each(function( index, item ) {
                    if( ( $(item).val() === 'disabled' || $(item).val() === 'all' ) && item.checked ) {
                        $(item).click();
                    }
                });
            }
        });

        function set_all_po_types_disabled_except( optionValue ) {
            $at_po_types.each(function( index, item ) {
                if( $(item).val() !== optionValue && item.checked ) {
                    $(item).click();
                }
            });
        }
    } );

})( jQuery );
