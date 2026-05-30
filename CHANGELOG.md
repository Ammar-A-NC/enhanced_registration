# Changelog

## 0.1.1

Security and stability hardening release.

- Validate the registration token again during final account details submission.
- Use the verified registration email from the database instead of trusting submitted form data.
- Add the missing password reset database migration.
- Replace short password reset codes with long random reset tokens stored as SHA-256 hashes.
- Add rate limiting for password reset verification and password update attempts.
- Fix password reset resend flow by using the existing email lookup method.
- Bump app version to 0.1.1.

## 0.1.0

Initial public development version.

### Added

- Registration flow with email confirmation code
- LLDAP user creation
- Pending approval workflow
- Admin approval and rejection actions
- Assignable LLDAP group selection
- Configurable default approval groups
- Password reset flow
- Password bridge integration for setting and changing LLDAP passwords
- Configurable mail templates
- Configurable password policy
- Have I Been Pwned password check
- Email domain allow/deny lists
- Rate limiting
- Audit log with retention settings
- Admin setup status and test actions
- Language mode: auto, German, English
