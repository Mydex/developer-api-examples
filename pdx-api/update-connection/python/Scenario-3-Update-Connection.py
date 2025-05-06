#!/usr/bin/env python3

import base64
import hashlib
import json
import requests
import urllib.parse

# Constants
MYDEX_PDS_PATH = "https://sbx-api.mydex.org"
CONNECTION_NID = "45678"
CONNECTION_TOKEN = "abcdefghijklmnopqrstuvwxyz123456789"

OAUTH_TOKEN_ENDPOINT = "https://sbx-op.mydexid.org/oauth2/token"
OAUTH_CLIENT_ID = "abcd1234-abcd-1234-abcd-123456abcdef"
OAUTH_CLIENT_SECRET = "CHANGEME"
OAUTH_SCOPE = "mydex:pdx"

def get_oauth2_access_token():
    """
    Get an OAuth2.0 access token from Mydex's OAuth2.0 server.
    """
    post_data = {
        'grant_type': 'client_credentials',
        'scope': OAUTH_SCOPE
    }

    # Basic Auth Header
    client_id_enc = urllib.parse.quote(OAUTH_CLIENT_ID, safe='')
    client_secret_enc = urllib.parse.quote(OAUTH_CLIENT_SECRET, safe='')
    basic_auth = base64.b64encode(f"{client_id_enc}:{client_secret_enc}".encode()).decode()

    headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Authorization': f"Basic {basic_auth}"
    }

    response = requests.post(OAUTH_TOKEN_ENDPOINT, headers=headers, data=post_data)
    response.raise_for_status()
    return response.json()['access_token']

def get_short_access_token():
    """
    Get a short access token from the PDX API.
    """
    url = f"{MYDEX_PDS_PATH}/access-token/{CONNECTION_NID}"
    headers = {
        'Content-Type': 'application/json'
    }

    response = requests.get(url, headers=headers)
    response.raise_for_status()
    return response.json()

def create_ftc():
    """
    Make request to the PDX API using Connection Token Hash,
    Short Access Token and OAuth2.0 access token to create a
    link for the existing MydexID to enter their Private Key
    and accept the updated Data Sharing Agreement, updating
    their existing connection between their PDS and your
    Subscriber Dedicated Connection.
    """
    mydexid = "johncitizen"

    request_data = {
        'mydexid': mydexid,
        'connection_nid': CONNECTION_NID,
        'connection_token_hash': hashlib.sha512(CONNECTION_TOKEN.encode()).hexdigest(),
        'return_to': "https://example.com/hello-world",
        'version': "2",
    }

    # Get tokens
    short_access_token = get_short_access_token()
    authentication = hashlib.sha512((short_access_token + CONNECTION_TOKEN).encode()).hexdigest()
    oauth_access_token = get_oauth2_access_token()

    # POST to the connection endpoint
    url = f"{MYDEX_PDS_PATH}/api/connection/update"
    headers = {
        'Authentication': authentication,
        'Short-Access-Token': short_access_token,
        'Authorization': f"Bearer {oauth_access_token}",
        'Content-Type': 'application/json'
    }

    response = requests.post(url, headers=headers, data=json.dumps(request_data))
    response.raise_for_status()

    print("Please visit", response.json())

if __name__ == "__main__":
    create_ftc()
