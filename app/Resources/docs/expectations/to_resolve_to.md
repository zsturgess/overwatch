#ToResolveTo Expectation
The _toResolveTo_ expectation expects the actual to have a DNS record with the same value as the expected.

##Example
```
Expect status.github.com toResolveTo octostatus-production.github.com
```
The actual value should be a valid DNS hostname such as "www.example.com".

This expectation will check the record types configured for a value that equals the expected value.

##Configuration
```
to_resolve_to:
    record_types:
        - A
        - AAAA
        - CNAME
```
**record_types** (array) List of record types to look at when searching for the expected value.

##Notes
Reverse lookups can be tested by using an actual value in in-addr.arpa notation.
