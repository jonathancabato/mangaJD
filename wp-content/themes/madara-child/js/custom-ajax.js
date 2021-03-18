
jQuery(function ($) { // use jQuery code inside this to avoid "$ is not defined" error
    $('.load-more').click(function (e) {
        e.preventDefault();
        var genre_id = $('.manga-section .manga-container .posts').data('genre-id')
        var max_page = $('.manga-section .manga-container .posts').data('total-num-page')
        console.log(loadmore_params.current_page, loadmore_params.max_page);
        var button = $(this),
            data = {
                'action': 'loadmore',
                'paged': loadmore_params.current_page,
                'post_per_page': loadmore_params.post_count,
                'term': genre_id

            };

        $.ajax({ // you can also use $.post here
            url: loadmore_params.ajaxurl, // AJAX handler
            data: data,
            type: 'POST',
            beforeSend: function (xhr) {
                console.log(data);
                button.addClass('disabled')
                button.find('.overlay-btn-text').text('Loading...'); // change the button text, you can also add a preloader image
            },

            success: function (data) {
                if (data) {
                    button.removeClass('disabled')

                    button.find('.overlay-btn-text').text('More posts') // insert new posts
                    $('.manga-section .manga-container .posts').append(data)
                    loadmore_params.current_page++;

                    if (loadmore_params.current_page - 2 == max_page)
                        button.remove(); // if last page, remove the button

                    // you can also fire the "post-load" event here if you use a plugin that requires it
                    // $( document.body ).trigger( 'post-load' );
                } else {
                    button.remove();
                }

            }
        });
    });
});