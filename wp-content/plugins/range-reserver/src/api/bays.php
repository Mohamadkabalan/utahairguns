<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRBays
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var RRDBModels
     */
    private $models;

    /**
     * @var RROptions
     */
    private $options;

    public function __construct($models, $options)
    {
        $this->namespace = 'range-reserver/v1';
        $this->models = $models;
        $this->options = $options;

    }

    public static function get_url()
    {
        return rest_url('range-reserver/v1/bays');
    }

    /**
     *
     */
    public function register_routes()
    {
        //read bays
        $bay = 'bays';
        register_rest_route($this->namespace, '/'.$bay, array(
            array(
                'methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_bays'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ), array(
                'methods' => WP_REST_Server::CREATABLE, 'callback' => array($this, 'add_bay'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::EDITABLE, 'callback' => array($this, 'edit_bay'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::DELETABLE, 'permission_callback' => function () {
                    return current_user_can('manage_options');
                }, 'callback' => array($this, 'delete_bay')
            )
        ));
    }
    public function get_bays()
    {
        $orderPart = $this->models->get_order_by_part('rr_bays');
        $result = $this->models->get_all_rows('rr_bays', array(), $orderPart);
        wp_send_json($result);
    }

    public static function add_bay($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['name']) && isset($params['duration'])) {
                $arr = array();
                if (isset($params['name'])) {
                    $arr['name'] = $params['name'];
                }
                if (isset($params['bay_color'])) {
                    $arr['bay_color'] = $params['bay_color'];
                }
                if (isset($params['duration'])) {
                    $arr['duration'] = $params['duration'];
                }
                if (isset($params['slot_step'])) {
                    $arr['slot_step'] = $params['slot_step'];
                }
                if (isset($params['block_before'])) {
                    $arr['block_before'] = $params['block_before'];
                }
                if (isset($params['block_after'])) {
                    $arr['block_after'] = $params['block_after'];
                }
                if (isset($params['daily_limit'])) {
                    $arr['daily_limit'] = $params['daily_limit'];
                }
                if (isset($params['price'])) {
                    $arr['price'] = $params['price'];
                }
                $wpdb->insert($wpdb->prefix.'rr_bays', $arr);

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function edit_bay($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $arr = array();
                if (isset($params['name'])) {
                    $arr['name'] = $params['name'];
                }
                if (isset($params['bay_color'])) {
                    $arr['bay_color'] = $params['bay_color'];
                }
                if (isset($params['duration'])) {
                    $arr['duration'] = $params['duration'];
                }
                if (isset($params['slot_step'])) {
                    $arr['slot_step'] = $params['slot_step'];
                }
                if (isset($params['block_before'])) {
                    $arr['block_before'] = $params['block_before'];
                }
                if (isset($params['block_after'])) {
                    $arr['block_after'] = $params['block_after'];
                }
                if (isset($params['daily_limit'])) {
                    $arr['daily_limit'] = $params['daily_limit'];
                }
                if (isset($params['price'])) {
                    $arr['price'] = $params['price'];
                }
                $wpdb->update($wpdb->prefix.'rr_bays', $arr, array('id' => $params['id']));

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function delete_bay($request)
    {

        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $wpdb->delete($wpdb->prefix.'rr_bays', array('id' => $params['id']));
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }



}