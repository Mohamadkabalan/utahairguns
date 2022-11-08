<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Report class
 */
class RRReport
{

    /**
     * @var RRLogic
     */
    protected $logic;

    /**
     * @var RROptions
     */
    protected $options;

    /**
     * RRReport constructor.
     * @param RRLogic $logic
     * @param RROptions $options
     */
    function __construct($logic, $options)
    {
        $this->logic = $logic;
        $this->options = $options;
    }

    /**
     * Main function for reports
     * @param  string $report Report type
     * @param  array $params All params for report
     * @return array          Report data
     */
    public function get($report, $params)
    {
        $result = null;

        switch ($report) {
            case 'overview':

                $result = $this->get_whole_month_slots(
                    $params['location'],
                    $params['bay'],
                    $params['lane'],
                    $params['month'],
                    $params['year']
                );

                break;

            default:
                # code...
                break;
        }

        return $result;
    }

    /**
     * Get open times for whole month
     *
     * @param  int $location Location
     * @param  int $bay Bay
     * @param  int $lane Lane
     * @param  string $month Month
     * @param  string $year Year
     * @param  int $block_time Block time in minutes
     * @return array            Result for report
     */
    public function get_whole_month_slots($location, $bay, $lane, $month, $year, $current_day = false, $block_time = 0)
    {

        $result = array();

        $num_of_days = date('t', strtotime($year . '-' . $month . '-01'));
        for ($i = 1; $i <= $num_of_days; $i++) {
            $day = $year . "-" . sprintf("%02d", $month) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);

            $result[$day] = $this->logic->get_open_slots($location, $bay, $lane, $day, null, $current_day, $block_time);
        }

        return $result;
    }

    /**
     * @param $location
     * @param $bay
     * @param $lane
     * @param $month
     * @param $year
     * @return array
     */
    public function get_available_dates($location, $bay, $lane, $month, $year)
    {

        $block_time = $this->options->get_option_value('block.time', 0);

        $slots = $this->get_whole_month_slots($location, $bay, $lane, $month, $year, true, $block_time);

        $currentDate = date('Y-m-d');

        $result = array();

        foreach ($slots as $key => $value) {
            if ($currentDate > $key) {
                continue;
            }

            if (count($value) == 0) {
                $result[$key] = 'no-slots';
                continue;
            }

            $has_free = false;
            foreach ($value as $k => $v) {
                if (((int)$v['count']) > 0) {
                    $result[$key] = 'free';
                    $has_free = true;
                    break;
                }
            }

            if (!$has_free) {
                $result[$key] = 'busy';
            }
        }

        return $result;
    }
    /**
     * @param $location
     * @param $bay
     * @param $lane
     * @param $month
     * @param $year
     * @return array
     */
    public function get_available_days($data)
    {

        $block_time = $this->options->get_option_value('block.time', 0);
        $current_day=true;
        $location=$data['location'];
        $year=$data['year'];
        $month=$data['month'];

        $slots = $this->get_whole_slots($current_day,$block_time,$location,$year,$month);

        $currentDate = date('Y-m-d');

        $result = array();

        foreach ($slots as $key => $value) {
            if ($currentDate > $key) {
                continue;
            }

            if (count($value) == 0) {
                $result[$key] = 'no-slots';
                continue;
            }

            $has_free = false;
            foreach ($value as $k => $v) {
                if (((int)$v['count']) > 0) {
                    $result[$key] = 'free';
                    $has_free = true;
                    break;
                }
            }

            if (!$has_free) {
                $result[$key] = 'busy';
            }
        }

        return $result;
    }
    /**
     * Get open times for whole month
     *
     * @param  int $location Location
     * @param  int $bay Bay
     * @param  int $lane Lane
     * @param  string $month Month
     * @param  string $year Year
     * @param  int $block_time Block time in minutes
     * @return array            Result for report
     */
    public function get_whole_slots($current_day = false, $block_time = 0,$location,$year,$month)
    {

        $result = array();

        $num_of_days = date('t', strtotime($year . '-' . $month . '-01'));
        for ($i = 1; $i <= $num_of_days; $i++) {
            $day = $year . "-" . sprintf("%02d", $month) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);

            $result[$day] = $this->logic->get_open_slots($location, null, null, $day, null, $current_day, $block_time);
        }

        return $result;
    }

}