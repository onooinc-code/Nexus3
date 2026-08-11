# 21. REST API Endpoints & Broadcasting Channels Directory

This document provides a comprehensive technical reference for external integration interfaces, internal REST APIs, and real-time WebSocket communication channels operating across the PeopleConnect architecture.

Compiled from **`routes/web.php`**, **`routes/api.php`**, and internal controller route decorators, this directory details required parameters, authentication policies, and data payload specifications across all active interface endpoints.

---

## 1. REST API & Controller Endpoints Directory

### 1.1 Inbound Webhook Gateway Endpoints (`api.php`)
These routes handle automated webhook transmissions sent from external messaging infrastructure providers (such as WAHA) into the backend processing pipeline.
* **Authentication Policy:** Stateless verification using shared secret headers (`X-Api-Key: {WAHA_WEBHOOK_SECRET}`).

| HTTP Method | Route Endpoint URL | Target Controller / Action | Operational Description & Payload Requirements |
| :--- | :--- | :--- | :--- |
| **`POST`** | `/api/waha/webhook` | `WahaWebhookIngestionService::handle` | Ingests incoming messaging events (`message.any`, `message.ack`, `state.change`). Requires JSON formatted payload containing message strings and conversation identifiers. |

---

### 1.2 Interactive Hub Dashboard Routes (`web.php`)
Secured administrative navigation endpoints and interactive API actions accessed directly through the PeopleConnect web application UI.
* **Authentication Policy:** Session cookies (`auth` middleware) verified against verified operator access permissions.

| HTTP Method | Route Endpoint URL | Target Controller & Method | Operational Responsibility |
| :--- | :--- | :--- | :--- |
| **`GET`** | `/hub/people-connect` | `HubController@peopleConnect` | Renders the primary messaging interface, loading contact lists and conversational histories. |
| **`GET`** | `/hub/people-connect/agent-settings` | `HubController@peopleConnectAgentSettings` | Displays the 4-tab AI Agent Studio and hyperparameter management console. |
| **`POST`** | `/hub/people-connect/agent-settings/save` | `HubController@savePeopleConnectAgentSettings` | Updates AI system instructions, model fallbacks, and hyperparameter JSON configurations. |
| **`POST`** | `/hub/people-connect/manage-key-rotation` | `HubController@manageKeyRotation` | Executes operational commands against the encryption key storage pool (`add_key`, `release_key`, `set_cooldown`, `revoke_key`). |
| **`POST`** | `/hub/people-connect/send-message` | `HubController@sendHedraMessage` | Dispatches outbound messages directly to active WAHA communication gateways. |

---

## 2. Laravel Reverb WebSocket Broadcasting Directory

To deliver interface updates across administrative consoles without requiring polling requests, background events distribute over dedicated WebSocket channels via **Laravel Reverb**.

```mermaid
graph LR
    classDef chan fill:#1e1e2e,stroke:#8b5cf6,stroke-width:2px,color:#fff;
    classDef echo fill:#064e3b,stroke:#34d399,stroke-width:1px,color:#a7f3d0;
    classDef ui fill:#1e293b,stroke:#3b82f6,stroke-width:2px,color:#93c5fd;

    A[Laravel Reverb Server<br/><b>Port: 8080 / WS Protocol</b>] ::: chan --> C1[Private Channel:<br/><i>contact.{contact_id}</i>] ::: echo
    A --> C2[Private Channel:<br/><i>conversation.{conversation_id}</i>] ::: echo
    A --> C3[Presence Channel:<br/><i>war-room.{room_id}</i>] ::: echo
    A --> C4[Public Channel:<br/><i>people-connect-general</i>] ::: echo

    C1 --> UI1[Contact Chat Timeline UI<br/>Event: MessageReceived / MessageSent] ::: ui
    C2 --> UI2[Conversation Metadata UI<br/>Event: ConversationUpdated] ::: ui
    C3 --> UI3[Multi-Operator War Room UI<br/>Tracks currently typing operators] ::: ui
    C4 --> UI4[Global Hub Dashboard UI<br/>Event: GlobalSyncStatusUpdated] ::: ui
```

---

### Detailed Channel Authorization Specifications

| Channel Name Pattern | Channel Visibility | Required Authentication | Broadcast Events Emitted | Primary Consumer Actions |
| :--- | :--- | :--- | :--- | :--- |
| **`contact.{contact_id}`** | Private | Verified dashboard user (`auth` session guard) | `MessageReceived`, `MessageSent`, `TypingIndicator` | Appends incoming chat bubbles and updates delivery markers without reloading the browser page. |
| **`conversation.{id}`** | Private | Verified dashboard user | `ConversationUpdated`, `SessionWindowClosed` | Updates unread counters, summary previews, and channel status badges across sidebar navigation menus. |
| **`war-room.{room_id}`** | Presence | Verified dashboard user + team workspace role | `UserJoined`, `UserLeft`, `DraftUpdated` | Supports collaborative operator workflows by displaying active agent typing indicators and shared message edits. |
| **`people-connect`** | Public | Authenticated workspace sessions | `GlobalSyncStatusUpdated`, `WahaStateChange` | Broadcasts system-wide health statuses, provider connectivity alerts, and scheduled database sync notices. |

---

## 3. Webhook Event Ingestion Payloads (Schema Specification)

To assist teams implementing mock endpoints or conducting API verification tests, the standard JSON structures required by `WahaWebhookIngestionService` are defined below:

### 3.1 Standard Inbound Message Ingestion (`message.any`)
```json
{
  "event": "message.any",
  "session": "nexus-primary",
  "payload": {
    "id": "false_201xxxxxxxxx@c.us_3A9F7B8C2D1E4A6B",
    "timestamp": 1717238400,
    "from": "201xxxxxxxxx@c.us",
    "to": "201000000000@c.us",
    "body": "Can you provide more details regarding the recent billing change?",
    "fromMe": false,
    "hasMedia": false,
    "ack": 1
  },
  "engine": "WEBJS"
}
```

---

### 3.2 Delivery & Read Status Notification (`message.ack`)
```json
{
  "event": "message.ack",
  "session": "nexus-primary",
  "payload": {
    "id": "true_201xxxxxxxxx@c.us_8F9D7E6C5B4A3F2E1D",
    "ack": 3,
    "ackName": "READ",
    "from": "201000000000@c.us",
    "to": "201xxxxxxxxx@c.us",
    "timestamp": 1717238445
  }
}
```
> [!TIP]
> **Understanding Transmission Acknowledgement Markers:**
> * `ack: 1` (**SERVER_ACK**): Message received by external infrastructure gateways (Single gray checkmark).
> * `ack: 2` (**DELIVERY_ACK**): Delivered to target user handset (Double gray checkmarks).
> * `ack: 3` (**READ_ACK**): Read by targeted contact (Double blue checkmarks). When `ack === 3` is captured, backend event consumers update `peopleconnect_messages.read_at` timestamps and adjust unread counter records in `peopleconnect_conversations`.

---

## 4. Summary & Project Completion

This technical directory completes the **PeopleConnect Architectural Audit & Documentation Portal**.

Across **21 technical specifications**, **interactive frontend components**, and **system reference matrices**, every functional domain within the PeopleConnect pipeline—from inbound webhook deduplication and real-time Reverb broadcasting to autonomous AI fallbacks and cryptographic key rotation—is fully mapped and documented for ongoing operational support.
