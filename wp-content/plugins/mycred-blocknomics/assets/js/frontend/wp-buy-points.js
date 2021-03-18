jQuery(document).ready(function ($) {
    var int = setInterval(function () {

        if ($('#blockoPayBtnSuccess').is(':visible')) {
            var points = $('#blockoPayModal').data('points-value')
            clearInterval(int)
            $.ajax({
                url: wp_manga_buy_params.ajaxurl,
                type: 'POST',
                data: {
                    action: 'add_points',
                    user_id: wp_manga_buy_params.user_id,
                    points: points,
                    nonce: wp_manga_buy_params.nonce,

                },
                success: function (response) {

                    window.location.reload()

                }
            });

        }
    }, 100)
})