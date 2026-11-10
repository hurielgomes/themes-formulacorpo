<?php
/**
 * Componente: abertura da ferramenta (único H1 da página).
 *
 * Argumentos ($args):
 *  - id    (string) id do H1, usado em aria-labelledby.
 *  - title (string) título principal.
 *  - lead  (string) descrição curta.
 *  - chips (array)  lista de [ 'icon' => nome do ícone, 'label' => texto ].
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fc_id    = isset( $args['id'] ) ? (string) $args['id'] : 'fc-tools-title';
$fc_title = isset( $args['title'] ) ? (string) $args['title'] : '';
$fc_lead  = isset( $args['lead'] ) ? (string) $args['lead'] : '';
$fc_chips = isset( $args['chips'] ) && is_array( $args['chips'] ) ? $args['chips'] : array();
?>
<section class="fc-tools-hero" aria-labelledby="<?php echo esc_attr( $fc_id ); ?>">
	<div class="fc-tools-container fc-tools-hero__inner">
		<div class="fc-tools-hero__text">
			<h1 id="<?php echo esc_attr( $fc_id ); ?>" class="fc-tools-hero__title"><?php echo esc_html( $fc_title ); ?></h1>
			<p class="fc-tools-hero__lead"><?php echo esc_html( $fc_lead ); ?></p>
			<?php if ( $fc_chips ) : ?>
				<div class="fc-tools-chips">
					<?php foreach ( $fc_chips as $fc_chip ) : ?>
						<span class="fc-tools-chip"><?php fc_tools_icon( $fc_chip['icon'] ); ?><?php echo esc_html( $fc_chip['label'] ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<svg class="fc-tools-hero__art" viewBox="0 0 360 260" aria-hidden="true" focusable="false">
			<circle cx="250" cy="130" r="112" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".22"/>
			<circle cx="250" cy="130" r="80" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".32"/>
			<circle cx="250" cy="130" r="48" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".45"/>
			<path d="M24 196C76 196 88 122 140 132s70 78 120-12 80-52 96-66" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" opacity=".85"/>
			<circle cx="356" cy="54" r="6" fill="currentColor"/>
		</svg>
	</div>
</section>
