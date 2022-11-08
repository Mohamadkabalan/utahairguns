<?php

interface RRISendMessage {
    public function send_message($app_id, $to_phone, $data, $reminder, $follow);
}