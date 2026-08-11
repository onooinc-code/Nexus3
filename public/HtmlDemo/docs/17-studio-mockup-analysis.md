# 17. Mockup Identification & Missing Capability Bridges

An architectural audit of any production platform requires clearly distinguishing between **fully functional operational systems** and **interface prototypes or unlinked implementation layers**.

During our review of the AI Agent Settings & Studio console (`resources/views/hubs/people-connect-agent-settings.blade.php`) and its corresponding backend handlers in `HubController.php`, we identified several design structures that function as visual mockups or lack full execution connectivity. This document maps these operational boundaries to support future development planning.

---

## 1. Studio Subsystem Operational Status

| Studio Feature Module | UI Frontend Status | Backend Controller Status | Runtime Pipeline Bridge | Architectural Classification |
| :--- | :--- | :--- | :--- | :--- |
| **Persona & System Prompt** | **Active & Interactive** | **Saved via JSON update** | **Active** (Read by Context Assembler) | **Production-Grade System** |
| **Hyperparameters (Temp / Tokens)** | **Active (DOM slider bound)** | **Saved into `settings` JSON** | **Partial** (Saved, awaiting active engine reading) | **Persistent Setting (Bridge Pending)** |
| **Multi-Key LRU Rotation Engine** | **Active (Status & Cooldown tags)** | **Active (`manageKeyRotation` actions)** | **Active** (Used by active provider adapters) | **Production-Grade Engine** |
| **3-Tier Fallback Model Pipeline** | **Active Select Inputs** | **Saved into `settings['fallback_models']`** | **Unconnected** (No loop in message analysis) | **Persistent Schema / Disconnected Bridge** |
| **Active Capability Indicator Switches** | **Hardcoded Disabled Checkboxes** | **Unreceived during normal form POST** | **Unconnected** (Visual presentation only) | **Static Interface Mockup** |
| **Studio Agent Record Resolution** | **Single default model display** | **Fuzzy Description/Name Query** | **Unreliable in Multi-Agent layouts** | **Heuristic Fallback Approximation** |

---

## 2. Deep Analysis of Identified Gaps

### 2.1 Fuzzy Agent Selection Heuristic (`peopleConnectAgentSettings`)
When navigating to `/hub/people-connect/agent-settings`, how does the controller select which AI agent record to load? Instead of reading a fixed configuration identifier or an explicit environment key (such as `config('people-connect.default_agent_id')`), `HubController.php` utilizes a heuristic query lookup:

```php
    public function peopleConnectAgentSettings()
    {
        $agent = Agent::where('description', 'like', '%people%connect%')
            ->orWhere('name', 'like', '%Souly%')
            ->orWhere('is_active', true)
            ->first();

        if (! $agent) {
            $agent = Agent::first();
        }
```

> [!WARNING]
> **Multi-Agent Configuration Risk:** Because this query falls back to `orWhere('is_active', true)->first()`, if multiple active agents exist within the database without specific keyword matches in their descriptions or names, the console may display an unintended agent record. 
>
> **Remediation Recommendation:** Replace fuzzy string matching with a deterministic configuration reference (e.g., storing `PEOPLE_CONNECT_AGENT_UUID` within system configuration tables) or allow explicit query-string identification (`?agent_id=...`).

---

### 2.2 Capability Indicator Disconnect (Visual Mockups vs. Payload)
In Tab 1 of the Studio interface (`people-connect-agent-settings.blade.php`), the console presents three capability switches labeled **"Active Agent Capabilities"**:

```html
<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" role="switch" id="toolWeb" checked disabled>
    <label class="form-check-label text-muted small" for="toolWeb">Real-Time Firestore Sync Pipeline</label>
</div>
<div class="form-check form-switch mb-2">
    <input class="form-check-input" type="checkbox" role="switch" id="toolRotation" checked disabled>
    <label class="form-check-label text-muted small" for="toolRotation">Auto-Exhaustion API Key Rotation</label>
</div>
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" role="switch" id="toolFallback" checked disabled>
    <label class="form-check-label text-muted small" for="toolFallback">3-Tier Fallback Resilience Engine</label>
</div>
```

While these controls communicate active background functionalities to operators, notice that the inputs do not include form attributes (`name="tools[]"`) and are set to `disabled`. Because disabled inputs do not submit data in HTTP POST payloads, these controls function primarily as visual indicators rather than dynamic system toggles.

In `HubController::savePeopleConnectAgentSettings()`, the backend checks for tool array structures:
```php
$settings['tools'] = $validated['tools'] ?? [];
$settings['skills'] = $validated['skills'] ?? [];
```
To enable runtime activation or deactivation of individual tools, the frontend interface must be updated to emit structured form array arrays (`name="tools[]" value="firestore_sync"`).

---

### 2.3 The Missing Fallback Consumer Bridge
The 3-Tier Fallback resilience configuration (Tab 3) successfully collects primary and secondary model IDs and persists them cleanly into the database via `HubController`:

```php
$settings['fallback_models'] = array_filter($validated['fallback_models'] ?? []);
if (! empty($validated['primary_model_id'])) {
    $settings['model_id'] = $validated['primary_model_id'];
}
$agent->update(['settings' => $settings]);
```

However, as revealed during our earlier review of Phase 4 (`GenerateContactReplyDraftJob` and `PeopleConnectAnalysisService`), current conversational processing relies on simulated responses or simple model executions. The message generators do not yet implement an iterative try/catch fallback loop over `$agent->settings['fallback_models']`.

```mermaid
graph LR
    classDef ui fill:#1e293b,stroke:#3b82f6,stroke-width:2px,color:#fff;
    classDef db fill:#1f2937,stroke:#10b981,stroke-width:2px,color:#a7f3d0;
    classDef missing fill:#450a0a,stroke:#ef4444,stroke-width:2px,stroke-dasharray: 5 5,color:#fca5a5;

    A[Studio Tab 3 UI:<br/>Fallback Selects] ::: ui -->|POST Form Save| B[MySQL Database:<br/>agents.settings JSON] ::: db
    B -.-x|Missing Consumer Loop| C[GenerateContactReplyDraftJob /<br/>Message Reply Engine] ::: missing
```

---

## 3. Summary & Next Step

Our micro-audit has mapped the current implementations of the Studio UI and controller endpoints:
- **Core capabilities** such as System Prompts, Multi-Key Rotation (`EncryptedApiKeyStorage`), and JSON hyperparameter storage operate as production models.
- **Identified Gaps** center around deterministic agent selection, connecting static capabilities UI toggles to persistent arrays, and linking saved fallback arrays into an active runtime execution loop.

How do we bridge these disconnected layers and complete the transition from static mockups to fully autonomous AI message handling?

In **Task 21 (Architectural Roadmap to Autonomous Execution)**, we synthesize our findings into a clear execution roadmap to complete Phase 5.
