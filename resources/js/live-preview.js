(function($) {
	$(document).ready(function() {
		// Override RWM get so that we will be notified when the settings change.
		var oldRWMCodeGet = RWM.Code.get;
		var updatingPreviewOptions = false;
		
		RWM.Code.get = function() {
			oldRWMCodeGet();
			
			if (updatingPreviewOptions) {
				return;
			}
			
			updatingPreviewOptions = true;
			handleRatingOptionsChange();
			updatingPreviewOptions = false;
		};

		handleShowInfoChange();
		handleRatingReadOnlyStateChange();

		$('.rw-add-rating').on('click', function() {
			var ratingID = $('#rw_preview_star tr.rw-rating').length + 1;
			
			$('table.rw-preview').each(function() {
				var parent = $(this);
				var type = parent.data('type');
				
				var ratingTemplate = parent.find('.rw-template-rating');
				var newRating = $(ratingTemplate.get(0).outerHTML);
				newRating.removeAttr('id');
				newRating.removeClass('rw-template-rating');
				newRating.addClass('rw-rating');
				newRating.attr('data-rid', ratingID);
				newRating.find('.multi-rating-label').attr('name', 'multi_rating[criteria][' + ratingID + '][label]').val('Add Label');
				if (parent.find('tr.rw-rating').length) {
					newRating.insertAfter(parent.find('tr.rw-rating:last'));
				} else {
					parent.prepend(newRating);
				}
				
				newRating.find('.rw-rating-type div:first').addClass('rw-ui-container rw-urid-preview-' + ratingID + ('star' == type ? '0' : '1'));
				newRating.show();
			});
			
			initializeRatings(ratingID);
			
			// Show the summary rating after adding a new rating widget.
			toggleSummaryRatingOptions();
			return false;
		});
		
		$('.show-summary-rating').on('click', function() {
			$('input.show-summary-rating').prop('checked', $(this).prop('checked'));
			if ($(this).prop('checked')) {
				$('.rw-summary-rating').show();
			} else {
				$('.rw-summary-rating').hide();
			}
		});
		
		$('#rw_preview_container').on('keyup keydown blur', '.rw-add-label input', function(e) {
			try {
				if (e.type == 'keydown') {
					// If enter key is pressed
					if (e.keyCode == 13) {
						return false;
					} else {
						return true;
					}
				} else if (e.type == 'keyup') {
					if (e.keyCode != 13) {
						return true;
					}
				}
				
				var ratingID = $(this).parents('tr:first').data('rid');
				var newLabel = $(this).val();
				$(this).parents('tr:first').find('input').val(newLabel);
				var placeholderText = $(this).attr('placeholder');
				var addLabelButton = $('<a href="#"></a>');
				$(this).replaceWith(addLabelButton);
				
				// .data('placeholder') is not working here
				addLabelButton.attr('data-placeholder', placeholderText);
				if (newLabel != placeholderText) {
					addLabelButton.addClass('has-custom-value');
				}
				addLabelButton.text(newLabel);
				$('tr.rw-rating[data-rid="' + ratingID + '"] input').val(newLabel);
			} catch(err) {
			}
		});
		
		// Handle the removal of a criterion
		$('#rw_wp_set').on('click', '.multi-rating .rw-remove-button', function() {
			var parent = $(this).parents('tr:first');
			var id = parent.attr('data-rid');
			$('.rw-rating[data-rid="'+id+'"]').remove();
			toggleSummaryRatingOptions();
			return false;
		});
		
		$('#rw_preview_container').on('click', '.rw-add-label a', function() {
			var currentLabel = $(this).text();
			var placeholderText = $(this).data('placeholder');
			var inputField = $('<input type="text" />');
			inputField.attr('placeholder', placeholderText);
			inputField.val(currentLabel);
			$(this).replaceWith(inputField);
			inputField.focus();
			inputField.select();
			
			return false;
		});
	});
	
	function getCurrentRatingOptions(type) {
		var rw = RWM.STAR;
		if (type == RW.TYPE.NERO){
			rw = RWM.NERO;
		}
		
		return rw.options;
	}
	
	/**
	 * This is called everytime the live preview settings change.
	 */
	function handleRatingOptionsChange() {
		for (var type in RW.TYPE) {
			var updatedOptions = getCurrentRatingOptions(RW.TYPE[type]);
			var type = updatedOptions.type ? updatedOptions.type : 'star';

			$('#rw_preview_' + type).find('.rw-rating .rw-ui-container, .rw-summary-rating .rw-ui-container').each(function() {
				var matches = $(this).attr('class').match(/\brw-urid-(\d+)\b/);
				if (matches) {
					if (matches.length >= 1) {
						var urid = matches[1];
						var rating = RW.getRating(urid).getInstances()[0];
						var oldOptions = rating.getCalculatedOptions();
						oldOptions.advanced = updatedOptions.advanced;
						oldOptions.lng = updatedOptions.lng;
						oldOptions.readOnly = updatedOptions.readOnly;
						rating.setOptions(oldOptions);
						//console.log([oldOptions, updatedOptions]);
						if (oldOptions.size != updatedOptions.size) {
							rating.setSize(RW.SIZE[updatedOptions.size.toUpperCase()]);
						}
						if (oldOptions.style != updatedOptions.style) {
							rating.setStyle(updatedOptions.style);
						}
					}
				}
			});
		}
	}
	
	function handleShowInfoChange() {
		$('.hide_info_bubble').on('click', function() {
			var showInfo = !$(this).prop('checked');
			$('.rw-rating .rw-ui-container, .rw-summary-rating .rw-ui-container').each(function() {
				var urid = $(this).attr('class').match(/\brw-urid-(\d+)\b/)[1];
				var rating = RW.getRating(urid).getInstances()[0];
				var options = rating.getCalculatedOptions();
				options.showInfo = showInfo;
				rating.setOptions(options);
			});
		});
	}
	
	function setRatingReadOnly(readOnly) {
		$('.rw-rating .rw-ui-container, .rw-summary-rating .rw-ui-container').each(function() {
			var urid = $(this).attr('class').match(/\brw-urid-(\d+)\b/)[1];
			var rating = RW.getRating(urid).getInstances()[0];
			rating.setReadOnly(readOnly);
		});
	}
	
	function handleRatingReadOnlyStateChange() {
		$('.author_rating_readonly').on('click', function() {
			var readOnly = $(this).prop('checked');
			if (readOnly) {
				$('#rw_rate_readonly .rw-ui-img-radio.rw-selected').removeClass('rw-selected');
				$('#rw_rate_readonly .rw-ui-img-radio:last').addClass('rw-selected');
			} else {
				$('#rw_rate_readonly .rw-ui-img-radio.rw-selected').removeClass('rw-selected');
				$('#rw_rate_readonly .rw-ui-img-radio:first').addClass('rw-selected');
			}
			
			setRatingReadOnly(readOnly);
		});
		
		$('#rw_rate_readonly').on('click', '.rw-ui-img-radio', function() {
			var readOnly = $(this).find('span:first').text() == 'ReadOnly';
			$('input[name="rw_author_rating_readonly"]').prop('checked', readOnly);
			setRatingReadOnly(readOnly);
		});
	}
	
	function initializeRatings(ratingID, container) {
		for (var criteriaID = 0; criteriaID <= 1; criteriaID++) {
			var type = (0 == criteriaID ? 'star' : 'nero');
			var currentOptions = getCurrentRatingOptions(type);
			var defaultOptions = getOptions();
			
			defaultOptions.type = type;
			defaultOptions.advanced = currentOptions.advanced;
			defaultOptions.lng = currentOptions.lng;
			defaultOptions.readOnly = currentOptions.readOnly;
			defaultOptions.size = currentOptions.size;
			defaultOptions.style = currentOptions.style;
			defaultOptions.theme = currentOptions.theme;

			RW.initRating(
				'preview-' + ratingID + criteriaID,
				defaultOptions
			);
		}
		
		RW.render(function() {}, false/*, container*/);
		handleRatingOptionsChange();
	}
	
	/**
	 *  Decides whether to show or hide the summary rating.
	 * 
	 */
	function toggleSummaryRatingOptions() {
		var total = $('#rw_preview_star tr.rw-rating').length;
		
		if (total > 1) {
			$('#rw_wp_preview').addClass('multi-rating');
		} else {
			$('#rw_wp_preview').removeClass('multi-rating');
		}
	}
})(jQuery);