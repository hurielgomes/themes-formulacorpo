<?php
/**
 * The header for our theme
 *
 * @package NewsBlogger
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
	<head itemscope itemtype="http://schema.org/WebSite">
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
		
		<?php if ( ! function_exists( 'fc_tools_tracking_allowed' ) || fc_tools_tracking_allowed() ) : // Rastreadores desativados nas páginas de ferramentas. ?>
		<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17617532793"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-17617532793');
</script>
		<?php endif; ?>
	
	<?php 
if (is_singular() && pings_open(get_queried_object())) : 
    echo '<link rel="pingback" href=" '.esc_url(get_bloginfo( 'pingback_url' )).' ">';
endif;

// ATENÇÃO: Esta linha DEVE continuar aqui para carregar o CSS e JS do site!
wp_head(); 
?>	
		
		<?php if ( ! function_exists( 'fc_tools_tracking_allowed' ) || fc_tools_tracking_allowed() ) : // Rastreadores desativados nas páginas de ferramentas. ?>
		<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1875109253404733');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1427214545022386&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
		<?php endif; ?>
		
		<?php if ( ! function_exists( 'fc_tools_ads_allowed' ) || fc_tools_ads_allowed() ) : ?>
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1967197068799923"
     crossorigin="anonymous"></script>
		<?php endif; ?>
	</head>

<?php do_action('newscrunch_wide_boxed_layout');
	  wp_body_open(); ?>
	  
<div class="spnc-wrapper spnc-btn-<?php echo esc_attr(get_theme_mod('heading_layout','1'));?>" id="wrapper">
	<div id="page" class="site <?php echo esc_attr(get_theme_mod('link_animate','a_effect1')). ' '. 'custom-'.esc_attr(get_theme_mod('img_animation','i_effect1'));?>">
		<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'newsblogger' ); ?></a>
		<?php
		if ( class_exists('Newscrunch_Plus') ):
			if(get_theme_mod('progress_bar_enable',true)):echo '<div id="spnc_scroll_progressbar"></div>';endif;
			do_action('spncp_preloader_hook');
			do_action( 'spncp_header_variation' );
			if((get_theme_mod('ad_type','banner')=='popup')):do_action('newscrunch_plus_popup_ad');endif;
		else:
			do_action('newscrunch_preloader');
			if(get_theme_mod('header_layout','9') == '9') {
				get_template_part( 'partials/header/header-nav' );
			}
			else {
				do_action( 'newscrunch_header' );
			}
			
		endif;
	