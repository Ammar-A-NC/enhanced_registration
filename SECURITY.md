# Security Policy

## Reporting a vulnerability

Please report security issues privately instead of opening a public issue.

If no dedicated security contact is configured for this repository yet, open a minimal issue asking for a private contact method without disclosing technical details.

## Response expectations

This project is maintained on a best-effort basis in the maintainer’s spare time. Response times and fix timelines are not guaranteed. Critical security reports will be prioritized where possible, but some issues may take time to investigate or resolve.

If you use this app in production, please assess the risk for your own environment and apply temporary mitigations where appropriate.

## Scope

This project handles registration, password reset, LLDAP user creation, and password changes through a bridge service. Treat configuration values such as LLDAP admin credentials and bridge secrets as sensitive.

## Recommendations

- Run the password bridge only in a trusted internal network.
- Use HTTPS for public Nextcloud access.
- Use a strong bridge secret.
- Restrict access to the bridge endpoint.
- Review audit logs regularly.
- Keep Nextcloud, PHP, LLDAP, and this app updated.
