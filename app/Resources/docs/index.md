#Overwatch
##An introduction to testing
Tests in Overwatch take one of two forms:
- _Expect xxx toBeMadeOfXs_
- _Expect xxx toBeBefore yyy_

The `toXxx` part is called the _expectation_. The part before the expectation is called the _actual value_. The part after the expectation is called the _expected value_, and may be optional, depending on the expectation.

##Expectations
Overwatch comes bundled with the following expectations:
- [toPing](expectations/to_ping.md) - Expects the hostname or IP address given as the actual to respond to an ICMP ping
- [toResolveTo](expectations/to_resolve_to.md) - Expects the hostname given as the actual to have a DNS record with the value of the expected

Overwatch is also set up in such a way to allow the creation of 3rd Party "addon" expectations, see [Extending Overwatch](extending.md)

##Getting test results
Tests are run by the overwatch:tests:run command (`php app/console overwatch:tests:run`) and the results are saved into the database.

Overwatch will pass test results off to _result reporters_ as it saves them to the database. Overwatch comes bundled with the following result reporters:
- [EmailReporter](result-reporters/email_reporter.md) - Will send a notification e-mail to each user in the same group as the test, if the user's notification settings allow

Overwatch is also set up in such a way to allow the creation of 3rd Party "addon" result reporters, see [Extending Overwatch](extending.md)
