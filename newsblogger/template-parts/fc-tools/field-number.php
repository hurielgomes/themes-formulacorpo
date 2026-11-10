<?php
/**
 * Componente: campo numérico com unidade visível.
 *
 * Argumentos ($args):
 *  - id          (string) id do campo (o aviso usa "{id}-msg").
 *  - field       (string) nome lógico do campo (peso, altura...).
 *  - label       (string) rótulo visível.
 *  - unit        (string) unidade visível (kg, cm...).
 *  - unit_spoken (string) unidade por extenso, para leitores de tela.
 *  - placeholder (string) exemplo discreto.
 *  - hint        (string) texto de ajuda (o JavaScript o atualiza a partir dos limites do cálculo).
 *  - inputmode   (string) decimal | numeric.
 *
 * Privacidade: o campo NÃO tem atributo name e o formulário não tem action.
 * Mesmo que um envio ocorresse por engano, nada seria enviado nem iria para a URL.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fc_id    = (string) $args['id'];
$fc_msg   = $fc_id . '-msg';
$fc_mode  = isset( $args['inputmode'] ) ? (string) $args['inputmode'] : 'decimal';
?>
<div class="fc-tools-field">
	<label class="fc-tools-label" for="<?php echo esc_attr( $fc_id ); ?>"><?php echo esc_html( $args['label'] ); ?> <span class="fc-tools-sr">em <?php echo esc_html( $args['unit_spoken'] ); ?></span></label>
	<div class="fc-tools-input-wrap">
		<input class="fc-tools-input" id="<?php echo esc_attr( $fc_id ); ?>" type="text" inputmode="<?php echo esc_attr( $fc_mode ); ?>" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="16" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" aria-describedby="<?php echo esc_attr( $fc_msg ); ?>" aria-invalid="false" data-fc-field="<?php echo esc_attr( $args['field'] ); ?>">
		<span class="fc-tools-unit" aria-hidden="true"><?php echo esc_html( $args['unit'] ); ?></span>
	</div>
	<p class="fc-tools-msg" id="<?php echo esc_attr( $fc_msg ); ?>"><?php echo esc_html( $args['hint'] ); ?></p>
</div>
