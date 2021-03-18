jQuery(document).ready(function ($) {

    $('body').on('click', '#wp-manga-chapter-modal .wp-manga-modal-addons .community-pay-checkbox', function () {

        if ($(this).is(':checked')) {

            $('#wp-manga-chapter-modal .wp-manga-modal-addons-input').attr('style', 'display:block!important');
            $('#wp-manga-chapter-modal #wp-manga-save-paging-button').attr('disabled', true)

        } else {
            $('#wp-manga-chapter-modal .wp-manga-modal-addons-input').val('')
            $('#wp-manga-chapter-modal .wp-manga-modal-addons-input').attr('style', 'display:none!important');
            $('#wp-manga-chapter-modal #wp-manga-save-paging-button').attr('disabled', false)


        }
    })
    $('body').on('blur', '#wp-manga-chapter-modal .wp-manga-modal-addons-input .donation-value', function () {

        var inpt_val = $(this).val()
        console.log(inpt_val)
        if (inpt_val === 0 || inpt_val === '') {
            $('#wp-manga-chapter-modal .wp-manga-modal-addons-input .alert.alert-message').attr('style', 'display:block!important');

            $('#wp-manga-chapter-modal #wp-manga-save-paging-button').attr('disabled', true)
        } else {
            $('#wp-manga-chapter-modal #wp-manga-save-paging-button').attr('disabled', false)
            $('#wp-manga-chapter-modal .wp-manga-modal-addons-input .alert.alert-message').attr('style', 'display:none!important');

        }

    })
})