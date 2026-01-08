(function (blocks, element, components, i18n) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType('interslide/vertical-menu', {
		editor: function (props) {
			return el('div', { className: 'ivm-block-placeholder' }, [
				el('strong', {}, __('Interslide Menu', 'interslide-vertical-menu')),
				el('p', {}, __('This block renders the vertical menu on the front-end.', 'interslide-vertical-menu'))
			]);
		},
		save: function () {
			return null;
		}
	});
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.i18n);
