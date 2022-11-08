<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRSchedules
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
        return rest_url('range-reserver/v1/schedules');
    }

    /**
     *
     */
    public function register_routes()
    {
        //read schedules
        $schedule = 'schedules';
        register_rest_route($this->namespace, '/'.$schedule, array(
            array(
                'methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_schedules'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ), array(
                'methods' => WP_REST_Server::CREATABLE, 'callback' => array($this, 'add_schedule'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::EDITABLE, 'callback' => array($this, 'edit_schedule'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::DELETABLE, 'permission_callback' => function () {
                    return current_user_can('manage_options');
                }, 'callback' => array($this, 'delete_schedule')
            )
        ));
    }
    public function get_schedules()
    {
        global $wpdb;
        $orderPart = $this->models->get_order_by_part('rr_schedules');
        $results = $this->models->get_all_rows('rr_schedules', array(), $orderPart);
        foreach ($results as $result){
            $locations= $wpdb->get_col( $wpdb->prepare( "SELECT name  from ".$wpdb->prefix."rr_locations where id=".$result->location));
            if(isset($locations[0])) {
                $location = $locations[0];
                $result->location = $location;
            }
            $bays= $wpdb->get_col( $wpdb->prepare( "SELECT name  from ".$wpdb->prefix."rr_bays where id=".$result->bay));
            if(isset($bays[0])) {
                $bay = $bays[0];
                $result->bay = $bay;
            }
            $lanes= $wpdb->get_col( $wpdb->prepare( "SELECT name  from ".$wpdb->prefix."rr_lanes where id=".$result->lane));
            if(isset($lanes[0])) {
                $lane = $lanes[0];
                $result->lane = $lane;
            }
        }
        wp_send_json($results);
    }

    public static function add_schedule($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['location']) && isset($params['bay']) && isset($params['lane'])) {
                $arr = array();
                if (isset($params['location'])) {
                    $loc= $params['location'];
                    $location_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_locations where name='$loc'"));
                    if(isset($location_ids[0])){
                        $location_id=$location_ids[0];
                        $arr['location'] = (int)$location_id;
                    }else{
                        return new WP_REST_Response(array('msg' => 'Location not found', 'code' => 500), 200);
                    }
                }
                if (isset($params['bay'])) {
                    $bay = $params['bay'];
                    $bay_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_bays where name='$bay'"));
                    if(isset($bay_ids[0])){
                        $bay_id=$bay_ids[0];
                        $arr['bay'] = (int)$bay_id;
                    }else{
                        return new WP_REST_Response(array('msg' => 'Bay not found', 'code' => 500), 200);
                    }
                }
                if (isset($params['lane'])) {
                    $lane= $params['lane'];
                    $lane_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_lanes where name='$lane'"));
                    if(isset($lane_ids[0])){
                        $lane_id=$lane_ids[0];
                        $arr['lane'] = (int)$lane_id;
                    }else{
                        return new WP_REST_Response(array('msg' => 'Lane not found', 'code' => 500), 200);
                    }
                }
                if (isset($params['day_of_week'])) {
                    $arr['day_of_week'] = $params['day_of_week'];
                }
                if (isset($params['day_from'])) {
                    $arr['day_from'] = $params['day_from'];
                }
                if (isset($params['day_to'])) {
                    $arr['day_to'] = $params['day_to'];
                }
                if (isset($params['time_from'])) {
                    $arr['time_from'] = $params['time_from'];
                }
                if (isset($params['time_to'])) {
                    $arr['time_to'] = $params['time_to'];
                }
                if (isset($params['is_working'])) {
                    $arr['is_working'] = (int)$params['is_working'];
                }
                $wpdb->insert($wpdb->prefix.'rr_schedules', $arr);
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function edit_schedule($request)
    {
        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $arr = array();
                if (isset($params['location'])) {
                    $loc= $params['location'];
                    $location_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_locations where name='$loc'"));
                    if(isset($location_ids[0])){
                        $location_id=$location_ids[0];
                        $arr['location'] = (int)$location_id;
                    }else{
                        return new WP_REST_Response(array('msg' => 'Location not found', 'code' => 500), 200);
                    }
                }
                if (isset($params['bay'])) {
                    $bay = $params['bay'];
                    $bay_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_bays where name='$bay'"));
                    if(isset($bay_ids[0])){
                        $bay_id=$bay_ids[0];
                        $arr['bay'] = (int)$bay_id;
                    }else{
                        return new WP_REST_Response(array('msg' => 'Bay not found', 'code' => 500), 200);
                    }
                }
                if (isset($params['lane'])) {
                    $lane= $params['lane'];
                    $lane_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_lanes where name='$lane'"));
                    if(isset($lane_ids[0])){
                        $lane_id=$lane_ids[0];
                        $arr['lane'] = (int)$lane_id;
                    }else{
                        return new WP_REST_Response(array('msg' => 'Lane not found', 'code' => 500), 200);
                    }
                }
                if (isset($params['day_of_week'])) {
                    $arr['day_of_week'] = $params['day_of_week'];
                }
                if (isset($params['day_from'])) {
                    $arr['day_from'] = $params['day_from'];
                }
                if (isset($params['day_to'])) {
                    $arr['day_to'] = $params['day_to'];
                }
                if (isset($params['time_from'])) {
                    $arr['time_from'] = $params['time_from'];
                }
                if (isset($params['time_to'])) {
                    $arr['time_to'] = $params['time_to'];
                }
                if (isset($params['is_working'])) {
                    $arr['is_working'] = (int)$params['is_working'];
                }
                $wpdb->update($wpdb->prefix.'rr_schedules', $arr, array('id' => $params['id']));

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function delete_schedule($request)
    {

        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $wpdb->delete($wpdb->prefix.'rr_schedules', array('id' => $params['id']));
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }



}