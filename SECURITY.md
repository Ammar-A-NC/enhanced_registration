# Security Policy

## Reporting a vulnerability

Please report security issues privately instead of opening a public issue.

If no dedicated security contact is configured for this repository yet, open a minimal issue asking for a private contact method without disclosing technical details.

Do not include exploit details, credentials, tokens, server URLs, logs with secrets, or private configuration values in public issues.

## Maintenance status

This project is provided as open source and maintained on a best-effort basis in the maintainer's spare time. The maintainer does not commit to continued development, support, maintenance, or security fixes at any particular time.

Everyone is free to fork the project, adapt it to their own needs, submit pull requests, or continue development independently within the terms of the license.

## Response expectations

Response times and fix timelines are not guaranteed. Critical security reports will be prioritized where possible, but some issues may take time to investigate or resolve.

If you use this app in production, assess the risk for your own environment and apply temporary mitigations where appropriate.

## Scope

Enhanced Registration handles:

- public registration requests
- email verification codes
- pending admin approval
- LLDAP user creation
- LLDAP group assignment
- password reset codes and tokens
- direct LDAP password changes
- optional legacy password bridge calls
- audit logging
- administrative configuration

Treat the following configuration values as sensitive:

- LLDAP admin credentials
- LDAP bind DN and password
- password writer configuration
- legacy bridge secrets
- mail configuration
- generated registration and password-reset tokens
- audit logs that may contain user identifiers or operational details

## Recommended password writer mode

The recommended mode is the Direct LDAP password writer.

The Direct LDAP writer connects to the LLDAP LDAP endpoint and uses the LDAP password modify extended operation. This is the preferred path for normal deployments.

The legacy password bridge is still included only as a fallback for older or special deployments. If used, it must be treated as a sensitive internal service.

## Pending, blacklist, and login restrictions

Enhanced Registration creates users in LLDAP before final approval. Production deployments must ensure that pending users cannot log in to Nextcloud.

Configure the Nextcloud LDAP login filter so that only approved groups can authenticate.

Pending and blacklisted users must not be login-capable.

Recommended rule:

- the pending group is not allowed to log in
- the blacklist group is not allowed to log in
- only explicitly approved target groups are allowed to log in

The app-side pending workflow is not a replacement for a correct Nextcloud LDAP login filter.

## Legacy password bridge recommendations

The legacy bridge is optional and should only be used when Direct LDAP password writing is not possible.

If the bridge is enabled:

- keep it on a trusted internal network
- do not expose it directly to the public internet
- bind it to localhost or a private interface where possible
- use HTTPS or another protected transport if it crosses hosts
- use a strong shared secret
- rotate the secret if it may have leaked
- firewall the endpoint to trusted systems only
- monitor bridge logs for failed or unexpected requests

## Production recommendations

- Use HTTPS for all public Nextcloud access.
- Keep Nextcloud, PHP, LLDAP, the PHP LDAP extension, and this app updated.
- Use Direct LDAP password writer where possible.
- Restrict LLDAP admin credentials to the minimum required scope where possible.
- Store sensitive values only in trusted server-side configuration.
- Review audit logs regularly.
- Test upgrades in a staging instance before production deployment.
- Keep reliable backups of Nextcloud, its database, and LLDAP data.
- Verify mail delivery before relying on registration or password-reset flows.
- Configure rate limits appropriately for your environment.
- Configure domain allow/deny lists if registration should be limited to specific email domains.

## Pre-release and signing status

Enhanced Registration is currently distributed as a GitHub custom app release. It is not yet packaged or signed for the Nextcloud App Store.

Administrators should treat it as a custom app, review the code, verify the source, and test it in a staging environment before production use.

Unsigned custom apps may trigger warnings in Nextcloud. This does not automatically mean the app is unsafe, but it means administrators are responsible for reviewing and trusting the deployed code.

## License and forks

This project is licensed under AGPL-3.0-or-later.

Forks, private modifications, and continued independent development are allowed within the terms of the license.
