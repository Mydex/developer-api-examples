<?php

define("MYDEX_PDS_PATH", "https://sbx-api.mydex.org");
define("CONNECTION_NID", "45678");
define("CONNECTION_TOKEN", "abcdefghijklmnopqrstuvwxyz123456789");

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
 * Get a short access token from the PDX API for this Dedicated
 * Connection. Note: This is not the same as an OAuth2.0 token.
 */
function getShortAccessToken() {
    $url = MYDEX_PDS_PATH . "/access-token/" . CONNECTION_NID;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $token = json_decode($response);
    return $token;
}

/**
 * Make request to the PDX API using Connection Token Hash,
 * Short Access Token and OAuth2.0 access token to create a
 * link for the member to create a Private Key and register
 * their account.
 */
function createRegistrationLink() {
    // prepare the payload to be posted
    $mydexid = "johncitizen";
    $email = "john@example.com";
    $password = "This-Is-Johns-Password";

    $request_data = array(
        'mydexid' => $mydexid,
        'email' => $email,
        'password' => $password,
        'connection_nid' => CONNECTION_NID,
        'connection_token_hash' => hash('SHA512', CONNECTION_TOKEN),
        "return_to" => "https://example.com/hello-world",
    );

    // Get short access token
    $short_access_token = getShortAccessToken();

    // compute authentication key based on the short access token and connection token
    $authentication = hash('SHA512', $short_access_token.CONNECTION_TOKEN);

    // get OAuth2.0 access token
    $oauth_access_token = getOAuth2AccessToken();

    // Construct request to the PDX API
    $pds_request_url = MYDEX_PDS_PATH . '/api/pds';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pds_request_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authentication: ' . $authentication,
        'Short-Access-Token: ' . $short_access_token,
        'Authorization: Bearer ' . $oauth_access_token # OAuth2.0 access token, scoped to mydex:pdx
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$request_data));

    $pdsCreateRequest = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    echo "Please visit " . json_decode($pdsCreateRequest) . "\n";
}

createRegistrationLink();

// Continue with Scenario 2 to perform First Time Connection using the newly-registered MydexID and PDS
