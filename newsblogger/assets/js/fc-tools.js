/**
 * Fórmula Corpo — Ferramentas e Calculadoras
 * JavaScript puro, sem dependências (não usa jQuery nem bibliotecas de gráficos).
 *
 * Os gráficos são renderizados a partir de dados em atributos data-*, para que
 * as calculadoras futuras reutilizem as mesmas funções: FCTools.donut(),
 * FCTools.bars(), FCTools.bmi() e FCTools.mountCalculator() (formulário genérico
 * ligado a uma função de cálculo pura, ver assets/js/calc/).
 */
(function () {
	'use strict';

	var SVG_NS = 'http://www.w3.org/2000/svg';

	/* ----------------------------- utilitários ----------------------------- */

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text !== undefined && text !== null) { node.textContent = text; }
		return node;
	}

	function svgEl(tag, attrs) {
		var node = document.createElementNS(SVG_NS, tag);
		Object.keys(attrs || {}).forEach(function (key) { node.setAttribute(key, attrs[key]); });
		return node;
	}

	function readData(host, attr) {
		try {
			return JSON.parse(host.getAttribute(attr));
		} catch (e) {
			return null;
		}
	}

	function formatNumber(value) {
		return Number(value).toLocaleString('pt-BR');
	}

	function formatDecimal(value) {
		return Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
	}

	function clamp(value, min, max) {
		return Math.min(max, Math.max(min, value));
	}

	/* -------------------------------- rosca -------------------------------- */

	function renderDonut(host, data) {
		if (!host || !data || !data.items) { return; }
		host.textContent = '';

		var radius = 46;
		var circumference = 2 * Math.PI * radius;
		var gap = 2.5;
		var offset = 0;

		var summary = data.items.map(function (item) {
			return item.label + ' ' + item.percent + '%';
		}).join(', ');

		var svg = svgEl('svg', {
			'class': 'fc-tools-donut__svg',
			viewBox: '0 0 120 120',
			role: 'img',
			'aria-label': 'Gráfico de rosca. ' + summary + '. Total: ' + data.total + ' ' + data.unit + '.'
		});

		svg.appendChild(svgEl('circle', {
			'class': 'fc-tools-donut__track',
			cx: 60, cy: 60, r: radius, fill: 'none', 'stroke-width': 16
		}));

		var group = svgEl('g', { transform: 'rotate(-90 60 60)' });
		data.items.forEach(function (item, index) {
			var length = circumference * item.percent / 100;
			var visible = Math.max(length - gap, 0);
			group.appendChild(svgEl('circle', {
				'class': 'fc-tools-donut__seg fc-tools-s' + index,
				cx: 60, cy: 60, r: radius, fill: 'none', 'stroke-width': 16,
				'stroke-dasharray': visible + ' ' + (circumference - visible),
				'stroke-dashoffset': -offset
			}));
			offset += length;
		});
		svg.appendChild(group);

		var total = svgEl('text', { 'class': 'fc-tools-donut__total', x: 60, y: 61 });
		total.textContent = data.total;
		var unit = svgEl('text', { 'class': 'fc-tools-donut__unit', x: 60, y: 73 });
		unit.textContent = data.unit;
		svg.appendChild(total);
		svg.appendChild(unit);

		var legend = el('div', 'fc-tools-donut__legend');
		data.items.forEach(function (item, index) {
			var row = el('div', 'fc-tools-donut__row');
			var swatch = el('span', 'fc-tools-swatch fc-tools-s' + index);
			swatch.setAttribute('aria-hidden', 'true');
			row.appendChild(swatch);
			row.appendChild(el('span', 'fc-tools-donut__name', item.label));
			row.appendChild(el('span', 'fc-tools-donut__val', item.percent + '% das calorias · ' + formatNumber(item.grams) + ' g'));
			legend.appendChild(row);
		});

		host.appendChild(svg);
		host.appendChild(legend);
	}

	/* -------------------------------- barras ------------------------------- */

	function renderBars(host, data) {
		if (!host || !data || !data.items) { return null; }
		host.textContent = '';

		var rows = data.items.map(function (item) {
			var row = el('div', 'fc-tools-bar');
			var track = el('span', 'fc-tools-bar__track');
			var fill = el('span', 'fc-tools-bar__fill');
			track.setAttribute('aria-hidden', 'true');
			track.appendChild(fill);
			var value = el('span', 'fc-tools-bar__value');
			row.appendChild(el('span', 'fc-tools-bar__label', item.label));
			row.appendChild(track);
			row.appendChild(value);
			host.appendChild(row);
			return { item: item, fill: fill, value: value };
		});

		function update(minutes) {
			rows.forEach(function (row) {
				var kcal = Math.round(row.item.kcal60 * minutes / 60);
				row.fill.style.width = clamp(kcal / data.axisMax * 100, 2, 100) + '%';
				row.value.textContent = formatNumber(kcal) + ' ' + data.unit;
			});
		}

		update(data.minutes || 60);
		return { update: update };
	}

	/* ---------------------------- escala do IMC ---------------------------- */

	/**
	 * Escala de faixas (genérica). Cada faixa: { label, range, min, max, open }.
	 * - A faixa ativa pode vir pronta de quem calculou (`activeIndex`), para que a
	 *   interface nunca reclassifique o valor com números já arredondados.
	 * - Faixas sem limite superior (`open: true`) não são desenhadas como
	 *   numericamente limitadas e recebem o marcador no centro.
	 * Retorna { update({ value, activeIndex, summary }) }.
	 */
	function renderBmi(host, data) {
		if (!host || !data || !data.segments) { return null; }
		host.textContent = '';

		var segments = data.segments;
		var cells = [];
		var flags = [];
		var tracks = [];

		var scale = el('div', 'fc-tools-bmi__scale');
		segments.forEach(function (seg) {
			var cell = el('div', 'fc-tools-bmi__seg' + (seg.open ? ' is-open' : ''));
			var track = el('div', 'fc-tools-bmi__track');
			track.setAttribute('aria-hidden', 'true');
			var flag = el('span', 'fc-tools-bmi__here', data.hereLabel || 'Faixa do exemplo');
			flag.hidden = true;

			cell.appendChild(track);
			cell.appendChild(el('span', 'fc-tools-bmi__name', seg.label));
			cell.appendChild(el('span', 'fc-tools-bmi__range', seg.range));
			cell.appendChild(flag);
			scale.appendChild(cell);

			cells.push(cell);
			tracks.push(track);
			flags.push(flag);
		});

		var summary = el('p', 'fc-tools-bmi__summary');
		host.appendChild(scale);
		host.appendChild(summary);

		var marker = null;

		function markerFraction(seg, value) {
			if (seg.open || typeof seg.min !== 'number' || typeof seg.max !== 'number') { return 0.5; }
			return clamp((value - seg.min) / (seg.max - seg.min), 0.06, 0.94);
		}

		function update(state) {
			state = state || {};
			var value = state.value;
			var index = state.activeIndex;
			var hasValue = typeof value === 'number' && isFinite(value);

			if (marker && marker.parentNode) { marker.parentNode.removeChild(marker); }
			marker = null;
			cells.forEach(function (cell, i) {
				cell.classList.remove('is-active');
				cell.removeAttribute('aria-current');
				flags[i].hidden = true;
			});

			if (hasValue && (index === undefined || index === null)) {
				// Uso da demonstração: descobre a faixa pelo valor numérico.
				index = 0;
				segments.forEach(function (seg, i) {
					if (typeof seg.min === 'number' && value >= seg.min) { index = i; }
				});
			}

			if (hasValue && index >= 0 && index < segments.length) {
				cells[index].classList.add('is-active');
				cells[index].setAttribute('aria-current', 'true');
				flags[index].hidden = false;
				marker = el('span', 'fc-tools-bmi__marker');
				marker.style.left = (markerFraction(segments[index], value) * 100) + '%';
				tracks[index].appendChild(marker);
			}

			if (state.summary !== undefined) {
				summary.textContent = state.summary;
			} else if (hasValue && index >= 0 && index < segments.length) {
				summary.textContent = 'Valor de exemplo: ' + formatDecimal(value) + ' ' + (data.unit || '') +
					', na faixa “' + segments[index].label + '”.';
			} else {
				summary.textContent = '';
			}
		}

		update({ value: data.value, activeIndex: data.activeIndex, summary: data.summary });
		return { update: update };
	}

	/* ------------------- formulário de calculadora (genérico) ------------------- */

	/**
	 * Liga um formulário a uma função de cálculo pura.
	 *
	 * opts.form       elemento <form>
	 * opts.fields     { nome: <input> }  (cada input usa aria-describedby -> mensagem)
	 * opts.submit     botão de calcular (é habilitado aqui; sem JS permanece desabilitado)
	 * opts.formError  elemento para erros que não pertencem a um campo (role="alert")
	 * opts.hints      { nome: texto }  texto de ajuda de cada campo (opcional)
	 * opts.calculate  function(valores) -> { ok, errors? , ... }   (função pura)
	 * opts.message    function(erro) -> texto do erro para a pessoa
	 * opts.onResult   function(resultado, origem)   origem: 'submit' (botão) ou 'live' (ao digitar)
	 * opts.onInvalid  function()   chamado quando os dados atuais não geram resultado
	 * opts.onReset    function()
	 *
	 * Nenhum valor digitado é enviado, salvo ou colocado na URL.
	 */
	function mountCalculator(opts) {
		var names = Object.keys(opts.fields);
		var engaged = false; // a pessoa já pediu um cálculo; a partir daí atualizamos ao digitar
		var timer = null;

		function messageNode(input) {
			return document.getElementById(input.getAttribute('aria-describedby'));
		}

		names.forEach(function (name) {
			var node = messageNode(opts.fields[name]);
			if (node) {
				if (opts.hints && opts.hints[name]) { node.textContent = opts.hints[name]; }
				node.setAttribute('data-fc-hint', node.textContent);
			}
		});

		function values() {
			var out = {};
			names.forEach(function (name) { out[name] = opts.fields[name].value; });
			return out;
		}

		function setFieldError(name, text) {
			var input = opts.fields[name];
			var node = messageNode(input);
			var field = input.closest('.fc-tools-field');
			if (field) { field.classList.toggle('is-invalid', !!text); }
			input.setAttribute('aria-invalid', text ? 'true' : 'false');
			if (node) { node.textContent = text || node.getAttribute('data-fc-hint') || ''; }
		}

		function clearErrors() {
			names.forEach(function (name) { setFieldError(name, ''); });
			if (opts.formError) { opts.formError.textContent = ''; opts.formError.hidden = true; }
		}

		function showErrors(errors) {
			clearErrors();
			var first = null;
			var general = [];
			errors.forEach(function (error) {
				if (opts.fields[error.field]) {
					setFieldError(error.field, opts.message(error));
					if (!first) { first = opts.fields[error.field]; }
				} else {
					general.push(opts.message(error));
				}
			});
			if (general.length && opts.formError) {
				opts.formError.textContent = general.join(' ');
				opts.formError.hidden = false;
			}
			return first;
		}

		function silentUpdate() {
			var result = opts.calculate(values());
			if (result.ok) {
				clearErrors();
				opts.onResult(result, 'live');
			} else {
				opts.onInvalid();
			}
		}

		opts.form.addEventListener('submit', function (event) {
			event.preventDefault();
			var result = opts.calculate(values());
			if (result.ok) {
				engaged = true;
				clearErrors();
				opts.onResult(result, 'submit');
				return;
			}
			engaged = true;
			var first = showErrors(result.errors);
			opts.onInvalid();
			if (first) { first.focus(); }
		});

		names.forEach(function (name) {
			var input = opts.fields[name];

			input.addEventListener('input', function () {
				if (!engaged) { return; }
				window.clearTimeout(timer);
				timer = window.setTimeout(silentUpdate, 200);
			});

			// Ao sair do campo, mostra o erro só daquele campo e só se houver algo digitado.
			input.addEventListener('blur', function () {
				if (String(input.value).trim() === '') { setFieldError(name, ''); return; }
				var result = opts.calculate(values());
				var mine = result.ok ? [] : result.errors.filter(function (e) { return e.field === name; });
				setFieldError(name, mine.length ? opts.message(mine[0]) : '');
			});
		});

		opts.form.addEventListener('reset', function () {
			window.clearTimeout(timer);
			window.setTimeout(function () {
				engaged = false;
				clearErrors();
				if (opts.onReset) { opts.onReset(); }
			}, 0);
		});

		if (opts.submit) { opts.submit.disabled = false; }
		return { values: values };
	}

	/* ---------------------------- inicialização ---------------------------- */

	function initCharts() {
		var donut = document.querySelector('[data-fc-donut]');
		if (donut) { renderDonut(donut, readData(donut, 'data-fc-donut')); }

		var bmi = document.querySelector('[data-fc-bmi]');
		if (bmi) { renderBmi(bmi, readData(bmi, 'data-fc-bmi')); }

		var barsHost = document.querySelector('[data-fc-bars]');
		if (!barsHost) { return; }

		var chart = renderBars(barsHost, readData(barsHost, 'data-fc-bars'));
		var live = document.getElementById('fc-bars-live');
		var radios = document.querySelectorAll('#fc-tools-duration input[name="fc-duration"]');

		Array.prototype.forEach.call(radios, function (radio) {
			radio.addEventListener('change', function () {
				if (!radio.checked || !chart) { return; }
				chart.update(Number(radio.value));
				if (live) { live.textContent = 'Valores atualizados para ' + radio.value + ' minutos.'; }
			});
		});
	}

	/* ------------------------ formulário demonstrativo ----------------------- */

	function initForm() {
		var form = document.getElementById('fc-tools-form');
		if (!form) { return; }

		var status = document.getElementById('fc-tools-status');
		var results = document.getElementById('fc-tools-results');
		var inputs = Array.prototype.slice.call(form.querySelectorAll('input[data-fc-name], select[data-fc-name]'));

		function messageFor(input) {
			return document.getElementById(input.getAttribute('aria-describedby'));
		}

		function setStatus(text) {
			if (status) { status.textContent = text; }
		}

		function validate(input) {
			var field = input.closest('.fc-tools-field');
			var msg = messageFor(input);
			var invalid = false;

			if (input.type === 'number' && input.value !== '' && !input.disabled) {
				var number = Number(input.value);
				var min = Number(input.min);
				var max = Number(input.max);
				invalid = isNaN(number) || number < min || number > max;
				if (msg) {
					if (invalid) {
						msg.textContent = 'Informe um valor entre ' + min + ' e ' + max + ' ' + input.getAttribute('data-fc-unit') + '.';
					} else {
						msg.textContent = msg.getAttribute('data-fc-hint');
					}
				}
			} else if (msg) {
				msg.textContent = msg.getAttribute('data-fc-hint');
			}

			if (field) { field.classList.toggle('is-invalid', invalid); }
			input.setAttribute('aria-invalid', invalid ? 'true' : 'false');
			return !invalid;
		}

		// Guarda o texto de ajuda original de cada campo.
		inputs.forEach(function (input) {
			var msg = messageFor(input);
			if (msg) { msg.setAttribute('data-fc-hint', msg.textContent); }
			input.addEventListener('blur', function () { validate(input); });
			input.addEventListener('input', function () {
				if (input.closest('.fc-tools-field').classList.contains('is-invalid')) { validate(input); }
			});
		});

		// Campo que depende de outro (duração depende da atividade).
		var dependent = form.querySelector('[data-fc-depends]');
		function syncDependent() {
			if (!dependent) { return; }
			var source = document.getElementById(dependent.getAttribute('data-fc-depends'));
			var msg = messageFor(dependent);
			var enabled = !source || source.value !== '';
			dependent.disabled = !enabled;
			if (!enabled) {
				dependent.value = '';
				if (msg) { msg.textContent = 'Escolha uma atividade para informar a duração.'; }
				dependent.closest('.fc-tools-field').classList.remove('is-invalid');
				dependent.setAttribute('aria-invalid', 'false');
			} else if (msg) {
				msg.textContent = msg.getAttribute('data-fc-hint');
			}
		}
		var activity = document.getElementById('fc-atividade');
		if (activity) { activity.addEventListener('change', syncDependent); }
		syncDependent();

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			var firstInvalid = null;
			inputs.forEach(function (input) {
				if (!validate(input) && !firstInvalid) { firstInvalid = input; }
			});

			if (firstInvalid) {
				setStatus('Revise os campos destacados antes de continuar.');
				firstInvalid.focus();
				return;
			}

			var filled = inputs.filter(function (input) { return !input.disabled && input.value !== ''; });
			var names = filled.map(function (input) { return input.getAttribute('data-fc-name'); });
			var summary = filled.length === 0
				? 'Nenhum campo preenchido.'
				: filled.length + (filled.length === 1 ? ' campo preenchido (' : ' campos preenchidos (') + names.join(', ') + ').';

			setStatus(summary + ' Demonstração: os cartões ao lado mostram valores de exemplo e não usam o que você digitou.');
			if (results) { results.focus({ preventScroll: false }); }
		});

		form.addEventListener('reset', function () {
			window.setTimeout(function () {
				inputs.forEach(function (input) { validate(input); });
				syncDependent();
				setStatus('Campos limpos.');
			}, 0);
		});
	}

	function init() {
		var root = document.querySelector('.fc-tools');
		if (root) { root.classList.add('fc-tools-js'); }
		initCharts();
		initForm();
	}

	window.FCTools = { donut: renderDonut, bars: renderBars, bmi: renderBmi, mountCalculator: mountCalculator };

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
