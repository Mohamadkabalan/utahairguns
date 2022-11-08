<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRClosureActions
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var RRDBModels
     */
    private $db_models;

    /**
     * @var RROptions
     */
    private $options;

    public function __construct($db_models, $options)
    {
        $this->namespace = 'range-reserver/v1';
        $this->db_models = $db_models;
        $this->options = $options;
    }

    public static function get_url()
    {
        return rest_url('range-reserver/v1/closure');
    }

    /**
     *
     */
    public function register_routes()
    {
        $closure = 'closure';
        register_rest_route($this->namespace, '/' . $closure, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_closures'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ),

        ));

        register_rest_route($this->namespace, '/' . $closure, array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_closures'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            )
        ));

        register_rest_route($this->namespace, '/add-closure', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'add_closure'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ),
        ));

        register_rest_route($this->namespace, '/edit-closure', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'edit_closure'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ),
        ));

        register_rest_route($this->namespace, '/delete-closure', array(
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_closure'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ),
        ));

    }

    public function get_closures()
    {
        $closures = $this->options->get_option_value('closures');

        $result = $closures === null ? array() : json_decode($closures);

        wp_send_json($result);
    }

    /**
     * @param WP_REST_Request $request get data from request.
     */
    public function update_closures($request)
    {

        $input = $request->get_body();
        $data = $this->process_data($input);

        $option = array(
            'rr_key'   => 'closures',
            'rr_value' => $data,
            'type'     => 'JSON_ARRAY',
        );

        $result = $this->db_models->update_option($option);

        wp_send_json(array('result' => $result));
    }

    /**
     * Checks if array of data is valid
     *
     * @param $data
     * @return false|float|int|mixed|Bays_JSON_Error|string|void
     */
    private function process_data($data)
    {
        $array = json_decode($data, true);

        if (!is_array($array)) {
            return '[]';
        }

        $result = array();
        $keys = array('id', 'title', 'tooltip', 'lanes', 'days');

        foreach ($array as $item) {
            if (!is_array($item)) {
                continue;
            }

            $is_valid = true;
            // check for name, tooltip, lanes, days
            foreach ($keys as $key) {
                if (array_key_exists($key, $item)) {
                    continue;
                }

                $is_valid = false;
                break;
            }

            if ($is_valid) {
                $result[] = $item;
            }
        }

        return json_encode($result);
    }

     function add_closure($request){
        global $wpdb;
        $params = json_decode($request->get_body(), true);

        try {
                 if(isset($params['id']) && isset($params['lanes']) && isset($params['tooltip']) && isset($params['title']) ){
                     foreach($params['lanes'] as $lane){
                         $arr=array();
                         $query=  $wpdb->prepare( "SELECT *  from ".$wpdb->prefix."rr_lanes where name='$lane'");
                         $lanes=$wpdb->get_results($query);
                         if(isset($lanes[0])) {
                             $lane = $lanes[0];
                             array_push($arr,$lane);
                         }
                     }
                     $params['lanes']=$arr;
                     $query=  $wpdb->prepare( "SELECT *  from ".$wpdb->prefix."rr_options where rr_key='closures'");
                     $closures=$wpdb->get_results($query);
                     if(!isset($closures[0])){
                         $option = array(
                             'rr_key'   => 'closures',
                             'rr_value' => json_encode(array()),
                             'type'     => 'JSON_ARRAY',
                         );
                         $result = $this->db_models->update_option($option);
                         $closures[0]= (object) $option;
                     }
                     if(isset($closures[0])) {
                         $closure = $closures[0];
                         $rr_value=$closure->rr_value;
                         $closures=json_decode($rr_value,true);
                         foreach ($closures as $closure){
                             if($closure['id']==$params["id"]){
                                 return new WP_REST_Response(array('msg' => 'Closure with same id is already found', 'code' => 500), 200);
                             }
                         }
                         array_push($closures,$params);
                         $closures=json_encode($closures);
                         $option = array(
                             'rr_key'   => 'closures',
                             'rr_value' => $closures,
                             'type'     => 'JSON_ARRAY',
                         );
                         $result = $this->db_models->update_option($option);
                         return new WP_REST_Response(array('msg' => 'Closure Added Successfully', 'code' => 200), 200);
                     }

                 }else{
                     return new WP_REST_Response(array('msg' => 'Missing parameters', 'code' => 500), 200);
                 }
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);

        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }
    function edit_closure($request){
        global $wpdb;
        $params = json_decode($request->get_body(), true);

        try {
            if(isset($params['id']) && isset($params['lanes']) && isset($params['tooltip']) && isset($params['title']) ){
                foreach($params['lanes'] as $lane){
                    $arr=array();
                    $query=  $wpdb->prepare( "SELECT *  from ".$wpdb->prefix."rr_lanes where name='$lane'");
                    $lanes=$wpdb->get_results($query);
                    if(isset($lanes[0])) {
                        $lane = $lanes[0];
                        array_push($arr,$lane);
                    }
                }
                $params['lanes']=$arr;
                $query=  $wpdb->prepare( "SELECT *  from ".$wpdb->prefix."rr_options where rr_key='closures'");
                $closures=$wpdb->get_results($query);
                if(isset($closures[0])) {
                    $closure = $closures[0];
                    $rr_value=$closure->rr_value;
                    $closures=json_decode($rr_value,true);
                    foreach ($closures as $key => $closure){
                        if($closure['id']==$params["id"]){
                            $closure['title']=$params["title"];
                            $closure['tooltip']=$params["tooltip"];
                            $closure['lanes']=$params["lanes"];
                            $closure['days']=$params["days"];
                            $closures[$key]=$closure;
                            $option = array(
                                'rr_key'   => 'closures',
                                'rr_value' => json_encode($closures),
                                'type'     => 'JSON_ARRAY',
                            );
                            $result = $this->db_models->update_option($option);
                            return new WP_REST_Response(array('msg' => 'Closure Updated Successfully', 'code' => 200), 200);
                        }
                    }
                }

            }else{
                return new WP_REST_Response(array('msg' => 'Missing parameters', 'code' => 500), 200);
            }
            return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);

        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }

    function delete_closure($request){
        global $wpdb;
        $params = json_decode($request->get_body(), true);

        try {
            if(isset($params['id'])){
                $query=  $wpdb->prepare( "SELECT *  from ".$wpdb->prefix."rr_options where rr_key='closures'");
                $closures=$wpdb->get_results($query);
                if(isset($closures[0])) {
                    $closure = $closures[0];
                    $rr_value=$closure->rr_value;
                    $closures=json_decode($rr_value,true);
                    $index=-1;
                    foreach ($closures as $key => $closure){
                        if($closure['id']==$params["id"]){
                            $index=$key;
                        }
                    }
                    if($index!=-1){
                        unset($closures[$index]);
                        $closures = array_values($closures);
                        $option = array(
                            'rr_key'   => 'closures',
                            'rr_value' => json_encode($closures),
                            'type'     => 'JSON_ARRAY',
                        );
                        $result = $this->db_models->update_option($option);
                    }
                    return new WP_REST_Response(array('msg' => 'Closure Deleted Successfully', 'code' => 200), 200);
                }

            }else{
                return new WP_REST_Response(array('msg' => 'Missing parameters', 'code' => 500), 200);
            }
            return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);

        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }

}