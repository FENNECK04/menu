jQuery(function ($) {
	$(document).on('click', '.ivm-upload-button', function (event) {
		event.preventDefault();
		var button = $(this);
		var frame = wp.media({
			title: 'Select or upload logo',
			button: { text: 'Use this logo' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			button.closest('.ivm-logo-upload').find('input').val(attachment.url);
		});

		frame.open();
	});

	function updateSectionOrder($container) {
		var order = [];
		$container.find('.ivm-section-order__item').each(function () {
			var $item = $(this);
			var key = $item.data('key');
			if (key === 'divider') {
				order.push('divider');
				return;
			}
			var enabled = $item.find('.ivm-section-order__toggle').prop('checked');
			if (enabled) {
				order.push(key);
			}
		});
		var target = $container.data('target');
		$container.find('#' + target).val(order.join(','));
	}

	function moveSectionItem($item, direction) {
		if (direction === 'up') {
			var $prev = $item.prev();
			if ($prev.length) {
				$item.insertBefore($prev);
			}
		} else {
			var $next = $item.next();
			if ($next.length) {
				$item.insertAfter($next);
			}
		}
	}

	function buildDividerItem($container) {
		var label = $container.data('divider-label') || 'Divider';
		var removeLabel = $container.data('remove-label') || 'Remove';
		return $(
			'<li class="ivm-section-order__item" data-key="divider">' +
				'<span class="ivm-section-order__handle" aria-hidden="true">⋮⋮</span>' +
				'<span class="ivm-section-order__label"></span>' +
				'<button type="button" class="button-link ivm-section-order__remove"></button>' +
				'<span class="ivm-section-order__actions">' +
					'<button type="button" class="button-link ivm-section-order__up" aria-label="Move up">↑</button>' +
					'<button type="button" class="button-link ivm-section-order__down" aria-label="Move down">↓</button>' +
				'</span>' +
			'</li>'
		).find('.ivm-section-order__label').text(label).end()
			.find('.ivm-section-order__remove').text(removeLabel).end();
	}

	$(document).on('click', '.ivm-section-order__up', function () {
		var $item = $(this).closest('.ivm-section-order__item');
		var $container = $item.closest('.ivm-section-order');
		moveSectionItem($item, 'up');
		updateSectionOrder($container);
	});

	$(document).on('click', '.ivm-section-order__down', function () {
		var $item = $(this).closest('.ivm-section-order__item');
		var $container = $item.closest('.ivm-section-order');
		moveSectionItem($item, 'down');
		updateSectionOrder($container);
	});

	$(document).on('change', '.ivm-section-order__toggle', function () {
		var $container = $(this).closest('.ivm-section-order');
		updateSectionOrder($container);
	});

	$(document).on('click', '.ivm-section-order__remove', function () {
		var $item = $(this).closest('.ivm-section-order__item');
		var $container = $item.closest('.ivm-section-order');
		$item.remove();
		updateSectionOrder($container);
	});

	$(document).on('click', '.ivm-section-order__add-divider', function () {
		var $container = $(this).closest('.ivm-section-order');
		$container.find('.ivm-section-order__list').append(buildDividerItem($container));
		updateSectionOrder($container);
	});

	$('.ivm-section-order').each(function () {
		updateSectionOrder($(this));
	});
});
