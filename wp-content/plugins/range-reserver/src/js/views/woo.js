/**
 * Twilio
 */
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