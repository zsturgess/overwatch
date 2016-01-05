# ToContainText expectation

The _ToContainText_ expectation expects the URL given as the actual value to respond with content that can be matched by the expected value. The expected value is required and can be a simple string or a regular expression. The actual value should be a valid URL.

## Example

```
Expect https://www.google.co.uk/ toContainText search
```

This expectation will check that https://www.google.co.uk/ responds where "search" is somewhere on the page.

## Configuration

```
to_contain_text:
    allow_errors: false
    crawlable_types: ['text/html', 'text/xml', 'application/xml', 'text/rss+xml', 'application/rss+xml', 'application/rdf+xml', 'application/atom+xml']
    timeout: 5
```
**allow_errors** (boolean) If true, HTTP errors (4xx and 5xx responses) will still have their responses matched. When false (the default), HTTP errors will cause the expectation to return an ERROR result.
**crawlable_types** (array) An array of MIME Types that will be parsed as an XML-based document. Only the textual content of elements, not attributes will be used for the purposes of matching.
**timeout** (float) Time, in seconds, to wait for a HTTP response before timing out. Use 0 for no timeout.