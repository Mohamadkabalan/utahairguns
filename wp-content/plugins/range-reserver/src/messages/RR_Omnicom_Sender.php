<?php

class RR_Omnicom_Sender implements RRISendMessage {

    const URL = 'https://omnicom.gr/api/send_sms';

    protected $username = null;
    protected $api_password = null;
    protected $api_token = null;

    public function __construct()
    {
        $raw = get_option('RRC_' . RRC_Twilio_Fields::SETTINGS, '{"omnicom":{},"mail":{}}');

        $settings = json_decode(stripslashes($raw), true);

        if (!empty($settings['omnicom']['username'])) {
            $this->username = $settings['omnicom']['username'];
        }

        if (!empty($settings['omnicom']['api_password'])) {
            $this->api_password = $settings['omnicom']['api_password'];
        }

        if (!empty($settings['omnicom']['api_token'])) {
            $this->api_token = $settings['omnicom']['api_token'];
        }
    }


    public function send_message($app_id, $to_phone, $data, $reminder, $follow)
    {
        if (empty($this->username) || empty($this->api_password) || empty($this->api_token)) {
            return false;
        }

        $data_body = mb_strtoupper($data['body'], 'UTF-8');
        $data_body = $this->sanitize_greek($data_body);

        $body = array(
            'username'     => $this->username,
            'api_password' => $this->api_password,
            'api_token'    => $this->api_token,
            'from'         => $data['from'],
            'message'      => $data_body,
            'bulklist'     => $to_phone,
            'long'         => 'TRUE'
        );

        $response = wp_remote_get(self::URL, array(
            'body' => $body,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
        ));

        if (is_wp_error($response)) {
            error_log($response->get_error_message());
        }

        return true;
    }

    public function sanitize_greek($string) {
        $dict = array(
            'Α' => "A",
            'Ά' => "A",
            'α' => "A",
            'ά' => "A",
            'Β' => "B",
            'β' => "B",
            'γ' => "Γ",
            'δ' => "Δ",
            'Ε' => "E",
            'ε' => "E",
            'έ' => "E",
            'Έ' => "E",
            'Ζ' => "Z",
            'ζ' => "Z",
            'Η' => "H",
            'Ή' => "H",
            'η' => "H",
            'ή' => "H",
            'θ' => "Θ",
            'Ι' => "I",
            'ι' => "I",
            'ί' => "I",
            'Ί' => "I",
            'ϊ' => "I",
            'Ϊ' => "I",
            'Κ' => "K",
            'κ' => "K",
            'λ' => "Λ",
            'Μ' => "M",
            'μ' => "M",
            'Ν' => "N",
            'ν' => "N",
            'ξ' => "Ξ",
            'Ο' => "O",
            'Ό' => "O",
            'ό' => "O",
            'ο' => "O",
            'π' => "Π",
            'Ρ' => "P",
            'ρ' => "P",
            'Σ' => "Σ",
            'σ' => "Σ",
            'ς' => "Σ",
            'Τ' => "T",
            'τ' => "T",
            'Υ' => "Y",
            'Ύ' => "Y",
            'υ' => "Y",
            'ύ' => "Y",
            'ϋ' => "Y",
            'Ϋ' => "Y",
            'φ' => "Φ",
            'Χ' => "X",
            'χ' => "X",
            'ψ' => "Ψ",
            'Ψ' => "Ψ",
            'Ω' => "Ω",
            'ω' => "Ω",
            'Ώ' => "Ω",
            'ώ' => "Ω",
            '΄' => "'",
            '`' => "'"
        );

        return strtr($string, $dict);
    }
}