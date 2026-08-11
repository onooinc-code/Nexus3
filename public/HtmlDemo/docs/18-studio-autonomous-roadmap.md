# 18. Architectural Roadmap to Autonomous Execution

Our deep dive into Phase 4 (AI Workflows & Logic Status) and Phase 5 (AI Agent Studio & Settings) has revealed the architectural baseline of PeopleConnect. While core scaffolding—such as Redis locking, 3-Tier fallback configuration arrays, encrypted LRU key rotation, and Event-Condition-Action (ECA) evaluations—is already established in code, certain connection layers currently function as placeholders or operate strictly under supervised rules (Copilot Mode).

This document serves as the **Engineering Roadmap to Autonomous Execution**, detailing four structural milestones to bridge disconnected system capabilities and enable automated message handling.

---

## 1. Roadmap to Autopilot Architecture

```mermaid
graph TD
    classDef mstone fill:#1e1e2e,stroke:#8b5cf6,stroke-width:2px,color:#fff;
    classDef act fill:#064e3b,stroke:#10b981,stroke-width:1px,color:#d1fae5;
    classDef target fill:#312e81,stroke:#6366f1,stroke-width:2px,color:#e0e7ff;

    M1[Milestone 1:<br/>NLP Engine Integration] ::: mstone --> M2[Milestone 2:<br/>Fallback Consumer Loop] ::: mstone
    M2 --> M3[Milestone 3:<br/>Autopilot Threshold Gating] ::: mstone
    M3 --> M4[Milestone 4:<br/>Deterministic Studio Binding] ::: mstone

    M1 --- A1[Replace static analysis stubs with<br/>live requests to AiModelsHub] ::: act
    M2 --- A2[Implement iterative retry loop over<br/>fallback_models array in message generation] ::: act
    M3 --- A3[Bypass pending_approval when<br/>confidence >= 0.85 & safety flags = 0] ::: act
    M4 --- A4[Bind Studio UI to explicit config<br/>and enable dynamic tool toggles] ::: act

    M4 --> END((Fully Autonomous<br/>Production Engine)) ::: target
```

---

## 2. Detailed Execution Milestones

### Milestone 1: Bridging the NLP Analysis Engine
* **Current State:** Both `PeopleConnectAnalysisService` and `WahaAnalysisService` rely on static fallback logic, returning fixed responses (`'intent' => 'general_inquiry'`, `'sentiment' => 'neutral'`, `'language' => 'ar'`).
* **Implementation Objectives:**
  1. Replace static returns in `PeopleConnectAnalysisService::analyze()` with an asynchronous execution call to `AiModelsHub`.
  2. Implement an operational prompt classifier (e.g., using `claude-3-5-sonnet` or a lightweight fine-tuned model) configured to produce standardized JSON response payloads:
     ```json
     {
       "topic": "billing_support",
       "intent": "refund_request",
       "sentiment": "frustrated",
       "urgency": "high",
       "safety_flags": []
     }
     ```
  3. Store derived intelligence directly inside `peopleconnect_message_analyses` and bind emotional markers to `peopleconnect_messages.emotional_baseline_snapshot`.

---

### Milestone 2: Implementing the 3-Tier Fallback Consumer Loop
* **Current State:** The Studio interface allows users to select primary and backup models, storing these configurations as structured JSON arrays inside `agents.settings['fallback_models']`. However, the current generation loop executes only against a single primary provider endpoint without implementing retry alternatives when exceptions occur.
* **Implementation Objectives:**
  1. Update `GenerateContactReplyDraftJob::handle()` to evaluate available fallback sequences during processing:
     ```php
     $modelsToTry = array_merge(
         [$agent->settings['model_id'] ?? config('ai.default_model_id')],
         $agent->settings['fallback_models'] ?? []
     );
     ```
  2. Wrap generative calls inside an iterative `foreach` block containing specific error handling for provider faults:
     ```php
     foreach ($modelsToTry as $modelId) {
         try {
             $response = $aiEngine->execute($modelId, $contextPayload);
             break; // Exit loop on successful payload reception!
         } catch (ProviderRateLimitException | ProviderOutageException $e) {
             Log::warning("Model tier [$modelId] failed. Escalating to fallback...");
             continue; // Iterate to next model in the fallback pipeline
         }
     }
     ```

---

### Milestone 3: Activating Autopilot Mode & Threshold Verification
* **Current State:** Because of supervised safety policies, `GenerateContactReplyDraftJob` sets generated replies to `'status' => 'pending_approval'`, stopping automatic messaging until an operator manually approves the response in the UI.
* **Implementation Objectives:**
  1. Inspect the contact's operational reply rule by reviewing `peopleconnect_reply_mode_overrides.reply_mode` (checking between `manual`, `copilot`, and `autopilot`).
  2. Implement an automated decision gate before queuing outbound transmissions:
     ```php
     $mode = $contact->replyModeOverride->reply_mode ?? 'copilot';
     $analysis = $message->analysis;

     if ($mode === 'autopilot' && empty($analysis->safety_flags) && $analysis->confidence >= 0.85) {
         $draft->update(['status' => 'approved', 'approved_by' => null]);
         
         // Trigger real-time background dispatch without manual intervention!
         SendWahaMessageJob::dispatch($draft->id)->onQueue('high-priority');
     } else {
         $draft->update(['status' => 'pending_approval']);
     }
     ```

---

### Milestone 4: Deterministic Studio Binding & Tool Persistence
* **Current State:** `HubController::peopleConnectAgentSettings()` uses fuzzy database string matching (`where('description', 'like', '%people%connect%')`) to select which AI agent to display, while capability toggles render as static visual indicators without functional parameters.
* **Implementation Objectives:**
  1. Store an explicit environment identifier (`PEOPLE_CONNECT_DEFAULT_AGENT_ID` in `.env` or system configuration tables) to ensure consistent agent bindings.
  2. Modify `people-connect-agent-settings.blade.php` to include valid array parameter assignments (`name="tools[]" value="firestore_sync"`) so form submissions correctly persist capability selections directly into the database via `HubController::savePeopleConnectAgentSettings()`.

---

## 3. Summary & Phase 5 Completion

Completing this four-milestone integration sequence bridges existing structural components to transform PeopleConnect from a supervised copilot workspace into an automated messaging platform capable of continuous background processing.

This completes **Phase 5: Deep Audit of AI Agent Settings & Studio**. To provide a solid technical foundation for incoming engineers, we begin **Phase 6: Comprehensive Reference Matrices & API Specifications** by documenting the core schema tables driving the application.
