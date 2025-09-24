(function ($) {
	"use strict";

	// Custom deps handler for special types
	$('.yith-plugins_page_yith_wpv_panel .form-table, #plugin-fw-wc').find('[data-deps]').each(function () {
		var t = $(this),
			wrap = t.closest('tr, .yith-plugin-fw__panel__option'),
			deps = t.attr('data-deps').split(','),
			values = t.attr('data-deps_value').split(','),
			conditions = [];

		$.each(deps, function (i, dep) {
			$('[name="' + dep + '"]').on('change', function () {
				var value = this.value,
					check_values = '';
				// exclude radio if not checked
				if ( 'radio' === this.type && !$(this).is(':checked') ) {
					return;
				}
				if ( 'checkbox' === this.type ) {
					value = $(this).is(':checked') ? 'yes' : 'no';
				}
				check_values = values[i] + ''; // force to string
				check_values = check_values.split('|');
				conditions[i] = $.inArray(value, check_values) !== -1;
				if ( $.inArray(false, conditions) === -1 ) {
					wrap.fadeIn();
				} else {
					wrap.hide();
				}
			}).change();
		});
	});

	// Custom upload field.
	var uploadAttachmentHandler = {
		onButtonClick: function (event) {
			event.preventDefault();

			var container = $(this).closest('.yith-wcmv-attachment-upload-container');
			// Create the media frame.
			const file_frame = (wp.media.frames.downloadable_file = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image',
				},
				multiple: false,
			}));

			// When an image is selected, run a callback.
			file_frame.on('select', function () {
				const attachment = file_frame.state().get('selection').first().toJSON(),
					imageRegex = new RegExp("(http|ftp|https)://[a-zA-Z0-9@?^=%&amp;:/~+#-_.]*.(gif|jpg|jpeg|png|ico|svg)");

				if ( imageRegex.test(attachment.sizes.full.url) ) {
					uploadAttachmentHandler.imageChange(container, attachment.id, attachment.sizes.full.url);
				}
			});

			// Finally, open the modal.
			file_frame.open();
		},
		onResetClick: function (event) {
			event.preventDefault();

			var container = $(this).closest('.yith-wcmv-attachment-upload-container'),
				id = $(this).data('id'),
				src = $(this).data('src');

			uploadAttachmentHandler.imageChange(container, id, src);
		},
		imageChange: function (container, id, url) {
			container.find('.yith-wcmv-attachment-upload-preview').html(url ? '<img src="' + url + '" style="max-width:300px; height:auto;" />' : '');
			container.find('input.yith-wcmv-attachment-upload-value').val(id);
		},
		init: function () {
			if ( typeof wp !== 'undefined' && typeof wp.media !== 'undefined' ) {
				$(document).on('click', '.yith-wcmv-attachment-upload', uploadAttachmentHandler.onButtonClick);
				$(document).on('click', '.yith-wcmv-attachment-reset', uploadAttachmentHandler.onResetClick);
			}
		}
	};
	$(document).ready(uploadAttachmentHandler.init);

	$(document.body).on('change', 'input.yith-wcmv-input-integer', function (event) {
		this.value = parseInt(this.value);
	});

	// Edit prompt
	$(function () {
		$(':input').on('change', function () {
			window.onbeforeunload = '';
		});
	});

	// Move notice to a new location.
	$(function () {
		const notices = $('.yith-wcmv-admin-notice');
		if ( notices.length ) {
			notices.appendTo( $('.yith-plugin-fw__panel__secondary-notices') ).show();
			// Handle dismiss
			notices.on('click', '.notice-dismiss', function () {
				$(this).parent().fadeOut();
			})
		}
	});

	console.log( $(document).find('input[type="checkbox"]:disabled' ) );

	$(document).find('input[type="checkbox"]:disabled').each( function(index, option) {
		console.log(option);
		$(option).closest('label').addClass('disabled-option');
	});

})(jQuery);
