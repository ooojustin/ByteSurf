<?php

    class SellyAPI {
        
        static $endpoint = 'https://selly.gg/api/v2';

        var $email;
        var $api_key;
        
        function __construct($email, $api_key) {
            $this->email = $email;
            $this->api_key = $api_key; 
        }

        function get_orders() {
            return $this->get('orders');
        }
        
        function create_payment($title, $gateway, $email, $value, $currency, $return_url, $webhook_url) {
            $params = array(
                'title' => $title, 
                'gateway' => $gateway, 
                'email' => $email, 
                'value' => $value, 
                'currency' => $currency, 
                'return_url' => $return_url, 
                'webhook_url' => $webhook_url, 
                'white_label' => true
            );
            if (isset($GLOBALS['ip'])) 
                $params['ip_address'] = $GLOBALS['ip'];
            return $this->post('pay', $params);
        }

        function get($cmd) {
            $url = SellyAPI::$endpoint.'/'.$cmd;
            $opts = array(
                'http'=>array(
                    'method'=>"GET",
                    'header'=>
                        "User-agent: " . $this->get_user_agent() . "\r\n" .
                        "Authorization: Basic " . $this->get_authorization() . "\r\n"
                )
            );
            $context = stream_context_create($opts);
            $result = @file_get_contents($url, false, $context);
            if ($result === false) {
                // there's an error
                // find out more info by copying this request into postman
                // var_dump($opts);
                // var_dump($params);
                return false;
            } else {
                $data = json_decode($result, true);
                return $data;
            }
        }

        function post($cmd, $params) {
            $url = SellyAPI::$endpoint.'/'.$cmd;
            $query = http_build_query($params);
            $opts = array(
                'http'=>array(
                    'method'=>"POST",
                    'header'=>
                        "User-agent: " . $this->get_user_agent() . "\r\n" .
                        "Authorization: Basic " . $this->get_authorization() . "\r\n" .
                        "Content-Type: application/x-www-form-urlencoded",
                    'content' => $query
                )
            );
            $context = stream_context_create($opts);
            $result = @file_get_contents($url, false, $context);
            if ($result === false) {
                // there's an error
                // find out more info by copying this request into postman
                // var_dump($opts);
                // var_dump($params);
                return false;
            } else {
                $data = json_decode($result, true);
                return $data;
            }
        }

        function get_user_agent() {
            return $this->email . ' - ' . $_SERVER['SERVER_NAME'];
        }

        function get_authorization() {
            $str = $this->email . ':' . $this->api_key;
            return base64_encode($str);
        }

    }

?>