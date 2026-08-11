# NexusV3 — Monolithic AI Ecosystem

> **A Laravel 13 AI-first platform for contact management, cognitive AI orchestration, workflow automation, and real-time messaging.**  
> Base Domain: `https://n.soulyeg.online/`

---

## 🎯 Project Purpose & Objectives (وظيفة المشروع وأهدافه)

### 📌 Purpose (الهدف العام)
NexusV3 is designed as a high-performance, event-driven, monolithic AI platform that unifies multi-channel communication (WhatsApp/WAHA), AI agent orchestration, intelligent CRM contact profiles, semantic vector memories, and automated workflow triggers under a single unified architecture.

### 🌟 Core Objectives (الأهداف الرئيسية)
1. **Cognitive CRM Intelligence**: Transforming traditional contact data into dynamic AI profiles with confidence-scored memories, topics, notes, and live telemetry.
2. **Multi-Agent Orchestration**: Coordinating autonomous AI agents (Hedra Soul "Souly") with tool definitions, skill sets, and step-by-step task execution.
3. **Multi-Provider AI Routing**: Providing zero-downtime, budget-aware failover and load balancing across OpenAI, Anthropic, Gemini, Groq, and custom LLM endpoints.
4. **Zero-Latency Real-Time Telemetry**: Real-time WebSocket updates via Laravel Reverb for chats, device monitoring, and system metrics.
5. **Complete System Observability**: Offering dynamic public metadata APIs for system routes, database schema, codebase reflection (Controllers/Services), documentation, and Blade views.

---

## 📡 Public Metadata APIs & Browser Links (No Auth Required)

All system metadata endpoints are publicly accessible without authentication for system monitoring, developer inspection, and browser preview.

### 🌐 Browser Interactive Web Explorer
Open directly in your browser:
- 🖥️ **System Explorer Dashboard**: `https://n.soulyeg.online/system`
- 🛣️ **Routes Explorer**: `https://n.soulyeg.online/system/routes`
- 🗄️ **DB Schema Explorer**: `https://n.soulyeg.online/system/schema`
- ⚙️ **Codebase Explorer**: `https://n.soulyeg.online/system/codebase`
- 📚 **Documentation Reader**: `https://n.soulyeg.online/system/docs`
- 🖼️ **Blade Views Explorer**: `https://n.soulyeg.online/system/views`
- 📖 **System Readme & Specification**: `https://n.soulyeg.online/system/readme` (or `https://n.soulyeg.online/readme`)

---

### 🔌 API Endpoints Summary (الروابط النهائية للـ APIs)

| Task | Endpoint URL | Method | Auth | Description |
|---|---|---|---|---|
| **Task 1: Routes** | `https://n.soulyeg.online/api/v1/system/routes` | `GET` | **None** | Returns all project routes separated into `api` and `web` with middleware, HTTP methods, actions, and summaries. |
| **Task 2: DB Schema** | `https://n.soulyeg.online/api/v1/system/schema` | `GET` | **None** | Returns complete database schema for all 114 tables, column types, nullable constraints, indexes, and foreign keys. |
| **Task 3: Codebase** | `https://n.soulyeg.online/api/v1/system/codebase` | `GET` | **None** | Reflection analysis of all Controllers and Services, including public method signatures, parameters, return types, and doc comments. |
| **Task 4: Documentation** | `https://n.soulyeg.online/api/v1/system/docs` | `GET` | **None** | Scans and lists all project documentation files (`.md`). Optional query `?file=Documentation/Api/README.md` loads full markdown content. |
| **Task 5: Blade Views** | `https://n.soulyeg.online/api/v1/system/views` | `GET` | **None** | Lists all Blade template files (`.blade.php`), view dot-notation names, categories, file sizes, and extracted UI purpose descriptions. |
| **Task 6: System Readme Spec** | `https://n.soulyeg.online/api/v1/system/readme` | `GET` | **None** | Returns `README.md` project specification content. Dual-purpose: Returns JSON for AI Agents, raw text via `?format=raw`, or rendered UI via browser. |

---

## 🧩 Current Hubs & Functionalities (الـ Hubs الحالية ووظائف كلاً منها)

Nexus is organized into **15 domain-driven hubs**:

| Hub Name | Route Path | Primary Function / Purpose |
|---|---|---|
| **Dashboard** | `/hub/dashboard` | Central system overview, activity feed, real-time system metrics, and operational health. |
| **Contacts Hub** | `/hub/contacts` | Advanced CRM contact management, Studio view, War Room, live device tracking, and intelligence extraction. |
| **People Connect** | `/hub/people-connect` | Real-time chat console integrated with WhatsApp (WAHA) for instant direct messaging and session history. |
| **Hedra Soul** | `/hub/hedra-soul` | AI cognitive core ("Souly") executing complex multi-step user prompts and conversational memory. |
| **AI Models Hub** | `/hub/models` | Dynamic AI model router, provider API key management, cost budget charts, and playground testing. |
| **Agents Hub** | `/hub/agents` | Multi-agent framework for creating, configuring, toggling, and assigning automated agent tasks. |
| **Workflows Hub** | `/hub/workflows` | Graphical automation workflow builder, schedule management, and execution logger. |
| **Tasks Hub** | `/hub/tasks` | Kanban task board for heterogeneous manual, agent, and system tasks with sub-task checklists. |
| **Memory Hub** | `/hub/memory` | Semantic memory storage with confidence scoring, tag associations, and temporal versioning. |
| **Proactive AI** | `/hub/proactive-ai` | Event-Condition-Action (ECA) rule engine triggering proactive AI interactions based on user events. |
| **Scheduler** | `/hub/scheduler` | Cron job scheduling management and monitoring. |
| **Logs Hub** | `/hub/logs` | Dual-write structured audit logger for API requests, errors, and system events. |
| **Settings Hub** | `/hub/settings` | System-wide configuration editor, cache flushing, and environment toggles. |
| **Admin Hub** | `/hub/admin` | Dead Letter Queue (DLQ) inspection, process management, and developer tools. |
| **WAHA Hub** | `/hub/waha` | WhatsApp HTTP API session management, QR code scanning, and synchronization controls. |

---

## ⚙️ Project Tech Specs (الـ Project Tech Specs)

| Category | Specification |
|---|---|
| **PHP Runtime** | PHP 8.4 (Compatible with PHP 8.3+) |
| **Framework** | Laravel 13 (Latest Monolith Architecture) |
| **Frontend Rendering** | Blade Templates, Vanilla JS, Vite, CSS Grid/Flexbox |
| **Database Engine** | MySQL 8.0+ / PostgreSQL |
| **Caching & Queues** | Redis + Laravel Horizon v5 |
| **Real-time WebSockets** | Laravel Reverb v1 (Port 8080) |
| **API Authentication** | Laravel Sanctum v4 |
| **Code Style & Linting** | Laravel Pint v1 |
| **Testing Suite** | PHPUnit 11 Feature & Unit Tests |
| **Development Profiling** | Laravel Telescope v5, Laravel Debugbar, Pail |

---

## 🗄️ Database Schema Summary (ملخص هيكلية قاعدة البيانات)

Nexus database architecture consists of **114 tables** divided into core domain categories:

- **Contacts & CRM**: `contacts`, `contact_notes`, `contact_tags`, `contact_custom_fields`, `contact_rules`, `devices`.
- **Conversations & Messaging**: `conversations`, `messages`, `conversation_sessions`, `topics`.
- **AI & Agents Core**: `ai_models`, `api_keys`, `agents`, `agent_tools`, `agent_skills`, `agent_tasks`, `task_steps`.
- **Memory & Intelligence**: `memories`, `memory_tags`, `intelligence_extractions`.
- **Workflows & Schedules**: `workflows`, `workflow_executions`, `schedules`.
- **System & Auditing**: `settings`, `logs`, `sessions`, `jobs`, `failed_jobs`.

*Inspect full column types, indexes, and foreign keys interactively at `/system/schema` or via `GET /api/v1/system/schema`.*

---

## 📚 Documentation Index (فهرس الوثائق والـ Docs)

All technical documentation is organized in the [`Documentation/`](Documentation/) directory:

### 📍 Main Documentation (`Documentation/Main-Files/`)
- [`PROJECT_OVERVIEW.md`](Documentation/Main-Files/PROJECT_OVERVIEW.md) — Comprehensive architecture and project vision.
- [`SYSTEM_ARCHITECTURE.md`](Documentation/Main-Files/SYSTEM_ARCHITECTURE.md) — Monolithic Hub architecture and data flows.
- [`API_DESIGN.md`](Documentation/Main-Files/API_DESIGN.md) — API standards, endpoints, and response formats.
- [`DEVELOPER_QUICK_START.md`](Documentation/Main-Files/DEVELOPER_QUICK_START.md) — Environment setup guide.
- [`COMPREHENSIVE_FEATURES_LIST.md`](Documentation/Main-Files/COMPREHENSIVE_FEATURES_LIST.md) — Complete feature breakdown.
- [`Data_Models.md`](Documentation/Main-Files/Data_Models.md) — Data dictionary and Eloquent model definitions.
- [`THIRD_PARTY_INTEGRATIONS.md`](Documentation/Main-Files/THIRD_PARTY_INTEGRATIONS.md) — Integrations with WAHA, Mem0, and LLM providers.

### 📍 API & Swagger Docs (`Documentation/Api/`)
- [`POSTMAN_API_DOCUMENTATION.md`](Documentation/Api/POSTMAN_API_DOCUMENTATION.md) — Postman collection setup.
- [`SWAGGER_AND_POSTMAN_GUIDE.md`](Documentation/Api/SWAGGER_AND_POSTMAN_GUIDE.md) — OpenAPI / Swagger UI guide.
- [`API_QUICK_REFERENCE.md`](Documentation/Api/API_QUICK_REFERENCE.md) — Cheat sheet of core API endpoints.

### 📍 Integrations & Hub Deep Dives (`Documentation/Integrations/` & `Documentation/The-Hubs/`)
- [`Integrations/WAHA.md`](Documentation/Integrations/WAHA.md) — WhatsApp HTTP API integration details.
- [`Integrations/Mem0.md`](Documentation/Integrations/Mem0.md) — Vector memory engine setup.
- [`Integrations/MCP.md`](Documentation/Integrations/MCP.md) — Model Context Protocol servers guide.
- [`The-Hubs/PeopleConnectHub/PeopleConnect_WAHA_Integration.md`](Documentation/The-Hubs/PeopleConnectHub/PeopleConnect_WAHA_Integration.md) — Real-time WhatsApp sync architecture.

---

## 🚀 Quick Start & Development

```bash
# Install PHP & JS Dependencies
composer install && npm install

# Generate Application Key & Run Migrations
php artisan key:generate
php artisan migrate --seed

# Start All Development Services
composer run dev
```

---

## 📄 License
MIT License