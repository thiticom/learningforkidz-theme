(function ($) {
	$(function () {
		var fileFrame;
		var coverFrame;
		var $fileId = $('#lfk_resource_file_id');
		var $fileUrl = $('#lfk_resource_file_url');
		var $coverId = $('#lfk_resource_cover_id');
		var $coverUrl = $('#lfk_resource_cover_url');
		var $coverPreview = $('#lfk_resource_cover_preview');

		$('[data-lfk-resource-file-picker]').on('click', function (event) {
			event.preventDefault();

			if (fileFrame) {
				fileFrame.open();
				return;
			}

			fileFrame = wp.media({
				title: 'Choose download file',
				button: {
					text: 'Use this file'
				},
				multiple: false
			});

			fileFrame.on('select', function () {
				var attachment = fileFrame.state().get('selection').first().toJSON();

				$fileId.val(attachment.id);
				$fileUrl.val(attachment.url);
			});

			fileFrame.open();
		});

		$('[data-lfk-resource-file-clear]').on('click', function (event) {
			event.preventDefault();
			$fileId.val('');
			$fileUrl.val('');
		});

		$('[data-lfk-resource-cover-picker]').on('click', function (event) {
			event.preventDefault();

			if (coverFrame) {
				coverFrame.open();
				return;
			}

			coverFrame = wp.media({
				title: 'Choose cover preview',
				button: {
					text: 'Use this cover'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			coverFrame.on('select', function () {
				var attachment = coverFrame.state().get('selection').first().toJSON();
				var previewUrl = attachment.url;

				if (attachment.sizes && attachment.sizes.medium) {
					previewUrl = attachment.sizes.medium.url;
				}

				$coverId.val(attachment.id);
				$coverUrl.val(attachment.url);
				$coverPreview.html(
					$('<img>', {
						src: previewUrl,
						alt: '',
						style: 'display:block;max-width:140px;height:auto;margin:8px 0;border:1px solid #dcdcde;border-radius:4px;'
					})
				);
			});

			coverFrame.open();
		});

		$('[data-lfk-resource-cover-clear]').on('click', function (event) {
			event.preventDefault();
			$coverId.val('');
			$coverUrl.val('');
			$coverPreview.empty();
		});
	});
})(jQuery);
