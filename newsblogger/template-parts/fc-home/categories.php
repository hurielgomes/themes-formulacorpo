<?php
$fc_cats = array(
	'emagrecimento'   => array(
		'<svg viewBox="0 0 24 24"><path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15"/><path d="M5 19l8-8"/></svg>',
		'Estratégias, hábitos e orientações para alcançar seus objetivos.'
	),
	'receitas'        => array(
		'<svg viewBox="0 0 24 24"><path d="M12 7c-2-1.5-6-1-6 4 0 4 3 9 6 8 3 1 6-4 6-8 0-5-4-5.5-6-4z"/><path d="M12 7c0-2 1-3.5 3-4"/></svg>',
		'Alimentação equilibrada, receitas e dicas para mais energia.'
	),
	'fitness-e-saude' => array(
		'<svg viewBox="0 0 24 24"><path d="M6 7v10M18 7v10M3 10v4M21 10v4M6 12h12"/></svg>',
		'Treino, performance e bem-estar para todas as fases da vida.'
	),
	'suplementos'     => array(
		'<svg viewBox="0 0 24 24"><rect x="2.5" y="8.5" width="19" height="7" rx="3.5" transform="rotate(-45 12 12)"/><path d="M9.5 9.5l5 5"/></svg>',
		'Evidências, comparativos e orientações seguras para o dia a dia.'
	),
);
?>
<section class="fc-wrap fc-cats" aria-label="Categorias em destaque">
	<?php foreach ( $fc_cats as $slug => $d ) :
		$cat = get_category_by_slug( $slug );
		if ( ! $cat ) continue; ?>
		<a class="fc-cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>">
			<span class="fc-cat-ico" aria-hidden="true"><?php echo $d[0]; // SVG fixo, seguro ?></span>
			<h3><?php echo esc_html( $cat->name ); ?></h3>
			<p><?php echo esc_html( $d[1] ); ?></p>
			<span class="fc-link">Ver artigos <span aria-hidden="true">→</span></span>
		</a>
	<?php endforeach; ?>
</section>
