# Enhanced Registration

Enhanced Registration is a community Nextcloud app for self-hosted installations that use LLDAP as their identity backend and need an approval-based registration and password reset flow.

It is not a replacement for every use case covered by the official Registration app. It targets a narrower setup: Nextcloud + LLDAP + a password bridge service.

## Features

- Public registration form
- Email confirmation code flow
- LLDAP user creation
- Pending approval workflow
- Admin approval and rejection actions
- Assignable LLDAP group management
- Configurable default groups for approved users
- Password reset flow
- Password bridge integration for setting and changing LLDAP passwords
- Configurable mail templates
- Configurable password policy
- Optional Have I Been Pwned password check
- Email domain allow/deny lists
- Rate limiting
- Audit log with retention settings
- Admin setup status and test actions
- Language mode: auto, German, English

## Requirements

- Nextcloud
- PHP compatible with your Nextcloud version
- LLDAP
- Working Nextcloud mail configuration
- Password bridge service for LLDAP password changes

The password bridge service is required for registration and password reset flows. Nextcloud can authenticate users through LDAP/LLDAP, but this app needs the bridge to set or change LLDAP passwords during account creation and password reset.

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
- Password bridge URL
- Bridge secret
- Login URL
- Mail templates
- Password policy

Use the built-in test buttons to verify:

- LLDAP connection
- Password bridge
- Mail delivery

## Password bridge

The app delegates password setting and password reset operations to a bridge service.

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

## Security notes

- Do not expose the password bridge publicly without additional protection.
- Use a strong bridge secret.
- Do not commit real configuration values or secrets.
- Keep LLDAP admin credentials restricted.
- Review audit logs regularly.

## Status

This app is an early community release. Test carefully before using it in production.

## License

AGPL-3.0-or-later.

## Disclaimer

This project is not affiliated with or endorsed by Nextcloud GmbH or the official Registration app.
