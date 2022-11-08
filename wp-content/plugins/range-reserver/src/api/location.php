<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRLocations
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
        return rest_url('range-reserver/v1/locations');
    }

    /**
     *
     */
    public function register_routes()
    {
        //read locations
        $location = 'locations';
        register_rest_route($this->namespace, '/'.$location, array(
            array(
                'methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_locations'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ), array(
                'methods' => WP_REST_Server::CREATABLE, 'callback' => array($this, 'add_location'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::EDITABLE, 'callback' => array($this, 'edit_location'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::DELETABLE, 'permission_callback' => function () {
                    return current_user_can('manage_options');
                }, 'callback' => array($this, 'delete_loaction')
            )
        ));
    }
    public function get_locations()
    {
        $orderPart = $this->models->get_order_by_part('rr_locations');
        $result = $this->models->get_all_rows('rr_locations', array(), $orderPart);
        wp_send_json($result);
    }

    public static function add_location($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['name']) && isset($params['address']) && isset($params['location'])) {
                $wpdb->insert($wpdb->prefix.'rr_locations', array(
                    'name' => $params['name'], 'address' => $params['address'], 'location' => $params['location']
                ));

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function edit_location($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $arr = array();
                if (isset($params['address'])) {
                    $arr['address'] = $params['address'];
                }
                if (isset($params['name'])) {
                    $arr['name'] = $params['name'];
                }
                if (isset($params['location'])) {
                    $arr['location'] = $params['location'];
                }

                $wpdb->update($wpdb->prefix.'rr_locations', $arr, array('id' => $params['id']));

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function delete_loaction($request)
    {

        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $wpdb->delete($wpdb->prefix.'rr_locations', array('ID' => $params['id']));
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }



}