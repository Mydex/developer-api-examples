# Sample OpenIDConnect RP in python

This is an example application, written in Python, which acts as a basic
Relying Party (RP) with the Mydex OpenIDConnect system (the OP), in the
Sandbox environment.

It is intended to help subscribers understand how to integrate with OIDC
to offload authentication to Mydex so that members can login to an app
with their MydexID.

# How to use this example

We assume you are using Linux as your Operating System. Adjust as needed
for other operating systems.

First, clone this repository, and enter this directory.

Then, create a virtual environment:

```sh
python3 -m venv venv
```

Then, activate the environment:

```sh
source venv/bin/activate
```

Now install the dependencies:

```sh
pip install flask requests
```

Now adjust the `oidc_rp.py` file, specifically the `CLIENT_ID`, `CLIENT_SECRET`
and `REDIRECT_URI` to the values that have been provided to you by Mydex.

Note also the section about `params` in the 'index' route. If you want to go
through a **registration** flow (to create a new MydexID/PDS) instead of a
login flow (**existing** MydexID/PDS), add `"action": "register"` to the
params.


Now you can run the Flask application:

```sh
python oidc_rp.py
```

This will start the service on localhost, port 5555. You will want to add
a reverse proxy in front that proxies traffic for your real RP URL that
Mydex added to the OIDC system, to this port. This would be the same domain
that forms part of your `REDIRECT_URI` in the python file.

Finally, you can visit the site using the real RP URL. You should get
redirected to the Mydex Login and Consent app. Login with a valid Sandbox
MydexID and password, and you should be returned to the `/userinfo` endpoint
which will show you claims such as this:

```json
{
  "aud": [
    "your-client-id"
  ],
  "auth_time": 1747280238,
  "iat": 1747280285,
  "iss": "https://sbx-op.mydexid.org/",
  "mydexid": "johncitizen",
  "rat": 1747280283,
  "sub": "9cefd48874bf6a1a39d5db53583b9f198ee8850cdc88a8e05ee5cafa832e9e52",
  "username": "johncitizen"
}
```
