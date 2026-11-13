<?php
/**
 * Template de artigo – Fórmula Corpo (NewsBlogger)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Referências: o H2/H3 "Referências" ou "Fontes" e o que vem depois ganham uma seção própria */
add_filter( 'the_content', function ( $c ) {
	if ( ! is_singular( 'post' ) ) return $c;
	return preg_replace(
		'#(<h[23][^>]*>\s*(Refer[eê]ncias|Fontes)[^<]*</h[23]>)(.*?)(?=<h[23]|<aside class="fc-vitrine"|$)#is',
		'<section class="fc-refs" aria-label="Referências">$1$3</section>',
		$c, 1
	);
}, 15 );

get_header();

$fc_cats = array( 'emagrecimento', 'receitas', 'fitness-e-saude', 'suplementos' );

while ( have_posts() ) : the_post();
	if ( function_exists( 'newscrunch_view' ) ) newscrunch_view( get_the_ID() );
	$pid   = get_the_ID();
	$cats  = get_the_category();
	$cat   = $cats ? $cats[0] : null;
	$pub   = get_the_time( 'U' );
	$mod   = get_the_modified_time( 'U' );
	$aid   = get_the_author_meta( 'ID' );
	$bio   = get_the_author_meta( 'description' );
?>
<main id="content" class="fc-single">

	<nav class="fc-bc" aria-label="Breadcrumb">
		<?php if ( function_exists( 'yoast_breadcrumb' ) ) {
			yoast_breadcrumb( '<p>', '</p>' );
		} else { ?>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a> ›
			<?php if ( $cat ) : ?><a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a> ›<?php endif; ?>
			<span aria-current="page"><?php the_title(); ?></span></p>
		<?php } ?>
	</nav>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'fc-art' ); ?>>
		<header class="fc-art-head">
			<?php if ( $cat ) : ?>
				<a class="fc-art-cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endif; ?>
			<h1 class="fc-art-tit"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="fc-art-resumo"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<p class="fc-art-meta">
				Por <a href="<?php echo esc_url( get_author_posts_url( $aid ) ); ?>"><?php the_author(); ?></a>
				· <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php if ( $mod - $pub > DAY_IN_SECONDS ) : ?>
					· Atualizado em <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time>
				<?php endif; ?>
			</p>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="fc-art-img"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
		<?php endif; ?>

		<div class="fc-art-grid">
			<div class="fc-art-main">
				<div class="fc-art-body">
					<?php the_content(); wp_link_pages(); ?>
				</div>

				<?php if ( has_tag() ) : ?><p class="fc-art-tags"><?php the_tags( '', ' ' ); ?></p><?php endif; ?>

				<?php if ( $bio ) : ?>
				<section class="fc-autor" aria-label="Sobre o autor">
					<?php echo get_avatar( $aid, 72, '', '' ); ?>
					<div>
						<h2><a href="<?php echo esc_url( get_author_posts_url( $aid ) ); ?>"><?php the_author(); ?></a></h2>
						<p><?php echo esc_html( $bio ); ?></p>
					</div>
				</section>
				<?php endif; ?>

				<?php edit_post_link( 'Editar artigo', '<p class="fc-edit">', '</p>' ); ?>
			</div>

			<aside class="fc-side" aria-label="Barra lateral">
				<div class="fc-side-box"><?php get_search_form(); ?></div>

				<div class="fc-side-box">
					<h2>Categorias</h2>
					<ul>
					<?php foreach ( $fc_cats as $s ) : $c = get_category_by_slug( $s ); if ( ! $c ) continue; ?>
						<li><a href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a></li>
					<?php endforeach; ?>
					</ul>
				</div>

				<?php $rec = get_posts( array( 'numberposts' => 4, 'post__not_in' => array( $pid ), 'ignore_sticky_posts' => 1 ) );
				if ( $rec ) : ?>
				<div class="fc-side-box">
					<h2>Mais recentes</h2>
					<ul class="fc-side-posts">
					<?php foreach ( $rec as $r ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $r ) ); ?>">
							<?php echo get_the_post_thumbnail( $r, 'thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
							<span><?php echo esc_html( get_the_title( $r ) ); ?></span></a></li>
					<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'fc-ads' ) ) : ?><div class="fc-side-box fc-ad"><?php dynamic_sidebar( 'fc-ads' ); ?></div><?php endif; ?>
			</aside>
		</div>
	</article>

	<?php
	$rel = $cats ? get_posts( array(
		'numberposts' => 3, 'post__not_in' => array( $pid ), 'ignore_sticky_posts' => 1,
		'category__in' => wp_list_pluck( $cats, 'term_id' ), 'orderby' => 'rand',
	) ) : array();
	if ( $rel ) : ?>
	<section class="fc-rel" aria-labelledby="fc-rel-t">
		<h2 id="fc-rel-t">Leia também</h2>
		<div class="fc-rel-grid">
		<?php foreach ( $rel as $r ) : ?>
			<a class="fc-rel-card" href="<?php echo esc_url( get_permalink( $r ) ); ?>">
				<?php echo get_the_post_thumbnail( $r, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
				<h3><?php echo esc_html( get_the_title( $r ) ); ?></h3>
			</a>
		<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( comments_open() || get_comments_number() ) : ?>
		<div class="fc-coments"><?php comments_template(); ?></div>
	<?php endif; ?>

</main>
<?php endwhile;
get_footer();
