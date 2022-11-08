<?php

class RR_Mail_Sender implements RRISendMessage {

    private $wpdb;

    protected $subject = null;

    private $send_from;

    /**
     * @var RRDBModels
     */
    private $db_model;

    public function __construct($wpdb, $options, $db_model)
    {
        $this->wpdb = $wpdb;
        $this->db_model = $db_model;

        $raw  = get_option('RRC_' . RRC_Twilio_Fields::SETTINGS, '{"omnicom":{},"mail":{}}');
        $settings = json_decode(stripslashes($raw), true);

        if (!empty($settings['mail']['subject'])) {
            $this->subject = $settings['mail']['subject'];
        }

        $this->send_from = $options->get_option_value('send.from.email', '');
    }

    public function send_message($app_id, $to_phone, $data, $reminder, $follow)
    {
        if (empty($this->subject)) {
            return false;
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (!empty($this->send_from)) {
            $headers[] = 'From: ' . $this->send_from;
        }

        $emails = $this->db_model->get_email_values_for_app_id($app_id);

        wp_mail($emails, $this->subject, $data['body'], $headers);

        return true;
    }
}