# Enhanced Registration

> **Current stable release:** `v0.2.3`
> **Previous stable release:** `v0.2.2`
>
> `v0.2.3` adds safer approval handling, optional local/LAN access restrictions, CSP-friendly success pages, generated route URLs, improved English UI translations, and security documentation cleanup.

Enhanced Registration is a community Nextcloud app for self-hosted installations that use LLDAP as their identity backend and need an approval-based registration and password reset flow.

It is not a replacement for every use case covered by the official Registration app. It targets a narrower setup: Nextcloud + LLDAP. Since v0.2.0, passwords are changed directly through LDAP; the bridge is only a legacy fallback.

## v0.2.3 notes

Version 0.2.3 is a security and polish release.

Highlights:

- Approval is blocked when no assignable target group is selected.
- Users remain pending when approval cannot safely assign a valid target group.
- Registration and password reset can be configured as public, local/LAN-only, or disabled.
- Success pages no longer use inline JavaScript; the countdown is handled by the bundled `success-countdown.js` asset.
- Public app URLs and admin form actions use generated Nextcloud route URLs instead of hardcoded `/index.php/...` paths.
- English UI translations are improved for public pages, password reset, and admin areas.
- Security documentation now clarifies Direct LDAP as the recommended password writer and the legacy bridge as fallback only.

## v0.2.2 notes

Version 0.2.2 hardens approval group assignment server-side, keeps users in the pending group until target groups were assigned successfully, adds the custom LLDAP password-reset flow, improves the login/register/reset links, adds basic CI and smoke checks, and updates release metadata.

## v0.2.0 notes

Version 0.2.0 adds the Direct LDAP password writer, keeps the bridge as a legacy fallback, improves LLDAP admin usability, shows LLDAP users without group memberships, adds full LLDAP user deletion from the admin UI, and stores password-reset one-time codes only as SHA-256 hashes.

## Features

- Public registration form
- Email confirmation code flow
- Hashed 8-digit one-time codes for registration and password reset
- LLDAP user creation
- Pending approval workflow
- Admin approval and rejection actions
- Assignable LLDAP group management
- Configurable default groups for approved users
- Password reset flow
- Direct LDAP password writer for setting and changing LLDAP passwords
- Legacy password bridge fallback
- Configurable mail templates
- Configurable password policy
- Optional Have I Been Pwned password check
- Email domain allow/deny lists
- Rate limiting
- Audit log with retention settings
- Admin setup status and test actions
- Language mode: auto, German, English

## Requirements

- Nextcloud 33
- PHP compatible with your Nextcloud version
- LLDAP
- Working Nextcloud mail configuration
- PHP LDAP extension with `ldap_exop_passwd`
- Nextcloud LDAP integration configured for LLDAP

Since v0.2.0, the recommended mode is the Direct LDAP password writer. The bridge is no longer required for normal setups and remains available only as a legacy fallback.

## Installation

Clone or copy this app into your Nextcloud custom_apps directory:

If you download the repository as a ZIP file from GitHub, rename the extracted folder from `enhanced_registration-main` to `enhanced_registration` before placing it in Nextcloud's `custom_apps` directory.

    cd /path/to/nextcloud/custom_apps
    git clone <repository-url> enhanced_registration

Enable the app:

    cd /path/to/nextcloud
    php occ app:enable enhanced_registration

Open the admin settings page:

    /settings/admin/enhanced_registration

## Configuration

Configure at least:

- LLDAP GraphQL URL
- LLDAP admin user
- LLDAP admin password
- Pending group ID
- Blacklist group ID
- Password writer mode
- LLDAP LDAP URL
- LLDAP Base DN
- LLDAP Admin DN if needed
- LLDAP User-DN template
- Legacy bridge URL and secret only if using bridge fallback
- Login URL
- Mail templates
- Password policy

Use the built-in test buttons to verify:

- LLDAP connection
- Direct LDAP password writer or legacy bridge fallback
- Mail delivery

## Release and signing status

This repository is currently distributed as an early GitHub pre-release. It is not yet packaged or signed for the Nextcloud App Store. Administrators should treat it as a custom app, review the code, and test upgrades in a staging instance before production use.

## Pending users and login restrictions

Enhanced Registration creates users in LLDAP and assigns them to a pending group before approval. For production-like deployments, configure Nextcloud's LDAP login filter so that only approved groups can log in. Pending users must not be able to authenticate to Nextcloud before they are approved.

## Password writer

The app uses the Direct LDAP password writer by default. It connects to the LLDAP LDAP endpoint and uses the LDAP password modify extended operation to set or reset passwords.

Password reset codes are stored only as SHA-256 hashes. Users receive an 8-digit one-time code by email; the database never stores that manual code in plaintext.

The legacy bridge is still included as a fallback option.

## Legacy password bridge

The bridge service is optional since v0.2.0 and should only be used for legacy fallback deployments.

Expected flow:

    Nextcloud app -> password bridge -> LLDAP

A minimal example bridge is included in the `bridge/` directory. It can be used as a starting point for a separate Docker Compose deployment.

The bridge should only be reachable from trusted systems and should validate a strong shared secret.

## Language

The app supports:

- Auto
- German
- English

In auto mode, the browser language is used. Unsupported languages fall back to English.

## CSP notes

Success pages use the bundled `success-countdown.js` asset instead of inline JavaScript. This keeps the templates friendlier to stricter Content Security Policy settings.

## Access modes

Registration and the custom password-reset flow can be configured independently:

- `public`: available from all networks
- `local_only`: only available from configured CIDR networks
- `disabled`: flow is blocked and no new registration or password-reset action is started

The default for both flows remains `public`. For password reset, `public` is usually recommended unless the deployment intentionally only supports local/LAN users.

When using `local_only` behind a reverse proxy, make sure Nextcloud's trusted proxy configuration is correct so the app sees the real client IP instead of only the proxy IP.

## Security notes

- Do not allow pending users to log in through the Nextcloud LDAP login filter.
- Do not allow blacklisted users to log in through the Nextcloud LDAP login filter.
- Use Direct LDAP password writer where possible.
- Treat LLDAP admin credentials, LDAP bind credentials, bridge secrets, reset tokens, and audit logs as sensitive.
- If using the legacy bridge, keep it internal and do not expose it publicly without additional protection.
- Use a strong bridge secret if bridge fallback is enabled.
- Review audit logs regularly.
- Test upgrades in a staging instance before production use.
- Keep reliable backups of Nextcloud, its database, and LLDAP data.

## Production recommendations

For production-like deployments:

- Configure the Nextcloud LDAP login filter so that only approved groups can authenticate.
- Keep pending and blacklisted groups outside the login-capable LDAP filter.
- Prefer Direct LDAP password writer over the legacy bridge.
- Keep registration and password reset rate limits enabled.
- Use domain allow/deny lists if public registration should be limited.
- Verify mail delivery before enabling registration for users.
- Review audit logs after approval, rejection, password reset, and LLDAP admin actions.

## Status

This app is an early community release. Test carefully before using it in production.

## License

Enhanced Registration is licensed under the GNU Affero General Public License v3.0 or later.

The `LICENSE` file contains the full standard AGPL-3.0 license text. The final “How to Apply These Terms to Your New Programs” section is part of the standard license text and is intentionally left unchanged.
