<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class RRReservations
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
        return rest_url('range-reserver/v1/reservations');
    }

    /**
     *
     */
    public function register_routes()
    {
        //read reservations
        $reservation = 'reservations';
        register_rest_route($this->namespace, '/'.$reservation, array(
            array(
                'methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_reservations'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ), array(
                'methods' => WP_REST_Server::CREATABLE, 'callback' => array($this, 'add_reservation'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::EDITABLE, 'callback' => array($this, 'edit_reservation'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::DELETABLE, 'permission_callback' => function () {
                    return current_user_can('manage_options');
                }, 'callback' => array($this, 'delete_reservation')
            )
        ));
    }
    public function get_reservations()
    {
        global $wpdb;
        $orderPart = $this->models->get_order_by_part('rr_appointments');
        $results = $this->models->get_all_rows('rr_appointments', array(), $orderPart);
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
            $name_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='name'"));
            if(isset($name_field_ids[0])){
                $name_field_id= (int)$name_field_ids[0];
                $names= $wpdb->get_col( $wpdb->prepare( "SELECT value  from ".$wpdb->prefix."rr_fields where field_id=".$name_field_id." and app_id=".$result->id));
                if(isset($names[0])){
                    $result->name=$names[0];
                }
            }
            $email_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='email'"));
            if(isset($email_field_ids[0])){
                $email_field_id= (int)$email_field_ids[0];
                $emails= $wpdb->get_col( $wpdb->prepare( "SELECT value  from ".$wpdb->prefix."rr_fields where field_id=".$email_field_id." and app_id=".$result->id));
                if(isset($emails[0])){
                    $result->email=$emails[0];
                }
            }
            $phone_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='phone'"));
            if(isset($phone_field_ids[0])){
                $phone_field_id= (int)$phone_field_ids[0];
                $phones= $wpdb->get_col( $wpdb->prepare( "SELECT value  from ".$wpdb->prefix."rr_fields where field_id=".$phone_field_id." and app_id=".$result->id));
                if(isset($phones[0])){
                    $result->phone=$phones[0];
                }
            }
            $description_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='description'"));
            if(isset($description_field_ids[0])){
                $description_field_id= (int)$description_field_ids[0];
                $descriptions= $wpdb->get_col( $wpdb->prepare( "SELECT value  from ".$wpdb->prefix."rr_fields where field_id=".$description_field_id." and app_id=".$result->id));
                if(isset($descriptions[0])){
                    $result->description=$descriptions[0];
                }
            }
        }
        wp_send_json($results);
    }

    public static function add_reservation($request)
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
                if (isset($params['date'])) {
                    $arr['date'] = $params['date'];
                }
                if (isset($params['start'])) {
                    $arr['start'] = $params['start'];
                }
                if (isset($params['end'])) {
                    $arr['end'] = $params['end'];
                }
                if (isset($params['end_date'])) {
                    $arr['end_date'] = $params['end_date'];
                }
                if (isset($params['status'])) {
                    $arr['status'] = $params['status'];
                }
                if (isset($params['price'])) {
                    $arr['price'] = (int)$params['price'];
                }
                $wpdb->insert($wpdb->prefix.'rr_appointments', $arr);
                $reservation_id = (int)$wpdb->insert_id;
                if($reservation_id){
                    if(isset($params['email'])){
                        $email_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='email'"));
                        if(isset($email_field_ids[0])){
                            $email_field_id= (int)$email_field_ids[0];
                            $wpdb->insert($wpdb->prefix.'rr_fields', array('app_id'=>$reservation_id,'field_id'=>$email_field_id,'value'=>$params['email']));
                        }
                    }
                    if(isset($params['name'])){
                        $name_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='name'"));
                        if(isset($name_field_ids[0])){
                            $name_field_id= (int)$name_field_ids[0];
                            $wpdb->insert($wpdb->prefix.'rr_fields', array('app_id'=>$reservation_id,'field_id'=>$name_field_id,'value'=>$params['name']));
                        }
                    }
                    if(isset($params['phone'])){
                        $phone_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='phone'"));
                        if(isset($phone_field_ids[0])){
                            $phone_field_id= (int)$phone_field_ids[0];
                            $wpdb->insert($wpdb->prefix.'rr_fields', array('app_id'=>$reservation_id,'field_id'=>$phone_field_id,'value'=>$params['phone']));
                        }
                    }
                    if(isset($params['description'])){
                        $description_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='description'"));
                        if(isset($description_field_ids[0])){
                            $description_field_id= (int)$description_field_ids[0];
                            $wpdb->insert($wpdb->prefix.'rr_fields', array('app_id'=>$reservation_id,'field_id'=>$description_field_id,'value'=>$params['description']));
                        }
                    }
                }
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function edit_reservation($request)
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
                if (isset($params['date'])) {
                    $arr['date'] = $params['date'];
                }
                if (isset($params['start'])) {
                    $arr['start'] = $params['start'];
                }
                if (isset($params['end'])) {
                    $arr['end'] = $params['end'];
                }
                if (isset($params['end_date'])) {
                    $arr['end_date'] = $params['end_date'];
                }
                if (isset($params['status'])) {
                    $arr['status'] = $params['status'];
                }
                if (isset($params['price'])) {
                    $arr['price'] = (int)$params['price'];
                }
                $wpdb->update($wpdb->prefix.'rr_appointments', $arr, array('id' => $params['id']));
                    if(isset($params['email'])){
                        $email_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='email'"));
                        if(isset($email_field_ids[0])){
                            $email_field_id= (int)$email_field_ids[0];
                            $field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_fields where field_id=".$email_field_id));
                            if(isset($field_ids[0])){
                                $field_id= (int)$field_ids[0];
                                $wpdb->update($wpdb->prefix.'rr_fields', array( 'value' => $params['email'] ), array('id' => $field_id));
                            }
                        }
                    }
                    if(isset($params['name'])){
                        $name_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='name'"));
                        if(isset($name_field_ids[0])){
                            $name_field_id= (int)$name_field_ids[0];
                            $field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_fields where field_id=".$name_field_id));
                            if(isset($field_ids[0])){
                                $field_id= (int)$field_ids[0];
                                $wpdb->update($wpdb->prefix.'rr_fields', array( 'value' => $params['name'] ), array('id' => $field_id));
                            }
                        }
                    }
                    if(isset($params['phone'])){
                        $phone_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='phone'"));
                        if(isset($phone_field_ids[0])){
                            $phone_field_id= (int)$phone_field_ids[0];
                            $field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_fields where field_id=".$phone_field_id));
                            if(isset($field_ids[0])){
                                $field_id= (int)$field_ids[0];
                                $wpdb->update($wpdb->prefix.'rr_fields', array( 'value' => $params['phone'] ), array('id' => $field_id));
                            }
                        }
                    }
                    if(isset($params['description'])){
                        $description_field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_meta_fields where slug='description'"));
                        if(isset($email_field_ids[0])){
                            $description_field_id= (int)$description_field_ids[0];
                            $field_ids= $wpdb->get_col( $wpdb->prepare( "SELECT id  from ".$wpdb->prefix."rr_fields where field_id=".$description_field_id));
                            if(isset($field_ids[0])){
                                $field_id= (int)$field_ids[0];
                                $wpdb->update($wpdb->prefix.'rr_fields', array( 'value' => $params['description'] ), array('id' => $field_id));
                            }
                        }
                    }

                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }

    }

    public static function delete_reservation($request)
    {

        global $wpdb;
        $params = json_decode($request->get_body(), true);
        try {
            if (isset($params['id'])) {
                $wpdb->delete($wpdb->prefix.'rr_appointments', array('id' => $params['id']));
                $wpdb->delete($wpdb->prefix.'rr_fields', array('app_id' => $params['id']));
                return new WP_REST_Response(array('msg' => 'success', 'code' => 200), 200);
            }
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        } catch (Exception $e) {
            return new WP_REST_Response(array('msg' => 'error', 'code' => 500), 200);
        }
    }



}