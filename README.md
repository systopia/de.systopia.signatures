# Signatures Extension

CiviCRM allows associating HTML-formatted signatures to e-mail addresses of contacts, which are being added when composing messages. This is feasible for sporadic generation of easy e-mails. When it comes to using those signatures in mass mailings or letters or utilising message templates, this is going to to be difficult.

The *Signatures* extension allows multiple separate signatures to be attached to contacts, per contact and message type, i.e. letters and e-mails, each as plain text and HTML-formatted, as well as for mass mailings. Once created, those are available as tokens in each message and can be used within message templates.

When sending messages, those tokens are being replaced with the particular signature of the logged-in contact, or the creator contacts, when using mass mailings (which can be sent automatically by a cron job), respectively.

## Documentation
- https://docs.civicrm.org/signatures/en/latest