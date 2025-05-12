# Mydex Developer API examples

This repository contains example API requests to compliment the instructions at https://dev.mydex.org .

They are intended for developers who are seeking to integrate their services into the Mydex Safe, Secure Cloud.

## Structure of the examples

This repository contains the following top-level folders:

 * pdx-api - Examples of how to register MydexIDs and PDS and perform First Time Connection, as well as how to read/write to a PDS once that connection is made
 * idp-api - (coming soon) Examples of how to use OpenIDConnect to register or perform login with a MydexID, or map third party identity providers to MydexIDs.
 * mrd-api - (coming soon) Examples of how to authenticate and use Master Reference Data services


Inside each of those top-level directories are the following sub-directories:

 * php - Examples using the PHP programming language
 * python - Examples using the Python programming language
 * postman - Postman collections which can be run with Postman or the `newman` CLI tool.

## What to change in the examples

Be sure to change the values in the examples to match the credentials you have been provided by Mydex. These include:

 * The OAuth2.0 client ID and secret
 * The Dedicated Connection NID (45678 is just an example)
 * The Dedicated Connection Key (and its SHA512 hash, in the Postman files)
 * The example MydexID/email/password of 'johncitizen' to be a more realistic user/email/password combination
 * The return_to parameter to take the member back to your app after registration or FTC
 * The 'Member Key' and 'Connection ID' (which is a concatenation of the member's UID and the Dedicated Connection NID) which would only be known once you've completed FTC with a registered MydexID. These values are POSTed back to your API callback URL that you provided Mydex, for you to consume and use in the read/write request examples.

## Getting more help

If you are still stuck and need more help, please see https://dev.mydex.org/support.html .
