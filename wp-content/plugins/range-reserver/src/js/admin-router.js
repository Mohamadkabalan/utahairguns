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

// Google page
app_router.on('route:google', function () {
    this.clearState();

    var google = new RRC.GoogleView({
        el: '#tab-content'
    });

    this.setState(google);
});

// Twilio settings page
app_router.on('route:twilio', function () {
    this.clearState();

    var twilio = new RRC.TwilioView({
        el: '#tab-content'
    });

    this.setState(twilio);
});

// Woo settings page
app_router.on('route:icalendar', function () {
    this.clearState();

    var icalendar = new RRC.ICalendarView({
        el: '#tab-content'
    });

    this.setState(icalendar);
});

// PayPal settings page
app_router.on('route:paypal', function () {
    this.clearState();

    var paypal = new RRC.PayPalView({
        el: '#tab-content'
    });

    this.setState(paypal);
});

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