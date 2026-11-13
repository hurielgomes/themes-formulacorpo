<?php
/** Card de post. Uso: get_template_part( 'template-parts/fc-home/card', null, array( 'excerpt' => true ) ); */
$args = wp_parse_args( $args ?? array(), array( 'excerpt' => false, 'size' => 'medium_large' ) );
$cat  = get_the_category();
?>
<article <?php post_class( 'fc-card' ); ?>>
	<a class="fc-card__thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) { the_post_thumbnail( $args['size'], array( 'loading' => 'lazy' ) ); } ?>
	</a>
	<div class="fc-card__body">
		<?php if ( $cat ) : ?>
			<a class="fc-card__cat" href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>"><?php echo esc_html( $cat[0]->name ); ?></a>
		<?php endif; ?>
		<h3 class="fc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( ! empty( $args['excerpt'] ) ) : ?>
			<p class="fc-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<?php endif; ?>
		<div class="fc-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( function_exists( 'fc_reading_time' ) ) : ?>
				<span> · <?php echo esc_html( fc_reading_time( get_the_ID() ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</article>
