<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Home of the front end short codes
 */
class RRFrontend
{

    /**
     * @var boolean
     */
    protected $generate_next_option = true;

    /**
     * @var RROptions
     */
    protected $options;

    /**
     * @var RRDBModels
     */
    protected $models;

    /**
     * @var RRDateTime
     */
    protected $datetime;

    /**
     * @var RRUtils
     */
    protected $utils;

    /**
     * @param RRDBModels $models
     * @param RROptions $options
     * @param $datetime
     * @param RRUtils $utils
     */
    function __construct($models, $options, $datetime, $utils)
    {
        $this->options  = $options;
        $this->models   = $models;
        $this->datetime = $datetime;
        $this->utils    = $utils;
    }

    public function init()
    {
        // register JS
        add_action('wp_enqueue_scripts', array($this, 'init_scripts'));
        // add_action( 'admin_enqueue_scripts', array( $this, 'init' ) );

        // add shortcode standard
        add_shortcode('rr_standard', array($this, 'standard_app'));

        // bootstrap form
        add_shortcode('rr_bootstrap', array($this, 'rr_bootstrap'));
    }

    /**
     * Front end init
     */
    public function init_scripts()
    {
        // start session
        if (!headers_sent() && !session_id()) {
            session_start();
        }

        // bootstrap script
        wp_register_script(
            'rr-momentjs',
            RR_PLUGIN_URL . 'js/libs/moment.min.js',
            array(),
            RANGE_RESERVER_VERSION,
            true
        );

        wp_register_script(
            'rr-validator',
            RR_PLUGIN_URL . 'js/libs/jquery.validate.min.js',
            array('jquery'),
            RANGE_RESERVER_VERSION,
            true
        );

        wp_register_script(
            'rr-masked',
            RR_PLUGIN_URL . 'js/libs/jquery.inputmask.min.js',
            array('jquery'),
            RANGE_RESERVER_VERSION,
            true
        );

        wp_register_script(
            'rr-datepicker-localization',
            RR_PLUGIN_URL . 'js/libs/jquery-ui-i18n.min.js',
            array('jquery', 'jquery-ui-datepicker'),
            RANGE_RESERVER_VERSION,
            true
        );

        // frontend standard script
        wp_register_script(
            'rr-front-end',
            RR_PLUGIN_URL . 'js/frontend.js',
            array('jquery', 'jquery-ui-datepicker', 'rr-datepicker-localization', 'rr-momentjs'),
            RANGE_RESERVER_VERSION,
            true
        );

        // bootstrap script
        wp_register_script(
            'rr-bootstrap',
            RR_PLUGIN_URL . 'components/bootstrap/js/bootstrap.js',
            array(),
            RANGE_RESERVER_VERSION,
            true
        );

        // frontend standard script
        wp_register_script(
            'rr-front-bootstrap',
            RR_PLUGIN_URL . 'js/frontend-bootstrap.js',
            array('jquery', 'jquery-ui-datepicker', 'rr-datepicker-localization', 'rr-momentjs'),
            RANGE_RESERVER_VERSION,
            true
        );

        // frontend standard script
        wp_register_script(
            'rr-google-recaptcha',
            'https://www.google.com/recaptcha/api.js',
            array(),
            RANGE_RESERVER_VERSION,
            true
        );

        // init for masked input field
        wp_add_inline_script('rr-front-end', "jQuery(document).on('rr-init:completed', function () { jQuery('.masked-field').inputmask(); });", 'after');
        wp_add_inline_script('rr-front-bootstrap', "jQuery(document).on('rr-init:completed', function () { jQuery('.masked-field').inputmask(); });", 'after');

        wp_register_style(
            'jquery-style',
            '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'
        );

        wp_register_style(
            'rr-bootstrap',
            RR_PLUGIN_URL . 'components/bootstrap/rr-css/bootstrap.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        wp_register_style(
            'rr-bootstrap-select',
            RR_PLUGIN_URL . 'components/bootstrap-select/css/bootstrap-select.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        wp_register_style(
            'rr-frontend-style',
            RR_PLUGIN_URL . 'css/rrfront.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        wp_register_style(
            'rr-frontend-bootstrap',
            RR_PLUGIN_URL . 'css/rrfront-bootstrap.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // admin style
        wp_register_style(
            'rr-admin-awesome-css',
            RR_PLUGIN_URL . 'css/font-awesome.css',
            array(),
            RANGE_RESERVER_VERSION
        );

        // custom fonts
        wp_register_style(
            'rr-admin-fonts-css',
            RR_PLUGIN_URL . 'css/fonts.css',
            array(),
            RANGE_RESERVER_VERSION
        );
    }

    /**
     * SHORTCODE
     *
     * Standard widget
     */
    public function standard_app($atts)
    {
        $code_params = shortcode_atts(array(
            'scroll_off'           => false,
            'save_form_content'    => true,
            'start_of_week'        => get_option('start_of_week', 0),
            'default_date'         => date('Y-m-d'),
            'min_date'             => null,
            'max_date'             => null,
            'show_remaining_slots' => '0',
            'show_week'            => '0',
        ), $atts);

        $settings = $this->options->get_options();

        // unset secret
        unset($settings['captcha.secret-key']);

        $settings['check'] = wp_create_nonce('rr-bootstrap-form');

        $settings['scroll_off']           = $code_params['scroll_off'];
        $settings['start_of_week']        = $code_params['start_of_week'];
        $settings['default_date']         = $code_params['default_date'];
        $settings['min_date']             = $code_params['min_date'];
        $settings['max_date']             = $code_params['max_date'];
        $settings['show_remaining_slots'] = $code_params['show_remaining_slots'];
        $settings['save_form_content']    = $code_params['save_form_content'];
        $settings['show_week']            = $code_params['show_week'];

        $settings['trans.please-select-new-date'] = __('Please select another day', 'range-reserver');
        $settings['trans.date-time'] = __('Date & time', 'range-reserver');
        $settings['trans.price'] = __('Price', 'range-reserver');

        // datetime format
        $settings['time_format'] = $this->datetime->convert_to_moment_format(get_option('time_format', 'H:i'));
        $settings['date_format'] = $this->datetime->convert_to_moment_format(get_option('date_format', 'F j, Y'));
        $settings['default_datetime_format'] = $this->datetime->convert_to_moment_format($this->datetime->default_format());

        $settings['trans.nonce-expired'] = __('Form validation code expired. Please refresh page in order to continue.', 'range-reserver');
        $settings['trans.internal-error'] = __('Internal error. Please try again later.', 'range-reserver');
        $settings['trans.ajax-call-not-available'] = __('Unable to make ajax request. Please try again later.', 'range-reserver');

        $customCss = $settings['custom.css'];
        $customCss = strip_tags($customCss);
        $customCss = str_replace(array('<?php', '?>', "\t"), array('', '', ''), $customCss);

        $meta = $this->models->get_all_rows("rr_meta_fields", array(), array('position' => 'ASC'));

        $add_maks_js = false;
        foreach ($meta as $row) {
            // we need to add masked js
            if ($row->type === 'MASKED') {
                $add_maks_js = true;
            }
        }
        if ($add_maks_js) {
            wp_enqueue_script('rr-masked');
        }

        wp_enqueue_script('underscore');
        wp_enqueue_script('rr-validator');
        wp_enqueue_script('rr-front-end');

        if (empty($settings['css.off'])) {
            wp_enqueue_style('jquery-style');
            wp_enqueue_style('rr-frontend-style');
            wp_enqueue_style('rr-admin-awesome-css');
        }

        if (!empty($settings['captcha.site-key'])) {
            wp_enqueue_script('rr-google-recaptcha');
        }

        $custom_form = $this->generate_custom_fields($meta);

        // add custom CSS

        ob_start();

        $this->output_inline_rr_settings($settings, $customCss);

        // GET TEMPLATE
        require $this->utils->get_template_path('booking.overview.tpl.php');

        ?>
        <script type="text/javascript">
            var rr_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        </script>
        <div class="rr-standard">
            <form>
                <div class="step">
                    <div class="block"></div>
                    <label class="rr-label"><?php _e($this->options->get_option_value("trans.location"), 'range-reserver') ?></label><select
                        name="location" data-c="location"
                        class="filter"><?php $this->get_options("locations") ?></select>
                </div>
                <div class="step">
                    <div class="block"></div>
                    <label class="rr-label"><?php _e($this->options->get_option_value("trans.bay"), 'range-reserver') ?></label><select
                        name="bay" data-c="bay" class="filter"
                        data-currency="<?php echo $this->options->get_option_value("trans.currency") ?>"><?php $this->get_options("bays") ?></select>
                </div>
                <div class="step">
                    <div class="block"></div>
                    <label class="rr-label"><?php _e($this->options->get_option_value("trans.lane"), 'range-reserver') ?></label><select
                        name="lane" data-c="lane" class="filter"><?php $this->get_options("lane") ?></select>
                </div>
                <div class="step calendar" class="filter">
                    <div class="block"></div>
                    <div class="date"></div>
                </div>
                <div class="step" class="filter">
                    <div class="block"></div>
                    <div class="time"></div>
                </div>
                <div class="step final">
                    <div class="block"></div>
                    <p class="section"><?php _e('Personal information', 'range-reserver'); ?></p>
                    <small><?php _e('Fields with * are required', 'range-reserver'); ?></small>
                    <br>
                    <?php echo $custom_form; ?>
                    <br>
                    <p class="section"><?php _e('Booking overview', 'range-reserver'); ?></p>
                    <div id="booking-overview"></div>
                    <?php if (!empty($settings['show.iagree'])) : ?>
                        <p>
                            <label
                                style="font-size: 65%; width: 80%;" class="i-agree"><?php _e('I agree with terms and conditions', 'range-reserver'); ?>
                                * : </label><input style="width: 15%;" type="checkbox" name="iagree"
                                                   data-rule-required="true"
                                                   data-msg-required="<?php _e('You must agree with terms and conditions', 'range-reserver'); ?>">
                        </p>
                        <br>
                    <?php endif; ?>
                    <?php if (!empty($settings['gdpr.on'])) : ?>
                        <p>
                            <label
                                    style="font-size: 65%; width: 80%;" class="gdpr"><?php echo $settings['gdpr.label'];?>
                                * : </label><input style="width: 15%;" type="checkbox" name="iagree"
                                                   data-rule-required="true"
                                                   data-msg-required="<?php echo $settings['gdpr.message'];?>">
                        </p>
                        <br>
                    <?php endif; ?>

                    <?php if (!empty($settings['captcha.site-key'])) : ?>
                        <div style="width: 100%" class="g-recaptcha" data-sitekey="<?php echo $settings['captcha.site-key'];?>"></div><br>
                    <?php endif; ?>

                    <div style="display: inline-flex;">
                        <?php echo apply_filters('rr_checkout_button', '<button class="rr-btn rr-submit">' . __('Submit', 'range-reserver') . '</button>'); ?>
                        <button class="rr-btn rr-cancel"><?php _e('Cancel', 'range-reserver'); ?></button>
                    </div>
                </div>
            </form>
            <div id="rr-loader"></div>
        </div>
        <?php

        apply_filters('rr_checkout_script', '');

        $content = ob_get_clean();
        // compress output
        if ($this->options->get_option_value('shortcode.compress', '1') === '1') {
            $content = preg_replace('/\s+/', ' ', $content);
        }

        return $content;
    }

    /**
     * Generate custom fields inside standard form
     *
     * @param $meta
     * @return string
     */
    public function generate_custom_fields($meta)
    {
        $html = '';

        // TODO add phone field

        foreach ($meta as $item) {

            if (empty($item->visible)) {
                continue;
            }

            if ($item->visible === "2") {
                $html .= '<input class="custom-field" type="hidden" name="' . $item->slug . '" value="" />';
                continue;
            }

            $r = !empty($item->required);

            $star = ($r) ? ' * ' : ' ';

            $html .= '<p>';
            $html .= '<label>' . __($item->label, 'range-reserver') . $star . ': </label>';

            if ($item->type == 'INPUT') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'range-reserver') . '"' : '';
                $email = ($item->validation == 'email') ? 'data-msg-email="' . __('Please enter a valid email address', 'range-reserver') . '" data-rule-email="true"' : '';

                $html .= '<input class="custom-field" type="text" name="' . $item->slug . '" ' . $msg . ' ' . $email . ' />';
            } else if ($item->type == 'MASKED') {
                $html .= '<input class="custom-field masked-field" type="text" name="' . $item->slug . '" data-inputmask="\'mask\':\'' . $item->default_value . '\'" />';
            } else if ($item->type == 'EMAIL') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'range-reserver') . '"' : '';
                $email = 'data-msg-email="' . __('Please enter a valid email address', 'range-reserver') . '" data-rule-email="true"';

                $html .= '<input class="custom-field" type="text" name="' . $item->slug . '" ' . $msg . ' ' . $email . ' />';
            } else if ($item->type == 'SELECT') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'range-reserver') . '"' : '';

                $html .= '<select class="form-control custom-field" name="' . $item->slug . '" ' . $msg . '>';
                $options = explode(',', $item->mixed);

                foreach ($options as $o) {
                    if ($o == '-') {
                        $html .= '<option value="">-</option>';
                    } else {
                        $html .= '<option value="' . $o . '" >' . $o . '</option>';
                    }
                }

                $html .= '</select>';

            } else if ($item->type == 'TEXTAREA') {
                $msg = ($r) ? 'data-rule-required="true" data-msg-required="' . __('This field is required.', 'range-reserver') . '"' : '';
                $html .= '<textarea class="form-control custom-field" rows="3" style="height: auto;" name="' . $item->slug . '" ' . $msg . '></textarea>';
            }

            $html .= '</p>';
        }

        return $html;
    }

    private function output_inline_rr_settings($settings, $customCss)
    {
        $clean_settings = RRTableColumns::clear_settings_data_frontend($settings);
        $data_settings = json_encode($clean_settings);
        $data_closure = $this->options->get_option_value('closures', '[]');

        // make sure it is just array structure
        if (!is_array(json_decode($data_closure))) {
            $data_closure = '[]';
        }

        echo "<script>var rr_settings = {$data_settings};</script>";
        echo "<script>var rr_closures = {$data_closure};</script>";
        echo "<style>{$customCss}</style>";
    }

    /**
     * SHORTCODE
     *
     * Bootstrap
     * @param array $atts
     * @return string
     */
    public function rr_bootstrap($atts)
    {

        $code_params = shortcode_atts(array(
            'location'             => null,
            'bay'              => null,
            'lane'               => null,
            'width'                => '400px',
            'scroll_off'           => false,
            'save_form_content'    => true,
            'layout_cols'          => '1',
            'start_of_week'        => get_option('start_of_week', 0),
            'rtl'                  => '0',
            'default_date'         => date('Y-m-d'),
            'min_date'             => null,
            'max_date'             => null,
            'show_remaining_slots' => '0',
            'show_week'            => '0',
            'cal_auto_select'      => '1',
            'block_days'           => null,
            'block_days_tooltip'   => '',
            'select_placeholder'   => '-'
        ), $atts);

        // check params
        apply_filters('rr_bootstrap_shortcode_params', $atts);

        // used inside template rr_bootstrap.tpl.php
        $location_id = $code_params['location'];
        $bay_id  = $code_params['bay'];
        $lane_id   = $code_params['lane'];

        $settings = $this->options->get_options();

        // unset secret
        unset($settings['captcha.secret-key']);

        $settings['check'] = wp_create_nonce('rr-bootstrap-form');

        $settings['width']                 = $code_params['width'];
        $settings['scroll_off']            = $code_params['scroll_off'];
        $settings['layout_cols']           = $code_params['layout_cols'];
        $settings['start_of_week']         = $code_params['start_of_week'];
        $settings['rtl']                   = $code_params['rtl'];
        $settings['default_date']          = $code_params['default_date'];
        $settings['min_date']              = $code_params['min_date'];
        $settings['max_date']              = $code_params['max_date'];
        $settings['show_remaining_slots']  = $code_params['show_remaining_slots'];
        $settings['show_week']             = $code_params['show_week'];
        $settings['save_form_content']     = $code_params['save_form_content'];
        $settings['cal_auto_select']       = $code_params['cal_auto_select'];
        $settings['block_days']            = $code_params['block_days'] !== null ? explode(',', $code_params['block_days']) : null;
        $settings['block_days_tooltip']    = $code_params['block_days_tooltip'];

            // LOCALIZATION
        $settings['trans.please-select-new-date'] = __('Please select another day','range-reserver');
        $settings['trans.personal-informations'] = __('Personal information','range-reserver');
        $settings['trans.field-required'] = __('This field is required.','range-reserver');
        $settings['trans.error-email'] = __('Please enter a valid email address','range-reserver');
        $settings['trans.error-name'] = __('Please enter at least 3 characters.','range-reserver');
        $settings['trans.error-phone'] = __('Please enter at least 3 digits.','range-reserver');
        $settings['trans.fields'] = __('Fields with * are required','range-reserver');
        $settings['trans.email'] = __('Email','range-reserver');
        $settings['trans.name'] = __('Name','range-reserver');
        $settings['trans.phone'] = __('Phone','range-reserver');
        $settings['trans.comment'] = __('Comment','range-reserver');
        $settings['trans.overview-message'] = __('Please check your appointment details below and confirm:','range-reserver');
        $settings['trans.booking-overview'] = __('Booking overview','range-reserver');
        $settings['trans.date-time'] = __('Date & time','range-reserver');
        $settings['trans.submit'] = __('Submit','range-reserver');
        $settings['trans.cancel'] = __('Cancel','range-reserver');
        $settings['trans.price'] = __('Price','range-reserver');
        $settings['trans.iagree'] = __('I agree with terms and conditions','range-reserver');
        $settings['trans.field-iagree'] = __('You must agree with terms and conditions','range-reserver');
        $settings['trans.slot-not-selectable'] = __('You can\'t select this time slot!\'','range-reserver');

        $settings['trans.nonce-expired'] = __('Form validation code expired. Please refresh page in order to continue.','range-reserver');
        $settings['trans.internal-error'] = __('Internal error. Please try again later.','range-reserver');
        $settings['trans.ajax-call-not-available'] = __('Unable to make ajax request. Please try again later.','range-reserver');

        // datetime format
        $settings['time_format'] = $this->datetime->convert_to_moment_format(get_option('time_format', 'H:i'));
        $settings['date_format'] = $this->datetime->convert_to_moment_format(get_option('date_format', 'F j, Y'));
        $settings['default_datetime_format'] = $this->datetime->convert_to_moment_format($this->datetime->default_format());

        // CUSTOM CSS
        $customCss = $settings['custom.css'];
        $customCss = strip_tags($customCss);
        $customCss = str_replace(array('<?php', '?>', "\t"), array('', '', ''), $customCss);

        unset($settings['custom.css']);

        if ($settings['form.label.above'] === '1') {
            $settings['form_class'] = 'rr-form-v2';
        }

        $rows = $this->models->get_all_rows("rr_meta_fields", array(), array('position' => 'ASC'));
        $add_maks_js = false;

        foreach ($rows as $key => $row) {
            $rows[$key]->label = __($row->label,'range-reserver');

            // we need to add masked js
            if ($row->type === 'MASKED') {
                $add_maks_js = true;
            }
        }

        if ($add_maks_js) {
            wp_enqueue_script('rr-masked');
        }

        $rows = apply_filters( 'rr_form_rows', $rows);
        $settings['MetaFields'] = $rows;

        wp_enqueue_script('underscore');
        wp_enqueue_script('rr-validator');
        wp_enqueue_script('rr-bootstrap');
        wp_enqueue_script('rr-front-bootstrap');

        if (empty($settings['css.off'])) {
            wp_enqueue_style('rr-bootstrap');
            wp_enqueue_style('rr-admin-awesome-css');
            wp_enqueue_style('rr-frontend-bootstrap');
        }

        if (!empty($settings['captcha.site-key'])) {
            wp_enqueue_script('rr-google-recaptcha');
        }

        if (!empty($settings['captcha3.site-key'])) {
            wp_enqueue_script('rr-google-recaptcha-v3', "https://www.google.com/recaptcha/api.js?render={$settings['captcha3.site-key']}");
        }

        ob_start();
        $this->output_inline_rr_settings($settings, $customCss);

        // FORM TEMPLATE
        if ($settings['rtl'] == '1') {
            require RR_SRC_DIR . 'templates/rr_bootstrap_rtl.tpl.php';
        } else {
            require RR_SRC_DIR . 'templates/rr_bootstrap.tpl.php';
        }

        // OVERVIEW TEMPLATE
        require $this->utils->get_template_path('booking.overview.tpl.php');

        ?>
        <div class="rr-bootstrap bootstrap"></div><?php

        // load scripts if there are some
        apply_filters('rr_checkout_script', '');

        $content = ob_get_clean();
        // compress output
        if ($this->options->get_option_value('shortcode.compress', '1') === '1') {
            $content = preg_replace('/\s+/', ' ', $content);
        }

        return $content;
    }

    /**
     * Get options for select fields
     *
     * @param $type
     * @param null $location_id
     * @param null $bay_id
     * @param null $lane_id
     */
    private function get_options($type, $location_id = null, $bay_id = null, $lane_id = null, $placeholder = '-')
    {
        if (!$this->generate_next_option) {
            return;
        }

        $hide_price = $this->options->get_option_value('price.hide', '0');
        $hide_price_bay = $this->options->get_option_value('price.hide.bay', '0');

        $before = $this->options->get_option_value('currency.before', '0');
        $currency = $this->options->get_option_value('trans.currency', '$');

//        $rows = $this->models->get_all_rows("rr_$type");
        $rows = $this->models->get_frontend_select_options("rr_$type", $location_id, $bay_id, $lane_id);

        // If there is only one result, like one lane in whole system or one location etc
        if (count($rows) == 1) {
            $price = !empty($rows[0]->price) ? " data-price='{$rows[0]->price}'" : '';
            if ($type === 'bays') {
                echo "<option data-duration='{$rows[0]->duration}' data-slot_step='{$rows[0]->slot_step}' value='{$rows[0]->id}' selected='selected'$price>{$rows[0]->name}</option>";
            } else {
                echo "<option value='{$rows[0]->id}' selected='selected'$price>{$rows[0]->name}</option>";
            }
            return;
        }

        // if there is only one preselected option, like personal calendar for one lane
        if ($type === 'bays' && $bay_id !== null) {
            foreach ($rows as $row) {
                if ($row->id == $bay_id) {
                    $price = !empty($row->price) ? " data-price='{$row->price}'" : '';
                    echo "<option value='{$row->id}' data-duration='{$row->duration}' data-slot_step='{$row->slot_step}' selected='selected'$price>{$row->name}</option>";
                    return;
                }
            }
        }

        if ($type === 'locations' && $location_id !== null) {
            foreach ($rows as $row) {
                if ($row->id == $location_id) {
                    $price = !empty($row->price) ? " data-price='{$row->price}'" : '';
                    echo "<option value='{$row->id}' selected='selected'$price>{$row->name}</option>";
                    return;
                }
            }
        }

        if ($type === 'lane' && $lane_id !== null) {
            foreach ($rows as $row) {
                if ($row->id == $lane_id) {
                    $price = !empty($row->price) ? " data-price='{$row->price}'" : '';
                    echo "<option value='{$row->id}' selected='selected'$price>{$row->name}</option>";
                    return;
                }
            }
        }

        // option
/*        $default_value = esc_html($placeholder);
        echo "<option value='' selected='selected'>{$default_value}</option>";*/

        foreach ($rows as $row) {
            $price = !empty($row->price) ? " data-price='{$row->price}'" : '';

            // case when we are hiding price
            if ($hide_price == '1') {

                // for all other types
                if ($type != 'bays') {
                    echo "<option value='{$row->id}'>{$row->name}</option>";
                } else if ($type == 'bays') {
                    // for bay
                    echo "<option data-duration='{$row->duration}' data-slot_step='{$row->slot_step}' value='{$row->id}'>{$row->name}</option>";
                }

            } else if ($type == 'bays') {
                $name = $row->name;
                $name_price = ($before == '1') ? $name . ' ' . $currency . $row->price : $name . ' ' . $row->price . $currency;

                // maybe we want to hide price in bay option
                if ($hide_price_bay) {
                    $name_price = $name;
                }

                echo "<option data-duration='{$row->duration}' data-slot_step='{$row->slot_step}' value='{$row->id}'$price>{$name_price}</option>";
            } else {
                echo "<option value='{$row->id}'>{$row->name}</option>";
            }
        }
    }
}
