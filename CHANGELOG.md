# Changelog

## 0.2.3

Security and release-polish development release.

- Require at least one assignable approval group before approving a pending user.
- Keep users in the pending group if approval is attempted without a valid target group.
- Add an admin error message for missing approval target groups.
- Prepare documentation cleanup for README, SECURITY notes, and release hygiene.
- Keep the AGPL license text unchanged and document licensing clearly in README instead.

- Add optional public/local-only/disabled access modes for registration and password reset.

## 0.2.2

Hardening and polish release.

- Bump app metadata and documentation to 0.2.2.
- Harden approval group assignment with server-side filtering.
- Add target groups before removing users from the pending group during approval.
- Keep users in the pending group more safely if target-group assignment fails.
- Improve login-link JavaScript by using Nextcloud URL generation.
- Correct password-rule wording for uppercase letters and password confirmation.
- Add GitHub Actions CI workflow.
- Add local smoke-check script.
- Update README release notes.

## 0.2.1

Hardening hotfix.

- Harden `/resend-code` with email validation, domain checks, and IP/email rate limits.
- Prevent resend-code from creating new registrations.
- Use Nextcloud request remote address for rate-limit identity where available.
- Catch Direct LDAP password-change errors during password reset and show a user-friendly message.
- Add timeout handling to the HaveIBeenPwned password check.
- Remove duplicate password-reset route attributes if present.
- Improve login-link JavaScript to avoid continuous polling.
- Harden legacy bridge example documentation and localhost binding.
- Improve admin Users & Permissions layout with compact expandable rows.
- Bump app version to 0.2.1.

## 0.2.0

Direct LDAP password writer pre-release.

- Add Direct LDAP password writer using PHP LDAP and ldap_exop_passwd.
- Store password-reset manual codes as SHA-256 hashes and use 8-digit one-time codes.
- Keep the password bridge as legacy-only fallback.
- Add admin settings for LDAP URL, Base DN, Admin DN, User-DN template, and password writer mode.
- Show all LLDAP users in the admin UI, including users without group memberships.
- Add full LLDAP user deletion from the admin UI with protected-user safeguards.
- Add admin UI warnings for Pending group and Nextcloud LDAP login-filter configuration.
- Update README and app metadata toward an App-Store-friendly project description.
- Bump app version to 0.2.0.

## 0.1.4

Usability pre-release.

- Integrate login-page registration and password-reset links into the main app.
- Remove the need for a separate login-link companion app.
- Bump app version to 0.1.4.

## 0.1.3

Security and hardening pre-release.

- Add public route attributes to the registration start and duplicate-registration pages.
- Add IP-based rate limiting in addition to email-based rate limiting for registration and password reset requests.
- Make initial password reset responses uniform to reduce account enumeration risk.
- Catch and log initial password reset mail delivery errors.
- Handle registration confirmation mail delivery errors without redirecting to the duplicate-registration page.
- Harden LLDAP HTTP handling with curl timeouts, HTTP status checks, JSON validation, and warning logs.
- Clean up partially created LLDAP users if password setup or pending-group assignment fails.
- Harden password bridge calls with curl timeouts, HTTP status checks, and warning logs.
- Bump app version to 0.1.3.

## 0.1.2

Security and hardening release.

- Replace registration confirmation storage with hashed long link tokens and hashed 8-digit manual codes.
- Add rate limiting for registration verification and final details submission.
- Add modern Nextcloud route attributes for public registration and password reset pages.
- Catch mail delivery errors during the initial password reset request.
- Limit declared Nextcloud compatibility to the tested major version.
- Add documentation notes for pending-user login restrictions, app signing status, and bridge hardening.
- Store rate-limit counters in a dedicated database table instead of app config values.
- Harden the example bridge LDAP DN handling.
- Bump app version to 0.1.2.

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
