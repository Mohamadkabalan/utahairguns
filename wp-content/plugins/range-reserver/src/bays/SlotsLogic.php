<?php

class RRSlotsLogic {
    private $LANE = '0';
    private $MATCH_ALL = '1';
    private $LOCATION = '2';
    private $BAY = '3';

    private $PLACEHOLDER = '#DYNAMIC#';

    /**
     * @var RROptions
     */
    protected $options;

    /**
     * @var wpdb
     */
    protected $wpdb;

    public function __construct($wpdb, $options)
    {
        $this->wpdb = $wpdb;
        $this->options = $options;
    }

    public function get_busy_slot_query($location, $bay, $lane, $day, $app_id)
    {
        $mode = $this->options->get_option_value('multiple.work', '1');
        $table_name = "{$this->wpdb->prefix}rr_appointments";
        $static_part = "SELECT * FROM {$table_name} WHERE 
			{$this->PLACEHOLDER} AND 
			date <= %s AND
			end_date >= %s AND
			id <> %d AND 
			status NOT IN ('abandoned','canceled')";

        $params = array();

        switch ($mode) {
            case $this->LOCATION:
                $dynamic_part = 'location=%d';
                $params[] = $location;
                break;
            case $this->BAY:
                $dynamic_part = 'bay=%d';
                $params[] = $bay;
                break;
            case $this->MATCH_ALL:
                $dynamic_part = 'location=%d AND bay=%d AND lane=%d';
                $params[] = $location;
                $params[] = $bay;
                $params[] = $lane;
                break;
            case $this->LANE:
            default:
                $dynamic_part = 'lane=%d';
                $params[] = $lane;
                break;
        }


        $params[] = $day;
        $params[] = $day;
        $params[] = $app_id;
        $full_query = str_replace($this->PLACEHOLDER, $dynamic_part, $static_part);

        return $this->wpdb->prepare($full_query, $params);
    }
}