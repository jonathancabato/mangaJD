<?php

	/**
	 * The Template for displaying all single page.
	 *
	 * @package madara
	 */

	get_header();

	$madara_page_sidebar = madara_get_theme_sidebar_setting();

?>


	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'html/single/content', 'page' ); 
	 endwhile; // end of the loop. ?>



<?php

	get_footer();
