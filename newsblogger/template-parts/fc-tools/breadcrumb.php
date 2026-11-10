<?php
/**
 * Componente: breadcrumb das ferramentas.
 *
 * Argumentos ($args):
 *  - current (string) Rótulo da página atual.
 *
 * Início > [página-pai, se existir] > página atual.
 * Não usa dados estruturados: o Yoast já cuida disso, se estiver configurado.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fc_current   = isset( $args['current'] ) ? (string) $args['current'] : get_the_title( get_queried_object_id() );
$fc_parent_id = (int) wp_get_post_parent_id( get_queried_object_id() );
?>
<div class="fc-tools-container">
	<nav class="fc-tools-crumbs" aria-label="Você está em">
		<ol class="fc-tools-crumbs__list">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Início</a></li>
			<?php if ( $fc_parent_id ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $fc_parent_id ) ); ?>"><?php echo esc_html( get_the_title( $fc_parent_id ) ); ?></a></li>
			<?php endif; ?>
			<li><span aria-current="page"><?php echo esc_html( $fc_current ); ?></span></li>
		</ol>
	</nav>
</div>
