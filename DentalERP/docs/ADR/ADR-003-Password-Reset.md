# ADR-003

## Title

Password Reset Strategy

## Status

Accepted

## Context

Password reset should follow Laravel's supported password-broker behavior and avoid unnecessary schema customization.

## Decision

Use Laravel's default `password_reset_tokens` table without schema customization.

Password Reset Token TTL:

- 15 minutes.

The password broker configuration is the source of truth for expiry. Tokens remain hashed at rest and are deleted/consumed through Laravel's standard password reset lifecycle.

## Reasons

- Maximum compatibility with Laravel 12 password brokers.
- Less custom persistence code and lower maintenance risk.
- Security behavior follows a framework-supported implementation.

## Consequences

- The table contains Laravel's default columns only: `email`, `token`, and `created_at`.
- Custom `expires_at` and `used_at` columns are not created.
- Session revocation after a successful password reset remains an Authentication Service responsibility.

## Repository Status Note

The current repository skeleton does not yet contain Laravel's default `password_reset_tokens` migration.

When the Laravel 12 application skeleton or framework migrations are installed/published:

- Use the standard Laravel `password_reset_tokens` migration unchanged.
- Do not create an Authentication-domain replacement migration.
- Do not add custom persistence columns.
- Configure the 15-minute expiry through the password broker in `config/auth.php`.

Until that framework migration is present, Stage 06 runtime migration verification remains blocked; the absence must not be solved by introducing a custom schema.
