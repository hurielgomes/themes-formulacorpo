<?php
/**
 * Componente: ferramentas relacionadas.
 *
 * Argumentos ($args):
 *  - id      (string) id do título da seção.
 *  - heading (string) título.
 *  - lead    (string) texto introdutório.
 *  - keys    (array)  chaves do registro (inc/fc-tools-registry.php) a exibir.
 *
 * Ferramenta com página publicada vira link. Sem página publicada, o nome
 * aparece sem link e com a etiqueta "Em breve" (nunca um link quebrado).
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fc_id       = isset( $args['id'] ) ? (string) $args['id'] : 'fc-tools-related-title';
$fc_registry = fc_tools_registry();
$fc_keys     = isset( $args['keys'] ) && is_array( $args['keys'] ) ? $args['keys'] : array();
$fc_cards    = array();

foreach ( $fc_keys as $fc_key ) {
	if ( isset( $fc_registry[ $fc_key ] ) ) {
		$fc_cards[ $fc_key ] = $fc_registry[ $fc_key ] + array( 'url' => fc_tools_tool_url( $fc_key ) );
	}
}

if ( ! $fc_cards ) {
	return;
}
?>
<section class="fc-tools-section" aria-labelledby="<?php echo esc_attr( $fc_id ); ?>">
	<div class="fc-tools-container">
		<div class="fc-tools-section__head">
			<h2 id="<?php echo esc_attr( $fc_id ); ?>" class="fc-tools-h2"><?php echo esc_html( $args['heading'] ); ?></h2>
			<?php if ( ! empty( $args['lead'] ) ) : ?>
				<p class="fc-tools-section__lead"><?php echo esc_html( $args['lead'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="fc-tools-related fc-tools-related--<?php echo (int) count( $fc_cards ); ?>">
			<?php foreach ( $fc_cards as $fc_card ) : ?>
				<?php $fc_active = ! empty( $fc_card['url'] ); ?>
				<article class="fc-tools-card fc-tools-tool<?php echo $fc_active ? '' : ' is-disabled'; ?>">
					<span class="fc-tools-stat__icon"><?php fc_tools_icon( $fc_card['icon'] ); ?></span>
					<h3 class="fc-tools-tool__title">
						<?php if ( $fc_active ) : ?>
							<a class="fc-tools-tool__link" href="<?php echo esc_url( $fc_card['url'] ); ?>"><?php echo esc_html( $fc_card['title'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $fc_card['title'] ); ?>
						<?php endif; ?>
					</h3>
					<p class="fc-tools-tool__desc"><?php echo esc_html( $fc_card['desc'] ); ?></p>
					<?php if ( ! $fc_active ) : ?>
						<span class="fc-tools-tag">Em breve</span>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
