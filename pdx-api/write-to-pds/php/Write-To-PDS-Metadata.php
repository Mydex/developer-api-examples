<?php

define("MYDEX_PDS_PATH", "https://sbx-api.mydex.org");
define("MEMBER_UID", "1234");
define("MEMBER_KEY", "ABCDEFGHIJKLMNOP123456789");
define("CONNECTION_ID", "1234-45678");

define("OAUTH_TOKEN_ENDPOINT", "https://sbx-op.mydexid.org/oauth2/token");
define("OAUTH_CLIENT_ID", "abcd1234-abcd-1234-abcd-123456abcdef");
define("OAUTH_CLIENT_SECRET", "CHANGEME");
define("OAUTH_SCOPE", "mydex:pdx");

/*
 * Get an OAuth2.0 access token from Mydex's OAuth2.0 server.
 */
function getOAuth2AccessToken() {
    $post_data = [
        'grant_type' => "client_credentials",
        'scope' => OAUTH_SCOPE,
    ];

    // Convert token params to string format
    $post_params = http_build_query($post_data, '', '&', PHP_QUERY_RFC1738);

    // Content type needs to be form encoded
    $content_type = 'application/x-www-form-urlencoded';
    $headers[] = "Content-Type: {$content_type}";
    // Client ID and Secret are sent as the Authorization (basic) header
    $headers[] = 'Authorization: Basic ' . base64_encode(urlencode(OAUTH_CLIENT_ID) . ':' . urlencode(OAUTH_CLIENT_SECRET));

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

    return $auth_data->access_token;
}

/**
 * Write the ds_personal_details dataset for this PDS.
 */
function writeToPds() {
    // prepare the payload to be posted
    $payload = array(
        "ds_personal_details" => array(
            "field_personal_email_address" => "john@example.com",
            "field_personal_faname" => "Citizen",
            "field_personal_fname" => "John",
            "field_personal_title" => "Mr",
            "field_personal_gender" => "Male",
            "field_gender_birth" => "Male",
            "field_personal_marital_status" => "Married",
            "field_personal_mobile_phone" => "+441234567890"
        )
    );

    $arguments_array = array(
        'source_type=connection',
        'con_id=' . CONNECTION_ID,
        'key=' . MEMBER_KEY,
        'instance=0',
        'dataset=ds_personal_details',
    );
    $arguments = implode('&', $arguments_array);

    // get OAuth2.0 access token
    $oauth_access_token = getOAuth2AccessToken();

    // Construct request to the PDX API
    $pds_request_url = MYDEX_PDS_PATH . "/api/pds/pds/" . MEMBER_UID . ".json?" . $arguments;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pds_request_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $oauth_access_token # OAuth2.0 access token, scoped to mydex:pdx
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$payload));

    $request = curl_exec($ch);

    $curl_error = curl_error($ch);
    curl_close($ch);

    echo print_r($request, true) . "\n";
}

writeToPds();
