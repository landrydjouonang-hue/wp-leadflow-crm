# Security policy

## Reporting a vulnerability

Please report security issues privately, not in a public issue tracker.

Use GitHub's **Report a vulnerability** button (Security → Advisories) on
this repository, or contact the maintainer directly. Include:

- what the issue is and how to reproduce it,
- the affected version, and
- the impact you think it has.

You will get an acknowledgement, and a fix or an explanation. Please give
a reasonable amount of time for a fix before disclosing the issue.

## Supported versions

The latest release is supported. Security fixes are released as a new
patch version.

## How the plugin protects data

- **Authorization:** every screen, form action, AJAX endpoint and REST
  route checks a capability; record-level rules decide who may edit or
  delete a specific record.
- **Request integrity:** every state-changing form and AJAX call verifies
  a nonce before doing anything.
- **Input:** values are sanitized and validated by type in the data layer,
  with the same rules in the admin and the API.
- **Output:** templates escape on output (`esc_html`, `esc_attr`,
  `esc_url`); rich text goes through `wp_kses_post()`.
- **SQL:** all statements are prepared; identifiers are allow-listed and
  passed as `%i` placeholders.
- **Email:** addresses are validated and names and subjects stripped of
  line breaks, so headers cannot be injected. Sending is rate limited per
  user.
- **Files:** CSV uploads are stored with random names in a deny-all
  folder, deleted after use, and exported cells starting with `=`, `+`,
  `-` or `@` are neutralized against spreadsheet formula injection. The
  debug log lives in the same kind of protected folder and never records
  passwords or tokens.
- **API:** authentication is WordPress's own (logged-in cookie with a
  nonce, or Application Passwords over HTTPS). There are no custom
  credentials and no secrets stored by the plugin.

## Notes for site owners

- Serve the site over HTTPS; WordPress only offers Application Passwords
  on secure origins.
- Give people the smallest role that fits their work
  (**LeadFlow CRM → Access control**).
- The complete JSON backup contains personal data — store it safely.
- Turn the debug log off when you are done troubleshooting.
