# Production Deployment Checklist

**Phase:** 29 — Deployment | **Step:** STEP_29.1

Complete every mandatory item before the first production deployment. Items
marked **OPEN** are not yet satisfied and block production, not staging.

## 1. Code and verification

- [x] Full regression suite passes
- [x] Static analysis passes (`./vendor/bin/phpstan analyse`)
- [x] CI runs tests, static analysis, dependency audit, and the frontend build
- [x] Smoke test verifies a running deployment, not just the test suite
- [ ] **OPEN** — Load test at expected peak concurrency
- [ ] **OPEN** — PHPStan baseline burned down; the baseline currently suppresses
      439 pre-existing findings and must shrink, not grow

## 2. Configuration

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated and stored in the secret manager
- [ ] `APP_URL` matches the public hostname
- [ ] `LOG_LEVEL=warning` or stricter
- [ ] Configuration, route, and event caches applied during deployment
- [ ] No `.env` file committed; verified with `git ls-files | grep -c '\.env$'`

## 3. Secrets

- [ ] All secrets sourced from a secret manager, never from the repository
- [ ] Midtrans **production** keys configured and `MIDTRANS_IS_PRODUCTION=true`
- [ ] Database and object storage credentials unique to production
- [ ] Credential rotation procedure documented and owned

## 4. Database

- [ ] Migrations reviewed for destructive operations
- [ ] Every destructive migration has a tested rollback path
- [ ] Connection pooling sized for the expected worker count
- [ ] `DB_SSLMODE=require` or stricter
- [ ] Row Level Security verified on all tenant-scoped tables
- [ ] Restore drill completed against a production-sized dataset

## 5. Backup and recovery

- [x] Backup procedure documented and scripted
- [x] Restore procedure documented and scripted
- [x] Backup archives verified at creation time
- [ ] **OPEN** — Continuous WAL archiving or managed point-in-time recovery.
      The 1-hour production RPO is **not** achievable with periodic dumps alone
- [ ] Off-site replication to a separate failure domain
- [ ] Quarterly restore drill scheduled with a named owner

## 6. Security

- [x] Security headers applied globally, including on non-API routes
- [x] HSTS emitted only over HTTPS and configurable per environment
- [x] Unauthenticated requests return 401 JSON and are never redirected
- [ ] TLS terminated in front of the application; HTTP redirects to HTTPS
- [ ] `SECURITY_HSTS_ENABLED=true` once TLS is confirmed stable
- [ ] CORS `allowed_origins` restricted to known frontend hosts, not `*`
- [ ] Rate limiting configured on authentication endpoints
- [ ] Dependency audit clean (`composer audit`)
- [ ] Penetration test or third-party security review completed

## 7. Operations

- [ ] Queue worker supervised and restarts on failure
- [ ] Scheduler supervised and verified to fire subscription lifecycle jobs
- [ ] Log aggregation configured and retained per policy
- [ ] Health check wired into the load balancer or orchestrator
- [ ] Alerting on error rate, queue depth, and failed jobs
- [ ] On-call rotation and escalation path defined

## 8. Compliance

- [ ] Patient data retention policy implemented and enforced
- [ ] Audit log retention meets the regulatory requirement
- [ ] Data processing agreements in place with every third-party processor
- [ ] Access to production data restricted and logged

## Sign-off

Production deployment requires named sign-off from engineering and from the
clinical data owner. Record the release SHA, the date, and both approvers.

| Field | Value |
|---|---|
| Release SHA | |
| Date | |
| Engineering approver | |
| Clinical data owner | |
