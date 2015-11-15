# ToRespondWithMimeType expectation

The _ToRespondWithMimeType_ expectation expects the URL given as the actual value to respond with the mime type given as the expected value. The expected value is required. The actual value should be a valid URL.

## Example

```
Expect https://www.google.co.uk/ toRespondWithMimeType text/html
```

This expectation will check that https://www.google.co.uk/ responds with the mime type "text/html".

## Configuration

```
to_respond_with_mime_type: ~
```

There are currently no global configuration options.
