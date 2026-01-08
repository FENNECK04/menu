(function () {
	var menus = document.querySelectorAll('.ivm');
	if (!menus.length) {
		return;
	}

	var focusableSelectors = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

	function getMenuState(menu) {
		return {
			panel: menu.querySelector('.ivm__panel'),
			toggle: menu.querySelector('.ivm__toggle'),
			overlay: menu.querySelector('.ivm__overlay'),
			lastFocused: null
		};
	}

	function openMenu(menu, state) {
		state.lastFocused = document.activeElement;
		state.overlay.hidden = false;
		menu.classList.add('ivm--open');
		state.toggle.setAttribute('aria-expanded', 'true');
		state.panel.setAttribute('aria-hidden', 'false');
		var focusables = state.panel.querySelectorAll(focusableSelectors);
		if (focusables.length) {
			focusables[0].focus();
		}
	}

	function closeMenu(menu, state) {
		menu.classList.remove('ivm--open');
		state.overlay.hidden = true;
		state.toggle.setAttribute('aria-expanded', 'false');
		state.panel.setAttribute('aria-hidden', 'true');
		if (state.lastFocused) {
			state.lastFocused.focus();
		}
	}

	function trapFocus(event, menu, state) {
		if (!menu.classList.contains('ivm--open')) {
			return;
		}
		if (event.key !== 'Tab') {
			return;
		}
		var focusables = state.panel.querySelectorAll(focusableSelectors);
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

	menus.forEach(function (menu) {
		var state = getMenuState(menu);
		if (!state.panel || !state.toggle || !state.overlay) {
			return;
		}

		state.toggle.addEventListener('click', function () {
			if (menu.classList.contains('ivm--open')) {
				closeMenu(menu, state);
			} else {
				openMenu(menu, state);
			}
		});

		state.overlay.addEventListener('click', function () {
			closeMenu(menu, state);
		});

		menu.addEventListener('keydown', function (event) {
			trapFocus(event, menu, state);
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') {
			return;
		}
		menus.forEach(function (menu) {
			if (!menu.classList.contains('ivm--open')) {
				return;
			}
			var state = getMenuState(menu);
			if (state.panel && state.toggle && state.overlay) {
				closeMenu(menu, state);
			}
		});
	});

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.ivm__toggle');
		if (!button) {
			return;
		}
		var menu = button.closest('.ivm');
		if (!menu) {
			return;
		}
		var state = getMenuState(menu);
		if (!state.panel || !state.toggle || !state.overlay) {
			return;
		}
		if (menu.classList.contains('ivm--open')) {
			closeMenu(menu, state);
		} else {
			openMenu(menu, state);
		}
	});

	menus.forEach(function (menu) {
		var editionSelect = menu.querySelector('.ivm__edition-select');
		if (editionSelect) {
			editionSelect.addEventListener('change', function () {
				if (editionSelect.value) {
					window.location.href = editionSelect.value;
				}
			});
		}
	});
})();
