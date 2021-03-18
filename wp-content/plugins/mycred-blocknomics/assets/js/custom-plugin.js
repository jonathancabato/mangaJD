jQuery(document).ready(function ($) {
    console.log('sampllllle')
    $("input:checkbox.get-cpt-blck").on('click', function () {

        console.log($(this))
        var $box = $(this);
        if ($box.is(":checked")) {

            var group = ".get-cpt-blck";

            $(group).prop("checked", false);
            $box.prop("checked", true);
        } else {
            $box.prop("checked", false);
        }
    });

    // $('.blockoPayBtn').on('click', function () {
    //     var name = $('input.donor-name').val()
    //     console.log(name)
    //     $('#blockoPayBtnname').val(name)
    // })

    setInterval(function () {
        if ($('#blockoPayBtnForm #blockoPayBtnSuccess').css('display') === 'block') {
            console.log('sample')
        }


    }, 1000)
    // $(window).on('load', function () {

    //     // var ChapterContents = $('#manga-chapters-holder').html();

    //     // $('#manga-chapters-holder').html(ChapterContents);
    //     console.log($('#manga-chapters-holder').find('.coin'))

    //     $(document).on("click", '#manga-chapters-holder .coin', function (e) {
    //         console.log('sample')
    //     });

    // });

    // var interval = setInterval(function () {

    //     if ($('.open-unlock-modal').length > 0) {
    //         clearInterval(interval);
    //         console.log($('.open-unlock-modal'))
    //         $('.open-unlock-modal').on('click', function (e) {

    //         })
    //     }
    // })
    // $('body').on('click', '.form.myCRED-buy-form .manga-points-container', function () {
    //     var $this = $(this).find('.madara_purchase_points')

    // })

    $('.blockoPayBtn').on('click', function (e) {
        $('#myCredForm').modal('hide')
        var points_value = $('#myCredForm .manga-points-container.active').find('.madara_purchase_points').attr('value')
        var money_value = $('#myCredForm .manga-points-container.active').find('.madara_purchase_points').data('mon-val')


        var interval = setInterval(function () {
            if ($('#blockoPayBtnQty').length > 0) {
                clearInterval(interval)
                $('#blockoPayModal').attr('data-points-value', points_value)
                $('#blockoPayModal').attr('step', '0.05')
                $("#blockoPayBtnQty").val(money_value)

                $("#blockoPayBtnQty").attr('disabled', true)
            }
        }, 100)
    })

})