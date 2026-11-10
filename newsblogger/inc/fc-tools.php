<?php
/**
 * Fórmula Corpo — Ferramentas e Calculadoras (base de integração).
 *
 * Carregado por functions.php. Mantém tudo do módulo isolado:
 * - carrega CSS/JS somente nas páginas que usam um template de ferramentas;
 * - ajusta o H1 duplicado do logo apenas nessas páginas;
 * - impede rastreadores e (por padrão) anúncios nessas páginas;
 * - fornece ícones SVG e as listas de ferramentas relacionadas e fontes.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Registro central das ferramentas (inc/fc-tools-registry.php).
if ( file_exists( __DIR__ . '/fc-tools-registry.php' ) ) {
	require_once __DIR__ . '/fc-tools-registry.php';
}

/**
 * Prefixo dos templates de ferramentas. Qualquer arquivo em page-templates/
 * cujo nome comece assim é tratado como página de ferramenta (CSS/JS próprios,
 * sem rastreadores). Não é preciso registrar templates novos aqui.
 */
function fc_tools_template_prefix() {
	return 'page-templates/calculadora-';
}

/**
 * Template usado pela página atual ('' quando não há).
 */
function fc_tools_template_slug() {
	if ( ! is_singular( 'page' ) ) {
		return '';
	}
	return (string) get_page_template_slug( get_queried_object_id() );
}

/**
 * A página atual usa um template de ferramentas?
 */
function fc_tools_is_tool_page() {
	$slug   = fc_tools_template_slug();
	$prefix = fc_tools_template_prefix();
	return '' !== $slug && 0 === strpos( $slug, $prefix );
}

/**
 * PRIVACIDADE — rastreadores (Meta Pixel, Google tag, etc.) nunca carregam nas
 * páginas de ferramentas. O header.php do tema consulta esta função.
 */
function fc_tools_tracking_allowed() {
	return ! fc_tools_is_tool_page();
}

/**
 * Publicidade (AdSense) nas páginas de ferramentas: DESLIGADA por padrão.
 * Para permitir depois de conferir a configuração no painel do AdSense:
 *   add_filter( 'fc_tools_ads_enabled', '__return_true' );
 */
function fc_tools_ads_allowed() {
	if ( ! fc_tools_is_tool_page() ) {
		return true;
	}
	return (bool) apply_filters( 'fc_tools_ads_enabled', false );
}

/**
 * Trechos que identificam scripts de rastreamento/publicidade. Usados pela
 * rede de segurança abaixo, que remove da saída das páginas de ferramentas
 * qualquer <script> ou <noscript> injetado por outro caminho (plugins,
 * snippets, Elementor, etc.).
 *
 * @param bool $include_ads Incluir também os trechos de publicidade.
 * @return string[] Fragmentos de expressão regular (PCRE).
 */
function fc_tools_blocked_patterns( $include_ads ) {
	$patterns = array(
		'connect\.facebook\.net',
		'facebook\.com/tr',
		'googletagmanager\.com',
		'google-analytics\.com',
		'googleadservices\.com',
		'clarity\.ms',
		'static\.hotjar\.com',
		'analytics\.tiktok\.com',
		'snap\.licdn\.com',
		'ads-twitter\.com',
		'bat\.bing\.com',
		'\bfbq\s*\(',
		'\bgtag\s*\(',
		'\bdataLayer\b',
		'\b_gaq\b',
		'\bttq\.',
		'\bclarity\s*\(',
	);

	if ( $include_ads ) {
		$patterns[] = 'googlesyndication\.com';
		$patterns[] = 'doubleclick\.net';
		$patterns[] = '\badsbygoogle\b';
	}

	return $patterns;
}

/**
 * Filtro de saída (rede de segurança): remove scripts de rastreamento.
 */
function fc_tools_filter_output( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	$pattern = '#(' . implode( '|', fc_tools_blocked_patterns( ! fc_tools_ads_allowed() ) ) . ')#i';
	$remove  = function ( $match ) use ( $pattern ) {
		return preg_match( $pattern, $match[0] ) ? '' : $match[0];
	};

	$clean = preg_replace_callback( '#<script\b[^>]*>.*?</script\s*>#is', $remove, $html );
	if ( null === $clean ) {
		return $html;
	}

	$clean_noscript = preg_replace_callback( '#<noscript\b[^>]*>.*?</noscript\s*>#is', $remove, $clean );
	return null === $clean_noscript ? $clean : $clean_noscript;
}

function fc_tools_start_output_filter() {
	if ( fc_tools_is_tool_page() ) {
		ob_start( 'fc_tools_filter_output' );
	}
}
add_action( 'template_redirect', 'fc_tools_start_output_filter', 0 );

/**
 * Enfileira um script do tema filho (com versão pela data do arquivo).
 */
function fc_tools_enqueue_script( $handle, $relative_path, $deps = array() ) {
	$dir = trailingslashit( get_stylesheet_directory() );
	$uri = trailingslashit( get_stylesheet_directory_uri() );

	wp_enqueue_script(
		$handle,
		$uri . $relative_path,
		$deps,
		file_exists( $dir . $relative_path ) ? filemtime( $dir . $relative_path ) : null,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

/**
 * CSS e JS: somente nas páginas de ferramentas. Cada ferramenta do registro
 * carrega também o próprio módulo de cálculo e de interface.
 */
function fc_tools_enqueue_assets() {
	if ( ! fc_tools_is_tool_page() ) {
		return;
	}

	$dir = trailingslashit( get_stylesheet_directory() );
	$uri = trailingslashit( get_stylesheet_directory_uri() );

	// Depende dos estilos do tema (quando registrados) para carregar depois deles.
	$deps = array();
	foreach ( array( 'newsblogger-parent-style', 'newsblogger-dark-style' ) as $handle ) {
		if ( wp_style_is( $handle, 'registered' ) ) {
			$deps[] = $handle;
		}
	}

	$css = 'assets/css/fc-tools.css';
	wp_enqueue_style(
		'fc-tools',
		$uri . $css,
		$deps,
		file_exists( $dir . $css ) ? filemtime( $dir . $css ) : null
	);

	$tool = function_exists( 'fc_tools_current_tool' ) ? fc_tools_current_tool() : null;

	// 1) cálculo (funções puras), 2) núcleo da interface, 3) interface da ferramenta.
	if ( $tool && ! empty( $tool['calc'] ) ) {
		fc_tools_enqueue_script( 'fc-calc-common', 'assets/js/calc/calc-common.js' );
		fc_tools_enqueue_script( 'fc-calc-' . $tool['key'], $tool['calc'], array( 'fc-calc-common' ) );
	}

	fc_tools_enqueue_script( 'fc-tools', 'assets/js/fc-tools.js' );

	if ( $tool && ! empty( $tool['ui'] ) ) {
		$ui_deps = array( 'fc-tools' );
		if ( ! empty( $tool['calc'] ) ) {
			$ui_deps[] = 'fc-calc-' . $tool['key'];
		}
		fc_tools_enqueue_script( 'fc-tools-' . $tool['key'], $tool['ui'], $ui_deps );
	}
}
add_action( 'wp_enqueue_scripts', 'fc_tools_enqueue_assets', 20 );

/**
 * Carrega um componente de template-parts/fc-tools/ passando argumentos
 * (disponíveis no componente como $args).
 *
 * Se o arquivo não existir, não falha em silêncio: quem administra o site vê um
 * aviso na própria página; os demais visitantes recebem só um comentário HTML.
 */
function fc_tools_part( $name, $args = array() ) {
	$slug = 'template-parts/fc-tools/' . $name;

	if ( '' === locate_template( $slug . '.php' ) ) {
		if ( current_user_can( 'edit_theme_options' ) ) {
			printf(
				'<div class="fc-tools-container"><p class="fc-tools-alert" role="alert">Componente ausente: <code>%s.php</code> não foi encontrado no tema ativo (pasta <code>wp-content/themes/%s/template-parts/fc-tools/</code>).</p></div>',
				esc_html( $name ),
				esc_html( get_stylesheet() )
			);
		} else {
			echo '<!-- fc-tools: componente ausente: ' . esc_html( $name ) . ' -->';
		}
		return;
	}

	get_template_part( $slug, null, $args );
}

/**
 * O tema imprime um <h1 class="site-title"> (texto oculto) dentro do logo em
 * todas as páginas. Nas páginas de ferramentas, o único H1 deve ser o título
 * da página; aqui o H1 do logo vira <div>, sem alterar o restante do markup.
 */
function fc_tools_logo_buffer_start() {
	ob_start();
}

function fc_tools_logo_buffer_end() {
	$html = (string) ob_get_clean();
	$html = preg_replace( '/<h1(\s[^>]*)?>/i', '<div$1>', $html );
	$html = preg_replace( '/<\/h1>/i', '</div>', $html );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML já gerado e escapado pelo tema.
}

function fc_tools_prepare_page() {
	if ( ! fc_tools_is_tool_page() ) {
		return;
	}
	add_action( 'newscrunch_header_logo', 'fc_tools_logo_buffer_start', 1 );
	add_action( 'newscrunch_header_logo', 'fc_tools_logo_buffer_end', 99 );
}
add_action( 'wp', 'fc_tools_prepare_page' );

/**
 * Ferramentas relacionadas, a partir do registro central. O endereço é o da
 * página publicada que usa o template da ferramenta; sem página publicada,
 * 'url' fica vazio e o cartão aparece como "Em breve", sem link.
 */
function fc_tools_get_related_tools() {
	$tools = array();

	foreach ( fc_tools_registry() as $key => $tool ) {
		$tools[ $key ] = array(
			'title' => $tool['title'],
			'desc'  => $tool['desc'],
			'icon'  => $tool['icon'],
			'url'   => fc_tools_tool_url( $key ),
		);
	}

	return apply_filters( 'fc_tools_related_tools', $tools );
}

/**
 * Fontes e metodologia exibidas na seção de explicação.
 * Mesmo comportamento: 'url' vazio = item desativado e identificado.
 */
function fc_tools_get_sources() {
	$sources = array(
		array(
			'title' => 'Como calculamos',
			'desc'  => 'Fórmulas, unidades e arredondamentos usados em cada ferramenta.',
			'url'   => '',
		),
		array(
			'title' => 'Referências científicas',
			'desc'  => 'Artigos e diretrizes que embasam os cálculos.',
			'url'   => '',
		),
		array(
			'title' => 'Base de dados de alimentos',
			'desc'  => 'De onde vêm os valores nutricionais dos alimentos.',
			'url'   => '',
		),
	);

	return apply_filters( 'fc_tools_sources', $sources );
}

/**
 * Imprime um ícone do sprite (ver fc_tools_icon_sprite()).
 */
function fc_tools_icon( $name, $class = '' ) {
	printf(
		'<svg class="fc-tools-icon %1$s" aria-hidden="true" focusable="false"><use href="#fc-i-%2$s"></use></svg>',
		esc_attr( $class ),
		esc_attr( $name )
	);
}

/**
 * Sprite SVG (imprimido uma vez por página, pelo template).
 */
function fc_tools_icon_sprite() {
	?>
	<svg class="fc-tools-sprite" width="0" height="0" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
		<defs>
			<symbol id="fc-i-scale" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="3.5" width="17" height="17" rx="4"/><path d="M8 10.5a5.2 5.2 0 0 1 8 0"/><path d="M12 15l1.9-2.7"/></symbol>
			<symbol id="fc-i-flame" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3c.5 3.2 4.5 5.2 4.5 9.5a4.5 4.5 0 0 1-9 0c0-2 1-3.4 2.1-4.5.1 1.4.8 2.3 1.7 2.6C11.1 8.4 11.3 5.4 12 3z"/></symbol>
			<symbol id="fc-i-bolt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M13 3 5.5 13.2h5.2L10 21l8-10.4h-5.4L13 3z"/></symbol>
			<symbol id="fc-i-pie" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 3.5V12l6 6"/></symbol>
			<symbol id="fc-i-walk" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="4.5" r="1.7"/><path d="M13 8l-2.5 3.5 2.5 2.2.8 5.8"/><path d="M13 8l2.7 3 2.3.6"/><path d="M12.9 13.7l-3.2 5.6"/><path d="M10.5 11.5l-3 1"/></symbol>
			<symbol id="fc-i-run" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="15" cy="4.5" r="1.7"/><path d="M13.5 8l-3.2 3.3 3.2 2.4-1 5.6"/><path d="M13.5 8l3.3 2.7 3-.2"/><path d="M13.5 13.7l3.8 1.3 1.2 3.5"/><path d="M10.3 11.3l-3.3.4"/></symbol>
			<symbol id="fc-i-bike" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="16" r="3.5"/><circle cx="18" cy="16" r="3.5"/><path d="M6 16l3.5-7h5.5l3 7"/><path d="M9.5 9L12 16"/><path d="M8 7h3M15 9l-1-2.5h2"/></symbol>
			<symbol id="fc-i-dumbbell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 7.5v9M17.5 7.5v9M3.5 10v4M20.5 10v4M6.5 12h11"/></symbol>
			<symbol id="fc-i-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 11v5.2"/><path d="M12 7.9h.01"/></symbol>
			<symbol id="fc-i-alert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4 21 19.5H3L12 4z"/><path d="M12 10v4"/><path d="M12 16.8h.01"/></symbol>
			<symbol id="fc-i-calc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3.5" width="14" height="17" rx="3"/><path d="M8.5 8h7"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01M8.5 16h.01M12 16h.01M15.5 16h.01" stroke-width="2.4"/></symbol>
			<symbol id="fc-i-body" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2"/><path d="M8 21v-6.5L6.5 9.5A1.5 1.5 0 0 1 8 8h8a1.5 1.5 0 0 1 1.5 1.5L16 14.5V21"/></symbol>
			<symbol id="fc-i-apple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7.5c-1.5-1.2-5-1.2-6 2.2-1 3.4 1.2 9.8 4 9.8 1 0 1.2-.5 2-.5s1 .5 2 .5c2.8 0 5-6.4 4-9.8-1-3.4-4.5-3.4-6-2.2z"/><path d="M12 7.5c0-2 1-3.5 3-4"/></symbol>
			<symbol id="fc-i-pot" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 11h14v5a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4v-5z"/><path d="M3.5 11h17"/><path d="M9 7.5c0-1 .8-1.5.8-2.5M13 7.5c0-1 .8-1.5.8-2.5"/></symbol>
			<symbol id="fc-i-compare" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h13l-3-3"/><path d="M20 16H7l3 3"/></symbol>
			<symbol id="fc-i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></symbol>
			<symbol id="fc-i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></symbol>
			<symbol id="fc-i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10.5" width="14" height="9.5" rx="2.5"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/></symbol>
			<symbol id="fc-i-book" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 5.5A2 2 0 0 1 7 3.5h11v15H7a2 2 0 0 0-2 2v-15z"/><path d="M5 20.5h13"/></symbol>
		</defs>
	</svg>
	<?php
}
