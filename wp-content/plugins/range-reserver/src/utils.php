<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Utils class
 */
class RRUtils
{
    public function get_template_path($template_file_name)
    {
        $default_path = RR_SRC_DIR . 'templates/' . $template_file_name;
        $theme_path = get_stylesheet_directory() . '/range-reserver/' . $template_file_name;

        if (file_exists($theme_path)) {
            return $theme_path;
        }

        return $default_path;
    }
    /**
     * @param $email
     * @return string
     * @throws Exception
     */
    public static function get_worker_id_from_email($email)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'rr_lanes';

        $query = $wpdb->prepare("SELECT `id` FROM {$table_name} WHERE `email` = %s", $email);

        $worker_id = $wpdb->get_var($query);

        if (!empty($worker_id)) {
            return $worker_id;
        }

        throw new Exception('There are worker with email address: ' . $email);
    }

    /**
     * @param $bay_id
     * @return null|string
     * @throws Exception
     */
    public static function get_bay_price($bay_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'rr_bays';

        $query = $wpdb->prepare("SELECT `price` FROM {$table_name} WHERE `id` = %d", $bay_id);

        $price = $wpdb->get_var($query);

        if (!empty($price)) {
            return $price;
        }

        throw new Exception('There is no bay with id: ' . $bay_id);
    }

    public static function get_custom_fields_tags()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'rr_meta_fields';

        $fields = $wpdb->get_col('SELECT CONCAT(\'#\', `slug`, \'#\') FROM ' . $table_name);

        return $fields;
    }



}