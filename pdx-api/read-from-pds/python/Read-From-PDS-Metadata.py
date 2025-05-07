#!/usr/bin/env python3

import base64
import json
import urllib.parse
import requests

# Constants
MYDEX_PDS_PATH = "https://sbx-api.mydex.org"
MEMBER_UID = "1234"
MEMBER_KEY = "ABCDEFGHIJKLMNOP123456789"
CONNECTION_ID = "1234-45678"

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

def read_from_pds():
    """
    Read the ds_personal_details dataset for this PDS.
    """
    query_params = {
        'source_type': 'connection',
        'con_id': CONNECTION_ID,
        'key': MEMBER_KEY,
        'instance': '0',
        'dataset': 'ds_personal_details',
    }

    oauth_access_token = get_oauth2_access_token()

    url = f"{MYDEX_PDS_PATH}/api/pds/pds/{MEMBER_UID}.json"
    headers = {
        'Authorization': f"Bearer {oauth_access_token}"
    }

    response = requests.get(url, headers=headers, params=query_params)
    response.raise_for_status()

    print(json.dumps(response.json(), indent=2))

if __name__ == "__main__":
    read_from_pds()
