<?php
/**
 * Fórmula Corpo — cabeçalho, banner promocional e destaques da home.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function fc_home_active() {
	return true;
}

/* Local de menu opcional (se não for atribuído, o menu é montado a partir das categorias). */
add_action( 'after_setup_theme', function () {
	register_nav_menu( 'fc_header', 'Cabeçalho Fórmula Corpo' );
} );

/* CSS e JS. */
add_action( 'wp_enqueue_scripts', function () {
	$dir = NEWSBLOGGER_CHILD_TEMPLATE_DIR;
	$uri = NEWSBLOGGER_TEMPLATE_DIR_URI;
	wp_enqueue_style( 'fc-home', $uri . '/assets/css/fc-home.css', array( 'newsblogger-parent-style' ), filemtime( $dir . 'assets/css/fc-home.css' ) );
	wp_enqueue_script( 'fc-header', $uri . '/assets/js/fc-header.js', array(), filemtime( $dir . 'assets/js/fc-header.js' ), true );
}, 20 );

/* ------------------------------ menu ------------------------------ */

function fc_home_nav_map() {
	return apply_filters( 'fc_home_nav_map', array(
		'emagrecimento'   => 'Emagrecimento',
		'nutricao'        => 'Nutrição',
		'fitness-e-saude' => 'Fitness',
		'suplementos'     => 'Suplementos',
		'receitas'        => 'Receitas',
		'saude-bem-estar' => 'Bem-estar',
	) );
}

function fc_home_nav() {
	if ( has_nav_menu( 'fc_header' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'fc_header',
			'container'      => false,
			'menu_class'     => 'fc-nav__list',
			'depth'          => 2,
			'fallback_cb'    => false,
		) );
		return;
	}

	$items = array(
		array( 'label' => 'Início', 'url' => home_url( '/' ), 'current' => is_front_page() ),
	);

	foreach ( fc_home_nav_map() as $slug => $label ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term || ! $term->count ) {
			continue; // Categoria inexistente ou vazia: nada de link quebrado.
		}
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$items[] = array( 'label' => $label, 'url' => $link, 'current' => is_category( $term->term_id ) );
	}

	$about = get_page_by_path( 'sobre' );
	if ( $about && 'publish' === $about->post_status ) {
		$items[] = array( 'label' => 'Sobre', 'url' => get_permalink( $about ), 'current' => is_page( $about->ID ) );
	}

	echo '<ul class="fc-nav__list">';
	foreach ( $items as $item ) {
		printf(
			'<li class="menu-item%s"><a href="%s"%s>%s</a></li>',
			$item['current'] ? ' current-menu-item' : '',
			esc_url( $item['url'] ),
			$item['current'] ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

/* ---------------------------- destaques ---------------------------- */

/**
 * Exatamente 3 posts reais com imagem destacada: fixados primeiro, depois os mais recentes.
 *
 * @return WP_Post[] Índice 0 = principal.
 */
function fc_home_featured_posts() {
	$base = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_key'            => '_thumbnail_id', // Só posts com imagem destacada.
		'orderby'             => 'date',
		'order'               => 'DESC',
	);
	$posts  = array();
	$sticky = array_filter( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ) );

	if ( $sticky ) {
		$posts = get_posts( $base + array( 'post__in' => $sticky, 'posts_per_page' => 3 ) );
	}

	if ( count( $posts ) < 3 ) {
		$more  = get_posts( $base + array(
			'post__not_in'   => wp_list_pluck( $posts, 'ID' ),
			'posts_per_page' => 3 - count( $posts ),
		) );
		$posts = array_merge( $posts, $more );
	}

	return array_slice( $posts, 0, 3 );
}

function fc_home_primary_category( $post_id ) {
	$primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
	if ( $primary ) {
		$term = get_term( $primary, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}
	$cats = get_the_category( $post_id );
	return $cats ? $cats[0] : null;
}

function fc_home_reading_time( $post ) {
	$text  = trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	$words = $text === '' ? 0 : count( preg_split( '/\s+/u', $text ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/* Modo escuro: aplica a preferência salva antes da renderização. */
add_action( 'wp_head', function () {
	?>
	<script>
	(function () {
		try {
			if (localStorage.getItem('fc-theme') === 'dark') {
				document.documentElement.setAttribute('data-theme', 'spnc_dark');
			}
		} catch (e) {}
	})();
	</script>
	<?php
}, 1 );


/* ===== Home: helpers e assets ===== */
function fc_home_cats( array $slugs ) {
	$out = array();
	foreach ( $slugs as $slug ) {
		$cat = get_category_by_slug( $slug );
		if ( $cat ) { $out[] = $cat; }
	}
	return $out;
}

function fc_slider_ids() {
	return array_slice( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ), 0, 3 );
}

function fc_reading_time( $post_id ) {
	$text  = trim( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) ) );
	if ( '' === $text ) { return 0; }
	$words = count( preg_split( '/\s+/u', $text ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_front_page() ) { return; }
	$css = get_stylesheet_directory() . '/assets/css/fc-home-sections.css';
	wp_enqueue_style( 'fc-home-sections', get_stylesheet_directory_uri() . '/assets/css/fc-home-sections.css', array(), file_exists( $css ) ? filemtime( $css ) : null );
}, 20 );