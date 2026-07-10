# E2E Test Infra: AI Models Hub Providers

## Test Philosophy
- Opaque-box, requirement-driven. No dependency on implementation design.
- Methodology: Category-Partition + BVA + Pairwise + Workload Testing.
- Framework: Laravel Feature Testing (HTTP Json endpoints)

## Feature Inventory
| # | Feature | Source (requirement) | Tier 1 | Tier 2 | Tier 3 |
|---|---------|---------------------|:------:|:------:|:------:|
| 1 | Add Provider | ORIGINAL_REQUEST R3.B | 5      | 5      | ✓      |
| 2 | Ping Provider | ORIGINAL_REQUEST R3.A | 5      | 5      | ✓      |
| 3 | Sync Models | ORIGINAL_REQUEST R3.A | 5      | 5      | ✓      |
| 4 | Manage API Keys | Updated Requirements | 5      | 5      | ✓      |

## Test Architecture
- Test runner: `php artisan test --testsuite=Feature`
- Test case format: Laravel `$this->postJson()`, `$this->getJson()`
- Directory layout: `tests/Feature/E2E/ProvidersE2ETest.php`

## Real-World Application Scenarios (Tier 4)
| # | Scenario | Features Exercised | Complexity |
|---|----------|--------------------|------------|
| 1 | Full Provider Setup & Test | Add Provider, API Keys, Ping, Sync Models | High       |
| 2 | Invalid Provider Rejection | Add Provider (Invalid URL), Ping | Medium     |
| 3 | Multi-Key Rotation | Add 3 API keys, test default switching | Medium     |

## Coverage Thresholds
- Tier 1: ≥5 per feature
- Tier 2: ≥5 per feature (where boundaries exist)
- Tier 3: pairwise coverage of major feature interactions
- Tier 4: ≥5 realistic application scenarios
