(function($) {

    var RR = {};

    

    /**
     *
     * @param time string value like 23:00
     * @returns {string}
     */
    function formatTime(time) {
        var timeFormat = rr_settings.time_format;

        if (typeof timeFormat === 'undefined') {
            return time;
        }

        var m = moment(time, ['HH:mm']);

        if (!m.isValid()) {
            return '--:--';
        }

        if (timeFormat === 'am-pm') {
            return m.format('h:mm A');
        }

        return m.format('HH:mm');
    }

    /**
     *
     * @param date
     */
    function formatDate(date) {
        var dateFormat = rr_settings.date_format;

        if (typeof dateFormat === 'undefined') {
            return date;
        }

        var m = moment(date, ['YYYY-MM-DD']);

        if (!m.isValid()) {
            return '-';
        }

        return m.format(dateFormat);
    }

    function formatDateTime(datetime) {

        if (typeof datetime === 'undefined' || datetime.length < 10) {
            return datetime;
        }

        var parts = datetime.split(' ');

        if (parts.length !== 2) {
            return datetime;
        }

        return formatDate(parts[0]) + ' ' + formatTime(parts[1]);
    }

    _.mixin({
        formatTime:formatTime,
        formatDate:formatDate,
        formatDateTime:formatDateTime
    });    Backbone.ajax = function() {
        var args = Array.prototype.slice.call(arguments, 0)[0];
        var change = {};

        if(args.type === 'PUT' || args.type === 'DELETE') {
            change.type = 'POST';
            change.url = args.url + '&_method=' + args.type;
        }

        var newArgs = _.extend(args, change);
        return Backbone.$.ajax.apply(Backbone.$, [newArgs]);
    };    /**
     * Single Appointment
     */
    RR.Appointment = Backbone.Model.extend({
        defaults : {
            location    : null,
            bay     : null,
            lane      : null,
            // name        : '',
            // email       : '',
            // phone       : '',
            date        : null,
            start       : null,
            end         : null,
            end_date    : null,
            description : null,
            status      : null,
            user        : null,
            price       : 0
        },

        url: function() { return ajaxurl+'?action=rr_appointment&id=' + encodeURIComponent(this.id) },

        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        },

        parse: function(data, options) {

            if(typeof data.start !== "undefined" && data.start != null && data.start.length === 8) {
                data.start = data.start.substring(0, 5);
            }

            if(typeof data.created !== "undefined" && data.created.length === 19) {
                data.created = data.created.substring(0, 16);
            }

            return data;
        }
    });    /**
     * Single location
     */
    RR.Location = Backbone.Model.extend({
        defaults : {
            name:"",
            address: "",
            location: "",
            cord: null
        },

        url: function() { return ajaxurl+'?action=rr_location&id=' + encodeURIComponent(this.id) },

        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        }
    });    /**
     * Bay model
     */
    RR.Bay = Backbone.Model.extend({
        defaults : {
            name:"",
            duration: 60,
            slot_step: 60,
            block_before: 0,
            block_after: 0,
            price: 10
        },
        url : function() {
            return ajaxurl+'?action=rr_bay&id=' + this.id;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        }
    });    /**
     * Bay model
     */
    RR.Lane = Backbone.Model.extend({
        defaults : {
            name:"",
            description : ""
        },
        url : function() {
            return ajaxurl+'?action=rr_lane&id=' + this.id;
        },
        toJSON : function() {
            var attrs = _.clone( this.attributes );
            return attrs;
        }
    });    /**
     * Appointments collection
     */
    RR.Appointments = Backbone.Collection.extend({
        url : ajaxurl+'?action=rr_appointments',
        model: RR.Appointment
    });    /**
     * Locations collection
     */
    RR.Locations = Backbone.Collection.extend({
        url : ajaxurl+'?action=rr_locations',
        model: RR.Location,
        cacheData: function() {
            if(typeof rrData !== 'undefined') {
                rrData.Locations = this.toJSON();
            }
        }
    });    /**
     * Bays collection
     */
    RR.Bays = Backbone.Collection.extend({
        url : ajaxurl+'?action=rr_bays',
        model: RR.Bay,
        parse: function(response) {

            return response;
        },
        cacheData: function() {
            if(typeof rrData !== 'undefined') {
                rrData.Bays = this.toJSON();
            }
        }
    });    /**
     * Lanes collection
     */
    RR.Lanes = Backbone.Collection.extend({
        url : ajaxurl+'?action=rr_lanes',
        model: RR.Lane,
        cacheData: function() {
            if(typeof rrData !== 'undefined') {
                rrData.Lanes = this.toJSON();
            }
        }
    });    /**
     * Main Admin View
     * Renders Admin tab panel
     *
     **/
    RR.MainView = Backbone.View.extend({
        el: jQuery('#wpbody-content'),

        template: _.template(jQuery("#rr-appointments-main").html()),

        events: {
            "change .filter-part input": "filterChange",
            "change .filter-part select": "filterChange",
            "click .refresh-list": "refreshList",
            "click .add-new": "addNew",

            "change #rr-filter-locations": "filterLocationChanged",
            "change #rr-filter-bays" : "filterBayChanged",
            "change #rr-sort-by" : "onSortChange",
            "change #rr-order-by" : "onSortChange",
            "click .rr-set-sort": "columnSortClick"
        },

        initialize: function () {
            jQuery.datepicker.setDefaults(jQuery.datepicker.regional[rr_settings.datepicker]);

            // Empty array of schedules
            this.collection = new RR.Appointments();

            if (typeof rrData !== 'undefined') {
                // In page cache
                this.locations = new RR.Locations(rrData.Locations);
                this.bays = new RR.Bays(rrData.Bays);
                this.lanes = new RR.Lanes(rrData.Lanes);
            } else {
                // Get from server
                this.locations = new RR.Locations();
                this.bays = new RR.Bays();
                this.lanes = new RR.Lanes();

                this.locations.fetch();
                this.bays.fetch();
                this.lanes.fetch();
            }

            this.render();

            this.setDefaults();

            // Bind the reset event
            this.collection.bind("reset sort", this.showRows, this);

            // Get data from server
            // this.collection.fetch( {reset:true} );

            var period = localStorage.getItem('rr-appointments-period');

            if (period) {
                this.$el.find('#rr-period')
                    .val(period)
                    .change();

                return;
            }

            this.filterChange();
        },

        /**
         * Set defaults if there are one
         */
        setDefaults: function () {
            if (this.locations.length === 1) {
                this.$el.find('#rr-filter-locations').val(this.locations.at(0).get('id'));
            }

            if (this.bays.length === 1) {
                this.$el.find('#rr-filter-bays').val(this.bays.at(0).get('id'));
            }

            if (this.lanes.length === 1) {
                this.$el.find('#rr-filter-lanes').val(this.lanes.at(0).get('id'));
            }
        },

        render: function () {
            this.$el.empty();

            this.$el.html(this.template({cache: rrData}));

            // From datepicker
            this.$el.find('#rr-filter-from').datepicker({
                dateFormat: jQuery.datepicker.regional[rr_settings.datepicker].dateFormat
            });

            this.$el.find('#rr-filter-from').datepicker('setDate', this.getMonday(new Date()));

            // To datepicker
            this.$el.find('#rr-filter-to').datepicker({
                dateFormat: jQuery.datepicker.regional[rr_settings.datepicker].dateFormat
            });

            this.$el.find('#rr-filter-to').datepicker('setDate', this.getSunday(new Date()));

            this.showRows();

            return this;
        },

        showRows: function () {
            var self = this; // so you can use this inside the each function

            var row_container = self.$el.find("#rr-appointments");

            row_container.empty();

            this.collection.each(function (appointment) { // iterate through the collection
                var appointmentView = new RR.AppointmentView({
                    model: appointment
                });

                appointmentView.setData(
                    self.locations,
                    self.bays,
                    self.lanes
                );

                appointmentView.render();

                row_container.append(appointmentView.$el);
            });

            this.showMessage('');
        },

        // get current Filter
        getFilter: function () {
            var filters = this.$el.find('input, select');

            var filter = {};

            jQuery.each(filters, function (index, elem) {
                var value = jQuery(elem).val();
                var col = jQuery(elem).data('c');

                if (value !== '') {

                    if (col === 'from') {
                        value = moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD');
                    } else if (col === 'to') {
                        value = moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD');
                    }

                    filter[col] = value;
                }
            });

            return filter;
        },

        // Filter has changed
        filterChange: function (e) {
            if (typeof e !== 'undefined' && jQuery(e.currentTarget).is('#rr-period')) {
                var selected = jQuery(e.currentTarget).val();

                localStorage.setItem('rr-appointments-period', selected);

                switch (selected) {
                    case 'week':
                        this.setThisWeekPeriod();
                        break;
                    case 'month':
                        this.setThisMonthPeriod();
                        break;
                    case 'today':
                        this.setThisDayPeriod();
                        break;
                    case 'tomorrow':
                        this.setNextDayPeriod();
                        break;
                    case '30d':
                        this.setLastXPeriod(30);
                        break;
                    case '7d':
                        this.setLastXPeriod(7);
                        break;
                    default:
                        return;
                }
            }

            var filter = this.getFilter();
            var that = this;

            this.showMessage('Loading table...', true);

            this.collection.fetch({data: jQuery.param(filter), reset: true}, {
                error: function (response) {
                    that.showMessage('');
                    alert('Error, try refresh again.');
                }
            });
        },

        /**
         *
         */
        filterLocationChanged: function() {
            var location = this.$el.find('#rr-filter-locations').val();

            // enabled all the fields
            this.$el.find('#rr-filter-bays').children().prop("disabled", false).show();
            this.$el.find('#rr-filter-lanes').children().prop("disabled", false).show();

            if (location === '') {
                return;
            }

            var bays = [];
            var lanes = [];

            jQuery.each(rr_schedules, function(index, schedule) {
                if (schedule.location === location) {
                    if (_.indexOf(bays, schedule.bay) === -1 ) {
                        bays.push(schedule.bay);
                    }

                    if (_.indexOf(lanes, schedule.lane) === -1 ) {
                        lanes.push(schedule.lane);
                    }
                }
            });

            this.$el.find('#rr-filter-bays').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(bays, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });

            this.$el.find('#rr-filter-lanes').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(lanes, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        filterBayChanged: function() {
            var location = this.$el.find('#rr-filter-locations').val();
            var bay = this.$el.find('#rr-filter-bays').val();

            // enabled all the fields
            this.$el.find('#rr-filter-lanes').children().prop("disabled", false).show();

            var lanes = [];

            jQuery.each(rr_schedules, function(index, schedule) {
                if (schedule.location === location && schedule.bay === bay) {
                    if (_.indexOf(lanes, schedule.lane) === -1 ) {
                        lanes.push(schedule.lane);
                    }
                }
            });

            this.$el.find('#rr-filter-lanes').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(lanes, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        addNew: function (e) {
            e.preventDefault();

            var appointment = new RR.Appointment();

            var location = this.$el.find('#rr-filter-locations').val();
            var bay = this.$el.find('#rr-filter-bays').val();
            var lane = this.$el.find('#rr-filter-lanes').val();

            if (location !== '') {
                appointment.set('location', location);
            }

            if (bay !== '') {
                appointment.set('bay', bay);
            }

            if (lane !== '') {
                appointment.set('lane', lane);
            }

            this.collection.add(appointment, {at: 0});

            var appointmentView = new RR.AppointmentView({
                model: appointment
            });

            appointmentView.setData(
                this.locations,
                this.bays,
                this.lanes
            );

            this.$el.find("#rr-appointments").prepend(appointmentView.$el);

            appointmentView.edit();
        },

        /**
         * Refresh list
         */
        refreshList: function (e) {
            e.preventDefault();

            this.filterChange();
        },

        getMonday: function (d) {
            d = new Date(d);
            var day = d.getDay();
            var diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
            return new Date(d.setDate(diff));
        },

        getSunday: function (d) {
            d = new Date(d);
            var day = d.getDay();
            var diff = d.getDate() + (day == 0 ? 0 : (7 - day)); // adjust when day is sunday
            return new Date(d.setDate(diff));
        },

        showMessage: function (text, hold) {
            var onHold = hold || false;

            if (onHold) {
                this.$el.find('#status-msg').text(text).show();
            } else {
                this.$el.find('#status-msg').text(text).show().delay(2000).fadeOut();
            }
        },

        setThisMonthPeriod: function () {
            var date = new Date(), y = date.getFullYear(), m = date.getMonth();
            var firstDay = new Date(y, m, 1);
            var lastDay = new Date(y, m + 1, 0);

            this.$el.find('#rr-filter-from').datepicker('setDate', firstDay);
            this.$el.find('#rr-filter-to').datepicker('setDate', lastDay);
        },

        setLastXPeriod: function (days) {
            var firstDay = new Date();
            var lastDay = new Date(firstDay.getTime()+(1000*60*60*24*days));

            this.$el.find('#rr-filter-from').datepicker('setDate', firstDay);
            this.$el.find('#rr-filter-to').datepicker('setDate', lastDay);
        },

        setThisWeekPeriod: function () {
            this.$el.find('#rr-filter-from').datepicker('setDate', this.getMonday(new Date()));
            this.$el.find('#rr-filter-to').datepicker('setDate', this.getSunday(new Date()));
        },

        setThisDayPeriod: function () {
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            this.$el.find('#rr-filter-from').datepicker('setDate', new Date());
            this.$el.find('#rr-filter-to').datepicker('setDate', tomorrow);
        },

        setNextDayPeriod: function () {
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);

            var nextDay = new Date();
            nextDay.setDate(tomorrow.getDate() + 2);

            this.$el.find('#rr-filter-from').datepicker('setDate', tomorrow);
            this.$el.find('#rr-filter-to').datepicker('setDate', nextDay);
        },

        onSortChange: function() {
            var sortBy = this.$el.find('#rr-sort-by').val();
            var orderBy = this.$el.find('#rr-order-by').val();

            this.collection.comparator = function(model) {
                if (sortBy === 'id') {
                    return (orderBy === 'DESC') ? -model.get('id') : model.get('id');
                }

                if (sortBy === 'date') {
                    var value = model.get('date') + '' + model.get('start');
                    value = value.replace(/\D/g,'');

                    return (orderBy === 'DESC') ? -value : value;
                }

                if (sortBy === 'created') {
                    var value = model.get('created');
                    value = value.replace(/\D/g,'');

                    return (orderBy === 'DESC') ? -value : value;
                }
            };

            this.collection.sort();
        },

        columnSortClick: function (e) {
            e.preventDefault();

            var key = jQuery(e.target).data('key');

            this.$el.find('#rr-sort-by').val(key).change();
        }
    });    /**
     *
     */
    RR.AppointmentView = Backbone.View.extend({

        tagName: "tr",

        // show template
        template_show : _.template( jQuery("#rr-tpl-appointment-row").html() ),

        // edit template
        template_edit : _.template( jQuery("#rr-tpl-appointment-row-edit").html() ),

        // select times template
        template_times : _.template( jQuery("#rr-tpl-appointment-times").html() ),

        template : null,

        edit_mode : false,

        events: {
            "click .btn-edit"   : "edit",
            "click .btn-clone"  : "clone",
            "dblclick"          : "edit",
            "click .btn-del"    : "removeItem",
            "click .btn-save"   : "save",
            "click .btn-cancel" : "cancel",
            "keydown input"     : "keydownEvent",
            "keydown select"    : "keydownEvent",
            "change .app-fields": "changeApp",
            "change .time-start": "setEndTimeApp",
            "change .rr-bay": "bayChange",

            "change #rr-input-locations": "locationChanged",
            "change #rr-input-bays" : "bayChanged"
        },

        initialize: function () {
            this.template = this.template_show;
        },

        render: function () {
            var self = this;

            var renderedContent = this.template( {
                row       : this.model.toJSON(),
                cache     : rrData
            } );

            jQuery(this.el).html( renderedContent );

            this.setDefaults();

            this.$el.addClass('rr-row');
            this.$el.attr('tabindex', '0');

            this.locationChanged();
            this.bayChanged();

            return this;
        },

        /**
         * Set defaults if there are one
         */
        setDefaults: function() {
            if (rrData.Locations.length === 1) {
                this.$el.find('#rr-input-locations').val(rrData.Locations[0].id);
            }

            if (rrData.Bays.length === 1) {
                this.$el.find('#rr-input-bays').val(rrData.Bays[0].id);
                this.bayChange();
            }

            if (rrData.Lanes.length === 1) {
                this.$el.find('#rr-input-lanes').val(rrData.Lanes[0].id);
            }

        },

        /**
         *
         */
        locationChanged: function() {
            var location = this.$el.find('#rr-input-locations').val();

            // enabled all the fields
            this.$el.find('#rr-input-bays').children().prop("disabled", false).show();
            this.$el.find('#rr-input-lanes').children().prop("disabled", false).show();

            if (location === '') {
                return;
            }

            var bays = [];
            var lanes = [];

            jQuery.each(rr_schedules, function(index, schedule) {
                if (schedule.location === location) {
                    if (_.indexOf(bays, schedule.bay) === -1 ) {
                        bays.push(schedule.bay);
                    }

                    if (_.indexOf(lanes, schedule.lane) === -1 ) {
                        lanes.push(schedule.lane);
                    }
                }
            });

            this.$el.find('#rr-input-bays').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(bays, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });

            this.$el.find('#rr-input-lanes').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(lanes, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        bayChanged: function() {
            var location = this.$el.find('#rr-input-locations').val();
            var bay = this.$el.find('#rr-input-bays').val();

            // enabled all the fields
            this.$el.find('#rr-input-lanes').children().prop("disabled", false).show();

            var lanes = [];

            jQuery.each(rr_schedules, function(index, schedule) {
                if (schedule.location === location && schedule.bay === bay) {
                    if (_.indexOf(lanes, schedule.lane) === -1 ) {
                        lanes.push(schedule.lane);
                    }
                }
            });

            this.$el.find('#rr-input-lanes').children().each(function(index, element) {
                var value = jQuery(element).attr('value');

                if (value === '') {
                    return;
                }

                if (_.indexOf(lanes, value) === -1 ) {
                    jQuery(element).prop('disabled', true).hide();
                }
            });
        },

        /**
         *
         */
        edit: function() {
            var self = this;

            if(this.edit_mode) {
                return;
            }

            if(this.$el.hasClass('rr-editing')) {
                return;
            }

            // Edit class
            this.$el.addClass('rr-editing');

            this.template = this.template_edit;
            this.render();

            this.$el.find('select, input').first().focus();

            // this.$el.find('[data-prop="start"]').timepicker();
            var datepickerElement = this.$el.find('[data-prop="date"]');

            datepickerElement.datepicker({
                dateFormat: jQuery.datepicker.regional[rr_settings.datepicker].dateFormat,
                minDate: 0
            });

            datepickerElement.datepicker("setDate", moment(this.model.get('date'), "YYYY-MM-DD").toDate());

            this.changeApp();

            this.edit_mode = true;
        },

        /**
         * Clone object
         * @param e
         */
        clone: function(e) {
            e.preventDefault();

            var collection = this.model.collection;
            var appointment = this.model.clone();

            appointment.unset('_id');
            appointment.unset('id');
            appointment.unset('start');
            appointment.unset('end');

            collection.add(appointment, {at: 0});

            var appointmentView = new RR.AppointmentView({
                model: appointment
            });

            appointmentView.setData(
                this.locations,
                this.bays,
                this.lanes
            );

            this.$el.closest("#rr-appointments").prepend(appointmentView.$el);

            appointmentView.edit();
        },

        save: function() {
            var appointment = this.model;
            var view = this;
            var customParams = {};

            this.$el.find('.time-start').change();

            jQuery.each(this.$el.find('input, select, textarea'), function(index, elem) {
                var $elem = jQuery(elem);

                if ($elem.data('prop') === 'date') {
                    appointment.set($elem.data('prop'), moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD'));
                    appointment.set('end_date', moment(jQuery(elem).datepicker('getDate')).format('YYYY-MM-DD'));
                } else {
                    if (!$(elem).is(':disabled')) {
                        appointment.set($elem.data('prop'), $elem.val());
                    }
                }

                if($elem.attr('name') === 'send-mail' && $elem.is(':checked')) {
                    customParams._mail = $elem.val();
                }
            });

            // Saves appointment
            appointment.save(customParams, {
                success: function(model, response) {
                    view.render();
                }
            });

            this.$el.removeClass('rr-editing');

            // show row
            this.template = this.template_show;

            this.render();

            this.edit_mode = false;
        },

        cancel: function() {
            // If is new remove model/view
            if(this.model.isNew()) {

                this.model.destroy();
                this.remove();

            } else {

                this.$el.removeClass('rr-editing');

                this.template = this.template_show;
                this.render();
            }

            this.edit_mode = false;
        },

        // Deletes model and view
        removeItem: function() {
            var view = this;

            if (!confirm('Are you sure?')) {
                return;
            }

            this.model.destroy({
                success: function(model, response) {
                    view.remove();
                }
            });
        },

        setData: function(locations, bays, lanes) {
            this.locations = locations;
            this.bays  = bays;
            this.lanes   = lanes;
        },

        //
        keydownEvent: function(e) {
            switch (e.which) {
                // esc
                case 27 :
                    this.cancel();
                break;
            }
        },

        /**
         * Change of App params
         */
        changeApp: function() {

            var fields = this.$el.find(".app-fields");
            var timeField = this.$el.find('[data-prop="start"]');

            // remove current times
            timeField.empty();

            var isComplete = true;

            var filter = {};

            jQuery.each(fields, function(index, element){
                var value = jQuery(element).val();

                filter[jQuery(element).data('prop')] = value;

                // format date field
                if (jQuery(element).data('prop') === 'date') {
                    filter['date'] = moment(jQuery(element).datepicker('getDate')).format('YYYY-MM-DD');
                }

                if(value === '') {
                    isComplete = false;
                }
            });

            if(isComplete) {
                filter.action = 'rr_open_times';
                filter.app_id = this.model.get('id');

                var that = this;

                jQuery.get(
                    ajaxurl,
                    filter,
                    function(response) {
                        if(response.length > 0) {

                            var options = that.template_times({
                                app : that.model.toJSON(),
                                times: response
                            });

                            timeField.html(options);
                            timeField.prop('disabled', false);
                        }
                }, "json");
            } else {
                timeField.prop('disabled', true);
            }
        },

        setEndTimeApp: function() {
            var start = this.$el.find('.time-start').val();
            var date = this.$el.find('.date-start').val();

            // bay duration
            var bay = this.$el.find('[name="rr-input-bays"]');
            var duration = parseInt(bay.children(':selected').data('duration'));

            var startTime = new Date(date + "T" + start);

            var newDateObj = new Date(startTime.getTime() + duration * 60000);

            var minutes = newDateObj.getMinutes();
            var hours = newDateObj.getHours();

            if(minutes.length === 1) {
                minutes = '0' + minutes;
            }

            if(hours.length === 1) {
                hours = '0' + hours;
            }
            // FIX there is time end issue here
            // this.model.set('end', hours + ":" + minutes);
            this.model.set('end', null);
        },

        bayChange: function() {
            if (!this.model.isNew()) {
                return;
            }

            var option = this.$el.find('.rr-bay').children(':selected');

            this.$el.find('.rr-price').val(option.data('price'));
        }
    });
    var mainView = new RR.MainView();

}(jQuery));