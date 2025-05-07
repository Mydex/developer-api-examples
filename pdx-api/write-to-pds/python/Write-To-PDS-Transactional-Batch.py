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

def write_to_pds():
    """
    Write two rows at once into the ds_referrals dataset for this PDS.
    """
    payload = {
        "ds_referrals": [
            {
                "complete_time": "1642137822",
                "contact_name": "John Smith",
                "contact_number": "0123456788",
                "created_timestamp": "1642137822",
                "contact_title": "Manager",
                "duration": "12",
                "end_time": "1642137822",
                "external_id": "123",
                "is_processed": "1",
                "memo": "Note test",
                "referree_availability": {
                    "AM": ["Monday", "Friday"],
                    "PM": ["Monday", "Tuesday", "Wednesday"]
                },
                "referree_background": "The first day that we attended is the first day that we have started to introduce ourselves to friends and families.",
                "referrer_name": "Service Test",
                "referree_requirements": "Fruit, vegetables, legumes (e.g. lentils and beans), nuts and whole grains",
                "service_description": "Description test",
                "service_email": "test@email.com",
                "service_id": "1",
                "service_name": "Service Test",
                "service_url": "test.com",
                "start_time": "1642137822",
                "status": "In Progress",
                "type": "signpost",
                "updated_timestamp": "1642137822"
            },
            {
                "complete_time": "1642137822",
                "contact_name": "Jane Smyth",
                "contact_number": "9012345678",
                "created_timestamp": "1642137822",
                "contact_title": "Supervisor",
                "duration": "12",
                "end_time": "1642137822",
                "external_id": "45014",
                "is_processed": "1",
                "memo": "Note test",
                "referree_availability": {
                    "AM": ["Monday","Tuesday"]
                    "PM": ["Monday","Tuesday","Friday"]
                },
                "referree_background": "Seeking job placement.",
                "referrer_name": "Careers UK",
                "referree_requirements": "Pen, paper, recent copy of curriculum vitae",
                "service_description": "We help you find a job",
                "service_email": "test@example.com",
                "service_id": "1",
                "service_name": "Careers UK",
                "service_url": "example.com",
                "start_time": "1642137822",
                "status": "In Progress",
                "type": "signpost",
                "updated_timestamp": "1642137822"
            }
        ]
    }

    query_params = {
        'uid': MEMBER_UID,
        'source_type': 'connection',
        'con_id': CONNECTION_ID,
        'key': MEMBER_KEY,
        'instance': '0',
        'dataset': 'ds_referrals',
    }

    oauth_access_token = get_oauth2_access_token()

    url = f"{MYDEX_PDS_PATH}/api/pds/transaction"
    headers = {
        'Authorization': f"Bearer {oauth_access_token}",
        'Content-Type': 'application/json'
    }

    response = requests.post(url, headers=headers, params=query_params, data=json.dumps(payload))
    response.raise_for_status()

    print(json.dumps(response.json(), indent=2))

if __name__ == "__main__":
    write_to_pds()
