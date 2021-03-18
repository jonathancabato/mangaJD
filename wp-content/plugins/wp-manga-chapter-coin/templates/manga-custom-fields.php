<?php

$label = $field['name'];
$value = get_post_meta(get_the_ID(), $field['id'], true);
$type = $field['type'];
$taxonomy = $field['taxonomy'];
if(empty($value)) return;
?>
<div class="post-content_item">
	<div class="summary-heading">
		<h5>
			<?php echo esc_html($label); ?>
		</h5>
	</div>
	<div class="summary-content">
		<?php 
		if($type == 'taxonomy_checkbox'){
			$names = array();
			foreach($value as $term_id){
				$term = get_term_by('id', $term_id, $taxonomy);
				if($term){
					array_push($names, '<a href="' . get_term_link($term) . '" alt="' . $term->name . '">' . $term->name . '</a>');
				}
			}
			echo implode(', ', $names);
		} elseif($type == 'taxonomy_select') {
			$term = get_term_by('id', $value, $taxonomy);
			if($term){
				echo '<a href="' . get_term_link($term) . '" alt="' . $term->name . '">' . $term->name . '</a>';
			}
		} elseif($type == 'select') {
			$options = explode(PHP_EOL, $field['values']);
			$label = '';
			foreach($options as $option){
				list($key, $val) = explode(':', $option);
				if(trim($key) == $value){
					$label = $val;
					break;
				}
			}
			echo wp_kses_post( $label );
		} else {
			echo wp_kses_post( $value );
		}
		 ?>
	</div>
</div>