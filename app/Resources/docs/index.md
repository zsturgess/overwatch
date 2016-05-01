#Overwatch
##An introduction to testing
Tests in Overwatch take one of two forms:
- _Expect xxx toBeMadeOfXs_
- _Expect xxx toBeBefore yyy_

The `toXxx` part is called the _expectation_. The part before the expectation is called the _actual value_. The part after the expectation is called the _expected value_, and may be optional, depending on the expectation.

Test results have a status:
- _Pass_ (shown in green) - The test completed successfully
- _Unsatisfactory_ (shown in yellow) - The test completed successfully, but encountered an unexpected result not considered a failure
- _Fail_ (shown in red) - The test encountered a result considered a failure, as according to the test's expectation.
- _Error_ (shown in red) - The test failed to execute. The most common cause is attempting to run an expectation that requires administrative permissions as a non-admin.

##Expectations
Overwatch comes bundled with the following expectations:
- [toPing](expectations/to_ping.md) - Expects the hostname or IP address given as the actual to respond to an ICMP ping
- [toResolveTo](expectations/to_resolve_to.md) - Expects the hostname given as the actual to have a DNS record with the value of the expected
- [toRespondHttp](expectations/to_respond_http.md) - Expects the URL given as the actual to respond with a HTTP code
- [toRespondWithMimeType](expectations/to_respond_with_mime_type.md) - Expects the URL given as the actual to respond with Content-Type equal to the expected
- [toContainText](expectations/to_contain_text.md) - Expects the URL given as the actual to contain the text, or match the regular expression given as the expected

Overwatch is also set up in such a way to allow the creation of 3rd Party "addon" expectations, see [Extending Overwatch](extending.md)

##Getting test results
Tests are run by the overwatch:tests:run command (`php app/console overwatch:tests:run`) and the results are saved into the database.

Overwatch will pass test results off to _result reporters_ as it saves them to the database. Overwatch comes bundled with the following result reporters:
- [EmailReporter](result-reporters/email_reporter.md) - Will send a notification e-mail to each user in the same group as the test, if the user's notification settings allow
- [SmsReporter](result-reporters/sms_reporter.md) - Will send an SMS alert to each user in the same group as the test, as long as the user has provided a telephone number and their notification settings allow.

Overwatch is also set up in such a way to allow the creation of 3rd Party "addon" result reporters, see [Extending Overwatch](extending.md)

##Overwatch REST API
In addition to being extensible, Overwatch is designed to be hackable, and as such exposes a [RESTful API](api.md).

