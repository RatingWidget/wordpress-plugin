(function($) {
    $(document).ready(function() {
        // Override RWM.Code.get so that we will be notified when the settings change.
        var oldRWMCodeGet = RWM.Code.get;
        var updatingPreviewOptions = false;

        RWM.Code.get = function() {
            if (updatingPreviewOptions) {
                return;
            }

            updatingPreviewOptions = true;

            oldRWMCodeGet();

            handleRatingOptionsChange();
            updatingPreviewOptions = false;
        };

        handleShowInfoChange();
        handleRatingReadOnlyStateChange();
        handleRatingTypeChange();

        $('.rw-add-rating').on('click', addRatingCriterion);

        $('.show-summary-rating').on('click', function() {
            if ($(this).prop('checked')) {
                $('.rw-summary-rating').show();
            } else {
                $('.rw-summary-rating').hide();
            }
        });

        $('#rw_preview_container').on('keyup keydown blur', '.rw-add-label input', function(e) {
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

            var placeholderText = $(this).attr('placeholder');
            var newLabel = $(this).val().trim();
            if (!newLabel) {
                newLabel = placeholderText;
            }

            var addLabel = $('<a href="#"><nobr></nobr></a>');
            addLabel.find('nobr').text(newLabel);
            addLabel.attr('data-placeholder', placeholderText);

            var hasCustomValue = newLabel != placeholderText;

            var parentRow = $(this).parents('tr:first');

            if (hasCustomValue) {
                parentRow.find('input.multi-rating-label').val(newLabel);
                addLabel.addClass('has-custom-value');
            } else {
                parentRow.find('input.multi-rating-label').val('');
                addLabel.removeClass('has-custom-value');
            }

            $(this).replaceWith(addLabel);
        });

        // Handles the removal of a criterion
        $('#rw_wp_set').on('click', '.multi-rating .rw-remove-button', function() {
            $('tr.rw-rating:last').remove();

            // If not multi-criterion, remove the summary rating and make the 1st criterion the main rating
            if (!isMultiCriterion()) {
                for (var typeIndex in RW.TYPE) {
                    var type = RW.TYPE[typeIndex];

                    var updatedOptions = getCurrentRatingOptions(type);

                    var ratingElement = $('tr.rw-rating .rw-ui-' + type);

                    var urid = getSummaryUrid(type);

                    ratingElement.removeAttr('data-uarid').removeAttr('data-hide-recommendations').attr({
                        'class': 'rw-ui-container rw-ui-' + type,
                        'data-urid': urid
                    });

                    var summaryRatingRow = $('tr.rw-summary-rating');
                    summaryRatingRow.find('.rw-ui-' + type).attr('class', 'rw-ui-' + type).html('');

                    var newOptions = $.extend(true, {}, updatedOptions);
                    RW.initRating(urid, newOptions);
                }

                RW.render(null, false);
            }

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

    /**
     * Extracts the rating's ID from the element's class
     * @param {type} elementClass
     * @returns {Number}
     */
    function parseUrid(elementClass) {
        var matches = elementClass.match(/\brw-urid-(\d+)\b/);
        if (matches) {
            if (matches.length >= 1) {
                return matches[1];
            }
        }

        return 0;
    }

    /**
     * Checks if there's more than one row
     * @returns {Boolean}
     */
    function isMultiCriterion() {
        return $('tr.rw-rating').length > 1;
    }

    /**
     * Creates a new rating widget and appends it to the current rating widgets list
     * @returns {Boolean}
     */
    function addRatingCriterion() {
        if (!$(this).hasClass('upgrade')) {
            var parent = $('table.rw-preview');
			
            var ratingTemplate = parent.find('.rw-template-rating');
            var newRating = $(ratingTemplate.get(0).outerHTML);
            newRating.removeAttr('id');
            newRating.removeClass('rw-template-rating');
            newRating.addClass('rw-rating');

            var multiRatingLabel = newRating.find('.multi-rating-label');
            multiRatingLabel.attr('name', 'multi_rating[criteria][][label]');
			
            newRating.insertAfter(parent.find('tr.rw-rating:last'));
			
            // Add the necessary class so that the summary rating can be initialized
            $('tr.rw-summary-rating').children('td').eq(1).children('div').addClass('rw-ui-container');

            newRating.find('.rw-rating-type div').addClass('rw-ui-container');
            newRating.show();

            initializeRatings();

            // Show the summary rating after adding a new rating widget.
            toggleSummaryRatingOptions();

            return false;
        }
    }

    /**
     * Retrieves the current options of the base rating stored in RWM
     * @param {string} type
     * @returns {Object}
     */
    function getCurrentRatingOptions(type) {
        var rw = RWM[type.toUpperCase()];

        return rw.options;
    }

    /**
     * Called every time the live preview settings change.
     * @returns {undefined}
     */
    function handleRatingOptionsChange() {
		var rclass = $('table.rw-preview').data('rclass');
		
        for (var typeIndex in RW.TYPE) {
            var type = RW.TYPE[typeIndex];

            var updatedOptions = getCurrentRatingOptions(type);

            $('#base-rating .rw-ui-' + type + ',' + 'tr.rw-rating .rw-ui-' + type + ',' + 'tr.rw-summary-rating .rw-ui-' + type).each(function() {
                var elementRow = $(this).parents('tr:first');

                var urid = parseUrid($(this).attr('class'));
                var rating = RW.getRating(urid);
				
                if (rating) {
					var criteria_class = rclass;
					
                    var ratingInstance = rating.getInstances(0);
                    var newOptions = $.extend(true, {}, updatedOptions);
                    var urid_summary = getSummaryUrid(type);

                    if (isMultiCriterion() && elementRow.hasClass('rw-rating')) {
                        newOptions.uarid = urid_summary;
                        newOptions.hideRecommendations = true;
                    }

                    if (isMultiCriterion() && urid == urid_summary) {
                        newOptions.readOnly = true;
                    } else if (isMultiCriterion()) {
						var dataUrid = $(this).data('urid').toString();
						var dataUridParts = dataUrid.split('-');
						
						if (2 === dataUridParts.length) {
							criteria_class += '-criteria-' + dataUridParts[1];
						}
					}
					
					newOptions.rclass = criteria_class;
					
                    ratingInstance.setOptions(newOptions);
                }
            });
        }

        var currentOptions = getCurrentRatingOptions(RW.TYPE.STAR);
        handleLayoutDirectionChange(currentOptions.advanced.layout.dir);
    }

    /**
     * Loads the necessary CSS styles based on the current layout direction
     * @param {string} dir
     * @returns {undefined}
     */
    function handleLayoutDirectionChange(dir) {
        if ($('#rw_wp_preview').hasClass('rw-' + dir)) {
            return;
        }

        if ('rtl' == dir) {
            $('#rw_wp_preview').addClass('rw-rtl');
            $('#rw_wp_preview').removeClass('rw-ltr');
        } else {
            $('#rw_wp_preview').addClass('rw-ltr');
            $('#rw_wp_preview').removeClass('rw-rtl');
        }
    }

    /**
     * Hides or shows the rating widget's bubble
     * @returns {undefined}
     */
    function handleShowInfoChange() {
        $('.hide-info-bubble').on('click', function() {
            var showInfo = !$(this).prop('checked');

            for (var typeIndex in RW.TYPE) {
                var type = RW.TYPE[typeIndex];

                // Update the base rating instance stored in RWM to preserve
                // the value for new rating widgets
                RWM[type.toUpperCase()].options.showInfo = showInfo;

                $('.rw-ui-' + type).each(function() {
                    var urid = $(this).data('urid');
                    var rating = RW.getRating(urid);

                    if (rating) {
                        var instances = rating.getInstances();
                        var totalInstance = instances.length;

                        for (var i = 0; i < totalInstance; i++) {
                            var ratingInstance = instances[i];
                            var newOptions = ratingInstance.getCalculatedOptions();
                            newOptions.showInfo = showInfo;
                            ratingInstance.setOptions(newOptions);
                        }
                    }
                });
            }

            RWM.Code.refresh();
        });
    }

    /**
     * Displays or hides the star- or nero-rating widgets based on the selected type
     * @returns {undefined}
     */
    function handleRatingTypeChange() {
        $('#rw_rate_type').on('click', '.rw-ui-img-radio', function() {
            var type = $(this).find('span:first').text().toLowerCase();

            if (type == RW.TYPE.STAR) {
                $('.rw-preview').removeClass('rw-preview-nero').addClass('rw-preview-star');
            } else {
                $('.rw-preview').removeClass('rw-preview-star').addClass('rw-preview-nero');
            }
        });
    }

    /**
     * Handles the updating of the readOnly property of the base and criteria rating widgets
     * @returns {undefined}
     */
    function handleRatingReadOnlyStateChange() {
        $('.author-rating-readonly').on('click', function() {
            var readOnly = $(this).prop('checked');

            setRatingReadOnly(readOnly);

            if (readOnly) {
                $('#rw_rate_readonly .rw-ui-img-radio:last').click();
            } else {
                $('#rw_rate_readonly .rw-ui-img-radio:first').click();
            }
        });

        $('#rw_rate_readonly').on('click', '.rw-ui-img-radio', function() {
            var readOnly = ('ReadOnly' == $(this).find('span:first').text());
            $('.author-rating-readonly').prop('checked', readOnly);
        });
    }

    /**
     * Sets the rating widget to be read-only or active
     * @param {Boolean} readOnly
     * @returns {undefined}
     */
    function setRatingReadOnly(readOnly) {
        for (var typeIndex in RW.TYPE) {
            var type = RW.TYPE[typeIndex];

            // Update the base rating instance stored in RWM to preserve
            // the value for new rating widgets
            RWM[type.toUpperCase()].options.readOnly = readOnly;

            $('#base-rating .rw-ui-' + type + ', .rw-preview .rw-ui-' + type).each(function() {
                var urid = $(this).data('urid');
                var rating = RW.getRating(urid);

                if (rating) {
                    var instances = rating.getInstances();
                    var totalInstance = instances.length;

                    for (var i = 0; i < totalInstance; i++) {
                        var ratingInstance = instances[i];
                        ratingInstance.setReadOnly(readOnly);
                    }
                }
            });
        }
    }

    /**
     * Initializes all rating widgets with the current base rating settings and renders the newly added widget
     * @returns {undefined}
     */
    function initializeRatings() {
		var rclass = $('table.rw-preview').data('rclass');
		
        for (var typeIndex in RW.TYPE) {
            var type = RW.TYPE[typeIndex];

            var updatedOptions = getCurrentRatingOptions(type);

            $('tr.rw-rating .rw-ui-' + type + ',' + 'tr.rw-summary-rating .rw-ui-' + type).each(function() {
                var newOptions = $.extend(true, {}, updatedOptions);

                var urid = getSummaryUrid(type);

                var elementRow = $(this).parents('tr:first');

                $(this).attr('class', 'rw-ui-container rw-ui-' + type);

                if (isMultiCriterion() && elementRow.hasClass('rw-rating')) {
                    $(this).attr({
                        'data-hide-recommendations': true,
                        'data-uarid': urid
                    });

                    var criterionID = elementRow.index() + 1;
                    urid += '-' + criterionID;
					
					newOptions.rclass = rclass + '-criteria-' + criterionID;
                }

                $(this).attr('data-urid', urid);

                RW.initRating(urid, newOptions);
            });
        }

        RW.render(null, false);
    }

    /**
     * Decides whether to show or hide the summary rating based on the number of the visible widgets
     * @returns {undefined}
     */
    function toggleSummaryRatingOptions() {
        var total = $('tr.rw-rating').length;

        if (total > 1) { // We have a multi-criterion, show additional options
            $('#rw_wp_preview').addClass('multi-rating');
            if ($('.show-summary-rating').prop('checked')) {
                $('.rw-summary-rating').show();
            }
        } else {
            $('#rw_wp_preview').removeClass('multi-rating');
            $('.rw-summary-rating').hide();
        }

        if (total >= 3 && !(RW._isProfessional() || RW._isTrial())) {
            $('a.rw-add-rating').text($('a.rw-add-rating').data('upgrade-text'));
            $('a.rw-add-rating').attr('href', $('a.rw-add-rating').data('upgrade-href'));
            $('a.rw-add-rating').addClass('upgrade');
        } else {
            $('a.rw-add-rating').text($('a.rw-add-rating').data('default-text'));
            $('a.rw-add-rating').attr('href', '#');
            $('a.rw-add-rating').removeClass('upgrade');
        }
    }
})(jQuery);