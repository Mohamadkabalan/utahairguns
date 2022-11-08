<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class UserRoles
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
        return rest_url('range-reserver/v1/user-roles');
    }

    /**
     *
     */
    public function register_routes()
    {
        //read user roles
        $user_roles = 'user-roles';
        register_rest_route($this->namespace, '/'.$user_roles, array(
            array(
                'methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_user_roles'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },

            ), array(
                'methods' => WP_REST_Server::CREATABLE, 'callback' => array($this, 'add_user_roles'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::EDITABLE, 'callback' => array($this, 'edit_user_roles'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ), array(
                'methods' => WP_REST_Server::DELETABLE, 'permission_callback' => function () {
                    return current_user_can('manage_options');
                }, 'callback' => array($this, 'delete_user_roles')
            )
        ));
    }
    public function get_user_roles()
    {
        $rolesList = \WPFront\URE\Roles\WPFront_User_Role_Editor_Roles_List::instance();
        $result= $rolesList->get_api_role_data();
        wp_send_json($result);
    }

    public static function add_user_roles($request)
    {
        $addRole = \WPFront\URE\Roles\WPFront_User_Role_Editor_Role_Add_Edit::instance();
        $params = json_decode($request->get_body(), true);
        $result= $addRole->add_api_role($params);
        wp_send_json($result);
    }

    public static function edit_user_roles($request)
    {
        $editRole = \WPFront\URE\Roles\WPFront_User_Role_Editor_Role_Add_Edit::instance();
        $params = json_decode($request->get_body(), true);
        $result= $editRole->edit_api_role($params);
        wp_send_json($result);
    }

    public static function delete_user_roles($request)
    {
        $deleteRole = \WPFront\URE\Roles\WPFront_User_Role_Editor_Role_Delete::instance();
        $params = json_decode($request->get_body(), true);
        $result= $deleteRole->delete_api_role($params);
        wp_send_json($result);
    }

}