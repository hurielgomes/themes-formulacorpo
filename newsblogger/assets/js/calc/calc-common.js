/**
 * Fórmula Corpo — utilitários comuns dos cálculos (sem DOM, sem rede).
 *
 * Por que aritmética exata?
 * Com números de ponto flutuante, 64 / (1,6 × 1,6) resulta em 24,999999999999996
 * (o valor exato é 25) e cairia na faixa errada. Aqui os valores digitados viram frações de inteiros
 * (BigInt) e as comparações com os limites de classificação são exatas.
 *
 * Funciona no navegador (expõe window.FCCalc.common) e no Node (module.exports),
 * o que permite testar as fórmulas sem depender da interface.
 *
 * Observação: não usa literais BigInt (10n) para não quebrar a leitura do
 * arquivo em navegadores muito antigos; usa BigInt(...) em tempo de execução.
 */
(function (root, factory) {
	'use strict';
	var api = factory();
	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	}
	if (root) {
		root.FCCalc = root.FCCalc || {};
		root.FCCalc.common = api;
	}
}(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : this), function () {
	'use strict';

	var supported = typeof BigInt === 'function';

	/** Limites de leitura: até 9 dígitos inteiros e 6 casas decimais. */
	var MAX_INT_DIGITS = 9;
	var MAX_DECIMALS = 6;

	function fail(code) {
		return { ok: false, code: code };
	}

	function pow10(exponent) {
		var text = '1';
		for (var i = 0; i < exponent; i++) { text += '0'; }
		return BigInt(text);
	}

	/**
	 * Lê um número decimal digitado por uma pessoa.
	 * Aceita "80", "80,5" e "80.5". Rejeita vazio, texto, notação científica,
	 * separador de milhar (1.234,5), sinal "+" e valores com formato incompleto
	 * (".5", "5."). Valores negativos recebem o código "not_positive".
	 *
	 * @param {string|number|null|undefined} raw
	 * @return {{ok:true, n:bigint, scale:number, text:string}|{ok:false, code:string}}
	 *   códigos: required | not_a_number | not_positive | too_long
	 */
	function parseDecimal(raw) {
		if (raw === null || raw === undefined) { return fail('required'); }

		if (typeof raw === 'number') {
			if (!isFinite(raw)) { return fail('not_a_number'); }
			raw = String(raw);
		} else if (typeof raw !== 'string') {
			return fail('not_a_number');
		}

		var text = raw.trim();
		if (text === '') { return fail('required'); }

		var negative = false;
		if (/^[-\u2212]/.test(text)) {
			negative = true;
			text = text.slice(1).trim();
		}

		if (!/^\d+([.,]\d+)?$/.test(text)) { return fail('not_a_number'); }
		if (negative) { return fail('not_positive'); }

		var parts = text.split(/[.,]/);
		var intPart = parts[0];
		var fraction = parts[1] || '';
		if (intPart.length > MAX_INT_DIGITS || fraction.length > MAX_DECIMALS) { return fail('too_long'); }

		return {
			ok: true,
			n: BigInt(intPart + fraction),
			scale: fraction.length,
			text: fraction ? intPart + '.' + fraction : intPart
		};
	}

	/** Converte um decimal lido em fração { num, den } (BigInt). */
	function toRatio(parsed) {
		return { num: parsed.n, den: pow10(parsed.scale) };
	}

	/** Compara duas frações positivas: retorna -1, 0 ou 1. */
	function compareRatio(a, b) {
		var left = a.num * b.den;
		var right = b.num * a.den;
		if (left < right) { return -1; }
		if (left > right) { return 1; }
		return 0;
	}

	/** Arredonda num/den para `decimals` casas (metade para cima). Retorna inteiro escalado. */
	function roundHalfUp(num, den, decimals) {
		var two = BigInt(2);
		var scaled = num * pow10(decimals);
		return (scaled * two + den) / (den * two);
	}

	/** Trunca num/den para `decimals` casas (sem arredondar). Retorna inteiro escalado. */
	function truncate(num, den, decimals) {
		return (num * pow10(decimals)) / den;
	}

	/** Formata um inteiro escalado como texto decimal. Ex.: (2469, 2) -> "24,69". */
	function formatFixed(scaled, decimals, separator) {
		var sep = separator === undefined ? ',' : separator;
		var digits = scaled.toString();
		if (decimals === 0) { return digits; }
		while (digits.length <= decimals) { digits = '0' + digits; }
		return digits.slice(0, digits.length - decimals) + sep + digits.slice(digits.length - decimals);
	}

	return {
		supported: supported,
		MAX_INT_DIGITS: MAX_INT_DIGITS,
		MAX_DECIMALS: MAX_DECIMALS,
		parseDecimal: parseDecimal,
		toRatio: toRatio,
		compareRatio: compareRatio,
		roundHalfUp: roundHalfUp,
		truncate: truncate,
		formatFixed: formatFixed,
		pow10: pow10
	};
}));
