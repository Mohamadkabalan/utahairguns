<?php
/*
Plugin Name: LitExtension SEO Plugin
Plugin URI: http://litextension.com/
Description: A brief description of the Plugin.
Version: 1.0.1
Author: LitExtension
Author URI: http://litextension.com/
License: A "Slug" license name e.g. GPL2
*/

defined('ABSPATH') or die();

define('LEUR_TABLE', 'lecm_rewrite');

function le_url_rewrite_install()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(ABSPATH . 'wp-admin/includes/schema.php');
    global $wpdb;
    $table_name = $wpdb->prefix . LEUR_TABLE;
    $query = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, `link` VARCHAR(255), `type` VARCHAR(255), `type_id` INT(11), `redirect_type` SMALLINT(5)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    dbDelta($query);
    add_option('LEUR_VERSION', '1.0.1');
}
register_activation_hook(__FILE__, 'le_url_rewrite_install');

//function le_url_rewrite_uninstall()
//{
//    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
//    require_once ABSPATH . 'wp-admin/includes/schema.php';
//    global $wpdb;
//    $table_name = $wpdb->prefix . LEUR_TABLE;
//    $query = "DROP TABLE IF EXISTS `" . $table_name . "`";
//    $wpdb->query($query);
//    delete_option('LEUR_VERSION');
//}
//register_deactivation_hook(__FILE__, 'le_url_rewrite_uninstall');

function le_url_rewrite_request($query)
{
    if(!$query || empty($query['product']) || empty($query['category']) || empty($query['page']) || empty($query['post'])){
        global $wpdb;
        $lang = '';
        if(defined('ICL_LANGUAGE_CODE')){
            $lang = ICL_LANGUAGE_CODE;
        }
        if(!$lang && isset($_GET['lang']) && $_GET['lang']){
            $lang = $_GET['lang'];
        }
        $url = parse_url(get_bloginfo('url'));
        $url = isset($url['path']) ? $url['path'] : '';
        $request = trim(substr($_SERVER['REQUEST_URI'], strlen($url)), '/');
        $request = str_replace('index.php/','', $request);
        $request = ltrim($request, '?');

        if(!$request || strpos($request,'wp-admin') !== false){
            return $query;
        }
        $table_name = $wpdb->prefix . LEUR_TABLE;
        $query_check = "SELECT * FROM `". $table_name ."` WHERE `link` = '" . $request . "'";
        if($lang){
            $sql = $wpdb->get_results( $query_check . " AND lang = '".$lang."'", ARRAY_A);
            if(!$sql){
                $sql = $wpdb->get_results( $query_check, ARRAY_A);
            }
        }else{
            $sql = $wpdb->get_results( $query_check, ARRAY_A);

        }
        if($sql){
            $sql = $sql[0];
            if($sql && ($sql['type'] == 'product' || $sql['type'] == 'post')){

                $product = get_post((int) $sql['type_id']);

                if ($product){
                    if ($sql['redirect_type'] == 301){
                        $redirect_link = trim(get_post_permalink((int) $product->ID), '/');
                        if($redirect_link != $request){
                            wp_redirect($redirect_link, 301);
                            exit;
                        }
                    }
                    $query = array(
                        'post_type' => $sql['type'],
                        $sql['type'] => $product->post_name,
                        'name' => $product->post_name
                    );
                }
            }
            if($sql && $sql['type'] == 'category'){

                $term = get_term( (int) $sql['type_id'], 'product_cat');
                if ($term){
                    if ($sql['redirect_type'] == 301){
                        $redirect_link = trim(get_term_link((int) $term->term_id, 'product_cat'), '/');
                        if($redirect_link != $request){
                            wp_redirect($redirect_link, 301);
                            exit;
                        }
                    }
                    $query = array(
                        'product_cat' => $term->slug
                    );
                }
            }
        }
    }
    return $query;
}

add_filter( 'request', 'le_url_rewrite_request', 'edit_files', 1);