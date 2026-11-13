<?php
/**
 * Banner promocional (Ozemix). Mensagem comercial inalterada.
 * Para trocar: edite o array abaixo. Para remover: add_filter( 'fc_home_promo_banner', '__return_empty_array' );
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fc_banner = apply_filters( 'fc_home_promo_banner', array(
	'url' => 'https://formulacorpo.com.br/ozemix',
	'img' => 'https://formulacorpo.com.br/wp-content/uploads/2025/10/comece-agora-o-seu-emagrecimento-1.png',
	'alt' => 'Promoção Compre Agora o Ozemix',
) );

if ( empty( $fc_banner['url'] ) || empty( $fc_banner['img'] ) ) {
	return;
}
?>
<aside class="fc-wrap fc-promo" aria-label="Publicidade">
	<a class="fc-promo__link" href="<?php echo esc_url( $fc_banner['url'] ); ?>">
		<img class="fc-promo__img" src="<?php echo esc_url( $fc_banner['img'] ); ?>" alt="<?php echo esc_attr( $fc_banner['alt'] ); ?>" decoding="async">
	</a>
</aside>
