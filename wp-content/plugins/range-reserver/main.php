<?php

/**
 * Plugin Name: Range Reserver
 * Plugin URI: https://2acommerce.com/
 * Description: Simple and easy to use management system for Range Reserve Appointments and Bookings
 * Version: 1.0.0
 * Requires PHP: 5.3
 * Author: 2A Commerce
 * Author URI: https://2acommerce.com
 * Text Domain: 2A Commerce
 * Domain Path: /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'RANGE_RESERVER_VERSION', '3.9.1' );

// path for source files
define('RR_SRC_DIR', dirname(__FILE__) . '/src/');

// path for JS files
define('RR_JS_DIR', dirname(__FILE__) . '/js/');

// url for RR plugin dir
define('RR_PLUGIN_URL', plugins_url(null, __FILE__) . '/');
define('RR_PLUGIN_DIR', plugin_dir_path( __FILE__));

define( 'RR_CONNECT_VERSION', '0.15.3' );

define('RR_CONNECT_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);

define('RR_CONNECT_URL', plugins_url(null, __FILE__) . '/');




// Register the autoloader that loads everything except the Google namespace.
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if (!function_exists('rr_autoload')) {
        function rr_autoload($class)
        {
            global $rr_class_location;

            if (empty($rr_class_location)) {
                $rr_class_location = include dirname(__FILE__) . '/vendor/composer/autoload_classmap.php';
            }

            if (is_array($rr_class_location) && array_key_exists($class, $rr_class_location)) {
                require_once $rr_class_location[$class];
            }
        }
    }
    // register autoloader
    spl_autoload_register('rr_autoload');
} else {
    // PHP 5.3.0+ use composer auto loader
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Entry point
 */
class RangeReserver
{

    /**
     * DI Container
     * @var tad_DI52_Container
     */
    protected $container;

    protected $models;


    /**
     * @var RRC_Woo_Logic
     */
    protected $woo_logic;





    /**
     * Init all resources and callbacks for EAC
     */


    function __construct()
    {


        global $wpdb;

        // hooks for table structure
        register_activation_hook(__FILE__, array($this, 'registration'));
        register_uninstall_hook(__FILE__, array('RangeReserver', 'uninstall'));

        // check if there are need to update db
        add_action('plugins_loaded', array($this, 'update_db'));
        // end of table structure

        // assets
        add_action('admin_init', array($this, 'init_assets'), 1000);

        // Hook for adding admin menus
        add_action('admin_menu', array($this, 'add_menu_pages'), 1000);


        //
        add_action( 'init', array($this, 'makeplugins_endpoints_add_endpoint') );
        add_action( 'parse_query', array($this, 'makeplugins_endpoints_template_redirect') );

        add_filter('query_vars', function($vars) {
            $vars[] = 'rr_api';
            $vars[] = 'hub_challenge';
            $vars[] = 'hub_verify_token';
            return $vars;
        });



        add_action( 'woocommerce_cart_calculate_fees', array($this, 'add_user_discounts') );



        $this->woo_logic = new RRC_Woo_Logic($wpdb);
        $this->woo_logic->attach_to_hooks();

    }
    function  add_user_discounts( WC_Cart $cart ){
        //any of your rules
        $user = wp_get_current_user(); // getting & setting the current user
        $roles = ( array ) $user->roles; // obtaining the role
        $discount=0;
        foreach ($roles as $role){
            $option = get_option($role.'_role_discount');
            if($option){
                $discount=$option;
            }
        }
        if((float)$discount > 0){
            // Calculate the amount to reduce

            $discount_value = ($cart->get_subtotal() * $discount) / 100;

            $cart->add_fee( 'Discount '.$discount.'%', -$discount_value);
        }
    }

    /**
     * Set all hooks and action callbacks
     */
    public function init()
    {
        $this->init_container();

        // on register hook
        register_activation_hook(__FILE__, array($this, 'install'));

        // register uninstall hook
        register_uninstall_hook(__FILE__, array('RangeReserver', 'uninstall'));

        // register deactivation hook
        register_deactivation_hook(__FILE__, array('RangeReserver', 'remove_scheduled_event'));

        // plugin loaded
        add_action('plugins_loaded', array($this, 'update'));

        // cron
        add_action('rangeapp_hourly_event', array($this, 'delete_reservations'));
        add_action('rr_gdpr_auto_delete', array($this, 'delete_old_data'));

        // we want to check if it is link from RR mail
        add_action('init', array($this, 'url_delete_reservations'));

        add_action('rest_api_init', array($this, 'register_api'));

        // init action for mails
        /** @var RRMail $mail */
        $mail = $this->container['mail'];
        $mail->init();

        // admin panel split loading for optimization
        if (is_admin()) {
            /** @var RRAdminPanel $admin */
            $admin = $this->container['admin_panel'];
            $admin->init();
        } else {
            /** @var RRFrontend $frontend */
            $frontend = $this->container['frontend'];
            $frontend->init();

            /** @var RRFullCalendar $full_calendar */
            $full_calendar = $this->container['fullcalendar']; // not ready yet
            $full_calendar->init();

            /** @var RRUserFieldMapper $field_mapper */
            $field_mapper = $this->container['user_field_mapper'];
            $field_mapper->init();
        }

        // ajax hooks
        /** @var RRAjax $ajax */
        $ajax = $this->container['ajax'];
        $ajax->init();

        // Register API endpoints
    }

    /**
     * Init DI Container, set all bays as globals
     */
    public function init_container()
    {
        global $wpdb;

        $this->container = new tad_DI52_Container();
        $this->container['wpdb'] = $wpdb;
        $this->container['utils'] = new RRUtils();

        $this->container['options'] = function($container) {
            return new RROptions($container['wpdb']);
        };

        $this->container['table_columns'] = function ($container) {
            return new RRTableColumns();
        };

        $this->container['db_models'] = function ($container) {
            return new RRDBModels( $container['wpdb'], $container['table_columns'], $container['options']);
        };

        $this->container['slots_logic'] = function ($container) {
            return new RRSlotsLogic($container['wpdb'], $container['options']);
        };

        $this->container['datetime'] = function ($container) {
            return new RRDateTime();
        };

        $this->container['logic'] = function ($container) {
            return new RRLogic($container['wpdb'], $container['db_models'], $container['options'], $container['slots_logic']);
        };

        $this->container['install_tools'] = function ($container) {
            return new RRInstallTools( $container['wpdb'], $container['db_models'], $container['options']);
        };

        $this->container['report'] = function ($container) {
            return new RRReport($container['logic'], $container['options']);
        };

        $this->container['admin_panel'] = function ($container) {
            return new RRAdminPanel($container['options'], $container['logic'], $container['db_models'], $container['datetime'] );
        };

        $this->container['frontend'] = function ($container) {
            return new RRFrontend($container['db_models'], $container['options'], $container['datetime'], $container['utils']);
        };

        $this->container['fullcalendar'] = function ($container) {
            return new RRFullCalendar($container['db_models'], $container['logic'], $container['options'], $container['datetime']);
        };

        $this->container['ajax'] = function ($container) {
            return new RRAjax($container['db_models'], $container['options'], $container['mail'], $container['logic'], $container['report']);
        };

        $this->container['mail'] = function ($container) {
            return new RRMail($container['wpdb'], $container['db_models'], $container['logic'], $container['options'], $container['utils']);
        };

        $this->container['user_field_mapper'] = function ($container) {
            return new RRUserFieldMapper();
        };
    }

    /**
     * @return tad_DI52_Container
     */
    public function get_container()
    {
        return $this->container;
    }

    /**
     * Installation of DB
     */
    public function install()
    {
        /** @var RRInstallTools $install */
        $install = $this->container['install_tools'];

        file_put_contents('/tmp/log.txt', $install->range_app_db_version . '$' . get_option('range_app_db_version'));

        // skip update if db version are the same
        if ($install->range_app_db_version !== get_option('range_app_db_version')) {
            $install->init_db();
            $install->init_data();
        }

        wp_schedule_event(time(), 'hourly', 'rangeapp_hourly_event');
    }

    /**
     * Remove tables of Appointments plugin
     */
    public static function uninstall()
    {

        global $wpdb;

        $uninstall = new RRUninstallTools();

        $uninstall->drop_db();
        $uninstall->delete_db_version();
        $uninstall->clear_cron();


        // remove options
        $options = array();

        $options[] = 'RRC_last_cron_runtime';

        // WOO
        $options[] = 'RRC_' . RRC_Woo_Fields::PRODUCTS;
        $options[] = 'RRC_' . RRC_Woo_Fields::STATUS;


        $options[] = 'range__connect_db_version';

        foreach ($options as $option_name) {
            delete_option($option_name);
        }

    }

    /**
     * Remove cron action
     */
    public static function remove_scheduled_event()
    {
        wp_clear_scheduled_hook('rangeapp_hourly_event');
    }

    public function update()
    {
        // register domain
        $this->register_text_domain();

        // update database
        /** @var RRInstallTools $tools */
        $tools = $this->container['install_tools'];
        $tools->update();
    }

    public function register_text_domain()
    {
        load_plugin_textdomain('range-reserver', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }


    /**
     * Register all api endpoints
     */
    public function register_api()
    {
        // register API endpoints
        new RRMainApi($this->get_container()); // not ready yet
    }

    /**
     * Reserved for cron execution, url for deleting reservations
     */
    public function url_delete_reservations()
    {

        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (!empty($_GET['_rr-action']) && $_GET['_rr-action'] == 'clear_reservations') {

            // only do this when is called from localhost
            if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
                $this->delete_reservations();
                die;
            }
        }
    }

    /**
     * Delete old reservations that are not complete
     */
    public function delete_reservations()
    {
        /** @var RRDBModels $models */
        $models = $this->container['db_models'];
        $models->delete_reservations();
    }

    public function delete_old_data()
    {
        $gdpr = new RRGDPRActions($this->container['db_models']);
        $gdpr->clear_old_custom_data();
    }

    public function makeplugins_endpoints_template_redirect() {
        global $wp_query;

        // if this is not a request for json or it's not a singular object then bail
        if ( ! isset( $wp_query->query_vars['rr_api'] ) )
            return;
        // output some JSON (normally you might include a template file here)
        header( 'Content-Type: application/json' );

        if ($wp_query->query_vars['hub_verify_token'] != 'ovojetest12345') {
            echo 'Error, wrong validation token';
            exit;
        }

        echo $wp_query->query_vars['hub_challenge'];
        exit;
    }

    public function makeplugins_endpoints_add_endpoint() {
        // register a "json" endpoint to be applied to posts and pages
        //        add_rewrite_endpoint( 'api', EP_PERMALINK | EP_PAGES );

        add_rewrite_rule(
            '^rr-api',
            'index.php?rr_api=1',
            'top');
    }

    /**
     * Get protocol for Google redirect
     *
     * @return string
     */
    public static function get_protocol()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    }

    /**
     * Register assets
     */
    public function init_assets()
    {

        // report style
        wp_register_style(
            'rr-connect-css',
            RR_CONNECT_URL . 'css/admin.css'
        );

        wp_register_script(
            'rrc-connect-admin',
            RR_CONNECT_URL . 'src/js/admin.prod.js',
            array('jquery', 'backbone', 'underscore', 'thickbox'),
            RR_CONNECT_VERSION,
            true
        );

        wp_register_script(
            'jquery-ui-autocomplete',
            RR_CONNECT_URL . 'js/jquery.ui.autocomplete.min.js',
            array('jquery', 'backbone', 'underscore', 'thickbox'),
            RR_CONNECT_VERSION,
            true
        );
    }

    /**
     * CSS assets for admin page
     */
    public function add_connect_assets()
    {
        // we need auto complete
        wp_enqueue_script('jquery-ui-autocomplete');

        // script for admin page
        wp_enqueue_script('rrc-connect-admin');

        //style for admin page
        wp_enqueue_style('rr-connect-css');

        // style for thick box
        wp_enqueue_style('thickbox');

        // use existing EA assets
        wp_enqueue_style('rr-admin-css');
        wp_enqueue_style('rr-admin-awesome-css');
        wp_enqueue_style('rr-admin-fonts-css');
    }

    /**
     * Menu pages, adds Connect menu item into existing EA menu
     */
    public function add_menu_pages()
    {
        // settings
        $page_settings_suffix = add_submenu_page(
            'range__top_level',
            __('Connect [BETA]', 'range-reserver-connect'),
            __('Connect [BETA]', 'range-reserver-connect'),
            'edit_posts',
            'range__connect',
            array($this, 'connect_settings_page')
        );

        add_action('load-' . $page_settings_suffix, array($this, 'add_connect_assets'));

    }

    /**
     * Page for rendering easy page
     * @return null
     */
    public function connect_settings_page()
    {
        global $rr_app;

        $container = $rr_app->get_container();
        $this->models = $container['db_models'];

        $is_cron_running = false;
        $cron_message = '';

        try {
            $is_cron_running = $this->check_cron_status();
        } catch (Exception $e) {
            $cron_message = $e->getMessage();
        }

        require_once RR_CONNECT_ROOT . 'src/templates/admin-connect.tpl.php';
    }

    /**
     * Get google service calendar
     * @return RRCGoogle_Service_Calendar google calendar object
     */
    public function get_google_client()
    {
        $client = new RRCGoogle_Client();
        // $client->setAuthConfigFile(RR_CONNECT_ROOT . 'client_secret.json');
        $client->setClientId(get_option('RRC_' . RRCGoogleFields::CLIENT_ID));
        $client->setClientSecret(get_option('RRC_' . RRCGoogleFields::CLIENT_SECRET));

        $client->addScope(RRCGoogle_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');

        $token = get_option('RRC_DEFAULT_GOOGLE_TOKEN', null);

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {

            // $client->authenticate();
            $NewAccessToken = json_decode($client->getAccessToken());
            $client->refreshToken($NewAccessToken->refresh_token);

            // update new token
            update_option('RRC_DEFAULT_GOOGLE_TOKEN', $client->getAccessToken());
        }

        $service = new RRCGoogle_Service_Calendar($client);

        return $service;
    }

    /**
     * Create Database structure
     */
    public function delta_db()
    {
        global $wpdb;

        // get table prefix
        $table_prefix = $wpdb->prefix;
        // default collate
        $charset_collate = $wpdb->get_charset_collate();


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($table_query);
    }

    /**
     * Latest DELTA DB
     */
    public function delta_db_82()
    {
        global $wpdb;

        // get table prefix
        $table_prefix = $wpdb->prefix;
        // default collate
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    }

    public function registration()
    {
        $this->update_db();

        $this->makeplugins_endpoints_add_endpoint();
        // flush rewrite rules - only do this on activation as anything more frequent is bad!
        flush_rewrite_rules();
    }

    /**
     * Update DB
     */
    public function update_db()
    {
        global $wpdb;

        $version = get_option('range__connect_db_version', '0');
        $latest_version = '0.10.6';

        // check for latest version if it current break
        if ($version == $latest_version) {
            return;
        }

        if (version_compare($version, '0.1', '<')) {
            $this->delta_db();
            $version = '0.1';
        }

        if (version_compare($version, '0.1.1', '<')) {
            $version = '0.1.1';
        }

        if (version_compare($version, '0.5.0', '<')) {

            $this->delta_db();
            $version = '0.5.0';
        }


        if (version_compare($version, '0.8.3', '<')) {

            $this->delta_db_82();

            $version = '0.8.3';
        }

        if (version_compare($version, '0.8.4', '<')) {
            $table_prefix = $wpdb->prefix;
            $wpdb->query("UPDATE {$table_prefix}rr_meta_fields SET slug='paypal-transaction-id' WHERE slug='paypal_transaction_id'");

            $version = '0.8.4';
        }


        if (version_compare($version, '0.10.2', '<')) {
            $this->delta_db_82();

            $version = '0.10.2';
        }

        if (version_compare($version, '0.10.4', '<')) {
            $this->delta_db_82();

            $version = '0.10.4';
        }


        update_option('range__connect_db_version', $version);
    }
}

/**
 * INIT Range Reserver
 */

$rr_app = new RangeReserver;
$rr_app->init();
WPFront_User_Role_Editor::init();

class WPFront_User_Role_Editor {

    const VERSION = '3.2.1.11184';
    const PLUGIN_SLUG = 'wpfront-user-role-editor';

    public static $instance = null;
    public $plugin_url = null;
    public $plugin_dir = null;
    public $includes_dir = null;
    public $plugin_basename = null;
    public $plugin_file = null;
    public $parent_menu_slug = null;

    public function __construct() {
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->includes_dir = trailingslashit($this->plugin_dir . 'includes');
        $this->plugin_basename = plugin_basename(__FILE__);
        $this->plugin_file = __FILE__;
    }

    /**
     * Singleton instance.
     *
     * @return WPFront_User_Role_Editor
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new WPFront_User_Role_Editor();
        }

        return self::$instance;
    }

    /**
     * Hooks into plugins_loaded.
     * Loads controller files and fires wpfront_ure_init.
     */
    public static function init() {
        add_action('plugins_loaded', array(self::instance(), 'plugins_loaded'));
        self::instance()->includes();
        add_action('admin_enqueue_scripts', array(self::instance(), 'admin_enqueue_styles'));
    }

    public function plugins_loaded() {
        load_plugin_textdomain('wpfront-user-role-editor', false, basename($this->plugin_dir) . '/languages/');
    }

    /**
     * Loads controller files.
     */
    public function includes() {
        require_once $this->includes_dir . 'class-uninstall.php';
        require_once $this->includes_dir . 'class-roles-helper.php';
        require_once $this->includes_dir . 'class-utils.php';
        require_once $this->includes_dir . 'class-cache.php';
        require_once $this->includes_dir . 'class-entity.php';
        require_once $this->includes_dir . 'class-controller.php';
        require_once $this->includes_dir . 'settings/class-options.php';
        require_once $this->includes_dir . 'class-debug.php';
        require_once $this->includes_dir . 'users/class-assign-migrate.php';
        require_once $this->includes_dir . 'users/class-user-profile.php';
        require_once $this->includes_dir . 'roles/class-roles-list.php';
        require_once $this->includes_dir . 'roles/class-role-add-edit.php';
        require_once $this->includes_dir . 'restore/class-restore.php';
        /*require_once $this->includes_dir . 'login-redirect/class-login-redirect.php';*/
        require_once $this->includes_dir . 'bulk-edit/class-bulk-edit.php';
        require_once $this->includes_dir . 'add-remove-cap/class-add-remove-cap.php';
        require_once $this->includes_dir . 'nav-menu/class-nav-menu-permissions.php';
        require_once $this->includes_dir . 'widget/class-widget-permissions.php';
        require_once $this->includes_dir . 'users/class-user-permissions.php';
        require_once $this->includes_dir . 'media/class-media-permissions.php';
        require_once $this->includes_dir . 'shortcodes/class-shortcodes.php';
        /*        require_once $this->includes_dir . 'post-type/class-post-type.php';
                require_once $this->includes_dir . 'taxonomies/class-taxonomies.php';*/
        require_once $this->includes_dir . 'wp/includes.php';
        require_once $this->includes_dir . 'go-pro/class-go-pro.php';

        require_once $this->includes_dir . 'integration/plugins/class-wpfront-user-role-editor-plugin-integration.php';


        if (file_exists($this->includes_dir . 'ppro/includes.php')) {
            require_once $this->includes_dir . 'ppro/includes.php';
        }

        if (file_exists($this->includes_dir . 'bpro/includes.php')) {
            require_once $this->includes_dir . 'bpro/includes.php';
        }
    }

    /**
     * Returns parent menu slug for sub menu items.
     * Also adds the parent menu on the very first call.
     *
     * @param string $submenu_slug
     * @param string $submenu_capability
     * @return string
     */
    public function get_parent_menu_slug($submenu_slug, $submenu_capability) {
        if ($this->parent_menu_slug == null) {
            $this->parent_menu_slug = $submenu_slug;
            if (is_network_admin()) {
                $position = 9;
            } else {
                $position = 69;
            }
            /*add_menu_page(__('Roles', 'wpfront-user-role-editor'), __('Roles', 'wpfront-user-role-editor'), $submenu_capability, $submenu_slug, null, 'dashicons-groups', $position);*/
        }

        return $this->parent_menu_slug;
    }

    /**
     * Returns the includes directory path.
     *
     * @return string
     */
    public function get_includes_dir() {
        return $this->includes_dir;
    }

    /**
     * Returns the plugin directory path.
     *
     * @return string
     */
    public function get_plugin_dir() {
        return $this->plugin_dir;
    }

    /**
     * Returns the url of the asset passed.
     * Passed path should be relative to assets directory.
     *
     * @param string $relativePath
     * @return string
     */
    public function get_asset_url($relativePath) {
        return $this->plugin_url . 'assets/' . $relativePath;
    }

    /**
     * Returns the plugin base name.
     *
     * @return string
     */
    public function get_plugin_basename() {
        return $this->plugin_basename;
    }

    /**
     * Returns the plugin file.
     *
     * @return string
     */
    public function get_plugin_file() {
        return $this->plugin_file;
    }

    /**
     * WP die with a permission denied message.
     */
    public function permission_denied() {
        wp_die(
            __('You do not have sufficient permissions to access this page.', 'wpfront-user-role-editor'),
            __('Access Denied', 'wpfront-user-role-editor'),
            array('response' => 403, 'back_link' => true)
        );
    }

    /**
     * Hooks into admin_enqueue_scripts and enqueues wp-admin styles.
     */
    public function admin_enqueue_styles() {
        wp_enqueue_style('wpfront-user-role-editor-admin-css', $this->get_asset_url('css/admin.css'), array(), self::VERSION);
    }

}




/**
 * END
 */
