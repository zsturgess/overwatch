#Email reporter
The _EmailReporter_ result reporter sends users results via e-mail. It respects user alert settings.

##Configuration
```
email_reporter:
    enabled: true
    reporter_from: overwatch@example.com
```

**enabled** (boolean) If false, will not attempt to send any emails. Defaults to true.

**reporter_from** (string) The from address the result reporter will use. Defaults to `overwatch@example.com` (but see notes).

##Notes
EmailReporter will attempt to use the SMTP credentials provided during installation.

Whilst the `reporter_from` configuration parameter defaults to `overwatch@example.com` when no value is provided, a default Overwatch installation pre-configures this value to use the `mailer_from` value provided during the installation.

If you wish to edit the values provided during installation, you can edit the app/config/parameters.yml file.
