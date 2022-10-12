# Usage

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
| `{signatures.signature_letter_html}`        | Letter signature (HTML)             |
| `{signatures.signature_email_html}`         | E-mail signature (HTML)             |
| `{signatures.signature_email_plain}`        | E-mail signature (plain text)       |
| `{signatures.signature_mass_mailing_html}`  | Mass mailing signature (HTML)       |
| `{signatures.signature_mass_mailing_plain}` | Mass mailing signature (plain text) |