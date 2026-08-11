# 14. Studio UI & Frontend Interactive Components

To manage autonomous conversational AI in enterprise messaging environments, operators need an intuitive control console. In the Nexus3 architecture, this is delivered via **AI Agent Settings & Studio** (`/hub/people-connect/agent-settings`), implemented in `resources/views/hubs/people-connect-agent-settings.blade.php`.

Designed as a neon-glass interface, the console abstracts deep system configurations into **four distinct interactive operational tabs**, allowing seamless management of system prompts, provider inspection, sequential fallbacks, and encryption key pools.

---

## 1. Architectural UI Anatomy & Tab Structure

```mermaid
graph TD
    classDef console fill:#1e1e2e,stroke:#8b5cf6,stroke-width:2px,color:#fff;
    classDef tab fill:#2b2d42,stroke:#4f46e5,stroke-width:1px,color:#d8b4fe;
    classDef comp fill:#111827,stroke:#34d399,stroke-width:1px,color:#a7f3d0;

    A[AI Command Center: Agent Settings & Studio<br/><b>/hub/people-connect/agent-settings</b>] ::: console
    
    A --> T1[Tab 1: Persona & Behavior Studio<br/><i>#persona</i>] ::: tab
    A --> T2[Tab 2: Provider & Model Inspector<br/><i>#providers</i>] ::: tab
    A --> T3[Tab 3: 3-Tier Fallback Pipeline<br/><i>#fallbacks</i>] ::: tab
    A --> T4[Tab 4: Multi-Key Rotation Engine<br/><i>#keys</i>] ::: tab

    T1 --> C1[System Prompt Monospace Editor<br/>Temperature Slider with DOM binding<br/>Max Tokens Counter & Capabilities] ::: comp
    T2 --> C2[Dark Table: Providers, API URLs,<br/>Driver Protocol & Model Tags] ::: comp
    T3 --> C3[Sequential Fallback Node Stack<br/>Primary -> Tier 1 -> Tier 2 -> Tier 3] ::: comp
    T4 --> C4[Key Pool List & Masked Strings<br/>LRU Algorithm & Cooldown Badges] ::: comp
```

---

## 2. Neon-Glass Design & Styling Scaffolding

The studio utilizes a custom design aesthetic (`.studio-shell`) constructed on top of Bootstrap and dark-mode CSS variables. Notice how backdrop blurring and border opacities create depth and separation across components:

```css
.studio-shell {
    background: rgba(11, 14, 20, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    backdrop-filter: blur(20px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
    overflow: hidden;
}
.nav-tabs-custom .nav-link.active {
    color: #8b5cf6;
    background: transparent;
    border-bottom: 2px solid #8b5cf6;
}
.glass-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 12px;
    padding: 24px;
}
```

---

## 3. Tab-by-Tab Frontend Feature Analysis

### 3.1 Tab 1: Persona & Behavior Studio (`#persona`)
This view centers on conversational personality and execution hyperparameter constraints:
- **System Prompt Editor:** Renders a 12-row monospace textarea (`font-size: 0.88rem;`) where operators specify conversational rules, boundaries, and linguistic styling.
- **Interactive Temperature Slider:** A dynamic UI component configured with attributes `min="0"`, `max="2"`, `step="0.1"`. To provide immediate tactile feedback without server requests, an inline event listener maps slider adjustments directly into the interface indicator:
  ```html
  <input type="range" class="form-range" min="0" max="2" step="0.1" name="temperature" id="tempSlider" 
         value="{{ $agent ? ($agent->settings['temperature'] ?? 0.7) : 0.7 }}" 
         oninput="document.getElementById('tempValue').innerText=this.value">
  ```
- **Active Agent Capabilities Indicators:** A dedicated sidebar card displays core runtime engine integrations (`Real-Time Firestore Sync Pipeline`, `Auto-Exhaustion API Key Rotation`, `3-Tier Fallback Resilience Engine`). These switches render in a locked `checked disabled` state, visually informing operators that these infrastructure layers operate as immutable framework features.

---

### 3.2 Tab 2: Provider & Model Inspector (`#providers`)
Presents a responsive relational data grid displaying every AI engine integration currently registered in the database:
- **Provider Identity & Endpoint Identification:** Displays provider names, system slugs, and API endpoint targets inside dark code blocks (`<code class="text-info bg-dark">`).
- **Protocol Driver Mapping:** Highlights the driver interface (`REST / OPENAI`, `ANTHROPIC`, `OLLAMA`) using styled status indicators.
- **Model Array Tagging:** Iterates over `@forelse($prov->models as $m)` to display active model identifiers (such as `gpt-4o`, `claude-3-5-sonnet`, `deepseek-r1`) as inline pill tags.
- **Health Indicators:** Uses directional logic (`$prov->status === 'active'`) to switch between green operational badges and red alert flags.

---

### 3.3 Tab 3: 3-Tier Fallback Resilience Pipeline (`#fallbacks`)
Visualizes sequential failure redirection through an interactive vertical node structure:
- Each tier is enclosed inside `.fallback-node` cards connected by gradient visual elements (`.fallback-connector`).
- **Primary Model (Tier 0):** Selected from available models as highest priority for outgoing conversations.
- **Fallback Tier #1 (Triggered on HTTP 429 / 500 errors):** Captures failed requests and redirects context to a backup provider.
- **Fallback Tier #2 & Tier #3 (Last Resort):** Serves as an ultimate fallback before dispatching error events to dashboard operators.

---

### 3.4 Tab 4: Multi-Key Rotation Engine (`#keys`)
Provides operational management for API keys operating under a **Round-Robin LRU (Least Recently Used) algorithm**:
- **Security Masking:** Prevents credential exposure by rendering only the last four characters of any stored secret key (`&bull;&bull;&bull;&bull;&bull;&bull;{{ substr($key->api_key, -4) }}`).
- **Cooldown Countdown Visualizer:** Uses Carbon time checking (`\Carbon\Carbon::parse($key->cooldown_until)->isFuture()`) to toggle between two operational badges:
  - **Active State:** `<span class="badge badge-active-key"><i class="fa-solid fa-check"></i> ACTIVE IN POOL</span>`
  - **Exhaustion Cooldown State:** `<span class="badge badge-cooldown"><i class="fa-solid fa-clock"></i> COOLDOWN UNTIL 14:30</span>`

---

## 4. Summary & Next Step

We have successfully reviewed the interface structure and interactive components driving the four studio tabs. When an operator clicks **"Save All Settings"**, how are these complex form structures, hyperparameters, and JSON definitions handled by the database layer?

In **Task 18 (Persona Definition & Hyperparameters Backend)**, we examine `App\Models\Agent` and `AgentPersona` to see how typed columns and JSON casting support runtime configurations.
