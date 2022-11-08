<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRLanes
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
        return rest_url('range-reserver/v1/lanes');
    }

    /**
     *
     */
    public function register_routes()
    {
        //read lanes
        $lane = 'lanes';
        register_rest_route($this->namespace, '/'.$lane, array(
            array(
                'methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_lanes'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ), array(
                'methods' => WP_REST_Server::CREATABLE, 'callback' => array($this, 'add_lane'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::EDITABLE, 'callback' => array($this, 'edit_lane'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::DELETABLE, 'permission_callback' => function () {
                    return current_user_can('manage_options');
                }, 'callback' => array($this, 'delete_lane')
            )
        ));
    }
    public function get_lanes()
    {
        $orderPart = $this->models->get_order_by_part('rr_lanes');
        $result = $this->models->get_all_rows('rr_lanes', array(), $orderPart);
        wp_send_json($result);
    }

    public static function add_lane($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['name'])) {
                 $arr=array();
                 $arr['name']=$params['name'];
                 if(isset($params['description'])){
                     $arr['description']=$params['description'];
                 }
                $wpdb->insert($wpdb->prefix.'rr_lanes', $arr);
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function edit_lane($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $arr = array();
                if (isset($params['name'])) {
                    $arr['name'] = $params['name'];
                }
                if (isset($params['description'])) {
                    $arr['description'] = $params['description'];
                }

                $wpdb->update($wpdb->prefix.'rr_lanes', $arr, array('id' => $params['id']));

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function delete_lane($request)
    {

        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $wpdb->delete($wpdb->prefix.'rr_lanes', array('id' => $params['id']));
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }



}