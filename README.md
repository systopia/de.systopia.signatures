# Signatures Extension

CiviCRM allows associating HTML-formatted signatures to e-mail addresses of
contacts, which are being added when composing messages. This is feasible for
sporadic generation of easy e-mails. When it comes to using those signatures in
mass mailings or letters or utilising message templates, this is going to to be
difficult.

The *Signatures* extension allows multiple separate signatures to be attached to
contacts, per contact and message type, i.e. letters and e-mails, each as plain
text and HTML-formatted, as well as for mass mailings. Once created, those are
available as tokens in each message and can be used within message templates.

When sending messages, those tokens are being replaced with the particular
signature of the logged-in contact, or the creator contacts, when using mass
mailings (which can be sent automatically by a cron job), respectively.

## Usage

When installed, the extension provides a navigation menu item "Signatures"
within the "Contacts" sub menu, which leads to a configuration form for the
signatures for the current contact.

!!! hint
    The form tells you which contact's signatures are currently being edited.
    Users with the permission to edit all contacts can edit signatures for other
    contacts by adding the `cid` parameter to the URL, e.g. `?cid=1`.

The following tokens are available for usage in messages:

| Token                            | Meaning                             |
|----------------------------------|-------------------------------------|
| `{signature_letter_html}`        | Letter signature (HTML)             |
| `{signature_email_html}`         | E-mail signature (HTML)             |
| `{signature_email_plain}`        | E-mail signature (plain text)       |
| `{signature_mass_mailing_html}`  | Mass mailing signature (HTML)       |
| `{signature_mass_mailing_plain}` | Mass mailing signature (plain text) |
