<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WP_MANGA_COIN_REPORT_TABLE extends WP_List_Table {
	public function get_columns() {
		$columns = array(
						'title' => esc_html__('Title', MANGA_CHAPTER_COIN_TEXT_DOMAIN),
						'author' => esc_html__('Author', MANGA_CHAPTER_COIN_TEXT_DOMAIN),
						'coins' => esc_html__('Revenue (Coins)', MANGA_CHAPTER_COIN_TEXT_DOMAIN)
					);
					
		return $columns;
	}
	
	private function get_all_items( $author_id = 0, $manga_id = 0){
		$args = array('post_type' => 'wp-manga',
						'posts_per_page' => -1);
						
		if($author_id) {
			$args['author'] = $author_id; 
		}
		
		if($manga_id) {
			$args['include'] = array($manga_id);
		}
						
		return get_posts( $args );
	}
	
	private function get_data( $posts_per_page = 10, $paged = 1, $author_id = 0, $manga_id = 0, $date_from = '', $date_to = '', $order = 'ASC', $orderby = 'title'){
		$args = array('post_type' => 'wp-manga',
						'posts_per_page' => $posts_per_page,
						'offset' => ($paged - 1) * $posts_per_page
					);
		
		if($orderby == 'title') {
			$args['orderby'] = 'title';
			$args['order'] = $order;
		}
						
		if($author_id) {
			$args['author'] = $author_id; 
		}
		
		if($manga_id) {
			$args['include'] = array($manga_id);
		}
		
		if($orderby == 'coins'){
			$args['posts_per_page'] = -1;
		}
		
		$mangas = get_posts($args);
		
		$data = array();
		
		$backend = WP_MANGA_ADDON_CHAPTER_COIN_BACKEND::get_instance();
		
		foreach($mangas as $manga){
			$item = array(
						'id' => $manga->ID,
						'title' => $manga->post_title,
						'author' => $manga->post_author,
						'coins' => $backend->get_revenue($manga->ID, $date_from, $date_to)
						);
						
			array_push($data, $item);
		}
		
		if($orderby == 'coins'){
			if($order == 'desc'){
				usort($data, function( $item1, $item2 ){
					if($item1['coins'] == $item2['coins']){
						return 0;
					}
					
					return ($item1['coins'] > $item2['coins'] ? -1 : 1);
				});
			} else {
				usort($data, function( $item1, $item2 ){
					if($item1['coins'] == $item2['coins']){
						return 0;
					}
					
					return ($item1['coins'] > $item2['coins'] ? 1 : -1);
				});
			}
			
			$data = array_slice($data, ($paged - 1) * $posts_per_page, $posts_per_page);
		}
		
		return $data;
	}
	
	function extra_tablenav( $which ) {
		$author_id = isset($_GET['authors']) ? $_GET['authors'] : 0;
		$manga_id = isset($_GET['manga_id']) ? $_GET['manga_id'] : 0;
		$date_from = isset($_GET['from']) ? $_GET['from'] : '';
		$date_to = isset($_GET['to']) ? $_GET['to'] : '';
		$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
		if ( $which == "top" ){
			?>
			<div class="filter-actions">
				<form method="GET" action="<?php echo admin_url('admin.php');?>">
					<input type="hidden" value="wp-manga-chapter-coin/revenue" name="page"/>
				<label><?php echo esc_html__('Filter by', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?>
					<select name="authors" class="">
						<option value=""><?php echo esc_html__('Author', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></option><?php
						
						$authors = get_users(array('role__in' => apply_filters('wp_manga_chapter_coin_report_author_roles', array('author', 'editor', 'administrator'))));
						
						foreach( $authors as $author ){
						?>
						<option <?php selected($author->ID, $author_id);?> value="<?php echo $author->ID; ?>"><?php echo $author->display_name; ?></option>
						<?php
						}
						?>
					</select>
				</label>
				
				<label><?php echo esc_html__('Manga ID', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?><input type="text" name="manga_id" value="<?php echo $manga_id ? $manga_id : '';?>"/></label>
				
				<label><?php echo esc_html__('Date From', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?><input type="date" name="from" value="<?php echo $date_from;?>"/></label>
				
				<label><?php echo esc_html__('Date To', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?><input type="date" name="to" value="<?php echo $date_to;?>"/></label>
				
				<input type="submit" value="<?php echo esc_html__('Filter', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?>"/>
				
				<label><?php echo esc_html__('Per Page', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?>
					<select name="per_page" class="">
						<option value="10" <?php selected($per_page, 10);?>>10</option>
						<option value="20" <?php selected($per_page, 20);?>>20</option>
						<option value="50" <?php selected($per_page, 50);?>>50</option>
						<option value="100" <?php selected($per_page, 100);?>>100</option>
					</select>
				</label>
				</form>
			</div>
			<?php
		}
		
		if ( $which == "bottom" ){
			//The code that goes after the table is there

		}
	}
	
	function get_sortable_columns() {
	  $sortable_columns = array(
		'coins'  => array('coins',false),
		'title' => array('title',false)
	  );
	  return $sortable_columns;
	}
  
	public function prepare_items() {
		$columns = $this->get_columns();
	    $hidden = array();
	    $sortable = $this->get_sortable_columns();
	    $this->_column_headers = array($columns, $hidden, $sortable);
	    
		
		  $current_page = $this->get_pagenum();
		  
		  $author_id = isset($_GET['author']) ? $_GET['author'] : 0;
		  $manga_id = isset($_GET['manga_id']) ? $_GET['manga_id'] : 0;
		  $date_from = isset($_GET['from']) ? $_GET['from'] : '';
		  $date_to = isset($_GET['to']) ? $_GET['to'] : '';
		  $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
		  $order = isset($_GET['order']) ? $_GET['order'] : 'asc';
		  $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'title';
		  
		  $total_items = count($this->get_all_items( $author_id, $manga_id ));

		  $this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page                     //WE have to determine how many items to show on a page
		  ) );
		  
		  $this->items = $this->get_data($per_page, $current_page, $author_id, $manga_id, $date_from, $date_to, $order, $orderby );
	}
	
	function column_default( $item, $column_name ) {
	  switch( $column_name ) {
		 case 'title':
			return '<a href="' . get_permalink($item['id']) . '" target="_blank">' . $item['title'] . '</a>';
		 case 'index': 
			return;
		 case 'author':
			return '<a href="' . admin_url('user-edit.php') . '?user_id=' . $item['author'] . '">' . get_the_author_meta('display_name', $item['author']) . '</a>';
		default:
		  return isset($item[$column_name]) ? $item[$column_name] : '';
	  }
	}
}