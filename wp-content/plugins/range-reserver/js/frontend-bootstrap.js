;(function ( $, window, document, undefined ) {

    var pluginName = "rrBootstrap",

    defaults = {
        main_selector: '#rr-bootstrap-main',
        main_template: null,
        overview_selector: "#rr-appointments-overview",
        overview_template: null,
        store: {},
        ajaxCount: 0,
        initScrollOff: false
    };

    // The actual plugin constructor
    function Plugin ( element, options ) {
        this.element = element;
        this.$element = jQuery(element);
        this.settings = jQuery.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    jQuery.extend(Plugin.prototype, {
        closure: function(laneId, day) {
            var response = [true, day, ''];

            // block days from shortcode
            if (Array.isArray(rr_settings.block_days) && rr_settings.block_days.includes(day)) {
                return [
                    false,
                    'blocked',
                    rr_settings.block_days_tooltip
                ];
            }

            if (!Array.isArray(rr_closures) || rr_closures.length === 0) {
                return response;
            }

            jQuery.each(rr_closures, function(index, closure) {
                // Check events
                // Case we have lanes selected
                if (closure.lanes.length > 0) {
                    // extract lane ids
                    var laneIds = jQuery.map(closure.lanes, function(lane) {
                        return lane.id;
                    });
                    // selected lane is not in closure list exit
                    if (jQuery.inArray(laneId, laneIds) === -1) {
                        return true;
                    }

                }

                if (jQuery.inArray(day, closure.days) === -1) {
                    return true;
                }

                response = [false, 'blocked closure', closure.tooltip];

                return false;
            });

            return response;
        },
        /**
         * Plugin init
         */
        init: function () {
            var plugin = this;

            if (rr_settings['datepicker'] && rr_settings['datepicker'].length > 1) {
                moment.locale(rr_settings['datepicker'].substr(0,2));
            }

            plugin.settings.main_template = _.template(jQuery(plugin.settings.main_selector).html());

            plugin.settings.overview_template = _.template(jQuery(plugin.settings.overview_selector).html());
            this.$element.html(plugin.settings.main_template({settings:rr_settings}));

            // close plugin if something is missing
            if (!this.settingsOk()) {
                return;
            }

            this.$element.find('.rr-phone-number-part, .rr-phone-country-code-part').change(function() {
                plugin.parsePhoneField($(this));
            });
            $('#lane-select').change(function() {
                $('#final-step').removeClass('hidden');
            });
            // set default value for phone fields
            this.$element.find('.rr-phone-country-code-part').each(function(index, select) {
                $(select).val($(select).data('default'));
            });

            // handle form validation with scroll to field with error
            this.$element.find('form').validate({
                focusInvalid: false,
                invalidHandler: function(form, validator) {
                    if (!validator.numberOfInvalids())
                        return;
                    $('html, body').animate({
                        scrollTop: ($(validator.errorList[0].element).offset().top - 30)
                    }, 1000);
                }
            });

            // select change event
            this.$element.find('select').not('.custom-field').change(jQuery.proxy( this.getNextOptions, this ));

            jQuery.datepicker.setDefaults( jQuery.datepicker.regional[rr_settings.datepicker] );

            var firstDay = rr_settings.start_of_week;
            var minDate = (rr_settings.min_date === null) ? 0 : rr_settings.min_date;


            const options = {};
            options.action = 'rr_locations';
            options.check=rr_settings['check'];
            var req = jQuery.get(rr_ajaxurl, options, function (result) {
                $('#location-select').html(' ');
                $('#location-select').append($('<option>', {
                    value: '',
                    text:  '-'
                }));
                for(let i=0; i < result.length; i++){
                    $('#location-select').append($('<option>', {
                        value: result[i]['id'],
                        text:  result[i]['name']
                    }));
                }
                if(result.length==1){
                $("#location-select").val(result[0]['id']).change();
                $("#location-container").addClass('hidden');
                }
            }, 'json');

            req.fail(function (xhr, status) {

            });


            // datePicker
            this.$element.find('.date').datepicker({
                onSelect : jQuery.proxy( plugin.dateChange, plugin ),
                dateFormat : 'yy-mm-dd',
                minDate: minDate,
                firstDay: firstDay,
                maxDate: rr_settings.max_date,
                defaultDate: rr_settings.default_date,
                showWeek: rr_settings.show_week === '1',
                // on month change event
                onChangeMonthYear: function(year, month, widget) {
                    plugin.selectChange(month, year);
                },
                // add class to every field, so we can later find it
                beforeShowDay: function(date) {
                    var month = date.getMonth() + 1;
                    var days = date.getDate();

                    if(month < 10) {
                        month = '0' + month;
                    }

                    if(days < 10) {
                        days = '0' + days;
                    }

                    var dateString = date.getFullYear() + '-' + month + '-' + days;
                    var laneId = plugin.$element.find('[name="lane"]').val();

                    return plugin.closure(laneId, dateString);
                }
            });

            // hide options with one choice
            /*this.hideDefault();*/

            // time is selected
            this.$element.find('.rr-bootstrap').on('click', '.time-value', function(event) {
                event.preventDefault();
                $('#bay-select').html(' ');
                $('#lane-select').html(' ');
                $('.time-value').removeClass('selected-time');
                $(this).addClass('selected-time');
                const options = {};
                options.action = 'rr_available_locations_by_time_slot';
                options.day = plugin.$element.find('.date').datepicker().val();
                options.slot = plugin.$element.find('.selected-time').data('val');
                options.duration = plugin.$element.find('.selected-time').data('duration');
                options.check=rr_settings['check'];
                options.location = $('#location-select').val();
                var req = jQuery.get(rr_ajaxurl, options, function (response) {

                    for (let i=0;i<response.length;i++){
                        if(response[i]['id']==$('#location-select').val()){
                            $('#bay-select').html(' ');
                            $('#bay-select').append($('<option>', {
                                value: '',
                                text:  '-'
                            }));
                            for(let j=0; j < response[i]['bays'].length; j++){
                                $('#bay-select').append($('<option>', {
                                    value: response[i]['bays'][j]['id'],
                                    text:  response[i]['bays'][j]['name']
                                }));
                            }
                            if(response[i]['bays'].length==1){
                            $("#bay-select").val(response[i]['bays'][0]['id']).change();
                            $("#bay-container").addClass('hidden');
                            }
                        }
                    }
                }, 'json');

            });

            // init blur next steps
            this.blurNextSteps(this.$element.find('.step:visible:first'), true, true);

            if (rr_settings['pre.reservation'] === '1') {
                this.$element.find('.rr-submit').on('click', jQuery.proxy( plugin.finalComformation, plugin ));
            } else {
                this.$element.find('.rr-submit').on('click', jQuery.proxy( plugin.singleConformation, plugin ));
            }

            this.$element.find('.rr-cancel').on('click', jQuery.proxy( plugin.cancelApp, plugin ));

            setTimeout(function() {
                jQuery(document).trigger('rr-init:completed');
            }, 1000);
        },

        selectTimes: function ($element) {
            var plugin = this;

            var bayData = plugin.$element.find('[name="bay"] > option:selected').data();
            var duration = bayData.duration;
            var slot_step = bayData.slot_step;

            var takeSlots = parseInt(duration) / parseInt(slot_step);

            if (rr_settings["label.from_to"] == "1") {
                takeSlots = 1;
            }

            var $nextSlots = $element.nextAll();

            var forSelection = [];
            forSelection.push($element);

            if (($nextSlots.length + 1) < takeSlots) {
                return false;
            }

            $element.parent().children().removeClass('selected-time');

            jQuery.each($nextSlots, function (index, elem) {
                var $elem = jQuery(elem);

                var startTime = moment($element.data('val'), 'HH:mm');
                var calculatedTime = (index + 1) * slot_step;
                var expectedTime = startTime.add(calculatedTime, 'minutes').format('HH:mm');

                if ($elem.data('val') !== expectedTime) {
                    return false;
                }

                if (index + 2 > takeSlots) {
                    return false;
                }

                if ($elem.hasClass('time-disabled')) {
                    return false;
                }

                forSelection.push($elem);
            });

            if (forSelection.length < takeSlots) {
                return false;
            }

            jQuery.each(forSelection, function (index, elem) {
                elem.addClass('selected-time');
            });

            return true;
        },

        /**
         * Check if settings are ok
         *
         * @returns {boolean}
         */
        settingsOk: function () {
            var selectOptions = this.$element.find('select').not('.custom-field');
            var errors = jQuery('<div style="border: 1px solid gray; padding: 20px;">');
            var valid = true;

            selectOptions.each(function(index, element) {
                var $el = jQuery(element);
                var options = $el.children('option');

                // <option value="">-</option>
               if (options.length === 1 && options.attr('value') == '') {
                    jQuery(document.createElement('p'))
                        .html('You need to define at least one <strong>' + $el.attr('name') + '</strong>.')
                        .appendTo(errors);

                    valid = false;
                }
            });

            if (!valid) {
                errors.prepend('<h4>Range Reserver - Settings validation:</h4>');
                errors.append('<p>There should be at least one Schedule.</p>');

                this.$element.html(errors);
            }

            return valid;
        },
        /**
         * If there is only one select option used don't need to choose
         */
        hideDefault: function () {
            var steps = this.$element.find('.step');
            var counter = 0;

            steps.each(function (index, element) {
                var select = jQuery(element).find('select').not('.custom-field');

                if (select.length < 1) {
                    return;
                }

                var options = select.children('option');

                if (options.length !== 1) {
                    return;
                }

                if (options.value !== '') {
                    jQuery(element).hide();
                    counter++;
                }
            });

            if (counter === 3) {
                this.settings.initScrollOff = true;
            }
        },
        /**
         * Find all previous options that are selected
         * @param element
         * @returns {{}}
         */
        getPrevousOptions: function (element) {
            var step = element.parents('.step');

            var options = {};

            var data_prev = step.prevAll('.step');

            data_prev.each(function (index, elem) {
                // var option = jQuery(elem).find('select,input').first();
                var input_field = jQuery(elem).find('.filter').filter('input, select');

                options[jQuery(input_field).data('c')] = input_field.val();
            });

            return options;
        },
        /**
         * Get next select option
         */
        getNextOptions: function (event) {
            var current = jQuery(event.target);

            var step = current.closest('.step');

            // blur next options
            this.blurNextSteps(step);

            // nothing selected
            if (current.val() === '') {
                return;
            }

            var options = {};

            options[current.data('c')] = current.val();

            var data_prev = step.prevAll('.step');

            data_prev.each(function (index, elem) {
                var option = jQuery(elem).find('select,input').first();

                options[jQuery(option).data('c')] = option.val();
            });
            // hidden
            this.$element.find('.step:hidden').each(function (index, elem) {
                var option = jQuery(elem).find('select,input').first();

                options[jQuery(option).data('c')] = option.val();
            });

            //only visible step
            var nextStep = step.nextAll('.step:visible:first');

            var next = jQuery(nextStep).find('select,input');

            if (next.length === 0) {
                this.blurNextSteps(nextStep);
                //nextStep.removeClass('disabled');
                return;
            }

            options.next = next.data('c');

            this.callServer(options, next);
        },
        /**
         * Standard call for select options (location, bay, lane)
         */
        callServer: function (options, next_element) {
            var plugin = this;

            options.action = 'rr_available_locations_by_time_slot';
            options.check  = rr_settings['check'];
            options.day = plugin.$element.find('.date').datepicker().val();
            options.slot = plugin.$element.find('.selected-time').data('val');
            if($('#lane-select').val()){
                $('#final-step').removeClass('hidden');
            }else if($('#bay-select').val()){
                options.location = $('#location-select').val();
                options.bay = $('#bay-select').val();
                var req = jQuery.get(rr_ajaxurl, options, function (response) {
                    for (let i=0;i<response.length;i++){
                        if(response[i]['id']==$('#location-select').val()){
                            for (let j=0;j<response[i]['bays'].length;j++){
                             if(response[i]['bays'][j]['id']==$('#bay-select').val()){
                                 $('#lane-select').html(' ');
                                 $('#lane-select').append($('<option>', {
                                     value: '',
                                     text:  '-'
                                 }));
                                 for(let k=0; k < response[i]['bays'][j]['lanes'].length; k++){
                                     $('#lane-select').append($('<option>', {
                                         value: response[i]['bays'][j]['lanes'][k]['id'],
                                         text:  response[i]['bays'][j]['lanes'][k]['name']
                                     }));
                                 }
                             }
                            }
                        }
                    }

                    // enabled
                    next_element.closest('.step').removeClass('hidden');

                    plugin.removeLoader();

                    plugin.scrollToElement(next_element.parent());
                }, 'json');
            }else if($('#location-select').val()){
                options.location = $('#location-select').val();
                var req = jQuery.get(rr_ajaxurl, options, function (response) {
                    next_element.empty();

                    for (let i=0;i<response.length;i++){
                        if(response[i]['id']==$('#location-select').val()){
                            $('#bay-select').html(' ');
                            $('#bay-select').append($('<option>', {
                                value: '',
                                text:  '-'
                            }));
                            for(let j=0; j < response[i]['bays'].length; j++){
                                $('#bay-select').append($('<option>', {
                                    value: response[i]['bays'][j]['id'],
                                    text:  response[i]['bays'][j]['name']
                                }));
                            }
                            if(response[i]['bays'].length==1){
                            $("#bay-select").val(response[i]['bays'][0]['id']).change();
                            $("#bay-container").addClass('hidden');
                            }
                        }
                    }
                }, 'json');
            }


        },
        placeLoader: function ($element) {
            if (++this.settings.ajaxCount !== 1) {
                return;
            }

            var width = $element.width();
            var height = $element.height();
            jQuery('#rr-loader').prependTo($element);
            jQuery('#rr-loader').css({
                'width': width,
                'height': height
            });
            jQuery('#rr-loader').show();
        },
        removeLoader: function () {
            if (--this.settings.ajaxCount > 1) {
                return;
            }

            this.settings.ajaxCount = 0;

            jQuery('#rr-loader').hide();
        },
        getCurrentStatus: function () {
            var options = jQuery(this.element).find('select').not('.custom-field');
        },
        blurNextSteps: function (current, dontScroll, initialCall) {

            // check if there is scroll param
            dontScroll = dontScroll || false;

            initialCall = initialCall || false;

            current.removeClass('disabled');

            var nextSteps = current.nextAll('.step:visible');

            var nextParentSteps = current.parent().nextAll('.step:visible');

            jQuery.merge(nextSteps, nextParentSteps);
            // find all next steps in second column

            nextSteps.each(function (index, element) {
                /*jQuery(element).addClass('disabled');*/
            });

            // if next step is calendar
            if (current.hasClass('calendar')) {

                var calendar = this.$element.find('.date');

                // refresh calendar
                calendar.datepicker("refresh");

                // skip auto select date if
                if (!initialCall || rr_settings.cal_auto_select !== '0') {
                    this.selectChange();
                }

                if (!dontScroll) {
                    this.scrollToElement(calendar);
                }
            }
        },
        /**
         * Change of date - datepicker
         */
        dateChange: function (dateString, calendar) {
            var plugin = this, next_element, calendarEl;

            calendarEl = jQuery(calendar.dpDiv).parents('.date');

            if (plugin.settings.currentDate === dateString && calendarEl.find('.time-row').length > 0) {
                calendarEl.find('.time-row').remove();
                return;
            }

            plugin.settings.currentDate = dateString;

       //     calendarEl.parent().next().addClass('disabled');

            var options = this.getPrevousOptions(calendarEl);

            options.action = 'rr_date_selected';
            options.date   = dateString;
            options.check  = rr_settings['check'];

            this.placeLoader(calendarEl);

            var req = jQuery.get(rr_ajaxurl, options, function (response) {

                next_element = jQuery(document.createElement('div'))
                    .addClass('time well well-lg');

                var fromTo = rr_settings["label.from_to"] == "1";
                var classAMPM = (rr_settings["time_format"] == "am-pm") ? ' am-pm' : '';

                if (fromTo) {
                    next_element.addClass('time well well-lg col-50');
                }

                // sort response by value 11:00, 12:00, 13:00...
                response.sort(function (a, b) {
                    var a1 = a.value, b1 = b.value;

                    if (a1 == b1) {
                        return 0;
                    }

                    return a1 > b1 ? 1 : -1;
                });


                // TR > TD WITH TIME SLOTS
                jQuery.each(response, function (index, element) {
                    var selectLabel = fromTo ? element.show + ' - ' + element.ends : element.show;

                    if (element.count > 0) {
                        // show remaining slots or not
                        if (rr_settings['show_remaining_slots'] === '1') {
                            next_element.append('<a href="#" class="time-value slots' + classAMPM + '" data-duration="' + element.duration + '" data-val="' + element.value + '">' + selectLabel + ' (' + element.count + ')</a>');
                        } else {
                            next_element.append('<a href="#" class="time-value' + classAMPM + '" data-duration="' + element.duration + '"  data-val="' + element.value + '">' + selectLabel + '</a>');
                        }
                    } else {
                        if (rr_settings['show_remaining_slots'] === '1') {
                            next_element.append('<a class="time-disabled slots' + classAMPM + '">' + selectLabel + ' (0)</a>');
                        } else {
                            next_element.append('<a class="time-disabled' + classAMPM + '">' + selectLabel + '</a>');
                        }
                    }
                });

                if (response.length === 0) {
                    next_element.html('<p class="time-message">' + rr_settings['trans.please-select-new-date'] + '</p>');
                }

                // if we have column that shows week number then it is 8
                var colSpan = rr_settings.show_week === '1' ? 8 : 7;

                var newRow = jQuery(document.createElement('tr'))
                    .addClass('time-row')
                    .append('<td colspan="' + colSpan +'" />');

                newRow.find('td').append(next_element);

                jQuery(calendar.dpDiv).find('.ui-datepicker-current-day').closest('tr').after(newRow);

                // enabled
                next_element.parent().removeClass('disabled');

                if (!plugin.settings.initScrollOff) {
                    next_element.find('.time-value:first').focus();
                } else {
                    plugin.settings.initScrollOff = false;
                }

            }, 'json');

            req.always(function () {
                plugin.refreshData(plugin.settings.store);
                plugin.removeLoader();
            });

            // in case of failed ajax request
            req.fail(function(xhr, status) {

                if (xhr.status === 403) {
                    alert(rr_settings['trans.nonce-expired']);
                }

                if (xhr.status === 404) {
                    alert(rr_settings['trans.ajax-call-not-available']);
                }

                if (xhr.status === 500) {
                    alert(rr_settings['trans.internal-error']);
                }

                plugin.removeLoader();
            });
        },
        /**
         * Change month in calendar
         *
         * @param month
         * @param year
         */
        selectChange: function (month, year) {
            var self = this;
            self.placeLoader(self.$element.find('.calendar'));

            var simulateClick = false;

            if (typeof month === 'undefined' || typeof year === 'undefined') {

                var $firstDay = this.$element.find('[data-handler="selectDay"]:first');
                month = parseInt($firstDay.data('month')) + 1;
                year = $firstDay.data('year');
            }

            simulateClick = true;

            // check is all filled
            if (this.checkStatus()) {
                var selects = this.$element.find('select').not('.custom-field');

                var fields = selects.serializeArray();

                fields.push({'name': 'action', 'value': 'rr_available_days'});
                fields.push({'name': 'month', 'value': month});
                fields.push({'name': 'year', 'value': year});
                fields.push({'name': 'location', 'value': $('#location-select').val()});
                fields.push({'name': 'check', 'value': rr_settings['check']});

                var req = jQuery.get(rr_ajaxurl, fields, function (result) {
                    self.settings.store = result;
                    self.refreshData(result);

                    // simulate click for current date if there is one on calendar
                    if (simulateClick) {
                        // current day TD
                        var $cDay = self.$element.find('.ui-datepicker-current-day');

                        // it's free day after refresh
                        if ($cDay.hasClass('free')) {
                            // but only if auto select is off
                            if (rr_settings.cal_auto_select !== '0') {
                                $cDay.click();
                            }
                        } else {
                            // remove time slots row
                            self.$element.find('.time-row').remove();
                        }
                    }
                }, 'json');

                req.fail(function (xhr, status) {
                    if (xhr.status === 403) {
                        alert(rr_settings['trans.nonce-expired']);
                    }

                    if (xhr.status === 404) {
                        alert(rr_settings['trans.ajax-call-not-available']);
                    }

                    if (xhr.status === 500) {
                        alert(rr_settings['trans.internal-error']);
                    }

                    plugin.removeLoader();
                });
            }
        },
        /**
         * Refresh table cells
         * @param data
         */
        refreshData: function (data) {

            var datepicker = this.$element.find('.date');

            jQuery.each(data, function (key, status) {
                var $td = datepicker.find('.' + key);

                // remove all class and leave just date 2020-01-01
                $td.removeClass('free');
                $td.removeClass('busy');
                $td.removeClass('no-slots');

                $td.addClass(status);
            });

            this.removeLoader();
        },
        /**
         * Is everything selected
         * @return {boolean} Is ready for sending data
         */
        checkStatus: function () {
            var selects = this.$element.find('select').not('.custom-field');

            var isComplete = true;

            selects.each(function (index, element) {
                isComplete = isComplete && jQuery(element).val() !== '';
            });

            return isComplete;
        },
        /**
         * Appointment information - before user add personal
         * information
         */
        appSelected: function (element) {
            var plugin = this;

            this.placeLoader(this.$element.find('.selected-time'));

            // make pre reservation
            var options = {
                location: this.$element.find('[name="location"]').val(),
                bay: this.$element.find('[name="bay"]').val(),
                lane: this.$element.find('[name="lane"]').val(),
                date: this.$element.find('.date').datepicker().val(),
                end_date: this.$element.find('.date').datepicker().val(),
                start: this.$element.find('.selected-time').data('val'),
                check: rr_settings['check'],
                action: 'rr_res_appointment'
            };

            // for booking overview
            var booking_data = {};
            booking_data.location = this.$element.find('[name="location"] > option:selected').text();
            booking_data.bay = this.$element.find('[name="bay"] > option:selected').text();
            booking_data.lane = this.$element.find('[name="lane"] > option:selected').text();
            booking_data.date = this.$element.find('.date').datepicker().val();
            booking_data.time = this.$element.find('.selected-time').data('val');
            booking_data.price = this.$element.find('[name="bay"] > option:selected').data('price');

            var format = rr_settings['date_format'] + ' ' + rr_settings['time_format'];
            booking_data.date_time = moment(booking_data.date + ' ' + booking_data.time, rr_settings['defult_detafime_format']).format(format);

            var req = jQuery.get(rr_ajaxurl, options, function (response) {
                plugin.res_app = response.id;

                plugin.$element.find('.step').addClass('disabled');
                plugin.$element.find('.final').removeClass('disabled');

                plugin.scrollToElement(plugin.$element.find('.final'));

                // set overview cancel_appointment
                var overview_content = '';

                overview_content = plugin.settings.overview_template({data: booking_data, settings: rr_settings});

                plugin.$element.find('#booking-overview').html(overview_content);

                plugin.$element.find('#rr-total-amount').on('checkout:done', function( event, checkoutId ) {
                    var paypal_input = plugin.$element.find('#paypal_transaction_id');

                    if (paypal_input.length == 0) {
                        paypal_input = jQuery('<input id="paypal_transaction_id" class="custom-field" name="paypal_transaction_id" type="hidden"/>');
                        plugin.$element.find('.final').append(paypal_input);
                    }

                    paypal_input.val(checkoutId);

                    // make final conformation
                    plugin.finalComformation(event);
                });

            }, 'json');

            req.fail(function (xhr, status) {
                if (xhr.status === 403) {
                    alert(rr_settings['trans.nonce-expired']);
                }

                if (xhr.status === 404) {
                    alert(rr_settings['trans.ajax-call-not-available']);
                }

                if (xhr.status === 500) {
                    alert(rr_settings['trans.internal-error']);
                }

                plugin.removeLoader();
            });

            req.always(jQuery.proxy(function () {
                plugin.removeLoader();
            }));
        },
        /**
         *
         * @param $form
         */
        loadPreviousFormData: function ($form) {

            if (typeof localStorage === 'undefined') {
                return;
            }

            // load data from local storage
            var options = JSON.parse(localStorage.getItem('rr-form-options'));

            if (options === null) {
                options = {};
            }

            var params = this.getJsonFromUrl();
            
            if (options == null && params == null) {
                return;
            }

            // place values inside form fields
            Object.keys(options).forEach(function (key) {
                $form.find('[name="' + key + '"]').val(options[key]);
            });

            // place values inside form fields
            Object.keys(params).forEach(function (key) {
                $form.find('[name="' + key + '"]').val(params[key]);
            });
        },

        /**
         *
         * @param options
         */
        storeFormData: function (options) {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('rr-form-options', JSON.stringify(options));
            }
        },

        /**
         * Comform appointment
         */
        finalComformation: function (event) {
            event.preventDefault();

            var plugin = this;

            var form = this.$element.find('form');

            if (!form.valid()) {
                return;
            }

            this.$element.find('.rr-submit').prop('disabled', true);

            // make pre reservation
            var options = {
                id: this.res_app,
                check: rr_settings['check']
            };

            this.$element.find('.custom-field').not('.dummy').each(function (index, element) {
                var name = jQuery(element).attr('name');
                options[name] = jQuery(element).val();
            });

            options.action = 'rr_final_appointment';

            var req = jQuery.get(rr_ajaxurl, options, function (response) {
                // store values from form
                plugin.storeFormData(options);

                // disable fields
                plugin.$element.find('.rr-submit').hide();
                plugin.$element.find('.rr-cancel').hide();
                plugin.$element.find('#paypal-button').hide();
                plugin.$element.find('.final').append('<h3>' + rr_settings['trans.done_message'] + '</h3>');
                plugin.$element.find('form').find('input,select,textarea').prop('disabled', true);
                plugin.$element.find('.calendar').addClass('disabled');
                plugin.$element.find('.g-recaptcha').remove();

                plugin.triggerEvent();

                var redirected = false;

                // if there is redirect do that
                if (rr_settings['advance.redirect'] !== '') {
                    var data = JSON.parse(rr_settings['advance.redirect']);
                    var bay = plugin.$element.find('[name="bay"]').val();

                    var redirect = data.find(function(el) {
                        return el.bay === bay;
                    });

                    if (redirect) {
                        redirected = true;
                        setTimeout(function () {
                            window.location.href = redirect.url;
                        }, 2000);
                    }
                }

                // if there is redirect do that
                if (rr_settings['submit.redirect'] !== '' && redirected === false) {
                    setTimeout(function () {
                        window.location.href = rr_settings['submit.redirect'];
                    }, 2000);
                }
            }, 'json')
            .fail(jQuery.proxy(function (response, status, error) {
                alert(response.responseJSON.message);
                this.$element.find('.rr-submit').prop('disabled', false);
            }, plugin));
        },

        /**
         * Checkout process
         * @param event
         */
        singleConformation: function (event) {
            if (typeof event !== 'undefined') {
                event.preventDefault();
            }

            var plugin = this;

            var form = this.$element.find('form');

            if (!form.valid()) {
                return;
            }

            this.$element.find('.rr-submit').prop('disabled', true);

            // make pre reservation
            var options = {
                location: $('#location-select').val(),
                bay: $('#bay-select').val(),
                lane: $('#lane-select').val(),
                date: this.$element.find('.date').datepicker().val(),
                end_date: this.$element.find('.date').datepicker().val(),
                start: this.$element.find('.selected-time').data('val'),
                check: rr_settings['check'],
                action: 'rr_res_appointment'
            };

            if (this.$element.find('.g-recaptcha-response').length === 1) {
                options.captcha = this.$element.find('.g-recaptcha-response').val();
            }

            // recaptcha v3
            if (rr_settings['captcha3.site-key'] && grecaptcha) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(rr_settings['captcha3.site-key'], { action: 'submit' }).then(function(token) {
                        options.captcha = token;

                        jQuery.get(rr_ajaxurl, options, function (response) {
                            plugin.res_app = response.id;

                            plugin.finalComformation(event);
                        }, 'json')
                            .fail(jQuery.proxy(function (response) {
                                alert(response.responseJSON.message);
                                this.$element.find('.rr-submit').prop('disabled', false);
                            }, plugin))
                            .always(jQuery.proxy(function () {
                                plugin.removeLoader();
                            }, plugin));
                    });
                });

                return;
            }


            // simple call
            jQuery.get(rr_ajaxurl, options, function (response) {
                plugin.res_app = response.id;

                plugin.finalComformation(event);
            }, 'json')
            .fail(jQuery.proxy(function (response) {
                alert(response.responseJSON.message);
                this.$element.find('.rr-submit').prop('disabled', false);
            }, plugin))
            .always(jQuery.proxy(function () {
                plugin.removeLoader();
            }, plugin));
        },
        /**
         * Event when new appointment is booked
         */
        triggerEvent: function () {
            var plugin = this;
            var booking_data = {};
            booking_data.location = plugin.$element.find('[name="location"] > option:selected').text();
            booking_data.bay = plugin.$element.find('[name="bay"] > option:selected').text();
            booking_data.lane = plugin.$element.find('[name="lane"] > option:selected').text();
            booking_data.date = plugin.$element.find('.date').datepicker().val();
            booking_data.time = plugin.$element.find('.selected-time').data('val');
            booking_data.price = plugin.$element.find('[name="bay"] > option:selected').data('price');

            // Create the event.
            var event = new CustomEvent('rangeappnewappointment', { detail: booking_data });

            // send event to document
            document.dispatchEvent(event);
        },

        /**
         * Event when customer select time slot
         */
        triggerSlotSelectEvent: function () {
            // Create the event.
            var event = new Event('rangeappslotselect');

            // send event to document
            document.dispatchEvent(event);
        },
        /**
         * Cancel appointment
         */
        cancelApp: function (event) {
            event.preventDefault();
            var plugin = this;

            if (rr_settings['pre.reservation'] === '0') {
                plugin.chooseStep();
                plugin.res_app = null;
                this.$element.find('.step:not(.final)').prevAll('.step').removeClass('disabled');
                return false;
            }

            this.$element.find('.final').addClass('disabled');
            this.$element.find('.step:not(.final)').prevAll('.step').removeClass('disabled');

            var options = {
                id: this.res_app,
                check: rr_settings['check'],
                action: 'rr_cancel_appointment'
            };

            jQuery.get(rr_ajaxurl, options, function (response) {
                if (response.data) {
                    // remove selected time
                    plugin.$element.find('.time').find('.selected-time').removeClass('selected-time');

                    //plugin.scrollToElement(plugin.$element.find('.date'));
                    plugin.chooseStep();
                    plugin.res_app = null;

                }
            }, 'json');
        },
        chooseStep: function () {
            var plugin = this;
            var $temp;

            // if there i advance redirect do that
            if (rr_settings['advance_cancel.redirect'] !== '') {
                var data = JSON.parse(rr_settings['advance_cancel.redirect']);
                var bay = plugin.$element.find('[name="bay"]').val();

                var redirect = data.find(function(el) {
                    return el.bay === bay;
                });

                if (redirect) {
                    setTimeout(function () {
                        window.location.href = redirect.url;
                    }, 2000);
                }
                return;
            }

            switch (rr_settings['cancel.scroll']) {
                case 'calendar':
                    plugin.scrollToElement(plugin.$element.find('.date'));
                    break;
                case 'lane' :
                    $temp = plugin.$element.find('[name="lane"]');
                    $temp.val('');
                    $temp.change();
                    $temp.closest('.step').nextAll('.step').find('select').val('');
                    this.$element.find('.time-row').remove();
                    plugin.scrollToElement($temp);
                    break;
                case 'bay' :
                    $temp = plugin.$element.find('[name="bay"]');
                    $temp.val('');
                    $temp.change();
                    $temp.closest('.step').nextAll('.step').find('select').val('');
                    this.$element.find('.time-row').remove();
                    plugin.scrollToElement($temp);
                    break;
                case 'location' :
                    $temp = plugin.$element.find('[name="location"]');
                    $temp.val('');
                    $temp.change();
                    $temp.closest('.step').nextAll('.step').find('select').val('');
                    this.$element.find('.time-row').remove();
                    plugin.scrollToElement($temp);
                    break;
                case 'pagetop':
                    break;
            }
        },
        scrollToElement: function (element) {
            if (rr_settings.scroll_off === 'true') {
                return;
            }

            jQuery('html, body').animate({
                scrollTop: ( element.offset().top - 20 )
            }, 500);
        },

        getJsonFromUrl: function() {
            var query = location.search.substr(1);
            var result = {};

            query.split("&").forEach(function(part) {
                var item = part.split("=");
                result[item[0]] = decodeURIComponent(item[1]);
            });

            return result;
        },

        parsePhoneField: function ($el) {
            var code = $el.parent().find('.rr-phone-country-code-part').val();
            var number = $el.parent().find('.rr-phone-number-part').val().replace(/^0+/, '');

            $el.parent().find('.full-value').val('+' + code + number);
        }
    });

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    jQuery.fn[pluginName] = function (options) {
        this.each(function () {
            if (!jQuery.data(this, "plugin_" + pluginName)) {
                jQuery.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
        // chain jQuery functions
        return this;
    };
})(jQuery, window, document);


(function ($) {
    jQuery('.rr-bootstrap').rrBootstrap();
})(jQuery);