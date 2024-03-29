<?php
require 'vendor/autoload.php'; // If you're using Composer (recommended)
// comment out the above line if not using Composer
// require("./sendgrid-php.php"); 
// If not using Composer, uncomment the above line


$apiKey = getenv('SENDGRID_API_KEY');
$sg = new \SendGrid($apiKey);

////////////////////////////////////////////////////
// Create a domain whitelabel. #
// POST /whitelabel/domains #

$request_body = json_decode('{
  "automatic_security": false, 
  "custom_spf": true, 
  "default": true, 
  "domain": "example.com", 
  "ips": [
    "192.168.1.1", 
    "192.168.1.2"
  ], 
  "subdomain": "news", 
  "username": "john@example.com"
}');

try {
    $response = $sg->client->whitelabel()->domains()->post($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// List all domain whitelabels. #
// GET /whitelabel/domains #

$query_params = json_decode('{"username": "test_string", "domain": "test_string", "exclude_subusers": "true", "limit": 1, "offset": 1}');

try {
    $response = $sg->client->whitelabel()->domains()->get(null, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Get the default domain whitelabel. #
// GET /whitelabel/domains/default #

try {
    $response = $sg->client->whitelabel()->domains()->default()->get();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// List the domain whitelabel associated with the given user. #
// GET /whitelabel/domains/subuser #

try {
    $response = $sg->client->whitelabel()->domains()->subuser()->get();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Disassociate a domain whitelabel from a given user. #
// DELETE /whitelabel/domains/subuser #

try {
    $response = $sg->client->whitelabel()->domains()->subuser()->delete();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Update a domain whitelabel. #
// PATCH /whitelabel/domains/{domain_id} #

$request_body = json_decode('{
  "custom_spf": true, 
  "default": false
}');
$domain_id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($domain_id)->patch($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve a domain whitelabel. #
// GET /whitelabel/domains/{domain_id} #

$domain_id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($domain_id)->get();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Delete a domain whitelabel. #
// DELETE /whitelabel/domains/{domain_id} #

$domain_id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($domain_id)->delete();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Associate a domain whitelabel with a given user. #
// POST /whitelabel/domains/{domain_id}/subuser #

$request_body = json_decode('{
  "username": "jane@example.com"
}');
$domain_id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($domain_id)->subuser()->post($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Add an IP to a domain whitelabel. #
// POST /whitelabel/domains/{id}/ips #

$request_body = json_decode('{
  "ip": "192.168.0.1"
}');
$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($id)->ips()->post($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Remove an IP from a domain whitelabel. #
// DELETE /whitelabel/domains/{id}/ips/{ip} #

$id = "test_url_param";
$ip = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($id)->ips()->_($ip)->delete();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Validate a domain whitelabel. #
// POST /whitelabel/domains/{id}/validate #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->domains()->_($id)->validate()->post();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Create an IP whitelabel #
// POST /whitelabel/ips #

$request_body = json_decode('{
  "domain": "example.com", 
  "ip": "192.168.1.1", 
  "subdomain": "email"
}');

try {
    $response = $sg->client->whitelabel()->ips()->post($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve all IP whitelabels #
// GET /whitelabel/ips #

$query_params = json_decode('{"ip": "test_string", "limit": 1, "offset": 1}');

try {
    $response = $sg->client->whitelabel()->ips()->get(null, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve an IP whitelabel #
// GET /whitelabel/ips/{id} #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->ips()->_($id)->get();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Delete an IP whitelabel #
// DELETE /whitelabel/ips/{id} #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->ips()->_($id)->delete();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Validate an IP whitelabel #
// POST /whitelabel/ips/{id}/validate #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->ips()->_($id)->validate()->post();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Create a Link Whitelabel #
// POST /whitelabel/links #

$request_body = json_decode('{
  "default": true, 
  "domain": "example.com", 
  "subdomain": "mail"
}');
$query_params = json_decode('{"limit": 1, "offset": 1}');

try {
    $response = $sg->client->whitelabel()->links()->post($request_body, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve all link whitelabels #
// GET /whitelabel/links #

$query_params = json_decode('{"limit": 1}');

try {
    $response = $sg->client->whitelabel()->links()->get(null, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve a Default Link Whitelabel #
// GET /whitelabel/links/default #

$query_params = json_decode('{"domain": "test_string"}');

try {
    $response = $sg->client->whitelabel()->links()->default()->get(null, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve Associated Link Whitelabel #
// GET /whitelabel/links/subuser #

$query_params = json_decode('{"username": "test_string"}');

try {
    $response = $sg->client->whitelabel()->links()->subuser()->get(null, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Disassociate a Link Whitelabel #
// DELETE /whitelabel/links/subuser #

$query_params = json_decode('{"username": "test_string"}');

try {
    $response = $sg->client->whitelabel()->links()->subuser()->delete(null, $query_params);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Update a Link Whitelabel #
// PATCH /whitelabel/links/{id} #

$request_body = json_decode('{
  "default": true
}');
$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->links()->_($id)->patch($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Retrieve a Link Whitelabel #
// GET /whitelabel/links/{id} #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->links()->_($id)->get();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Delete a Link Whitelabel #
// DELETE /whitelabel/links/{id} #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->links()->_($id)->delete();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Validate a Link Whitelabel #
// POST /whitelabel/links/{id}/validate #

$id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->links()->_($id)->validate()->post();
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

////////////////////////////////////////////////////
// Associate a Link Whitelabel #
// POST /whitelabel/links/{link_id}/subuser #

$request_body = json_decode('{
  "username": "jane@example.com"
}');
$link_id = "test_url_param";

try {
    $response = $sg->client->whitelabel()->links()->_($link_id)->subuser()->post($request_body);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
