# ToContainText expectation

The _ToContainText_ expectation expects the URL given as the actual value to respond with content that can be matched by the expected value. The expected value is required and can be a simple string or a regular expression. The actual value should be a valid URL.

## Example

```
Expect https://www.google.co.uk/ toContainText search
```

This expectation will check that https://www.google.co.uk/ responds where "search" is somewhere on the page.

## Configuration

```
expectations_global_httpTimeout:                30
expectations_global_permitHttpErrors:           false
expectations_toContainText_crawlableTypes:      ['text/html', 'text/xml', 'application/xml', 'text/rss+xml', 'application/rss+xml', 'application/rdf+xml', 'application/atom+xml']
```
**expectations_global_httpTimeout** (float) Time, in seconds, to wait for a HTTP response before timing out. Use 0 for no timeout.

**expectations_global_permitHttpErrors** (boolean) If true, HTTP errors (4xx and 5xx responses) will still have their responses matched. When false (the default), HTTP errors will cause the expectation to return an ERROR result.

**expectations_toContainText_crawlableTypes** (array) An array of MIME Types that will be parsed as an XML-based document. Only the textual content of elements, not attributes will be used for the purposes of matching.