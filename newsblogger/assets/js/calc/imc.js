/**
 * Fórmula Corpo — Calculadora de IMC (função pura, sem DOM, sem rede).
 *
 * METODOLOGIA
 * -----------
 * IMC = peso (kg) / (altura (m) × altura (m)).
 * A altura é digitada em centímetros; com a altura em cm, a conta é feita como
 *   IMC = peso × 10.000 / (altura em cm)²
 * usando frações exatas (ver calc-common.js), sem ponto flutuante.
 *
 * CLASSIFICAÇÃO (adultos) — pontos de corte da OMS:
 *   abaixo de 18,5            baixo peso
 *   de 18,5 até menos de 25   faixa de peso normal
 *   de 25 até menos de 30     sobrepeso
 *   de 30 até menos de 35     obesidade classe I
 *   de 35 até menos de 40     obesidade classe II
 *   40 ou mais                obesidade classe III
 * Cada faixa inclui o limite inferior e exclui o superior. A classificação é
 * decidida pelo valor EXATO do IMC. O número exibido é arredondado para duas
 * casas decimais (metade para cima) e pode, por isso, coincidir com um limite
 * mesmo quando o valor exato está logo abaixo dele (ex.: 24,9969 aparece como
 * 25,00 e continua em "peso normal"). Esse caso é sinalizado em `rounding`.
 *
 * REFERÊNCIAS
 *  - OMS. Obesity and overweight (ficha técnica): IMC = peso (kg) / altura² (m²);
 *    adultos com IMC ≥ 25 têm sobrepeso e IMC ≥ 30, obesidade.
 *    https://www.who.int/news-room/fact-sheets/detail/obesity-and-overweight
 *  - OMS. Obesity: preventing and managing the global epidemic. Report of a WHO
 *    Consultation. WHO Technical Report Series 894. Genebra, 2000
 *    (classificação em baixo peso, normal, sobrepeso e obesidade classes I-III).
 *
 * LIMITES TÉCNICOS DE ENTRADA (evitam erros de digitação; NÃO são limites
 * clínicos e não têm relação com a classificação):
 *   peso   de 20 a 400 kg
 *   altura de 100 a 250 cm
 *   IMC resultante de 8 a 100 (combinações fora disso são consideradas
 *   improváveis e não são classificadas)
 *
 * ESCOPO: a classificação é para adultos. A função não tem como saber a idade
 * nem a situação (gestação) da pessoa; a interface avisa que não se aplica a
 * crianças, adolescentes e gestantes.
 */
(function (root, factory) {
	'use strict';
	var common = (typeof module === 'object' && module.exports && typeof require === 'function')
		? require('./calc-common.js')
		: (root && root.FCCalc && root.FCCalc.common);
	var api = factory(common);
	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	}
	if (root) {
		root.FCCalc = root.FCCalc || {};
		root.FCCalc.imc = api;
	}
}(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this), function (common) {
	'use strict';

	var supported = !!(common && common.supported);

	/** Limites técnicos de entrada (texto decimal com ponto). */
	var LIMITS = {
		peso: { min: '20', max: '400', unit: 'kg' },
		altura: { min: '100', max: '250', unit: 'cm' },
		imc: { min: '8', max: '100' }
	};

	/** Altura em metros digitada por engano (ex.: 1,80) recebe orientação própria. */
	var HEIGHT_IN_METERS = { min: '0.5', max: '3' };

	/**
	 * Faixas de classificação, em ordem crescente. `min` é inclusivo, `max` é
	 * exclusivo; null significa sem limite naquele lado.
	 */
	var CLASSES = [
		{ id: 'low', label: 'Baixo peso', min: null, max: '18.5', rangeText: 'menos de 18,5' },
		{ id: 'normal', label: 'Peso normal', min: '18.5', max: '25', rangeText: '18,5 até menos de 25' },
		{ id: 'overweight', label: 'Sobrepeso', min: '25', max: '30', rangeText: '25 até menos de 30' },
		{ id: 'obesity1', label: 'Obesidade classe I', min: '30', max: '35', rangeText: '30 até menos de 35' },
		{ id: 'obesity2', label: 'Obesidade classe II', min: '35', max: '40', rangeText: '35 até menos de 40' },
		{ id: 'obesity3', label: 'Obesidade classe III', min: '40', max: null, rangeText: '40 ou mais' }
	];

	function ratioOf(text) {
		return common.toRatio(common.parseDecimal(text));
	}

	/** Valida um campo e devolve { ok, parsed } ou { ok:false, error }. */
	function readField(name, raw) {
		var parsed = common.parseDecimal(raw);
		var limit = LIMITS[name];

		if (!parsed.ok) {
			return { ok: false, error: { field: name, code: parsed.code } };
		}

		var value = common.toRatio(parsed);
		var below = common.compareRatio(value, ratioOf(limit.min)) < 0;
		var above = common.compareRatio(value, ratioOf(limit.max)) > 0;

		if (below || above) {
			var code = below ? 'below_min' : 'above_max';
			if (name === 'altura' && below &&
				common.compareRatio(value, ratioOf(HEIGHT_IN_METERS.min)) >= 0 &&
				common.compareRatio(value, ratioOf(HEIGHT_IN_METERS.max)) <= 0) {
				code = 'height_in_meters';
			}
			return {
				ok: false,
				error: { field: name, code: code, min: limit.min, max: limit.max, unit: limit.unit }
			};
		}

		return { ok: true, parsed: parsed };
	}

	/** Encontra a faixa do IMC exato (num/den). */
	function classify(num, den) {
		var bmi = { num: num, den: den };
		for (var i = 0; i < CLASSES.length; i++) {
			var c = CLASSES[i];
			var aboveMin = c.min === null || common.compareRatio(bmi, ratioOf(c.min)) >= 0;
			var belowMax = c.max === null || common.compareRatio(bmi, ratioOf(c.max)) < 0;
			if (aboveMin && belowMax) { return i; }
		}
		return -1; // inalcançável: as faixas cobrem todos os valores positivos
	}

	function formatLimit(text) {
		return text.replace('.', ',');
	}

	/**
	 * Calcula o IMC e a classificação.
	 *
	 * @param {{peso: (string|number), altura: (string|number)}} input
	 *   peso em kg e altura em cm, como texto digitado ou número.
	 * @return {object}
	 *   Sucesso: {
	 *     ok: true,
	 *     input: { peso: '80', altura: '180' },          // texto normalizado
	 *     bmi: { value: 24.69, display: '24,69', exactTruncated: '24,6913' },
	 *     classification: { id, label, index, min, max, rangeText },
	 *     rounding: { displayReachesLimit: boolean, limit: '25' | null }
	 *   }
	 *   Falha: { ok: false, errors: [{ field, code, min?, max?, unit? }] }
	 *   Campos: 'peso' | 'altura' | 'form'.
	 *   Códigos: required, not_a_number, not_positive, too_long, below_min,
	 *            above_max, height_in_meters, implausible_bmi, unsupported.
	 */
	function calculate(input) {
		if (!supported) {
			return { ok: false, errors: [{ field: 'form', code: 'unsupported' }] };
		}

		var source = input || {};
		var peso = readField('peso', source.peso);
		var altura = readField('altura', source.altura);

		var errors = [];
		if (!peso.ok) { errors.push(peso.error); }
		if (!altura.ok) { errors.push(altura.error); }
		if (errors.length) { return { ok: false, errors: errors }; }

		// IMC = (pn / 10^ps) / ((hn / 10^hs) / 100)^2 = pn * 10^(4 + 2*hs) / (10^ps * hn^2)
		var p = peso.parsed;
		var h = altura.parsed;
		var num = p.n * common.pow10(4 + 2 * h.scale);
		var den = common.pow10(p.scale) * h.n * h.n;
		var exact = { num: num, den: den };

		var tooLow = common.compareRatio(exact, ratioOf(LIMITS.imc.min)) < 0;
		var tooHigh = common.compareRatio(exact, ratioOf(LIMITS.imc.max)) > 0;
		if (tooLow || tooHigh) {
			return {
				ok: false,
				errors: [{ field: 'form', code: 'implausible_bmi', min: LIMITS.imc.min, max: LIMITS.imc.max }]
			};
		}

		var index = classify(num, den);
		var cls = CLASSES[index];

		var rounded = common.roundHalfUp(num, den, 2);
		var display = common.formatFixed(rounded, 2);
		var exactTruncated = common.formatFixed(common.truncate(num, den, 4), 4);

		// O número arredondado alcança o limite superior da faixa, mas o valor exato não?
		var reachesLimit = false;
		if (cls.max !== null) {
			reachesLimit = common.compareRatio({ num: rounded, den: common.pow10(2) }, ratioOf(cls.max)) >= 0;
		}

		return {
			ok: true,
			input: { peso: p.text, altura: h.text },
			bmi: {
				value: Number(rounded) / 100,
				display: display,
				exactTruncated: exactTruncated
			},
			classification: {
				id: cls.id,
				label: cls.label,
				index: index,
				min: cls.min,
				max: cls.max,
				rangeText: cls.rangeText
			},
			rounding: {
				displayReachesLimit: reachesLimit,
				limit: reachesLimit ? formatLimit(cls.max) : null
			}
		};
	}

	return {
		supported: supported,
		LIMITS: LIMITS,
		CLASSES: CLASSES,
		calculate: calculate
	};
}));
