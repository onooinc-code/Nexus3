# 20. Complete Eloquent Models & Service Dependency Matrix

To assist engineers in extending or maintaining the PeopleConnect ecosystem, this document compiles an inventory of application layer implementations.

Across **Models**, **Services**, **Jobs**, and **Events**, this architectural dependency matrix establishes clear relationships between backend database schemas and real-time execution pipelines.

---

## 1. Architectural Namespace Layering

```mermaid
graph LR
    classDef model fill:#1f2937,stroke:#3b82f6,stroke-width:2px,color:#93c5fd;
    classDef service fill:#1e1e2e,stroke:#8b5cf6,stroke-width:2px,color:#d8b4fe;
    classDef job fill:#064e3b,stroke:#10b981,stroke-width:2px,color:#a7f3d0;

    M[App\Models\PeopleConnect\*<br/><i>13 Eloquent Entities</i>] ::: model --> S[App\Services\*<br/><i>Core Business Logic</i>] ::: service
    S --> J[App\Jobs\*<br/><i>Horizon Queue Workers</i>] ::: job
```

---

## 2. Eloquent Models Directory (`App\Models\PeopleConnect\*`)

Each model inside the `App\Models\PeopleConnect` namespace extends the framework `BaseModel` class and provides dedicated accessor methods, relational bindings, and type casting definitions for its corresponding schema table.

| Eloquent Model Class | Schema Table Mapping | Primary Responsibilities & Key Relations |
| :--- | :--- | :--- |
| **`PeopleConnectConversation`** | `peopleconnect_conversations` | Binds to `Contact`. Tracks active WhatsApp chat state and unread counter metrics. |
| **`PeopleConnectSession`** | `peopleconnect_sessions` | Manages 2-hour conversational sliding interaction windows and summarization metadata. |
| **`PeopleConnectContextSnapshot`** | `peopleconnect_context_snapshots` | Holds immutable JSON context payloads and token budget calculations for LLM execution. |
| **`PeopleConnectMessage`** | `peopleconnect_messages` | Represents raw conversation turns, transmission flags, and tone evaluations. |
| **`PeopleConnectMessageAnalysis`** | `peopleconnect_message_analyses` | Stores NLP sentiment scores, language classification identifiers, and urgency flags. |
| **`PeopleConnectMessageTag`** | `peopleconnect_message_tags` | Relational join table linking custom classification tags directly to individual message IDs. |
| **`PeopleConnectReplyDraft`** | `peopleconnect_reply_drafts` | Holds generated AI response suggestions awaiting operator approval under Copilot workflows. |
| **`PeopleConnectDeliveryAttempt`** | `peopleconnect_delivery_attempts` | Logs background HTTP gateway transmission retries and return headers from external providers. |
| **`PeopleConnectSyncRun`** | `peopleconnect_sync_runs` | Audit logger for scheduled background database-to-Firestore synchronization tasks. |
| **`PeopleConnectRawProviderEvent`** | `peopleconnect_raw_provider_events` | Immutable staging table capturing raw external HTTP webhook event payloads. |
| **`PeopleConnectProcessingLog`** | `peopleconnect_processing_logs` | Centralized system execution logger tracking operational traces and debug flags per thread. |
| **`PeopleConnectConversationTopic`** | `peopleconnect_conversation_topics` | Records shifting conversational focus areas over extended messaging relationships. |
| **`PeopleConnectReplyModeOverride`** | `peopleconnect_reply_mode_overrides` | Stores 1:1 contact rules determining automated response tolerances (`manual`, `copilot`, `autopilot`). |

---

## 3. Core Service Abstractions Matrix

Following clean architecture principles, application logic remains isolated within domain-specific service layers rather than controller classes:

| Service Class | Namespace Location | Operational Description & Primary Dependency Injections |
| :--- | :--- | :--- |
| **`WahaWebhookIngestionService`** | `App\Services\PeopleConnect\` | Receives raw webhook payloads, computes SHA-256 hashes for deduplication, and queues processing jobs. |
| **`WahaSessionService`** | `App\Services\PeopleConnect\` | Evaluates active messaging channels to assign 2-hour conversational interaction windows. |
| **`WahaCacheFallbackService`** | `App\Services\PeopleConnect\` | Resolves connection failures by cascading checks across Redis, MySQL tables, and default fallback strings. |
| **`PeopleConnectAnalysisService`** | `App\Services\PeopleConnect\` | Processes inbound messages through natural language analysis pipelines to evaluate intent and sentiment. |
| **`WahaAnalysisService`** | `App\Services\PeopleConnect\` | Generates profile evaluations from historic conversational data to refine conversational personality parameters. |
| **`PeopleConnectContextAssembler`** | `App\Services\PeopleConnect\` | Compiles historical dialogue and executes token truncation to construct immutable AI prompt contexts. |
| **`WorkflowInterpreter`** | `App\Services\Workflows\` | Evaluates Event-Condition-Action (ECA) logic networks and synchronizes concurrent step branch executions. |
| **`WorkflowExecutor`** | `App\Services\` | Orchestrates asynchronous workflow evaluations by managing execution records and background job queues. |
| **`EncryptedApiKeyStorage`** | `App\Services\AiModelsHub\` | Manages cryptographic application encryption and least-recently-used (LRU) rotation across available API keys. |

---

## 4. Background Jobs & Horizon Queue Allocations (`App\Jobs\*`)

To ensure responsive operations during message volume spikes, intensive data interactions execute within designated background queue channels via Laravel Horizon:

| Job Class Name | Designated Queue | Retry / Backoff Policy | Operational Responsibility |
| :--- | :--- | :--- | :--- |
| **`ProcessWahaWebhookJob`** | `high` / `default` | 3 Tries (Exponential: 10s, 30s, 60s) | Unpacks staging payloads, verifies atomic Redis locks, and persists records to the database. |
| **`ProcessMessageReceived`** | `messaging` | 3 Tries (15s backoff) | Coordinates messaging event distributions and triggers downstream processing integrations. |
| **`AnalyzePeopleConnectMessageJob`** | `default` | 3 Tries (30s delay on failure) | Executes natural language processing tasks against saved message content. |
| **`GenerateContactReplyDraftJob`** | `long-running` | 2 Tries (60s backoff) | Uses historical context snapshots to draft contextual reply suggestions via AI engine APIs. |
| **`SendWahaMessageJob`** | `high-priority` | 5 Tries (10s backoff) | Transmits queued outgoing messages to external HTTP gateway servers. |
| **`ExtractMemoryJob`** | `long-running` | 3 Tries | Analyzes completed conversations to extract long-term user profile observations. |

---

## 5. Event & Listener Dependency Tree

When significant state changes occur across the application, structured event notifications notify external frontends and connected background services:

```mermaid
graph TD
    classDef evt fill:#312e81,stroke:#6366f1,stroke-width:2px,color:#e0e7ff;
    classDef lst fill:#1f2937,stroke:#34d399,stroke-width:1px,color:#a7f3d0;

    E1[Event: App\Events\MessageReceived] ::: evt
    E2[Event: App\Events\MessageSent] ::: evt
    E3[Event: App\Events\ConversationUpdated] ::: evt

    E1 --> L1[Listener: ProcessMessageReceived<br/><i>Queue: messaging</i>] ::: lst
    E1 --> L2[Listener: WorkflowEventTriggerService<br/><i>Evaluates active ECA rule conditions</i>] ::: lst
    E1 --> L3[Laravel Reverb WebSocket Broadcast<br/><i>Channel: private-contact.{id}</i>] ::: lst

    E2 --> L3
    E2 --> L4[Listener: UpdateConversationUnreadCounter<br/><i>Resets unread tracking metrics to zero</i>] ::: lst
    
    E3 --> L5[Laravel Reverb WebSocket Broadcast<br/><i>Channel: private-conversation.{id}</i>] ::: lst
    E3 --> L6[Listener: SyncFirestoreConversationState<br/><i>Mirrors updated timestamps to cloud storage</i>] ::: lst
```

---

## 6. Summary & Next Step

We have documented the relationships between Eloquent schema models, domain service abstractions, background worker allocations, and system event pipelines.

In **Task 24 (REST API Endpoints & Broadcasting Channels Directory)**, we conclude our technical reference library by detailing the HTTP route definitions, external webhook formats, and Reverb channel bindings across the platform.
