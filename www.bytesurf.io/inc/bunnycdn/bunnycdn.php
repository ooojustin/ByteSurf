<?php

    /*

        BunnyCDN API Wrapper
        API Documentation: https://bunnycdn.docs.apiary.io/
        Get your API key: https://bunnycdn.com/dashboard/account

        Written by Justin Garofolo © 2019
        https://github.com/ooojustin

    */

    date_default_timezone_set('UTC');

    // === EXAMPLE CODE ===
    /*
    define('BUNNYCDN_API_KEY', 'put your api key here');
    $bcdn = new BunnyCDN(BUNNYCDN_API_KEY, 'put a user agent here');
    echo $bcdn->get_balance();
    */
    // === END EXAMPLE CODE ===

    class BunnyCDN {

        const API_URL = 'https://bunnycdn.com/api/';

        private $key;
        private $user_agent;

        function __construct($key, $user_agent) {
            $this->key = $key;
            $this->user_agent = $user_agent;
        }

        // Gets user billing summary.
        function get_billing_summary() {
            return $this->send_api_request('GET', 'billing');
        }

        // Returns the balance spent this month.
        function get_month_spent() {
            return $this->get_billing_summary()['ThisMonthCharges'];
        }

        // Returns users current account balance.
        function get_balance() {
            return $this->get_billing_summary()['Balance'];
        }

        // Returns deposits the user has made into their balance, with a useable 'DateTime' object.
        function get_deposits() {
            return $this->get_billing_summary()['BillingRecords'];
        }

        // Returns the total amount of money deposited on a given day.
        function get_deposited_on_day($date = NULL) {
            // If call doesn't provide date, assume it's today.
            if (is_null($date))
                $date = new DateTime();
            $date->setTime(0, 0, 0);
            $deposited = 0;
            $deposits = $this->get_deposits();
            foreach ($deposits as $deposit) {
                $deposit['DateTime'] = new DateTime($deposit['Timestamp']);
                $deposit['DateTime']->setTime(0, 0, 0);
                $days = intval($date->diff($deposit['DateTime'])->format("%R%a"));
                if ($days == 0)
                    $deposited += $deposit['Amount'];
                else if ($days < 0)
                    break;
            }
            return $deposited;
        }

        // Applys a promo code to the users account.
        function apply_promo_code($code) {
            $path = 'billing/applycode?couponCode=' . $code;
            $response = $this->send_api_request('GET', $path, NULL, false);
            return strlen($response) == 0;
        }

        // Returns an estimate of how much longer the current balance will last, in days.
        // Based on current balance/amount spent thus far in the current month.
        function get_balance_remainder_estimate() {
            $billing_data = $this->get_billing_summary();
            $day = intval(date('d'));
            return $billing_data['Balance'] / ($billing_data['ThisMonthCharges'] / $day);
        }

        // Gets a specific storage zone via the name.
        function get_storage_zone($name) {
            $list = $this->get_storage_zone_list();
            foreach ($list as $zone)
                if ($zone['Name'] == $name)
                    return $zone;
            return NULL;
        }

        // Gets a list of all storage zones.
        function get_storage_zone_list() {
            return $this->send_api_request('GET', 'storagezone');
        }

        // Blocks (or unblock) specified IP address from a pull zone.
        function block_ip_from_pull_zone($name, $ip, $blocked = true) {
            $id = $this->get_pull_zone_id($name);
            $data = array(
                'PullZoneId' => $id,
                'BlockedIp' => $ip
            );
            $path = 'pullzone/' . ($blocked ? 'addBlockedIp' : 'removeBlockedIp');
            $response = $this->send_api_request('POST', $path, $data, false);
            return strlen($response) == 0; // empty response = successful
        }

        // Gets the 'id' of a specified pull zone via the name.
        function get_pull_zone_id($name) {
            $zone = $this->get_pull_zone($name);
            return $zone['Id'];
        }

        // Gets a specific pull zone via the name.
        function get_pull_zone($name) {
            $list = $this->get_pull_zone_list();
            foreach ($list as $zone)
                if ($zone['Name'] == $name)
                    return $zone;
            return NULL;
        }

        // Gets a list of all pull zones.
        function get_pull_zone_list() {
            return $this->send_api_request('GET', 'pullzone');
        }

        // Purges a given URL from server cache.
        function purge_cache($url) {
            $query = 'purge?url=' . $url;
            $response = $this->send_api_request('POST', $query, NULL, false);
            return strlen($response) == 0;
        }

        // Gets a plethora of statistics, with options for dates/pull zone/server zone.
        // Dates must be formatted as yyyy-MM-dd.
        function get_statistics($dateFrom = NULL, $dateTo = NULL, $pullZone = NULL, $serverZoneId = NULL) {
            $data = array(
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'pullZone' => $pullZone,
                'serverZoneId' => $serverZoneId
            );
            $data = array_filter($data);
            $path = sprintf('%s?%s', 'statistics', http_build_query($data));
            return $this->send_api_request('GET', $path);
        }

        // Sends a request to the API, returns response data in an array.
        private function send_api_request($method, $path, $data = NULL, $decode_json = true) {

            // determine full url, send web request
            $url = self::API_URL . $path;
            $response = $this->send_web_request($method, $url, $data);

            // return response data as an associate array
            if ($decode_json) {
                $data = json_decode($response, true);
                return $data;
            }

            // if decoding is disabled, return standard response
            return $response;

        }

        // Sends a web request to a given URL.
        // AccessKey and User-Agent are applied automatically.
        private function send_web_request($method, $url, $data = NULL) {

            // base http headers
            $opts = array('http' => array('method'  => $method, 'header' => ''));

            // if data is a provided array, set http content
            if (!is_null($data) && is_array($data)) {
                $data = http_build_query($data);
                $opts['http']['content'] = $data;
            }

            // manually set content-length if needed
            if ($method == 'POST' && !isset($opts['http']['content']))
                $opts['http']['header'] .= 'Content-Length: 0' . "\r\n";

            // add accesskey and user-agent to authorize request
            $opts['http']['header'] .= 'AccessKey: ' . $this->key . "\r\n";
            $opts['http']['header'] .= 'User-Agent: ' . $this->user_agent . "\r\n";
            $opts['http']['ignore_errors'] = true;

            // send request to server, get response
            $context  = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);

            // determine response code
            $code_raw = $http_response_header[0];
            $code = strstr($code_raw, ' ');
            $code = substr($code, 1);

            // handle an exception
            if ($response === FALSE)
                throw new Exception('Web request failed in BunnyCDN send_web_request function. Status code: ' + $code . ', Response: ' . $response);

            // return the response
            return $response;

        }

    }

?>
