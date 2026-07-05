# Original User Request

## Initial Request — 2026-07-05T02:08:14+03:00

Build a comprehensive, enterprise-grade UI for the "AI Models Hub" inside an existing Laravel application (Blade + Bootstrap 5 + jQuery + Chart.js + Laravel Echo/Reverb already set up). The current `resources/views/hubs/models.blade.php` is a very basic placeholder (168 lines). Replace it entirely and build all sub-pages as per the detailed specifications below.

Working directory: c:/Users/hedra/Desktop/N-V3/Nexus
Integrity mode: development

---

## CONTEXT & EXISTING INFRASTRUCTURE

- **Layout:** All views extend `layouts.app` — use `@extends('layouts.app')`, `@section('content')`, `@push('scripts')`.
- **CSS variables and classes already available:** `bg-dark`, `.card`, `.hover-3d`, `.animate-fade-in`, `.stagger-*`, custom CSS variables like `--nexus-blue`, `--nexus-teal`, `--nexus-border`, `--text-primary`, `--text-secondary`.
- **JS already loaded globally:** jQuery, Bootstrap 5, Chart.js, DataTables, NProgress, Laravel Echo (connected to Reverb WebSocket server), `window.Nexus.notify()`, `window.Nexus.showTaskLoader()`.
- **Sidebar:** `AIModelsHub` link already exists in sidebar pointing to `/hub/models` (currently renders `models.blade.php`).
- **API Routes available on the backend:**
  - `GET /api/v1/ai/providers` — list providers
  - `POST /api/v1/ai/providers` — create provider
  - `GET/PUT/DELETE /api/v1/ai/providers/{id}` — manage provider
  - `POST /api/v1/ai/providers/{id}/sync-models` — sync models
  - `POST /api/v1/ai/providers/{id}/test` — test connectivity
  - `PATCH /api/v1/ai/providers/{id}/toggle-active` — toggle active
  - `GET /api/v1/ai/models` — list models
  - `GET/POST/PUT/DELETE /api/v1/ai/models/{id}` — manage model
  - `POST /api/v1/ai/models/{id}/test` — test model with prompt
  - `POST /api/v1/ai-hub/route` — route AI request
  - `GET /api/v1/ai-hub/provider-health` — provider health scorecard
  - `GET /api/v1/ai-hub/audit-trail` — audit logs (filterable)
  - `GET /api/v1/ai-hub/telemetry` — telemetry stats
  - `GET /api/v1/ai-hub/routing-matrix` — routing matrix
  - `POST /api/v1/ai/route-intent` — update intent route
  - `GET /api/v1/ai/cost/forecast` — cost forecast
  - `POST /api/v1/ai/cost/budget` — set budget

---

## REQUIREMENTS

### R1. Main Entry: AI Models Hub Dashboard Page
**File:** `resources/views/hubs/models.blade.php` (overwrite completely)

Build a fully tabbed single-page layout (no full page reload between tabs) with these top-level tabs:
`📊 Dashboard` | `🏢 Providers` | `🤖 Models` | `🔑 API Keys` | `🔀 Intent Routing` | `💰 Cost & Budget` | `🧪 Playground` | `📋 Logs & Audit`

Each tab loads its content either inline or via AJAX-fetched blade partials stored in `resources/views/hubs/partials/ai-hub/`.

---

### R2. Tab 1 — Dashboard (Global Telemetry)

**A. Live Health Bar (fixed ribbon at top of content area):**
- 🟢/🟡/🔴 System Status badge (fetched from `/api/v1/ai-hub/provider-health`)
- Live Active Requests counter (WebSocket via `window.Echo`)
- Total Tokens/Min rate
- Est. Today's Cost (from telemetry)
- Emergency Kill Switch button (shows confirmation modal)

**B. 6 Summary Cards (top row, clickable):**
| Card | Data Source | Visual | On Click |
|------|-------------|--------|----------|
| Total Requests 24h | telemetry | Sparkline (Chart.js) | Filter Logs |
| Success Rate % | telemetry | Colored circle ring | Error breakdown modal |
| Avg Latency P50 | provider-health | Gauge indicator | Latency histogram |
| Total Cost (Month) | cost/forecast | Thin progress bar | Go to Cost tab |
| Active Providers | provider-health | Colored dots | Go to Providers tab |
| Cache Hit Rate % | telemetry | Doughnut mini | Go to Cache Inspector |

**C. Charts Section:**
- Token Timeline (Line, 7d): dual-line Input vs Output, filter by Hour/Day/Week/Month
- Cost by Provider (Stacked Bar): one color per provider, drill-down on click
- Request Volume Heatmap (GitHub-style grid): 7 rows × 24 cols colored by request density
- Routing Distribution: visual flow showing Intent→Model routing percentages

**D. Performance Leaderboard table:**
- Fastest Models (Top 3 by avg latency)
- Most Expensive (Top 3 by cost/request)
- Highest Error Rate (Top 3)

**E. Active Alerts Panel:** collapsible with alert cards (Critical/Warning/Info), `Investigate` and `Snooze 15m` buttons.

---

### R3. Tab 2 — Providers Management

**A. Provider List:**
Card Grid view (default) + Table View toggle.

**Provider Card:**
- Provider name + status badge (Active/Degraded/Unreachable/Disabled)
- Base URL
- Models count synced + Active Keys count
- Health Sparkline: 24-point mini bar chart of latency last 1h
- Budget Progress Bar: colored green/amber/red by % used (green < 60%, amber 60-80%, red > 80%)
- Quick stats: Today ($ + tokens + reqs) and Month ($ + tokens + reqs)
- Buttons: `[Ping]` `[Sync Models]` `[Edit]` `[Disable/Enable]`

**Ping:** calls `/api/v1/ai/providers/{id}/test`, shows modal with HTTP status, latency, response.
**Sync Models:** calls sync endpoint, shows slide-in drawer with progress + synced model list.

**B. Add/Edit Provider — right-side Offcanvas Drawer:**

Section 1 — Identity:
- Provider Name (quick-select: OpenAI / Anthropic / Google Gemini / Groq / Ollama / Custom)
- Base URL + inline `[Test Connection]` button (✅ 127ms or ❌ Timeout)
- Models Fetch Endpoint
- Generate Endpoint
- Auth Header Format (Select: Bearer {key} / x-api-key: {key} / Custom)

Section 2 — Payload Format:
- Select: OpenAI-Compatible / Anthropic / Google Gemini / Custom
- If Custom → JSON template textarea

Section 3 — Security:
- API Key (password + Reveal toggle)
- Key Label/Name
- Monthly Budget Cap

Section 4 — Advanced:
- Auto-Sync Models toggle + Frequency
- Circuit Breaker Threshold
- Request Timeout ms
- Max Retries

Footer: `[Cancel]` `[Test & Save]`

**C. Provider Detail Page (clicking provider name opens full-page with 5 tabs):**
- Overview: expanded stats + 7d latency chart + 90d uptime timeline strip
- Models: table of models belonging to this provider
- API Keys: keys filtered to this provider
- Usage Analytics: charts filtered to this provider
- Logs: audit trail filtered to this provider

---

### R4. Tab 3 — Models Management

**A. Model Library — data-dense table:**
Columns: Model Name (+ provider icon) | Provider (colored badge) | Context Window (visual bar `▓▓░░ 128K`) | Input Cost | Output Cost | Quality Tier badge | Status (inline toggle) | Last Synced | Actions

**Filter bar:** Provider (multi-select) | Quality Tier | Context Window range slider | Cost range | Status | Last Synced | Search | Favorites ★

**B. Model Detail Page:**
- Header: model name + status + provider + tier + context size
- 6 Stat cards: Cost/Month, Requests, Avg Latency P50/P95, Avg Input Tokens, Avg Output Tokens, Budget Cap Progress Bar
- Charts: Cost per Day (Bar, 30d) | Latency per Day (Line) | Token Distribution Histogram
- Context Window Visualizer (text bars showing Model Limit / Avg Usage / Max Seen)
- Pricing History table
- Intent Routes list (which routes use this model)
- Settings: Monthly Cap (inline edit) | Tags (add/remove pills) | Default Parameters | Deprecation Date

---

### R5. Tab 4 — API Keys Management

**A. Mini Summary Bar:** `Total Keys: 18 | Active: 14 | Expiring Soon: 2 | Exhausted: 1 | Revoked: 1`

**B. Quick Filter Tabs:** All | Active | ⚠ Near Limit | ❌ Exhausted | 🔒 Revoked | ⭐ Favorites

**C. Advanced Filter Bar:**
- Provider (multi-select) | Status | Budget Status | Usage level | Date range | Search | Sort by

**D. Key Card (expanded row for each key):**
```
⭐ Production Key #1    sk-...****8f2a    [OpenAI]  ● Active
─────────────────────────────────────────────────────────
Budget Usage:  ████████████████░░░░░░░░  $48.20 / $80.00  (60.3%)
Token Usage:   ████████░░░░░░░░░░░░░░░░  3.2M / 5M tokens (64%)

Today: 1,240 reqs │ 890K tokens │ $8.20    Last Used: 3 mins ago
Month: 18,430 reqs │ 3.2M tokens │ $48.20   Created: 2026-05-01

Success Rate: 98.4% ████████████████████ │ Errors: 23 (429: 18, 500: 5)

[Analytics] [Edit Budget] [Rotate] [Revoke] [⋮ More]
```

Progress bars: green < 60%, amber 60-80%, red > 80%.

**E. Per-Key Analytics Drawer (right-side offcanvas):**
- 8 Stat cards with trend arrows vs last month
- 4 Charts: Requests per Hour Today (Bar) | Daily Cost 30d (Line) | Error Distribution (Doughnut) | Token Breakdown per Day (Stacked Bar)
- Last 50 Requests table with [Replay] button
- Settings: Budget Cap, Daily Limit, Alert thresholds, Alert method, Auto-action at 100%

**F. Add Key Modal — 4 Steps:**
1. Provider + Label + API Key (with live validation on Continue)
2. Budget Cap / Daily Limit / Rate Limit / Expiry
3. Alert thresholds + method
4. Review + Save & Encrypt

**G. Lifecycle Actions:** Rotate (grace period) | Revoke (with warning of affected routes) | Archive

---

### R6. Tab 5 — Intent Routing Engine

**A. Routing Matrix:**
Each intent row:
```
general_chat
Primary: [Gemini 1.5 Pro] via [Google]    Fallback: [GPT-4o] [OpenAI]
Traffic Today: 4,320 reqs │ 98.1% via Primary │ 1.9% Fallback
Avg Cost: $0.0021/req │ Avg Latency: 720ms
Rules: [Cost-Optimized] [Standard-Latency]
[Edit Route] [View Logs] [Test This Intent]
```
All 9 known intents pre-listed: general_chat | data_extraction | summarization | embedding | fast_response | reasoning | contact_extraction | intent_classification | agent_execution

**B. Route Rule Builder Drawer:**
Condition builder: IF [condition ▼] [operator ▼] [value] → THEN Route to [Provider + Model] → ELSE Route to [Provider + Model]
Conditions: prompt_length, cost_profile, latency_profile, language, security_class, time_of_day, workspace_id

**C. Visual Fallback Chain Editor:**
Horizontal flow: [Primary] → (fails) → [Fallback 1] → (fails) → [Fallback 2]
Drag to reorder, Edit/Remove per node, [+ Add Fallback]

**D. A/B Traffic Split:**
```
Gemini 1.5 Pro  [███████░░░] 70%  [−][+]
GPT-4o          [███░░░░░░░] 30%  [−][+]
Total: 100% | Purpose: [text] | Duration: [dates] | Goal: [metric buttons]
```

---

### R7. Tab 6 — Cost & Budget Center

**A. Financial Summary Header:**
```
Month-to-Date Spend:  $148.20
Monthly Budget:       $250.00   ████████████░░░░░░░░  59.3%
Remaining:            $101.80  │  Days Left: 26
Projected Month End:  $185.50  ✅ Under Budget
Daily Burn Rate:      $5.93/day
```
Data from `GET /api/v1/ai/cost/forecast`.

**B. 5 Charts:**
- Cost Breakdown by Provider (Doughnut)
- Cost by Intent (Horizontal Bar)
- Cost by Model (Treemap or nested bars)
- Daily Cost Trend 30 Days (Line with budget + projection lines)
- Unit Economics table: Cost per Request | Cost per 1K tokens per model

**C. Budget Manager Table:** Scope | Monthly Cap | Spent | % | Progress Bar | Action on Exceed | Edit
Rows: Global / Per Provider / Per Model / Per Intent

**D. Edit Budget Modal:**
- Monthly Cap | Daily Cap | Per-Request Max
- Action: Alert Only / Route to Cheapest Fallback / Block Requests / Alert + Block
- Thresholds: 50% / 75% / 90% / 100% checkboxes

---

### R8. Tab 7 — Pro AI Playground (4 sub-tabs)

**A. Chat Tester:**
Left sidebar: Provider + Model dropdowns | OR Intent-based toggle | Sliders (Temperature, Max Tokens, Top-P, Frequency Penalty) | System Prompt textarea | Load/Save Preset | Clear Chat | Export JSON

Chat area: Streaming response rendering (EventSource or WebSocket)
Per-message metadata footer: Model | TTFT | Total latency | Tokens in/out | Cost | Fallback Yes/No
Per-message buttons: Copy | Regenerate | 👍 Rate | 🔁 Replay in Battle

Input: auto-resize textarea + token estimator + Send button

**B. Multi-Model Battle:**
2-4 column split (user chooses)
Shared prompt input at bottom → sends to ALL simultaneously
Each column streams independently
Battle Results table after all complete:
| Metric | Model A | Model B | Model C |
Metrics: TTFT | Total Latency | Input Tokens | Output Tokens | Cost | Quality Score
Winner badge 🏆 per metric
Buttons: Save Comparison | Open in Playground | Export Report

**C. Prompt Registry:**
Grid of saved templates, each showing: name + version + usage count + preview + avg quality/cost + last used
Buttons: Open in Playground | Compare Versions | Edit | Delete
Version diff view (side-by-side)

**D. Background Job Simulator:**
LEFT — Config: Job Type dropdown | JSON payload editor | Queue selector | Delay | Dispatch button
RIGHT — Live Monitor: Job ID + Status | Progress bar | Timeline log | Live WebSocket Event Console showing streamed tokens
Previous Jobs table: Job ID | Type | Status | Duration | Cost | Timestamp

---

### R9. Tab 8 — Logs & Audit (3 sub-tabs)

**A. Request Logs:**
Advanced filter bar: text search | Provider/Model/Intent/Status dropdowns | Date range | Latency filter | Cost filter | Fallback filter | Cache filter | Error filter | Apply/Reset/Save Preset

Expanded log row:
```
2026-07-04 14:32:15  │  general_chat  │  gpt-4o-mini (OpenAI)
🟢 200 OK │ ⏱️ 820ms │ 📝 123/456 tokens │ 💰 $0.00042 │ 🔄 No fallback
📦 Cache: MISS │ Workspace: Acme Corp
Decision: Primary route selected. Circuit: CLOSED. Cost profile matched.
[View Details] [Replay in Playground] [Copy as cURL] [Tag]
```

Log Detail Drawer (4 tabs):
- Overview: all metadata + Decision Explainability Tree (visual tree showing routing decision chain)
- Request/Response: formatted JSON with Copy buttons
- Performance Timeline: horizontal bar chart showing each processing stage (Key Decrypt / Payload Adapt / API Call / Response Parse)
- Related: links to Job ID / Conversation ID / User ID

**B. Cache Inspector sub-tab:**
Summary stats: Hit Rate | Total Hits | Total Misses | Cost Saved | Avg Cache Age
Cache Entries table: MD5 Key | Intent | Prompt Preview | Hits | Created | Expires | [Delete]
Buttons: Flush All Cache | Flush by Intent
Cache Effectiveness Line Chart (Hit Rate trend 7 days)

---

### R10. Hub Global Settings

A settings section (can be an additional tab or linked page `/hub/models/settings`) with 3 cards:

1. Circuit Breaker: Failure Threshold | Recovery Timeout | Success Threshold | Per-Provider Override toggle
2. Semantic Cache: Enabled toggle | TTL | Similarity Threshold (slider) | Max Size | Embedding Model | [Flush Cache Now]
3. Notification Alerts: Channel checkboxes (In-App/Email/Slack/Discord) | Alert trigger checkboxes

---

## DESIGN REQUIREMENTS

- Dark mode matching Nexus design system
- Use existing CSS variables (`--nexus-blue`, `--nexus-teal`, `--text-primary`, `--nexus-border`, etc.)
- Cards: `class="card hover-3d border-0 shadow-sm"` with `style="background: rgba(22, 27, 34, 0.5); backdrop-filter: blur(10px);"`
- Progress bars: auto-color: green < 60%, amber 60-80%, red > 80%
- Animations: `.animate-fade-in`, `.stagger-1`, `.stagger-2` etc.
- Charts: Chart.js (globally loaded)
- Tables: DataTables (globally loaded)
- Toasts: `window.Nexus.notify(message, type)`
- Loading: `window.Nexus.showTaskLoader()` / `window.Nexus.hideTaskLoader()`
- WebSocket: `window.Echo` (already configured for Reverb)
- CSRF: all AJAX POST must include `X-CSRF-TOKEN: $('meta[name="csrf-token"]').attr('content')`
- Font: Inter (already loaded in layout)
- Icons: FontAwesome 6 (already loaded)

---

## ACCEPTANCE CRITERIA

### Completeness
- [ ] `models.blade.php` contains full tabbed UI with all 8 tabs
- [ ] Each provider card shows budget progress bar + health sparkline
- [ ] Each API key shows 2 progress bars (Budget % + Token %)
- [ ] Intent Routing matrix shows all 9 known intents
- [ ] Playground has working streaming chat interface
- [ ] Log rows show Decision Explainability text
- [ ] Job Simulator shows live WebSocket timeline

### Integration
- [ ] All data fetched from real backend APIs
- [ ] Provider Ping calls real test endpoint and shows actual latency
- [ ] Adding/Editing Provider saves to backend
- [ ] Adding/Revoking API Key calls correct endpoint
- [ ] Routing matrix loads from `/api/v1/ai-hub/routing-matrix`
- [ ] Cost forecast loads from `/api/v1/ai/cost/forecast`

### UX Quality
- [ ] Progress bars change color based on % thresholds
- [ ] Charts animate on load
- [ ] DataTables used for tables with > 10 rows
- [ ] Streaming renders tokens progressively
- [ ] All actions show success/error toasts via `window.Nexus.notify()`
