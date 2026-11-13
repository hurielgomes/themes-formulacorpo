<aside class="fc-side">
	<form class="fc-box fc-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" placeholder="Buscar por um tema, receita ou artigo...">
		<button>🔍</button>
	</form>

	<div class="fc-box">
		<h4>Categorias</h4>
		<ul class="fc-catlist">
			<?php foreach ( get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 6 ) ) as $c ) : ?>
				<li><a href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a><span><?php echo $c->count; ?></span></li>
			<?php endforeach; ?>
		</ul>

		<h4>Artigos em alta</h4>
		<?php $t = new WP_Query( array( 'posts_per_page' => 4, 'orderby' => 'comment_count', 'ignore_sticky_posts' => 1 ) );
		while ( $t->have_posts() ) : $t->the_post(); ?>
			<a class="fc-trend" href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'thumbnail' ); ?>
				<span><?php the_title(); ?></span>
			</a>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>

	<div class="fc-box fc-news">
		<h4>Receba nossos conteúdos</h4>
		<p>Dicas, novidades e artigos exclusivos direto no seu e-mail.</p>
		<input type="email" placeholder="Seu melhor e-mail">
		<button>Quero receber</button>
	</div>
</aside>
