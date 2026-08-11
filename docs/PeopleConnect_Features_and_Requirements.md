# PeopleConnect AI Command Center & WAHA Messaging Engine — Complete Features & Requirements Specification

> [!IMPORTANT]
> **Master Technical Document**: This document is the comprehensive, exhaustive technical specification and reference manual for the **PeopleConnect AI Command Center** within **NexusV3**. It merges all architectural designs, existing code implementations, UI capabilities, AI agent behaviors, real-time synchronization workflows, database schemas, scheduled jobs, and remediation audit findings from both historical and newly developed documentation across the system.

---

## 1. Executive Summary & Architectural Vision

**PeopleConnect** serves as the central cognitive communication and multi-channel messaging engine in NexusV3. It unifies live external messaging via WhatsApp (powered by the **WAHA HTTP API Engine**), high-performance persistent relational storage (MySQL 8 / Eloquent), zero-latency real-time frontend streaming (Google Cloud Firestore + Laravel Reverb WebSockets), and autonomous AI conversational agents (Hedra Soul / Souly).

```mermaid
flowchart TD
    subgraph External & Channels
        WAHA[WAHA Engine HTTP API / WhatsApp Webhooks]
        FS[Google Cloud Firestore Real-Time DB]
        USER_UI[PeopleConnect Interactive Web Hub & Command Center]
    end

    subgraph Inbound Webhook Pipeline
        WHC[WebhookController @ handleWahaWebhook] -->|HMAC-SHA256/512 Verify| WIS[WahaWebhookIngestionService]
        WIS -->|Log Raw Payload & Hash Dedup| RAWDB[(peopleconnect_raw_provider_events)]
        WIS -->|Dispatch Queue Job| PWHJ[ProcessWahaWebhookJob]
        PWHJ -->|Redis Atomic Lock| PCR[PeopleConnectContactResolver]
        PWHJ -->|Resolve/Create| PCS[PeopleConnectConversationService]
        PWHJ -->|2-Hour Sliding Window| PSS[PeopleConnectSessionService]
        PWHJ -->|Idempotency Check| PMS[PeopleConnectMessageService]
        PMS -->|Persist Inbound Message| PCDB[(peopleconnect_messages & conversations)]
        PWHJ -->|Dispatch Async| ANA[AnalyzePeopleConnectMessageJob]
        PWHJ -->|Sync Real-time| FBS[SyncWahaChatsToFirebaseJob]
        PWHJ -->|Broadcast WebSockets| RTB[PeopleConnectRealtimeBroadcaster / Reverb]
    end

    subgraph AI Autopilot & Fallback Engine
        ANA -->|NLP / Sentiment / Topic Extraction| PCAS[PeopleConnectAnalysisService]
        PCAS -->|Token-Budgeted Snapshot| PCCA[PeopleConnectContextAssembler]
        PCCA -->|Check Mode & Rate Limits| PCRMS[PeopleConnectReplyModeService]
        PCRMS -->|Manual / Assisted / Autopilot| PCARS[PeopleConnectAgentReplyService]
        PCARS -->|3-Tier Fallback Routing| AI_MODELS[AI Models Hub: OpenAI → Gemini → Anthropic / Groq]
        PCARS -->|Multi-Key Quota Rotation| KEY_POOL[EncryptedApiKeyStorage & Rotation Pool]
        PCARS -->|Store Draft / Auto-Send| DRAFT_DB[(peopleconnect_reply_drafts)]
    </del>

    subgraph Outbound & Bulk Synchronization Pipeline
        DRAFT_DB -->|Autopilot Dispatch| DWMJ[DispatchWahaMessageJob]
        USER_UI -->|Manual Send / Approval| DWMJ
        DWMJ -->|HTTP POST /api/sendText| WMD[WahaMessageDispatcher]
        WMD -->|Transmit to Receiver| WAHA
        WMD -->|Log HTTP Attempt & Status| ATT[(peopleconnect_delivery_attempts)]
        LMSS[LiveMsgsSyncService / WahaManageController] -->|Bulk Historical Chunk Sync| WAHA
        LMSS -->|Persist History| LEGACY_DB[(contact_messages & contacts)]
    end
```

---

## 2. Inbound Webhook & Processing Pipeline

### 2.1 Security, HMAC Authentication & Ingestion
- **Route Definition**: `POST /api/v1/webhooks/waha` handled by `WebhookController@handleWahaWebhook`.
- **HMAC Signature Verification**:
  - Validates `X-Waha-Secret` header or `secret` URL parameter against `config('services.waha.webhook_secret')`.
  - Verifies cryptographic signature via `X-Waha-Signature` or `x-hmac-sha512256` headers using HMAC SHA-256 and SHA-512 against the raw HTTP request body.
- **Payload Deduplication & Raw Event Logging**:
  - `WahaWebhookIngestionService::ingest()` receives valid payloads and calculates an immutable SHA-256 digest (`provider_payload_hash`).
  - Stores the raw payload in `peopleconnect_raw_provider_events` with `processing_status = 'pending'`.
  - Performs an immediate idempotency query: if an identical message ID (`waha_message_id` / `payload->payload->id`) or payload hash already exists for the active session, the ingestion gracefully aborts, logging a duplicate notification without raising exceptions.
- **Asynchronous Dispatch**: Confirmed novel events instantiate and dispatch `ProcessWahaWebhookJob` to the background queue, instantly releasing the webhook HTTP response with `200 OK`.

### 2.2 Processing State Machine (`ProcessWahaWebhookJob`)
1. **Suffix Cleaning & Normalization**: Strips internal WhatsApp provider suffixes (`@c.us`, `@g.us`, `@lid`, `@broadcast`, `@s.whatsapp.net`) cleanly from target identifiers.
2. **Thread-Safe Contact Resolution**:
   - `PeopleConnectContactResolver` acquires an atomic Redis lock (`contact_resolve_{phone}`) to prevent database race conditions during sudden burst messaging.
   - Cross-references `ContactIdentityResolver` by phone number and WhatsApp linked ID (`@lid`).
   - Updates existing records with push name (`pushname`) and avatar URL, or creates a new CRM `Contact` entity linked to channel `whatsapp`.
3. **Conversation Resolution**: Calls `PeopleConnectConversationService::resolveOrCreate()` to fetch or instantiate a `PeopleConnectConversation` matching the resolved contact ID and channel target (`chatId`).
4. **Session Window Lifecycle Management**:
   - `PeopleConnectSessionService::resolveOrOpen()` inspects the conversation's interactive session state.
   - Implements a **2-Hour Sliding Window**: if an existing open session's last activity is older than 120 minutes, the old session is closed (`status = 'closed'`, setting `closed_at`) and a brand new interaction session (`PeopleConnectSession`) is instantiated.
5. **Message Persistence**:
   - `PeopleConnectMessageService::insert()` writes the finalized record into `peopleconnect_messages` with sender type (`contact`), direction (`inbound`), timestamp (`delivered_at`), status (`delivered`), and payload hash.
   - Updates `PeopleConnectConversation` metadata: increments `unread_count` by 1, updates `last_message_at`, and extracts a 100-character string slice for `last_message_preview`.
   - Increments `message_count` on the active interaction session.
6. **Real-Time Synchronization & Events**:
   - Invokes `FirestoreSyncService::syncConversationOverview()` and `syncMessage()` to immediately push JSON payloads to Cloud Firestore via Kreait SDK.
   - Triggers Laravel Reverb WebSockets broadcasting via `PeopleConnectRealtimeBroadcaster::messageReceived()`.
   - Dispatches `AnalyzePeopleConnectMessageJob` to begin AI cognitive processing and autopilot evaluation.

---

## 3. Outbound Messaging & AI Autopilot Reply Pipeline

### 3.1 Manual Outbound Dispatch
- **Interface Action**: Operator inputs text and clicks "Send" from the PeopleConnect Web UI (`/hub/people-connect/message`).
- **Validated Routing**: Validated via Form Request, invoking `DispatchWahaMessageJob($conversation, $text)`.
- **HTTP Transmission**: `WahaMessageDispatcher` builds the JSON payload (`chatId`, `text`, `session: 'default'`) and executes an HTTP POST to `{WAHA_URL}/api/sendText`.
- **Delivery Auditing**: Records every network attempt in `peopleconnect_delivery_attempts` table with HTTP status code and latency, updating message status in `peopleconnect_messages` from `sending` to `delivered` (or `failed`).

### 3.2 AI Reply & Draft Generation (`GenerateContactReplyDraftJob`)
When an inbound message is analyzed, the cognitive engine activates to assist or autonomously reply based on conversation rules:
1. **Token-Budgeted Context Assembly**:
   - `PeopleConnectContextAssembler` extracts historical messages, CRM notes, memory embeddings (Mem0), and contact preferences.
   - Formulates a structured conversational context constrained by an strict token budget (**8,000 tokens** max) to optimize prompt performance and prevent context-window overflow.
   - Saves a JSON snapshot of the exact prompt context to `peopleconnect_context_snapshots` for diagnostic auditability.
2. **Reply Mode Resolution (`PeopleConnectReplyModeService`)**:
   - Evaluates the effective operational mode (`reply_mode_effective`) for the conversation:
     - **`manual`**: No AI drafts or automatic actions are generated.
     - **`assisted`**: AI generates high-quality draft responses and saves them to `peopleconnect_reply_drafts` with status `draft`. The operator UI receives an instant draft alert via Reverb WebSockets (`ReplyDraftCreated`) to review, modify, or approve.
     - **`autopilot`**: AI autonomously constructs the final response and immediately transmits it to WhatsApp.
3. **Autopilot Safety & Rate Limiting**:
   - Before executing an automatic reply in `autopilot` mode, the service verifies rigorous safety boundaries:
     - **Velocity Limit**: Maximum of **5 automated responses per 5 minutes** per conversation. Exceeding this triggers an automatic safety trip, reverting the conversation to `assisted` mode and logging an anomaly in `peopleconnect_processing_logs`.
     - **Confidence Threshold**: Ensures AI intent confidence score surpasses defined safety parameters before transmission.
4. **Agent Execution**: Calls `PeopleConnectAgentReplyService`, which invokes the AgentsHub orchestration route (`route('agents.run')`) with the designated agent persona and tokenized snapshot.
5. **Autopilot Dispatch**: If valid under `autopilot`, automatically creates an outbound message record and dispatches `DispatchWahaMessageJob`.

---

## 4. AI Command Center & Persona Studio

### 4.1 Dedicated Agent Persona Settings Studio (`/hub/people-connect/agent-settings`)
A state-of-the-art interactive command studio designed for managing cognitive AI agents directly within the PeopleConnect workspace:
- **Header Action Integration**: Dedicated command action button embedded in the main PeopleConnect header bar (`Agent Studio`) allowing instant navigation between messaging console and AI configuration.
- **Persona Identity Management**: Configure active agent metadata, including:
  - **Agent Name & Identity**: Primary operational name (e.g., *Ertugrul Browser Orchestrator*, *Souly Cognitive Core*).
  - **System Prompt & Role Definition**: Multi-line markdown core system instructions dictating tone, behavior, boundaries, and domain expertise.
- **Precision Operating Parameters**:
  - **Temperature Adjustment**: Slider interface controlling generative randomness (from deterministic `0.0` to creative `1.0`).
  - **Maximum Token Output Limit**: Precise control over output verbosity and computational ceiling (e.g., 2,048 or 4,096 tokens).
  - **Skills Activation Array**: Checkbox assignment of project domain skills (e.g., `laravel-best-practices`, `configuring-horizon`, `debug-using-debugbar`, `tailwindcss-development`).
  - **MCP Tool Bindings**: Direct linkage to Real-Browser MCP and Chrome DevTools MCP functions (`browser_snapshot`, `browser_click`, `database-query`, etc.).

### 4.2 Multi-Tier AI Provider Fallback Engine
To ensure 99.9% uptime and immunity against external LLM API outages or network degradation, the Command Center incorporates a dynamic 3-Tier sequential fallback hierarchy:
- **Provider Registry**: Dynamically reads active providers from `AIProvider` and models from `AIModel` where `is_active = true`.
- **Sequential Routing Chain**: Configurable primary and fallback models (e.g., Tier-1: *OpenAI GPT-4o* &rarr; Tier-2: *Google Gemini 1.5 Pro* &rarr; Tier-3: *Anthropic Claude 3.5 Sonnet* or *Groq / Mistral*).
- **Zero-Downtime Failover**: If an API invocation experiences HTTP 429 (Too Many Requests), HTTP 503 (Service Unavailable), or exceeds a 5-second network timeout, the AI router immediately interrupts the thread and re-issues the identical tokenized payload to the next tier in the fallback hierarchy without surfacing exceptions to the user UI.

### 4.3 Encrypted Multi-Key Quota & Rotation Engine (`EncryptedApiKeyStorage`)
- **Multi-Key Pool**: Supports binding multiple distinct API keys per AI provider stored in `ai_api_keys`.
- **Enterprise Encryption**: All API keys are securely encrypted at rest using Laravel's native OpenSSL encryption (`Crypt::encryptString`) before storage and decrypted dynamically in operational RAM during transmission.
- **Automatic Quota Rotation**:
  - When an active API key reaches quota limits or hits rate-limiting ceilings, the engine automatically catches the error response, tags the exhausted credential with a temporary cooldown timestamp (`cooldown_until`), and selects the next available, healthy encrypted key in the provider pool.
  - **Interactive Pool Management**: Via `/hub/people-connect/agent-settings/key-rotation` (`manageKeyRotation`), administrators can insert new encrypted keys or release keys from cooldown states in real time.

---

## 5. Frontend Command Center & Real-Time UI Specifications

### 5.1 Hybrid Zero-Latency Rendering Engine (`people-connect.blade.php`)
The PeopleConnect UI architecture utilizes a high-reliability **Hybrid (MySQL SSR + Cloud Firestore JS)** rendering engine:
- **Server-Side Rendering (SSR) Initialization**:
  - On initial HTTP page load, `HubController::peopleConnect()` queries Eloquent directly for the latest **55 active conversations** and their associated contact details.
  - Pre-renders conversation cards into the DOM immediately using Blade `@forelse` loops. This guarantees a **Zero-Latency First Contentful Paint (< 500ms)** with zero loading spinners, blank screens, or layout shift.
- **Real-Time Cloud Firestore Synchronizer**:
  - Once the DOM paints, client-side JavaScript connects to Firebase / Cloud Firestore and initializes real-time listeners (`onSnapshot`) on conversation collections.
  - Incoming chats and message acknowledgments update the DOM directly in real-time without requiring browser refreshes or server polling.
- **Resilient Offline & Connection Degradation Shield**:
  - If Cloud Firestore experiences connection timeouts, empty project configurations, or backend disconnects, defensive JavaScript guards prevent empty snapshots from erasing server-rendered MySQL conversation cards.
  - **Hybrid Message History Fetch**: Clicking any conversation card (`selectChat`) immediately requests the accurate, persistent historical message timeline directly from our internal MySQL API endpoint (`/api/v1/people-connect/conversations/{id}`) before layering real-time Firestore listeners on top for live streaming updates.

### 5.2 Genuine Real-Time Telemetry & HUD Statistics
All indicators displayed in the Command Center header HUD are dynamically computed from live database models without static HTML placeholder strings:
- **Active Agent Persona**: Renders the exact DB name of the primary active persona (`Ertugrul Browser Orchestrator`).
- **Fallback Hierarchy Indicator**: Visual displays of the active routing chain (e.g., `Mistral AI → Perplexity AI → Groq`).
- **Rotation Pool State**: Verification tag confirming operational key encryption pool status (`Pool Active & Protected`).
- **Pipeline Throughput Counters**: Live aggregation of total active conversations and total stored messages directly from MySQL (`55 Chats / 601 Msgs`).

### 5.3 Live Device Telemetry & Monitoring Tabs
- **WhatsApp Linked ID & Session Status**: Full support and formatting for WhatsApp mobile identifiers (`@c.us`) and linked web device IDs (`@lid`).
- **Device Telemetry Inspector**: Real-time monitoring of connected contact devices, displaying battery telemetry, GPS coordinates, signal quality, and screen capture galleries via Firebase SDK.
- **Unlink/Link Workflow**: Interactive modal workflow allowing operators to associate or unlink detected mobile devices directly to active CRM Contact IDs.

### 5.4 Notification & Approval Hub Integration
- **Risk-Leveled Approvals**: Integrates with `HedrasoulApprovalRequest` to surface pending action approvals sorted by risk level (`high`, `medium`, `low`).
- **Live Notification Stream**: Asynchronous polling via `/hub/notifications/data` fetching active system alerts and pending approval counts (`HedrasoulNotification`).
- **Instant Decision Execution**: Interactive approval/rejection/deferral execution via `respondApproval()` (`POST /hub/notifications/approve/{id}`) with custom Decision Notes.

---

## 6. Comprehensive Component & Service Registry

| Component / Service Class | File Location | Operational Responsibility & Features |
| :--- | :--- | :--- |
| **`HubController@peopleConnect`** | `app/Http/Controllers/Web/HubController.php` | Renders main PeopleConnect view, injects SSR MySQL conversations and dynamic statistics HUD. |
| **`HubController@peopleConnectAgentSettings`**| `app/Http/Controllers/Web/HubController.php` | Manages AI Persona Studio, active providers, models, and encrypted key pool management. |
| **`PeopleConnectController`** | `app/Http/Controllers/PeopleConnect/PeopleConnectController.php`| Handles REST API search, reply mode updates, and individual chat persistence queries. |
| **`LiveMsgsController`** | `app/Http/Controllers/PeopleConnect/LiveMsgsController.php` | Serves bounded paginated streaming API endpoints for raw message feeds and diagnostic monitoring. |
| **`WebhookController@handleWahaWebhook`** | `app/Http/Controllers/WebhookController.php` | Inbound WAHA webhook route handler; validates HMAC signatures and secret headers. |
| **`WahaWebhookIngestionService`** | `app/Services/PeopleConnect/WahaWebhookIngestionService.php` | Audits raw webhooks to `peopleconnect_raw_provider_events`, verifies SHA-256 deduplication, dispatches queue job. |
| **`ProcessWahaWebhookJob`** | `app/Jobs/ProcessWahaWebhookJob.php` | Core asynchronous orchestration job for processing individual inbound WhatsApp messages. |
| **`PeopleConnectContactResolver`** | `app/Services/PeopleConnect/PeopleConnectContactResolver.php` | Redis atomic locked (`contact_resolve_{phone}`) thread-safe contact identity resolution and linking. |
| **`PeopleConnectConversationService`** | `app/Services/PeopleConnect/PeopleConnectConversationService.php` | Encapsulates all domain queries for finding, creating, and updating `PeopleConnectConversation` entities. |
| **`PeopleConnectSessionService`** | `app/Services/PeopleConnect/PeopleConnectSessionService.php` | Implements 2-hour sliding interaction window sessions in `peopleconnect_sessions`. |
| **`PeopleConnectMessageService`** | `app/Services/PeopleConnect/PeopleConnectMessageService.php` | Enforces message deduplication and persists message records to `peopleconnect_messages`. |
| **`FirestoreSyncService`** | `app/Services/PeopleConnect/FirestoreSyncService.php` | Direct-to-Firestore synchronization pipeline using Kreait Firebase SDK for chats and messages. |
| **`PeopleConnectContextAssembler`** | `app/Services/PeopleConnect/PeopleConnectContextAssembler.php` | Assembles token-budgeted prompt contexts into `peopleconnect_context_snapshots` for LLMs. |
| **`PeopleConnectReplyModeService`** | `app/Services/PeopleConnect/PeopleConnectReplyModeService.php` | Resolves reply modes (`manual`, `assisted`, `autopilot`) and enforces 5-msg/5-min velocity safety caps. |
| **`PeopleConnectAgentReplyService`** | `app/Services/PeopleConnect/PeopleConnectAgentReplyService.php` | Interoperates with AgentsHub (`agents.run`) to generate conversational AI draft replies. |
| **`WahaMessageDispatcher`** | `app/Services/PeopleConnect/WahaMessageDispatcher.php` | Formats and transmits HTTP POST payloads to WAHA `/api/sendText` and logs delivery audits. |
| **`EncryptedApiKeyStorage`** | `app/Services/AiModelsHub/EncryptedApiKeyStorage.php` | Manages multi-key encryption, decryption, quota cooldowns, and automated key rotation pool. |
| **`LiveMsgsSyncService`** | `app/Services/PeopleConnect/LiveMsgsSyncService.php` | Executes bulk historical synchronization of contacts and historical message chunks from WAHA API. |
| **`PeopleConnectRealtimeBroadcaster`** | `app/Services/PeopleConnect/PeopleConnectRealtimeBroadcaster.php`| Triggers instantaneous WebSocket broadcasts via Laravel Reverb for messages and AI draft alerts. |

---

## 7. Database Schema & Dual Store Architecture

NexusV3 incorporates two structured relational schema layers for messaging persistence:

### 7.1 Primary PeopleConnect Core Tables (`peopleconnect_*`)
Used exclusively by Webhook Ingestion, AI Autopilot, Context Assembly, and Command Center UI:
- **`peopleconnect_conversations`**: Stores channel (`whatsapp`), provider (`waha`), provider chat target (`provider_conversation_id` / `chatId`), effective reply mode (`reply_mode_effective`), unread badge counter (`unread_count`), and preview slices (`last_message_preview`, `last_message_at`).
- **`peopleconnect_messages`**: Central repository for all active UI chat history. Contains `conversation_id`, `session_id`, `contact_id`, sender type (`contact`/`agent`/`system`), direction (`inbound`/`outbound`), body text, WAHA tracking ID (`waha_message_id`), delivery status (`delivered`/`failed`), and immutable SHA-256 payload digest (`provider_payload_hash`).
- **`peopleconnect_sessions`**: Tracks chronological communication windows (`status`: `open`/`closed`, `message_count`, `opened_at`, `closed_at`).
- **`peopleconnect_reply_drafts`**: Stores AI-generated response proposals (`draft_text`, `status`: `draft`/`approved`/`sent`, associated `agent_id`, confidence metrics).
- **`peopleconnect_raw_provider_events`**: Immutable auditing table for all raw JSON webhook payloads received from WAHA along with processing flags.
- **`peopleconnect_processing_logs`**: System event logs capturing deduplication skips, rate-limit safety trips, and runtime anomalies.
- **`peopleconnect_context_snapshots`**: Serialized JSON snapshots of exact token-budgeted prompt frameworks transmitted to AI models during reply generation.
- **`peopleconnect_delivery_attempts`**: Comprehensive outgoing delivery transaction log tracking HTTP response latency and WAHA API return codes.

### 7.2 Legacy CRM Messaging Tables & Historical Reconciliation
Used primarily by historical bulk synchronization tasks (`LiveMsgsSyncService` & `WahaManageController`):
- **`contacts`**: Central CRM entity linking `waha_contact_id`, `firebase_uid`, phone number, push names, and avatar URLs.
- **`contact_messages`**: Repository for bulk historical message archive chunks imported during deep synchronization jobs (`waha_message_id`, `contact_id`, `direction`, `content`, `source`).
- **`contact_preferences`**: Stores conversational memory insights and confidence metrics (`confidence`).

> [!NOTE]
> **Schema Harmonization Rule**: While historical archives write to `contact_messages`, real-time conversational features and AI Command Center interactions read and write strictly to `peopleconnect_messages`. Historical import bridges continuously mirror relevant records to ensure complete timeline visibility.

---

## 8. Scheduled Background Tasks & Automations

Configured inside `routes/console.php` to guarantee persistent system synchronization and health:

```php
// PeopleConnect Scheduled Autonomous Tasks
Schedule::job(new \App\Jobs\PeopleConnect\SyncWahaContactsJob, null, 'peopleconnect')->hourly();
Schedule::job(new \App\Jobs\PeopleConnect\SyncWahaConversationsJob, null, 'peopleconnect')->hourly();
Schedule::job(new \App\Jobs\PeopleConnect\SyncWahaMessagesJob, null, 'peopleconnect')->hourly();
Schedule::job(new \App\Jobs\SyncWahaChatsToFirebaseJob, null, 'peopleconnect')->hourly();
Schedule::job(new \App\Jobs\PeopleConnect\ReconcileWahaDeliveryStatusJob, null, 'peopleconnect')->hourly();
Schedule::job(new \App\Jobs\PeopleConnect\CloseInactivePeopleConnectSessionsJob, null, 'peopleconnect')->everyFifteenMinutes();
```

---

## 9. Comprehensive Audit Remediation Matrix & Technical Debt Tracker

Synthesized from intensive interactive codebase audits (`PeopleConnect_Deep_Audit_Interactive_Report.html`, `PeopleConnect Interactive Report.html`, and `STATUS_AND_FIX_PLAN.md`), all system extensions must adhere to solutions established for these identified architectural challenges:

| Issue / Finding ID | Priority Level | Category | Target Location | Root Problem & Established Remediation Standard |
| :--- | :--- | :--- | :--- | :--- |
| **PC-FIX-01** | **P1 (Critical)** | UI / Job Disconnect | `HubController.php:701` | **Issue**: Hub "Sync WAHA" button dispatched legacy mock job (`App\Jobs\SyncWahaContactsJob` with sleep loops) instead of actual Service jobs.<br>**Standard**: Always import and execute real domain jobs (`App\Jobs\PeopleConnect\*`) and generate `WahaSyncProcess` records. |
| **PC-FIX-02** | **P1 (Critical)** | Data Isolation | `LiveMsgsSyncService.php` | **Issue**: Bulk history wrote solely to `contact_messages`, rendering imported chats invisible to PeopleConnect UI which queries `peopleconnect_messages`.<br>**Standard**: Direct bulk chunk processors to mirror imported data into `peopleconnect_messages` and update conversation records. |
| **PC-FIX-03** | **P1 (Critical)** | Schema Exception | `WahaAnalysisService.php:86`| **Issue**: AI preference batch sync attempted to update non-existent `confidence_score` column instead of migration-defined `confidence` column, triggering fatal SQL exceptions.<br>**Standard**: Always inspect DB schema (`Schema::getColumnListing`) prior to query writing. |
| **PC-FIX-04** | **P2 (High)** | Unimplemented Stub | `ReconcileWahaDeliveryStatusJob` | **Issue**: Scheduled hourly delivery status reconciliation job contained an empty `handle()` method, leaving sending states unreconciled.<br>**Standard**: Implement active HTTP polling to WAHA `/api/sessions/default/messages/{id}` for ACK status evolution (Sent `1` → Delivered `2` → Read `3`). |
| **PC-FIX-05** | **P2 (High)** | Mock AI Service | `PeopleConnectAnalysisService` | **Issue**: Sentiment and intent analysis method returned hardcoded neutral values (`unknown`/`0.0`) without invoking LLM routes.<br>**Standard**: Connect message analysis to AiModelsHub router to persist actual sentiment and topic extraction in `peopleconnect_message_analyses`. |
| **PC-FIX-06** | **P2 (High)** | WebSocket Omission | `GenerateContactReplyDraftJob`| **Issue**: Real-time broadcast for AI draft creation (`ReplyDraftCreated`) was bypassed in code comments, requiring page refreshes to see AI suggestions.<br>**Standard**: Inject `PeopleConnectRealtimeBroadcaster` and transmit immediate WebSocket alerts upon draft persistence. |
| **PC-FIX-07** | **P3 (Normal)** | Legacy Tech Debt | `SyncWahaContactsJob` (Root) | **Issue**: Root-level legacy jobs contain sleep loops simulating progress.<br>**Standard**: Deprecate root mock jobs in favor of `App\Services\PeopleConnect\LiveMsgsSyncService` integrations. |
| **PC-FIX-08** | **P3 (Normal)** | Hardcoded Config | `FirestoreSyncService.php:17` | **Issue**: Hardcoded service account path (`nexus-c9155-firebase-adminsdk-fbsvc-be5bcfadde.json`) caused deployment failures.<br>**Standard**: Use environment-aware configuration paths: `config('services.firebase.credentials_path')` with default fallbacks. |
| **PC-AUDIT-09** | **P1 (Critical)** | Broken Test Route | `WahaWebhookTest.php` + `routes/api.php` | **Issue**: Tests encountered `405 Method Not Allowed` because webhook route was outside `v1` prefix group while tests called `/api/v1/webhooks/waha`.<br>**Standard**: Align Webhook API route inside `v1` route prefix group. |
| **PC-AUDIT-10** | **P1 (Critical)** | DI Test Failure | `IngestionPipelineTest.php` | **Issue**: Tests experienced `ArgumentCountError` when manually instantiating `ProcessWahaWebhookJob::handle()` without providing newly added `FirestoreSyncService` dependency.<br>**Standard**: Ensure automated tests inject all required dependencies (`app(FirestoreSyncService::class)`). |
| **PC-AUDIT-11** | **P2 (High)** | Frontend Fallback | `people-connect.blade.php:582` | **Issue**: JS manual message transmission hardcoded `{contact_id: 1}` as a static fallback, misrouting outbound conversations.<br>**Standard**: Rely strictly on resolved `waha_chat_id` and conversation IDs without static IDs. |
| **PC-AUDIT-12** | **P2 (High)** | Fat Controller | `PeopleConnectController@search`| **Issue**: Oracle standards violation (Rule 7/8). Controller contained bloated Eloquent queries and query scoping.<br>**Standard**: Move all business logic and relational filtering into `PeopleConnectConversationService::searchConversations()`. |
| **PC-AUDIT-13** | **P2 (High)** | Inline Validation | `PeopleConnectController@updateReplyMode` | **Issue**: Oracle standards violation (Rule 9). Controller executed inline `$request->validate()`.<br>**Standard**: Create and utilize dedicated Form Requests (`UpdateReplyModeRequest`, `SendPeopleConnectMessageRequest`). |
| **PC-AUDIT-14** | **P2 (High)** | Memory DoS Vulnerability | `LiveMsgsController@index` | **Issue**: Unbounded pagination allow parameter injection (`?per_page=1000000`), risking RAM exhaustion.<br>**Standard**: Enforce strict numerical bounds on all API paginations: `max(1, min((int) $perPage, 200))`. |

---

## 10. Oracle Architectural Standards & Governance Mandates

To enforce institutional reliability, maintain clean architecture, and uphold **Laravel Boost Guidelines** and **Oracle Workflow Rules**, all developers and autonomous agents must strictly abide by these unbreakable rules:

### 1. Strict Separation of Concerns (Oracle Rule 7 & 8)
- **Controller Responsibilities**: HTTP Controllers (`PeopleConnectController`, `LiveMsgsController`, `HubController`, `WebhookController`) must strictly remain simple routing dispatchers. Their role is solely to unpack HTTP requests, invoke designated Service classes, and format JSON/Blade responses.
- **Service Isolation**: Absolutely ZERO domain logic, complex Eloquent query builders, external HTTP network calls, or mathematical computations are permitted inside controllers. All domain tasks must reside within dedicated, reusable single-action or domain Service classes (`WahaWebhookIngestionService`, `PeopleConnectConversationService`, `FirestoreSyncService`).

### 2. Database Performance, Eager Loading & Transactions (Oracle Rule 10)
- **Zero $N+1$ Query Tolerance**: Always utilize explicit Eloquent eager loading (`with(['contact', 'messages', 'session'])`) when reading conversations or listing message histories. Never allow loops to execute lazy queries against relational tables.
- **Atomic Transactions & Redis Locking**: Multi-step relational mutations (e.g., resolving contacts, generating sessions, inserting messages, updating unread counters) must utilize explicit database transactions (`DB::transaction`) and Redis locks to prevent race conditions during concurrent data influxes.
- **Strict Pagination Bounding**: Never allow unbounded API pagination. All endpoint paginators must enforce strict numerical clamps (e.g., maximum 200 items per page) to eliminate memory exhaustion vulnerabilities.

### 3. Form Request Validation Enforcement (Oracle Rule 9)
- **Zero Inline Controller Validation**: Never execute `$request->validate()` directly inside controller actions.
- **Dedicated Form Requests**: Every API input and form submission must be validated through dedicated Laravel Form Request classes (`SaveAgentSettingsRequest`, `ManageKeyRotationRequest`, `SendPeopleConnectMessageRequest`, `UpdateReplyModeRequest`).

### 4. No Schema Guessing & No Log Guessing (Oracle Rule 3 & 4)
- **Schema Verification First**: Before writing any Eloquent query, migration, or relationship, you must inspect the actual database schema using read-only database tools or Tinker (`php artisan tinker --execute 'echo json_encode(Schema::getColumnListing("table_name"));'`). Never assume column names (e.g., `role` vs `description`, `status` vs `is_active`, `confidence_score` vs `confidence`).
- **Log Inspection First**: When debugging unexpected exceptions or 500 server errors, inspect recent log streams (`storage/logs/laravel.log` or `browser-logs`) first before hypothesizing fixes.

### 5. PSR-12 Code Cleanliness & PHP 8.4 Best Practices (Oracle Rule 11 & Boost Rules)
- **Laravel Pint Enforcement**: Whenever PHP files are modified, execute `vendor/bin/pint --dirty --format agent` to guarantee formatting consistency.
- **Modern PHP Constructs**: Apply PHP 8.4 constructor property promotion, mandatory explicit return type declarations (`: void`, `: JsonResponse`, `: bool`), and strict parameter type hinting.
- **Descriptive Naming**: Use clear, expressive variable and method naming conventions (`isRegisteredForDiscounts`, `calculateTotalInvoiceAmount`, `savePeopleConnectAgentSettings`). Avoid abbreviations or vague nomenclature.
- **PHPDoc vs Inline Comments**: Prefer descriptive array-shaped PHPDoc blocks over inline comments. Preserve all existing documentation comments unless explicitly instructed otherwise.

### 6. Automated Testing & Verification Protocols (Oracle Rule 5 & Boost Rules)
- **Test Suite Enforcement**: Every codebase mutation or bug remediation must be verified programmatically against PHPUnit test suites before finalizing:
  ```bash
  php artisan test --compact tests/Feature/AiCommandCenterResilienceTest.php tests/Feature/PeopleConnect/
  ```
- **Property-Based Idempotency Validation**: Maintain test coverage for deduplication idempotency (`DedupSessionPropertyTest`), proving that submitting identical payloads $N$ times results in exactly 1 conversation and 1 message record.
- **Synchronous Test Queues & Mocking**: Ensure feature tests correctly simulate job execution via `config(['queue.default' => 'sync'])` and mock expensive external network invocations (`Queue::fake([AnalyzePeopleConnectMessageJob::class])`).

---

## 11. Complete Directory & File Map

```text
/www/wwwroot/Nexus/core/Nexus3/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PeopleConnect/
│   │   │   │   ├── PeopleConnectController.php (REST API & conversational actions)
│   │   │   │   └── LiveMsgsController.php (Bounded paginated raw stream)
│   │   │   ├── Web/
│   │   │   │   └── HubController.php (SSR View routing & genuine stats aggregation)
│   │   │   ├── WahaManageController.php (Bulk synchronization API controls)
│   │   │   └── WebhookController.php (HMAC verified WAHA webhook listener)
│   │   └── Requests/
│   │       ├── SaveAgentSettingsRequest.php (AI Persona Studio validation)
│   │       └── ManageKeyRotationRequest.php (Multi-Key pool validation)
│   ├── Jobs/
│   │   ├── ProcessWahaWebhookJob.php (Main asynchronous inbound message orchestrator)
│   │   ├── SyncWahaChatsToFirebaseJob.php (Hourly & event-driven Firestore synchronization)
│   │   └── PeopleConnect/
│   │       ├── AnalyzePeopleConnectMessageJob.php (NLP cognitive intent analysis)
│   │       ├── GenerateContactReplyDraftJob.php (Assisted & Autopilot AI reply generation)
│   │       ├── ReconcileWahaDeliveryStatusJob.php (Hourly outbound status reconciliation)
│   │       ├── CloseInactivePeopleConnectSessionsJob.php (15-min session window lifecycle)
│   │       ├── SyncWahaContactsJob.php (Service-backed contacts sync)
│   │       ├── SyncWahaConversationsJob.php (Service-backed chats sync)
│   │       └── SyncWahaMessagesJob.php (Service-backed historical messages sync)
│   ├── Models/
│   │   ├── Agent.php & AIApiKey.php (AI Persona metadata & encrypted rotation key pool)
│   │   ├── AIProvider.php & AIModel.php (3-Tier sequential fallback hierarchy)
│   │   └── PeopleConnect/
│   │       ├── PeopleConnectConversation.php (WhatsApp chats, badges, effective reply modes)
│   │       ├── PeopleConnectMessage.php (Primary message timeline, delivery states, hashes)
│   │       ├── PeopleConnectSession.php (2-Hour chronological interaction window sessions)
│   │       ├── PeopleConnectReplyDraft.php (AI proposals awaiting review or auto-dispatched)
│   │       └── PeopleConnectRawProviderEvent.php (Immutable raw incoming webhook audit log)
│   └── Services/
│       ├── AiModelsHub/
│       │   └── EncryptedApiKeyStorage.php (Multi-Key encryption & quota cooldown engine)
│       └── PeopleConnect/
│           ├── WahaWebhookIngestionService.php (HMAC validation & payload SHA-256 dedup)
│           ├── PeopleConnectContactResolver.php (Redis atomic locked identity linking)
│           ├── PeopleConnectConversationService.php (Isolated Eloquent conversation domain)
│           ├── PeopleConnectSessionService.php (Sliding interaction session window manager)
│           ├── PeopleConnectMessageService.php (Idempotent message persistence layer)
│           ├── FirestoreSyncService.php (Kreait SDK direct Cloud Firestore synchronizer)
│           ├── PeopleConnectContextAssembler.php (8,000-token prompt context snapshot builder)
│           ├── PeopleConnectReplyModeService.php (Reply mode evaluation & 5/5min rate limiter)
│           ├── PeopleConnectAgentReplyService.php (AgentsHub cognitive orchestration router)
│           ├── WahaMessageDispatcher.php (Outbound HTTP transmit & delivery auditing)
│           ├── WahaAnalysisService.php (Batch preference insights & confidence scoring)
│           ├── LiveMsgsSyncService.php (Bulk historical chunk synchronization engine)
│           └── PeopleConnectRealtimeBroadcaster.php (Laravel Reverb WebSockets broadcaster)
│
├── resources/views/hubs/
│   ├── people-connect.blade.php (Hybrid SSR + Firestore real-time command center UI)
│   └── people-connect-agent-settings.blade.php (Dedicated interactive AI Persona Studio)
│
├── docs/
│   └── PeopleConnect_Features_and_Requirements.md (This definitive technical specification)
│
└── tests/Feature/
    ├── AiCommandCenterResilienceTest.php (Verifies key rotation cooldowns & Persona studio)
    └── PeopleConnect/
        ├── IngestionPipelineTest.php (End-to-end webhook processing & dependency injection)
        ├── WahaWebhookTest.php (HMAC SHA-512/256 security & route group validation)
        └── PropertyBased/
            └── DedupSessionPropertyTest.php (Idempotency fuzz test: 10 dupes = 1 record)
```
