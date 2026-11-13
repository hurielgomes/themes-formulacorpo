(function () {
	'use strict';
	var header = document.getElementById('fc-header');
	if (!header) { return; }
	var button = header.querySelector('.fc-header__burger');
	var panel = document.getElementById('fc-header-panel');
	if (!button || !panel) { return; }

	function setOpen(open) {
		button.setAttribute('aria-expanded', open ? 'true' : 'false');
		button.querySelector('.fc-sr').textContent = open ? 'Fechar menu' : 'Abrir menu';
		header.classList.toggle('is-open', open);
	}

	button.addEventListener('click', function () {
		setOpen(button.getAttribute('aria-expanded') !== 'true');
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && header.classList.contains('is-open')) {
			setOpen(false);
			button.focus();
		}
	});

	var desktop = window.matchMedia('(min-width: 961px)');
	var onChange = function (mq) { if (mq.matches) { setOpen(false); } };
	if (desktop.addEventListener) { desktop.addEventListener('change', onChange); }
	else if (desktop.addListener) { desktop.addListener(onChange); }
})();

/* Modo escuro independente do script do Newscrunch. */
(function () {
	'use strict';
	var link = document.querySelector('#fc-header .fc-theme-toggle .spnc-dark-icon');
	if (!link) { return; }
	var root = document.documentElement;
	var icon = link.querySelector('i');

	function isDark() { return root.getAttribute('data-theme') === 'spnc_dark'; }

	function sync() {
		var dark = isDark();
		if (icon) {
			icon.classList.toggle('fa-moon', !dark);
			icon.classList.toggle('fa-sun', dark);
		}
		link.setAttribute('aria-pressed', dark ? 'true' : 'false');
		link.setAttribute('aria-label', dark ? 'Ativar tema claro' : 'Ativar tema escuro');
	}

	link.addEventListener('click', function (event) {
		event.preventDefault();
		event.stopImmediatePropagation();
		var dark = !isDark();
		if (dark) { root.setAttribute('data-theme', 'spnc_dark'); }
		else { root.removeAttribute('data-theme'); }
		try { localStorage.setItem('fc-theme', dark ? 'dark' : 'light'); } catch (e) {}
		sync();
	});

	sync();
})();
