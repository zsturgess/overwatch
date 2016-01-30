#SMS reporter
The _SmsReporter_ result reporter sends users alerts via text message. It respects user alert settings.

##Configuration
```
resultReporters_sms_enabled:                    false
resultReporters_sms_twilio_accountSid:          ~
resultReporters_sms_twilio_authToken:           ~
resultReporters_sms_twilio_fromNumber:          ~
```

**resultReporters_sms_enabled** (boolean) If false, will not attempt to send any SMS alerts. Defaults to false.

**resultReporters_sms_twilio_accountSid** (string) The Account SID that will be used with the Twilio API to send text messages. (See _Notes_ for how to get this)

**resultReporters_sms_twilio_authToken** (string) The Auth Token that will be used with the Twilio API to send text messages. (See _Notes_ for how to get this)

**tresultReporters_sms_twilio_fromNumber** (string) The Twilio number that text messages should be sent from. (See _Notes_ for how to get this)

##Notes
SmsReporter uses the Twilio API to send SMS messages. To obtain an _account SID_, _auth token_ and _from number_, you will need to [sign up for a Twilio account](https://www.twilio.com/try-twilio).

If you pre-verify the telephone numbers wanting to get SMS alerts with Twilio, you can remain on a free trial account, but for convenience it's highly recommended to upgrade to a paid account. You can [find the prices for paid accounts on the Twilio website](https://www.twilio.com/sms/pricing).