(function () {
	var menu = document.querySelector('.ivm');
	if (!menu) {
		return;
	}

	var panel = menu.querySelector('.ivm__panel');
	var toggle = menu.querySelector('.ivm__toggle');
	var overlay = menu.querySelector('.ivm__overlay');
	var focusableSelectors = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';
	var lastFocused = null;

	function openMenu() {
		lastFocused = document.activeElement;
		overlay.hidden = false;
		menu.classList.add('ivm--open');
		toggle.setAttribute('aria-expanded', 'true');
		panel.setAttribute('aria-hidden', 'false');
		var focusables = panel.querySelectorAll(focusableSelectors);
		if (focusables.length) {
			focusables[0].focus();
		}
	}

	function closeMenu() {
		menu.classList.remove('ivm--open');
		overlay.hidden = true;
		toggle.setAttribute('aria-expanded', 'false');
		panel.setAttribute('aria-hidden', 'true');
		if (lastFocused) {
			lastFocused.focus();
		}
	}

	function trapFocus(event) {
		if (!menu.classList.contains('ivm--open')) {
			return;
		}
		if (event.key !== 'Tab') {
			return;
		}
		var focusables = panel.querySelectorAll(focusableSelectors);
		if (!focusables.length) {
			return;
		}
		var first = focusables[0];
		var last = focusables[focusables.length - 1];
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	toggle.addEventListener('click', function () {
		if (menu.classList.contains('ivm--open')) {
			closeMenu();
		} else {
			openMenu();
		}
	});

	overlay.addEventListener('click', closeMenu);
	menu.addEventListener('keydown', trapFocus);
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && menu.classList.contains('ivm--open')) {
			closeMenu();
		}
	});

	var editionSelect = menu.querySelector('.ivm__edition-select');
	if (editionSelect) {
		editionSelect.addEventListener('change', function () {
			if (editionSelect.value) {
				window.location.href = editionSelect.value;
			}
		});
	}
})();
