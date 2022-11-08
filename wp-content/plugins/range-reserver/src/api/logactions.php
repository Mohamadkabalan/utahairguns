<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRLogActions {
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var RRDBModels
     */
    private $db_models;

    public function __construct($db_models) {
        $this->namespace = 'range-reserver/v1';
        $this->db_models = $db_models;
    }

    /**
     *
     */
    public function register_routes() {
        $mail_log = 'mail_log';
        register_rest_route( $this->namespace, '/' . $mail_log, array(
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'clear_error_log' ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                }
            )
        ));

        $log_file = 'log_file';
        register_rest_route( $this->namespace, '/' . $log_file, array(
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'clear_log_file' ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                }
            )
        ));
    }

    public function clear_error_log() {
        $table_app = $this->db_models->get_wpdb()->prefix . 'rr_error_logs';
        $query = "DELETE FROM $table_app";
        $this->db_models->get_wpdb()->query($query);

        return __('Log records deleted', 'range-reserver');
    }

    public static function clear_error_url()
    {
        return rest_url('range-reserver/v1/mail_log');
    }

    public function clear_log_file() {
        do_action('RR_CLEAR_LOG');

        return __('Log file removed', 'range-reserver');
    }
}