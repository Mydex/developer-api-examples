<?php

// This script queries the ALISS MRD endpoint, specifically
// searching based on 'category' => 'physical-activity'

define("OAUTH_TOKEN_ENDPOINT", "https://op.mydexid.org/oauth2/token");
define("OAUTH_MRD_CLIENT_ID", "abcd1234-abcd-1234-abcd-123456abcdef");
define("OAUTH_MRD_CLIENT_SECRET", "CHANGEME");
define("OAUTH_MRD_SCOPES", "aliss");
define("OAUTH_MRD_GRANT_TYPE", "client_credentials");
define("MRD_API_ENDPOINT", "https://api-mrd.mydex.org/aliss/get-services/search");

function request_token() {
    // Prepare our POST data's token params
    $post_data = [
        'grant_type' => OAUTH_MRD_GRANT_TYPE,
        'scope' => OAUTH_MRD_SCOPES,
    ];
    // Convert token params to string format
    $post_params = http_build_query($post_data, '', '&', PHP_QUERY_RFC1738);

    // Content type needs to be form encoded
    $content_type = 'application/x-www-form-urlencoded';
    $headers[] = "Content-Type: {$content_type}";

    // Client ID and Secret are sent as the Authorization (basic) header
    $headers[] = 'Authorization: Basic ' . base64_encode(urlencode(OAUTH_MRD_CLIENT_ID) . ':' . urlencode(OAUTH_MRD_CLIENT_SECRET));

    // Request a token from the OAuth endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, OAUTH_TOKEN_ENDPOINT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);

    $auth_data = curl_exec($ch);
    $auth_data = json_decode($auth_data);
    $error = curl_error($ch);
    curl_close($ch);

    // Did we get a token back?
    if (isset($auth_data->access_token)) {
        return $auth_data->access_token;
    } else {
        echo print_r($auth_data, TRUE);
        return false;
    }
}

function request_aliss($token) {
    $params = array('categories' => 'physical-activity');
    $url = MRD_API_ENDPOINT . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'X-Mrd-Scopes: ' . OAUTH_MRD_SCOPES,
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $mrd_data = curl_exec($ch);
    $mrd_data = json_decode($mrd_data);
    $error = curl_error($ch);
    curl_close($ch);

    if (!empty($mrd_data)) {
        echo print_r($mrd_data, TRUE);
    } else {
        echo $error;
    }
}

echo "Requesting a token\n";
$token = request_token();

if ($token) {
    request_aliss($token);
}
