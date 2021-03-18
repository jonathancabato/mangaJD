
(function ($) {

	"use strict";

	/**
	 * Add 'mobile' class on Responsive Mode
	 * @type {Window}
	 */
	$(window).on('load resize', function () {
		var viewportWidth = window.outerWidth;
		;

		var siteHeader = $('.site-header');

		var isMobile = siteHeader.hasClass('mobile');

		if (viewportWidth < 1008) {
			if (!isMobile) {
				siteHeader.addClass('mobile');
				$('body').addClass('mobile');
			}
		} else {
			if (isMobile) {
				siteHeader.removeClass('mobile');
				$('body').removeClass('mobile');
			}
		}
	});


	$('.distorted-menu ul li a').each(function(index) {
		// console.log($(this)[0].outerText);
		$(this).attr('title', $(this)[0].outerText);
	});
	$('.main-navbar li a').each(function(index) {
		// console.log($(this)[0].outerText);
		$(this).attr('title', $(this)[0].outerText);
	})

})(jQuery);
;
