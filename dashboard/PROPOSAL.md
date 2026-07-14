# Nexus Dev — Antigravity Control Dashboard (Proposal)

> Status: **PROPOSAL — awaiting Hedra approval (2026-07-14)**. Nothing built yet.
> Built per `112233` deep-research protocol (EN+CN+verify).

## 1. Goal
A self-hosted dashboard at `n.soulyeg.online/dev` (inside the Nexus project) that lets Hedra monitor, drive, and verify the headless Antigravity dev agent — without opening the Antigravity UI. Souly (orchestrator) uses this backend to: decompose tasks, send prompts, poll status, verify, and report.

## 2. What the research said (grounding)
- **Agent UX best practices (fuselab, CSDN, read.ai4pm):** transparency, override/human-in-loop, progressive delegation, attention-guiding, thinking-exposed, pause-feedback.
- **Multi-agent dashboards (ClawPort, Mission Control, claw-pilot):** task board, cost tracking, cron monitor, real-time orchestration, budget caps.
- **Antigravity Automation REST API (verified endpoints):**
  - `POST /toggle_auto_run` — auto-click Run
  - `POST /toggle_auto_allow` — auto-click Allow
  - `POST /send_command` — send prompt/command to active chat
  - `POST /start-new-chat` / `POST /switch_chat` — manage sessions
  - WebSocket `ws://localhost:9812` — live chat stream
  - `http://localhost:5000` — push/poll feed
  - (Freemium gating on auto-run/allow — note for production)

## 3. Proposed Features (8 modules)

| # | Feature | What it does | Source principle |
|---|---------|--------------|------------------|
| F1 | **Agent Status** | running/idle/done, current task_id, model, thinking level, uptime | observability |
| F2 | **Task Board (Kanban)** | TODO → In Progress → Done; per-task Abort/Retry; maps to Souly's decomposition | ClawPort/Mission Control |
| F3 | **Live Log / Terminal** | streaming agent output (ws://localhost:9812) + manual command box | real-time |
| F4 | **Chat-with-Agent** | textarea → `POST /send_command`; shows ack + result | human-in-loop |
| F5 | **Model Switcher** | Flash 3.5 / Pro 3.1 / Claude + thinking low/med/high (Souly default logic) | progressive delegation |
| F6 | **Project Tree** | browse `/www/wwwroot/Nexus/core/Nexus3` live; click file → preview | transparency |
| F7 | **Reports Feed** | Souly's short progress + final "done X,Y" messages | reporting |
| F8 | **Credentials (masked)** | shows active key index (1/2/3) without exposing values; auto-rotate indicator | security |

## 4. UI Shape & Style
- **Stack (matches Nexus frontend, per Hedra):** Blade-like static HTML + **jQuery + Bootstrap 5 + Ajax**; Vue 3 ONLY if a widget gets complex later. No Livewire/Next.js (Hedra removed them).
- **Layout:** top navbar (Nexus Dev · project path · connection status) → responsive CSS-grid of cards (F1–F8). Dark theme (GitHub-dark inspired) for low eye-strain during long dev sessions.
- **Interaction:** real-time updates via WebSocket; auto-refresh fallback every 3s via Ajax poll to localhost:5000.
- **Controls:** every autonomous action has an explicit **Override/Pause** button (human-in-loop). Souly never auto-deploys; Hedra approves via this panel.
- **Branding:** small "⚡ Nexus Dev" mark; Souly avatar touch in header.

## 5. Build steps (after approval)
1. Static `index.html` + `app.js` (jQuery) in `/www/wwwroot/Nexus/core/Nexus3/dashboard/`.
2. `app.js`: connect `ws://localhost:9812`, poll `localhost:5000`, wire F4→`/send_command`, F2→task state.
3. Small PHP route `/hub/dev` (HubController) or a standalone Blade page serving the dashboard (per Laravel Boost conventions — check sibling hub controllers first).
4. Expose via **aaPanel** vhost: `n.soulyeg.online/dev` → dashboard folder (Souly does NOT touch nginx).
5. Wire Souly backend (Python/Node) to push Reports (F7) + status (F1) from the orchestration loop.

## 6. Open questions for Hedra
- Q1: Standalone Blade page under `/hub/dev` OR separate static site? (Laravel Boost says check existing hub pattern first.)
- Q2: Keep it read+chat only first, or also add Abort/Retry buttons now?
- Q3: Auth on `/dev`? (Sanctum guard, since Nexus already has auth.)
