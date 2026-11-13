<?php
$q = new WP_Query( array( 'posts_per_page' => 4, 'ignore_sticky_posts' => 1 ) );
$blog = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/page/2/' );
?>
<div class="fc-head"><h2>Últimos Artigos</h2><a class="fc-link" href="<?php echo esc_url( $blog ); ?>">Ver todos os artigos →</a></div>
<div class="fc-grid-2">
<?php while ( $q->have_posts() ) : $q->the_post();
	$cat = get_the_category();
	$min = max( 1, ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ); ?>
	<article class="fc-card">
		<a class="fc-thumb" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'medium_large' ); ?>
			<?php if ( $cat ) : ?><span class="fc-badge"><?php echo esc_html( $cat[0]->name ); ?></span><?php endif; ?>
		</a>
		<div class="fc-body">
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
			<div class="fc-meta">👤 <?php the_author(); ?> · 📅 <?php echo get_the_date( 'j \d\e M. \d\e Y' ); ?> · ⏱ <?php echo $min; ?> min</div>
		</div>
	</article>
<?php endwhile; wp_reset_postdata(); ?>
</div>
