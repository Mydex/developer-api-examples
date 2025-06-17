# MRD API Postman examples

To run these examples, you can use newman:

```
newman run aliss.json \
    --env-var "OAUTH_MRD_CLIENT_AUTH_BASIC=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
    --env-var "OAUTH_URL=https://op.mydexid.org" \
   --env-var="MRD_API_URL=https://api-mrd.mydex.org"
```

To generate the OAUTH_MRD_CLIENT_AUTH_BASIC base64-encoded value, you can use Python:

```
import base64

OAUTH_MRD_CLIENT_ID="abcd1234-abcd-1234-abcd-123456abcdef"
OAUTH_MRD_CLIENT_SECRET="CHANGEME"

credentials = f"{OAUTH_MRD_CLIENT_ID}:{OAUTH_MRD_CLIENT_SECRET}"
b64_credentials = base64.b64encode(credentials.encode()).decode()

print(b64_credentials)
```
