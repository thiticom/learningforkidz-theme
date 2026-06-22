(function ($) {
	$(function () {
		var frame;
		var $fileId = $('#lfk_resource_file_id');
		var $fileUrl = $('#lfk_resource_file_url');

		$('[data-lfk-resource-file-picker]').on('click', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Choose download file',
				button: {
					text: 'Use this file'
				},
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();

				$fileId.val(attachment.id);
				$fileUrl.val(attachment.url);
			});

			frame.open();
		});

		$('[data-lfk-resource-file-clear]').on('click', function (event) {
			event.preventDefault();
			$fileId.val('');
			$fileUrl.val('');
		});
	});
})(jQuery);
