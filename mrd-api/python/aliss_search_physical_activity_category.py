#!/usr/bin/env python3

import base64
import requests

# Constants
OAUTH_TOKEN_ENDPOINT = "https://op.mydexid.org/oauth2/token"
OAUTH_MRD_CLIENT_ID = "abcd1234-abcd-1234-abcd-123456abcdef"
OAUTH_MRD_CLIENT_SECRET = "CHANGEME"
OAUTH_MRD_SCOPES = "aliss"
OAUTH_MRD_GRANT_TYPE = "client_credentials"
MRD_API_ENDPOINT = "https://api-mrd.mydex.org/aliss/get-services/search"

def request_token():
    # Prepare the POST data
    post_data = {
        'grant_type': OAUTH_MRD_GRANT_TYPE,
        'scope': OAUTH_MRD_SCOPES,
    }

    # Prepare the headers with Basic Auth
    credentials = f"{OAUTH_MRD_CLIENT_ID}:{OAUTH_MRD_CLIENT_SECRET}"
    b64_credentials = base64.b64encode(credentials.encode()).decode()

    headers = {
        "Content-Type": "application/x-www-form-urlencoded",
        "Authorization": f"Basic {b64_credentials}",
    }

    # Send the POST request
    response = requests.post(OAUTH_TOKEN_ENDPOINT, data=post_data, headers=headers)

    if response.ok:
        auth_data = response.json()
        return auth_data.get("access_token")
    else:
        print("Token request failed:", response.status_code, response.text)
        return None

def request_aliss(token):
    params = {
        'categories': 'physical-activity'
    }

    headers = {
        "Authorization": f"Bearer {token}",
        "X-Mrd-Scopes": OAUTH_MRD_SCOPES,
    }

    response = requests.get(MRD_API_ENDPOINT, headers=headers, params=params)

    if response.ok:
        print(response.json())
    else:
        print("ALISS request failed:", response.status_code, response.text)

def main():
    print("Requesting a token")
    token = request_token()

    if token:
        request_aliss(token)

if __name__ == "__main__":
    main()

