<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * DataBase models
 */
class RRDBModels
{
    /**
     * WPDB
     *
     * @var wpdb $wpdb
     **/
    protected $wpdb;

    /**
     * @var RRTableColumns
     */
    protected $table_columns;

    /**
     * @var RROptions
     */
    protected $options;

    function __construct($wpdb, $table_columns, $options)
    {
        $this->wpdb = $wpdb;
        $this->table_columns = $table_columns;
        $this->options = $options;
    }

    /**
     * @param string $table_name
     * @param array $data
     * @param array $order
     * @return array|null|object
     */
    public function get_all_rows($table_name, $data = array(), $order = array('id' => 'DESC'))
    {

        $ignore = array('action');

        $where = '';

        $params = array();

        foreach ($data as $key => $value) {
            if (!in_array($key, $ignore)) {

                $helper = '=';

                // if equal or greater
                if (strpos($value, '+') === 0) {
                    $helper = '>=';
                    $value = substr($value, 1);

                    // if equal or smaller
                } else if (strpos($value, '-') === 0) {
                    $helper = '<=';
                    $value = substr($value, 1);
                }

                if (in_array($key, array('from', 'to'))) {
                    $key = 'date';
                }

                if (is_numeric($value)) {
                    $where .= " AND {$key}{$helper}%d";
                } else {
                    $where .= " AND {$key}{$helper}%s";
                }

                $params[] = $value;
            }
        }

        if ($where === '') {
            $where = ' AND 1=%d';
            $params[] = 1;
        }

        $order_part = array();

        foreach ($order as $key => $value) {
            $order_part[] = $key . ' ' . $value;
        }

        $order_part = implode(',', $order_part);

        $query = $this->wpdb->prepare("SELECT * 
			FROM {$this->wpdb->prefix}{$table_name} 
			WHERE 1$where 
			ORDER BY {$order_part}",
            $params
        );

        return $this->wpdb->get_results($query);
    }

    public function get_all_appointments($data)
    {
        $tableName = $this->wpdb->prefix . 'rr_appointments';
        $tableFields = $this->wpdb->prefix . 'rr_fields';

        $params = array(
            $data['from'],
            $data['to']
        );

        $location = '';
        $bay = '';
        $lane = '';
        $status = '';
        $search = '';

        if (array_key_exists('location', $data)) {
            $location = ' AND location = %d';
            $params[] = $data['location'];
        }

        if (array_key_exists('bay', $data)) {
            $bay = ' AND bay = %d';
            $params[] = $data['bay'];
        }

        if (array_key_exists('lane', $data)) {
            $lane = ' AND lane = %d';
            $params[] = $data['lane'];
        }

        if (array_key_exists('status', $data)) {
            $status = ' AND status = %s';
            $params[] = $data['status'];
        }

        if (array_key_exists('search', $data)) {
            $search = " AND id IN (SELECT app_id FROM $tableFields WHERE `value` LIKE %s)";
            $params[] = '%' . $this->wpdb->esc_like($data['search']) . '%';
        }

        $query = "SELECT * 
			FROM $tableName
			WHERE 1 AND date >= %s AND date <= %s {$location}{$bay}{$lane}{$status}{$search}
			ORDER BY id DESC";

        $apps = $this->wpdb->get_results($this->wpdb->prepare($query, $params), OBJECT_K);

        $ids = array_keys($apps);

        if (!empty($ids)) {
            $fields = $this->get_fields_for_apps($ids);

            foreach ($fields as $f) {
                if (array_key_exists($f->app_id, $apps)) {
                    $apps[$f->app_id]->{$f->slug} = $f->value;
                }
            }
        }

        return array_values($apps);
    }

    /**
     * List of custom fields for appointments
     *
     * @param array $ids
     * @return array|null|object
     */
    public function get_fields_for_apps($ids = array())
    {
        $meta = $this->wpdb->prefix . 'rr_meta_fields';
        $fields = $this->wpdb->prefix . 'rr_fields';

        $apps = implode(',', $ids);

        $query = "SELECT f.app_id, m.slug, f.value FROM {$meta} m JOIN {$fields} f ON (m.id = f.field_id) WHERE f.app_id IN ($apps)";
        $result = $this->wpdb->get_results($query);

        return $result;
    }

    /**
     * Get a list of email for that appointment
     *
     * @param $id
     * @return array
     */
    public function get_email_values_for_app_id($id)
    {
        $meta = $this->wpdb->prefix . 'rr_meta_fields';
        $fields = $this->wpdb->prefix . 'rr_fields';

        if (!is_numeric($id)) {
            return array();
        }

        $query = "SELECT f.value FROM {$meta} m JOIN {$fields} f ON (m.id = f.field_id) WHERE m.type = 'EMAIL' AND f.app_id = $id";
        $result = $this->wpdb->get_col($query);

        return $result;
    }

    /**
     * @param $table_name
     * @param array $order
     * @return mixed|string|void
     */
    public function get_pre_cache_json($table_name, $order = array('id' => 'DESC'))
    {
        $tmp = array();

        foreach ($order as $key => $value) {
            if (empty($key) || empty($value)) {
                continue;
            }

            $tmp[] = "{$key} {$value}";
        }

        if (count($tmp) === 0) {
            $tmp = 'id DESC';
        }

        $order = implode(',', $tmp);

        $query = "SELECT * 
			FROM {$this->wpdb->prefix}{$table_name} 
			ORDER BY {$order}";

        return json_encode($this->wpdb->get_results($query));
    }

    /**
     * @param $table_name
     * @param $id
     * @param string $output_type
     * @return array|null|object|void
     */
    public function get_row($table_name, $id, $output_type = OBJECT)
    {

        $query = $this->wpdb->prepare("SELECT * 
			FROM {$this->wpdb->prefix}{$table_name}
			WHERE id=%d",
            $id
        );

        return $this->wpdb->get_row($query, $output_type);
    }

    /**
     * @param $table_name
     * @param $data
     * @param bool $json
     * @param bool $forceStrings
     * @return bool|int|stdClass
     */
    public function replace($table_name, $data, $json = false, $forceStrings = false)
    {
        // strip out fields that are not mapped inside table
        $this->table_columns->clear_data($table_name, $data);

        // full table name
        $table_name = $this->wpdb->prefix . $table_name;

        $types = array();

        foreach ($data as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                // remove key->value
                unset($data[$key]);

                continue;
            }

            if (strlen($value) > 0 && substr($value, 0, 1) == '0') {
                $types[] = '%s';
            } else {
                if (is_float($value) && !$forceStrings) {
                    // float type
                    $types[] = '%f';

                } else if (is_integer($value) && !$forceStrings) {
                    // integer type
                    $types[] = '%d';

                } else {
                    // string as default
                    $types[] = '%s';
                }
            }
        }

        $insert_id = -1;

        // check if there is id set, if true just update
        if (array_key_exists('id', $data) && $data['id'] != '-1' && !empty($data['id'])) {
            $return = $this->wpdb->update(
                $table_name,
                $data,
                array('id' => $data['id']),
                $types
            );

            $insert_id = $data['id'];
        } else {
            // clone - new
            if (array_key_exists('id', $data)) {
                unset($data['id']);
                unset($types[0]);
            }

            $return = $this->wpdb->insert(
                $table_name,
                $data,
                $types
            );

            $insert_id = $this->wpdb->insert_id;
        }

        if ($return === false) {
            return false;
        }

        if ($json) {
            $output = new stdClass;
            $output->id = "{$insert_id}";
            return $output;
        }

        return $this->wpdb->insert_id;
    }

    /**
     * @param $table
     * @param $data
     * @param bool $json
     * @return false|int
     */
    public function delete($table, $data, $json = false)
    {

        $table_name = $this->wpdb->prefix . $table;

        if ($table == 'rr_fields') {
            return $this->wpdb->delete($table_name, array('app_id' => (int)$data['app_id']), array('%d'));
        }

        return $this->wpdb->delete($table_name, array('id' => (int)$data['id']), array('%d'));
    }

    /**
     * @param $options
     * @param string $order
     * @return array|null|object
     */
    public function get_next($options, $order = '')
    {
        $table_name = $this->wpdb->prefix . 'rr_schedules';

        $options['next'] = $this->table_columns->validate_next_step($options['next']);

        $vars = '';
        $values = array();

        foreach ($options as $key => $value) {
            if ($key === 'next') {
                continue;
            }

            if (is_numeric($value)) {
                $vars .= " AND $key=%d";
            } else {
                $vars .= " AND $key=%s";
            }

            $values[] = $value;
        }

        $query = $this->wpdb->prepare(
            "SELECT DISTINCT {$options['next']} FROM $table_name WHERE 1=1$vars",
            $values
        );

        $next_rows_raw = $this->wpdb->get_results($query, ARRAY_N);

        $next_rows = array();

        foreach ($next_rows_raw as $value) {
            $next_rows[] = $value[0];
        }

        $ids = implode(',', $next_rows);

        $entity_table = $options['next'] . 's';


        $next_table = $this->wpdb->prefix . "rr_{$entity_table}";

        $query = "SELECT * FROM $next_table WHERE id IN ({$ids})";

        if ($order != '') {
            $query .= $order;
        }

        return $this->wpdb->get_results($query);
    }

    /**
     * Check table name
     * @param  [type] $table_name [description]
     * @return bool [type]
     */
    private static function check_table_name($table_name)
    {
        $tables = array(
            'appointments',
            'schedules',
            'locations',
            'options',
            'bays',
            'lane',
            'fields',
            'meta_fields'
        );

        return in_array($table_name, $tables);
    }

    /**
     * Retrive all data for single appointment
     */
    public function get_appintment_by_id($id)
    {

        $table_app = $this->wpdb->prefix . 'rr_appointments';
        $table_bays = $this->wpdb->prefix . 'rr_bays';
        $table_lanes = $this->wpdb->prefix . 'rr_lanes';
        $table_locations = $this->wpdb->prefix . 'rr_locations';
        $table_meta = $this->wpdb->prefix . 'rr_meta_fields';
        $table_fields = $this->wpdb->prefix . 'rr_fields';

        $query = $this->wpdb->prepare("SELECT 
				a.*,
				s.name AS bay_name,
				s.duration AS bay_duration,
				s.price AS bay_price,
				w.name AS lane_name,
				l.name AS location_name,
				l.address AS location_address,
				l.location AS location_location
			FROM 
				{$table_app} a 
			JOIN 
				{$table_bays} s
				ON(a.bay = s.id)
			JOIN 
				{$table_locations} l
				ON(a.location = l.id)
			JOIN 
				{$table_lanes} w
				ON(a.lane = w.id)
			WHERE a.id = %d", $id);

        $results = $this->wpdb->get_results($query, ARRAY_A);

        $f_query = $this->wpdb->prepare("SELECT m.slug, f.value FROM {$table_meta} m JOIN $table_fields f ON (m.id = f.field_id) WHERE f.app_id = %d", $id);

        $fields = $this->wpdb->get_results($f_query);

        if (count($results) == 1) {
            foreach ($fields as $f) {
                $results[0][$f->slug] = $f->value;
            }

            return $results[0];
        }

        return array();
    }

    /**
     * Removes reservation older then 6 minutes
     */
    public function delete_reservations()
    {
        $table_app = $this->wpdb->prefix . 'rr_appointments';

        $query = "DELETE FROM $table_app WHERE status = 'reservation' AND created < (NOW() - INTERVAL 6 MINUTE)";

        $this->wpdb->query($query);
    }

    /**
     * @param $table_name
     * @param null $location_id
     * @param null $bay_id
     * @param null $lane_id
     * @return array|null|object
     */
    public function get_frontend_select_options($table_name, $location_id = null, $bay_id = null, $lane_id = null)
    {
        $table = $this->wpdb->prefix . $table_name;
        $schedules = $this->wpdb->prefix . 'rr_schedules';

        $query = '';

        switch ($table_name) {
            case 'rr_locations':
                $query  = "SELECT DISTINCT l.* FROM {$table} l INNER JOIN $schedules c ON (l.id = c.location) WHERE c.is_working=1";

                if (!empty($bay_id) && is_numeric($bay_id)) {
                    $query .= ' AND c.bay=' . $bay_id;
                }

                if (!empty($lane_id) && is_numeric($lane_id)) {
                    $query .= ' AND c.lane=' . $lane_id;
                }

                $query .= $this->get_order_by_part('rr_locations', true);

                break;
            case 'rr_bays':
                $query  = "SELECT DISTINCT s.* FROM {$table} s INNER JOIN $schedules c ON (s.id = c.bay) WHERE c.is_working=1";

                if (!empty($location_id) && is_numeric($location_id)) {
                    $query .= ' AND c.location=' . $location_id;
                }

                if (!empty($lane_id) && is_numeric($lane_id)) {
                    $query .= ' AND c.lane=' . $lane_id;
                }

                $query .= $this->get_order_by_part('rr_bays', true);

                break;
            case 'rr_lanes':
                $query  = "SELECT DISTINCT w.* FROM {$table} w INNER JOIN $schedules c ON (w.id = c.lane) WHERE c.is_working=1";

                if (!empty($location_id) && is_numeric($location_id)) {
                    $query .= ' AND c.location=' . $location_id;
                }

                if (!empty($bay_id) && is_numeric($bay_id)) {
                    $query .= ' AND c.bay=' . $bay_id;
                }

                $query .= $this->get_order_by_part('rr_lanes', true);

                break;
        };

        return $this->wpdb->get_results($query);
    }

    public function clear_options($type = 'default')
    {
        $table = $this->wpdb->prefix . 'rr_options';

        $query = $this->wpdb->prepare("DELETE FROM $table WHERE `type` = %s", $type);
        $this->wpdb->query($query);
    }

    /**
     *
     */
    public function get_schedules_combinations()
    {
        $schedules = $this->wpdb->prefix . 'rr_schedules';

        $query = "SELECT location, bay, lane FROM $schedules WHERE is_working=1";

        return $this->wpdb->get_results($query);
    }

    /**
     * @return array
     */
    public function get_all_tags_for_template()
    {
        $fields = json_decode($this->get_pre_cache_json('rr_meta_fields', array('position' => 'ASC')), true);

        // default tags
        $default = array(
            'id', 'location', 'bay', 'lane', 'date', 'start', 'end', 'end_date', 'status', 'user', 'price', 'ip', 'session'
        );

        $mapped = array_map(function($element) {
            return $element['slug'];
        }, $fields);

        return array_merge($default, $mapped);
    }

    /**
     * @return int
     */
    public function get_next_meta_field_id() {
        $meta = $this->wpdb->prefix . 'rr_meta_fields';

        $query = "SELECT MAX(id) FROM $meta";

        $max = (int)$this->wpdb->get_var($query);

        return $max + 1;
    }


    public function update_option($option)
    {
        $table_name = $this->wpdb->prefix . 'rr_options';
        $key = $option['rr_key'];
        $query = $this->wpdb->prepare("DELETE FROM $table_name WHERE rr_key=%s", $key);

        $this->wpdb->query($query);

        return $this->wpdb->insert($table_name, $option);
    }

    public function get_wpdb() {
        return $this->wpdb;
    }

    /**
     * @param string $table_name
     * @param bool $as_string
     * @return string|array
     */
    public function get_order_by_part($table_name, $as_string = false)
    {
        /**
         *
         */
        $mapping = array(
            'rr_locations' => array(
                'sort'  => 'sort.locations-by',
                'order' => 'order.locations-by'
            ),
            'rr_lanes'   => array(
                'sort'  => 'sort.lanes-by',
                'order' => 'order.lanes-by'
            ),
            'rr_bays'  => array(
                'sort'  => 'sort.bays-by',
                'order' => 'order.bays-by'
            ),
        );

        if (!array_key_exists($table_name, $mapping)) {
            if ($as_string) {
                return " ORDER BY `id` DESC";
            }

            return array('id' => 'DESC');
        }

        $column = $this->options->get_option_value($mapping[$table_name]['sort'], 'id');
        $order = $this->options->get_option_value($mapping[$table_name]['order'], 'DESC');

        if (!in_array($order, array('ASC', 'DESC'))) {
            if ($as_string) {
                return " ORDER BY `id` DESC";
            }

            return array('id' => 'DESC');
        }

        $this->wpdb->escape_by_ref($column);
        $this->wpdb->escape_by_ref($order);

        if ($as_string) {

            return " ORDER BY `$column` $order";
        }

        return array($column => $order);
    }

    public static function get_custom_fields_tags()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'rr_meta_fields';

        $fields = $wpdb->get_col('SELECT CONCAT(\'#\', `slug`, \'#\') FROM ' . $table_name);

        return $fields;
    }

    public function get_lane_id_by_email($email)
    {
        $table_name = $this->wpdb->prefix . 'rr_lanes';

        $query = $this->wpdb->prepare("SELECT id FROM {$table_name} WHERE email = %s", array($email));

        return $this->wpdb->get_var($query);
    }
}
