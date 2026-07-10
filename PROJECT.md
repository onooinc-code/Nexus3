# Project: AI Models Hub - Providers Integration

## Architecture
- Backend routes under `/hub/models/providers/` in `web.php` mapped to `AiHubController`.
- Logic should ideally be placed in `AiHubService` (or similar service layer) which interacts with external AI providers.
- Frontend views (`add-edit.blade.php`, `provider-cards.blade.php` etc.) submit data via AJAX to the newly created and existing routes.

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | Backend Implementation | Add `/ping` and `/{id}/sync` routes, implement `AiHubController` methods and `AiHubService` logic. | none | PLANNED |
| 2 | Frontend Implementation | Update Add Provider drawer to submit to `storeProvider`. Update Ping and Sync buttons to hit the new APIs and show notifications. | M1 | PLANNED |

## Interface Contracts
### Frontend ↔ Backend
- `POST /hub/models/providers/ping`
  - Input: `base_url`, `api_key`, `auth_header_format`
  - Output: `{ success: boolean, message: string, latency: int }`
- `POST /hub/models/providers/{id}/sync`
  - Output: `{ success: boolean, message: string, synced_count: int }`

## Code Layout
- Controllers: `app/Http/Controllers/Web/AiHubController.php`
- Services: `app/Services/AiHubService.php`
- Routes: `routes/web.php`
- Views: `resources/views/hubs/partials/ai-hub/providers/drawers/add-edit.blade.php` and provider cards view.
