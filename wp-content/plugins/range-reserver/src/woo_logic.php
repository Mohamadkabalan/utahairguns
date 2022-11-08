<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRC_Woo_Logic
{

    /**
     * @var boolean
     */
    protected $init;

    /**
     * @var array
     */
    protected $products;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var RRDBModels
     */
    protected $models;

    /**
     * @var wpdb
     */
    private $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function init_data()
    {
        $this->products = json_decode(get_option('RRC_' . RRC_Woo_Fields::PRODUCTS, '[]'));
        $this->status = get_option('RRC_' . RRC_Woo_Fields::STATUS);

        $this->init = true;
    }

    /**
     * Attach to hooks like ajax, action call backs etc
     */
    public function attach_to_hooks()
    {
        // add ajax: rrc_search_products
        add_action( 'wp_ajax_rrc_search_products', array($this, 'ajax_search_products'));
        // save action
        add_action( 'wp_ajax_save_woo_settings', array($this, 'ajax_save_settings'));
        // add to chart hook
        add_action('rr_new_app',  array($this, 'process_appointment'), 3000, 3);

        add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'checkout_item_event'), 10, 4 );

        add_action( 'woocommerce_order_status_processing', array($this, 'status_change_to_processing'));
        add_action( 'woocommerce_order_status_completed', array($this, 'status_change_to_processing'));
        add_action( 'woocommerce_order_status_refunded', array($this, 'status_change_to_cancelled'));
        add_action( 'woocommerce_order_status_cancelled', array($this, 'status_change_to_cancelled'));

        add_action( 'woocommerce_add_order_item_meta', array($this, 'add_order_item_meta') , 10, 2);
    }

    /**
     * We are placing order item meta into from chart meta, this is call back that is handling that
     *
     * @param $item_id
     * @param $values
     */
    public function add_order_item_meta ( $item_id, $values ) {
        if ( isset( $values[ 'rr_app_id' ] ) ) {
            try {
                wc_add_order_item_meta($item_id, 'Appointment Id', $values['rr_app_id']);
                wc_add_order_item_meta($item_id, 'Date', $values['rr_date']);
                wc_add_order_item_meta($item_id, 'Start', $values['rr_start']);
                wc_add_order_item_meta($item_id, 'End', $values['rr_end']);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param $order_id
     * @throws Exception
     */
    public function status_change_to_processing($order_id)
    {
        $order = wc_get_order( $order_id );
        $items = $order->get_items();

        foreach ( $items as $item_id => $item ) {
            $rr_app_id = wc_get_order_item_meta( $item_id, 'Appointment Id', true );
            $this->change_order_status($rr_app_id, 'confirmed');
        }
    }

    /**
     * @param $order_id
     * @throws Exception
     */
    public function status_change_to_cancelled($order_id)
    {
        $order = wc_get_order( $order_id );
        $items = $order->get_items();

        foreach ( $items as $item_id => $item ) {
            $rr_app_id = wc_get_order_item_meta( $item_id, 'Appointment Id', true );
            $this->change_order_status($rr_app_id, 'canceled');
        }
    }

    public function change_order_status($app_id, $status)
    {
        global $rr_app;

        if ( empty( $app_id ) ) {
            return;
        }

        // Container from RangeReserver
        $container = null;
        $models = null;

        if (!empty($rr_app)) {
            $container = $rr_app->get_container();
            $this->models = $container['db_models'];
        }

        $data = $this->models->get_appintment_by_id($app_id);

        $table = 'rr_appointments';
        $app_fields = array('id', 'location', 'bay', 'lane', 'date', 'start', 'end', 'status', 'user', 'price');
        $app_data = array();

        foreach ($app_fields as $value) {
            if (array_key_exists($value, $data)) {
                $app_data[$value] = $data[$value];
            }
        }

        // in order to confirm status of appointment must be pending
        if ($data['status'] !== 'pending' && $status === 'confirmed') {
            return;
        }

        $app_data['status'] = $status;

        $response = $this->models->replace($table, $app_data, true);

        // trigger new appointment
        do_action('rr_new_app', $app_id, $app_data);

        // for user
        do_action('rr_user_email_notification', $app_id);

        // for admin
        do_action('rr_admin_email_notification', $app_id);
    }

    /**
     * Search for products
     */
    public function ajax_search_products()
    {
        $search = $_GET['s'];
        $args = array( 'post_type' => 'product', 'posts_per_page' => 10, 's' => $search );

        $products = get_posts( $args );

        echo json_encode($products);
        wp_die();
    }

    /**
     * Save ajax call
     */
    public function ajax_save_settings()
    {
        $status = (isset($_POST['woo_status'])) ? $_POST['woo_status'] : 0;

        update_option('RRC_' . RRC_Woo_Fields::PRODUCTS, json_encode($_POST['woo_products']));
        update_option('RRC_' . RRC_Woo_Fields::STATUS, $status);

        wp_die();
    }

    /**
     * Add to chart event
     *
     * @param int $app_id
     * @param $appointment
     * @param boolean $front_end
     * @internal param array $data
     */
    public function process_appointment($app_id, $appointment, $front_end = false)
    {
        // get global object
        if (empty($front_end)) {
            return;
        }

        if (!$this->init) {
            $this->init_data();
        }

        if (empty($this->status)) {
            return;
        }

        if (!function_exists('WC')) {
            return;
        }

        $wc = WC();

        $bay = $appointment['bay'];

        $product_id = $this->get_product_id_from_bay($bay);

        if ($product_id == null) {
            return;
        }

        if (is_null( $wc->cart )) {
            wc_load_cart();
        }

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        $date  = DateTime::createFromFormat('Y-m-d', $appointment['date']);
        $start = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['date'] . ' ' . $appointment['start']);
        $end   = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['date'] . ' ' . $appointment['end']);

        // add to cart
        $wc->cart->add_to_cart($product_id, 1, 0, array(), array(
            'rr_app_id' => $app_id,
            'rr_date'   => $date->format($date_format),
            'rr_start'  => $start->format($time_format),
            'rr_end'    => $end->format($time_format),
        ));

//        // create meta field if it not exists
//        $this->resolve_meta_field();

    }

    protected function get_product_id_from_bay($bay)
    {
        foreach ($this->products as $product) {
            if ($bay == $product->bay) {
                return $product->id;
            }
        }

        return null;
    }


    /**
     * Check if meta field exists, if not create it
     */
    protected function resolve_meta_field() {

        $table_name = $this->wpdb->prefix . 'rr_meta_fields';

        $meta_field_exists = intval($this->wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE `slug`='woo-order-id'" ));

        if ($meta_field_exists == 1) {
            return;
        }

        $data = array(
            'type'          => 'INPUT',
            'slug'          => 'woo-order-id',
            'label'         => 'WooCommerce Order ID',
            'default_value' => '',
            'validation'    => '',
            'mixed'         => '',
            'visible'       => 0,
            'required'      => 0,
            'position'      => 200
        );

        $this->wpdb->insert(
            $table_name,
            $data
        );
    }
}
