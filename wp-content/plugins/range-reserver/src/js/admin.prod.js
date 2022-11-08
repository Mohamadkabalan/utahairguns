/**
 * Generated automated
 */
(function($) {

    /**
     * Namespace
     */
    var RRC = {};


    RRC.WooView = Backbone.View.extend({

        template : _.template( $("#rr-tpl-woo").html() ),

        events: {
            "click .save-woo-settings" : "save_settings",
        },

        initialize: function () {
            this.render();

            this.$el.find( ".woo-product" ).each(function(index, element) {
                $(element).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: ajaxurl,
                            dataType: "json",
                            data: {
                                action: 'rrc_search_products',
                                s: request.term
                            },
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 2,
                    select: function (event, ui) {
                        var input = $(event.target);

                        var item = {
                            id: ui.item.ID,
                            name: ui.item.post_title,
                            service: input.data('service')
                        };

                        input.data('woo-product', item);
                        input.val(ui.item.post_title);

                        return false;
                    }
                })
                    .autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>")
                        .text(item.post_title)
                        .appendTo(ul);
                };
            });
        },

        /**
         * Restore state from local memory
         */
        init_state: function() {
            var $el = this.$el;
            $.each(woo_products, function(index, product) {
                var input = $el.find('[data-service="' + product.service + '"]');

                if (input.length === 1) {
                    input.data('woo-product', product);
                    input.val(product.name);
                }
            });
        },

        render: function () {
            var obj = this;
            this.$el.empty(); // clear the element to make sure you don't double your contact view

            var content = this.template( );

            this.$el.html( content );

            this.init_state();

            return this;
        },

        save_settings: function() {

            var $fields = this.$el.find('.field');

            var data = {
                'action' : 'save_woo_settings',
                'woo_products': []
            };

            // get all projects
            this.$el.find('.woo-product').each(function(index, element) {
                var product = $(element).data('woo-product');

                if (_.isObject(product)) {
                    data.woo_products.push(product);
                }
            });

            //
            jQuery.each($fields, function(index, element){
                var $el, name, value;

                $el = jQuery(element);
                name = $el.attr('name');
                value = $el.val();

                if($el.is('[type="checkbox"]')) {
                    value = 0;

                    if($el.is(':checked')) {
                        value = 1;
                    }
                }

                data[name] = value;
            });

            jQuery.post(
                ajaxurl,
                data,
                function(response){
                    alert('Saved');
                }
            );
        },

        destroy_view: function() {
            // COMPLETELY UNBIND THE VIEW
            this.undelegateEvents();

            this.$el.removeData().unbind();

            // Remove view from DOM
            this.remove();
            Backbone.View.prototype.remove.call(this);
        }
    });

    RRC.MainView = Backbone.View.extend({
        el : $('#wpbody-content'),

        template : _.template( $("#rrc-settings-main").html() ),

        events : {
            "click #tab-header li a" : "select"
        },

        initialize: function () {
            this.render();
        },

        /**
         * Render main view for whole Connect admin page
         *
         * @returns {RRC.MainView}
         */
        render: function () {

            this.$el.empty();

            this.$el.html( this.template );

            return this;
        },

        /**
         * Prepare div for inserting
         */
        addContainer: function () {

            if( this.$el.find('#tab-content').length > 0 ) {
                return;
            }

            this.$el.children('.wrap').append(
                $( document.createElement('div') )
                    .attr( 'id', 'tab-content' )
            );
        },

        /**
         * Select event on header
         *
         * @param e
         */
        select: function(e) {
            var element = $(e.target);

            this.$el.find('#tab-header li').removeClass('tab-selected');

            element.parents('li:first').addClass('tab-selected');
        },

        /**
         * Move hash
         *
         * @param hash
         */
        selectHash: function(hash) {
            if(hash === '') {
                hash = '#google';
            }

            this.$el.find('[href="' + hash + '"]').click();
        }
    });
    var mainView = new RRC.MainView();

    /**
     *
     */
    RRC.AppRouter = Backbone.Router.extend({
        current: null,
        routes: {
            "google"    : "google",
            "twilio"    : "twilio",
            "icalendar" : "icalendar",
            "woo"       : "woo",
            "paypal"    : "paypal",
            ""          : "google" // default
        },

        /**
         * Select defalt hash
         */
        initialize: function () {
            var currentHash = window.location.hash;

            mainView.selectHash(currentHash);
        },

        /**
         * Remove previous view from DOM
         */
        clearState : function() {
            if(this.current != null) {
                this.current.destroy_view();

                // FIX
                mainView.addContainer();
            }
        },

        /**
         * Set new state
         *
         * @param newState
         */
        setState: function(newState) {
            this.current = newState;
            // FIX back/forward navigation
            var hash = window.location.hash;

            if(hash === '') {
                hash = '#google';
            }

            var tab = mainView.$el.find('[href="' + hash + '"]')[0];

            mainView.select({ target : tab});

        }
    });

    // Instantiate the router
    var app_router = new RRC.AppRouter();



    // Woo settings page
    app_router.on('route:woo', function () {
        this.clearState();

        var woo = new RRC.WooView({
            el: '#tab-content'
        });

        this.setState(woo);
    });

    // Start Backbone history a necessary step for bookmarkable URL's
    Backbone.history.start();
}(jQuery));