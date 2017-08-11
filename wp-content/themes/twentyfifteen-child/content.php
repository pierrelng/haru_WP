<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		// Post thumbnail.
		twentyfifteen_post_thumbnail();
	?>
    <a class="post-thumbnail" href="<?php esc_url(the_permalink()); ?>">
        <img src="<?php the_field('cover_source'); ?>">
    </a>

	<header class="entry-header">
        <div class="haru_tag_container">
		<?php
            $venue = get_field('tag_week');
            $fields = get_fields();
            $str_tags_array = [];
            foreach ($fields as $key => $value) {
                if (substr( $key, 0, 4 ) === "tag_" && !empty($value) && $key !== 'tag_week') {
                    if (is_array($value)) {
                        foreach ($value as $tag) {
                            $str_tags_array[] = str_replace("_"," ", $tag);
                            // echo '<div class="haru_tag">'.$str_tag.'</div>';
                        }
                    } else {
                        $str_tags_array[] = str_replace("_"," ", $value);
                        // echo '<div class="haru_tag">'.$str_tag.'</div>';
                    }
                }
            }
            shuffle($str_tags_array);
            for ($i=0; $i < 14; $i++) {
                if (isset($str_tags_array[$i])) {
                    echo '<a href="http://pierrelange.com/tags/'.$str_tags_array[$i].'"><div class="haru_tag">'.$str_tags_array[$i].'</div></a>';
                }
            }
            echo '<div class="haru_tag"> . . . </div>';
        ?>
        </div>
        <?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			endif;
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
			/* translators: %s: Name of current post */
            $start_datetime = get_field('start_time');
            $end_datetime = get_field('end_time');
            $start_datetime = DateTime::createFromFormat('Y-m-d_G:i', $start_datetime);
            $end_datetime = DateTime::createFromFormat('Y-m-d_G:i', $end_datetime);
            // echo $datetime->format('d M Y')." ".$start_time;
            setlocale (LC_TIME, 'fr_FR.utf8','fra');
            $str_start_datetime = strftime("%A %d %B %Hh%M", $start_datetime->getTimestamp());
            $str_end_datetime = strftime("%d %B %Hh%M", $end_datetime->getTimestamp());
            $venue = get_field('venue');
            $venue_name = $venue[0]->post_title;
            echo '<h4>'.ucfirst($str_start_datetime).' — '.$str_end_datetime.'<br>@ '.$venue_name.'</h4>';

			$fb_url = get_field('facebook_event_url');
			echo '<a class="blueanchor" href="'.$fb_url.'">Lien Facebook</a>';
			echo '<p> </p>';

            if ( is_home() || is_archive() ) {
                $description = get_field('description');
                // $description = strip_tags($description);
                if (strlen($description) > 500) { //https://stackoverflow.com/a/4258963/8319316
                    // truncate string
                    $descriptionCut = substr($description, 0, 500);
                    // make sure it ends in a word so assassinate doesn't become ass...
                    $description = substr($descriptionCut, 0, strrpos($descriptionCut, ' ')).'... ';
                }
                echo make_links_clickable($description);
                echo '<p><a class="blueanchor" href="'.esc_url( get_permalink() ).'">Voir plus >>></a></p>';
            } else {
				$description = get_field('description');
				echo make_links_clickable($description);
            }

			the_content( sprintf(
				__( 'Continue reading %s', 'twentyfifteen' ),
				the_title( '<span class="screen-reader-text">', '</span>', false )
			) );

			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfifteen' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) );
		?>
	</div><!-- .entry-content -->

	<?php
		// Author bio.
		if ( is_single() && get_the_author_meta( 'description' ) ) :
			get_template_part( 'author-bio' );
		endif;
	?>

	<footer class="entry-footer">
		<?php twentyfifteen_entry_meta(); ?>
		<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
