jQuery(document).ready(function ($) {
    $('#wp-manga-chapter-modal #wp-manga-save-paging-button').on('click', function () {
        var pay_to_unlock = $('#wp-manga-chapter-modal .wp-manga-modal-addons .community-pay-checkbox')

        if ($(pay_to_unlock).is(':checked')) {

            var inpt_val = $('#wp-manga-chapter-modal .wp-manga-modal-addons-input .donation-value').val()
            var chapter_id = $('#wp-manga-chapter-modal .wp-manga-modal-addons-input').data('chapter-id')
            $.ajax({
                url: wp_manga_params.ajaxurl,
                type: 'POST',
                data: {
                    donation_goal: inpt_val,
                    chapter_id: chapter_id,
                    query: 'insert',
                    action: 'savetodb'
                },
                success: function (response) {
                    console.log(response)
                }
            })

        } else if (!$(pay_to_unlock).is(':checked') && $('#wp-manga-chapter-modal .wp-manga-modal-addons-input .donation-value').val() !== 0 || $('#wp-manga-chapter-modal .wp-manga-modal-addons-input .donation-value').val() !== '') {
            // var inpt_val = $('#wp-manga-chapter-modal .wp-manga-modal-addons-input .donation-value').val()
            var chapter_id = $('#wp-manga-chapter-modal .wp-manga-modal-addons-input').data('chapter-id')
            console.log('sampleee')
            $.ajax({
                url: wp_manga_params.ajaxurl,
                type: 'POST',
                data: {
                    donation_goal: 0,
                    chapter_id: chapter_id,
                    query: 'delete',
                    action: 'savetodb'
                },
                success: function (response) {
                    console.log(response)
                }
            })

        }
    })
})