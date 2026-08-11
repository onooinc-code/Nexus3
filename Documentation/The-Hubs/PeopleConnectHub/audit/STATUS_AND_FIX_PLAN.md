# PeopleConnect & WAHA Integration — Audit Status & Priority Fix Plan

> [!IMPORTANT]
> This document details the **actual functional audit results** of the PeopleConnect / WAHA integration in Nexus3. It categorizes identified bugs, data discrepancies, and unimplemented stubs into prioritized actionable remediation items (P1–P3).

---

## 1. Audit Summary Matrix

| Issue ID | Priority | Category | Component / File | Description & Actual Behavior |
| :--- | :--- | :--- | :--- | :--- |
| **PC-FIX-01** | **P1 (Critical)** | UI / Job Disconnect | `app/Http/Controllers/Web/HubController.php:701` | "Sync WAHA" button in Hub dispatches legacy mock job `App\Jobs\SyncWahaContactsJob` (which only sleeps 1s) instead of `App\Jobs\PeopleConnect\SyncWahaContactsJob`. |
| **PC-FIX-02** | **P1 (Critical)** | Data Isolation | `app/Services/PeopleConnect/LiveMsgsSyncService.php` | Bulk historical contact/message sync writes to `contact_messages` (`ContactMessage`), whereas PeopleConnect Hub UI queries `peopleconnect_messages` (`PeopleConnectMessage`). Historical sync data is invisible in the Hub. |
| **PC-FIX-03** | **P1 (Critical)** | Database Exception | `app/Services/PeopleConnect/WahaAnalysisService.php:86` | Batch AI analysis attempts to update `confidence_score` on `contact_preferences`, but the migration column is named `confidence`. Triggers SQL `QueryException`. |
| **PC-FIX-04** | **P2 (High)** | Unimplemented Stub | `app/Jobs/PeopleConnect/ReconcileWahaDeliveryStatusJob.php` | Scheduled hourly job has an empty `handle()` method. Outbound delivery status reconciliation with WAHA is unimplemented. |
| **PC-FIX-05** | **P2 (High)** | Mock AI Service | `app/Services/PeopleConnect/PeopleConnectAnalysisService.php` | `analyze()` method returns hardcoded stub values (`intent: unknown`, `sentiment: neutral`, `confidence: 0.0`) without invoking AiModelsHub or LLM routing. |
| **PC-FIX-06** | **P2 (High)** | Event Omission | `app/Jobs/PeopleConnect/GenerateContactReplyDraftJob.php` | Comment explicitly states Phase 7 real-time draft created broadcast is skipped. UI fails to receive instant draft popups without page reload. |
| **PC-FIX-07** | **P3 (Normal)** | Legacy Tech Debt | `app/Jobs/SyncWahaContactsJob.php` & `SyncWahaMessagesJob.php` | Root-level jobs contain sleep-simulated progress loops. Should be removed or refactored to alias the service-backed `PeopleConnect` jobs. |
| **PC-FIX-08** | **P3 (Normal)** | Hardcoded Config | `app/Services/PeopleConnect/FirestoreSyncService.php:16` | Firebase admin service account filename is hardcoded (`nexus-c9155-firebase-adminsdk-fbsvc-be5bcfadde.json`) instead of referencing `config('services.firebase.credentials')`. |

---

## 2. Priority 1 — Critical Bugs & Broken User Flows

### PC-FIX-01: Hub "Sync WAHA" UI Button Dispatches Mock Jobs
- **File**: `app/Http/Controllers/Web/HubController.php` (Line 701)
- **Impact**: When an administrator clicks "Sync WAHA" in the main PeopleConnect Hub interface, the UI triggers `triggerWahaSync()`, which imports and dispatches `App\Jobs\SyncWahaContactsJob` or `App\Jobs\SyncWahaMessagesJob`. These root jobs perform `sleep(1)` loops and emit dummy progress events. No HTTP calls to WAHA are made, and no DB records are created.
- **Fix Steps**:
  1. Change imports in `HubController.php` to use `App\Jobs\PeopleConnect\SyncWahaContactsJob` and `App\Jobs\PeopleConnect\SyncWahaMessagesJob`.
  2. Ensure `WahaSyncProcess` model records are created to track real sync progress.

### PC-FIX-02: Dual Message Stores & Invisible Bulk History
- **Files**:
  - `app/Services/PeopleConnect/LiveMsgsSyncService.php` (Lines 176–185)
  - `app/Jobs/ProcessWahaMessageChunkJob.php` (Lines 88–104)
  - `app/Http/Controllers/Web/HubController.php` (Line 318)
- **Impact**: Historical bulk sync processes store messages in the legacy `contact_messages` table. However, the PeopleConnect Hub UI fetches conversation history strictly from `peopleconnect_messages`. As a result, bulk historical messages synced from WAHA API do not appear in the PeopleConnect conversation thread.
- **Fix Steps**:
  1. Update `LiveMsgsSyncService` and `ProcessWahaMessageChunkJob` to insert messages into `peopleconnect_messages` and update `peopleconnect_conversations`.
  2. Alternatively, implement a migration/reconciliation bridge job to mirror historical `contact_messages` into `peopleconnect_messages`.

### PC-FIX-03: `WahaAnalysisService` Database Exception
- **File**: `app/Services/PeopleConnect/WahaAnalysisService.php` (Line 86)
- **Impact**: Executing batch analysis via `WahaManageController@startAnalysis` causes a fatal SQL error: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'confidence_score' in 'field list'`. The migration `2026_06_21_043026_add_confidence_to_contact_preferences_table.php` added the column as `confidence`.
- **Fix Steps**:
  1. Edit `WahaAnalysisService.php` line 87: change `'confidence_score' => 0.9` to `'confidence' => 0.9`.
  2. Add automated unit test for `WahaAnalysisService`.

---

## 3. Priority 2 — Incomplete Features & Service Stubs

### PC-FIX-04: Implement `ReconcileWahaDeliveryStatusJob`
- **File**: `app/Jobs/PeopleConnect/ReconcileWahaDeliveryStatusJob.php`
- **Impact**: The scheduled job runs hourly but contains an empty `handle()` method. Messages stuck in `sending` state in `peopleconnect_messages` or `peopleconnect_delivery_attempts` are never reconciled against WAHA status endpoints.
- **Fix Steps**:
  1. Implement `handle()` logic to query WAHA `/api/sessions/default/messages/{id}` for pending delivery statuses (`ACK_READ`, `ACK_DELIVERED`, `ACK_FAILED`).
  2. Update `peopleconnect_messages.status` and `delivered_at` timestamps accordingly.

### PC-FIX-05: Real AI Analysis in `PeopleConnectAnalysisService`
- **File**: `app/Services/PeopleConnect/PeopleConnectAnalysisService.php`
- **Impact**: Inbound messages processed by `ProcessWahaWebhookJob` trigger sentiment and intent analysis, but `PeopleConnectAnalysisService` returns stubbed static neutral values.
- **Fix Steps**:
  1. Integrate `AiRouteController` / `AiRequestController` to dispatch messages to the configured intent detection model.
  2. Store actual sentiment scores, emotional tone, and extracted topics in `peopleconnect_message_analyses` and `peopleconnect_conversation_topics`.

### PC-FIX-06: Broadcast Draft Creation Events
- **File**: `app/Jobs/PeopleConnect/GenerateContactReplyDraftJob.php`
- **Impact**: Realtime WebSockets (`PeopleConnectRealtimeBroadcaster`) do not broadcast `ReplyDraftCreated` events when AI draft generation completes, requiring manual page refreshes.
- **Fix Steps**:
  1. Inject `PeopleConnectRealtimeBroadcaster` into `GenerateContactReplyDraftJob`.
  2. Call `$broadcaster->replyDraftCreated($draft)` upon successful draft saving.

---

## 4. Priority 3 — Tech Debt & Codebase Cleanup

### PC-FIX-07: Deprecate or Refactor Root Mock Jobs
- **Files**: `app/Jobs/SyncWahaContactsJob.php` and `app/Jobs/SyncWahaMessagesJob.php`
- **Fix**: Remove mock sleep loops or update classes to delegate directly to `App\Services\PeopleConnect\LiveMsgsSyncService`.

### PC-FIX-08: Config-driven Firestore Credentials Path
- **File**: `app/Services/PeopleConnect/FirestoreSyncService.php`
- **Fix**: Replace hardcoded file string `'nexus-c9155-firebase-adminsdk-fbsvc-be5bcfadde.json'` with `config('services.firebase.credentials_path', base_path('nexus-c9155-firebase-adminsdk-fbsvc-be5bcfadde.json'))`.
