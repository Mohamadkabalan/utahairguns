<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Install tools
 *
 * Create whole DB stracture
 */
class RRUninstallTools
{

    /**
     * Delete all database tables of RR
     */
    public function drop_db()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_fields");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_appointments");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_schedules");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_locations");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_bays");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_lanes");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_options");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_meta_fields");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rr_error_logs");
    }

    /**
     * Delete db version value
     */
    public function delete_db_version()
    {
        $option_name = 'range_app_db_version';

        delete_option($option_name);
    }

    /**
     * Empty all database tables
     */
    public function clear_database()
    {
        global $wpdb;

        $tables = array(
            'rr_fields',
            'rr_appointments',
            'rr_schedules',
            'rr_locations',
            'rr_options',
            'rr_bays',
            'rr_lanes',
        );

        $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
        $wpdb->query("SET AUTOCOMMIT = 0;");
        $wpdb->query("START TRANSACTION;");

        foreach ($tables as $table) {
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$table}");
        }

        $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
        $wpdb->query("COMMIT;");
    }

    public function clear_cron()
    {
        wp_clear_scheduled_hook('rr_gdpr_auto_delete');
    }
}
