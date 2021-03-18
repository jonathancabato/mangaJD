<script>
	jQuery(document).ready(function ($) {
		if ($('.c-header__top .manga-search-form').length !== 0 && $('.c-header__top .manga-search-form.search-form').length !== 0) {

			$('form#blog-post-search').append('<input type="hidden" name="post_type" value="wp-manga">');

			$('form#blog-post-search').addClass("manga-search-form");

			$('form#blog-post-search input[name="s"]').addClass("manga-search-field");

			$('form.manga-search-form input.manga-search-field').each(function(){

			var searchIcon = $(this).parent().children('.ion-ios-search-strong');

			var append = $(this).parent();

			$(this).autocomplete({
				appendTo: append,
				source: function( request, resp ) {
					$.ajax({
						url: manga.ajax_url,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'wp-manga-search-manga',
							title: request.term,
						},
						success: function( data ) {
							resp( $.map( data.data, function( item ) {
								if ( true == data.success ) {
									return {
										label: item.title,
										value: item.title,
										url: item.url,
										type: item.type
									}
								} else {
									return {
										label: item.message,
										value: item.message,
										type: item.type,
										click: false
									}
								}
							}))
						}
					});
				},
				select: function( e, ui ) {
					if ( ui.item.url ) {
						window.location.href = ui.item.url;
					} else {
						if ( ui.item.click ) {
							return true;
						} else {
							return false;
						}
					}
				},
				open: function( e, ui ) {
					var acData = $(this).data( 'uiAutocomplete' );
					acData.menu.element.addClass('manga-autocomplete').find('li div').each(function(){
						var $self = $(this),
							keyword = $.trim( acData.term ).split(' ').join('|');
						$self.html( $self.text().replace( new RegExp( "(" + keyword + ")", "gi" ), '<span class="manga-text-highlight">$1</span>' ) );
					});
				}
			}).autocomplete( "instance" )._renderItem = function( ul, item ) {
				  return $( "<li class='search-item'>" )
					.append( "<div class='manga-type-" + item.type + "'>" + item.label + "</div>" )
					.appendTo( ul );
			};
		});
		}
	});
</script>
<ul class="main-menu-search nav-menu">
    <li class="menu-search">
        <a href="javascript:;" class="open-search-main-menu"> <i class="icon ion-ios-search"></i>
            <i class="icon ion-android-close"></i> </a>
        <ul class="search-main-menu">
            <li>
                <form class="manga-search-form search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                    <input class="manga-search-field" type="text" placeholder="<?php echo esc_html__( 'Search...', 'madara' ); ?>" name="s" value="">
                    <input type="hidden" name="post_type" value="wp-manga"> <i class="icon ion-ios-search"></i>
                    <div class="loader-inner ball-clip-rotate-multiple">
                        <div></div>
                        <div></div>
                    </div>
                    <input type="submit" value="<?php esc_html_e( 'Search', 'madara' ); ?>">
                </form>
            </li>
        </ul>
    </li>
</ul>
