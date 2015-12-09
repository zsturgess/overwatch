# ToRespondWithMimeType expectation

The _ToRespondWithMimeType_ expectation expects the URL given as the actual value to respond with the mime type given as the expected value. The expected value is required. The actual value should be a valid URL.

## Example

```
Expect https://www.google.co.uk/ toRespondWithMimeType text/html
```

This expectation will check that https://www.google.co.uk/ responds with the mime type "text/html".

## Configuration

```
to_respond_with_mime_type:
    allow_errors: false
    timeout: 5
```
**allow_errors** (boolean) If true, HTTP errors (4xx and 5xx responses) will still have their MIME types checked. When false (the default), HTTP errors will cause the expectation to return an ERROR result.
**timeout** (float) Time, in seconds, to wait for a HTTP response before timing out. Use 0 for no timeout.
