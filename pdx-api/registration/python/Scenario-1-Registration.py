#!/usr/bin/env python3

import base64
import hashlib
import json
import requests
from urllib.parse import urlencode

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
    data = {
        "grant_type": "client_credentials",
        "scope": OAUTH_SCOPE,
    }

    auth_string = f"{OAUTH_CLIENT_ID}:{OAUTH_CLIENT_SECRET}"
    auth_header = base64.b64encode(auth_string.encode()).decode()

    headers = {
        "Content-Type": "application/x-www-form-urlencoded",
        "Authorization": f"Basic {auth_header}",
    }

    response = requests.post(OAUTH_TOKEN_ENDPOINT, data=urlencode(data), headers=headers)
    response.raise_for_status()
    return response.json()["access_token"]


def get_short_access_token():
    """
    Get a short access token from the PDX API.
    """
    url = f"{MYDEX_PDS_PATH}/access-token/{CONNECTION_NID}"
    headers = {"Content-Type": "application/json"}
    response = requests.get(url, headers=headers)
    response.raise_for_status()
    return response.json()


def create_registration_link():
    """
    Make request to the PDX API using Connection Token Hash,
    Short Access Token and OAuth2.0 access token to create a
    link for the member to create a Private Key and register
    their account.
    """
    mydexid = "johncitizen"
    email = "john@example.com"
    password = "This-Is-Johns-Password"

    request_data = {
        "mydexid": mydexid,
        "email": email,
        "password": password,
        "connection_nid": CONNECTION_NID,
        "connection_token_hash": hashlib.sha512(CONNECTION_TOKEN.encode()).hexdigest(),
        "return_to": "https://example.com/hello-world",
    }

    short_access_token = get_short_access_token()
    if isinstance(short_access_token, dict) and "token" in short_access_token:
        short_access_token = short_access_token["token"]

    authentication = hashlib.sha512((short_access_token + CONNECTION_TOKEN).encode()).hexdigest()
    oauth_access_token = get_oauth2_access_token()

    headers = {
        "Authentication": authentication,
        "Short-Access-Token": short_access_token,
        "Authorization": f"Bearer {oauth_access_token}",
        "Content-Type": "application/json",
    }

    url = f"{MYDEX_PDS_PATH}/api/pds"
    response = requests.post(url, headers=headers, json=request_data)
    response.raise_for_status()
    print("Please visit", response.json())


if __name__ == "__main__":
    create_registration_link()
