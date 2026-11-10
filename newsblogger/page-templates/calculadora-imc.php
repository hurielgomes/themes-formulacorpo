<?php
/**
 * Template Name: Calculadora de IMC
 * Template Post Type: page
 *
 * Calculadora de IMC do Fórmula Corpo.
 *
 * O cálculo acontece no navegador (assets/js/calc/imc.js). Este arquivo só
 * monta a página: nada do que a pessoa digita passa pelo servidor.
 * Metodologia e limites: ver o comentário em assets/js/calc/imc.js.
 *
 * @package NewsBlogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
fc_tools_icon_sprite();

/*
 * Textos que mudam conforme a faixa. O JavaScript escolhe o texto pelo id da
 * faixa devolvido pela função de cálculo. Os ids precisam coincidir com
 * FCCalc.imc.CLASSES (há um teste automatizado para isso).
 */
$fc_copy = array(
	'scaleEmpty'   => 'Informe peso e altura para posicionar o marcador na escala.',
	'roundingNote' => 'Valor sem arredondamento: {exact}. Como ele ainda está abaixo de {limit}, a classificação é “{class}”, mesmo que o número exibido apareça arredondado.',
	'classes'      => array(
		'low'        => array(
			'short' => 'Seu IMC está abaixo de 18,5, faixa classificada como baixo peso para adultos.',
			'long'  => 'Um IMC abaixo de 18,5 indica peso baixo para a altura. Isso pode refletir a constituição física da pessoa, mas também pode estar ligado a alimentação insuficiente, perda de peso não intencional ou a algumas condições de saúde, e o IMC sozinho não diferencia essas situações. Se o resultado vier acompanhado de cansaço, perda de peso sem explicação ou preocupação com a alimentação, vale conversar com um profissional de saúde.',
		),
		'normal'     => array(
			'short' => 'Seu IMC está na faixa de 18,5 até menos de 25, considerada normal para adultos.',
			'long'  => 'Estar nessa faixa não garante boa saúde, e estar fora dela não significa, por si só, que haja um problema. O IMC não mostra quanto do peso é gordura ou músculo, nem como a gordura se distribui no corpo. Hábitos, exames e histórico pessoal também fazem parte da avaliação.',
		),
		'overweight' => array(
			'short' => 'Seu IMC está na faixa de 25 até menos de 30, classificada como sobrepeso.',
			'long'  => 'Em estudos com grupos de pessoas, valores nessa faixa costumam estar associados a maior risco de algumas condições, como diabetes tipo 2 e doenças do coração. Para uma pessoa em particular, porém, o IMC não informa quanto do peso é gordura. Quem tem muita massa muscular, por exemplo, pode ter IMC nessa faixa sem excesso de gordura. Um profissional de saúde pode avaliar o seu caso com outras medidas.',
		),
		'obesity1'   => array(
			'short' => 'Seu IMC está na faixa de 30 até menos de 35, classificada como obesidade classe I.',
			'long'  => 'A Organização Mundial da Saúde considera a obesidade uma doença crônica, e valores de IMC a partir de 30 indicam, em grupos de pessoas, maior risco para a saúde. Ainda assim, o IMC é um indicador de triagem: ele não mostra a distribuição da gordura nem outros fatores. A avaliação individual por um profissional de saúde considera, por exemplo, a medida da cintura, exames, histórico e hábitos.',
		),
		'obesity2'   => array(
			'short' => 'Seu IMC está na faixa de 35 até menos de 40, classificada como obesidade classe II.',
			'long'  => 'Em grupos de pessoas, valores nessa faixa estão associados a um risco de saúde mais elevado do que na classe anterior. O IMC, porém, não faz diagnóstico: ele indica que vale uma avaliação mais completa. Um profissional de saúde pode considerar medidas adicionais, exames e o seu contexto individual para interpretar o resultado.',
		),
		'obesity3'   => array(
			'short' => 'Seu IMC é igual ou superior a 40, faixa classificada como obesidade classe III.',
			'long'  => 'Essa faixa não tem limite superior e, em grupos de pessoas, está associada aos maiores riscos para a saúde entre as categorias do IMC. O resultado não é um diagnóstico: é um sinal de que vale buscar uma avaliação com um profissional de saúde, que pode considerar outros indicadores e o seu contexto individual.',
		),
	),
);

// Tabela de referência (os rótulos precisam ser iguais aos de FCCalc.imc.CLASSES).
$fc_classes = array(
	array( 'Baixo peso', 'menos de 18,5' ),
	array( 'Peso normal', '18,5 até menos de 25' ),
	array( 'Sobrepeso', '25 até menos de 30' ),
	array( 'Obesidade classe I', '30 até menos de 35' ),
	array( 'Obesidade classe II', '35 até menos de 40' ),
	array( 'Obesidade classe III', '40 ou mais' ),
);

// Referências verificadas. Sem 'url' = citação sem link.
$fc_references = array(
	array(
		'title' => 'OMS — Obesity and overweight (ficha técnica)',
		'desc'  => 'Definição do IMC como peso (kg) dividido pela altura (m) ao quadrado e pontos de corte para sobrepeso e obesidade em adultos.',
		'url'   => 'https://www.who.int/news-room/fact-sheets/detail/obesity-and-overweight',
	),
	array(
		'title' => 'OMS — Obesity: preventing and managing the global epidemic (WHO Technical Report Series 894, 2000)',
		'desc'  => 'Documento técnico com a classificação do peso em adultos pelo IMC, em baixo peso, faixa normal, sobrepeso e obesidade classes I a III.',
		'url'   => '',
	),
	array(
		'title' => 'CDC — Adult BMI Categories',
		'desc'  => 'Quadro de categorias de IMC para adultos, útil para conferir os mesmos pontos de corte.',
		'url'   => 'https://www.cdc.gov/bmi/adult-calculator/bmi-categories.html',
	),
);
?>

<main id="content" class="fc-tools" data-fc-tool="imc" data-fc-copy="<?php echo esc_attr( wp_json_encode( $fc_copy ) ); ?>">

	<?php
	fc_tools_part( 'breadcrumb', array( 'current' => 'Calculadora de IMC' ) );

	fc_tools_part(
		'hero',
		array(
			'id'    => 'fc-tools-title',
			'title' => 'Calculadora de IMC',
			'lead'  => 'Calcule o índice de massa corporal a partir do peso e da altura e veja em qual faixa de classificação para adultos ele se encaixa, com uma explicação do que o resultado significa e do que ele não mostra.',
			'chips' => array(
				array( 'icon' => 'lock', 'label' => 'Cálculo no seu navegador' ),
				array( 'icon' => 'scale', 'label' => 'Faixas de classificação da OMS' ),
				array( 'icon' => 'book', 'label' => 'Resultado explicado' ),
			),
		)
	);
	?>

	<!-- Calculadora -->
	<section class="fc-tools-section" aria-labelledby="fc-imc-calc-title">
		<div class="fc-tools-container">
			<div class="fc-tools-section__head">
				<h2 id="fc-imc-calc-title" class="fc-tools-h2">Calcule seu IMC</h2>
				<p class="fc-tools-section__lead">Informe o peso em quilogramas e a altura em centímetros. O resultado aparece na página e se atualiza quando você altera os valores.</p>
			</div>

			<div class="fc-tools-notice" role="note">
				<?php fc_tools_icon( 'info' ); ?>
				<p><strong>Para adultos.</strong> A classificação desta página vale para adultos e não deve ser aplicada automaticamente a crianças, adolescentes ou gestantes.</p>
			</div>

			<div class="fc-tools-layout fc-tools-layout--calc">

				<form class="fc-tools-card fc-tools-form" id="fc-imc-form" novalidate autocomplete="off" aria-labelledby="fc-imc-form-title">
					<h3 id="fc-imc-form-title" class="fc-tools-h3"><span class="fc-tools-step" aria-hidden="true">1</span>Informe seus dados</h3>

					<p class="fc-tools-nojs" role="note">Esta calculadora precisa de JavaScript. Ative o JavaScript no navegador para calcular. As explicações abaixo continuam disponíveis.</p>

					<div class="fc-tools-fields">
						<?php
						fc_tools_part(
							'field-number',
							array(
								'id'          => 'fc-imc-peso',
								'field'       => 'peso',
								'label'       => 'Peso',
								'unit'        => 'kg',
								'unit_spoken' => 'quilogramas',
								'placeholder' => 'Ex.: 70',
								'hint'        => 'Entre 20 e 400 kg.',
								'inputmode'   => 'decimal',
							)
						);
						fc_tools_part(
							'field-number',
							array(
								'id'          => 'fc-imc-altura',
								'field'       => 'altura',
								'label'       => 'Altura',
								'unit'        => 'cm',
								'unit_spoken' => 'centímetros',
								'placeholder' => 'Ex.: 170',
								'hint'        => 'Entre 100 e 250 cm.',
								'inputmode'   => 'decimal',
							)
						);
						?>
					</div>

					<div class="fc-tools-alert" id="fc-imc-form-error" role="alert" hidden></div>

					<div class="fc-tools-actions">
						<button type="submit" class="fc-tools-btn fc-tools-btn--primary" data-fc-submit disabled><?php fc_tools_icon( 'calc' ); ?>Calcular IMC</button>
						<button type="reset" class="fc-tools-btn fc-tools-btn--ghost">Limpar</button>
					</div>

					<p class="fc-tools-status fc-tools-sr" id="fc-imc-status" role="status" aria-live="polite"></p>

					<p class="fc-tools-privacy">
						<?php fc_tools_icon( 'lock' ); ?>
						<span>Os cálculos acontecem no seu navegador. Peso, altura e resultado não são enviados a servidores nem a serviços externos, não aparecem no endereço da página e não ficam salvos.</span>
					</p>
				</form>

				<div class="fc-tools-card fc-tools-results fc-tools-resultcard" id="fc-imc-results" aria-labelledby="fc-imc-results-title">
					<h3 id="fc-imc-results-title" class="fc-tools-h3"><span class="fc-tools-step" aria-hidden="true">2</span>Seu resultado</h3>

					<div class="fc-tools-empty" data-fc-empty>
						<span class="fc-tools-stat__icon"><?php fc_tools_icon( 'scale' ); ?></span>
						<p>Informe seu peso e sua altura e use o botão “Calcular IMC” para ver o resultado e a faixa em que ele se encaixa.</p>
					</div>

					<div class="fc-tools-result" data-fc-result hidden>
						<p class="fc-tools-result__label">Seu IMC</p>
						<p class="fc-tools-bigvalue"><span class="fc-tools-bigvalue__num fc-tools-num" data-fc-value></span> <span class="fc-tools-bigvalue__unit">kg/m²</span></p>
						<p class="fc-tools-pill fc-tools-pill--class"><span class="fc-tools-dot" aria-hidden="true"></span><span class="fc-tools-sr">Classificação: </span><span data-fc-class></span></p>
						<p class="fc-tools-result__short" data-fc-short></p>
						<p class="fc-tools-callout" data-fc-note hidden></p>

						<div class="fc-tools-meaning">
							<h4 class="fc-tools-meaning__title">O que isso significa</h4>
							<p data-fc-long></p>
							<p class="fc-tools-meaning__more">O IMC é um indicador de triagem e não faz diagnóstico. Veja as <a class="fc-tools-link" href="#fc-imc-limits">limitações do indicador</a>.</p>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- Escala de classificação -->
	<section class="fc-tools-section fc-tools-section--tint" aria-labelledby="fc-imc-scale-title">
		<div class="fc-tools-container">
			<figure class="fc-tools-card fc-tools-chart" aria-labelledby="fc-imc-scale-title" aria-describedby="fc-imc-scale-desc">
				<figcaption class="fc-tools-chart__cap">
					<h2 id="fc-imc-scale-title" class="fc-tools-h2 fc-tools-h2--card">Faixas de classificação do IMC para adultos</h2>
					<p id="fc-imc-scale-desc" class="fc-tools-chart__desc">As seis faixas, do menor para o maior IMC. Depois de calcular, a faixa do seu resultado fica destacada e um marcador indica a posição. A faixa de obesidade classe III não tem limite superior.</p>
				</figcaption>

				<div class="fc-tools-bmi" id="fc-imc-scale"></div>

				<details class="fc-tools-details">
					<summary>Ver as faixas em tabela</summary>
					<div class="fc-tools-tablewrap">
						<table class="fc-tools-table">
							<caption class="fc-tools-sr">Faixas de classificação do IMC para adultos, em kg/m²</caption>
							<thead><tr><th scope="col">Classificação</th><th scope="col">IMC (kg/m²)</th></tr></thead>
							<tbody>
								<?php foreach ( $fc_classes as $fc_row ) : ?>
									<tr><th scope="row"><?php echo esc_html( $fc_row[0] ); ?></th><td><?php echo esc_html( $fc_row[1] ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</details>
			</figure>
		</div>
	</section>

	<!-- O que o IMC mostra e não mostra -->
	<section class="fc-tools-section" id="fc-imc-limits" aria-labelledby="fc-imc-limits-title">
		<div class="fc-tools-container">
			<div class="fc-tools-explain">
				<div class="fc-tools-explain__main">
					<h2 id="fc-imc-limits-title" class="fc-tools-h2">O que o IMC mostra e o que ele não mostra</h2>
					<p class="fc-tools-section__lead">O índice de massa corporal relaciona o peso à altura. Ele foi criado como uma medida simples para acompanhar grupos de pessoas e funciona como triagem: indica se o peso está baixo, na faixa normal ou elevado para a altura, sem dizer de que ele é feito.</p>

					<div class="fc-tools-blocks">
						<div class="fc-tools-block">
							<h3 class="fc-tools-block__title">Não mede a gordura corporal</h3>
							<p>O IMC não mede diretamente a gordura do corpo. Duas pessoas com o mesmo IMC podem ter quantidades muito diferentes de gordura.</p>
						</div>
						<div class="fc-tools-block">
							<h3 class="fc-tools-block__title">Não separa músculo de gordura</h3>
							<p>O indicador não diferencia massa muscular de massa gorda. Atletas e pessoas com muita massa muscular podem ter IMC elevado sem excesso de gordura.</p>
						</div>
						<div class="fc-tools-block">
							<h3 class="fc-tools-block__title">Depende do contexto individual</h3>
							<p>A interpretação muda com idade, condições de saúde, hábitos e histórico. A medida da cintura e exames complementam a avaliação feita por um profissional.</p>
						</div>
					</div>
				</div>

				<aside class="fc-tools-explain__side" aria-label="Aviso sobre o uso do resultado">
					<div class="fc-tools-warning" role="note">
						<div class="fc-tools-warning__head"><?php fc_tools_icon( 'alert' ); ?><h3 class="fc-tools-warning__title">Limitações importantes</h3></div>
						<p>O IMC é um indicador de triagem. Ele não faz diagnóstico, não recomenda tratamento e não deve ser usado sozinho para avaliar a saúde de uma pessoa.</p>
						<p>A classificação desta página é para adultos. Em crianças e adolescentes, o IMC é interpretado por idade e sexo, em curvas de crescimento. Na gestação, ele não é interpretado por estas faixas. Nesses casos, procure orientação profissional.</p>
					</div>
				</aside>
			</div>
		</div>
	</section>

	<!-- Como é calculado -->
	<section class="fc-tools-section fc-tools-section--tint" aria-labelledby="fc-imc-method-title">
		<div class="fc-tools-container">
			<div class="fc-tools-section__head">
				<h2 id="fc-imc-method-title" class="fc-tools-h2">Como o IMC é calculado</h2>
				<p class="fc-tools-section__lead">A fórmula é a mesma usada pela Organização Mundial da Saúde: o peso em quilogramas dividido pelo quadrado da altura em metros.</p>
			</div>

			<div class="fc-tools-method">
				<div class="fc-tools-card">
					<p class="fc-tools-formula"><strong>IMC</strong> = peso (kg) ÷ [altura (m) × altura (m)]</p>
					<p class="fc-tools-method__example">Exemplo: uma pessoa com 80 kg e 1,80 m tem IMC = 80 ÷ (1,80 × 1,80) = 80 ÷ 3,24, ou cerca de 24,69 kg/m².</p>
					<p>Na calculadora, a altura é digitada em centímetros e a conta usa frações exatas, sem as pequenas imprecisões dos números decimais do computador. O resultado aparece com duas casas decimais, mas a faixa é decidida pelo valor exato, sem arredondamento. Por isso, em casos raros, o número exibido pode coincidir com o limite de uma faixa enquanto o valor exato ainda está logo abaixo dele. Quando isso acontece, a página avisa.</p>
					<p>Os limites de entrada (peso de 20 a 400 kg e altura de 100 a 250 cm) servem apenas para evitar erros de digitação. Eles não têm relação com a classificação do IMC.</p>
				</div>

				<div class="fc-tools-card">
					<h3 class="fc-tools-h3">Classificação para adultos</h3>
					<div class="fc-tools-tablewrap">
						<table class="fc-tools-table">
							<caption class="fc-tools-sr">Classificação do IMC para adultos segundo a OMS</caption>
							<thead><tr><th scope="col">Classificação</th><th scope="col">IMC (kg/m²)</th></tr></thead>
							<tbody>
								<?php foreach ( $fc_classes as $fc_row ) : ?>
									<tr><th scope="row"><?php echo esc_html( $fc_row[0] ); ?></th><td><?php echo esc_html( $fc_row[1] ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<p class="fc-tools-footnote">Cada faixa inclui o limite inferior e exclui o superior.</p>
				</div>
			</div>

			<div class="fc-tools-sources fc-tools-sources--page">
				<h3 class="fc-tools-sources__title">Referências</h3>
				<ul class="fc-tools-sources__list">
					<?php foreach ( $fc_references as $fc_ref ) : ?>
						<li>
							<?php if ( ! empty( $fc_ref['url'] ) ) : ?>
								<a class="fc-tools-link" href="<?php echo esc_url( $fc_ref['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $fc_ref['title'] ); ?></a>
							<?php else : ?>
								<span class="fc-tools-sources__name"><?php echo esc_html( $fc_ref['title'] ); ?></span>
							<?php endif; ?>
							<span class="fc-tools-sources__desc"><?php echo esc_html( $fc_ref['desc'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>

	<?php
	fc_tools_part(
		'related',
		array(
			'id'      => 'fc-imc-related-title',
			'heading' => 'Continue explorando',
			'lead'    => 'O IMC é apenas um ponto de partida. As próximas ferramentas ajudam a olhar para outros aspectos da composição corporal e da alimentação.',
			'keys'    => array( 'composicao', 'calorias' ),
		)
	);
	?>

</main>

<?php
get_footer();
