jQuery(document).ready(function ($) {

    function checkMangaChaptersCmPay() {
        var is_anonymous = false;
        var $thisBtn;

        $.ajax({
            url: wp_manga_modal_params.ajaxurl,
            type: 'POST',
            data: {
                action: 'modal',
                query: 'get',
                nonce: wp_manga_modal_params.nonce,
                post_id: wp_manga_modal_params.post_id
            },
            success: function (response) {
                console.log(response)
                if (response.status == 'success') {
                    console.log($('.single-wp-manga .main.version-chap'), 0)
                    // $('.single-wp-manga .main.version-chap').css('display', 'none')

                    if (response.user !== 'false') {
                        $(response.modal).insertAfter('#frm-wp-manga-buy-coin')
                    }
                    setTimeout(function () {
                        $('.single-wp-manga .main.version-chap').attr('style', 'display:block!important')

                    }, 500)
                    var interval = setInterval(function () {

                        if ($('li.wp-manga-chapter').length > 0 || $('.reading-manga .reading-content').length > 0) {

                            clearInterval(interval);
                            for (var i = 0; i < response.data.length; i++) {
                                if (response.data[i].is_unlocked === 'no') {

                                    if ($('.premium.data-chapter-' + response.data[i].chapter_id).length > 0) {
                                        var $this = $('.data-chapter-' + response.data[i].chapter_id)

                                        $this.find('.coin').html('<i class="fas fa-lock"></i>Community Unlock ' + response.data[i].total_payment + '/' + response.data[i].goal)
                                        $this.find('a').addClass('community-unlock').attr('data-chapter', response.data[i].chapter_id)

                                    }

                                    // $('.single-wp-manga .main.version-chap').css('display', 'block')

                                } else if (response.data[i].is_unlocked === 'yes') {
                                    if ($('.free-chap.data-chapter-' + response.data[i].chapter_id).length > 0) {
                                        var $this = $('.data-chapter-' + response.data[i].chapter_id)

                                        $this.find('.coin').html('<i class="fas fa-unlock"></i>Unlocked By Community')
                                        $this.find('a').addClass('community-unlock').attr('data-chapter', response.data[i].chapter_id).parent().addClass('premium')

                                    }


                                } else {
                                    if ($('.premium-block.data-chapter-' + response.data[i].chapter_id).length > 0) {
                                        var $this = $('.data-chapter-' + response.data[i].chapter_id)
                                        $this.find('.coin').html('<i class="fas fa-lock"></i>Community Unlock')
                                        $this.find('a').addClass('community-unlock').attr('data-chapter', response.data[i].chapter_id)
                                        $('.single-wp-manga .main.version-chap').attr('style', 'display:block!important')

                                    }

                                }
                            }
                            $('.premium-block a').on('click', function (e) {
                                $thisBtn = $(this);
                                if ($thisBtn.hasClass('community-unlock')) {
                                    $("#frm-wp-manga-buy-coin .modal-premium-btn").hide()
                                    $('#frm-wp-manga-buy-coin .modal-footer .btn-agree').hide()
                                    $('#frm-wp-manga-buy-coin .modal-donation-btn').show()
                                    // if ($('#frm-wp-manga-buy-coin .modal-footer .btn-agree').length > 0) {
                                    $('#frm-wp-manga-buy-coin .message-sufficient').html('You can <span class="coin">DONATE</span> and reach a goal to unlock this chapter')
                                    $('#frm-wp-manga-buy-coin .message-lack-of-coin').html('You can <span class="coin">DONATE</span> and reach a goal to unlock this chapter')
                                    // }else{

                                    // }
                                    $('.modal-donation-btn').remove()
                                    if (response.user !== 'false') {
                                        $(response.button).insertBefore('#frm-wp-manga-buy-coin .btn-cancel')
                                    }
                                    $("#frm-wp-manga-buy-coin").attr('data-community-chapter', '')
                                    $('#frm-wp-manga-buy-coin').attr('data-community-chapter', $thisBtn.data('chapter'))


                                } else {
                                    $("#frm-wp-manga-buy-coin .modal-premium-btn").show()
                                    $('#frm-wp-manga-buy-coin .modal-footer .btn-agree').show()
                                    $('#frm-wp-manga-buy-coin .modal-donation-btn').hide()

                                    $('#frm-wp-manga-buy-coin .message-sufficient').html('You are about to spend <span class="coin">50</span> coins to unlock this chapter')
                                    $('#frm-wp-manga-buy-coin .message-lack-of-coin').text('You do not have enough Coins to buy this chapter')


                                }
                            })
                            $('body').on('click', '.modal-donation-btn', function (e) {
                                $('#frm-wp-manga-buy-coin').modal('hide')

                                for (var i = 0; i < response.data.length; i++) {
                                    if ($thisBtn.data('chapter') == response.data[i].chapter_id) {
                                        var total_payments = response.data[i].total_payment
                                        var goal = response.data[i].goal
                                        var percent = Math.round(((total_payments / goal) * 100) * 100) / 100
                                        $('#myDonateModal .modal-header .current-amount').text(response.data[i].total_payment)
                                        $('#myDonateModal .modal-header .total-amount').text(response.data[i].goal)
                                        $('#myDonateModal .progress .progress-text').text(percent + '%')
                                        $('#myDonateModal .progress .progress-bar').css('width', percent + '%')
                                        $('#myDonateModal .points-to-donate').attr('max', goal - total_payments)
                                        $('#myDonateModal .points-to-donate').attr('min', 1)
                                    }
                                }
                                setTimeout(function () {

                                    $('#myDonateModal').modal('show')

                                }, 400)
                            })

                            $('body').on('change', 'select.toggle-identity', function () {
                                // alert(this.value);

                                if (this.value === 'No') {
                                    // console.log('no')
                                    $('input.donor-name').prop("disabled", false);
                                    // $('input.donor-name').attr("placeholder", "Your Name");
                                    $('input.donor-name').attr("value", response.username)
                                    is_anonymous = 'false';


                                } else {
                                    let r = "Anonymous_" + Math.random().toString(36).substring(2);
                                    // console.log(r);
                                    $('input.donor-name').prop("disabled", true);
                                    $('input.donor-name').attr("placeholder", r);
                                    $('input.donor-name').attr("value", r);
                                    is_anonymous = 'true'

                                }

                            });

                            $('body').on('click', '#myDonateModal #sendPoints', function (e) {
                                e.preventDefault()
                                $('#myDonateModal .response-message').hide()
                                var donor_name = $('input.donor-name').val()
                                var points = $('input.points-to-donate').val()
                                var chapter_id = $('#frm-wp-manga-buy-coin').data('community-chapter')
                                console.log(donor_name, points, chapter_id)

                                if (donor_name !== '' && points !== '') {

                                    var max_donation = $('.points-to-donate').attr('max')

                                    if (parseInt(points) <= parseInt(max_donation)) {
                                        if (parseInt(points) > 0) {
                                            if (!$('#myDonateModal #sendPoints').hasClass('disabled')) {
                                                $('#myDonateModal #sendPoints').addClass('disabled')
                                                $('#myDonateModal .donor-name').attr('disabled', true)
                                                $('#myDonateModal .points-to-donate').attr('disabled', true)
                                                $('#myDonateModal .btn-loading').show()
                                                $.ajax({
                                                    url: wp_manga_modal_params.ajaxurl,
                                                    type: 'POST',
                                                    data: {
                                                        action: 'modal',
                                                        query: 'insert',
                                                        user: donor_name,
                                                        amount: points,
                                                        is_anonymous: is_anonymous,
                                                        nonce: wp_manga_modal_params.nonce,
                                                        chapter_id: chapter_id,
                                                        post_id: wp_manga_modal_params.post_id,
                                                        user_id: wp_manga_modal_params.user_id
                                                    },
                                                    beforeSend: function () {
                                                        $('#myDonateModal .response-message').text('Processing please wait...').addClass('text-light').removeClass('text-success, text-warning,text-danger')
                                                    },
                                                    success: function (response) {

                                                        if (response.status == 'success') {

                                                            $('#myDonateModal .btn-loading').show()
                                                            $('#myDonateModal .response-message').text(response.text).addClass('text-success').removeClass('text-warning, text-light, text-danger')
                                                            setTimeout(function () {
                                                                window.location.reload()
                                                            }, 500)
                                                        } else if (response.status == 'error_h') {
                                                            $('#myDonateModal .response-message').text(response.text).removeClass('text-success, text-light, text-danger').addClass('text-warning')
                                                            $('#myDonateModal .btn-loading').show()
                                                            $('#myDonateModal #sendPoints').removeClass('disabled')
                                                            $('#myDonateModal .donor-name').attr('disabled', false)
                                                            $('#myDonateModal .points-to-donate').attr('disabled', false)
                                                        } else {
                                                            $('#myDonateModal .response-message').text('Something bad happened, please refresh the page and try again').addClass('text-warning').removeClass('text-success, text-light, text-danger')
                                                            $('#myDonateModal .btn-loading').show()
                                                            $('#myDonateModal #sendPoints').removeClass('disabled')
                                                            $('#myDonateModal .donor-name').attr('disabled', false)
                                                            $('#myDonateModal .points-to-donate').attr('disabled', false)
                                                        }
                                                    }
                                                })
                                            }
                                        } else {
                                            $('#myDonateModal .response-message').text('Points to donate must be exact or minimun of the donation needed. Maximun points to donate: ' + max_donation).addClass('text-warning').removeClass('text-success, text-light, text-danger')
                                        }
                                    } else {
                                        $('#myDonateModal .response-message').text('Points to donate must be exact or minimun of the donation needed. Maximun points to donate: ' + max_donation).addClass('text-warning').removeClass('text-success, text-light, text-danger')
                                    }
                                } else if (donor_name != '' && points == '') {
                                    $('#myDonateModal .response-message').text('Empty input, please enter the amount to donate').addClass('text-warning').removeClass('text-success')
                                } else if (donor_name == '' && points != '') {
                                    $('#myDonateModal .response-message').text('Empty input, please enter the donor name').addClass('text-warning').removeClass('text-success, text-light, text-danger')
                                } else if (donor_name == '' && points == '') {
                                    $('#myDonateModal .response-message').text('Empty input, please input the missing fields').addClass('text-warning').removeClass('text-success, text-light, text-danger')
                                }
                                $('#myDonateModal .response-message').show()

                            })

                        }
                    })

                } else {
                    var int = setInterval(function () {
						if($('.single-wp-manga .main.version-chap').length > 0){
                       	 	clearInterval(int)
							$('.single-wp-manga .main.version-chap').attr('style', 'display:block!important')

						}
                    }, 100)
                }
            }
        })

    }
    checkMangaChaptersCmPay()


})