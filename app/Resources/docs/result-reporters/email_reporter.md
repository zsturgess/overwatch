#Email reporter
The _EmailReporter_ result reporter sends users results via e-mail. It respects user alert settings.

##Configuration
```
resultReporters_email_enabled:  true
mailer_from:                    example@example.com
```

**resultReporters_email_enabled** (boolean) If false, will not attempt to send any emails. Defaults to true.

**mailer_from** (string) The from address the result reporter will use. Defaults to `overwatch@example.com` (but see notes).

##Notes
EmailReporter will attempt to use the SMTP credentials provided during installation.
