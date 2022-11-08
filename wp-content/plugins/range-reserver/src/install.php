<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Install tools
 *
 * Create whole DB structure
 */
class RRInstallTools
{

    /**
     * DB version
     */
    public $range_app_db_version;

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var RRDBModels
     */
    protected $models;

    /**
     * @var RROptions
     */
    protected $options;

    /**
     * RRInstallTools constructor.
     * @param wpdb $wpdb
     * @param RRDBModels $models
     * @param RROptions $options
     */
    function __construct($wpdb, $models, $options)
    {
        $this->range_app_db_version = RANGE_RESERVER_VERSION;

        $this->wpdb = $wpdb;
        $this->models = $models;
        $this->options = $options;
    }

    /**
     * Create db
     */
    public function init_db()
    {
        // get table prefix
        $table_prefix = $this->wpdb->prefix;

        //
        $charset_collate = $this->wpdb->get_charset_collate();

        $table_querys = array();
        $alter_querys = array();

        // whole table struct
        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_appointments (
  id int(11) NOT NULL AUTO_INCREMENT,
  location int(11) NOT NULL,
  bay int(11) NOT NULL,
  lane int(11) NOT NULL,
  name varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  phone varchar(45) DEFAULT NULL,
  date date DEFAULT NULL,
  start time DEFAULT NULL,
  end time DEFAULT NULL,
  end_date date DEFAULT NULL,
  description text,
  status varchar(45) DEFAULT NULL,
  user int(11) DEFAULT NULL,
  created timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  price decimal(10,2) DEFAULT NULL,
  ip varchar(45) DEFAULT NULL,
  session varchar(32) DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY appointments_location (location),
  KEY appointments_bay (bay),
  KEY appointments_lane (lane)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_schedules (
  id int(11) NOT NULL AUTO_INCREMENT,
  group_id int(11) DEFAULT NULL,
  location int(11) DEFAULT NULL,
  bay int(11) DEFAULT NULL,
  lane int(11) DEFAULT NULL,
  slot_count int(11) DEFAULT 1,
  day_of_week varchar(60) DEFAULT NULL,
  time_from time DEFAULT NULL,
  time_to time DEFAULT NULL,
  day_from date DEFAULT NULL,
  day_to date DEFAULT NULL,
  is_working smallint(3) DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY location_to_schedule (location),
  KEY bay_to_location (bay),
  KEY lane_to_schedule (lane)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_locations (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  address text NOT NULL,
  location varchar(255) DEFAULT NULL,
  cord varchar(255) DEFAULT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE IF NOT EXISTS {$table_prefix}rr_options (
  id int(11) NOT NULL AUTO_INCREMENT,
  rr_key varchar(45) DEFAULT NULL,
  rr_value text,
  type varchar(45) DEFAULT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_lanes (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) DEFAULT NULL,
  description text,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_bays (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  bay_color varchar(7) DEFAULT '#0693E3',
  duration int(11) NOT NULL,
  slot_step int(11) DEFAULT NULL,
  block_before int(11) DEFAULT 0,
  block_after int(11) DEFAULT 0,
  daily_limit int(11) DEFAULT 0,
  price decimal(10,2) DEFAULT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_meta_fields (
  id int(11) NOT NULL AUTO_INCREMENT,
  type varchar(50) NOT NULL,
  slug varchar(255) NOT NULL,
  label varchar(255) NOT NULL,
  mixed text NOT NULL,
  default_value varchar(50) NOT NULL,
  visible tinyint(4) NOT NULL,
  required tinyint(4) NOT NULL,
  validation varchar(50) NULL,
  position int(11) NOT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_fields (
  id int(11) NOT NULL AUTO_INCREMENT,
  app_id int(11) NOT NULL,
  field_id int(11) NOT NULL,
  value varchar(500) DEFAULT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_error_logs (
  id int(11) NOT NULL AUTO_INCREMENT,
  error_type varchar(50) NULL,
  errors text,
  errors_data text,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

        $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_appointments
  ADD CONSTRAINT {$table_prefix}rr_appointments_ibfk_1 FOREIGN KEY (location) REFERENCES {$table_prefix}rr_locations (id) ON DELETE CASCADE,
  ADD CONSTRAINT {$table_prefix}rr_appointments_ibfk_2 FOREIGN KEY (bay) REFERENCES {$table_prefix}rr_bays (id) ON DELETE CASCADE,
  ADD CONSTRAINT {$table_prefix}rr_appointments_ibfk_3 FOREIGN KEY (lane) REFERENCES {$table_prefix}rr_lanes (id) ON DELETE CASCADE;
EOT;
        $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_schedules
  ADD CONSTRAINT {$table_prefix}rr_schedules_ibfk_1 FOREIGN KEY (location) REFERENCES {$table_prefix}rr_locations (id) ON DELETE CASCADE,
  ADD CONSTRAINT {$table_prefix}rr_schedules_ibfk_2 FOREIGN KEY (bay) REFERENCES {$table_prefix}rr_bays (id) ON DELETE CASCADE,
  ADD CONSTRAINT {$table_prefix}rr_schedules_ibfk_3 FOREIGN KEY (lane) REFERENCES {$table_prefix}rr_lanes (id) ON DELETE CASCADE;
EOT;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // create structure
        foreach ($table_querys as $table_query) {
            dbDelta($table_query);
        }

        // add relations
        foreach ($alter_querys as $alter_query) {
            $this->wpdb->query($alter_query);
        }

        update_option('range_app_db_version', $this->range_app_db_version);
    }

    /**
     * Insert start data into options
     */
    public function init_data()
    {
        // safety check if we already have meta fields
        $count_query = "SELECT count(*) FROM {$this->wpdb->prefix}rr_meta_fields";
        $num = (int) $this->wpdb->get_var($count_query);
        if ($num > 0) {
            return;
        }

        // options table
        $table_name = $this->wpdb->prefix . 'rr_options';

        // rows data
        $wp_rr_options = $this->options->get_insert_options();

        // insert options
        foreach ($wp_rr_options as $row) {
            $this->wpdb->insert(
                $table_name,
                $row
            );
        }

        // create custom form fields
        $default_fields = $this->migrateFormFields();

        $table_name = $this->wpdb->prefix . 'rr_meta_fields';

        foreach ($default_fields as $row) {
            $this->wpdb->insert(
                $table_name,
                $row
            );
        }
    }

    public function update()
    {

        // get table prefix
        $table_prefix = $this->wpdb->prefix;

        $charset_collate = $this->wpdb->get_charset_collate();

        $version = get_option('range_app_db_version', '1.0');

        // if it is already latest version
        if (version_compare($version, $this->range_app_db_version, '=')) {
            return;
        }

        // Migrate from 1.0 > 1.1
        if (version_compare($version, '1.1', '<')) {

            $this->init_db();

            // options table
            $table_name = $this->wpdb->prefix . 'rr_options';
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'pending.email', 'rr_value' => '', 'type' => 'default'),
                array('rr_key' => 'price.hide', 'rr_value' => '0', 'type' => 'default')
            );
            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.1';
        }

        // Migrate from 1.2.1- > 1.2.2
        if (version_compare($version, '1.2.2', '<')) {
            $version = '1.2.2';

            $alter_querys = array();

            $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_appointments DROP FOREIGN KEY {$table_prefix}rr_appointments_ibfk_1;
EOT;
            $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_appointments DROP FOREIGN KEY {$table_prefix}rr_appointments_ibfk_2;
EOT;
            $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_appointments DROP FOREIGN KEY {$table_prefix}rr_appointments_ibfk_3;
EOT;
            $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_schedules DROP FOREIGN KEY {$table_prefix}rr_schedules_ibfk_1;
EOT;
            $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_schedules DROP FOREIGN KEY {$table_prefix}rr_schedules_ibfk_2;
EOT;
            $alter_querys[] = <<<EOT
ALTER TABLE {$table_prefix}rr_schedules DROP FOREIGN KEY {$table_prefix}rr_schedules_ibfk_3;
EOT;

            $alter_querys[] = <<<EOT
DELETE FROM {$table_prefix}rr_schedules 
WHERE 
	location NOT IN (SELECT id FROM {$table_prefix}rr_locations)
	OR
	bay NOT IN (SELECT id FROM {$table_prefix}rr_bays)
	OR
	lane NOT IN (SELECT id FROM {$table_prefix}rr_lanes);
EOT;

            $alter_querys[] = <<<EOT
DELETE FROM {$table_prefix}rr_appointments 
WHERE 
	location NOT IN (SELECT id FROM {$table_prefix}rr_locations)
	OR
	bay NOT IN (SELECT id FROM {$table_prefix}rr_bays)
	OR
	lane NOT IN (SELECT id FROM {$table_prefix}rr_lanes);
EOT;

            // add relations
            foreach ($alter_querys as $alter_query) {
                $this->wpdb->query($alter_query);
            }

            $this->init_db();
        }

        // Migrate from 1.2.2 > 1.2.3
        if (version_compare($version, '1.2.3', '<')) {
            $version = '1.2.3';
        }

        // Migrate form 1.2.3 > 1.2.4
        if (version_compare($version, '1.2.4', '<')) {
            $option = array('rr_key' => 'datepicker', 'rr_value' => 'en-US', 'type' => 'default');

            $table_name = $this->wpdb->prefix . 'rr_options';

            $this->wpdb->insert(
                $table_name,
                $option
            );

            $version = '1.2.4';
        }

        // Migrate form 1.2.4 > 1.2.7
        if (version_compare($version, '1.2.7', '<')) {
            $version = '1.2.7';
        }

        // Migrate form 1.2.7 > 1.2.8
        if (version_compare($version, '1.2.8', '<')) {
            $option = array('rr_key' => 'send.user.email', 'rr_value' => '0', 'type' => 'default');

            $table_name = $this->wpdb->prefix . 'rr_options';

            $this->wpdb->insert(
                $table_name,
                $option
            );

            $version = '1.2.8';
        }

        // Migrate form 1.2.8 > 1.2.9
        if (version_compare($version, '1.2.9', '<')) {
            $option = array('rr_key' => 'custom.css', 'rr_value' => '', 'type' => 'default');

            $table_name = $this->wpdb->prefix . 'rr_options';

            $this->wpdb->insert(
                $table_name,
                $option
            );

            $version = '1.2.9';
        }

        if (version_compare($version, '1.3.0', '<')) {
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'show.iagree', 'rr_value' => '0', 'type' => 'default'),
                array('rr_key' => 'cancel.scroll', 'rr_value' => 'calendar', 'type' => 'default')
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.3.0';
        }

        if (version_compare($version, '1.4.0', '<')) {
            $version = '1.4.0';
        }

        // Migrate to last version
        if (version_compare($version, '1.5.0', '<')) {
            $version = '1.5.0';
            $table_querys = array();

            $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_fields (
  id int(11) NOT NULL AUTO_INCREMENT,
  app_id int(11) NOT NULL,
  field_id int(11) NOT NULL,
  value varchar(500) DEFAULT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

            $table_querys[] = <<<EOT
CREATE TABLE {$table_prefix}rr_meta_fields (
  id int(11) NOT NULL AUTO_INCREMENT,
  type varchar(50) NOT NULL,
  slug varchar(255) NOT NULL,
  label varchar(255) NOT NULL,
  mixed text NOT NULL,
  default_value varchar(50) NOT NULL,
  visible tinyint(4) NOT NULL,
  required tinyint(4) NOT NULL,
  validation varchar(50) NULL,
  position int(11) NOT NULL,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            // add relations
            foreach ($table_querys as $table) {
                dbDelta($table);
            }

            $default_fields = $this->migrateFormFields();

            $table_name = $this->wpdb->prefix . 'rr_meta_fields';

            $ids = array();

            foreach ($default_fields as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );

                $ids[$row['slug']] = $this->wpdb->insert_id;
            }

            $this->migrateOldFormValues($ids);
        }

        // Migrate to last version
        if (version_compare($version, '1.5.1', '<')) {
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'multiple.work', 'rr_value' => '1', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.5.1';
        }

        if (version_compare($version, '1.5.2', '<')) {
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'compatibility.mode', 'rr_value' => '0', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.5.2';
        }

        if (version_compare($version, '1.7.0', '<')) {

            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'pending.subject.email', 'rr_value' => 'New Reservation #id#', 'type' => 'default'),
                array('rr_key' => 'send.from.email', 'rr_value' => '', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.7.0';
        }

        if (version_compare($version, '1.7.1', '<')) {
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'css.off', 'rr_value' => '0', 'type' => 'default'),
                array('rr_key' => 'submit.redirect', 'rr_value' => '', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.7.1';
        }

        if (version_compare($version, '1.8.0', '<')) {
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'pending.subject.visitor.email', 'rr_value' => 'Reservation #id#', 'type' => 'default'),
                array('rr_key' => 'block.time', 'rr_value' => '0', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.8.0';
        }

        if (version_compare($version, '1.8.1', '<')) {
            // rows data
            $wp_rr_options = array(
                array('rr_key' => 'max.appointments', 'rr_value' => '5', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.8.1';
        }

        if (version_compare($version, '1.8.12', '<')) {
            $wp_rr_options = array(
                array('rr_key' => 'pre.reservation', 'rr_value' => '1', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.8.12';

        }

        if (version_compare($version, '1.8.14', '<')) {
            $wp_rr_options = array(
                array('rr_key' => 'default.status', 'rr_value' => 'pending', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.8.14';
        }

        if (version_compare($version, '1.9.3', '<')) {
            $table_queries = array();

            $table_queries[] = <<<EOT
CREATE TABLE {$table_prefix}rr_error_logs (
  id int(11) NOT NULL AUTO_INCREMENT,
  error_type varchar(50) NULL,
  errors text,
  errors_data text,
  PRIMARY KEY  (id)
) $charset_collate ;
EOT;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            // add relations
            foreach ($table_queries as $table) {
                dbDelta($table);
            }

            $version = '1.9.3';
        }

        if (version_compare($version, '1.9.5', '<')) {
            $wp_rr_options = array(
                array('rr_key' => 'send.lane.email', 'rr_value' => '0', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '1.9.5';
        }

        if (version_compare($version, '1.9.11', '<')) {
            $table_queries = array();

            $table_bays = $this->wpdb->prefix . 'rr_bays';
            $table_appointments = $this->wpdb->prefix . 'rr_appointments';

            $table_queries[] = "ALTER TABLE `{$table_bays}` CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL DEFAULT NULL";
            $table_queries[] = "ALTER TABLE `{$table_appointments}` CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL DEFAULT NULL";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '1.9.11';
        }

        if (version_compare($version, '1.10.4', '<')) {

            $table_name = $this->wpdb->prefix . 'rr_meta_fields';

            $rows = $this->wpdb->get_results("SELECT id, label FROM $table_name WHERE `slug` = ''");

            foreach ($rows as $row) {
                try {
                    $this->wpdb->update(
                        $table_name,
                        array('slug' => sanitize_title($row->label)),
                        array('id' => $row->id),
                        array('%s')
                    );
                } catch (Exception $e) { }
            }

            $version = '1.10.4';
        }

        if (version_compare($version, '2.0.0', '<')) {
            $table_queries = array();

            $table_appointments = $this->wpdb->prefix . 'rr_appointments';

            $table_queries[] = "ALTER TABLE `{$table_appointments}` ADD COLUMN `end_date` DATE NULL DEFAULT NULL AFTER `end`;";
            $table_queries[] = "UPDATE `{$table_appointments}` SET end_date=`date`;";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '2.0.0';
        }

        if (version_compare($version, '2.2.0', '<')) {
            $table_queries = array();

            $table_bays = $this->wpdb->prefix . 'rr_bays';

            $table_queries[] = "ALTER TABLE `{$table_bays}` ADD COLUMN `slot_step` int(11) DEFAULT NULL AFTER `duration`;";
            $table_queries[] = "UPDATE `{$table_bays}` SET slot_step=duration;";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '2.2.0';
        }

        if (version_compare($version, '2.8.0', '<')) {
            $table_queries = array();

            $table_bays = $this->wpdb->prefix . 'rr_bays';

            $table_queries[] = "ALTER TABLE `{$table_bays}` ADD COLUMN `block_before` int(11) DEFAULT 0 AFTER `slot_step`;";
            $table_queries[] = "ALTER TABLE `{$table_bays}` ADD COLUMN `block_after` int(11) DEFAULT 0 AFTER `block_before`;";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '2.8.0';
        }

        if (version_compare($version, '2.10.0', '<')) {
            $table_queries = array();

            $table_schedules = $this->wpdb->prefix . 'rr_schedules';

            $table_queries[] = "ALTER TABLE `{$table_schedules}` ADD COLUMN `slot_count` int(11) DEFAULT 1 AFTER `lane`;";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '2.10.0';
        }

        if (version_compare($version, '2.10.2', '<')) {
            $wp_rr_options = array(
                array('rr_key' => 'shortcode.compress', 'rr_value' => '0', 'type' => 'default'),
            );

            $table_name = $this->wpdb->prefix . 'rr_options';

            // insert options
            foreach ($wp_rr_options as $row) {
                $this->wpdb->insert(
                    $table_name,
                    $row
                );
            }

            $version = '2.10.2';
        }

        if (version_compare($version, '3.5.4', '<')) {
            $table_queries = array();

            $table_bays = $this->wpdb->prefix . 'rr_bays';

            $table_queries[] = "ALTER TABLE `{$table_bays}` ADD COLUMN `daily_limit` int(11) DEFAULT 0 AFTER `block_after`;";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '3.5.4';
        }

        if (version_compare($version, '3.6.0', '<')) {
            $table_queries = array();

            $table_bays = $this->wpdb->prefix . 'rr_bays';

            $table_queries[] = "ALTER TABLE `{$table_bays}` ADD COLUMN `bay_color` varchar(7) DEFAULT '#0693E3' AFTER `name`;";

            // add relations
            foreach ($table_queries as $query) {
                $this->wpdb->query($query);
            }

            $version = '3.6.0';
        }

        update_option('range_app_db_version', $version);
    }

    private function migrateFormFields()
    {
        $email = __('EMail', 'range-reserver');
        $name = __('Name', 'range-reserver');
        $phone = __('Phone', 'range-reserver');
        $comment = __('Description', 'range-reserver');

        $data = array();

        // email
        $data[] = array(
            'type'          => 'EMAIL',
            'slug'          => str_replace('-', '_', sanitize_title('email')),
            'label'         => $email,
            'default_value' => '',
            'validation'    => 'email',
            'mixed'         => '',
            'visible'       => 1,
            'required'      => 1,
            'position'      => 1
        );


        $data[] = array(
            'type'          => 'INPUT',
            'slug'          => str_replace('-', '_', sanitize_title('name')),
            'label'         => $name,
            'default_value' => '',
            'validation'    => 'minlength-3',
            'mixed'         => '',
            'visible'       => 1,
            'required'      => 1,
            'position'      => 2
        );

        $data[] = array(
            'type'          => 'INPUT',
            'slug'          => str_replace('-', '_', sanitize_title('phone')),
            'label'         => $phone,
            'default_value' => '',
            'validation'    => 'minlength-3',
            'mixed'         => '',
            'visible'       => 1,
            'required'      => 1,
            'position'      => 3
        );
        $data[] = array(
            'type'          => 'TEXTAREA',
            'slug'          => str_replace('-', '_', sanitize_title('description')),
            'label'         => $comment,
            'default_value' => '',
            'validation'    => NULL,
            'mixed'         => '',
            'visible'       => 1,
            'required'      => 0,
            'position'      => 4
        );

        return $data;
    }

    /**
     * Insert all the old values from appointments
     * @param $ids
     */
    private function migrateOldFormValues($ids)
    {
        $table_name = 'rr_appointments';

        $apps = $this->models->get_all_rows($table_name);

        $chunks = array_chunk($apps, 100);

        $rows = array();
        $keys = array('email', 'name', 'phone', 'description');

        $table_name = $this->wpdb->prefix . 'rr_fields';

        foreach ($chunks as $chunk) {
            // helpers
            $values = array();
            $place_holders = array();

            $query = "INSERT INTO $table_name (app_id, field_id, value) VALUES ";

            // all appointments
            foreach ($chunk as $app) {
                // set insert for every key name, description
                foreach ($keys as $key) {
                    array_push($values, $app->id, $ids[$key], $app->{$key});
                    $place_holders[] = "('%d', '%d', '%s')";
                }
            }

            $query .= implode(', ', $place_holders);
            $this->wpdb->query($this->wpdb->prepare("$query ", $values));
        }
    }

    /**
     *
     */
    public function set_demo_data()
    {

        $data = array(
            'rr_lanes' => array(
                array('id' => 1, 'name' => 'John Smit', 'description' => 'Lane 1'),
                array('id' => 2, 'name' => 'Peter Dalas', 'description' => 'Lane 2')
            ),
            'rr_locations' => array(
                array('id' => 1, 'name' => 'New York', 'address' => 'Street 1', 'location' => 'New York', 'cord' => ''),
                array('id' => 2, 'name' => 'Washington DC', 'address' => 'Street 10', 'location' => 'Wasington DC', 'cord' => '')
            ),
            'rr_bays' => array(
                array('id' => 1, 'name' => 'Car wash', 'duration' => 60, 'price' => 25, 'bay_color' => '#0693E3'),
                array('id' => 2, 'name' => 'Car polishing', 'duration' =>  45, 'price' => 10, 'bay_color' => '#FF6900')
            ),
        );

        foreach ($data as $table => $rows) {
            $tableName = $this->wpdb->prefix . $table;

            foreach ($rows as $row) {
                $this->wpdb->insert(
                    $tableName,
                    $row
                );
            }

        }
    }
}