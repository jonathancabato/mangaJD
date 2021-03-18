jQuery(document).ready(function($) {
	
	var navHeight = $('.main-navigation').height();
	$('.site-content').css('margin-top', navHeight);
	
    $(".madara-background").change(function() {
     
      
    $('.content-area').removeClass('bg-light-grey bg-white bg-dark bg-sand bg-wood bg-light-wood bg-light-yellow bg-dark-yellow bg-light-pink').addClass($(this).val());
	
    }); 
	
setInterval(function(){

	if($('body').hasClass('mobile')){
		if($('body.mobile .manga-inner-wrap').hasClass('desktop-background'))
		{
			$('body.mobile .manga-inner-wrap').removeClass('desktop-background')
		}
	}else{
		$('.manga-inner-wrap').addClass('desktop-background')
	}
},250)
    $(".madara-font").change(function() {
     
      
     
      $('.text-left').removeClass('font-palatino font-segoe font-roboto font-roboto-condensed font-patrick-hand font-noticia font-times-new-roman font-verdana font-tahoma font-arial').addClass($(this).val());
	
    });
    $('.madara-font-color').change(function(){
		$(".content-area, .c-blog-post, .text-left, .text-sidebar, #commentform").removeClass('font-dark font-light').addClass($(this).val());
	
    
    });
    $('.madara-font-size').change(function(){
      $(".text-left").removeClass('font-16 font-18 font-20 font-22 font-24 font-26 font-28 font-30').addClass($(this).val());
    
      
      })
      $('.madara-line-height').change(function(){
        $(".text-left").removeClass('line-100 line-120 line-140 line-160 line-180 line-200').addClass($(this).val());
      
        
        })
    $('.dropbtn').on('click', function(e){
      e.preventDefault()
      $(this).parent().find('.madara_dropdown').toggleClass('show')
    })
  

    $('.google-icon').on('click', function(e) {
      e.preventDefault();
      $('.google-wrapper').addClass('open');
      $('.google-wrapper').removeClass('close');
    });
 
    $('.close-google').on('click', function(e) {
      e.preventDefault();
      $('.google-wrapper').addClass('close');
      $('.google-wrapper').removeClass('open');
    });

    $('.madara_purchase_points').on('click', function(e) {
      var valOpt = $(this).attr('value');
      $('.select_crown_points').val(valOpt);
      
      // $('se option[value="4"]').attr("selected", true);
    });
    
    $('#myCredForm .card.card-body.mb-2').on('click', function(){
      $('#myCredForm .card').removeClass('active')
      $(this).addClass('active')
    })

    $('.modal-header-btn').on('click', function (e) {
        if ($(this).hasClass('modal-log-in')) {
            $('.modal-body .nav-tabs .nav-link, .modal-body .nav-tabs .nav-item').removeClass('active')

            $('#login-nav').addClass('active')
            $('#login-nav .nav-link').addClass('active')
            var href = $('#login-nav .nav-link').attr('href');

            $('#sign-up').css('display', 'none');
            $(href).css('display', 'block')
        } else {
            $('.modal-body .nav-tabs .nav-link,  .modal-body .nav-tabs .nav-item').removeClass('active')
            $('#sign-in-nav').addClass('active')
            $('#sign-in-nav .nav-link').addClass('active')

            var href = $('#sign-in-nav .nav-link').attr('href');
            console.log(href)
            $('#login').css('display', 'none');
            $(href).css('display', 'block')
        }

    })

   if($('#manga-reading-nav-head')){
    console.log($('#google_translate_element'))

     if(!$('.madara_dropdown')){

     }else{
    
    }
    $('#google_translate_element').remove()
    setTimeout(function(){
    if($('select.goog-te-combo').length > 1){
     $('select.goog-te-combo')[0].remove()
     $('select.goog-te-combo')[1].remove()
     $('a.goog-logo-link')[1].remove()
     $('.skiptranslate.goog-te-gadget')[2].remove()
     $('.skiptranslate.goog-te-gadget')[1].remove()
    }
  }, 500)
  }

    $('.wp-manga-section .modal-body .nav-tabs .nav-link').on('click', function (e) {
        e.preventDefault();
        $('.modal-body .nav-tabs .nav-link , .modal-body .nav-tabs .nav-item').removeClass('active')
        $(this).addClass('active')
        $(this).parent().addClass('active')
        var href = $(this).attr('href');
        $('.login').css('display', 'none');
        $(href).css('display', 'block')
    })
    $('.modal-premium-btn').on('click', function(e){
      $('#frm-wp-manga-buy-coin').modal('hide')
      setTimeout(function(){
        $('#myCredForm').modal('show')
      }, 400)
    })
  
  
    //On Scroll
    $(document).on('scroll', function(){
      if($('.c-sub-header-nav').hasClass('sticky')){
        $('.c-sub-header-nav .distorted-menu').show()
      }else{
        
        $('.c-sub-header-nav .distorted-menu').hide()
      }
    })
  });

// function myFunction() {
//   document.querySelectorAll(".madara_dropdown")[1].classList.toggle("show");
//   console.log(document.querySelectorAll(".madara_dropdown")[1])
// }
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
       
      }
    }
  }
}