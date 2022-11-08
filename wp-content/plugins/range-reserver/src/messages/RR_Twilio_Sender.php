<?php

use Twilio\Rest\Client;

class RR_Twilio_Sender implements RRISendMessage {
    /**
     * @var false|mixed|void
     */
    private $account_id;

    /**
     * @var false|mixed|void
     */

    private $token;

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->account_id  = get_option('RRC_' . RRC_Twilio_Fields::ACCOUNT_ID);
        $this->token       = get_option('RRC_' . RRC_Twilio_Fields::TOKEN);

        try {
            if (!empty($this->account_id) && !empty($this->token)) {
                $this->client = new Client($this->account_id, $this->token);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function send_message($app_id, $to_phone, $data, $reminder, $follow)
    {
        if (empty($this->client)) {
            return false;
        }

        $response = $this->client->messages->create($to_phone, $data);

        $this->process_message_error($response);

        return true;
    }

    /**
     * @param $message
     */
    protected function process_message_error($message)
    {
        $error_message = trim($message->errorMessage);
        $error_code = trim($message->errorCode);

        if (empty($error_message) || empty($error_code)) {
            return;
        }

        error_log("TWILIO: {$error_code} - {$error_message}");
    }
}