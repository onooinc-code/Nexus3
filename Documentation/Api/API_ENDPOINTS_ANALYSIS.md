# Nexus Platform - Complete API Endpoints Analysis

**API Base URL**: `/api/v1`  
**Authentication**: Bearer Token (Sanctum)  
**API Version**: v1  
**Last Updated**: 2026-07-12

---

## Table of Contents

1. [Authentication & Health Endpoints](#authentication--health-endpoints)
2. [Contacts Hub](#contacts-hub)
3. [Notifications Hub](#notifications-hub)
4. [HedraSoul Hub](#hedrasoul-hub)
5. [Tasks Hub](#tasks-hub)
6. [Workflows Hub](#workflows-hub)
7. [Agents Hub](#agents-hub)
8. [AI Models Hub](#ai-models-hub)
9. [Memory Hub](#memory-hub)
10. [People Connect Hub](#people-connect-hub)
11. [Settings Hub](#settings-hub)
12. [Dashboard & Monitoring](#dashboard--monitoring)
13. [Proactive AI Engine](#proactive-ai-engine)
14. [System Management](#system-management)
15. [Logging Hub](#logging-hub)

---

## Authentication & Health Endpoints

### 1. Health Check
- **Endpoint**: `GET /health`
- **Authentication**: None (Public)
- **Middleware**: `api`
- **Description**: Check API health status
- **Query Parameters**: None
- **Request Body**: None
- **Response**:
```json
{
  "status": "healthy",
  "timestamp": "2026-07-12T10:30:00Z",
  "app": "Nexus Platform"
}
```

### 2. Register User
- **Endpoint**: `POST /register`
- **Authentication**: None (Public)
- **Middleware**: `api`
- **Description**: Create a new user account
- **Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secure_password_123",
  "password_confirmation": "secure_password_123"
}
```
- **Response**:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "token": "bearer_token_here",
  "created_at": "2026-07-12T10:30:00Z"
}
```

### 3. Login
- **Endpoint**: `POST /login`
- **Authentication**: None (Public)
- **Middleware**: `api`
- **Description**: Authenticate user and receive Bearer token
- **Request Body**:
```json
{
  "email": "john@example.com",
  "password": "secure_password_123"
}
```
- **Response**:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "bearer_token_here",
  "expires_in": 86400
}
```

### 4. Verify Token
- **Endpoint**: `POST /verify-token`
- **Authentication**: None (Public)
- **Middleware**: `api`
- **Description**: Verify validity of Bearer token
- **Request Body**:
```json
{
  "token": "bearer_token_here"
}
```
- **Response**:
```json
{
  "valid": true,
  "user_id": 1,
  "token_name": "api_token",
  "last_used_at": "2026-07-12T10:30:00Z"
}
```

### 5. Logout
- **Endpoint**: `POST /logout`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Revoke current user's token
- **Request Body**: None
- **Response**:
```json
{
  "message": "Successfully logged out"
}
```

### 6. Refresh Token
- **Endpoint**: `POST /refresh-token`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get a new Bearer token
- **Request Body**: None
- **Response**:
```json
{
  "token": "new_bearer_token_here",
  "expires_in": 86400
}
```

### 7. Broadcasting Authentication
- **Endpoint**: `POST /broadcasting/auth`
- **Authentication**: Bearer Token (Optional - supports both session and token)
- **Middleware**: `api`
- **Description**: Authenticate user for real-time broadcasting via Reverb/Echo
- **Request Body**:
```json
{
  "socket_id": "socket_id_here",
  "channel_name": "private-channel-name"
}
```
- **Response**: Broadcast::auth() response with authentication payload

---

## Contacts Hub

### 8. List All Contacts
- **Endpoint**: `GET /contacts`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Retrieve paginated list of all contacts
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 15, max: 100)
  - `sort` (string: name, email, created_at, -created_at)
  - `search` (string: search by name or email)
  - `status` (string: active, inactive, archived)
- **Request Body**: None
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "phone": "+1234567890",
      "whatsapp_number": "+1234567890",
      "facebook_id": "fb_123456",
      "status": "active",
      "preferred_channel": "whatsapp",
      "last_message_at": "2026-07-12T09:00:00Z",
      "created_at": "2026-06-01T10:30:00Z",
      "updated_at": "2026-07-12T09:00:00Z",
      "metadata": {
        "custom_field_1": "value1",
        "segment": "premium"
      }
    }
  ],
  "pagination": {
    "total": 156,
    "per_page": 15,
    "current_page": 1,
    "last_page": 11,
    "from": 1,
    "to": 15
  }
}
```

### 9. Create Contact
- **Endpoint**: `POST /contacts`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Create a new contact
- **Request Body**:
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+1234567890",
  "whatsapp_number": "+1234567890",
  "facebook_id": "fb_123456",
  "preferred_channel": "whatsapp",
  "notes": "VIP customer",
  "metadata": {
    "custom_field_1": "value1",
    "segment": "premium"
  }
}
```
- **Response**: Returns created contact object (same structure as list endpoint)

### 10. Get Contact Details
- **Endpoint**: `GET /contacts/{id}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `id` (integer, required)
- **Query Parameters**:
  - `include` (string: identifiers,relationships,preferences,aliases,notes)
- **Request Body**: None
- **Response**: Full contact object with nested relationships

### 11. Update Contact
- **Endpoint**: `PUT /contacts/{id}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `id` (integer, required)
- **Request Body**: Same as create, all fields optional
- **Response**: Updated contact object

### 12. Delete Contact
- **Endpoint**: `DELETE /contacts/{id}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `id` (integer, required)
- **Request Body**: None
- **Response**:
```json
{
  "message": "Contact deleted successfully"
}
```

### 13. Import Contacts
- **Endpoint**: `POST /contacts/import`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`, `throttle:5,1`
- **Description**: Bulk import contacts from CSV/JSON
- **Rate Limit**: 5 requests per minute
- **Request Body**:
```json
{
  "file": "base64_encoded_file_content",
  "format": "csv|json",
  "mapping": {
    "name": "full_name",
    "email": "email_address",
    "phone": "phone_number"
  }
}
```
- **Response**:
```json
{
  "batch_id": "batch_123",
  "status": "processing",
  "total_rows": 500,
  "created_at": "2026-07-12T10:30:00Z"
}
```

### 14. Import Preview
- **Endpoint**: `POST /contacts/import/preview`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Preview import before committing
- **Request Body**:
```json
{
  "file": "base64_encoded_file_content",
  "format": "csv|json",
  "sample_rows": 10
}
```
- **Response**:
```json
{
  "sample_data": [
    {
      "name": "John Doe",
      "email": "john@example.com"
    }
  ],
  "total_rows": 500,
  "duplicates": 5,
  "valid_rows": 495,
  "warnings": ["Column 'phone' is missing"]
}
```

### 15. Import WhatsApp Contacts
- **Endpoint**: `POST /contacts/import/whatsapp`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`, `throttle:5,1`
- **Description**: Import from WhatsApp (integrated via WAHA)
- **Request Body**:
```json
{
  "phone_numbers": ["+1234567890", "+1987654321"],
  "group_ids": ["group_123", "group_456"],
  "include_profile_pics": true
}
```
- **Response**: Batch import response with batch_id

### 16. Get Contact Stats
- **Endpoint**: `GET /contacts/stats`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get aggregated contact statistics
- **Query Parameters**: None
- **Request Body**: None
- **Response**:
```json
{
  "total_contacts": 1250,
  "active_contacts": 980,
  "inactive_contacts": 270,
  "by_channel": {
    "whatsapp": 650,
    "facebook": 450,
    "email": 150
  },
  "new_contacts_today": 25,
  "new_contacts_this_month": 320,
  "last_updated": "2026-07-12T10:30:00Z"
}
```

### 17. Get Contact Messages
- **Endpoint**: `GET /contacts/{id}/messages`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `id` (integer, required)
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 50)
  - `channel` (string: whatsapp, facebook, email, all)
  - `from_date` (date: YYYY-MM-DD)
  - `to_date` (date: YYYY-MM-DD)
- **Request Body**: None
- **Response**: Paginated message objects

### 18. Get Contact Intelligence
- **Endpoint**: `GET /contacts/{id}/intelligence`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `id` (integer, required)
- **Description**: Get AI-generated intelligence about contact
- **Response**:
```json
{
  "contact_id": 1,
  "persona": {
    "personality_traits": ["friendly", "professional", "detail-oriented"],
    "communication_style": "formal",
    "emotional_tone": "positive"
  },
  "interests": ["technology", "business", "innovation"],
  "engagement_level": "high",
  "lifetime_value": 5000,
  "churn_risk": "low",
  "recommendations": [
    "Suggest premium upgrade",
    "Personalize communication style"
  ]
}
```

### 19. Contact Memory Maintenance
- **Endpoint**: `POST /contacts/{id}/memory-maintenance`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `id` (integer, required)
- **Description**: Trigger memory analysis and cleanup for contact
- **Request Body**:
```json
{
  "analyze_gaps": true,
  "consolidate_duplicates": true,
  "refresh_facts": true
}
```
- **Response**:
```json
{
  "run_id": "maintenance_run_123",
  "status": "completed",
  "findings": {
    "gaps_found": 3,
    "duplicates_consolidated": 2,
    "facts_refreshed": 15
  },
  "duration_ms": 2400
}
```

### 20. Contact Analysis Runs
- **Endpoint**: `POST /contacts/{id}/analysis-runs`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`, `throttle:analysis`
- **Parameters**: `id` (integer, required)
- **Description**: Create analysis run for contact intelligence
- **Request Body**:
```json
{
  "analysis_type": "sentiment|behavior|engagement|all",
  "include_historical": true,
  "depth_level": "basic|detailed|comprehensive"
}
```
- **Response**:
```json
{
  "run_id": "analysis_run_123",
  "status": "queued",
  "analysis_type": "comprehensive",
  "started_at": "2026-07-12T10:30:00Z",
  "estimated_duration_seconds": 45
}
```

### 21. Contact Reply Mode (Global)
- **Endpoint**: `GET /contacts/reply-mode`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get global auto-reply mode setting
- **Response**:
```json
{
  "mode": "auto_reply|manual|hybrid",
  "auto_reply_enabled": true,
  "response_template_id": 5,
  "updated_at": "2026-07-12T10:30:00Z"
}
```

### 22. Update Contact Reply Mode (Global)
- **Endpoint**: `PATCH /contacts/reply-mode`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Update global auto-reply mode
- **Request Body**:
```json
{
  "mode": "auto_reply",
  "auto_reply_enabled": true,
  "response_template_id": 5
}
```
- **Response**: Updated reply mode object

### 23. Contact Identifiers (Sub-resource)
- **Endpoint**: `GET /contacts/{contact}/identifiers`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get all identifiers for contact (email, phone, social handles)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "type": "email",
      "value": "jane@example.com",
      "verified": true,
      "primary": true
    },
    {
      "id": 2,
      "type": "whatsapp",
      "value": "+1234567890",
      "verified": true,
      "primary": true
    }
  ]
}
```

### 24. Create Contact Identifier
- **Endpoint**: `POST /contacts/{contact}/identifiers`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "type": "email|phone|whatsapp|facebook|linkedin|twitter",
  "value": "identifier_value",
  "verified": false,
  "primary": false
}
```
- **Response**: Created identifier object

### 25. Contact Relationships
- **Endpoint**: `GET /contacts/{contact}/relationships`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get relationships with other contacts
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "related_contact_id": 5,
      "related_contact_name": "John Smith",
      "relationship_type": "colleague|family|friend|manager",
      "notes": "Works together in marketing",
      "created_at": "2026-06-01T10:30:00Z"
    }
  ]
}
```

### 26. Create Contact Relationship
- **Endpoint**: `POST /contacts/{contact}/relationships`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "related_contact_id": 5,
  "relationship_type": "colleague",
  "notes": "Works together in marketing"
}
```
- **Response**: Created relationship object

### 27. Contact Preferences (Sub-resource)
- **Endpoint**: `GET /contacts/{contact}/preferences`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get contact communication preferences
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "key": "communication_frequency",
      "value": "weekly",
      "updated_at": "2026-07-12T10:30:00Z"
    },
    {
      "id": 2,
      "key": "do_not_contact",
      "value": false,
      "updated_at": "2026-07-12T10:30:00Z"
    }
  ]
}
```

### 28. Update Contact Preferences
- **Endpoint**: `PUT /contacts/{contact}/preferences/{preference}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "value": "bi-weekly"
}
```
- **Response**: Updated preference object

### 29. Contact Aliases (Sub-resource)
- **Endpoint**: `GET /contacts/{contact}/aliases`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get alternative names/nicknames for contact
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "alias_name": "Jane S.",
      "alias_type": "nickname",
      "context": "Used in team communications"
    }
  ]
}
```

### 30. Contact Notes (Sub-resource)
- **Endpoint**: `GET /contacts/{contact}/notes`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get all notes associated with contact
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 20)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "content": "Important note about contact",
      "note_type": "general|task|reminder|important",
      "created_by": "user_id",
      "created_at": "2026-07-12T10:30:00Z"
    }
  ]
}
```

### 31. Create Contact Note
- **Endpoint**: `POST /contacts/{contact}/notes`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "content": "Important note about contact",
  "note_type": "general"
}
```
- **Response**: Created note object

### 32. Export Contact
- **Endpoint**: `GET /contacts/{id}/export`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Export contact data as JSON
- **Query Parameters**:
  - `format` (string: json, csv, pdf, default: json)
  - `include` (string: all, basic, messages, intelligence)
- **Response**: File download or JSON data

### 33. Erase Contact Data
- **Endpoint**: `POST /contacts/{id}/erase`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: GDPR data erasure for contact
- **Request Body**:
```json
{
  "erase_messages": true,
  "erase_memory": true,
  "erase_activity": true,
  "reason": "GDPR request"
}
```
- **Response**:
```json
{
  "status": "erasure_queued",
  "contact_id": 1,
  "erasure_id": "erasure_task_123",
  "estimated_completion": "2026-07-13T10:30:00Z"
}
```

### 34. Contact Timeline
- **Endpoint**: `GET /contacts/{id}/timeline`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get chronological activity timeline for contact
- **Response**:
```json
{
  "timeline": [
    {
      "timestamp": "2026-07-12T10:30:00Z",
      "event_type": "message_received",
      "description": "Received message via WhatsApp",
      "metadata": {
        "channel": "whatsapp",
        "message_id": "msg_123"
      }
    }
  ]
}
```

### 35. Contact Conflicts
- **Endpoint**: `GET /contacts/{id}/conflicts`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Identify potential duplicate or conflicting contacts
- **Response**:
```json
{
  "conflicts": [
    {
      "conflicting_contact_id": 5,
      "name": "Jane Smyth",
      "similarity_score": 0.92,
      "conflicting_fields": ["email", "phone"],
      "confidence": "high"
    }
  ]
}
```

### 36. Merge Contacts
- **Endpoint**: `POST /contacts/{id}/merge`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Merge current contact with another
- **Request Body**:
```json
{
  "merge_with_id": 5,
  "keep_primary": true,
  "merge_rules": {
    "name": "keep_first",
    "email": "keep_both",
    "phone": "keep_latest"
  }
}
```
- **Response**:
```json
{
  "status": "merged",
  "primary_contact_id": 1,
  "archived_contact_id": 5,
  "merged_data_summary": {
    "names": 2,
    "emails": 3,
    "messages": 450
  }
}
```

### 37. Enrich Contact
- **Endpoint**: `POST /contacts/{id}/enrich`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Fetch additional data about contact from external sources
- **Request Body**:
```json
{
  "sources": ["clearbit|apollo|hunter|all"],
  "fields": ["company", "job_title", "social_profiles", "email_metadata"]
}
```
- **Response**:
```json
{
  "enrichment_id": "enrichment_123",
  "status": "processing",
  "new_data_found": true,
  "fields_enhanced": ["company", "job_title"]
}
```

---

## Notifications Hub

### 38. List Notification Templates
- **Endpoint**: `GET /notifications/templates`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 15)
  - `type` (string: sms, email, whatsapp, push)
  - `status` (string: active, inactive, archived)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Welcome Message",
      "type": "whatsapp",
      "content": "Welcome to our service! {{contact_name}}",
      "variables": ["contact_name", "registration_date"],
      "status": "active",
      "created_at": "2026-06-01T10:30:00Z"
    }
  ],
  "pagination": {}
}
```

### 39. Create Notification Template
- **Endpoint**: `POST /notifications/templates`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "Welcome Message",
  "type": "whatsapp|email|sms|push",
  "content": "Welcome to our service! {{contact_name}}",
  "subject": "Welcome (for email templates)",
  "variables": ["contact_name", "registration_date"],
  "status": "active"
}
```
- **Response**: Created template object

### 40. Send Notification
- **Endpoint**: `POST /notifications/send`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Send notification to contacts
- **Request Body**:
```json
{
  "template_id": 1,
  "recipients": [1, 2, 3],
  "channel": "whatsapp",
  "variables": {
    "contact_name": "Jane Smith",
    "registration_date": "2026-07-12"
  },
  "schedule_at": "2026-07-12T15:00:00Z"
}
```
- **Response**:
```json
{
  "notification_batch_id": "batch_123",
  "status": "queued",
  "recipients_count": 3,
  "scheduled": true,
  "created_at": "2026-07-12T10:30:00Z"
}
```

### 41. List Notification Logs
- **Endpoint**: `GET /notifications` or `GET /notifications/logs`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 50)
  - `status` (string: sent, pending, failed, bounced)
  - `channel` (string: whatsapp, email, sms, push)
  - `from_date` (date: YYYY-MM-DD)
  - `to_date` (date: YYYY-MM-DD)
- **Response**: Paginated notification log entries

### 42. Retry Failed Notification
- **Endpoint**: `POST /notifications/{notification}/retry`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `notification` (integer, required)
- **Request Body**:
```json
{
  "max_retries": 3
}
```
- **Response**:
```json
{
  "notification_id": 123,
  "status": "retrying",
  "attempt": 2,
  "next_retry_at": "2026-07-12T11:30:00Z"
}
```

### 43. Broadcast Notification (Real-time)
- **Endpoint**: `POST /v1/notifications/broadcast`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Send real-time notification via WebSocket/Reverb
- **Request Body**:
```json
{
  "channel": "private-user-1",
  "event": "notification.received",
  "data": {
    "id": 123,
    "type": "alert",
    "title": "Important Update",
    "message": "Your task is ready",
    "action_url": "/tasks/123"
  }
}
```
- **Response**:
```json
{
  "status": "broadcasted",
  "recipients": 1,
  "timestamp": "2026-07-12T10:30:00Z"
}
```

### 44. Register FCM Token
- **Endpoint**: `POST /v1/notifications/fcm-token`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Register Firebase Cloud Messaging token for push notifications
- **Request Body**:
```json
{
  "token": "firebase_token_here",
  "device_name": "iPhone 12",
  "os": "ios"
}
```
- **Response**:
```json
{
  "status": "registered",
  "token_id": 1,
  "device_name": "iPhone 12"
}
```

### 45. Get FCM Config
- **Endpoint**: `GET /v1/notifications/fcm-config`
- **Authentication**: None (Public)
- **Description**: Get Firebase configuration for web app
- **Response**:
```json
{
  "apiKey": "firebase_key",
  "authDomain": "project.firebaseapp.com",
  "projectId": "project_id",
  "storageBucket": "bucket.appspot.com",
  "messagingSenderId": "sender_id",
  "appId": "app_id"
}
```

---

## HedraSoul Hub

### 46. List HedraSoul Sessions
- **Endpoint**: `GET /hedrasoul/sessions`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 20)
  - `status` (string: active, archived, paused)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Customer Support Session",
      "status": "active",
      "contact_id": 5,
      "model": "gpt-4",
      "autonomy_level": "guided",
      "message_count": 45,
      "created_at": "2026-07-01T10:30:00Z",
      "updated_at": "2026-07-12T10:30:00Z"
    }
  ],
  "pagination": {}
}
```

### 47. Create HedraSoul Session
- **Endpoint**: `POST /hedrasoul/sessions`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "Customer Support Session",
  "contact_id": 5,
  "model": "gpt-4",
  "autonomy_level": "guided|autonomous|supervised",
  "initial_context": "Help customer resolve billing issue",
  "system_prompt": "You are a helpful customer support agent"
}
```
- **Response**: Created session object

### 48. Send HedraSoul Message
- **Endpoint**: `POST /hedrasoul/sessions/{session}/messages`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `session` (integer, required)
- **Request Body**:
```json
{
  "content": "User message text",
  "role": "user|assistant",
  "metadata": {
    "source": "web|mobile|api"
  }
}
```
- **Response**:
```json
{
  "message_id": 123,
  "session_id": 1,
  "role": "assistant",
  "content": "AI-generated response",
  "tokens_used": 150,
  "created_at": "2026-07-12T10:30:00Z"
}
```

### 49. Regenerate HedraSoul Message
- **Endpoint**: `POST /hedrasoul/messages/{message}/regenerate`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `message` (integer, required)
- **Request Body**:
```json
{
  "regenerate_params": {
    "temperature": 0.7,
    "top_p": 0.9
  }
}
```
- **Response**: Newly generated message object

### 50. Get HedraSoul Message Trace
- **Endpoint**: `GET /hedrasoul/messages/{message}/trace`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get execution trace and reasoning for message generation
- **Response**:
```json
{
  "message_id": 123,
  "trace": {
    "steps": [
      {
        "step": 1,
        "action": "retrieve_context",
        "result": "5 relevant facts found"
      },
      {
        "step": 2,
        "action": "generate_response",
        "result": "Response generated with temperature 0.7"
      }
    ],
    "total_tokens": 150,
    "duration_ms": 2300
  }
}
```

### 51. Archive HedraSoul Session
- **Endpoint**: `POST /hedrasoul/sessions/{session}/archive`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "session_id": 1,
  "status": "archived",
  "archived_at": "2026-07-12T10:30:00Z"
}
```

### 52. Get Souly Status
- **Endpoint**: `GET /hedrasoul/souly/status`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get status of Souly autonomous agent
- **Response**:
```json
{
  "status": "active|inactive|paused|quarantined",
  "autonomy_level": "guided|autonomous|supervised",
  "current_model": "gpt-4",
  "active_sessions": 3,
  "uptime_hours": 48,
  "last_action_at": "2026-07-12T10:25:00Z"
}
```

### 53. Update Souly Autonomy
- **Endpoint**: `PATCH /hedrasoul/souly/autonomy`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "autonomy_level": "autonomous",
  "max_actions_per_hour": 100,
  "require_approval": false
}
```
- **Response**: Updated autonomy settings

### 54. Quarantine Souly
- **Endpoint**: `POST /hedrasoul/souly/quarantine`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Pause Souly operations for review/debugging
- **Request Body**:
```json
{
  "reason": "Unexpected behavior observed",
  "duration_minutes": 60
}
```
- **Response**:
```json
{
  "status": "quarantined",
  "reason": "Unexpected behavior observed",
  "until": "2026-07-12T11:30:00Z"
}
```

### 55. List Souly Instructions Versions
- **Endpoint**: `GET /hedrasoul/instructions`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get all instruction versions for Souly
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "version": 3,
      "status": "active",
      "content": "You are an autonomous support agent...",
      "created_at": "2026-07-01T10:30:00Z",
      "activated_at": "2026-07-01T10:35:00Z"
    }
  ]
}
```

### 56. Create Souly Instruction Version
- **Endpoint**: `POST /hedrasoul/instructions`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "content": "You are an autonomous support agent...",
  "description": "Updated instructions with better conflict resolution"
}
```
- **Response**: Created instruction version object

### 57. Update Souly Instructions
- **Endpoint**: `PATCH /hedrasoul/instructions/{version}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "content": "Updated instruction content",
  "description": "Updated description"
}
```
- **Response**: Updated instruction object

### 58. Activate Souly Instructions
- **Endpoint**: `POST /hedrasoul/instructions/{version}/activate`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "version": 3,
  "status": "active",
  "activated_at": "2026-07-12T10:30:00Z",
  "previous_version": 2
}
```

### 59. Test Souly Instructions
- **Endpoint**: `POST /hedrasoul/instructions/{version}/test`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Test instructions with sample scenarios
- **Request Body**:
```json
{
  "test_scenarios": [
    {
      "scenario": "customer_billing_issue",
      "input": "Why was I charged twice?"
    }
  ]
}
```
- **Response**:
```json
{
  "test_results": [
    {
      "scenario": "customer_billing_issue",
      "response": "I can help with that...",
      "passed": true,
      "issues": []
    }
  ]
}
```

### 60. Get HedraSoul Profile
- **Endpoint**: `GET /hedrasoul/profile`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get Hedra profile and facts
- **Response**:
```json
{
  "id": 1,
  "name": "Hedra",
  "personality": {
    "traits": ["helpful", "professional", "empathetic"],
    "tone": "friendly"
  },
  "facts": [
    {
      "id": 1,
      "category": "background",
      "content": "I'm an AI assistant trained to help...",
      "status": "approved"
    }
  ],
  "expertise_areas": ["customer support", "sales", "technical help"]
}
```

### 61. Update HedraSoul Profile
- **Endpoint**: `PATCH /hedrasoul/profile`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "Updated Name",
  "personality": {
    "traits": ["helpful", "professional"],
    "tone": "professional"
  }
}
```
- **Response**: Updated profile object

### 62. List Hedra Memory
- **Endpoint**: `GET /hedrasoul/memories`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `status` (string: approved, pending, rejected)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "category": "customer_preferences",
      "content": "Most customers prefer morning meetings",
      "source": "extracted",
      "status": "approved",
      "created_at": "2026-07-01T10:30:00Z"
    }
  ]
}
```

### 63. Add Hedra Memory
- **Endpoint**: `POST /hedrasoul/memories`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "category": "customer_preferences",
  "content": "Most customers prefer morning meetings",
  "source": "manual"
}
```
- **Response**: Created memory object

### 64. Approve Hedra Memory
- **Endpoint**: `POST /hedrasoul/memories/{memory}/approve`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "memory_id": 1,
  "status": "approved",
  "approved_at": "2026-07-12T10:30:00Z"
}
```

### 65. Hedra Memory Maintenance
- **Endpoint**: `POST /hedrasoul/memory-maintenance`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Trigger memory decay and consolidation
- **Request Body**:
```json
{
  "consolidate": true,
  "apply_decay": true,
  "remove_duplicates": true
}
```
- **Response**:
```json
{
  "maintenance_id": "maintenance_123",
  "findings": {
    "duplicates_found": 3,
    "duplicates_consolidated": 3,
    "decay_applied": 12,
    "duration_ms": 1500
  }
}
```

### 66. List Hedra Approvals
- **Endpoint**: `GET /hedrasoul/approvals`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `status` (string: pending, approved, rejected, deferred)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "type": "memory_suggestion",
      "content": "Should I remember X about customer?",
      "status": "pending",
      "created_at": "2026-07-12T10:30:00Z"
    }
  ]
}
```

### 67. Approve Hedra Approval Item
- **Endpoint**: `POST /hedrasoul/approvals/{approval}/approve`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "approval_id": 1,
  "status": "approved",
  "approved_at": "2026-07-12T10:30:00Z"
}
```

### 68. Get HedraSoul Notifications
- **Endpoint**: `GET /hedrasoul/notifications`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `unread_only` (boolean, default: false)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "type": "approval_needed",
      "title": "Approval Needed",
      "message": "Review memory suggestion",
      "read": false,
      "created_at": "2026-07-12T10:30:00Z"
    }
  ]
}
```

### 69. Mark Hedra Notification as Read
- **Endpoint**: `POST /hedrasoul/notifications/{notification}/read`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "notification_id": 1,
  "read": true,
  "read_at": "2026-07-12T10:30:00Z"
}
```

### 70. Search HedraSoul Mentions
- **Endpoint**: `GET /hedrasoul/mentions/search`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `q` (string: search query)
  - `limit` (integer, default: 10)
- **Response**:
```json
{
  "mentions": [
    {
      "mention": "@customer_name",
      "context": "In session about billing",
      "count": 5
    }
  ]
}
```

### 71. Search HedraSoul
- **Endpoint**: `GET /hedrasoul/search`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `q` (string: search query)
  - `type` (string: session, memory, fact, instruction)
- **Response**:
```json
{
  "results": [
    {
      "id": 1,
      "type": "session",
      "title": "Customer Support Session",
      "score": 0.95
    }
  ]
}
```

### 72. Get HedraSoul Analytics
- **Endpoint**: `GET /hedrasoul/analytics`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "sessions": {
    "total": 150,
    "active": 5,
    "average_duration_minutes": 12
  },
  "messages": {
    "total": 3200,
    "average_per_session": 21,
    "sentiment_distribution": {
      "positive": 0.65,
      "neutral": 0.25,
      "negative": 0.10
    }
  },
  "autonomy": {
    "actions_taken": 450,
    "approvals_pending": 3,
    "success_rate": 0.98
  }
}
```

---

## Tasks Hub

### 73. List All Tasks
- **Endpoint**: `GET /tasks`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 20)
  - `status` (string: pending, in_progress, completed, failed)
  - `type` (string: manual, agent, system)
  - `assigned_to` (integer: user_id)
  - `sort` (string: created_at, updated_at, priority)
- **Response**: Paginated task objects

### 74. Get Task Statistics
- **Endpoint**: `GET /tasks/stats`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "total": 450,
  "pending": 50,
  "in_progress": 25,
  "completed": 350,
  "failed": 25,
  "completion_rate": 0.78,
  "average_completion_time_hours": 4.2
}
```

### 75. Get Active Tasks
- **Endpoint**: `GET /tasks/active`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**: Tasks with status "in_progress" or "pending"

### 76. Get Tasks by Type
- **Endpoint**: `GET /tasks/type/{type}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `type` (manual|agent|system, required)
- **Response**: Filtered tasks by type

### 77. Create Manual Task
- **Endpoint**: `POST /tasks/manual`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "title": "Follow up with customer",
  "description": "Call Jane Smith about pending order",
  "priority": "high|medium|low",
  "assigned_to": 2,
  "due_date": "2026-07-15T17:00:00Z",
  "tags": ["customer_service", "urgent"],
  "related_contact_id": 5
}
```
- **Response**: Created task object

### 78. Create Agent Task
- **Endpoint**: `POST /tasks/agent`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "title": "Send promotional email to segment",
  "agent_id": 1,
  "parameters": {
    "segment": "vip_customers",
    "template_id": 5
  },
  "priority": "medium"
}
```
- **Response**: Created agent task object

### 79. Execute Task
- **Endpoint**: `POST /tasks/{task}/execute`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `task` (integer, required)
- **Request Body**:
```json
{
  "force": false,
  "notify_on_completion": true
}
```
- **Response**:
```json
{
  "task_id": 123,
  "status": "executing",
  "execution_id": "exec_123",
  "started_at": "2026-07-12T10:30:00Z"
}
```

### 80. Update Task Status
- **Endpoint**: `PATCH /tasks/{task}/status`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `task` (integer, required)
- **Request Body**:
```json
{
  "status": "completed",
  "notes": "Task completed successfully"
}
```
- **Response**: Updated task object

### 81. Cancel Task
- **Endpoint**: `POST /tasks/{task}/cancel`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "reason": "No longer needed"
}
```
- **Response**:
```json
{
  "task_id": 123,
  "status": "cancelled",
  "cancelled_at": "2026-07-12T10:30:00Z"
}
```

### 82. Get Task Logs
- **Endpoint**: `GET /tasks/{task}/logs`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "logs": [
    {
      "timestamp": "2026-07-12T10:30:00Z",
      "level": "info",
      "message": "Task started",
      "details": {}
    }
  ]
}
```

---

## Workflows Hub

### 83. List All Workflows
- **Endpoint**: `GET /workflows`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 20)
  - `status` (string: draft, published, archived)
- **Response**: Paginated workflow objects

### 84. Get Workflow Templates
- **Endpoint**: `GET /workflows/templates`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "templates": [
    {
      "id": 1,
      "name": "Customer Onboarding",
      "description": "Automated onboarding workflow",
      "steps": 5,
      "category": "onboarding"
    }
  ]
}
```

### 85. Create Workflow
- **Endpoint**: `POST /workflows`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "Customer Onboarding",
  "description": "Automated onboarding for new customers",
  "trigger": {
    "type": "contact_created",
    "conditions": []
  },
  "steps": [
    {
      "id": 1,
      "type": "send_notification",
      "params": {
        "template_id": 1
      }
    }
  ]
}
```
- **Response**: Created workflow object

### 86. Execute Workflow
- **Endpoint**: `POST /workflows/{workflow}/execute`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "trigger_data": {
    "contact_id": 5
  }
}
```
- **Response**:
```json
{
  "execution_id": "exec_123",
  "workflow_id": 1,
  "status": "running",
  "started_at": "2026-07-12T10:30:00Z"
}
```

### 87. Get Workflow Execution Status
- **Endpoint**: `GET /workflows/executions/{execution}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "execution_id": "exec_123",
  "workflow_id": 1,
  "status": "completed|running|failed",
  "steps_completed": 5,
  "total_steps": 5,
  "started_at": "2026-07-12T10:30:00Z",
  "completed_at": "2026-07-12T10:35:00Z"
}
```

### 88. Resume Workflow Execution
- **Endpoint**: `POST /workflows/executions/{execution}/resume`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "execution_id": "exec_123",
  "status": "running",
  "resumed_at": "2026-07-12T10:30:00Z"
}
```

### 89. Cancel Workflow Execution
- **Endpoint**: `POST /workflows/executions/{execution}/cancel`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "execution_id": "exec_123",
  "status": "cancelled",
  "cancelled_at": "2026-07-12T10:30:00Z"
}
```

### 90. Get Workflow Progress
- **Endpoint**: `GET /workflows/{workflow}/progress`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "workflow_id": 1,
  "active_executions": 5,
  "completed_this_week": 125,
  "success_rate": 0.95,
  "average_duration_minutes": 8.5
}
```

---

## Agents Hub

### 91. List All Agents
- **Endpoint**: `GET /agents`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `status` (string: active, inactive, quarantined)
- **Response**: Paginated agent objects

### 92. Create Agent
- **Endpoint**: `POST /agents`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "Support Agent",
  "description": "Handles customer support inquiries",
  "model": "gpt-4",
  "system_prompt": "You are a helpful support agent",
  "tools": [1, 2, 3],
  "autonomy_level": "guided"
}
```
- **Response**: Created agent object

### 93. Run Agent
- **Endpoint**: `POST /agents/{agent}/run`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Parameters**: `agent` (integer, required)
- **Request Body**:
```json
{
  "input": "Help customer with billing issue",
  "context": {
    "contact_id": 5
  }
}
```
- **Response**:
```json
{
  "execution_id": "exec_123",
  "agent_id": 1,
  "status": "running",
  "started_at": "2026-07-12T10:30:00Z"
}
```

### 94. Quarantine Agent
- **Endpoint**: `POST /agents/{agent}/quarantine`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "reason": "Unusual behavior detected",
  "duration_minutes": 60
}
```
- **Response**:
```json
{
  "agent_id": 1,
  "status": "quarantined",
  "until": "2026-07-12T11:30:00Z"
}
```

### 95. Get Agent Status
- **Endpoint**: `GET /agents/{agent}/status`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "agent_id": 1,
  "status": "active",
  "health": "good",
  "active_executions": 3,
  "last_execution_at": "2026-07-12T10:25:00Z"
}
```

### 96. Get Agent Logs
- **Endpoint**: `GET /agents/{agent}/logs`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `limit` (integer, default: 50)
  - `level` (string: debug, info, warning, error)
- **Response**:
```json
{
  "logs": [
    {
      "timestamp": "2026-07-12T10:30:00Z",
      "level": "info",
      "message": "Agent executed successfully"
    }
  ]
}
```

### 97. List Agent Tools
- **Endpoint**: `GET /agent-tools`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "tools": [
    {
      "id": 1,
      "name": "Send Email",
      "description": "Send email to contact",
      "parameters": {
        "to": "string",
        "subject": "string",
        "body": "string"
      }
    }
  ]
}
```

### 98. Get Agent Tool Details
- **Endpoint**: `GET /agent-tools/{id}`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**: Detailed tool object

### 99. List Agent Personas
- **Endpoint**: `GET /agent-personas`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**: Paginated agent persona objects

### 100. Create Agent Persona
- **Endpoint**: `POST /agent-personas`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "Friendly Support",
  "description": "Friendly and helpful personality",
  "traits": ["empathetic", "patient", "helpful"],
  "tone": "conversational"
}
```
- **Response**: Created persona object

---

## AI Models Hub

### 101. List AI Providers
- **Endpoint**: `GET /ai/providers`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 20)
  - `status` (string: active, inactive)
- **Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "OpenAI",
      "type": "gpt",
      "status": "active",
      "priority": 1,
      "models": [
        {
          "id": 1,
          "name": "gpt-4",
          "model_code": "gpt-4",
          "type": "chat"
        }
      ]
    }
  ],
  "pagination": {}
}
```

### 102. Create AI Provider
- **Endpoint**: `POST /ai/providers`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "name": "OpenAI",
  "type": "gpt",
  "api_base": "https://api.openai.com/v1",
  "api_key": "sk-...",
  "priority": 1,
  "rate_limit": 1000,
  "monthly_budget": 5000
}
```
- **Response**: Created provider object

### 103. Get AI Provider Details
- **Endpoint**: `GET /ai/providers/{id}/details`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**: Detailed provider object with models and usage

### 104. Update AI Provider Metadata
- **Endpoint**: `PATCH /ai/providers/{id}/meta`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "priority": 2,
  "monthly_budget": 10000,
  "rate_limit": 2000
}
```
- **Response**: Updated provider object

### 105. Get AI Provider Usage Stats
- **Endpoint**: `GET /ai/providers/{id}/usage-stats`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "provider_id": 1,
  "total_requests": 5000,
  "total_tokens": 2500000,
  "total_cost": 450.50,
  "requests_today": 250,
  "cost_today": 22.30
}
```

### 106. Test AI Provider
- **Endpoint**: `POST /ai/providers/{id}/test`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "model": "gpt-4",
  "prompt": "Say hello",
  "temperature": 0.7
}
```
- **Response**:
```json
{
  "test_id": "test_123",
  "status": "success",
  "response": "Hello! How can I assist you?",
  "tokens_used": 15,
  "latency_ms": 1200
}
```

### 107. Sync AI Provider Models
- **Endpoint**: `POST /ai/providers/{id}/sync-models`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**: None
- **Response**:
```json
{
  "provider_id": 1,
  "models_fetched": 10,
  "models_added": 2,
  "models_updated": 3,
  "synced_at": "2026-07-12T10:30:00Z"
}
```

### 108. Get AI Provider Health
- **Endpoint**: `GET /ai/providers/health`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "providers": [
    {
      "id": 1,
      "name": "OpenAI",
      "status": "healthy",
      "uptime_percent": 99.9,
      "response_time_ms": 850,
      "error_rate": 0.001
    }
  ]
}
```

### 109. Get AI Request Routing Matrix
- **Endpoint**: `GET /ai/intents/routing`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Get current routing rules for intents to providers
- **Response**:
```json
{
  "routing_rules": [
    {
      "intent": "customer_support",
      "preferred_provider": 1,
      "fallback_provider": 2,
      "model_preference": "gpt-4"
    }
  ]
}
```

### 110. Handle AI Request
- **Endpoint**: `POST /ai/request`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Description**: Submit request to AI model with intelligent routing
- **Request Body**:
```json
{
  "intent": "customer_support",
  "prompt": "Help resolve billing issue",
  "context": {
    "contact_id": 5,
    "channel": "whatsapp"
  },
  "temperature": 0.7,
  "max_tokens": 500
}
```
- **Response**:
```json
{
  "request_id": "req_123",
  "provider_id": 1,
  "model": "gpt-4",
  "response": "I can help with that...",
  "tokens_used": 150,
  "cost": 0.45
}
```

### 111. Get AI Cost Forecast
- **Endpoint**: `GET /ai/cost/forecast`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `period` (string: week, month, quarter, year)
- **Response**:
```json
{
  "forecast_period": "month",
  "estimated_cost": 4500,
  "based_on_days": 12,
  "projection_confidence": 0.85
}
```

### 112. Set AI Budget
- **Endpoint**: `POST /ai/cost/budget`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Request Body**:
```json
{
  "monthly_budget": 5000,
  "alert_threshold_percent": 80,
  "enforce_hard_limit": false
}
```
- **Response**:
```json
{
  "budget": 5000,
  "alert_threshold": 4000,
  "enforce_hard_limit": false,
  "updated_at": "2026-07-12T10:30:00Z"
}
```

### 113. Get AI Audit Trail
- **Endpoint**: `GET /ai/audit-trail`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `from_date` (date)
  - `to_date` (date)
- **Response**:
```json
{
  "audit_entries": [
    {
      "timestamp": "2026-07-12T10:30:00Z",
      "action": "ai_request_submitted",
      "provider_id": 1,
      "model": "gpt-4",
      "tokens_used": 150,
      "cost": 0.45
    }
  ]
}
```

### 114. Get AI Telemetry
- **Endpoint**: `GET /ai-hub/telemetry`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "total_requests": 125000,
  "total_cost": 45000,
  "total_tokens": 62500000,
  "providers": {
    "openai": {
      "requests": 100000,
      "cost": 40000
    }
  },
  "models": {
    "gpt-4": {
      "requests": 75000,
      "cost": 35000
    }
  }
}
```

---

## Settings Hub

### 115. Get All Settings
- **Endpoint**: `GET /settings`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "settings": {
    "app_name": "Nexus",
    "app_environment": "production",
    "timezone": "UTC"
  }
}
```

### 116. Get Grouped Settings
- **Endpoint**: `GET /settings/grouped`
- **Authentication**: Bearer Token (Required)
- **Response**: Settings organized by category

### 117. Update Setting
- **Endpoint**: `PUT /settings/{key}`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "value": "new_value"
}
```
- **Response**: Updated setting object

### 118. Bulk Update Settings
- **Endpoint**: `PUT /settings/bulk`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "settings": {
    "key1": "value1",
    "key2": "value2"
  }
}
```
- **Response**:
```json
{
  "updated": 2,
  "settings": {}
}
```

### 119. Emergency: Pause Global Agents
- **Endpoint**: `POST /settings/system/agent-pause`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `can:toggleEmergency`
- **Description**: Emergency pause all agents (super-admin only)
- **Request Body**:
```json
{
  "pause": true,
  "reason": "Critical security issue"
}
```
- **Response**:
```json
{
  "status": "paused",
  "agents_affected": 15,
  "paused_at": "2026-07-12T10:30:00Z"
}
```

### 120. Toggle Maintenance Mode
- **Endpoint**: `POST /settings/system/maintenance-mode`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `can:toggleEmergency`
- **Request Body**:
```json
{
  "enabled": true,
  "message": "System maintenance in progress"
}
```
- **Response**: Maintenance mode status

---

## Dashboard & Monitoring

### 121. Get Dashboard Stats
- **Endpoint**: `GET /dashboard/stats`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`, `throttle:60,1`
- **Rate Limit**: 60 requests per minute
- **Response**:
```json
{
  "contacts": {
    "total": 1250,
    "active": 980,
    "new_today": 25
  },
  "messages": {
    "total": 125000,
    "today": 450,
    "pending": 12
  },
  "agents": {
    "active": 8,
    "healthy": 7,
    "queued_tasks": 45
  },
  "ai": {
    "requests_today": 5000,
    "cost_today": 150.50,
    "budget_used_percent": 35
  }
}
```

### 122. Get Dashboard Health
- **Endpoint**: `GET /dashboard/health`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "system_health": "healthy",
  "components": {
    "database": "healthy",
    "queue": "healthy",
    "cache": "healthy",
    "reverb": "healthy"
  }
}
```

### 123. Get Activity Feed
- **Endpoint**: `GET /dashboard/activity-feed`
- **Authentication**: Bearer Token (Required)
- **Query Parameters**:
  - `limit` (integer, default: 20)
  - `type` (string: all, contact, agent, task, workflow)
- **Response**:
```json
{
  "activities": [
    {
      "timestamp": "2026-07-12T10:30:00Z",
      "type": "contact_created",
      "description": "New contact Jane Smith added",
      "user_id": 1
    }
  ]
}
```

### 124. Monitoring: Health Check Endpoint
- **Endpoint**: `GET /monitoring/health`
- **Authentication**: None (Public)
- **Middleware**: `api`
- **Response**:
```json
{
  "status": "healthy",
  "timestamp": "2026-07-12T10:30:00Z",
  "checks": {
    "database": "ok",
    "cache": "ok",
    "queue": "ok"
  }
}
```

### 125. Monitoring: Metrics Endpoint
- **Endpoint**: `GET /monitoring/metrics`
- **Authentication**: None (Public)
- **Response**: Prometheus-format metrics

---

## Memory Hub

### 126. Search Memories
- **Endpoint**: `GET /memories/search`
- **Authentication**: Bearer Token (Required)
- **Query Parameters**:
  - `q` (string: search query)
  - `type` (string: contact, hedra, system)
  - `confidence` (float: 0.0-1.0, minimum confidence)
- **Response**:
```json
{
  "results": [
    {
      "id": 1,
      "type": "contact",
      "entity": "Jane Smith",
      "content": "Prefers morning meetings",
      "confidence": 0.95
    }
  ]
}
```

### 127. List Memories
- **Endpoint**: `GET /memories`
- **Authentication**: Bearer Token (Required)
- **Response**: Paginated memory objects

### 128. Create Memory
- **Endpoint**: `POST /memories`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "type": "contact",
  "entity_id": 5,
  "content": "Prefers morning meetings",
  "category": "preference",
  "confidence": 0.95
}
```
- **Response**: Created memory object

### 129. Get Memory Versions
- **Endpoint**: `GET /memories/{id}/versions`
- **Authentication**: Bearer Token (Required)
- **Response**: All versions of a memory entry

### 130. Contact Memories
- **Endpoint**: `GET /contacts/{contactId}/memories`
- **Authentication**: Bearer Token (Required)
- **Response**: All memories associated with a contact

### 131. Extract Contact Memories
- **Endpoint**: `POST /contacts/{contactId}/memories/extract`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "from_messages": true,
  "from_interactions": true,
  "analyze_sentiment": true
}
```
- **Response**:
```json
{
  "extraction_id": "extract_123",
  "memories_found": 12,
  "status": "processing"
}
```

---

## Proactive AI Engine

### 132. List Proactive Rules
- **Endpoint**: `GET /proactive/rules`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "rules": [
    {
      "id": 1,
      "name": "Follow up with inactive customers",
      "trigger": "contact_inactive_30_days",
      "action": "send_notification",
      "enabled": true
    }
  ]
}
```

### 133. Create Proactive Rule
- **Endpoint**: `POST /proactive/rules`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "name": "Follow up with inactive customers",
  "trigger": "contact_inactive_30_days",
  "action": "send_notification",
  "action_params": {
    "template_id": 5
  },
  "enabled": true
}
```
- **Response**: Created rule object

### 134. Toggle Proactive Rule
- **Endpoint**: `PATCH /proactive/rules/{id}/toggle`
- **Authentication**: Bearer Token (Required)
- **Response**: Updated rule object with new enabled status

### 135. List Proactive Logs
- **Endpoint**: `GET /proactive/logs`
- **Authentication**: Bearer Token (Required)
- **Query Parameters**:
  - `status` (string: approved, dismissed, pending)
  - `limit` (integer, default: 50)
- **Response**: Paginated proactive suggestion logs

### 136. Approve Proactive Suggestion
- **Endpoint**: `POST /proactive-ai/suggestions/{id}/approve`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "status": "approved",
  "suggestion_id": "id"
}
```

### 137. Dismiss Proactive Suggestion
- **Endpoint**: `POST /proactive-ai/suggestions/{id}/dismiss`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "status": "dismissed",
  "suggestion_id": "id"
}
```

---

## Logging Hub

### 138. Get Application Logs
- **Endpoint**: `GET /logs`
- **Authentication**: Bearer Token (Required)
- **Query Parameters**:
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 50)
  - `level` (string: debug, info, warning, error, critical)
  - `channel` (string: single, stack, etc.)
  - `from_date` (date)
  - `to_date` (date)
- **Response**: Paginated log entries

### 139. Get Log Statistics
- **Endpoint**: `GET /logs/stats`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "total_logs": 125000,
  "by_level": {
    "debug": 50000,
    "info": 60000,
    "warning": 10000,
    "error": 4000,
    "critical": 1000
  },
  "today": 5000
}
```

### 140. Get Log Levels
- **Endpoint**: `GET /logs/levels`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "levels": ["debug", "info", "warning", "error", "critical"]
}
```

### 141. Clear Old Logs
- **Endpoint**: `POST /logs/clear`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "older_than_days": 30,
  "level": "debug"
}
```
- **Response**:
```json
{
  "deleted": 50000,
  "freed_space_mb": 150
}
```

### 142. Get System Telemetry
- **Endpoint**: `GET /settings/system/telemetry`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "uptime_hours": 720,
  "requests_total": 125000,
  "database_queries": 2500000,
  "cache_hit_rate": 0.85,
  "queue_stats": {
    "processed": 50000,
    "failed": 500,
    "pending": 200
  }
}
```

---

## People Connect Hub

### 143. Get People Connect Stats
- **Endpoint**: `GET /people-connect/stats`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "total_connections": 450,
  "active_conversations": 25,
  "pending_replies": 12,
  "average_response_time_minutes": 5.2
}
```

### 144. Search People Connect
- **Endpoint**: `GET /people-connect/search`
- **Authentication**: Bearer Token (Required)
- **Query Parameters**:
  - `q` (string: search query)
  - `limit` (integer, default: 20)
- **Response**: Search results for connections and conversations

### 145. Get People Connect Conversation
- **Endpoint**: `GET /people-connect/conversations/{id}`
- **Authentication**: Bearer Token (Required)
- **Response**: Detailed conversation with message history

### 146. Update Conversation Reply Mode
- **Endpoint**: `POST /people-connect/conversations/{id}/reply-mode`
- **Authentication**: Bearer Token (Required)
- **Request Body**:
```json
{
  "mode": "auto_reply|manual|hybrid"
}
```
- **Response**: Updated conversation with new reply mode

### 147. List Live Messages
- **Endpoint**: `GET /people-connect/livemsgs`
- **Authentication**: Bearer Token (Required)
- **Response**: Real-time message stream status

### 148. Trigger Live Messages Sync
- **Endpoint**: `POST /people-connect/livemsgs/sync`
- **Authentication**: Bearer Token (Required)
- **Response**:
```json
{
  "sync_id": "sync_123",
  "status": "syncing",
  "messages_queued": 50
}
```

---

## System Management

### 149. Get System Status
- **Endpoint**: `GET /admin/system/status`
- **Authentication**: Bearer Token (Required)
- **Middleware**: `api`, `auth:sanctum`
- **Response**:
```json
{
  "status": "operational",
  "uptime_hours": 720,
  "cpu_usage_percent": 25,
  "memory_usage_percent": 60,
  "disk_usage_percent": 45,
  "services": {
    "database": "running",
    "queue": "running",
    "cache": "running"
  }
}
```

### 150. Get Service Logs
- **Endpoint**: `GET /admin/system/service/logs`
- **Authentication**: Bearer Token (Required)
- **Query Parameters**:
  - `service` (string: database, queue, cache, etc.)
  - `limit` (integer, default: 100)
- **Response**: Service-specific log entries

---

## Summary Statistics

**Total Endpoints**: 150+  
**Authentication Required**: 92% (138 endpoints)  
**Public Endpoints**: 8% (12 endpoints)  
**Rate Limited Endpoints**: 6 (contact import, analysis, etc.)  
**Webhook Endpoints**: 2 (WAHA, Workflows)  
**Real-time Broadcasting**: 5 endpoints  
**Admin-only Endpoints**: 8+ (with policy-based access control)

---

## Common Query Parameters Across APIs

| Parameter | Type | Purpose |
|-----------|------|---------|
| `page` | integer | Pagination page number |
| `per_page` | integer | Items per page (max varies) |
| `sort` | string | Sort field and direction |
| `search`/`q` | string | Full-text search query |
| `status` | string | Filter by status |
| `from_date` | date | Date range start |
| `to_date` | date | Date range end |
| `limit` | integer | Result limit |
| `include` | string | Include related resources |

---

## Standard Response Format

### Success Response (2xx)
```json
{
  "data": {},
  "message": "Operation successful",
  "meta": {
    "timestamp": "2026-07-12T10:30:00Z",
    "request_id": "req_123"
  }
}
```

### Error Response (4xx/5xx)
```json
{
  "error": "Error code",
  "message": "Human-readable error message",
  "details": {},
  "timestamp": "2026-07-12T10:30:00Z",
  "request_id": "req_123"
}
```

### Paginated Response
```json
{
  "data": [],
  "pagination": {
    "total": 156,
    "per_page": 15,
    "current_page": 1,
    "last_page": 11,
    "from": 1,
    "to": 15
  }
}
```

---

## Authentication Headers

All authenticated endpoints require:
```
Authorization: Bearer {token}
Content-Type: application/json
```

## Common Error Codes

- `400` - Bad Request
- `401` - Unauthorized (Missing/Invalid token)
- `403` - Forbidden (Insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `429` - Rate Limited
- `500` - Server Error
- `503` - Service Unavailable

---

## Middleware Applied

- `api` - API middleware (JSON responses)
- `auth:sanctum` - Bearer token authentication
- `throttle:X,Y` - Rate limiting (X requests per Y minutes)
- `can:*` - Policy-based authorization
- `cors` - Cross-Origin Resource Sharing

---

**Document Generated**: 2026-07-12  
**Nexus API Version**: v1  
**Ready for**: OpenAPI 3.0/Swagger Generation, API Documentation, SDK Generation
