<?php
/**
 * Fórmula Corpo — registro central das ferramentas.
 *
 * Uma única lista descreve todas as ferramentas, as implementadas e as
 * planejadas. Dela saem:
 * - o carregamento dos scripts de cada ferramenta (só na página dela);
 * - os cartões de "ferramentas relacionadas";
 * - o endereço de cada ferramenta, descoberto a partir da página publicada
 *   que usa o template correspondente (sem links escritos à mão e sem links
 *   quebrados: ferramenta sem página publicada fica sem link).
 *
 * Para adicionar uma ferramenta: preencha 'template' (arquivo em
 * page-templates/) e, se houver, 'calc' e 'ui' (arquivos em assets/js/).
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lista de ferramentas. 'template' vazio = ainda não implementada.
 */
function fc_tools_registry() {
	$tools = array(
		'imc'        => array(
			'title'    => 'Calculadora de IMC',
			'desc'     => 'Relacione peso e altura e veja a faixa de classificação do IMC para adultos.',
			'icon'     => 'scale',
			'template' => 'page-templates/calculadora-imc.php',
			'calc'     => 'assets/js/calc/imc.js',
			'ui'       => 'assets/js/tools/imc.js',
		),
		'composicao' => array(
			'title'    => 'Calculadora de Percentual de Gordura Corporal',
			'desc'     => 'Estime o percentual de gordura corporal a partir de medidas.',
			'icon'     => 'body',
			'template' => '',
		),
		'calorias'   => array(
			'title'    => 'Calculadora de Calorias e Macronutrientes',
			'desc'     => 'Entenda sua necessidade de energia e a divisão entre os nutrientes.',
			'icon'     => 'apple',
			'template' => '',
		),
		'atividade'  => array(
			'title'    => 'Calculadora de Gasto Calórico por Atividade Física',
			'desc'     => 'Compare o gasto de energia de diferentes atividades e durações.',
			'icon'     => 'flame',
			'template' => '',
		),
		'receitas'   => array(
			'title'    => 'Calculadora Nutricional de Receitas',
			'desc'     => 'Some os nutrientes dos ingredientes e veja o valor por porção.',
			'icon'     => 'pot',
			'template' => '',
		),
		'comparador' => array(
			'title'    => 'Comparador de Alimentos',
			'desc'     => 'Coloque dois alimentos lado a lado e compare os nutrientes.',
			'icon'     => 'compare',
			'template' => '',
		),
	);

	return apply_filters( 'fc_tools_registry', $tools );
}

/**
 * Ferramenta que usa o template informado (ou null).
 *
 * @param string $template_slug Ex.: page-templates/calculadora-imc.php
 * @return array|null Dados da ferramenta, com a chave 'key'.
 */
function fc_tools_get_tool_by_template( $template_slug ) {
	if ( '' === $template_slug ) {
		return null;
	}
	foreach ( fc_tools_registry() as $key => $tool ) {
		if ( ! empty( $tool['template'] ) && $tool['template'] === $template_slug ) {
			$tool['key'] = $key;
			return $tool;
		}
	}
	return null;
}

/**
 * Ferramenta da página atual (ou null, por exemplo na página de demonstração).
 */
function fc_tools_current_tool() {
	return fc_tools_get_tool_by_template( fc_tools_template_slug() );
}

/**
 * Endereço da página publicada que usa o template da ferramenta.
 * Retorna '' quando a ferramenta não tem template ou não há página publicada.
 * O resultado fica em cache (transient) e é renovado ao salvar páginas.
 */
function fc_tools_tool_url( $key ) {
	$registry = fc_tools_registry();
	if ( empty( $registry[ $key ]['template'] ) ) {
		return '';
	}

	$cache_key = 'fc_tools_url_' . $key;
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) && isset( $cached['url'] ) ) {
		return (string) $cached['url'];
	}

	$ids = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'has_password'           => false,
			'numberposts'            => 1,
			'fields'                 => 'ids',
			'meta_key'               => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => $registry[ $key ]['template'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	$url = $ids ? (string) get_permalink( $ids[0] ) : '';
	set_transient( $cache_key, array( 'url' => $url ), 12 * HOUR_IN_SECONDS );

	return $url;
}

/**
 * Limpa o cache dos endereços (ao criar, editar, apagar ou restaurar páginas).
 */
function fc_tools_flush_url_cache() {
	foreach ( array_keys( fc_tools_registry() ) as $key ) {
		delete_transient( 'fc_tools_url_' . $key );
	}
}
add_action( 'save_post_page', 'fc_tools_flush_url_cache' );
add_action( 'deleted_post', 'fc_tools_flush_url_cache' );
add_action( 'trashed_post', 'fc_tools_flush_url_cache' );
add_action( 'untrashed_post', 'fc_tools_flush_url_cache' );
