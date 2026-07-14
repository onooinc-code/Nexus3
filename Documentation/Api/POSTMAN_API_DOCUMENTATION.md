# Nexus Platform API Documentation

## Overview

The Nexus Platform provides a comprehensive REST API for managing contacts, notifications, workflows, AI models, and more. This collection contains all endpoints organized by functional area with examples and descriptions.

**Base URL:** `https://n.soulyeg.online/api/v1`

## Getting Started

### 1. Authentication

The API uses Laravel Sanctum for authentication. To get started:

1. Call the **Login** endpoint with your credentials:
   ```json
   {
     "email": "user@example.com",
     "password": "password123"
   }
   ```

2. You'll receive a Bearer token in the response

3. Add the token to Postman:
   - Open the collection settings
   - Go to Variables tab
   - Set `bearer_token` to your token
   - Or add it to individual requests' Authorization header

### 2. Base URL Setup

In Postman, the base URL is already configured as a variable: `{{base_url}}`

Current value: `https://n.soulyeg.online`

## API Endpoints by Category

### 🔐 Authentication (Public Routes)

These endpoints don't require authentication:

- `GET /health` - Check API health
- `POST /broadcasting/auth` - Authenticate for real-time broadcasting
- `POST /register` - Create new account
- `POST /login` - Login with credentials
- `POST /verify-token` - Verify token validity (requires auth)
- `POST /logout` - Logout (requires auth)
- `POST /refresh-token` - Refresh auth token (requires auth)

### 👥 Contacts Hub

Complete contact management system:

**Basic Operations:**
- `GET /contacts` - List all contacts
- `GET /contacts/{id}` - Get specific contact
- `POST /contacts` - Create contact
- `PUT /contacts/{id}` - Update contact
- `DELETE /contacts/{id}` - Delete contact

**Contact Intelligence:**
- `GET /contacts/{id}/intelligence` - AI-generated insights
- `GET /contacts/{id}/memory` - Contact memories
- `GET /contacts/{id}/persona` - Contact personality analysis
- `GET /contacts/{id}/emotional-baseline` - Emotional patterns
- `GET /contacts/{id}/topics` - Topics discussed with contact
- `GET /contacts/{id}/analytics` - Contact analytics
- `GET /contacts/{id}/timeline` - Activity timeline

**Messages & Communication:**
- `GET /contacts/{id}/messages` - All messages
- `GET /contacts/{id}/messages/whatsapp` - WhatsApp messages
- `GET /contacts/{id}/messages/facebook` - Facebook messages
- `GET /contacts/{id}/threads` - Message threads

**Advanced Features:**
- `POST /contacts/{id}/analysis-runs` - Start contact analysis
- `GET /contacts/{id}/analysis-runs` - Get analysis results
- `POST /contacts/{id}/memory-maintenance` - Maintain contact memory
- `POST /contacts/{id}/merge` - Merge duplicate contacts
- `POST /contacts/{id}/enrich` - Enrich contact data
- `POST /contacts/{id}/export` - Export contact bundle
- `POST /contacts/{id}/erase` - Erase all contact data
- `GET /contacts/{id}/audit` - View audit trail

**Identifiers, Notes & Relationships:**
- `GET /contacts/{id}/identifiers` - Email, phone, social identifiers
- `POST /contacts/{id}/identifiers` - Add identifier
- `GET /contacts/{id}/notes` - All notes
- `POST /contacts/{id}/notes` - Add note
- `GET /contacts/{id}/relationships` - Related contacts
- `GET /contacts/{id}/preferences` - Contact preferences

**Import/Export:**
- `POST /contacts/import` - Bulk import contacts
- `POST /contacts/import/whatsapp` - Import from WhatsApp
- `POST /contacts/import/facebook` - Import from Facebook
- `GET /contacts/export` - Export all contacts

**Reply Mode (Auto-response settings):**
- `GET /contacts/reply-mode` - Get global reply mode
- `PATCH /contacts/reply-mode` - Set global reply mode
- `GET /contacts/{id}/reply-mode` - Get contact reply mode
- `PATCH /contacts/{id}/reply-mode` - Set contact reply mode

### 🔔 Notifications Hub

Notification template and delivery management:

**Templates:**
- `GET /notifications/templates` - List templates
- `POST /notifications/templates` - Create template
- `GET /notifications/templates/{id}` - Get template
- `PUT /notifications/templates/{id}` - Update template
- `DELETE /notifications/templates/{id}` - Delete template

**Sending & Broadcasting:**
- `POST /notifications/send` - Send single notification
- `GET /notifications` - View notification logs
- `POST /notifications/{id}/retry` - Retry failed notification
- `POST /notifications/broadcast` - Broadcast real-time notification
- `POST /notifications/broadcast-batch` - Broadcast to multiple users

**Firebase Cloud Messaging:**
- `POST /notifications/fcm-token` - Register FCM device token
- `GET /notifications/fcm-config` - Get FCM configuration

### 💬 Conversations

Multi-channel conversation management:

- `GET /conversations` - List conversations
- `POST /conversations` - Create conversation
- `GET /conversations/{id}` - Get conversation
- `PUT /conversations/{id}` - Update conversation
- `DELETE /conversations/{id}` - Delete conversation
- `GET /conversations/{id}/messages` - Get conversation messages
- `POST /conversations/{id}/send-message` - Send message

### 🤖 Agents Hub

AI agent management and execution:

**CRUD Operations:**
- `GET /agents` - List agents
- `POST /agents` - Create agent
- `GET /agents/{id}` - Get agent
- `PUT /agents/{id}` - Update agent
- `DELETE /agents/{id}` - Delete agent

**Execution & Control:**
- `POST /agents/{id}/run` - Execute agent
- `POST /agents/{id}/simulate` - Simulate without executing
- `GET /agents/{id}/status` - Get agent status
- `GET /agents/{id}/logs` - Get execution logs
- `POST /agents/{id}/quarantine` - Quarantine agent
- `POST /agents/{id}/unquarantine` - Release from quarantine

**Agent Personas & Tools:**
- `GET /agent-personas` - List personas
- `GET /agent-tools` - Available tools library
- `GET /mcp-servers` - MCP server list
- `POST /mcp-servers/{name}/connect` - Connect MCP server
- `POST /mcp-servers/{name}/disconnect` - Disconnect MCP server

### ⚙️ Workflows

Workflow automation and orchestration:

- `GET /workflows` - List workflows
- `POST /workflows` - Create workflow
- `GET /workflows/{id}` - Get workflow
- `PUT /workflows/{id}` - Update workflow
- `DELETE /workflows/{id}` - Delete workflow
- `POST /workflows/{id}/execute` - Execute workflow
- `GET /workflows/{id}/progress` - Get execution progress
- `GET /workflows/templates` - Available templates

### 📋 Tasks

Task management and tracking:

**CRUD Operations:**
- `GET /tasks` - List tasks
- `POST /tasks` - Create task
- `GET /tasks/{id}` - Get task
- `PUT /tasks/{id}` - Update task
- `DELETE /tasks/{id}` - Delete task

**Task Management:**
- `POST /tasks/{id}/execute` - Execute task
- `POST /tasks/{id}/cancel` - Cancel task
- `POST /tasks/{id}/pause` - Pause task
- `POST /tasks/{id}/resume` - Resume task
- `GET /tasks/{id}/logs` - Get task logs

**Statistics & Filtering:**
- `GET /tasks/stats` - Overall stats
- `GET /tasks/active` - Active tasks only
- `GET /tasks/queue-stats` - Queue statistics
- `GET /tasks/routing-stats` - Routing statistics
- `GET /tasks/type/{type}` - Tasks by type

### 🧠 Memory Hub

Memory storage and retrieval:

- `GET /memories` - List memories
- `POST /memories` - Create memory
- `GET /memories/{id}` - Get memory
- `PUT /memories/{id}` - Update memory
- `DELETE /memories/{id}` - Delete memory
- `GET /memories/search` - Search memories
- `GET /memories/{id}/versions` - View memory versions
- `GET /contacts/{id}/memories` - Contact memories

### 👻 HedraSoul

AI soul/personality system:

**Sessions:**
- `GET /hedrasoul/sessions` - List sessions
- `POST /hedrasoul/sessions` - Create session
- `GET /hedrasoul/sessions/{id}` - Get session
- `PATCH /hedrasoul/sessions/{id}` - Update session
- `GET /hedrasoul/sessions/{id}/messages` - Get messages
- `POST /hedrasoul/sessions/{id}/messages` - Send message

**Souly Control:**
- `GET /hedrasoul/souly/status` - AI status
- `PATCH /hedrasoul/souly/autonomy` - Adjust autonomy
- `PATCH /hedrasoul/souly/model` - Change model
- `POST /hedrasoul/souly/quarantine` - Quarantine AI
- `POST /hedrasoul/souly/resume` - Resume AI

**Profile & Instructions:**
- `GET /hedrasoul/profile` - Get profile
- `PATCH /hedrasoul/profile` - Update profile
- `GET /hedrasoul/instructions` - List instructions
- `POST /hedrasoul/instructions` - Create instruction version
- `POST /hedrasoul/instructions/{version}/activate` - Activate version

**Approvals & Memory:**
- `GET /hedrasoul/approvals` - Pending approvals
- `POST /hedrasoul/approvals/{id}/approve` - Approve item
- `GET /hedrasoul/memories` - Soul memories
- `POST /hedrasoul/memory-maintenance` - Maintain memory

### 🧪 AI Models Hub

AI provider and model management:

**Providers:**
- `GET /ai/providers` - List providers
- `POST /ai/providers` - Add provider
- `GET /ai/providers/{id}` - Get provider details
- `PUT /ai/providers/{id}` - Update provider
- `DELETE /ai/providers/{id}` - Delete provider
- `POST /ai/providers/{id}/test` - Test connection
- `PATCH /ai/providers/{id}/toggle-active` - Enable/disable

**Models & Routing:**
- `GET /ai-models` - List available models
- `POST /ai/request` - Route AI request
- `GET /ai/intents/routing` - View routing matrix
- `PUT /ai/intents/routing` - Update routing

**Monitoring:**
- `GET /ai/providers/health` - Provider health status
- `GET /ai/providers/{id}/usage-stats` - Usage analytics
- `GET /ai/cost/forecast` - Cost forecasting
- `POST /ai/cost/budget` - Set budget limits

**API Keys:**
- `GET /ai/providers/{id}/keys` - List API keys
- `POST /ai/providers/{id}/keys` - Add key
- `DELETE /ai/api-keys/{keyId}` - Delete key
- `POST /ai/api-keys/{keyId}/set-default` - Set default key

### ⚙️ Settings

Application configuration:

**Basic Settings:**
- `GET /settings` - List all settings
- `GET /settings/grouped` - Grouped by category
- `GET /settings/public` - Public settings only
- `POST /settings` - Create setting
- `PUT /settings/{key}` - Update setting
- `DELETE /settings/{key}` - Delete setting
- `PUT /settings/bulk` - Bulk update

**Admin Functions:**
- `GET /settings/admin/dashboard` - Admin dashboard
- `GET /settings/admin/audit-trail` - Audit log
- `POST /settings/admin/export` - Export settings
- `POST /settings/admin/import` - Import settings
- `POST /settings/factory-reset` - Reset to defaults

**System Control:**
- `POST /settings/system/agent-pause` - Emergency pause agents
- `POST /settings/system/maintenance-mode` - Enable maintenance
- `GET /settings/system/telemetry` - System telemetry

**Integration Testing:**
- `POST /settings/credentials/validate` - Validate credentials
- `GET /settings/waha/webhook-url` - Get WAHA webhook URL
- `POST /settings/waha/test-connection` - Test WAHA
- `POST /settings/waha/test-webhook` - Test webhook

### 📊 Monitoring

System health and metrics:

- `GET /monitoring/health` - Overall health
- `GET /monitoring/health/reverb` - WebSocket health
- `GET /monitoring/health/queue` - Queue health
- `GET /monitoring/metrics` - System metrics
- `GET /monitoring/metrics/websocket` - WebSocket metrics
- `GET /dashboard/stats` - Dashboard stats
- `GET /dashboard/health` - Dashboard health

### 📝 Logs

Logging and error tracking:

- `GET /logs` - List logs
- `GET /logs/{id}` - Get log entry
- `GET /logs/stats` - Log statistics
- `GET /logs/levels` - Available levels
- `GET /logs/channels` - Log channels
- `GET /logs/errors` - Error logs only
- `POST /logs/clear` - Clear all logs (admin)
- `DELETE /logs/{id}` - Delete log entry

### 👤 Profile

User profile management:

- `GET /profile` - Get current user profile
- `PUT /profile` - Update profile
- `POST /profile/avatar` - Update avatar (multipart form)

### 📡 Other Endpoints

**Admin System:**
- `GET /admin/system/status` - System status
- `POST /admin/system/service/start` - Start service
- `POST /admin/system/service/stop` - Stop service
- `POST /admin/system/service/restart` - Restart service

**Scheduler:**
- `GET /scheduler` - List scheduled jobs
- `POST /scheduler` - Create scheduled job
- `PUT /scheduler/{id}` - Update job
- `DELETE /scheduler/{id}` - Delete job

**Proactive AI:**
- `GET /proactive/rules` - List rules
- `POST /proactive/rules` - Create rule
- `GET /proactive/logs` - View logs
- `PATCH /proactive/rules/{id}/toggle` - Enable/disable

## Common Use Cases

### 1. Import Contacts from WhatsApp

```bash
POST /api/v1/contacts/import/whatsapp
Authorization: Bearer {{bearer_token}}
Content-Type: application/json

{
  "phone_numbers": ["+1234567890", "+0987654321"],
  "group_id": "optional_group_id"
}
```

### 2. Send Automated Notification

```bash
POST /api/v1/notifications/send
Authorization: Bearer {{bearer_token}}
Content-Type: application/json

{
  "recipient_id": 1,
  "template_id": 1,
  "variables": {
    "name": "John",
    "order_id": "ORD-123"
  },
  "channel": "email"
}
```

### 3. Create and Execute Workflow

```bash
# Create workflow
POST /api/v1/workflows
Authorization: Bearer {{bearer_token}}
Content-Type: application/json

{
  "name": "Customer Onboarding",
  "steps": [...]
}

# Execute workflow
POST /api/v1/workflows/1/execute
Authorization: Bearer {{bearer_token}}
{
  "contact_id": 1
}
```

### 4. Run AI Agent

```bash
POST /api/v1/agents/1/run
Authorization: Bearer {{bearer_token}}
Content-Type: application/json

{
  "input": "Analyze this customer interaction",
  "context": {
    "contact_id": 1,
    "conversation_id": 5
  }
}
```

### 5. Search Memories

```bash
GET /api/v1/memories/search?query=customer+preferences
Authorization: Bearer {{bearer_token}}
```

## Error Handling

All errors follow standard HTTP status codes:

- `200 OK` - Success
- `201 Created` - Resource created
- `400 Bad Request` - Invalid parameters
- `401 Unauthorized` - Missing/invalid token
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `429 Too Many Requests` - Rate limited
- `500 Internal Server Error` - Server error

Example error response:
```json
{
  "error": "Validation failed",
  "message": "The contact name field is required",
  "errors": {
    "name": ["The name field is required"]
  }
}
```

## Rate Limiting

The API implements rate limiting:

- Standard: 60 requests per minute
- Analysis: 5 requests per minute (throttle:analysis)
- Import: 5 requests per minute (throttle:5,1)

Rate limit headers:
- `X-RateLimit-Limit` - Requests allowed
- `X-RateLimit-Remaining` - Requests remaining
- `X-RateLimit-Reset` - Time when limit resets

## Pagination

List endpoints support pagination:

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)

**Response Format:**
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  },
  "links": {
    "first": "...",
    "last": "...",
    "next": "...",
    "prev": null
  }
}
```

## Filtering & Sorting

Supported query parameters vary by endpoint:

**Common filters:**
- `search` - Full text search
- `status` - Filter by status
- `date_from`, `date_to` - Date range
- `sort` - Sort field (prefix with `-` for descending)
- `order` - `asc` or `desc`

## Real-time Features

The API supports real-time updates via Reverb WebSockets:

1. Register a device with FCM token
2. Subscribe to broadcast channels
3. Receive real-time notifications

## Authentication Methods

### Bearer Token (Recommended)
```
Authorization: Bearer {token}
```

### Personal Access Token
Generated in Settings > API Tokens

### Session Cookie
Automatically handled if using browser

## Webhook Events

Configure webhooks in Settings for:
- Contact created/updated/deleted
- Notification sent/failed
- Workflow started/completed
- Agent executed
- Message received

## File Uploads

For file uploads (avatars, imports):

```
POST /api/v1/endpoint
Authorization: Bearer {{bearer_token}}
Content-Type: multipart/form-data

file: [binary data]
```

## Testing in Postman

1. **Import Collection:**
   - Open Postman
   - Click Import
   - Select `Nexus_API_Collection.postman_collection.json`

2. **Set Environment Variables:**
   - Set `base_url` = https://n.soulyeg.online
   - Set `bearer_token` = your token from login

3. **Run Requests:**
   - Click any request
   - Click Send
   - View response in Response pane

4. **Run Collections:**
   - Click Runner
   - Select collection
   - Click Run

## Support & Documentation

For more information:
- GitHub: [Nexus Repository]
- Documentation: [Your docs URL]
- Issues: [Your issues URL]

## Version History

- **v1.0** - Initial API release with core functionality
  - Contacts, Notifications, Conversations
  - Workflows, Tasks, Memory Hub
  - HedraSoul, AI Models
  - Settings, Monitoring, Logs

## License

API Documentation © 2024 Nexus Platform. All rights reserved.
