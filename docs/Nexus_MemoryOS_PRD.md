# Nexus MemoryOS — Product Requirement Document (PRD) & Technical Spec

> **Status:** Live / Living Document  
> **Single Source of Truth (SSOT):** Central Architecture & Requirements Specification for Nexus MemoryOS  
> **Last Updated:** August 01, 2026  
> **Author / Maintainers:** Hedra & Souly (سولي)  

---

## 1. Overview & Goals

### 1.1 High-Level Vision
**Nexus MemoryOS** is the cognitive memory, session intelligence, and contextual persistence engine for the Nexus multi-agent ecosystem. It bridges individual user interactions, multi-agent workflows, and cross-platform communication channels into a unified, high-performance memory structure.

### 1.2 Core Objectives
- **Single Source of Truth (SSOT):** Consolidate all historical context, standing profiles, decisions, and system capabilities in one synchronized repository.
- **Contextual Persistence:** Enable seamless session recall, cross-platform history search, and persistent knowledge retrieval across tools (Honcho, Local Session DB, File Logs).
- **Zero Friction for Hedra:** Eliminate repeated instruction loops, automate context updates, and support immediate execution with zero manual intervention.

---

## 2. Architecture & Stack

### 2.1 Core Infrastructure & Environment
- **Project Base Directory:** `/www/wwwroot/Nexus/core/Nexus3`
- **Framework:** Laravel 13 (PHP / Artisan)
- **Database Layer:** MySQL / SQLite / Redis (Session Caching)
- **Inference & Gateway Routing:** LiteLLM Proxy (`:8889`), Custom Open AI-compatible Providers
- **Search & Indexing:** Local FTS5 SQLite Session DB + Hybrid Semantic/Keyword Search (Honcho Engine)

### 2.2 System Diagram (Conceptual Flow)
```
┌─────────────────────────────────────────────────────────────┐
│                    Client / Interaction                     │
│    (Telegram 'hms' / WhatsApp / Web / API / OpenClaw)      │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                 Nexus MemoryOS Core Routing                 │
│         - Context Ingestion & Session Search (FTS5)         │
│         - Standing Memory & Honcho Dialectic Engine         │
└──────────────────────────────┬──────────────────────────────┘
                               │
            ┌──────────────────┴──────────────────┐
            ▼                                     ▼
┌───────────────────────────────┐   ┌───────────────────────────┐
│     Persistent Storage        │   │    Agent Workspaces       │
│ - Docs (PRD / Specs / ADR)    │   │ - Souly / Pola / Nexus    │
│ - Memory Logs & DB            │   │ - Custom Profile Context  │
└───────────────────────────────┘   └───────────────────────────┘
```

---

## 3. Core Modules & Features

### 3.1 Session & Context Ingestion
- Real-time indexing of conversation streams across multi-channel integrations (Telegram, WhatsApp, Web, API).
- Fast multi-turn session retrieval (`session_search`) with FTS5 search and message-window scrolling.

### 3.2 Standing Profiles & Conclusions (Honcho Integration)
- Atomically managed peer cards, user profile snapshots, and factual conclusions (`honcho_conclude`, `honcho_profile`).
- Dialectic reasoning layer for nuanced context queries (`honcho_reasoning`).

### 3.3 Dynamic Living Logs (`/root/nexus/`)
- **`HedraLifeProblems.md`:** Tracking LP-00X challenges, mood/energy awareness, and workflow friction mitigations.
- **`HedraAgentsBusiness.md`:** Commercial AI Agent product strategies (B2B, Class A/B Egyptian enterprise tiers).
- **`IdeasTasks.md`:** Backlog for features, ideas, and system expansions.

---

## 4. Data Models & APIs

### 4.1 Document Structure Specs
- **PRD Location:** `docs/Nexus_MemoryOS_PRD.md`
- **Markdown Encoding:** UTF-8, Markdown-compliant, Git-tracked.

### 4.2 Key Endpoints & Internal Interfaces
- **Hermes AGENT API:** Exposed at port `8642` (`/v1/chat/completions`) for OpenAI-compatible client access.
- **WAHA WhatsApp API:** Local service at `127.0.0.1:3000` (`waha.soulyeg.online`).

---

## 5. Architecture Decision Records (ADR)

| ADR ID | Date | Decision Summary | Rationale / Context |
| :--- | :--- | :--- | :--- |
| **ADR-001** | 2026-08-01 | **Establish SSOT Document** | Create `docs/Nexus_MemoryOS_PRD.md` to track all MemoryOS design decisions, architectures, and features in one place. |
| **ADR-002** | 2026-08-01 | **Direct Execution & Zero-Touch** | All specification updates and feature implementations must be committed directly with zero unnecessary prompts or manual steps. |

---

## 6. Backlog / Pending Questions

- [ ] Define full API schema for MemoryOS context synchronization webhook.
- [ ] Finalize hybrid indexing strategy between SQLite FTS5 local DB and Honcho vector store.
- [ ] Map out multi-agent memory boundary rules for specialized sub-agents (Pola / Nexus workers).

---
*End of Specification — Managed live by Hedra & Souly.*

## 7. Memory Lifecycle & Components (Part 1)

### 7.1 Technical Stack
- **Vector Stores:** ChromaDB & Ragflow (Hybrid implementation).
- **Databases:** MySQL (Relational) & Redis (Caching/Session).
- **Time-Series/Analytics:** Clickhouse (To track the evolution and change of information over time).
- **Knowledge Graph:** Neo4j (To map relationships between topics, people, facts, lessons, and events).
- **Management Engine:** A custom lightweight engine with a UI for storing, reviewing, and retrieving JSON and Markdown files.
- **Orchestration:** n8n (Built-in + Community nodes) to build all workflows for extraction, processing, saving, and retrieval.

### 7.2 Memory Extraction & Processing
- **Time-Series Mirroring:** Every extracted memory, conclusion, or piece of info must be mirrored in a time-series database, linked by ID to the original source. This allows tracking how information evolves or is affected by time.
- **Agent-Driven Chunking:**
    - **Logic:** An Agent determines chunk boundaries based on topics and token limits.
    - **Closure:** Chunks must end at the conclusion of a topic unless the token limit is reached first.
    - **Concurrent Extraction:** During chunking, the agent extracts keywords and topics, assigning a unique ID.
    - **Anti-Hallucination:** Large sources must be split into smaller files to prevent LLM hallucinations.
- **Source-Specific Extraction:** Logic varies based on source type (Human-Human, Human-Agent, Agent-Agent, Documents, Profiles, Contacts).

### 7.3 Structured Linking (The Hub Model)
- **Topics Hub:** A structured "Topics" table serves as the central link connecting:
    - $ightarrow$ Relevant Chunks (Conversations, Memories) via IDs/Keywords/Metadata.
    - $ightarrow$ Time-series data (Evolution).
    - $ightarrow$ Entity/Graph data (Neo4j).
- **Interconnectivity:** Strong relational links between Topics $leftrightarrow$ Persons $leftrightarrow$ Facts $leftrightarrow$ Lessons $leftrightarrow$ Events.

### 7.4 Context Components
- **Temporal Context:** Date, Day, and Hour.
- **Daily Summaries:**
    - **General Agent:** Comprehensive summary of all daily events, tasks, and details for the primary Life/Work orchestrator.
    - **Specialized Agents:** Focused summary of results and events specific to their domain.

### 7.5 Entity Memory Models (Detailed Attribute Mapping)
*Target: 100+ detailed attributes per entity type.*

- **User $leftrightarrow$ Other Persons:**
    - Basic info, facts, memories, general personality.
    - Person's personality/behavior specifically toward the user.
    - Traits, events, promises, relationships, joys, pains, assets, strengths, and weaknesses.
- **User (Hedra):**
    - Strengths, weaknesses, goals, facts, memories, personality, traits, events, promises, tasks, plans, assets, and preferences.
- **Agents:**
    - Rules, preferences, skills, experiences, lessons, memories, tools, and knowledge boards.
- **Agent $leftrightarrow$ Person:**
    - Interaction rules, shared memories, person's preferences, and user-specific notes about the person.
- **Agent $leftrightarrow$ Agent:** (Defined in Part 2).

### 7.6 Operational Hierarchy & Decomposition
- **Breakdown Logic:** Goal $ightarrow$ Phases $ightarrow$ Tasks $ightarrow$ Sub-tasks.
- **Granularity:** Each level is divided into units (Minimum 10, Maximum 30).
- **Execution Constraints:** A hard limit on the total volume of work executable in a single cycle.
