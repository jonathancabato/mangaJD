jQuery(document).ready(function($){
	$(document).on('wp_manga_before_admin_save_chapter', function(e, manga_id, chapter_id){
		var coin = $('input[name="chapter-coin"]').val();
	
		
		
		if(coin != '') {
			$.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wp_manga_chapter_coin_save',
					chapter_id: chapter_id,
					manga_id: manga_id,
					chapter_coin: coin,
				},
				success: function (resp) {

					if (resp.success == true) {
						// do nothing
					} else {
						console.log('Unable to save chapter coin');
					}
					
				},
				complete : function(){
					// do nothing
				}
			});
		}
		
	});
	
	$(document).on('wp_manga_after_admin_fill_chapter_modal_content', function(e, chapter, storage){
		$.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wp_manga_admin_chapter_modal_get_coin',
					chapter_id: chapter.chapter.chapter_id,
				},
				success: function (coin) {
					$('input[name="chapter-coin"]').val(coin);
				},
				complete : function(){
					// do nothing
				}
			});
	});
});