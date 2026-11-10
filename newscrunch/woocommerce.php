<?php
/**
 * woocommerce
 *
 * @package WordPress
 * @subpackage newscrunch
 */

get_header();

if (is_shop()) {
  echo do_shortcode('[smartslider3 slider="3"]');
  ?>
  
  <div class="fc-benefits-bar">
    <div class="fc-benefit">
      <img src="https://images.tcdn.com.br/files/588991/themes/113/img/settings/truck.png" alt="Parcelamento">
      <span><strong>Parcelamento</strong><br>Em até 6x sem juros</span>
    </div>
    <div class="fc-benefit">
      <img src="https://images.tcdn.com.br/files/588991/themes/113/img/settings/truck.png" alt="Frete Grátis">
      <span><strong>Frete Grátis</strong><br>Pedidos acima de R$189</span>
    </div>
    <div class="fc-benefit">
      <img src="https://images.tcdn.com.br/files/588991/themes/113/img/settings/truck.png" alt="Atendimento">
      <span><strong>Atendimento</strong><br>Via WhatsApp</span>
    </div>
    <div class="fc-benefit">
      <img src="https://images.tcdn.com.br/files/588991/themes/113/img/settings/truck.png" alt="Compra Segura">
      <span><strong>Compra Segura</strong><br>Ambiente criptografado</span>
    </div>
    <div class="fc-benefit">
      <img src="https://images.tcdn.com.br/files/588991/themes/113/img/settings/truck.png" alt="Cashback">
      <span><strong>Cashback</strong><br>Dinheiro de volta!</span>
    </div>
  </div>

  <?php
}
?>




<?php

//Breadcrumb Section
do_action( 'newscrunch_breadcrumbs_filter' );

//News Highlight Section
newscrunch_highlight_views('inner'); ?>

<section class="spnc-container spnc-single-post spnc-wc-product-page <?php do_action( 'spncp_single_post_layout_class_hook' );  do_action( 'spncp_check_bread_hook' ); ?>" id="content">
    <div class="spnc-row">
    <?php
    //Left Sidebar
    if (!is_product()): if(get_theme_mod('wc_sidebar_layout','right')=='left'): get_sidebar('woocommerce'); endif; endif; ?>
        <div class="<?php if (is_product()):?> spnc-col-1<?php else: ?>spnc-col-<?php echo ( !is_active_sidebar( 'woocommerce' ) ? '1' :'7' ); endif;?> wow-callback zoomIn <?php echo newscrunch_wc_stickycontent();?>">
            <?php woocommerce_content(); ?>
        </div>
    <?php 
    //Right Sidebar
    if (!is_product()): if(get_theme_mod('wc_sidebar_layout','right')=='right'): get_sidebar('woocommerce'); endif; endif; ?>
    </div>
</section>
<?php
get_footer();