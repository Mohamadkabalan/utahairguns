<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Admin panel
 */
class RRAdminPanel
{
    protected $compatibility_mode;

    /**
     * @var RROptions
     */
    protected $options;

    /**
     * @var RRLogic
     */
    protected $logic;

    /**
     * @var RRDBModels
     */
    protected $models;

    /**
     * @var RRDateTime
     */
    protected $datetime;

    /**
     * RRAdminPanel constructor.
     * @param RROptions $options
     * @param RRLogic $logic
     * @param RRDBModels $models
     * @param RRDateTime $datetime
     */
    function __construct($options, $logic, $models, $datetime)
    {
        $this->options = $options;
        $this->logic = $logic;
        $this->models = $models;
        $this->datetime = $datetime;
    }

    /**
     * Init action callbacks
     */
    public function init()
    {
        // Hook for adding admin menus
        add_action('admin_menu', array($this, 'add_menu_pages'));

        // Init action
        add_action('admin_init', array($this, 'init_scripts'));
        //add_action( 'admin_enqueue_scripts', array( $this, 'init' ) );
    }

    /**
     * Init of admin page
     */
    public function init_scripts()
    {
        // admin panel script
        wp_register_script(
            'rr-compatibility-mode',
            RR_PLUGIN_URL . 'js/backbone.sync.fix.js',
            array('backbone'),
            RANGE_RESERVER_VERSION,
            true
        );

        // admin panel script
        wp_register_script(
            'time-picker-i18n',
            RR_PLUGIN_URL . 'js/libs/jquery-ui-timepicker-addon-i18n.js',
            array('jquery', 'time-picker'),
            RANGE_RESERVER_VERSION,
            true
        );

        // bootstrap script
        wp_register_script(
            'rr-momentjs',
            RR_PLUGIN_URL . 'js/libs/moment.min.js',
            array(),
            RANGE_RESERVER_VERSION,
            true
        );

        // admin panel script
        wp_register_script(
            'time-picker',
            RR_PLUGIN_URL . 'js/libs/jquery-ui-timepicker-addon.js',
            array('jquery', 'jquery-ui-datepicker'),
            RANGE_RESERVER_VERSION,
            true
        );

        // admin panel script
        wp_register_script(
            'jquery-chosen',
            RR_PLUGIN_URL . 'js/libs/chosen.jquery.min.js',
            array('jquery'),
            RANGE_RESERVER_VERSION,
            true
        );

        // closure panel script
        wp_register_script(
            'rr-admin-bundle',
            RR_PLUGIN_URL . 'js/bundle.js',
            array('jquery', 'wp-api', 'wp-i18n'),
            RANGE_RESERVER_VERSION,
            true
        );

        // closure style
        wp_register_style(
            'rr-admin-bundle-css',
            RR_PLUGIN_URL . 'css/theme/main.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // admin panel script
        wp_register_script(
            'rr-settings',
            RR_PLUGIN_URL . 'js/admin.prod.js',
            array(
                'jquery',
                'rr-momentjs',
                'jquery-ui-datepicker',
                'rr-datepicker-localization',
                'time-picker',
                'backbone',
                'underscore',
                'jquery-ui-sortable',
                'jquery-chosen',
                'wp-api',
                'thickbox'
            ),
            RANGE_RESERVER_VERSION,
            true
        );

        // appointments panel script
        wp_register_script(
            'rr-appointments',
            RR_PLUGIN_URL . 'js/settings.prod.js',
            array(
                'jquery',
                'rr-momentjs',
                'jquery-ui-datepicker',
                'rr-datepicker-localization',
                'time-picker',
                'backbone',
                'underscore'
            ),
            RANGE_RESERVER_VERSION,
            true
        );

        // report panel script
        wp_register_script(
            'rr-report',
            RR_PLUGIN_URL . 'js/report.prod.js',
            array('jquery', 'time-picker', 'rr-datepicker-localization', 'backbone', 'underscore'),
            RANGE_RESERVER_VERSION,
            true
        );

        wp_register_script(
            'rr-datepicker-localization',
            RR_PLUGIN_URL . 'js/libs/jquery-ui-i18n.min.js',
            array('jquery'),
            RANGE_RESERVER_VERSION,
            true
        );

        wp_register_script(
            'rr-tinymce',
            RR_PLUGIN_URL . 'js/libs/mce.plugin.code.min.js',
            array('tinymce_js'),
            RANGE_RESERVER_VERSION,
            true
        );

        // admin style
        wp_register_style(
            'rr-admin-css',
            RR_PLUGIN_URL . 'css/admin.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // admin style
        wp_register_style(
            'jquery-chosen',
            RR_PLUGIN_URL . 'css/chosen.min.css',
            array(),
            RANGE_RESERVER_VERSION
        );


        // report style
        wp_register_style(
            'rr-report-css',
            RR_PLUGIN_URL . 'css/report.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // admin style
        wp_register_style(
            'rr-admin-awesome-css',
            RR_PLUGIN_URL . 'css/font-awesome.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // admin style
        wp_register_style(
            'time-picker',
            RR_PLUGIN_URL . 'css/jquery-ui-timepicker-addon.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        wp_register_style(
            'jquery-style',
            RR_PLUGIN_URL . 'css/jquery-ui.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // custom fonts
        wp_register_style(
            'rr-admin-fonts-css',
            RR_PLUGIN_URL . 'css/fonts.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        wp_register_script(
            'jquery-ui-autocomplete',
            RR_PLUGIN_URL . 'js/libs/jquery.ui.autocomplete.min.js',
            array('jquery', 'backbone', 'underscore', 'thickbox'),
            RANGE_RESERVER_VERSION,
            true
        );

    }

    public function user_capability_callback($default_capability, $menu_slug) {
        return apply_filters('range-reserver-user-menu-capabilities', $default_capability, $menu_slug);
    }

    /**
     * Adds required JS
     */
    public function add_settings_js()
    {
        $this->compatibility_mode = $this->options->get_option_value('compatibility.mode', 0);

        if (!empty($this->compatibility_mode)) {
            wp_enqueue_script('rr-compatibility-mode');
        }

        // we need tinyMce for WYSIWYG editor
        wp_enqueue_script('tinymce_js', includes_url( 'js/tinymce/' ) . 'wp-tinymce.php', array( 'jquery' ), false, true );
        wp_enqueue_script('rr-tinymce');
        wp_enqueue_style('rr-editor-style', includes_url('/css/editor.min.css'));

//        wp_enqueue_script( 'time-picker-i18n' );
        wp_enqueue_script('rr-settings');

        wp_enqueue_style('rr-admin-css');
        wp_enqueue_style('jquery-style');
        wp_enqueue_style('time-picker');
        wp_enqueue_style('rr-admin-awesome-css');
        wp_enqueue_style('thickbox');
        wp_enqueue_style('jquery-chosen');
        wp_enqueue_style('rr-admin-fonts-css');
        // style editor
        // we need auto complete
        wp_enqueue_script('jquery-ui-autocomplete');
    }

    /**
     * Adds required JS
     */
    public function add_appointments_js()
    {
        $this->compatibility_mode = $this->options->get_option_value('compatibility.mode', 0);

        if (!empty($this->compatibility_mode)) {
            wp_enqueue_script('rr-compatibility-mode');
        }

        wp_enqueue_script('rr-appointments');
        wp_enqueue_style('rr-admin-css');
        wp_enqueue_style('jquery-style');
        wp_enqueue_style('time-picker');
        wp_enqueue_style('rr-admin-awesome-css');
    }

    /**
     * JS for report admin page
     */
    public function add_report_js()
    {
        if (!empty($this->compatibility_mode)) {
            wp_enqueue_script('rr-compatibility-mode');
        }

        wp_enqueue_script('rr-report');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('rr-admin-awesome-css');
        wp_enqueue_style('rr-report-css');
        wp_enqueue_style('jquery-style');
        wp_enqueue_style('rr-admin-fonts-css');
    }

    /**
     * create menu structure
     */
    public function add_menu_pages()
    {
        // top_level_menu
        add_menu_page(
            'Range Reserver',
            'Range Reserver',
            'edit_posts',
            'range_app_top_level',
            null,
            'dashicons-calendar-alt',
            '10.842015'
        );

        // Rename first
        $page_app_suffix = add_submenu_page(
            'range_app_top_level',
            __('Reservations', 'range-reserver'),
            __('Reservations', 'range-reserver'),
            $this->user_capability_callback('edit_posts', 'range_app_top_level'),
            'range_app_top_level',
            array($this, 'top_level_appointments')
        );

        // locations page
        $page_location_suffix = add_submenu_page(
            'range_app_top_level',
            __('Locations', 'range-reserver'),
            '1. ' . __('Locations', 'range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_locations'),
            'range_app_locations',
            array($this, 'locations_page')
        );

        // bays
        $page_bays_suffix = add_submenu_page(
            'range_app_top_level',
            __('Bay','range-reserver'),
            '2. ' . __('Bay','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_bays'),
            'range_app_bays',
            array($this, 'bays_page')
        );

        // Lanes
        $page_lane_suffix = add_submenu_page(
            'range_app_top_level',
            __('Lane','range-reserver'),
            '3. ' . __('Lane','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_lanes'),
            'range_app_lanes',
            array($this, 'lanes_page')
        );

        // schedules
        $page_schedules_suffix = add_submenu_page(
            'range_app_top_level',
            __('Schedule','range-reserver'),
            '4. ' . __('Schedule','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_schedules'),
            'range_app_schedules',
            array($this, 'schedules_page')
        );

        // settings
        $page_settings_suffix = add_submenu_page(
            'range_app_top_level',
            __('Settings','range-reserver'),
            '5. ' . __('Settings','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_settings'),
            'range_app_settings',
            array($this, 'top_settings_menu')
        );

        // closure page
        $page_closure_suffix = add_submenu_page(
            'range_app_top_level',
            __('Tools','range-reserver'),
            '6. ' . __('Tools','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_tools'),
            'range_app_tools',
            array($this, 'tools_page')
        );

        // closure page
        $page_closure_suffix = add_submenu_page(
            'range_app_top_level',
            __('Closures','range-reserver'),
            __('Closures','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_closure'),
            'range_app_closure',
            array($this, 'closure_page')
        );

        $page_roles_suffix = add_submenu_page(
            'range_app_top_level',
            __('Roles','range-reserver'),
            __('Roles','range-reserver'),
            $this->user_capability_callback('manage_options', 'admin.php?page=wpfront-user-role-editor-all-roles'),
            'admin.php?page=wpfront-user-role-editor-all-roles',
            null
        );

        // Overview - report
/*        $page_report_suffix = add_submenu_page(
            'range_app_top_level',
            __('Reports *OLD*','range-reserver'),
            __('Reports *OLD*','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_reports'),
            'range_app_reports',
            array($this, 'reports_page')
        );*/

        // Overview - report
        $page_new_report_suffix = add_submenu_page(
            'range_app_top_level',
            __('Reports','range-reserver'),
            __('Reports','range-reserver'),
            $this->user_capability_callback('manage_options', 'range_app_new_reports'),
            'range_app_new_reports',
            array($this, 'new_reports_page')
        );

        add_action('load-' . $page_settings_suffix, array($this, 'add_settings_js'));
        add_action('load-' . $page_app_suffix, array($this, 'add_appointments_js'));

    }

    /**
     * Content of appointments admin page
     */
    public function top_level_appointments()
    {

        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        $settings = $this->options->get_options();

        $settings['date_format'] = $this->datetime->convert_to_moment_format(get_option('date_format', 'F j, Y'));

        wp_localize_script('rr-appointments', 'rr_settings', $settings);
        wp_localize_script('rr-appointments', 'rr_app_status', $this->logic->getStatus());
        wp_localize_script('rr-appointments', 'rr_schedules', $this->models->get_schedules_combinations());

        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'    => 'rangeapp_settings_help'
        , 'title'   => 'Appointments manager'
        , 'content' => '<p>Use filter for date to reduce output results for appointments. You can filter by <b>location</b>, <b>bay</b>, <b>lane</b>, <b>status</b> and <b>date</b>.</p>'
        ));

        $screen->set_help_sidebar('<a href="https://range-reserver.net/documentation/">More info!</a>');

        require_once RR_SRC_DIR . 'templates/appointments.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.sorted.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function reports_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        $settings = $this->options->get_options();
        wp_localize_script('rr-report', 'rr_settings', $settings);

        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'    => 'rangeapp_settings_help'
        , 'title'   => 'Time table'
        , 'content' => '<p>Time table report shows free slots for every location - bay - lane schedule on whole month</p>' .
                '<p>There can you see free times an how many slots are taken.</p>'
        ));

        $screen->set_help_sidebar('<a href="https://range-reserver.net/documentation/">More info!</a>');

        require_once RR_SRC_DIR . 'templates/report.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function top_settings_menu()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();
        wp_localize_script('rr-settings', 'rr_settings', $settings);

        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'    => 'rangeapp_settings_help'
        , 'title'   => 'Settings'
        , 'content' => '<p>You need to define at least one location, lane and bay! Without that widget won\'t work.</p>'
        ));

        $screen->set_help_sidebar('<a href="https://range-reserver.net/documentation/">More info!</a>');

        require_once RR_SRC_DIR . 'templates/admin.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function closure_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        load_plugin_textdomain('range-reserver', false, RR_PLUGIN_DIR  . 'languages/');

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();
        $settings['rest_url_closure'] = RRClosureActions::get_url();

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver', RR_PLUGIN_DIR  . 'languages');
        }

        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id'    => 'rangeapp_settings_help'
        , 'title'   => 'Settings'
        , 'content' => '<p>You need to define at least one location, lane and bay! Without that widget won\'t work.</p>'
        ));

        $screen->set_help_sidebar('<a href="https://range-reserver.net/documentation/">More info!</a>');

        require_once RR_SRC_DIR . 'templates/closure.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function locations_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        load_plugin_textdomain('range-reserver', false, RR_PLUGIN_DIR  . 'languages/');

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver', RR_PLUGIN_DIR  . 'languages');
        }

        require_once RR_SRC_DIR . 'templates/locations.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function lanes_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        load_plugin_textdomain('range-reserver', false, RR_PLUGIN_DIR  . 'languages/');

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver', RR_PLUGIN_DIR  . 'languages');
        }

        require_once RR_SRC_DIR . 'templates/lanes.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function bays_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        load_plugin_textdomain('range-reserver', false, RR_PLUGIN_DIR  . 'languages/');

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver', RR_PLUGIN_DIR  . 'languages');
        }

        require_once RR_SRC_DIR . 'templates/bays.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Content of top menu page
     */
    public function schedules_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        load_plugin_textdomain('range-reserver', false, RR_PLUGIN_DIR  . 'languages/');

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();
        $settings['time_format'] = $this->datetime->convert_to_moment_format(get_option('time_format', 'H:i'));
        $settings['date_format'] = $this->datetime->convert_to_moment_format(get_option('date_format', 'F j, Y'));

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver', RR_PLUGIN_DIR  . 'languages');
        }

        require_once RR_SRC_DIR . 'templates/schedules.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Tools page
     */
    public function tools_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        load_plugin_textdomain('range-reserver', false, RR_PLUGIN_DIR  . 'languages/');

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();
        $settings['rest_url_clear_log'] = RRLogActions::clear_error_url();

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver', RR_PLUGIN_DIR  . 'languages');
        }

        require_once RR_SRC_DIR . 'templates/tools.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * Tools page
     */
    public function new_reports_page()
    {
        // check if APS tags are on
        if ($this->is_asp_tags_are_on()) {
            require_once RR_SRC_DIR . 'templates/asp_tag_message.tpl.php';
            return;
        }

        wp_enqueue_style('rr-admin-bundle-css');
        wp_enqueue_script('rr-admin-bundle');

        $settings = $this->options->get_options();
        $settings['rest_url'] = get_rest_url();
        $settings['rest_url_fullcalendar'] = RRApiFullCalendar::get_url();
        $settings['export_tags_list'] = $this->models->get_all_tags_for_template();
        $settings['saved_tags_list'] = get_option('rr_excel_columns', '');

        $wpurl = get_bloginfo('wpurl');
        $url   = get_bloginfo('url');

        $settings['image_base'] = $wpurl === $url ? '' : $wpurl;
        wp_localize_script('rr-admin-bundle', 'rr_settings', $settings);

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rr-admin-bundle','range-reserver');
        }

        require_once RR_SRC_DIR . 'templates/reports.tpl.php';
        require_once RR_SRC_DIR . 'templates/inlinedata.tpl.php';
    }

    /**
     * We need to check if asp tags are turned on
     */
    public function is_asp_tags_are_on()
    {
        $aps_tags = ini_get('asp_tags');

        if (!empty($aps_tags)) {
            if (ini_set('asp_tags', '0') === false) {
                return true;
            }
        }

        return false;
    }
}