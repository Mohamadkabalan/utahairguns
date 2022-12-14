<?php


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Class Options bay
 */
class RROptions
{
    /**
     * @var wpdb
     */
    protected $wpdb;

    protected $current_options;

    /**
     * RROptions constructor.
     * @param $wpdb
     */
    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;

        add_action('rr_update_options', array($this, 'manage_gdpr_cron'));
    }

    public function manage_gdpr_cron($options) {
        $set_cron = false;

        foreach ($options as $option) {
            if ($option['rr_key'] === 'gdpr.auto_remove' && !empty($option['rr_value'])) {
                $set_cron = true;
                break;
            }
        }
        // remove cron
        wp_clear_scheduled_hook('rr_gdpr_auto_delete');

        if ($set_cron) {
            // set cron
            wp_schedule_event(time(), 'daily', 'rr_gdpr_auto_delete');
        }
    }

    public function get_default_options() {
        return array(
            'mail.pending'                  => 'pending',
            'mail.reservation'              => 'reservation',
            'mail.canceled'                 => 'canceled',
            'mail.confirmed'                => 'confirmed',
            'mail.admin'                    => '',
            'mail.action.two_step'          => '0',
            'trans.bay'                 => 'Bay',
            'trans.location'                => 'Location',
            'trans.lane'                  => 'Lane',
            'trans.done_message'            => 'Done',
            'time_format'                   => '00-24',
            'trans.currency'                => '$',
            'pending.email'                 => '',
            'price.hide'                    => '0',
            'price.hide.bay'            => '0',
            'datepicker'                    => 'en-US',
            'send.user.email'               => '0',
            'custom.css'                    => '',
            'form.label.above'              => '0',
            'show.iagree'                   => '0',
            'cancel.scroll'                 => 'calendar',
            'multiple.work'                 => '1',
            'compatibility.mode'            => '0',
            'pending.subject.email'         => 'New Reservation #id#',
            'send.from.email'               => '',
            'css.off'                       => '0',
            'submit.redirect'               => '',
            'advance.redirect'              => '[]',
            'advance_cancel.redirect'       => '[]',
            'pending.subject.visitor.email' => 'Reservation #id#',
            'block.time'                    => '0',
            'max.appointments'              => '5',
            'pre.reservation'               => '0',
            'default.status'                => 'pending',
            'send.lane.email'             => '0',
            'currency.before'               => '0',
            'nonce.off'                     => '0',
            'gdpr.on'                       => '0',
            'gdpr.label'                    => 'By using this form you agree with the storage and handling of your data by this website.',
            'gdpr.link'                     => '',
            'gdpr.message'                  => 'You need to accept the privacy checkbox',
            'gdpr.auto_remove'              => '0',
            'sort.lanes-by'               => 'id',
            'sort.bays-by'              => 'id',
            'sort.locations-by'             => 'id',
            'order.lanes-by'              => 'DESC',
            'order.bays-by'             => 'DESC',
            'order.locations-by'            => 'DESC',
            'captcha.site-key'              => '',
            'captcha3.site-key'             => '',
            'captcha.secret-key'            => '',
            'captcha3.secret-key'           => '',
            'fullcalendar.public'           => '0',
            'fullcalendar.event.show'       => '0',
            'fullcalendar.event.template'   => '',
            'shortcode.compress'            => '1',
            'label.from_to'                 => '0',
        );
    }

    /**
     * Get data that are going to be inserted to database
     *
     * @return array
     */
    public function get_insert_options()
    {
        $options = $this->get_default_options();
        $output = array();

        foreach ($options as $key => $value) {
            $output[] = array(
                'rr_key'   => $key,
                'rr_value' => $value,
                'type'     => 'default'
            );
        }

        return $output;
    }

    public function get_mixed_options()
    {
        $missing = array();

        $defaults = $this->get_insert_options();
        $current = $this->cache_options();

        foreach ($defaults as $default) {
            $is_missing = true;

            foreach ($current as $option) {
                if ($option['rr_key'] == $default['rr_key']) {
                    $is_missing = false;
                }
            }

            if ($is_missing) {
                $missing[] = $default;
            }
        }

        return array_merge($current, $missing);

    }

    /**
     * Options for cache inline usage on front-end
     *
     * @return array
     */
    public function cache_options()
    {
        $options = $this->get_options();

        $output = array();

        foreach ($options as $key => $value) {
            $output[] = array(
                'rr_key'   => $key,
                'rr_value' => $value,
                'type'     => 'default'
            );
        }

        return $output;
    }

    /**
     * Get option from database
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public function get_option_value($key, $default = null)
    {
        // load options if there are not cached
        if (empty($this->current_options)) {
            $this->current_options = $this->get_options_from_db();
        }

        if (!array_key_exists($key, $this->current_options)) {
            return $default;
        }

        return $this->current_options[$key];
    }

    /**
     * Get all EA options [key => value]
     *
     * @return array
     */
    public function get_options()
    {
        // load options if there are not cached
        if (empty($this->current_options)) {
            $this->current_options = $this->get_options_from_db();
        }

        return $this->current_options;
    }

    /**
     * Get options, default options are overwritten by db ones
     *
     * @return array
     */
    protected function get_options_from_db()
    {
        $table_name = $this->wpdb->prefix . 'rr_options';

        $query =
            "SELECT rr_key, rr_value 
             FROM $table_name";

        $output = $this->wpdb->get_results($query, OBJECT_K);

        $db_options = array();

        foreach ($output as $key => $value) {
            $db_options[$key] = $value->rr_value;
        }

        $default = $this->get_default_options();

        // combine options from db and defaults
        return array_merge($default, $db_options);
    }
}
