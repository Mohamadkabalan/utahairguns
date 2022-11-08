/**
 * Generated automated
 */
(function($) {
    /**
     * Namespace
     */
    var RRC = {};

    //?...

    var files = [
        "views/google.js",
        "views/twilio.js",
        "views/icalendar.js",
        "views/woo.js",
        "views/paypal.js",
        "views/main.js"
    ];

    for (var i = 0; i < files.length; i++) {
        include(files[i]);
    }

    //?.

    var mainView = new RRC.MainView();

    //? include("admin-router.js")

}(jQuery));