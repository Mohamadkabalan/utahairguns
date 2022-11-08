<?php

/**
 * Class RRC_Google_Event_Helper
 */
class RRC_Google_Event_Helper
{
    /**
     * Get Google Calendar proxy object
     *
     * @return RRCGoogle_Service_Calendar
     */
    public function get_google_client()
    {
        $client = new RRCGoogle_Client();

        $client->setClientId(get_option('RRC_' . RRCGoogleFields::CLIENT_ID));
        $client->setClientSecret(get_option('RRC_' . RRCGoogleFields::CLIENT_SECRET));

        $client->addScope(RRCGoogle_Service_Calendar::CALENDAR);

        $token = get_option('RRC_DEFAULT_GOOGLE_TOKEN', null);

        if ($token != null) {
            $token_obj = json_decode($token, true);

            if (array_key_exists('refresh_token', $token_obj)) {
                $client->setAccessType('offline');
            }
        }

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {

            // $client->authenticate();
            $NewAccessToken = json_decode($client->getAccessToken());
            $client->refreshToken($NewAccessToken->refresh_token);

            // update new token
            update_option('RRC_DEFAULT_GOOGLE_TOKEN', $client->getAccessToken());
        }

        $service = new RRCGoogle_Service_Calendar($client);

        return $service;
    }

    /**
     * Get list of events
     *
     * @param $calendar
     * @param int $nextDays
     * @return array|mixed
     */
    public function get_events($calendar, $nextDays = 0)
    {
        $service = $this->get_google_client();

        $ends = new DateTime();
        $ends->modify("+{$nextDays} days");

        $timeMin = date('Y-m-d') ."T00:00:00Z";

        $calendarId = $calendar;

        $optParams = array(
            'orderBy'      => 'startTime',
            'singleEvents' => TRUE,
            'timeMin'      => $timeMin
        );

        if ($nextDays != '0') {
            $optParams['timeMax'] = $ends->format('Y-m-d') . 'T23:59:59Z';
        }

        $events = array();

        $safe = 0;

        $results = $service->events->listEvents($calendarId, $optParams);
        $events = array_merge($events, $results->getItems());

        do {

            $pageToken = $results->getNextPageToken();

            if ($pageToken) {
                $optParams['pageToken'] = $pageToken;

                $results = $service->events->listEvents($calendarId, $optParams);
                $events = array_merge($events, $results->getItems());
            } else {
                break;
            }

        } while(++$safe < 1000);

        return $events;
    }

    /**
     * Get internal Application id from google event id
     *
     * @param $google_event_id
     * @return null|string
     */
    public function get_appintment_id($google_event_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'rr_connect_links';

        $query = $wpdb->prepare("SELECT `app_id` FROM {$table_name} WHERE `google_event_id` = %s", $google_event_id);

        $app_id = $wpdb->get_var($query);

        return $app_id;
    }


    /**
     * @param $template
     * @param $data
     * @return mixed
     */
    public function format_description($template, $data) {
        $tags = array_keys($data);
        $values = array_values($data);

        foreach ($tags as $key => $tag) {
            $tags[$key] = "#{$tag}#";
        }

        // join together #key1# => value1, #key2# => value2,...
        $params = array_combine($tags, $values);

        // add cancel/confirm link actions
        $params = apply_filters('rr_format_notification_params', $params, $data);

        return str_replace(array_keys($params), array_values($params), $template);
    }

    /**
     * @param $calendars
     * @param $location
     * @param $service
     * @param $worker
     *
     * @return string
     */
    public function get_calendar_id($calendars, $location, $service, $worker)
    {
        if (empty($calendars) || !is_array($calendars)) {
            return 'primary';
        }

        $trys = array(3,2,1);

        // select calendar
        foreach ($trys as $try) {
            foreach ($calendars as $calendar) {
                $counter = 0;
                $any = 0;

                if ($location == $calendar['location']['id']) {
                    $counter++;
                }

                if ($service == $calendar['service']['id']) {
                    $counter++;
                }

                if ($worker == $calendar['worker']['id']) {
                    $counter++;
                }

                if ('*' == $calendar['location']['id']) {
                    $any++;
                }

                if ('*' == $calendar['service']['id']) {
                    $any++;
                }

                if ('*' == $calendar['worker']['id']) {
                    $any++;
                }

                if ($counter == $try && ($counter + $any) === 3) {
                    return $calendar['calendar']['id'];
                }
            }
        }

        foreach ($calendars as $calendar) {
            if ($calendar['location']['id'] == '*' && $calendar['service']['id'] = '*' && $calendar['worker']['id'] == '*') {
                return $calendar['calendar']['id'];
            }
        }

        return null;
    }

    /**
     * @param $calendars
     * @return array
     */
    public function get_calendar_ids_for_sync($calendars)
    {
        $result = array();
        if (empty($calendars) || !is_array($calendars)) {
            return array(
                'primary' => array(
                    'location' => '*',
                    'service'  => '*',
                    'worker'   => '*'
                )
            );
        }

        foreach ($calendars as $calendar) {
            $id = $calendar['calendar']['id'];
            $result[$id] = array(
                'location' => $calendar['location']['id'],
                'service'  => $calendar['service']['id'],
                'worker'   => $calendar['worker']['id']
            );
        }

        return $result;
    }
}
