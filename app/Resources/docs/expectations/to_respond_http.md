#ToRespondHttp Expectation
The _ToRespondHttp_ expectation expects the URL given as the actual value to respond with the HTTP code given as the expected value.
If the expected value is empty, or is an invalid HTTP response code, then the status code will be compared to the configuration to determine the test result.

##Example
```
Expect https://www.google.co.uk/ toRespondHttp 200
```
The actual value should be a valid URL.

This expectation will check that https://www.google.co.uk/ responds with a HTTP 200 OK.

```
Expect https://www.google.co.uk/ toRespondHttp
```
With the default configuration shown below, this expectation will check that https://www.google.co.uk/ responds with a HTTP 200, 201, 204, 206 or 304.

##Configuration
```
to_respond_http:
  allowable_codes:
    - 200
    - 201
    - 204
    - 206
    - 304
  unsatisfactory_codes:
    - 301
    - 302
    - 307
    - 308
  timeout: 2
```
**allowable_codes** (array) List of HTTP status codes to treat as a test pass when an expected value is not provided.
**unsatisfactory_codes** (array) List of HTTP status codes to treat as an unsatisfactory result when an expected value is not provided.
**timeout** (float) Time, in seconds, to wait for a HTTP response before timing out. Use 0 for no timeout.
