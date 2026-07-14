# Nexus API - Quick Reference Guide

**Base URL**: `/api/v1`  
**Authentication**: Bearer Token (Sanctum)  
**Response Format**: JSON

---

## Table of Quick Links

| Hub | Method | Endpoint | Auth | Rate Limit |
|-----|--------|----------|------|-----------|
| **Authentication** | POST | /login | ✗ | — |
| **Authentication** | POST | /register | ✗ | — |
| **Authentication** | POST | /logout | ✓ | — |
| **Authentication** | POST | /refresh-token | ✓ | — |
| | | | | |
| **Contacts** | GET | /contacts | ✓ | — |
| **Contacts** | POST | /contacts | ✓ | — |
| **Contacts** | GET | /contacts/{id} | ✓ | — |
| **Contacts** | PUT | /contacts/{id} | ✓ | — |
| **Contacts** | DELETE | /contacts/{id} | ✓ | — |
| **Contacts** | GET | /contacts/stats | ✓ | — |
| **Contacts** | POST | /contacts/import | ✓ | 5/min |
| **Contacts** | POST | /contacts/{id}/import/whatsapp | ✓ | 5/min |
| **Contacts** | GET | /contacts/{id}/intelligence | ✓ | — |
| **Contacts** | POST | /contacts/{id}/analysis-runs | ✓ | analysis |
| **Contacts** | GET | /contacts/{id}/messages | ✓ | — |
| **Contacts** | POST | /contacts/{id}/merge | ✓ | — |
| **Contacts** | POST | /contacts/{id}/erase | ✓ | — |
| **Contacts** | POST | /contacts/{id}/enrich | ✓ | — |
| | | | | |
| **Notifications** | GET | /notifications/templates | ✓ | — |
| **Notifications** | POST | /notifications/templates | ✓ | — |
| **Notifications** | POST | /notifications/send | ✓ | — |
| **Notifications** | GET | /notifications | ✓ | — |
| **Notifications** | POST | /v1/notifications/broadcast | ✓ | — |
| **Notifications** | POST | /v1/notifications/fcm-token | ✓ | — |
| **Notifications** | GET | /v1/notifications/fcm-config | ✗ | — |
| | | | | |
| **Tasks** | GET | /tasks | ✓ | — |
| **Tasks** | POST | /tasks | ✓ | — |
| **Tasks** | GET | /tasks/stats | ✓ | — |
| **Tasks** | GET | /tasks/active | ✓ | — |
| **Tasks** | POST | /tasks/manual | ✓ | — |
| **Tasks** | POST | /tasks/agent | ✓ | — |
| **Tasks** | POST | /tasks/{id}/execute | ✓ | — |
| **Tasks** | POST | /tasks/{id}/cancel | ✓ | — |
| **Tasks** | PATCH | /tasks/{id}/status | ✓ | — |
| | | | | |
| **Workflows** | GET | /workflows | ✓ | — |
| **Workflows** | POST | /workflows | ✓ | — |
| **Workflows** | GET | /workflows/templates | ✓ | — |
| **Workflows** | POST | /workflows/{id}/execute | ✓ | — |
| **Workflows** | GET | /workflows/executions/{id} | ✓ | — |
| **Workflows** | POST | /workflows/executions/{id}/resume | ✓ | — |
| **Workflows** | POST | /workflows/executions/{id}/cancel | ✓ | — |
| | | | | |
| **Agents** | GET | /agents | ✓ | — |
| **Agents** | POST | /agents | ✓ | — |
| **Agents** | POST | /agents/{id}/run | ✓ | — |
| **Agents** | GET | /agents/{id}/status | ✓ | — |
| **Agents** | POST | /agents/{id}/quarantine | ✓ | — |
| **Agents** | POST | /agents/{id}/unquarantine | ✓ | — |
| **Agents** | GET | /agent-tools | ✓ | — |
| **Agents** | GET | /agent-personas | ✓ | — |
| **Agents** | POST | /agent-personas | ✓ | — |
| | | | | |
| **AI Models** | GET | /ai/providers | ✓ | — |
| **AI Models** | POST | /ai/providers | ✓ | — |
| **AI Models** | GET | /ai/providers/{id}/details | ✓ | — |
| **AI Models** | POST | /ai/providers/{id}/test | ✓ | — |
| **AI Models** | GET | /ai/providers/health | ✓ | — |
| **AI Models** | POST | /ai/request | ✓ | — |
| **AI Models** | GET | /ai/intents/routing | ✓ | — |
| **AI Models** | GET | /ai/cost/forecast | ✓ | — |
| **AI Models** | POST | /ai/cost/budget | ✓ | — |
| **AI Models** | GET | /ai/audit-trail | ✓ | — |
| | | | | |
| **HedraSoul** | GET | /hedrasoul/sessions | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/sessions | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/sessions/{id}/messages | ✓ | — |
| **HedraSoul** | GET | /hedrasoul/souly/status | ✓ | — |
| **HedraSoul** | PATCH | /hedrasoul/souly/autonomy | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/souly/quarantine | ✓ | — |
| **HedraSoul** | GET | /hedrasoul/instructions | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/instructions | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/instructions/{id}/activate | ✓ | — |
| **HedraSoul** | GET | /hedrasoul/memories | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/memories | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/memories/{id}/approve | ✓ | — |
| **HedraSoul** | GET | /hedrasoul/approvals | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/approvals/{id}/approve | ✓ | — |
| **HedraSoul** | GET | /hedrasoul/notifications | ✓ | — |
| **HedraSoul** | POST | /hedrasoul/analytics | ✓ | — |
| | | | | |
| **Settings** | GET | /settings | ✓ | — |
| **Settings** | GET | /settings/grouped | ✓ | — |
| **Settings** | PUT | /settings/{key} | ✓ | — |
| **Settings** | PUT | /settings/bulk | ✓ | — |
| **Settings** | POST | /settings/system/agent-pause | ✓ | admin |
| **Settings** | POST | /settings/system/maintenance-mode | ✓ | admin |
| | | | | |
| **Memory** | GET | /memories/search | ✓ | — |
| **Memory** | GET | /memories | ✓ | — |
| **Memory** | POST | /memories | ✓ | — |
| **Memory** | GET | /contacts/{id}/memories | ✓ | — |
| **Memory** | POST | /contacts/{id}/memories/extract | ✓ | — |
| | | | | |
| **Dashboard** | GET | /dashboard/stats | ✓ | 60/min |
| **Dashboard** | GET | /dashboard/health | ✓ | — |
| **Dashboard** | GET | /dashboard/activity-feed | ✓ | — |
| | | | | |
| **Monitoring** | GET | /health | ✗ | — |
| **Monitoring** | GET | /monitoring/health | ✗ | — |
| **Monitoring** | GET | /monitoring/metrics | ✗ | — |
| | | | | |
| **Webhooks** | POST | /webhooks/waha | ✗ | — |
| **Webhooks** | POST | /webhooks/workflows/{id} | ✗ | — |

---

## Common Request Headers

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
Accept: application/json
```

---

## Common Query Parameters

| Param | Type | Default | Example |
|-------|------|---------|---------|
| `page` | int | 1 | ?page=2 |
| `per_page` | int | varies | ?per_page=50 |
| `search`/`q` | string | — | ?search=jane |
| `sort` | string | — | ?sort=-created_at |
| `status` | string | — | ?status=active |
| `from_date` | date | — | ?from_date=2026-07-01 |
| `to_date` | date | — | ?to_date=2026-07-31 |

---

## Login & Authentication Flow

```bash
# 1. Login
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Response:
# {
#   "user": {...},
#   "token": "YOUR_TOKEN_HERE",
#   "expires_in": 86400
# }

# 2. Use token for subsequent requests
curl -X GET http://localhost/api/v1/contacts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 3. Refresh token (before expiry)
curl -X POST http://localhost/api/v1/refresh-token \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 4. Logout
curl -X POST http://localhost/api/v1/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Pagination Example

```bash
# Get page 2 with 50 items per page
curl -X GET 'http://localhost/api/v1/contacts?page=2&per_page=50'

# Response includes:
# {
#   "data": [...],
#   "pagination": {
#     "total": 1250,
#     "per_page": 50,
#     "current_page": 2,
#     "last_page": 25,
#     "from": 51,
#     "to": 100
#   }
# }
```

---

## Error Handling

All errors follow this format:

```json
{
  "error": "error_code",
  "message": "Human readable message",
  "details": {},
  "timestamp": "2026-07-12T10:30:00Z",
  "request_id": "req_123"
}
```

**Common HTTP Status Codes**:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `429` - Rate Limited
- `500` - Server Error

---

## Key Workflows

### Create & Execute Task
```bash
# 1. Create task
curl -X POST http://localhost/api/v1/tasks \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Send emails",
    "description": "Send promotional emails",
    "priority": "high",
    "type": "manual"
  }'

# 2. Execute task (get ID from response)
curl -X POST http://localhost/api/v1/tasks/123/execute \
  -H "Authorization: Bearer TOKEN"

# 3. Check status
curl -X GET http://localhost/api/v1/tasks/123 \
  -H "Authorization: Bearer TOKEN"
```

### Import Contacts
```bash
# 1. Preview import
curl -X POST http://localhost/api/v1/contacts/import/preview \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "file": "base64_file_content",
    "format": "csv",
    "sample_rows": 5
  }'

# 2. Import (if preview looks good)
curl -X POST http://localhost/api/v1/contacts/import \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "file": "base64_file_content",
    "format": "csv"
  }'

# 3. Track import batch
curl -X GET http://localhost/api/v1/contacts/imports/batch_123 \
  -H "Authorization: Bearer TOKEN"
```

### Send Notification
```bash
# 1. Create template (if needed)
curl -X POST http://localhost/api/v1/notifications/templates \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Welcome",
    "type": "whatsapp",
    "content": "Welcome {{contact_name}}!"
  }'

# 2. Send to contacts
curl -X POST http://localhost/api/v1/notifications/send \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "template_id": 1,
    "recipients": [1, 2, 3],
    "channel": "whatsapp",
    "variables": {
      "contact_name": "Jane"
    }
  }'
```

### AI Request with Routing
```bash
curl -X POST http://localhost/api/v1/ai/request \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "intent": "customer_support",
    "prompt": "Help resolve billing issue",
    "context": {
      "contact_id": 5
    },
    "temperature": 0.7,
    "max_tokens": 500
  }'
```

### Contact Intelligence
```bash
# Get AI insights about a contact
curl -X GET http://localhost/api/v1/contacts/5/intelligence \
  -H "Authorization: Bearer TOKEN"

# Response includes:
# {
#   "persona": {...},
#   "interests": [...],
#   "engagement_level": "high",
#   "lifetime_value": 5000,
#   "churn_risk": "low"
# }
```

### Workflow Execution
```bash
# 1. Get available templates
curl -X GET http://localhost/api/v1/workflows/templates \
  -H "Authorization: Bearer TOKEN"

# 2. Create from template
curl -X POST http://localhost/api/v1/workflows \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Customer Onboarding",
    "trigger": {"type": "contact_created"},
    "steps": [...]
  }'

# 3. Execute workflow
curl -X POST http://localhost/api/v1/workflows/123/execute \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "trigger_data": {"contact_id": 5}
  }'

# 4. Check execution status
curl -X GET http://localhost/api/v1/workflows/executions/exec_123 \
  -H "Authorization: Bearer TOKEN"
```

### HedraSoul Session
```bash
# 1. Create session
curl -X POST http://localhost/api/v1/hedrasoul/sessions \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Support Session",
    "contact_id": 5,
    "model": "gpt-4",
    "autonomy_level": "guided"
  }'

# 2. Send message
curl -X POST http://localhost/api/v1/hedrasoul/sessions/1/messages \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "What can you help me with?",
    "role": "user"
  }'

# 3. Get messages
curl -X GET http://localhost/api/v1/hedrasoul/sessions/1/messages \
  -H "Authorization: Bearer TOKEN"
```

---

## Dashboard Stats Example

```bash
curl -X GET http://localhost/api/v1/dashboard/stats \
  -H "Authorization: Bearer TOKEN"

# Response:
# {
#   "contacts": {
#     "total": 1250,
#     "active": 980,
#     "new_today": 25
#   },
#   "messages": {
#     "total": 125000,
#     "today": 450,
#     "pending": 12
#   },
#   "agents": {
#     "active": 8,
#     "healthy": 7,
#     "queued_tasks": 45
#   },
#   "ai": {
#     "requests_today": 5000,
#     "cost_today": 150.50,
#     "budget_used_percent": 35
#   }
# }
```

---

## Emergency Admin Endpoints (Super-admin only)

```bash
# Pause all agents (emergency)
curl -X POST http://localhost/api/v1/settings/system/agent-pause \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "pause": true,
    "reason": "Critical security issue"
  }'

# Enable maintenance mode
curl -X POST http://localhost/api/v1/settings/system/maintenance-mode \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "enabled": true,
    "message": "System maintenance in progress"
  }'
```

---

## Rate Limiting Notes

- Standard endpoints: Laravel default (typically 60/minute)
- Contact import: 5 requests/minute
- Analysis runs: Custom "analysis" throttle
- Dashboard stats: 60 requests/minute
- Most real-time endpoints: No limit

**When rate limited**:
- Status code: `429`
- Retry-After header included
- Wait and retry after specified duration

---

## WebSocket Real-time (Reverb/Echo)

Broadcasting endpoints for real-time updates:
- `POST /broadcasting/auth` - Authenticate for channels
- `POST /v1/notifications/broadcast` - Broadcast to channels
- `POST /v1/notifications/broadcast-batch` - Batch broadcast

Supports private/presence channels:
- `private-user-{id}` - User-specific updates
- `presence-team-{id}` - Presence awareness in teams

---

## Documentation Files

- **Full API Analysis**: `API_ENDPOINTS_ANALYSIS.md`
- **OpenAPI Spec**: `openapi.yaml` (for Swagger UI, Postman, SDK tools)
- **This Quick Ref**: `API_QUICK_REFERENCE.md`

---

**Last Updated**: 2026-07-12  
**API Version**: v1  
**Total Endpoints**: 150+
