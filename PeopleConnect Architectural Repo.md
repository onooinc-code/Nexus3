# PeopleConnect Architectural Report

This report provides an extensive, production-grade technical breakdown of the **PeopleConnect** communications subsystem within the Nexus3 platform. It details the runtime execution, database schemas, component file mappings, and APIs.

---

## 1. Engine Architecture & Flow

The PeopleConnect subsystem acts as the communication orchestrator, bridging incoming client messages (e.g. from WAHA WhatsApp integration) with downstream AI processes (intent classification, preference extraction) and automated autopilot replies.

```mermaid
graph TD
    %% Styling
    classDef gateway fill:#0f172a,stroke:#38bdf8,stroke-width:2px,color:#f8fafc;
    classDef db fill:#581c87,stroke:#c084fc,stroke-width:2px,color:#f8fafc;
    classDef worker fill:#1e1b4b,stroke:#818cf8,stroke-width:2px,color:#f8fafc;
    classDef service fill:#064e3b,stroke:#34d399,stroke-width:2px,color:#f8fafc;

    %% Inbound Flow
    Webhook[WAHA Webhook Event] -->|1. Ingest Payload| IngestService[WahaWebhookIngestionService]
    IngestService -->|2. Deduplicate| RawEvent[(peopleconnect_raw_provider_events)]
    IngestService -->|3. Dispatch Job| ProcessWahaWebhookJob
    
    ProcessWahaWebhookJob -->|4. Locked Resolve| ContactResolver[PeopleConnectContactResolver]
    ContactResolver -->|Redis Lock / Serialize| DBContact[(contacts)]
    
    ProcessWahaWebhookJob -->|5. Resolve/Create| ConvService[PeopleConnectConversationService]
    ConvService -->|Get thread| DBConv[(peopleconnect_conversations)]
    
    ProcessWahaWebhookJob -->|6. Check 2-hour inactivity| SessionService[PeopleConnectSessionService]
    SessionService -->|Get/Open Session| DBSession[(peopleconnect_sessions)]
    
    ProcessWahaWebhookJob -->|7. Persist Message| MsgService[PeopleConnectMessageService]
    MsgService -->|Deduplicate & Create| DBMsg[(peopleconnect_messages)]

    %% Async Pipeline
    ProcessWahaWebhookJob -->|8. Dispatch Analysis| AnalyzeJob[AnalyzePeopleConnectMessageJob]
    AnalyzeJob -->|Run NLP / Extract Topics| AnalysisService[PeopleConnectAnalysisService]
    AnalysisService -->|Save Analysis| DBAnalysis[(peopleconnect_message_analyses)]
    AnalysisService -->|Upsert Topics| DBTopics[(peopleconnect_conversation_topics)]

    %% Real-time Broadcast
    ProcessWahaWebhookJob -->|9. Broadcast State| RealtimeBroadcaster[PeopleConnectRealtimeBroadcaster]
    RealtimeBroadcaster -->|Pusher/Echo: message.received| WebUI[Dashboard Web Interface]

    %% Reply Mode Pipeline
    ProcessWahaWebhookJob -->|10. Trigger Reply Logic| ReplyModeService[PeopleConnectReplyModeService]
    ReplyModeService -->|Evaluate Effective Mode| ModeCheck{Mode?}
    
    ModeCheck -->|manual| ManualMode[Wait for User response]
    ModeCheck -->|autopilot / copilot| GenerateDraftJob[GenerateContactReplyDraftJob]
    
    GenerateDraftJob -->|Assemble Message History| ContextAssembler[PeopleConnectContextAssembler]
    ContextAssembler -->|Freeze State| DBSnapshot[(peopleconnect_context_snapshots)]
    
    GenerateDraftJob -->|Call Agents Hub API| AgentReplyService[PeopleConnectAgentReplyService]
    AgentReplyService -->|POST /agents/{id}/run| AgentHub[Agent Execution Service]
    
    AgentReplyService -->|Store Draft| DBDraft[(peopleconnect_reply_drafts)]
    
    ModeCheck -->|autopilot| AutopilotGate{Autopilot Safe?}
    AutopilotGate -->|No: Rate Limit Exceeded| BlockLog[(peopleconnect_processing_logs)]
    AutopilotGate -->|Yes| DispatchWahaMessageJob[DispatchWahaMessageJob]

    class Webhook,AgentHub gateway;
    class RawEvent,DBContact,DBConv,DBSession,DBMsg,DBAnalysis,DBTopics,DBSnapshot,DBDraft,BlockLog db;
    class ProcessWahaWebhookJob,AnalyzeJob,GenerateDraftJob,DispatchWahaMessageJob worker;
    class IngestService,ContactResolver,ConvService,SessionService,MsgService,AnalysisService,RealtimeBroadcaster,ReplyModeService,ContextAssembler,AgentReplyService service;
```

---

## 2. Engine Mechanics

### 2.1 Contact Resolution & Race Prevention
To prevent multiple database entries for the same client when receiving messages simultaneously, the [PeopleConnectContactResolver](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectContactResolver.php) uses a Redis-backed atomic lock:
```php
$lock = Cache::lock("contact_resolve_{$phone}", 10);
try {
    $lock->block(5); // Blocks for up to 5 seconds
    // Re-verify existence before trying to create a new Contact record
} finally {
    $lock->release();
}
```

### 2.2 Inactivity Session Management
Messages are grouped into sessions to keep LLM context clean. The [PeopleConnectSessionService](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectSessionService.php) checks if `last_message_at` is older than **2 hours**. If so, it closes the inactive session, stores an inactivity reason, and opens a new session.

### 2.3 Context Snapshots
Before invoking an LLM, the [PeopleConnectContextAssembler](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectContextAssembler.php) constructs a snapshot payload. It counts tokens on the last 100 messages to fit within the `8,000 token budget`, excluding older items, and registers the snapshot in the database.

### 2.4 Autopilot Safety & Rate Limiting
If a conversation's reply mode evaluates to `autopilot`, the [PeopleConnectReplyModeService](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectReplyModeService.php) applies safety gates. It enforces a rate limit of **max 5 outbound messages in 5 minutes** per contact. If exceeded, it logs the block to `peopleconnect_processing_logs` and halts automatic dispatch.

---

## 3. Database Schema Blueprint (13 Tables)

Created in migration `2026_06_11_000004_create_peopleconnect_tables.php`:

| Table Name | Description | Key Fields & Relations |
| :--- | :--- | :--- |
| `peopleconnect_conversations` | Holds active communication channels. | Links to `contacts.id`. Unique on `(contact_id, channel, provider)`. |
| `peopleconnect_sessions` | Stores conversation sessions. | Foreign key to `peopleconnect_conversations`. Tracks status & inactivity. |
| `peopleconnect_context_snapshots` | Freezes context payloads for LLM invocation. | Links to `conversations` and `sessions`. Stores JSON payloads. |
| `peopleconnect_messages` | Individual inbound/outbound chat messages. | Unique on `(conversation_id, waha_message_id)`. Tracks sentiment & tone. |
| `peopleconnect_message_analyses` | Deep NLP parameters (intent, sentiment, urgency). | One-to-One relation with `peopleconnect_messages.id`. |
| `peopleconnect_message_tags` | Semantic classification tags. | Unique pair of `(message_id, tag)`. |
| `peopleconnect_reply_drafts` | AI-generated reply suggestions awaiting approval. | Links to `conversations`, `messages`, and `context_snapshots`. |
| `peopleconnect_delivery_attempts` | Outbound request tracking. | Links to `peopleconnect_messages`. Stores raw response & error. |
| `peopleconnect_sync_runs` | Sync job run metrics. | Tracks found items (`contacts_found`, `messages_found`, `errors`). |
| `peopleconnect_raw_provider_events` | Webhook duplicate prevention. | Holds JSON payloads and status (`pending`, `processed`, `error`). |
| `peopleconnect_processing_logs` | Error logging and pipeline diagnostics. | Foreign key to `peopleconnect_conversations`. Tracks event types. |
| `peopleconnect_conversation_topics` | Tracked topics within a thread. | Unique pair of `(conversation_id, name)`. |
| `peopleconnect_reply_mode_overrides` | Contact-specific reply mode overrides. | Unique on `contact_id`. Overrides global config settings. |

---

## 4. Component File Map

Below is a directory list of all files comprising the PeopleConnect engine.

### 4.1 Eloquent Models
*   **[PeopleConnectMessage.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Models/PeopleConnect/PeopleConnectMessage.php):** Holds message properties and relationships.
*   **[PeopleConnectConversation.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Models/PeopleConnect/PeopleConnectConversation.php):** Manages conversation mode overrides.
*   **[PeopleConnectSession.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Models/PeopleConnect/PeopleConnectSession.php):** Manages temporal session models.
*   **Others in [app/Models/PeopleConnect](file:///www/wwwroot/Nexus/core/Nexus3/app/Models/PeopleConnect):** `PeopleConnectContextSnapshot`, `PeopleConnectConversationTopic`, `PeopleConnectDeliveryAttempt`, `PeopleConnectMessageAnalysis`, `PeopleConnectMessageTag`, `PeopleConnectProcessingLog`, `PeopleConnectRawProviderEvent`, `PeopleConnectReplyDraft`, `PeopleConnectReplyModeOverride`, `PeopleConnectSyncRun`.

### 4.2 Services
*   **[PeopleConnectContactResolver.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectContactResolver.php):** Serializes contact creation with Redis locking.
*   **[PeopleConnectConversationService.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectConversationService.php):** Resolves/creates active conversation threads.
*   **[PeopleConnectSessionService.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectSessionService.php):** Evaluates session inactivity and bounds.
*   **[PeopleConnectMessageService.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectMessageService.php):** Handles deduplicated message persistence.
*   **[PeopleConnectAnalysisService.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectAnalysisService.php):** Saves NLP metrics and registers conversation topics.
*   **[PeopleConnectContextAssembler.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectContextAssembler.php):** Truncates history to fit LLM input token caps.
*   **[PeopleConnectReplyModeService.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectReplyModeService.php):** Evaluates overrides and applies autopilot rate limiters.
*   **[PeopleConnectAgentReplyService.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectAgentReplyService.php):** Communicates with the Agents Hub API to produce suggestions.
*   **[PeopleConnectRealtimeBroadcaster.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Services/PeopleConnect/PeopleConnectRealtimeBroadcaster.php):** Emits Echo WebSocket broadcasts.

### 4.3 Background Workers & Events
*   **[ProcessWahaWebhookJob.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Jobs/ProcessWahaWebhookJob.php):** Primary webhook payload worker.
*   **[AnalyzePeopleConnectMessageJob.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Jobs/PeopleConnect/AnalyzePeopleConnectMessageJob.php):** Asynchronous analyzer dispatch.
*   **[GenerateContactReplyDraftJob.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Jobs/PeopleConnect/GenerateContactReplyDraftJob.php):** Formulates LLM drafts asynchronously.
*   **[CloseInactivePeopleConnectSessionsJob.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Jobs/PeopleConnect/CloseInactivePeopleConnectSessionsJob.php):** Scheduled task to sweep inactive threads.
*   **Events in [app/Events/PeopleConnect](file:///www/wwwroot/Nexus/core/Nexus3/app/Events/PeopleConnect):** Broadcasters like `MessageReceived`, `MessageAnalyzed`, `MessageDelivered`, `MessageFailed`, `SessionOpened`, `SessionClosed`, and `ReplyDraftCreated`.

### 4.4 Web Interfaces & Controllers
*   **[PeopleConnectController.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Http/Controllers/PeopleConnect/PeopleConnectController.php):** REST controller for statistics, search, and mode management.
*   **[LiveMsgsController.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Http/Controllers/PeopleConnect/LiveMsgsController.php):** Inbound message browser feed and manual sync triggers.
*   **[HubController.php](file:///www/wwwroot/Nexus/core/Nexus3/app/Http/Controllers/Web/HubController.php):** UI data provider.
*   **[people-connect.blade.php](file:///www/wwwroot/Nexus/core/Nexus3/resources/views/hubs/people-connect.blade.php):** Hub control panel.

---

## 5. API Endpoints

All endpoints are grouped under prefix `/api/v1/people-connect`:

| Method | Endpoint | Action | Parameters | Response |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/api/v1/people-connect/stats` | `PeopleConnectController@stats` | None | `{total_contacts, active_sessions, unread_conversations, status}` |
| **GET** | `/api/v1/people-connect/search` | `PeopleConnectController@search` | `q` (query string) | Array of matching conversations & contact profiles |
| **GET** | `/api/v1/people-connect/conversations/{id}` | `PeopleConnectController@showConversation` | `id` (Conversation ID) | Complete Conversation object with 50 messages & 5 sessions |
| **POST** | `/api/v1/people-connect/conversations/{id}/reply-mode` | `PeopleConnectController@updateReplyMode` | `reply_mode` | Success response and updated Conversation model |
| **GET** | `/api/v1/people-connect/livemsgs` | `LiveMsgsController@index` | `per_page` | Paginated index of all incoming messages |
| **POST** | `/api/v1/people-connect/livemsgs/sync` | `LiveMsgsController@triggerSync` | `type` (contacts, conversations, all) | Sync dispatch confirmation response |

---

## 6. Broadcasting Channels

To power the reactive web interface, all events implement `ShouldBroadcast` using Private Channels:

1.  **`private-peopleconnect.hub`**: For general dashboard metric increases.
2.  **`private-peopleconnect.conversation.{conversation_id}`**: Direct conversation window updating for received messages, status shifts, and reply draft completions.
