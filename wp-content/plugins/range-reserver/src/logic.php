<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


/**
 * Class responsible for App logic
 * reservation, free times...
 */
class RRLogic
{

    /**
     * @var RRDBModels
     */
    protected $models;

    /**
     * @var RROptions
     */
    protected $options;

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var array Cache for bays
     */
    protected $bay_cache = [];

    /**
     * @var RRSlotsLogic
     */
    protected $slots_logic;

    /**
     * RRLogic constructor.
     * @param wpdb $wpdb
     * @param RRDBModels $models
     * @param RROptions $options
     */
    function __construct($wpdb, $models, $options, $slots_logic)
    {
        $this->wpdb = $wpdb;
        $this->models = $models;
        $this->options = $options;
        $this->slots_logic = $slots_logic;
    }

    /**
     * Get all open slots / times
     *
     * @param  int $location Location
     * @param  int $bay Bay
     * @param  int $lane Lane
     * @param  datetime $day DateTime
     * @param  int $app_id Previus appointment
     * @param  bool $check_current_day Previus appointment
     * @param  int $block_before
     * @return array Array of free times
     */
    public function get_open_slots(
        $location = null,
        $bay = null,
        $lane = null,
        $day = null,
        $app_id = null,
        $check_current_day = true,
        $block_before = 0
    )
    {
        // current day as weekday now (string)
        $day_of_week = date('l', strtotime($day));

        // get current datetime as int
        $time_now = current_time('timestamp', false);

        // add block minutes
        $block_time = $time_now + $block_before * 60;

        // calculate if that is current day that we are looking
        $is_current_day = (date('Y-m-d') == $day);

        $block_date = date('Y-m-d', $block_time);
         if(!$bay && !$lane){
             $query = $this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}rr_schedules WHERE  
			location=%d AND 
            day_of_week LIKE %s AND 
			is_working = 1 AND 
			(day_from IS NULL OR day_from <= %s) AND 
			(day_to IS NULL OR day_to >= %s)",
                 $location,"%{$day_of_week}%", $day, $day
             );
         }else{
             $query = $this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}rr_schedules WHERE 
			location=%d AND 
			bay=%d AND 
			lane=%d AND 
			day_of_week LIKE %s AND 
			is_working = 1 AND 
			(day_from IS NULL OR day_from <= %s) AND 
			(day_to IS NULL OR day_to >= %s)",
                 $location, $bay, $lane, "%{$day_of_week}%", $day, $day
             );
         }


        $open_days = $this->wpdb->get_results($query);

        $working_hours = array();



        /**
         * Example on Appointment 08:00 - 20:00 today
         */
        foreach ($open_days as $working_day) {
            $bayObj = $this->get_bay($working_day->bay);
            // upper time 20:00;
            $upper_time = strtotime($working_day->time_to);

            $counter = 0;

            while (true) {
                // 08:00 at first
                $temp_time = strtotime($working_day->time_from);

                // use smaller step
                if (!empty($bayObj->slot_step)) {
                    $run_time = $bayObj->slot_step * 60 * $counter++;
                } else {
                    $run_time = $bayObj->duration * 60 * $counter++;
                }

                // 08:00 at first pass, second 09:00
                $temp_time += $run_time;

                $temp_date_time = strtotime("$day {$working_day->time_from}") + $run_time;

                // is that before upper time limit
                if ($temp_time < $upper_time) {
                    $current_time = date('H:i', $temp_time);

                    // check if current time is greater then slot start time
                    if ($check_current_day && $is_current_day && $time_now > $temp_time) {
                        continue;
                    }

                    // block time - skip if it is under block time
                    if ($block_before > 0 && $check_current_day && $block_time > $temp_date_time) {
                        continue;
                    }

                    // slot count
                    $slot_count = is_numeric($working_day->slot_count) ? (int) $working_day->slot_count : 1;

                    if (!array_key_exists($current_time, $working_hours)) {
                        $working_hours[$current_time] = $slot_count;
                    } else {
                        $working_hours[$current_time] += $slot_count;
                    }
                } else {
                    break;
                }
            }
        }

        $bay_duration = $bayObj->duration;

        if (!empty($bayObj->slot_step)) {
            $bay_duration = $bayObj->slot_step;
        }

        // remove non-working time
        $this->remove_closed_slots($working_hours, $location, $bay, $lane, $day, $bay_duration);

        // remove already reserved times
        $this->remove_reserved_slots($working_hours, $location, $bay, $lane, $day, $bay_duration, $app_id);

        // format time
        return $this->format_time($working_hours, $bayObj->duration);
    }

    /**
     * Remove times when is not working
     *
     * @param  array &$slots Free slots
     * @param  int $location Location
     * @param  int $bay Bay
     * @param  int $lane Lane
     * @param  DateTime $day DateTime
     * @param  time $bay_duration Bay duration in minuts
     * @return null
     */
    private function remove_closed_slots(&$slots, $location = null, $bay = null, $lane = null, $day = null, $bay_duration = 60)
    {
        $day_of_week = date('l', strtotime($day));

        $query = $this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}rr_schedules WHERE 
			location=%d AND 
			bay=%d AND 
			lane=%d AND 
			day_of_week LIKE %s AND 
			is_working = 0 AND 
			(day_from IS NULL OR day_from <= %s) AND 
			(day_to IS NULL OR day_to >= %s)",
            $location, $bay, $lane, "%{$day_of_week}%", $day, $day
        );

        $closed_days = $this->wpdb->get_results($query);


        // check all no working times
        foreach ($closed_days as $working_day) {

            $lower_time = strtotime($working_day->time_from);
            $upper_time = strtotime($working_day->time_to);

            $counter = 0;

            // check slots
            foreach ($slots as $temp_time => $value) {
                $current_time = strtotime($temp_time);
//                $current_time_end = strtotime("$temp_time + $bay_duration minute");
                $current_time_end = strtotime("$temp_time + $bay_duration minute");

                if ($lower_time < $current_time && $upper_time <= $current_time) {
                    // before
                } else if ($lower_time >= $current_time_end && $upper_time > $current_time_end) {
                    // after
                } else {
                    // remove slot
                    $slot_count = is_numeric($working_day->slot_count) ? (int) $working_day->slot_count : 1;
                    $slots[$temp_time] = $value - $slot_count;
                }
            }
        }
    }

    /**
     * Can make reservation for that ip
     *
     * @param $data
     * @return bool Can make reservation
     */
    public function can_make_reservation($data)
    {
        $ip = $data['ip'];

        $result = array(
            'status'  => true,
            'message' => ''
        );

        $query = $this->wpdb->prepare(
            "SELECT id AS no_apps FROM {$this->wpdb->prefix}rr_appointments WHERE 
				ip=%s AND 
				status IN ('abandoned', 'pending') AND
				created >= now() - INTERVAL 1 DAY",
            $ip
        );

        $appIds = $this->wpdb->get_col($query);

        $maxNumber = (int) $this->options->get_option_value('max.appointments', 10);

        if (count($appIds) >= $maxNumber) {
            $result['status'] = false;
            $result['message'] = $maxNumber . __('Daily limit of booking request has been reached. Please contact us by email!', 'range-reserver');
        }

        $result = apply_filters( 'rr_can_make_reservation', $result, $data);

        return $result;
    }

    public function can_update_reservation($appointment, $data)
    {
        $result = array(
            'status'  => true,
            'message' => ''
        );

        $result = apply_filters( 'rr_can_update_reservation', $result, $appointment, $data);

        return $result;
    }

    /**
     * Remove times that are reserved (already booked)
     *
     * @param  array &$slots Free slots
     * @param  int $location Location
     * @param  int $bay Bay
     * @param  int $lane Lane
     * @param  DateTime $day DateTime
     * @param  time $bay_duration Bay duration in minuts
     * @param int $app_id
     */
    private function remove_reserved_slots(&$slots, $location, $bay, $lane, $day, $bay_duration, $app_id = -1)
    {
        if ($app_id == "") {
            $app_id = -1;
        }

//        $day_of_week = date('l', strtotime($day));

        $query = $this->slots_logic->get_busy_slot_query($location, $bay, $lane, $day, $app_id);

        $appointments = $this->wpdb->get_results($query);

        // dailyLimit section
        $currentBay = $this->get_bay($bay);
        $limit = $currentBay->daily_limit ? (int) $currentBay->daily_limit : 0;
        $bayCount = 0;
        foreach ($appointments as $app) {
            if ($bay === $app->bay) {
                $bayCount++;
            }
        }
        $limitReached = $limit > 0 && $limit <= $bayCount;
        // dailyLimit section end

        // check all no working times
        foreach ($appointments as $app) {
            $start = ($app->date == $day) ? $app->start : '00:00';
            $end = ($app->end_date == $day) ? $app->end : '23:59';

            $lower_time = strtotime($start);
            $upper_time = strtotime($end);

            $bayObj = $this->get_bay($app->bay);
            // add block before and after time
            if (!empty($bayObj)) {
                $lower_time -= ($bayObj->block_before * 60);
                $upper_time += ($bayObj->block_after * 60);
            }

            // all day event fix
            // if ($app->end === '00:00:00' || $upper_time < $lower_time) {
                // $upper_time = strtotime('23:59:59');
            // }

            // check slots
            foreach ($slots as $temp_time => $value) {
                // if we reached daily limit no point to go to calculation
                if ($limitReached) {
                    $slots[$temp_time] = 0;
                    continue;
                }

                $slot_time = strtotime($temp_time);
                $slot_time_end = strtotime("$temp_time + $bay_duration minute");

                // before / after
                if ($slot_time_end <= $lower_time || $upper_time <= $slot_time) { } else {
                    // Cross time - remove one slot
                    $slots[$temp_time] = $value - 1;
                }
            }
        }
    }

    /**
     * Return bay
     *
     * @param $bay_id
     * @return array|null|object|void
     */
    public function get_bay($bay_id)
    {
        if (array_key_exists($bay_id, $this->bay_cache)) {
            return $this->bay_cache[$bay_id];
        }

        $model = $this->models->get_row('rr_bays', $bay_id);

        $this->bay_cache[$bay_id] = $model;

        return $model;
    }

    /**
     * Get all statuses
     *
     * @return array
     */
    public function getStatus()
    {
        return array(
            'pending'     => __('pending', 'range-reserver'),
            'reservation' => __('reservation', 'range-reserver'),
            'abandoned'   => __('abandoned', 'range-reserver'),
            'canceled'    => __('cancelled', 'range-reserver'),
            'confirmed'   => __('confirmed', 'range-reserver'),
        );
    }

    /**
     * Translation for current statu
     */
    public function get_status_translation($status)
    {
        $statusCollection = $this->getStatus();

        if (array_key_exists($status, $statusCollection)) {
            return $statusCollection[$status];
        }

        return '';
    }

    /**
     * Time format function
     *
     * @param array &$times Array of slots
     * @param int $bay_duration
     * @return array         Result times array
     */
    public function format_time(&$times, $bay_duration)
    {
        $result = array();

        $format = $this->options->get_option_value('time_format');

        foreach ($times as $time => $count) {
            switch ($format) {
                case '00-24':
                    $result[] = array(
                        'count' => $count,
                        'value' => $time,
                        'show'  => $time,
                        'ends'  => date('G:i', strtotime("{$time} + $bay_duration minute")),
                        'duration' => $bay_duration
                    );
                    break;
                case 'am-pm':
                    $result[] = array(
                        'count' => $count,
                        'value' => $time,
                        'show'  => date( 'h:i a', strtotime($time)),
                        'ends'  => date('h:i a', strtotime("{$time} + $bay_duration minute")),
                        'duration' => $bay_duration
                    );
                    break;
                default:
                    $result[] = $time;
                    break;
            }
        }

        return $result;
    }
    public function available_locations_by_time_slot($data){
        $days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $day=$data['day'];
        $slot=$data['slot'];

        $day_of_week = $days[date('w', strtotime($day))];
        $sql="SELECT * FROM {$this->wpdb->prefix}rr_schedules WHERE 
			day_of_week LIKE %s AND 
			is_working = 1 AND
			(day_from IS NULL OR day_from <= %s) AND 
			(day_to IS NULL OR day_to >= %s)";
        if(isset($data['location'])){
            $location=$data['location'];
            $sql=$sql." and location='$location'";
        }
        if(isset($data['bay'])){
            $bay=$data['bay'];
            $sql=$sql." and bay='$bay'";
        }
        $query = $this->wpdb->prepare($sql,
            "%{$day_of_week}%", $day, $day
        );

        $results = $this->wpdb->get_results($query);
        $response=[];
        $locs=[];
        foreach ($results as $result){
            $locations= $this->wpdb->get_col( $this->wpdb->prepare( "SELECT name  from ".$this->wpdb->prefix."rr_locations where id=".$result->location));
            if(isset($locations[0])) {
                $location = $locations[0];
            }
            $bays= $this->wpdb->get_col( $this->wpdb->prepare( "SELECT name  from ".$this->wpdb->prefix."rr_bays where id=".$result->bay));
            if(isset($bays[0])) {
                $bay = $bays[0];
            }
            $lanes= $this->wpdb->get_col( $this->wpdb->prepare( "SELECT name  from ".$this->wpdb->prefix."rr_lanes where id=".$result->lane));
            if(isset($lanes[0])) {
                $lane = $lanes[0];
            }
            $locationFound=False;
            foreach ($locs as $locKey => $loc){
                if($loc['id']==$result->location){
                    $locationFound=true;
                    $bayFound=false;
                    foreach ($loc['bays'] as $bayKey => $bayArr){
                        if($bayArr['id']==$result->bay){
                            $bayFound=true;
                            $laneFound=false;
                            foreach ($bayArr['lanes'] as $laneKey => $laneArr){
                                if($laneArr['id']==$result->lane){
                                    $laneFound=true;
                                }
                            }
                            if(!$laneFound){
                                array_push($locs[$locKey]['bays'][$bayKey]['lanes'],['id'=>$result->lane,'name'=>$lane]);
                            }
                        }
                    }
                    if(!$bayFound){
                        array_push($locs[$locKey]['bays'],['id'=>$result->bay,'name'=>$bay,'lanes'=>[['id'=>$result->lane,'name'=>$lane]]]);
                    }

                }
            }
            if(!$locationFound){
                array_push($locs,['id'=>$result->location,'name'=>$location,'bays'=>[['id'=>$result->bay,'name'=>$bay,'lanes'=>[['id'=>$result->lane,'name'=>$lane]]]]]);
            }

        }
       return $locs;
    }
}