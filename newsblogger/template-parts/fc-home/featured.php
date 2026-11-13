<?php $f = new WP_Query( array( 'posts_per_page' => 4, 'offset' => 4, 'ignore_sticky_posts' => 1 ) ); ?>
<section class="fc-featured">
	<div class="fc-head">
		<h2>Conteúdos em Destaque</h2>
		<a class="fc-link" href="<?php echo esc_url( home_url( '/page/2/' ) ); ?>">Ver mais →</a>
	</div>
	<div class="fc-grid-4">
	<?php while ( $f->have_posts() ) : $f->the_post(); $cat = get_the_category(); ?>
		<a class="fc-mini" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'medium' ); ?>
			<?php if ( $cat ) : ?><span class="fc-tag"><?php echo esc_html( $cat[0]->name ); ?></span><?php endif; ?>
			<h5><?php the_title(); ?></h5>
		</a>
	<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
