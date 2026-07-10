# AI Models Hub - Providers Management E2E Test Suite

## Overview
The Providers Management component uses an opaque-box, requirement-driven testing strategy powered by Laravel Feature Testing. The test suite comprehensively validates the exact HTTP contracts without asserting against internal database state.

## 4 Tiers of Coverage
1. **Tier 1 (Happy Paths)**: Core validations for adding providers, managing API keys, pinging, and syncing models.
2. **Tier 2 (Boundary & Edge Cases)**: Tests for validation limits, duplicate errors, rate limiting, connection timeouts, and massive payloads.
3. **Tier 3 (Cross-Feature Combinations)**: 7 tests validating pairwise interactions (e.g., adding a key then pinging, deleting a key then syncing).
4. **Tier 4 (Real-World Application Scenarios)**: 5 end-to-end user workflows:
    - **Scenario 1**: Onboarding Flow (Full Happy Path Setup)
    - **Scenario 2**: Expired Key Flow (Key Rotation & Recovery)
    - **Scenario 3**: Overeager Admin Flow (Missing Prerequisites)
    - **Scenario 4**: Multi-Tenant / Isolation Flow
    - **Scenario 5**: External Outage Flow (Rate Limit / Sync Recovery)

## Running the E2E Test Suite
To run the full suite for the Providers Management, execute the following artisan command:

```bash
php artisan test --testsuite=Feature --filter=ProvidersE2ETest
```

Or simply run the file directly:
```bash
php artisan test tests/Feature/E2E/ProvidersE2ETest.php
```
