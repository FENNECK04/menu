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
});
