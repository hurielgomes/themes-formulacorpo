<?php
/**
 * Destaques editoriais: 1 principal (direita) + 2 menores (esquerda).
 * Sem a Fase 2 ativa, carrega o slider original do Newscrunch.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'fc_home_active' ) ) {
	require get_template_directory() . '/partials/front/main-banner.php';
	return;
}

if ( ! get_theme_mod( 'hide_show_banner', true ) ) {
	return;
}

$fc_posts = fc_home_featured_posts();
if ( count( $fc_posts ) < 3 ) {
	return; // Nunca exibe destaques incompletos ou inventados.
}

$fc_card = function ( $post, $is_main ) {
	$cat     = fc_home_primary_category( $post->ID );
	$heading = $is_main ? 'h2' : 'h3';
	?>
	<article class="fc-card <?php echo $is_main ? 'fc-card--main' : 'fc-card--small'; ?>">
		<a class="fc-card__link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php
			echo get_the_post_thumbnail( $post, $is_main ? 'full' : 'large', array(
				'class'         => 'fc-card__img',
				'alt'           => '',
				'loading'       => $is_main ? 'eager' : 'lazy',
				'fetchpriority' => $is_main ? 'high' : 'auto',
				'sizes'         => $is_main ? '(min-width: 1025px) 760px, 100vw' : '(min-width: 1025px) 440px, 100vw',
			) );
			?>
			<div class="fc-card__body">
				<?php if ( $cat ) : ?>
					<span class="fc-card__cat"><?php echo esc_html( $cat->name ); ?></span>
				<?php endif; ?>
				<<?php echo $heading; ?> class="fc-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></<?php echo $heading; ?>>
				<p class="fc-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), $is_main ? 26 : 16 ) ); ?></p>
				<p class="fc-card__meta">
					<?php if ( $is_main ) : ?>
						<span>Por <?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?></span>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( 'j \d\e M. \d\e Y', $post ) ); ?></time>
					<span><?php echo (int) fc_home_reading_time( $post ); ?> min de leitura</span>
				</p>
			</div>
		</a>
	</article>
	<?php
};
?>
<section class="fc-feat" aria-label="Destaques">
	<div class="fc-wrap fc-feat__grid">
		<div class="fc-feat__main"><?php $fc_card( $fc_posts[0], true ); ?></div>
		<div class="fc-feat__side">
			<?php $fc_card( $fc_posts[1], false ); ?>
			<?php $fc_card( $fc_posts[2], false ); ?>
		</div>
	</div>
</section>
