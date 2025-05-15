#!/usr/bin/env python3

from flask import Flask, redirect, request, session, url_for
import requests
import secrets
import base64
import urllib.parse

app = Flask(__name__)
app.secret_key = secrets.token_hex(16)

# === Configuration ===
CLIENT_ID = "your-client-id"
CLIENT_SECRET = "your-client-secret"
REDIRECT_URI = "https://oidc_rp.example.com/callback"

# OIDC provider config
OIDC_OP = "https://sbx-op.mydexid.org"
AUTHORIZATION_ENDPOINT = f"{OIDC_OP}/oauth2/auth"
TOKEN_ENDPOINT = f"{OIDC_OP}/oauth2/token"
USERINFO_ENDPOINT = f"{OIDC_OP}/userinfo"


# === Step 1: Redirect the front page to OP's authorization endpoint ===
@app.route("/")
def index():
    state = secrets.token_urlsafe(16)
    session["state"] = state

    params = {
        "response_type": "code",
        "client_id": CLIENT_ID,
        "redirect_uri": REDIRECT_URI,
        "scope": "openid mydexid",
        "state": state,
        # If you want to load a registration screen instead of
        # a login screen, add "action": "register" to these params.
    }
    auth_url = f"{AUTHORIZATION_ENDPOINT}?{urllib.parse.urlencode(params)}"
    return redirect(auth_url)


# === Step 2: Handle redirect back from OP and exchange code for tokens ===
@app.route("/callback")
def callback():
    if request.args.get("state") != session.get("state"):
        return "Invalid state", 400

    code = request.args.get("code")
    if not code:
        return "Missing code", 400

    # Use Basic Auth header with Client ID and Client Secret
    basic_auth = requests.auth.HTTPBasicAuth(CLIENT_ID, CLIENT_SECRET)

    # Exchange code for token
    data = {
        "grant_type": "authorization_code",
        "code": code,
        "redirect_uri": REDIRECT_URI,
    }

    headers = {"Content-Type": "application/x-www-form-urlencoded"}

    token_response = requests.post(
        TOKEN_ENDPOINT, data=data, auth=basic_auth, headers=headers
    )

    if token_response.status_code != 200:
        return f"Token exchange failed: {token_response.text}", 500

    # Get the tokens
    tokens = token_response.json()

    access_token = tokens.get("access_token")
    id_token = tokens.get("id_token")

    # Store tokens in the session
    session["access_token"] = access_token
    session["id_token"] = id_token

    # Redirect to the userinfo URL to see claims
    return redirect(url_for("userinfo"))


# === Step 3: Use access_token from session to query /userinfo ===
@app.route("/userinfo")
def userinfo():
    access_token = session.get("access_token")
    if not access_token:
        return "Missing access token", 400

    headers = {"Authorization": f"Bearer {access_token}"}
    userinfo_response = requests.get(USERINFO_ENDPOINT, headers=headers)
    userinfo_response.raise_for_status()
    return userinfo_response.json()


if __name__ == "__main__":
    app.run(debug=True, host="127.0.0.1", port=5555)
