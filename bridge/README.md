# Enhanced Registration Password Bridge

This bridge is a small HTTP service used by the Enhanced Registration Nextcloud app to set or change LLDAP passwords.

The Nextcloud app handles registration, approval, mail verification, and password reset flows. The bridge performs the password change against LDAP/LLDAP by calling `ldappasswd`.

## API

Health check:

    GET /health

Expected response:

    ok

Set password:

    POST /
    Content-Type: application/json

Payload:

    {
      "secret": "shared-secret",
      "username": "user-id",
      "password": "new-password"
    }

Expected success response:

    ok

The bridge also accepts:

    POST /set-password

for clearer reverse-proxy routing. The current app uses `POST /`.

## Environment variables

Copy `.env.example` to `.env` and adjust the values:

    BRIDGE_SECRET=change-this-to-a-long-random-secret
    LDAP_URL=ldap://lldap:3890
    LDAP_BIND_DN=uid=admin,ou=people,dc=example,dc=com
    LDAP_BIND_PASSWORD=change-this-password
    LDAP_USER_BASE_DN=ou=people,dc=example,dc=com
    BRIDGE_PORT=18080

## Docker Compose example

    cp .env.example .env
    docker compose -f docker-compose.example.yml up -d --build

In the Enhanced Registration app settings, set:

    Bridge URL: http://your-bridge-host:18081
    Bridge Secret: same value as BRIDGE_SECRET

## Security notes

- Do not expose this bridge publicly unless you add additional protection.
- Prefer running it on a trusted internal network reachable only by Nextcloud.
- Use a long random `BRIDGE_SECRET`.
- Use a restricted LDAP bind user if possible.
- Treat `LDAP_BIND_PASSWORD` and `BRIDGE_SECRET` as sensitive.
- Use HTTPS or a trusted private network between Nextcloud and the bridge.
