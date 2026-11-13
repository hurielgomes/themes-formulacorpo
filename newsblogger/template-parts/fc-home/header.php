<?php
/**
 * Cabeçalho Fórmula Corpo: logo | menu | busca | tema.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fc_sticky = (bool) get_theme_mod( 'hide_show_sticky_header', false );
?>
<header id="fc-header" class="fc-header">
	<div class="fc-wrap fc-header__bar">
		<div class="fc-header__logo"><?php do_action( 'newscrunch_header_logo' ); ?></div>

		<div class="fc-header__panel" id="fc-header-panel">
			<nav class="fc-nav" aria-label="Menu principal"><?php fc_home_nav(); ?></nav>

			<?php if ( get_theme_mod( 'hide_show_search_icon', true ) ) : ?>
				<form class="fc-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="fc-sr" for="fc-search-input">Buscar no site</label>
					<input id="fc-search-input" type="search" name="s" placeholder="Buscar no site…" value="<?php echo esc_attr( get_search_query() ); ?>">
					<button type="submit" aria-label="Buscar">
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</button>
				</form>
			<?php endif; ?>
		</div>

		<div class="fc-header__actions">
			<?php if ( get_theme_mod( 'hide_show_dark_light_icon', true ) ) : // Mantém id/classes que o JS do Newscrunch usa. ?>
				<div class="spnc-dark-layout fc-theme-toggle">
					<a class="spnc-dark-icon" id="spnc-layout-icon" href="#" role="button" aria-label="Alternar tema claro/escuro"><i class="fas fa-solid fa-moon" aria-hidden="true"></i></a>
				</div>
			<?php endif; ?>
			<button class="fc-header__burger" type="button" aria-expanded="false" aria-controls="fc-header-panel">
				<span class="fc-burger" aria-hidden="true"></span><span class="fc-sr">Abrir menu</span>
			</button>
		</div>
	</div>
	
	<?php // Compatibilidade com o custom.js do Newscrunch (espera este elemento). ?>
    <div id="spnc-menu-open" class="spnc-menu-open" hidden></div>

</header>
<script>document.getElementById('fc-header').className += ' is-js';</script>
<?php get_template_part( 'template-parts/fc-home/promo-banner' ); ?>
