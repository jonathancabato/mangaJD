jQuery(document).ready(function ($) {
    $('body').on('click', '.wp-manga-edit-chapter', function () {
        var chapter_id = $(this).data('chapter')
        console.log(chapter_id)
        $.ajax({
            url: wp_manga_params.ajaxurl,
            type: 'POST',
            data: {
                chapter_id: chapter_id,
                action: 'appendhtml'
            },
            success: function (response) {
                $('#wp-manga-chapter-modal .wp-manga-modal-addons').remove()
                $('#wp-manga-chapter-modal .wp-manga-modal-addons-input').remove()

                $(response).insertBefore('#wp-manga-chapter-modal .wp-manga-modal-coin');

            }
        })
    })
})