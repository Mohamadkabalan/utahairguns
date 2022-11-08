<?php

class RRTableColumns
{
    public function __construct()
    {
    }

    /**
     * @param string $table_name
     * @return array
     */
    public function get_columns($table_name) {

        $columns = array(
            'rr_appointments' => array(
                'id',
                'location',
                'bay',
                'lane',
                'name',
                'email',
                'phone',
                'date',
                'start',
                'end',
                'end_date',
                'description',
                'status',
                'user',
                'created',
                'price',
                'ip',
                'session'
            ),
            'rr_schedules' => array(
                'id',
                'group_id',
                'location',
                'bay',
                'lane',
                'slot_count',
                'day_of_week',
                'time_from',
                'time_to',
                'day_from',
                'day_to',
                'is_working'
            ),
            'rr_meta_fields' => array(
                'id',
                'type',
                'slug',
                'label',
                'mixed',
                'default_value',
                'visible',
                'required',
                'validation',
                'position'
            ),
            'rr_locations' => array(
                'id',
                'name',
                'address',
                'location',
                'cord'
            ),
            'rr_bays' => array(
                'id',
                'name',
                'bay_color',
                'duration',
                'slot_step',
                'block_before',
                'block_after',
                'daily_limit',
                'price'
            ),
            'rr_options' => array(
                'id',
                'rr_key',
                'rr_value',
                'type'
            ),
            'rr_lanes' => array(
                'id',
                'name',
                'description'
            ),
            'rr_fields' => array()
        );


        return $columns[$table_name];
    }

    public function validate_next_step($next) {
        $options = array(
            'location',
            'bay',
            'lane',
            'stuff',
        );

        if (in_array($next, $options)) {
            return $next;
        }

        return $options[0];
    }

    /**
     * @param string $table_name
     * @param array $params
     */
    public function clear_data($table_name, &$params) {
        $columns = $this->get_columns($table_name);

        if (empty($columns)) {
            return;
        }

        foreach ($params as $key => $param) {
            if (!in_array($key, $columns)) {
                unset($params[$key]);
            }
        }
    }

    public static function clear_settings_data_frontend($rr_settings) {
        $white_list = array(
            'MetaFields',
            'advance.redirect',
            'advance_cancel.redirect',
            'block.time',
            'block_days',
            'block_days_tooltip',
            'cal_auto_select',
            'cancel.scroll',
            'captcha.site-key',
            'captcha3.site-key',
            'check',
            'compatibility.mode',
            'css.off',
            'currency.before',
            'date_format',
            'datepicker',
            'default_date',
            'default_datetime_format',
            'form.label.above',
            'gdpr.label',
            'gdpr.link',
            'gdpr.message',
            'gdpr.on',
            'layout_cols',
            'max.appointments',
            'max_date',
            'min_date',
            'order.locations-by',
            'order.bays-by',
            'order.lanes-by',
            'pre.reservation',
            'price.hide',
            'price.hide.bay',
            'rtl',
            'save_form_content',
            'scroll_off',
            'show.iagree',
            'show_remaining_slots',
            'show_week',
            'sort.locations-by',
            'sort.bays-by',
            'sort.lanes-by',
            'start_of_week',
            'submit.redirect',
            'time_format',
            'trans.ajax-call-not-available',
            'trans.booking-overview',
            'trans.cancel',
            'trans.comment',
            'trans.currency',
            'trans.date-time',
            'trans.done_message',
            'trans.email',
            'trans.error-email',
            'trans.error-name',
            'trans.error-phone',
            'trans.field-iagree',
            'trans.field-required',
            'trans.fields',
            'trans.iagree',
            'trans.internal-error',
            'trans.location',
            'trans.name',
            'trans.nonce-expired',
            'trans.overview-message',
            'trans.personal-informations',
            'trans.phone',
            'trans.please-select-new-date',
            'trans.price',
            'trans.bay',
            'trans.slot-not-selectable',
            'trans.submit',
            'trans.lane',
            'width',
            'form.label.above',
            'form_class',
            'label.from_to'
        );

        foreach ($rr_settings as $key => $value) {
            if (!in_array($key, $white_list)) {
                unset($rr_settings[$key]);
            }
        }

        return $rr_settings;
    }
}