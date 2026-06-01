# Security Policy

## Reporting a vulnerability

Please report security issues privately instead of opening a public issue.

If this repository does not yet have a dedicated private security contact configured, open a minimal public issue asking for a private contact method. Do not include technical details, exploit steps, credentials, logs, or other sensitive information in the public issue.

## Supported status

Enhanced Registration is currently distributed as a GitHub/custom Nextcloud app. It is not signed for the Nextcloud App Store.

| Version | Status |
| --- | --- |
| 0.2.x | Best-effort maintenance |
| older than 0.2.x | Not recommended |

This project is maintained on a best-effort basis. Response times, fix timelines, and long-term support are not guaranteed.

## Scope

This app handles sensitive account-management flows:

- public registration
- email verification
- pending-user approval
- LLDAP user creation and group assignment
- password reset
- Direct LDAP password changes
- optional legacy bridge fallback
- admin configuration values

Treat LLDAP admin credentials, bridge secrets, mail configuration, audit logs, and server backups as sensitive.

## Production recommendations

For production-like deployments:

- Use HTTPS for public Nextcloud access.
- Configure the Nextcloud LDAP login filter so pending and blacklisted users cannot log in.
- Allow login only for approved target groups.
- Use the Direct LDAP password writer where possible.
- Keep the legacy bridge disabled unless it is explicitly needed.
- If the bridge is used, bind it only to a trusted internal interface and protect it with a strong shared secret.
- Enable rate limits.
- Use email-domain allow/deny rules where appropriate.
- Review audit logs regularly.
- Keep Nextcloud, PHP, LLDAP, the LDAP PHP extension, and this app updated.
- Test all registration, approval, rejection, and password-reset flows in staging before production use.

## Signing status

This app does not currently include an `appinfo/signature.json` file and is not App-Store-signed. Administrators should treat it as a custom app and review the code before installation.

## License

Enhanced Registration is distributed under the GNU Affero General Public License v3.0 or later. See `LICENSE` for the full license text.
