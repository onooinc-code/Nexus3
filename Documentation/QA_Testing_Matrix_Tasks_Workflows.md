# Comprehensive QA Testing Matrix: TasksHub & WorkflowHub

**Target Environment:** `https://n.soulyeg.online/hub/*`
**Tester Name:** **********\_**********
**Date of Testing:** ********\_\_********
**Browser/Device:** ********\_\_\_********

---

## 📋 General Testing Guidelines

- Open the Developer Console (`F12` -> `Console` tab) during testing to monitor for JavaScript errors or failed AJAX requests.
- Pay attention to network requests (`F12` -> `Network` tab) to ensure `TaskAPI` handles CSRF tokens correctly.
- Mark the result as **[ ] Pending**, **[ ] Passed**, **[ ] Failed**, or **[ ] Blocked**.
- Fill out the "Actual Result & Observations" accurately to assist the development team.

---

# Part 1: TasksHub Module (`/hub/tasks`)

## 1. Dashboard & Core Layout

### Feature: Top Navigation & Layout (F02, F03)

- **Steps to Test:**
    1. Navigate to `https://n.soulyeg.online/hub/tasks`.
    2. Verify the glassmorphism design loads correctly.
    3. Click on the tabs in the top navigation: `Dashboard`, `List`, `Board`, `Queue`, `Automations`.
- **Expected Result:** The content area switches instantly via AJAX without a full page reload. Active tab gets highlighted.
- **Tester Input / Execution Details:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

### Feature: Dashboard Live Stats & Charts (F05, F06)

- **Steps to Test:**
    1. Open the `Dashboard` tab.
    2. Observe the 10 statistic cards at the top.
    3. Scroll down to view the 4 Chart.js visualizations (Timeline, Status Distribution, etc.).
- **Expected Result:** Counters should animate from 0 to their current values. Charts should render correctly and show tooltips on hover.
- **Tester Input / Execution Details:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

### Feature: AI Insights & Activity Feed (F07, F08)

- **Steps to Test:**
    1. Open the `Dashboard` tab.
    2. Review the right sidebar containing the AI Insights and Activity Feed.
- **Expected Result:** Activity Feed should display recent events. If WebSocket (Laravel Echo) is active, new tasks created in another tab should appear here live.
- **Tester Input / Execution Details:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

## 2. List View & Management

### Feature: DataTables & Filters (F09, F10)

- **Steps to Test:**
    1. Click on the `List` tab.
    2. Verify the table loads tasks.
    3. Click the `Filters` button to reveal the advanced filter panel.
    4. Select a specific status (e.g., `failed`) and apply.
- **Expected Result:** The table updates via Server-Side Processing showing only failed tasks.
- **Tester Input / Filter Values Used:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

### Feature: Quick View Sidebar (F12)

- **Steps to Test:**
    1. In the `List` tab, click on any task row (not the checkbox).
- **Expected Result:** A sidebar slides in from the right edge showing task details, priority progress bar, and a live log terminal.
- **Tester Input / ID of Task Clicked:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

## 3. Kanban Board

### Feature: Drag & Drop (F13)

- **Steps to Test:**
    1. Click on the `Board` tab.
    2. Drag a task card from the `Todo` column to `In-Progress`.
- **Expected Result:** The card snaps into the new column. An AJAX request (`PATCH /tasks/{id}/status`) is sent. A success notification appears on screen. The column counters update.
- **Tester Input / ID of Task Moved:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

## 4. Task Creation

### Feature: Master Task Create Modal (F14)

- **Steps to Test:**
    1. Click the green `+ New Task` button in the top navigation.
    2. Select `Agent Task` from the left sidebar of the modal.
    3. Start typing "Fix database bug" in the Title input.
    4. Set Priority slider to `8`.
    5. Fill tags: `db,bug`.
    6. Click `Deploy Task`.
- **Expected Result:** As you type the title, the "Routing Prediction" should guess an agent (e.g., `DB_Admin` or `Code_Reviewer`). On save, modal closes, success toast appears, and tables refresh.
- **Tester Input / Values Entered:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

## 5. Task Detail Page

### Feature: Full Details & State Machine (F15, F16)

- **Steps to Test:**
    1. Navigate to `/hub/tasks/1` (replace `1` with a valid task ID).
    2. Observe the State Machine Visualizer (the circles and lines).
- **Expected Result:** The State Machine correctly highlights the current status (Todo, In-Progress, Completed, or turns Red if Failed).
- **Tester Input / Task ID Used:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

## 6. Queue Monitor & Automations

### Feature: Queue Gauges & Dead Letter Queue (F20, F21)

- **Steps to Test:**
    1. Go to `/hub/tasks` -> `Queue` tab.
    2. Check the circular Gauges and the Dead Letter Queue table.
- **Expected Result:** Gauges render properly via Chart.js. DLQ loads tasks that have exhausted retries.
- **Tester Input / Execution Details:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

---

# Part 2: WorkflowHub Module (`/hub/workflows`)

## 1. Workflow Listing & Creation

### Feature: Workflows DataTable & Layout

- **Steps to Test:**
    1. Navigate to `https://n.soulyeg.online/hub/workflows`.
    2. Verify the list of available workflows is displayed.
- **Expected Result:** DataTables loads the workflows successfully.
- **Tester Input / Execution Details:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

## 2. Workflow Execution & Diagram

### Feature: Visual Workflow Diagram

- **Steps to Test:**
    1. Click on a specific workflow to view its details.
    2. Locate the graphical map/diagram of the workflow nodes and edges.
- **Expected Result:** The nodes (Triggers, Actions) and connections are drawn correctly.
- **Tester Input / Workflow ID Used:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

### Feature: Trigger Workflow Execution

- **Steps to Test:**
    1. In the workflow details view, click the `Execute Workflow` button.
    2. If it requires a payload, enter a valid JSON payload.
    3. Submit the execution.
- **Expected Result:** The execution starts. The UI transitions to show the live execution trace, and node statuses update as the backend processes them.
- **Tester Input / JSON Payload Used:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

### Feature: Live Execution Trace

- **Steps to Test:**
    1. Open an active Execution trace link (`/hub/workflows/executions/{id}`).
    2. Watch the status updates.
- **Expected Result:** The steps (Pending -> Running -> Success/Failed) update dynamically via Polling or WebSockets. Logs stream into the inspector panel.
- **Tester Input / Execution ID Used:** ************\_************
- **Result Status:** [ ] Passed | [ ] Failed
- **Actual Result & Observations (Errors/Logs):** ************\_************

---

## 📝 Final Sign-off

**Testing Duration:** ******\_\_\_******
**Total Bugs Found:** ******\_\_\_******
**General Comments for Developers:**

---

---
