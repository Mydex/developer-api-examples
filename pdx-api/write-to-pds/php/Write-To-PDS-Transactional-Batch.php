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
 * Write two rows at once into the ds_referrals dataset for this PDS.
 */
function writeToPds() {
    // prepare the payload to be posted
    $payload = array(
        "ds_referrals" => array(
            array(
                "complete_time" => "1642137822",
                "contact_name" => "John Smith",
                "contact_number" => "0123456788",
                "created_timestamp" => "1642137822",
                "contact_title" => "Manager",
                "duration" => "12",
                "end_time" => "1642137822",
                "external_id" => "123",
                "is_processed" => "1",
                "memo" => "Note test",
                "referree_availability" => array(
                    "AM" => array(
                        "Monday",
                        "Friday"
                    ),
                    "PM" => array(
                        "Monday",
                        "Tuesday",
                        "Wednesday"
                    )
                ),
                "referree_background" => "The first day that we attended is the first day that we have started to introduce ourselves to friends and families.",
                "referrer_name" => "Service Test",
                "referree_requirements" => "Fruit, vegetables, legumes (e.g. lentils and beans), nuts and whole grains",
                "service_description" => "Description test",
                "service_email" => "test@email.com",
                "service_id" => "1",
                "service_name" => "Service Test",
                "service_url" => "test.com",
                "start_time" => "1642137822",
                "status" => "In Progress",
                "type" => "signpost",
                "updated_timestamp" => "1642137822"
            ),
            array(
                "complete_time" => "1642137822",
                "contact_name" => "Jane Smyth",
                "contact_number" => "9012345678",
                "created_timestamp" => "1642137822",
                "contact_title" => "Supervisor",
                "duration" => "12",
                "end_time" => "1642137822",
                "external_id" => "45014",
                "is_processed" => "1",
                "memo" => "Note test",
                "referree_availability" => array(
                    "AM" => array(
                        "Monday",
                        "Tuesday"
                    ),
                    "PM" => array(
                        "Monday",
                        "Tuesday",
                        "Friday"
                    )
                ),
                "referree_background" => "Seeking job placement.",
                "referrer_name" => "Careers UK",
                "referree_requirements" => "Pen, paper, recent copy of curriculum vitae",
                "service_description" => "We help you find a job",
                "service_email" => "test@example.com",
                "service_id" => "1",
                "service_name" => "Careers UK",
                "service_url" => "example.com",
                "start_time" => "1642137822",
                "status" => "In Progress",
                "type" => "signpost",
                "updated_timestamp" => "1642137822"
            )
    );

    $arguments_transactional_array = array(
        'uid=' . MEMBER_UID,
        'source_type=connection',
        'con_id=' . CONNECTION_ID,
        'key=' . MEMBER_KEY,
        'instance=0',
        'dataset=ds_referrals',
    );
    $arguments_transactional = implode('&', $arguments_transactional_array);

    // get OAuth2.0 access token
    $oauth_access_token = getOAuth2AccessToken();

    // Construct request to the PDX API
    $pds_request_url = MYDEX_PDS_PATH . "/api/pds/transaction?" . $arguments_transactional;
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

    echo json_decode($request) . "\n";
}

writeToPds();
