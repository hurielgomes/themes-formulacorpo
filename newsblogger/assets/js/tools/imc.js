/**
 * Fórmula Corpo — Calculadora de IMC: interface.
 *
 * Este arquivo só liga o formulário (FCTools.mountCalculator) à função pura
 * FCCalc.imc.calculate() e escreve o resultado na página. Fórmulas e limites
 * ficam em assets/js/calc/imc.js. Nada é enviado, salvo ou colocado na URL.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-fc-tool="imc"]');
	if (!root) { return; }

	function $(selector) { return root.querySelector(selector); }

	var formError = $('#fc-imc-form-error');

	/** Nunca falha em silêncio: avisa a pessoa e registra o motivo no console. */
	function stop(reason) {
		if (window.console && window.console.warn) {
			window.console.warn('Calculadora de IMC não iniciou: ' + reason);
		}
		if (formError) {
			formError.textContent = 'Não foi possível carregar a calculadora. Atualize a página e tente novamente.';
			formError.hidden = false;
		}
	}

	var Calc = window.FCCalc && window.FCCalc.imc;
	var Tools = window.FCTools;

	if (!Calc) { stop('os arquivos assets/js/calc/calc-common.js e assets/js/calc/imc.js não foram carregados.'); return; }
	if (!Tools || typeof Tools.mountCalculator !== 'function' || typeof Tools.bmi !== 'function') {
		stop('assets/js/fc-tools.js não foi carregado ou está desatualizado (falta FCTools.mountCalculator).');
		return;
	}

	var form = $('#fc-imc-form');
	var fields = { peso: $('#fc-imc-peso'), altura: $('#fc-imc-altura') };
	var submit = $('[data-fc-submit]');
	var status = $('#fc-imc-status');
	var card = $('#fc-imc-results');
	var emptyBox = $('[data-fc-empty]');
	var resultBox = $('[data-fc-result]');
	var valueNode = $('[data-fc-value]');
	var classNode = $('[data-fc-class]');
	var shortNode = $('[data-fc-short]');
	var longNode = $('[data-fc-long]');
	var noteNode = $('[data-fc-note]');
	var scaleHost = $('#fc-imc-scale');

	if (!form || !fields.peso || !fields.altura || !submit || !card || !resultBox || !emptyBox || !scaleHost) {
		stop('faltam elementos na página (confira os arquivos em template-parts/fc-tools/).');
		return;
	}

	var copy = {};
	try { copy = JSON.parse(root.getAttribute('data-fc-copy')) || {}; } catch (e) { copy = {}; }
	var texts = copy.classes || {};

	function comma(text) { return String(text).replace('.', ','); }
	function setStatus(text) { if (status) { status.textContent = text; } }

	if (!Calc.supported) {
		if (formError) {
			formError.textContent = 'Este navegador é antigo demais para a calculadora. Atualize o navegador e tente novamente.';
			formError.hidden = false;
		}
		return;
	}

	/* ---- escala de classificação (faixas vêm da função de cálculo) ---- */

	// Limites usados só para posicionar o marcador dentro da faixa (não classificam).
	var bounds = [[10, 18.5], [18.5, 25], [25, 30], [30, 35], [35, 40], [40, null]];
	var scale = Tools.bmi(scaleHost, {
		hereLabel: 'Seu resultado',
		summary: copy.scaleEmpty || '',
		segments: Calc.CLASSES.map(function (c, i) {
			return {
				label: c.label,
				range: c.rangeText,
				min: bounds[i][0],
				max: bounds[i][1],
				open: bounds[i][1] === null
			};
		})
	});

	/* ---- textos de erro (os limites vêm de LIMITS, fonte única) ---- */

	var L = Calc.LIMITS;
	var hints = {
		peso: 'Entre ' + comma(L.peso.min) + ' e ' + comma(L.peso.max) + ' ' + L.peso.unit + '.',
		altura: 'Entre ' + comma(L.altura.min) + ' e ' + comma(L.altura.max) + ' ' + L.altura.unit + '.'
	};

	function message(error) {
		var isHeight = error.field === 'altura';
		switch (error.code) {
			case 'required':
				return isHeight ? 'Informe a altura em centímetros.' : 'Informe o peso em quilogramas.';
			case 'not_a_number':
				return 'Use apenas números, por exemplo ' + (isHeight ? '170' : '70 ou 70,5') + '.';
			case 'not_positive':
				return 'Informe um valor maior que zero.';
			case 'too_long':
				return 'Número muito longo. Use até 9 dígitos antes da vírgula e 6 depois.';
			case 'below_min':
			case 'above_max':
				return (isHeight ? 'A altura' : 'O peso') + ' deve estar entre ' + comma(error.min) + ' e ' +
					comma(error.max) + ' ' + error.unit + '.';
			case 'height_in_meters':
				return 'Informe a altura em centímetros, por exemplo 180 (e não 1,80).';
			case 'implausible_bmi':
				return 'Peso e altura resultam em um IMC fora do intervalo plausível (' + comma(error.min) + ' a ' +
					comma(error.max) + '). Confira os valores digitados.';
			default:
				return 'Não foi possível calcular com esses valores.';
		}
	}

	/* ---- resultado ---- */

	function showResult(result, source) {
		var cls = result.classification;
		var text = texts[cls.id] || {};

		emptyBox.hidden = true;
		resultBox.hidden = false;
		card.setAttribute('data-class', cls.id);

		valueNode.textContent = result.bmi.display;
		classNode.textContent = cls.label;
		shortNode.textContent = text.short || '';
		longNode.textContent = text.long || '';

		if (noteNode) {
			if (result.rounding.displayReachesLimit && copy.roundingNote) {
				noteNode.textContent = copy.roundingNote
					.replace('{exact}', result.bmi.exactTruncated)
					.replace('{limit}', result.rounding.limit)
					.replace('{class}', cls.label);
				noteNode.hidden = false;
			} else {
				noteNode.textContent = '';
				noteNode.hidden = true;
			}
		}

		scale.update({
			value: result.bmi.value,
			activeIndex: cls.index,
			summary: 'IMC de ' + result.bmi.display + ' kg/m², na faixa “' + cls.label + '” (' + cls.rangeText + ').'
		});

		setStatus('IMC ' + result.bmi.display + ' quilogramas por metro quadrado. Classificação: ' + cls.label + '.');

		if (source === 'submit' && card.scrollIntoView) {
			var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			card.scrollIntoView({ block: 'nearest', behavior: calm ? 'auto' : 'smooth' });
		}
	}

	function clearResult() {
		resultBox.hidden = true;
		emptyBox.hidden = false;
		card.removeAttribute('data-class');
		if (noteNode) { noteNode.textContent = ''; noteNode.hidden = true; }
		scale.update({ summary: copy.scaleEmpty || '' });
		setStatus('');
	}

	Tools.mountCalculator({
		form: form,
		fields: fields,
		submit: submit,
		formError: formError,
		hints: hints,
		calculate: function (values) { return Calc.calculate(values); },
		message: message,
		onResult: showResult,
		onInvalid: clearResult,
		onReset: clearResult
	});
}());
