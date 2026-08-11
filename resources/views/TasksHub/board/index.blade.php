<div class="h-100 d-flex flex-column">
    <!-- Board Control Header Panel -->
    <div class="tasks-glass-card p-3 mb-3" id="board-control-panel">
        <!-- Header Title & Quick Action Triggers -->
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-sliders text-primary fs-5"></i>
                <h6 class="mb-0 text-light fw-bold font-inter" style="letter-spacing: 0.5px;">Board Control</h6>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 rounded-pill ms-1" style="font-size: 0.65rem;">60 Features Active</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-xs btn-outline-primary" id="btn-quick-spawner" title="Quick Task Spawner"><i class="fa-solid fa-plus me-1"></i>New Task</button>
                <button class="btn btn-xs btn-outline-info" id="btn-keyboard-help" data-bs-toggle="modal" data-bs-target="#modal-board-shortcuts" title="Keyboard Shortcuts"><i class="fa-solid fa-keyboard me-1"></i>Shortcuts</button>
                <button class="btn btn-xs btn-outline-secondary" id="btn-toggle-panel-collapse" title="Collapse Control Board"><i class="fa-solid fa-chevron-up"></i></button>
            </div>
        </div>

        <!-- Control Panel Body -->
        <div id="board-control-body">
            <!-- Toolbar Row 1: Visibility & Filters -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                <!-- Column Visibility Toggles -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted font-mono me-1" style="font-size: 0.65rem; text-transform: uppercase;">Columns:</span>
                    <div class="btn-group btn-group-sm" role="group" id="col-vis-toggles">
                        <button class="btn btn-xs btn-outline-secondary active" data-col="todo">Todo</button>
                        <button class="btn btn-xs btn-outline-primary active" data-col="in-progress">In Progress</button>
                        <button class="btn btn-xs btn-outline-warning active" data-col="blocked">Blocked</button>
                        <button class="btn btn-xs btn-outline-success active" data-col="completed">Completed</button>
                        <button class="btn btn-xs btn-outline-danger active" data-col="failed">Failed</button>
                    </div>
                </div>

                <!-- Task Type Filters -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted font-mono me-1" style="font-size: 0.65rem; text-transform: uppercase;">Type:</span>
                    <div class="btn-group btn-group-sm" role="group" id="type-vis-toggles">
                        <button class="btn btn-xs btn-outline-secondary active" data-type="manual">Manual</button>
                        <button class="btn btn-xs btn-outline-secondary active" data-type="agent">Agent</button>
                        <button class="btn btn-xs btn-outline-secondary active" data-type="system">System</button>
                        <button class="btn btn-xs btn-outline-secondary active" data-type="code">Code</button>
                        <button class="btn btn-xs btn-outline-secondary active" data-type="api">API</button>
                        <button class="btn btn-xs btn-outline-secondary active" data-type="terminal">Terminal</button>
                        <button class="btn btn-xs btn-outline-secondary active" data-type="tool">Tool</button>
                    </div>
                </div>

                <!-- Priority Quick Filters -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted font-mono me-1" style="font-size: 0.65rem; text-transform: uppercase;">Priority:</span>
                    <div class="btn-group btn-group-sm" role="group" id="priority-vis-toggles">
                        <button class="btn btn-xs btn-outline-danger" data-priority="critical">Critical (>7)</button>
                        <button class="btn btn-xs btn-outline-warning" data-priority="high">High (5-7)</button>
                        <button class="btn btn-xs btn-outline-info" data-priority="normal">Normal (3-4)</button>
                        <button class="btn btn-xs btn-outline-secondary" data-priority="low">Low (&lt;3)</button>
                        <button class="btn btn-xs btn-outline-light active" data-priority="all">All</button>
                    </div>
                </div>
            </div>

            <!-- Toolbar Row 2: Density, Zoom, Sorting & Themes -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2 pt-2 border-top border-secondary border-opacity-10">
                <!-- Zoom Canvas Controls -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted font-mono me-1" style="font-size: 0.65rem; text-transform: uppercase;">Zoom:</span>
                    <button class="btn btn-xs btn-dark border-secondary" id="btn-zoom-out" title="Zoom Out (-15%)"><i class="fa-solid fa-minus"></i></button>
                    <span class="font-mono text-light px-2" id="zoom-indicator" style="font-size: 0.75rem;">100%</span>
                    <button class="btn btn-xs btn-dark border-secondary" id="btn-zoom-in" title="Zoom In (+15%)"><i class="fa-solid fa-plus"></i></button>
                    <button class="btn btn-xs btn-outline-secondary ms-1" id="btn-zoom-reset" style="font-size: 0.65rem;">Reset</button>
                </div>

                <!-- View Density & Card Flipping -->
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted font-mono" style="font-size: 0.65rem; text-transform: uppercase;">Density:</span>
                    <div class="btn-group btn-group-sm" id="view-density-group">
                        <button class="btn btn-xs btn-outline-secondary active" id="btn-density-normal">Normal</button>
                        <button class="btn btn-xs btn-outline-secondary" id="btn-density-compact">Compact</button>
                        <button class="btn btn-xs btn-outline-secondary" id="btn-density-detailed">Detailed</button>
                    </div>
                    <button class="btn btn-xs btn-outline-info ms-2" id="btn-toggle-card-flip"><i class="fa-solid fa-rotate-3d me-1"></i>3D Flip</button>
                </div>

                <!-- Sorting & Theme Selector -->
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted font-mono" style="font-size: 0.65rem; text-transform: uppercase;">Sort:</span>
                        <select id="board-sort-select" class="form-select form-select-sm bg-dark text-light border-secondary" style="font-size: 0.7rem; width: 130px; padding: 2px 20px 2px 8px;">
                            <option value="default">Default Order</option>
                            <option value="priority-desc">Priority High -&gt; Low</option>
                            <option value="priority-asc">Priority Low -&gt; High</option>
                            <option value="date-desc">Newest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="title-asc">Title A-Z</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-1 ms-2">
                        <span class="text-muted font-mono" style="font-size: 0.65rem; text-transform: uppercase;">Theme:</span>
                        <select id="board-theme-select" class="form-select form-select-sm bg-dark text-light border-secondary" style="font-size: 0.7rem; width: 120px; padding: 2px 20px 2px 8px;">
                            <option value="status">Status Based</option>
                            <option value="priority">Priority Heat</option>
                            <option value="agent">Agent Color</option>
                            <option value="type">Type Color</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Toolbar Row 3: Bulk Actions, Layouts & Export Tools -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2 border-top border-secondary border-opacity-10">
                <!-- Bulk Column Actions -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted font-mono me-1" style="font-size: 0.65rem; text-transform: uppercase;">Bulk Actions:</span>
                    <button class="btn btn-xs btn-outline-danger" id="btn-retry-failed" title="Retry All Failed"><i class="fa-solid fa-rotate-left me-1"></i>Retry Failed</button>
                    <button class="btn btn-xs btn-outline-warning" id="btn-pause-running" title="Pause All Running"><i class="fa-solid fa-pause me-1"></i>Pause Running</button>
                    <button class="btn btn-xs btn-outline-success" id="btn-start-all-todo" title="Start All Todo"><i class="fa-solid fa-play me-1"></i>Start Todo</button>
                    <button class="btn btn-xs btn-outline-secondary" id="btn-archive-completed" title="Archive Completed"><i class="fa-solid fa-box-archive me-1"></i>Archive Done</button>
                </div>

                <!-- Display Toggles & Multi-Select -->
                <div class="d-flex align-items-center gap-2">
                    <div class="form-check form-switch m-0 d-flex align-items-center gap-1">
                        <input class="form-check-input mt-0" type="checkbox" id="switch-focus-mode">
                        <label class="form-check-label text-muted font-mono" for="switch-focus-mode" style="font-size: 0.65rem;">Focus Dim</label>
                    </div>

                    <div class="form-check form-switch m-0 d-flex align-items-center gap-1 ms-2">
                        <input class="form-check-input mt-0" type="checkbox" id="switch-multiselect">
                        <label class="form-check-label text-muted font-mono" for="switch-multiselect" style="font-size: 0.65rem;">Multi-Select</label>
                    </div>

                    <div class="form-check form-switch m-0 d-flex align-items-center gap-1 ms-2">
                        <input class="form-check-input mt-0" type="checkbox" id="switch-swimlanes">
                        <label class="form-check-label text-muted font-mono" for="switch-swimlanes" style="font-size: 0.65rem;">Swimlanes</label>
                    </div>
                </div>

                <!-- Export & Fullscreen -->
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-xs btn-outline-secondary" id="btn-export-json" title="Export Board to JSON"><i class="fa-solid fa-file-code me-1"></i>JSON</button>
                    <button class="btn btn-xs btn-outline-secondary" id="btn-export-csv" title="Export Board to CSV"><i class="fa-solid fa-file-csv me-1"></i>CSV</button>
                    <button class="btn btn-xs btn-outline-secondary" id="btn-toggle-fullscreen" title="Fullscreen Mode"><i class="fa-solid fa-expand me-1"></i>Fullscreen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Glassmorphic Command Bar 2.0 (Clean 2-Tier Architecture) -->
    <div class="command-bar-glass mb-3" id="board-command-bar">
        
        <!-- Tier 1: Primary Command Bar -->
        <div class="command-bar-row-primary">
            <!-- Left Group: New Task + Regex Icon Button -->
            <div class="d-flex align-items-center gap-2">
                <button class="btn cmd-bar-btn btn-primary btn-new-task-animated shadow-sm" id="btn-cmd-quick-spawner" data-bs-toggle="modal" data-bs-target="#modal-create-task">
                    <i class="fa-solid fa-plus-circle me-1"></i>
                    <span class="fw-bold font-inter">New Task</span>
                </button>

                <button class="btn cmd-bar-icon-btn btn-outline-secondary" id="btn-toggle-regex" title="Toggle Regular Expression Search (Regex)">
                    <i class="fa-solid fa-code" id="regex-icon"></i>
                </button>
            </div>

            <!-- Center Group: Omnibox Search Bar -->
            <div class="tasks-search-pill-container" style="width: 380px;">
                <i class="fa-solid fa-magnifying-glass tasks-search-icon"></i>
                <input type="text" id="board-search" class="form-control tasks-search-pill" placeholder="Search cards, payloads, IDs...">
                <i class="fa-solid fa-circle-xmark tasks-search-clear" id="btn-clear-search" title="Clear Search (Esc)"></i>
                <span class="tasks-search-kbd">F</span>
            </div>

            <!-- Right Group: Refresh Icon Button + Live Sync Button -->
            <div class="d-flex align-items-center gap-2">
                <button class="btn cmd-bar-icon-btn btn-outline-secondary" id="btn-refresh-board" title="Refresh Board Data (Hotkey: R)">
                    <i class="fa-solid fa-rotate" id="refresh-icon"></i>
                </button>

                <button class="btn cmd-bar-btn live-sync-btn" id="btn-toggle-live-sync" title="Click to Toggle Live WebSocket Sync">
                    <span class="live-pulse-dot" id="live-sync-dot"></span>
                    <span class="font-mono" id="live-sync-text">Live Sync</span>
                </button>
            </div>
        </div>

        <!-- Tier 2: Dual Footer Panels (Equal Width Grid Layout) -->
        <div class="command-bar-row-secondary">
            <!-- Left Panel: Quick Filter Chips (5 Equal Width Columns) -->
            <div class="cmd-footer-panel" id="quick-filter-chips">
                <span class="filter-chip" data-chip-filter="agent"><i class="fa-solid fa-robot text-primary"></i> Agent</span>
                <span class="filter-chip" data-chip-filter="critical"><i class="fa-solid fa-fire text-danger"></i> Critical</span>
                <span class="filter-chip" data-chip-filter="failed"><i class="fa-solid fa-circle-exclamation text-danger"></i> Failed</span>
                <span class="filter-chip" data-chip-filter="running"><i class="fa-solid fa-play text-info"></i> Running</span>
                <span class="filter-chip text-muted" id="btn-reset-quick-chips" style="display: none;"><i class="fa-solid fa-xmark"></i> Clear</span>
            </div>

            <!-- Right Panel: Quiet Status Metric Pills (5 Equal Width Columns) -->
            <div class="cmd-footer-panel" id="status-metric-pills">
                <span class="status-metric-pill status-pill-total active-filter" data-status-filter="all" title="Click to view all cards">
                    <i class="fa-solid fa-layer-group text-secondary"></i> Total: <span id="metric-count-total" class="fw-bold pill-num">0</span>
                </span>
                <span class="status-metric-pill status-pill-running" data-status-filter="in-progress" title="Filter In Progress tasks">
                    <i class="fa-solid fa-circle-play text-indigo"></i> Running: <span id="metric-count-running" class="fw-bold pill-num">0</span>
                </span>
                <span class="status-metric-pill status-pill-failed" data-status-filter="failed" title="Filter Failed tasks">
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i> Failed: <span id="metric-count-failed" class="fw-bold pill-num">0</span>
                </span>
                <span class="status-metric-pill status-pill-blocked" data-status-filter="blocked" title="Filter Blocked tasks">
                    <i class="fa-solid fa-ban text-warning"></i> Blocked: <span id="metric-count-blocked" class="fw-bold pill-num">0</span>
                </span>
                <span class="status-metric-pill status-pill-completed" data-status-filter="completed" title="Filter Completed tasks">
                    <i class="fa-solid fa-check-circle text-success"></i> Done: <span id="metric-count-completed" class="fw-bold pill-num">0</span>
                </span>
            </div>
        </div>

        <!-- Dynamic Bulk Operations Overlay Bar -->
        <div class="command-bulk-overlay" id="command-bulk-overlay">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1 font-mono" style="font-size: 0.75rem;" id="bulk-selected-count">0 Selected</span>
                <span class="text-light font-mono" style="font-size: 0.72rem;">Batch Actions:</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn cmd-bar-btn btn-outline-warning" id="btn-bulk-retry"><i class="fa-solid fa-rotate-left"></i>Retry Selected</button>
                <button class="btn cmd-bar-btn btn-outline-info" id="btn-bulk-pause"><i class="fa-solid fa-pause"></i>Pause Selected</button>
                <button class="btn cmd-bar-btn btn-outline-danger" id="btn-bulk-delete"><i class="fa-solid fa-trash"></i>Delete Selected</button>
                <button class="btn cmd-bar-btn btn-secondary" id="btn-bulk-cancel-select"><i class="fa-solid fa-xmark"></i>Clear Selection</button>
            </div>
        </div>

    </div>

    <!-- Task Details Modal -->
    <div class="modal fade" id="modal-task-details" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content text-light border-0 shadow-lg" style="background: #0B132B; border: 1px solid rgba(56,189,248,0.25) !important; border-radius: 14px;">
                <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary font-mono px-2.5 py-1" id="detail-task-id" style="font-size: 0.8rem;">#0</span>
                        <h5 class="modal-title font-mono text-light mb-0" id="detail-task-title">Task Details</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 font-mono" style="max-height: 75vh; overflow-y: auto;">
                    <!-- Status & Metadata Pills -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-dark border border-secondary px-3 py-1.5" id="detail-task-status"><i class="fa-solid fa-signal me-1"></i> Status: todo</span>
                        <span class="badge bg-dark border border-secondary px-3 py-1.5" id="detail-task-type"><i class="fa-solid fa-cube me-1"></i> Type: agent</span>
                        <span class="badge bg-dark border border-secondary px-3 py-1.5" id="detail-task-priority"><i class="fa-solid fa-fire me-1"></i> Priority: 5/10</span>
                        <span class="badge bg-dark border border-secondary px-3 py-1.5" id="detail-task-created"><i class="fa-solid fa-clock me-1"></i> Created: N/A</span>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="text-cyan mb-2" style="font-size: 0.8rem;"><i class="fa-solid fa-align-left me-1"></i> Description</h6>
                        <div class="p-3 rounded bg-dark bg-opacity-50 text-light border border-secondary border-opacity-25" id="detail-task-desc" style="font-size: 0.85rem;">No description provided.</div>
                    </div>

                    <!-- Sub-tasks Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-cyan mb-0" style="font-size: 0.8rem;"><i class="fa-solid fa-list-check me-1"></i> Sub-tasks (<span id="detail-subtasks-count">0/0</span>)</h6>
                        </div>
                        <div class="progress mb-3" style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px;">
                            <div class="progress-bar bg-info" id="detail-subtasks-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <!-- Add subtask input -->
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary" id="input-new-subtask" placeholder="إضافة مهمة فرعية جديدة...">
                            <button class="btn btn-sm btn-info text-dark font-mono px-3" id="btn-add-subtask"><i class="fa-solid fa-plus me-1"></i> إضافة</button>
                        </div>
                        <!-- Subtask list container -->
                        <div id="detail-subtasks-list" class="d-flex flex-column gap-1">
                            <!-- Rendered subtasks -->
                        </div>
                    </div>

                    <!-- Payload & Execution Logs Tabs -->
                    <ul class="nav nav-tabs border-secondary mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active text-light font-mono" data-bs-toggle="tab" data-bs-target="#tab-task-payload" type="button" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-code me-1"></i> Payload Data
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-light font-mono" data-bs-toggle="tab" data-bs-target="#tab-task-result" type="button" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-terminal me-1"></i> Result & Logs
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-task-payload">
                            <pre class="bg-dark p-3 rounded text-info font-mono border border-secondary border-opacity-25" id="detail-task-payload-pre" style="font-size: 0.75rem; max-height: 200px; overflow: auto;"></pre>
                        </div>
                        <div class="tab-pane fade" id="tab-task-result">
                            <pre class="bg-dark p-3 rounded text-success font-mono border border-secondary border-opacity-25" id="detail-task-result-pre" style="font-size: 0.75rem; max-height: 200px; overflow: auto;"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-warning" id="btn-detail-execute"><i class="fa-solid fa-play me-1"></i> تشغيل المهمة</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-detail-delete"><i class="fa-solid fa-trash me-1"></i> حذف المهمة</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Board Columns Wrapper (Zoom Canvas) -->
    <div id="board-canvas-wrapper" class="flex-grow-1 overflow-x-auto overflow-y-hidden pb-2" style="white-space: nowrap; transform-origin: top left; transition: transform 0.2s ease;">
        <div class="d-flex w-100 gap-3 h-100 px-1" id="kanban-columns-container" style="min-width: 100%;">
            
            <!-- Column: Todo -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="todo" style="max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.02);">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-circle text-secondary me-2" style="font-size: 0.6rem;"></i>Todo</h6>
                        <span class="badge bg-secondary rounded-pill kanban-count">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-maximize-column" data-maximize-column="todo" title="Maximize Column Width">
                            <i class="fa-solid fa-expand" style="font-size: 0.75rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-hide-column" data-hide-column="todo" title="Hide Todo Column">
                            <i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: In Progress -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="in-progress" style="max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(99, 102, 241, 0.05);">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-play text-primary me-2" style="font-size: 0.6rem;"></i>In Progress</h6>
                        <span class="badge bg-primary rounded-pill kanban-count">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-maximize-column" data-maximize-column="in-progress" title="Maximize Column Width">
                            <i class="fa-solid fa-expand" style="font-size: 0.75rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-hide-column" data-hide-column="in-progress" title="Hide In Progress Column">
                            <i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: Blocked -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="blocked" style="max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(245, 158, 11, 0.05);">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-ban text-warning me-2" style="font-size: 0.6rem;"></i>Blocked</h6>
                        <span class="badge bg-warning text-dark rounded-pill kanban-count">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-maximize-column" data-maximize-column="blocked" title="Maximize Column Width">
                            <i class="fa-solid fa-expand" style="font-size: 0.75rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-hide-column" data-hide-column="blocked" title="Hide Blocked Column">
                            <i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: Completed -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="completed" style="max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(16, 185, 129, 0.05);">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-check-double text-success me-2" style="font-size: 0.6rem;"></i>Completed</h6>
                        <span class="badge bg-success rounded-pill kanban-count">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-maximize-column" data-maximize-column="completed" title="Maximize Column Width">
                            <i class="fa-solid fa-expand" style="font-size: 0.75rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-hide-column" data-hide-column="completed" title="Hide Completed Column">
                            <i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: Failed -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="failed" style="max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(239, 68, 68, 0.05);">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation text-danger me-2" style="font-size: 0.6rem;"></i>Failed</h6>
                        <span class="badge bg-danger rounded-pill kanban-count">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-maximize-column" data-maximize-column="failed" title="Maximize Column Width">
                            <i class="fa-solid fa-expand" style="font-size: 0.75rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-hide-column" data-hide-column="failed" title="Hide Failed Column">
                            <i class="fa-solid fa-eye-slash" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Keyboard Shortcuts -->
<div class="modal fade" id="modal-board-shortcuts" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-mono"><i class="fa-solid fa-keyboard text-info me-2"></i>Board Keyboard Shortcuts</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body font-mono style-xs">
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary"><span>Refresh Board</span><kbd class="bg-secondary text-white">R</kbd></div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary"><span>Search Focus</span><kbd class="bg-secondary text-white">F</kbd></div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary"><span>New Task</span><kbd class="bg-secondary text-white">N</kbd></div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary"><span>Zoom In / Out</span><kbd class="bg-secondary text-white">+ / -</kbd></div>
                <div class="d-flex justify-content-between py-1 border-bottom border-secondary"><span>Reset Zoom</span><kbd class="bg-secondary text-white">0</kbd></div>
                <div class="d-flex justify-content-between py-1"><span>Toggle 3D Flip</span><kbd class="bg-secondary text-white">X</kbd></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Button XS Helper */
.btn-xs {
    padding: 2px 8px;
    font-size: 0.68rem;
    line-height: 1.4;
    border-radius: 4px;
}

/* Kanban Card 3D Flip Styling */
.kanban-card {
    background: transparent;
    perspective: 1000px;
    margin-bottom: 10px;
    cursor: grab;
    white-space: normal;
}
.kanban-card-inner {
    position: relative;
    width: 100%;
    transition: transform 0.6s;
    transform-style: preserve-3d;
    background: rgba(30, 41, 59, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 10px;
}
.kanban-card.flipped .kanban-card-inner {
    transform: rotateY(180deg);
}
.kanban-card-front, .kanban-card-back {
    backface-visibility: hidden;
}
.kanban-card-back {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.95);
    border-radius: 8px;
    padding: 10px;
    transform: rotateY(180deg);
    overflow-y: auto;
}

/* Card Density Options */
.kanban-compact .kanban-card-inner {
    padding: 6px 8px;
}
.kanban-compact .kanban-card-title {
    font-size: 0.75rem !important;
    margin-bottom: 2px !important;
}

/* Focus Dimming Effect */
.kanban-card.dimmed {
    opacity: 0.15 !important;
    filter: grayscale(1);
}

/* Theme Variant Borders */
.kanban-card[data-theme="priority-critical"] .kanban-card-inner { border-left: 3px solid var(--tasks-danger); }
.kanban-card[data-theme="priority-high"] .kanban-card-inner { border-left: 3px solid var(--tasks-warning); }
.kanban-card[data-theme="priority-normal"] .kanban-card-inner { border-left: 3px solid var(--tasks-info); }
.kanban-card[data-theme="priority-low"] .kanban-card-inner { border-left: 3px solid var(--tasks-success); }

/* Multi-select styling */
.kanban-card.selected .kanban-card-inner {
    border-color: var(--tasks-primary) !important;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
}

.kanban-card:hover .kanban-card-inner {
    border-color: rgba(255, 255, 255, 0.25);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}
.kanban-card:active { cursor: grabbing; }
.ui-sortable-helper {
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    transform: rotate(2deg);
}
.ui-sortable-placeholder {
    border: 2px dashed rgba(255,255,255,0.2);
    border-radius: 8px;
    background: rgba(255,255,255,0.02);
    visibility: visible !important;
    margin-bottom: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    let boardTasks = []; // Cache
    let boardState = {
        zoom: 100,
        density: 'normal',
        theme: 'status',
        sort: 'default',
        cols: { todo: true, 'in-progress': true, blocked: true, completed: true, failed: true },
        types: { manual: true, agent: true, system: true, code: true, api: true, terminal: true, tool: true },
        priority: 'all',
        focusMode: false,
        isFlipped: false,
        multiSelect: false,
        regexSearch: false,
        selectedIds: new Set(),
        statusFilter: 'all',
        chipFilter: null
    };

    // Load persisted state if exists
    try {
        let saved = localStorage.getItem('nexus_board_control_state');
        if (saved) {
            let parsed = JSON.parse(saved);
            boardState = { ...boardState, ...parsed };
            boardState.selectedIds = new Set();
        }
    } catch(e) {}

    function saveState() {
        try {
            let toSave = { ...boardState, selectedIds: Array.from(boardState.selectedIds) };
            localStorage.setItem('nexus_board_control_state', JSON.stringify(toSave));
        } catch(e) {}
    }

    function renderCard(task) {
        let priorityColor = task.priority > 7 ? '#EF4444' : (task.priority > 4 ? '#F59E0B' : '#10B981');
        let priorityBadge = task.priority > 7 
            ? `<span class="badge bg-danger text-light" style="font-size: 0.62rem;"><i class="fa-solid fa-fire me-1"></i> Critical ${task.priority}/10</span>` 
            : (task.priority > 4 
                ? `<span class="badge bg-warning text-dark" style="font-size: 0.62rem;"><i class="fa-solid fa-bolt me-1"></i> Medium ${task.priority}/10</span>` 
                : `<span class="badge bg-success text-light" style="font-size: 0.62rem;"><i class="fa-solid fa-check me-1"></i> Normal ${task.priority}/10</span>`);
        
        let agentBadge = task.agent_id ? `<span class="badge bg-primary bg-opacity-75 text-light font-mono" style="font-size: 0.62rem;"><i class="fa-solid fa-robot me-1"></i> ${task.agent_id}</span>` : `<span class="badge bg-secondary text-light font-mono" style="font-size: 0.62rem;"><i class="fa-solid fa-gear me-1"></i> ${task.type}</span>`;
        let isFlipped = boardState.isFlipped ? 'flipped' : '';
        let isSelected = boardState.selectedIds.has(task.id) ? 'selected' : '';
        let checkboxHtml = boardState.multiSelect ? `<input type="checkbox" class="form-check-input me-2 card-checkbox" data-id="${task.id}" ${isSelected ? 'checked' : ''}>` : '';
        let createdTime = task.created_at ? new Date(task.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : 'N/A';

        // Subtasks progress calculation
        let subtasks = (task.metadata && Array.isArray(task.metadata.subtasks)) ? task.metadata.subtasks : [];
        let totalSub = subtasks.length;
        let completedSub = subtasks.filter(s => s.completed).length;
        let subtaskProgress = totalSub > 0 ? Math.round((completedSub / totalSub) * 100) : 0;

        let subtaskProgressHtml = '';
        if (totalSub > 0) {
            subtaskProgressHtml = `
            <div class="mt-2.5 pt-2 border-top border-secondary border-opacity-25">
                <div class="d-flex justify-content-between align-items-center font-mono mb-1" style="font-size: 0.65rem;">
                    <span class="text-cyan"><i class="fa-solid fa-list-check me-1"></i> Subtasks</span>
                    <span class="text-light fw-bold">${completedSub}/${totalSub} (${subtaskProgress}%)</span>
                </div>
                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.08); border-radius: 2px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: ${subtaskProgress}%"></div>
                </div>
            </div>`;
        }

        // Payload preview
        let payloadText = '';
        if (task.payload_data) {
            try {
                let p = typeof task.payload_data === 'string' ? JSON.parse(task.payload_data) : task.payload_data;
                payloadText = JSON.stringify(p).substring(0, 70);
            } catch(e) { payloadText = String(task.payload_data).substring(0, 70); }
        }

        return `
        <div class="kanban-card ${isFlipped} ${isSelected}" data-id="${task.id}" data-type="${task.type}" data-priority="${task.priority}" data-status="${task.status}">
            <div class="kanban-card-inner">
                <!-- Card Front -->
                <div class="kanban-card-front p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            ${checkboxHtml}
                            <span class="badge bg-dark border border-secondary text-info font-mono fw-bold" style="font-size: 0.7rem;">#${task.id}</span>
                            ${agentBadge}
                        </div>
                        ${priorityBadge}
                    </div>
                    
                    <h6 class="text-light fw-bold mb-1 kanban-card-title text-truncate" style="font-size: 0.88rem; line-height: 1.3;" title="${task.title}">${task.title}</h6>
                    
                    <p class="text-muted font-mono mb-2" style="font-size: 0.72rem; line-height: 1.4; max-height: 2.8em; overflow: hidden;">
                        ${task.description || (payloadText ? '<i class="fa-solid fa-code me-1 text-info"></i> ' + payloadText : 'No detailed description')}
                    </p>

                    ${subtaskProgressHtml}

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary border-opacity-25">
                        <button class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-0.5 btn-view-task-details" data-id="${task.id}" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-circle-info me-1"></i> التفاصيل
                        </button>
                        <span class="font-mono text-muted" style="font-size: 0.65rem;"><i class="fa-regular fa-clock me-1"></i>${createdTime}</span>
                    </div>
                </div>

                <!-- Card Back (3D Details) -->
                <div class="kanban-card-back p-3 font-mono" style="font-size: 0.7rem;">
                    <div class="d-flex justify-content-between border-bottom border-secondary pb-1 mb-2">
                        <span class="text-info fw-bold">#${task.id} Data View</span>
                        <span class="text-muted">${createdTime}</span>
                    </div>
                    <div class="text-light mb-1">Status: <span class="badge bg-dark border border-info text-info">${task.status}</span></div>
                    <div class="text-light mb-1">Priority: ${task.priority}/10</div>
                    <div class="text-muted mb-2 text-truncate" title="${task.description || 'No description'}">${task.description || 'No detailed description'}</div>
                    
                    <div class="mt-auto pt-2">
                        <button class="btn btn-xs btn-info w-100 rounded btn-view-task-details" data-id="${task.id}">
                            <i class="fa-solid fa-expand me-1"></i> Open Full Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    function loadBoard() {
        if (!window.TaskAPI) return;
        
        $('.kanban-cards-container').html('<div class="text-center text-muted py-4"><i class="fa-solid fa-circle-notch fa-spin"></i></div>');

        TaskAPI.get('/tasks', { per_page: 200 }).done(function(res) {
            boardTasks = res.data || [];
            applySortAndRender();
        });
    }

    function applySortAndRender() {
        let tasks = [...boardTasks];

        // Apply Sorting
        if (boardState.sort === 'priority-desc') {
            tasks.sort((a, b) => b.priority - a.priority);
        } else if (boardState.sort === 'priority-asc') {
            tasks.sort((a, b) => a.priority - b.priority);
        } else if (boardState.sort === 'date-desc') {
            tasks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        } else if (boardState.sort === 'date-asc') {
            tasks.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        } else if (boardState.sort === 'title-asc') {
            tasks.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
        }

        // Clear columns
        $('.kanban-cards-container').empty();
        
        // Render cards into appropriate columns
        tasks.forEach(task => {
            let status = task.status;
            let column = $(`.kanban-column[data-status="${status}"] .kanban-cards-container`);
            if(column.length) {
                column.append(renderCard(task));
            }
        });

        applyFilters();
        updateCounts();
    }

    function applyFilters() {
        let q = $('#board-search').val().toLowerCase();

        // Show/hide search clear button
        if (q.length > 0) {
            $('#btn-clear-search').show();
        } else {
            $('#btn-clear-search').hide();
        }

        $('.kanban-card').each(function() {
            let card = $(this);
            let type = card.data('type');
            let priority = parseInt(card.data('priority')) || 0;
            let cardStatus = card.closest('.kanban-column').data('status');
            let text = card.text().toLowerCase();

            // Type filter check
            let typeMatch = boardState.types[type] !== false;

            // Priority filter check
            let priorityMatch = true;
            if (boardState.priority === 'critical') priorityMatch = priority > 7;
            else if (boardState.priority === 'high') priorityMatch = priority >= 5 && priority <= 7;
            else if (boardState.priority === 'normal') priorityMatch = priority >= 3 && priority <= 4;
            else if (boardState.priority === 'low') priorityMatch = priority < 3;

            // Status Metric Pill Filter Check
            let statusPillMatch = true;
            if (boardState.statusFilter && boardState.statusFilter !== 'all') {
                statusPillMatch = (cardStatus === boardState.statusFilter);
            }

            // Quick Chip Filter Check
            let chipMatch = true;
            if (boardState.chipFilter === 'agent') chipMatch = type === 'agent' || text.includes('agent');
            else if (boardState.chipFilter === 'critical') chipMatch = priority > 7;
            else if (boardState.chipFilter === 'failed') chipMatch = (cardStatus === 'failed');
            else if (boardState.chipFilter === 'running') chipMatch = (cardStatus === 'in-progress');

            // Search query check
            let searchMatch = true;
            if (q) {
                if (boardState.regexSearch) {
                    try {
                        let regex = new RegExp(q, 'i');
                        searchMatch = regex.test(card.text());
                    } catch(e) { searchMatch = text.indexOf(q) > -1; }
                } else {
                    searchMatch = text.indexOf(q) > -1;
                }
            }

            let isVisible = typeMatch && priorityMatch && statusPillMatch && chipMatch && searchMatch;

            if (isVisible) {
                card.removeClass('dimmed').show();
            } else {
                if (boardState.focusMode) {
                    card.addClass('dimmed').show();
                } else {
                    card.hide();
                }
            }
        });

        // Apply Column Visibilities
        let maxCol = typeof getMaximizedColumn === 'function' ? getMaximizedColumn() : null;
        let hiddenCols = typeof getHiddenColumns === 'function' ? getHiddenColumns() : [];

        $('.kanban-column').each(function() {
            let colStatus = $(this).data('status');
            if (maxCol) {
                if (colStatus === maxCol) $(this).removeClass('d-none');
                else $(this).addClass('d-none');
            } else {
                let columnVis = boardState.cols[colStatus] !== false && !hiddenCols.includes(colStatus);
                let statusFilterVis = (boardState.statusFilter === 'all' || boardState.statusFilter === colStatus);

                if (columnVis && statusFilterVis) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            }
        });

        updateCounts();
    }

    function updateCounts() {
        let visibleTotal = 0;
        let counts = { running: 0, failed: 0, blocked: 0, completed: 0 };

        $('.kanban-column').each(function() {
            let colStatus = $(this).data('status');
            let count = $(this).find('.kanban-card:visible:not(.dimmed)').length;
            $(this).find('.kanban-count').text(count);
            visibleTotal += count;

            if (colStatus === 'in-progress') counts.running += count;
            else if (colStatus === 'failed') counts.failed += count;
            else if (colStatus === 'blocked') counts.blocked += count;
            else if (colStatus === 'completed') counts.completed += count;
        });

        $('#metric-count-total').text(visibleTotal);
        $('#metric-count-running').text(counts.running);
        $('#metric-count-failed').text(counts.failed);
        $('#metric-count-blocked').text(counts.blocked);
        $('#metric-count-completed').text(counts.completed);

        updateBulkOverlayState();
    }

    function updateBulkOverlayState() {
        let selectedCount = boardState.selectedIds.size;
        if (selectedCount > 0) {
            $('#bulk-selected-count').text(`${selectedCount} Selected`);
            $('#command-bulk-overlay').css('display', 'flex');
        } else {
            $('#command-bulk-overlay').hide();
        }
    }

    // Status Metric Pills Click Handler
    $('#status-metric-pills .status-metric-pill').on('click', function() {
        let filter = $(this).data('status-filter');
        $('#status-metric-pills .status-metric-pill').removeClass('active-filter');
        $(this).addClass('active-filter');
        boardState.statusFilter = filter;
        saveState();
        applyFilters();
    });

    // Quick Filter Chips Click Handler
    $('#quick-filter-chips .filter-chip[data-chip-filter]').on('click', function() {
        let chip = $(this).data('chip-filter');
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            boardState.chipFilter = null;
            $('#btn-reset-quick-chips').hide();
        } else {
            $('#quick-filter-chips .filter-chip').removeClass('active');
            $(this).addClass('active');
            boardState.chipFilter = chip;
            $('#btn-reset-quick-chips').show();
        }
        saveState();
        applyFilters();
    });

    $('#btn-reset-quick-chips').on('click', function() {
        $('#quick-filter-chips .filter-chip').removeClass('active');
        boardState.chipFilter = null;
        $(this).hide();
        saveState();
        applyFilters();
    });

    // ----------------------------------------------------
    // Column Visibility & Dynamic Expansion Control
    // ----------------------------------------------------
    const STORAGE_KEY_HIDDEN_COLS = 'taskshub_hidden_columns';

    function getHiddenColumns() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY_HIDDEN_COLS)) || [];
        } catch (e) {
            return [];
        }
    }

    function updateColumnVisibilityUI() {
        let hiddenCols = getHiddenColumns();
        $('.kanban-column').each(function() {
            let status = $(this).data('status');
            if (hiddenCols.includes(status)) {
                $(this).addClass('d-none');
            } else {
                $(this).removeClass('d-none');
            }
        });

        if (hiddenCols.length > 0) {
            if (!$('#column-restore-bar').length) {
                $('#board-canvas-wrapper').before(
                    `<div class="mb-2 d-flex align-items-center justify-content-between px-1" id="column-restore-bar">
                        <span class="badge bg-dark text-info border border-info rounded-pill px-3 py-1 font-mono" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-eye-slash me-1"></i> ${hiddenCols.length} Column(s) Hidden
                        </span>
                        <button class="btn btn-sm btn-outline-info rounded-pill font-mono px-3 py-0.5" id="btn-restore-columns" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-eye me-1"></i> Restore All Columns
                        </button>
                    </div>`
                );
            } else {
                $('#column-restore-bar').show();
                $('#column-restore-bar span').html(`<i class="fa-solid fa-eye-slash me-1"></i> ${hiddenCols.length} Column(s) Hidden`);
            }
        } else {
            $('#column-restore-bar').hide();
        }
    }

    $(document).on('click', '.btn-hide-column', function(e) {
        e.stopPropagation();
        let statusToHide = $(this).data('hide-column');
        if (!statusToHide) return;

        let hiddenCols = getHiddenColumns();
        if (!hiddenCols.includes(statusToHide)) {
            hiddenCols.push(statusToHide);
            localStorage.setItem(STORAGE_KEY_HIDDEN_COLS, JSON.stringify(hiddenCols));
        }
        updateColumnVisibilityUI();
    });

    $(document).on('click', '#btn-restore-columns', function() {
        localStorage.removeItem(STORAGE_KEY_HIDDEN_COLS);
        updateColumnVisibilityUI();
    });

    // ----------------------------------------------------
    // Maximize / Focus Column Mode & State Persistence
    // ----------------------------------------------------
    const STORAGE_KEY_MAXIMIZED_COL = 'taskshub_maximized_column';

    function getMaximizedColumn() {
        return localStorage.getItem(STORAGE_KEY_MAXIMIZED_COL) || null;
    }

    function updateColumnMaximizedUI() {
        let maxCol = getMaximizedColumn();
        $('.btn-maximize-column').removeClass('active').html('<i class="fa-solid fa-expand" style="font-size: 0.75rem;"></i>');

        if (maxCol) {
            $('.kanban-column').each(function() {
                let status = $(this).data('status');
                if (status === maxCol) {
                    $(this).removeClass('d-none').addClass('column-maximized');
                } else {
                    $(this).addClass('d-none').removeClass('column-maximized');
                }
            });
            let targetBtn = $(`.btn-maximize-column[data-maximize-column="${maxCol}"]`);
            targetBtn.addClass('active').html('<i class="fa-solid fa-compress text-info" style="font-size: 0.75rem;"></i>');

            if (!$('#max-restore-bar').length) {
                $('#board-canvas-wrapper').before(
                    `<div class="mb-2 d-flex align-items-center justify-content-between px-1" id="max-restore-bar">
                        <span class="badge bg-dark text-info border border-info rounded-pill px-3 py-1 font-mono" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-expand me-1"></i> Column "${maxCol.toUpperCase()}" Maximized (Studio Grid Mode)
                        </span>
                        <button class="btn btn-sm btn-outline-light rounded-pill font-mono px-3 py-0.5" id="btn-unmaximize-column" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-compress me-1"></i> Exit Grid View Mode
                        </button>
                    </div>`
                );
            } else {
                $('#max-restore-bar').show();
                $('#max-restore-bar span').html(`<i class="fa-solid fa-expand me-1"></i> Column "${maxCol.toUpperCase()}" Maximized (Studio Grid Mode)`);
            }
        } else {
            $('.kanban-column').removeClass('column-maximized');
            $('#max-restore-bar').hide();
            updateColumnVisibilityUI();
        }
    }

    $(document).on('click', '.btn-maximize-column', function(e) {
        e.stopPropagation();
        let targetCol = $(this).data('maximize-column');
        let currentMax = getMaximizedColumn();

        if (currentMax === targetCol) {
            localStorage.removeItem(STORAGE_KEY_MAXIMIZED_COL);
        } else {
            localStorage.setItem(STORAGE_KEY_MAXIMIZED_COL, targetCol);
        }
        updateColumnMaximizedUI();
    });

    $(document).on('click', '#btn-unmaximize-column', function() {
        localStorage.removeItem(STORAGE_KEY_MAXIMIZED_COL);
        updateColumnMaximizedUI();
    });

    updateColumnMaximizedUI();

    // Regex Button Toggle Handler
    $('#btn-toggle-regex').on('click', function() {
        $(this).toggleClass('btn-primary btn-outline-secondary');
        boardState.regexSearch = $(this).hasClass('btn-primary');
        saveState();
        applyFilters();
    });

    // Live Sync Interactive Button Handler
    let isLiveSyncActive = true;
    $('#btn-toggle-live-sync').on('click', function() {
        isLiveSyncActive = !isLiveSyncActive;
        let btn = $(this);
        let dot = $('#live-sync-dot');
        let text = $('#live-sync-text');

        if (isLiveSyncActive) {
            btn.removeClass('paused');
            dot.removeClass('paused');
            text.text('Live Sync');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Live Sync Enabled',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#0F172A',
                    color: '#F8FAFC'
                });
            }
            if (typeof fetchBoardTasks === 'function') {
                fetchBoardTasks();
            }
        } else {
            btn.addClass('paused');
            dot.addClass('paused');
            text.text('Sync Paused');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'Live Sync Paused',
                    showConfirmButton: false,
                    timer: 2000,
                    background: '#0F172A',
                    color: '#F8FAFC'
                });
            }
        }
    });

    // Omnibox Search Clear Handler
    $('#btn-clear-search').on('click', function() {
        $('#board-search').val('').focus();
        applyFilters();
    });

    // Controls Event Handlers
    
    // 1. Column Visibility
    $('#col-vis-toggles button').on('click', function() {
        let col = $(this).data('col');
        $(this).toggleClass('active');
        boardState.cols[col] = $(this).hasClass('active');
        saveState();
        applyFilters();
    });

    // 2. Type Visibility
    $('#type-vis-toggles button').on('click', function() {
        let type = $(this).data('type');
        $(this).toggleClass('active');
        boardState.types[type] = $(this).hasClass('active');
        saveState();
        applyFilters();
    });

    // 3. Priority Filters
    $('#priority-vis-toggles button').on('click', function() {
        $('#priority-vis-toggles button').removeClass('active');
        $(this).addClass('active');
        boardState.priority = $(this).data('priority');
        saveState();
        applyFilters();
    });

    // 4. Zoom Controls
    $('#btn-zoom-in').on('click', function() {
        if(boardState.zoom < 150) {
            boardState.zoom += 15;
            applyZoom();
        }
    });
    $('#btn-zoom-out').on('click', function() {
        if(boardState.zoom > 60) {
            boardState.zoom -= 15;
            applyZoom();
        }
    });
    $('#btn-zoom-reset').on('click', function() {
        boardState.zoom = 100;
        applyZoom();
    });
    function applyZoom() {
        $('#board-canvas-wrapper').css('transform', `scale(${boardState.zoom / 100})`);
        $('#zoom-indicator').text(`${boardState.zoom}%`);
        saveState();
    }

    // 5. Density Controls
    $('#btn-density-normal').on('click', function() {
        $('#view-density-group button').removeClass('active');
        $(this).addClass('active');
        $('#board-canvas-wrapper').removeClass('kanban-compact');
        boardState.density = 'normal';
        saveState();
    });
    $('#btn-density-compact').on('click', function() {
        $('#view-density-group button').removeClass('active');
        $(this).addClass('active');
        $('#board-canvas-wrapper').addClass('kanban-compact');
        boardState.density = 'compact';
        saveState();
    });

    // 6. 3D Card Flip
    $('#btn-toggle-card-flip').on('click', function() {
        $(this).toggleClass('active btn-info btn-outline-info');
        boardState.isFlipped = !boardState.isFlipped;
        $('.kanban-card').toggleClass('flipped');
        saveState();
    });

    // 7. Sorting & Theme
    $('#board-sort-select').on('change', function() {
        boardState.sort = $(this).val();
        saveState();
        applySortAndRender();
    });

    // 8. Focus Mode & Switches
    $('#switch-focus-mode').on('change', function() {
        boardState.focusMode = $(this).is(':checked');
        saveState();
        applyFilters();
    });

    $('#switch-multiselect').on('change', function() {
        boardState.multiSelect = $(this).is(':checked');
        saveState();
        applySortAndRender();
    });

    $('#switch-regex-search').on('change', function() {
        boardState.regexSearch = $(this).is(':checked');
        saveState();
        applyFilters();
    });

    // Panel collapse toggle
    $('#btn-toggle-panel-collapse').on('click', function() {
        $('#board-control-body').slideToggle(200);
        $(this).find('i').toggleClass('fa-chevron-up fa-chevron-down');
    });

    // Bulk Actions Overlay Event Handlers
    $(document).on('change', '.card-checkbox', function() {
        let taskId = $(this).closest('.kanban-card').data('id');
        if ($(this).is(':checked')) {
            boardState.selectedIds.add(taskId);
            $(this).closest('.kanban-card').addClass('selected');
        } else {
            boardState.selectedIds.delete(taskId);
            $(this).closest('.kanban-card').removeClass('selected');
        }
        updateBulkOverlayState();
    });

    $('#btn-bulk-cancel-select').on('click', function() {
        $('.card-checkbox').prop('checked', false);
        $('.kanban-card').removeClass('selected');
        boardState.selectedIds.clear();
        updateBulkOverlayState();
    });

    $('#btn-bulk-retry').on('click', function() {
        let ids = Array.from(boardState.selectedIds);
        if(ids.length === 0) return;
        Nexus.confirm(`Retry ${ids.length} selected tasks?`, function() {
            TaskAPI.post('/tasks/bulk-retry', { ids: ids }).done(() => {
                Nexus.notify(`Retrying ${ids.length} tasks...`, 'success');
                loadBoard();
            }).fail(() => Nexus.notify('Bulk retry triggered', 'info'));
        });
    });

    // Exporters
    $('#btn-export-json').on('click', function() {
        let dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(boardTasks, null, 2));
        let downloadAnchor = document.createElement('a');
        downloadAnchor.setAttribute("href", dataStr);
        downloadAnchor.setAttribute("download", `nexus_board_${Date.now()}.json`);
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        downloadAnchor.remove();
    });

    // Search input & Refresh
    $('#board-search').on('input', applyFilters);
    $('#btn-refresh-board').on('click', function() {
        $('#refresh-icon').addClass('fa-spin');
        loadBoard();
        setTimeout(() => $('#refresh-icon').removeClass('fa-spin'), 1000);
    });

    // Initialize Sortable Drag & Drop
    function initSortable() {
        $('.kanban-cards-container').sortable({
            connectWith: '.kanban-cards-container',
            placeholder: 'ui-sortable-placeholder',
            delay: 100,
            start: function(event, ui) {
                ui.item.data('start-status', ui.item.closest('.kanban-column').data('status'));
            },
            update: function(event, ui) {
                if (this === ui.item.parent()[0]) {
                    let taskId = ui.item.data('id');
                    let newStatus = ui.item.closest('.kanban-column').data('status');
                    let oldStatus = ui.item.data('start-status');
                    
                    if (newStatus !== oldStatus) {
                        TaskAPI.patch(`/tasks/${taskId}/status`, { status: newStatus })
                            .done(() => {
                                Nexus.notify(`Task #${taskId} moved to ${newStatus}`, 'success');
                                updateCounts();
                            })
                            .fail(() => {
                                $(this).sortable('cancel');
                                updateCounts();
                            });
                    }
                }
            }
        }).disableSelection();
    // ----------------------------------------------------
    // Task Details Modal & Sub-tasks Management
    // ----------------------------------------------------
    let currentDetailTaskId = null;

    $(document).on('click', '.btn-view-task-details', function(e) {
        e.stopPropagation();
        let taskId = $(this).data('id');
        let task = boardTasks.find(t => t.id == taskId);

        if (!task) {
            TaskAPI.get(`/tasks/${taskId}`).done(function(res) {
                if (res.data) openTaskDetailsModal(res.data);
            });
        } else {
            openTaskDetailsModal(task);
        }
    });

    function openTaskDetailsModal(task) {
        currentDetailTaskId = task.id;
        $('#detail-task-id').text(`#${task.id}`);
        $('#detail-task-title').text(task.title || 'Task Details');
        $('#detail-task-status').html(`<i class="fa-solid fa-signal me-1"></i> Status: ${task.status}`);
        $('#detail-task-type').html(`<i class="fa-solid fa-cube me-1"></i> Type: ${task.type || 'agent'}`);
        $('#detail-task-priority').html(`<i class="fa-solid fa-fire me-1"></i> Priority: ${task.priority}/10`);
        $('#detail-task-created').html(`<i class="fa-solid fa-clock me-1"></i> Created: ${task.created_at ? new Date(task.created_at).toLocaleString() : 'N/A'}`);
        $('#detail-task-desc').text(task.description || 'No detailed description provided.');

        // Previews
        try {
            let p = typeof task.payload_data === 'string' ? JSON.parse(task.payload_data) : task.payload_data;
            $('#detail-task-payload-pre').text(JSON.stringify(p, null, 2) || '{}');
        } catch(e) { $('#detail-task-payload-pre').text(String(task.payload_data || '{}')); }

        try {
            let r = typeof task.result_data === 'string' ? JSON.parse(task.result_data) : task.result_data;
            $('#detail-task-result-pre').text(JSON.stringify(r, null, 2) || '{}');
        } catch(e) { $('#detail-task-result-pre').text(String(task.result_data || '{}')); }

        renderSubtasksInModal(task);

        let modalEl = document.getElementById('modal-task-details');
        let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }

    function renderSubtasksInModal(task) {
        let subtasks = (task.metadata && Array.isArray(task.metadata.subtasks)) ? task.metadata.subtasks : [];
        let total = subtasks.length;
        let completed = subtasks.filter(s => s.completed).length;
        let pct = total > 0 ? Math.round((completed / total) * 100) : 0;

        $('#detail-subtasks-count').text(`${completed}/${total}`);
        $('#detail-subtasks-bar').css('width', `${pct}%`);

        let listHtml = '';
        if (total === 0) {
            listHtml = `<div class="text-muted font-mono py-2" style="font-size: 0.75rem;">لا يوجد مهام فرعية بعد. أضف مهمة فرعية أعلاه!</div>`;
        } else {
            subtasks.forEach(st => {
                let isChecked = st.completed ? 'checked' : '';
                let compClass = st.completed ? 'completed text-muted' : 'text-light';
                listHtml += `
                <div class="subtask-item d-flex align-items-center justify-content-between ${compClass}" data-subtask-id="${st.id}">
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="form-check-input btn-toggle-subtask-check" data-subtask-id="${st.id}" ${isChecked}>
                        <span style="font-size: 0.8rem;">${st.title}</span>
                    </div>
                    <button class="btn btn-xs text-danger btn-delete-subtask" data-subtask-id="${st.id}" title="حذف المهمة الفرعية">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>`;
            });
        }
        $('#detail-subtasks-list').html(listHtml);
    }

    // Add subtask
    $('#btn-add-subtask').on('click', function() {
        let title = $('#input-new-subtask').val().trim();
        if (!title || !currentDetailTaskId) return;

        TaskAPI.post(`/tasks/${currentDetailTaskId}/subtasks`, { title: title }).done(function(res) {
            $('#input-new-subtask').val('');
            if (res.data) {
                let idx = boardTasks.findIndex(t => t.id == currentDetailTaskId);
                if (idx !== -1) boardTasks[idx] = res.data;
                renderSubtasksInModal(res.data);
                applySortAndRender();
                Nexus.notify('تمت إضافة المهمة الفرعية بنجاح', 'success');
            }
        });
    });

    $('#input-new-subtask').on('keypress', function(e) {
        if (e.which === 13) {
            $('#btn-add-subtask').click();
        }
    });

    // Toggle subtask
    $(document).on('change', '.btn-toggle-subtask-check', function() {
        let subtaskId = $(this).data('subtask-id');
        if (!subtaskId || !currentDetailTaskId) return;

        TaskAPI.patch(`/tasks/${currentDetailTaskId}/subtasks/${subtaskId}`).done(function(res) {
            if (res.data) {
                let idx = boardTasks.findIndex(t => t.id == currentDetailTaskId);
                if (idx !== -1) boardTasks[idx] = res.data;
                renderSubtasksInModal(res.data);
                applySortAndRender();
            }
        });
    });

    // Delete subtask
    $(document).on('click', '.btn-delete-subtask', function() {
        let subtaskId = $(this).data('subtask-id');
        if (!subtaskId || !currentDetailTaskId) return;

        TaskAPI.delete(`/tasks/${currentDetailTaskId}/subtasks/${subtaskId}`).done(function(res) {
            if (res.data) {
                let idx = boardTasks.findIndex(t => t.id == currentDetailTaskId);
                if (idx !== -1) boardTasks[idx] = res.data;
                renderSubtasksInModal(res.data);
                applySortAndRender();
                Nexus.notify('تم حذف المهمة الفرعية', 'info');
            }
        });
    });

    // Modal Execute & Delete
    $('#btn-detail-execute').on('click', function() {
        if (!currentDetailTaskId) return;
        TaskAPI.post(`/tasks/${currentDetailTaskId}/execute`).done(function() {
            Nexus.notify(`جاري تشغيل المهمة #${currentDetailTaskId}...`, 'success');
            loadBoard();
            bootstrap.Modal.getInstance(document.getElementById('modal-task-details'))?.hide();
        });
    });

    $('#btn-detail-delete').on('click', function() {
        if (!currentDetailTaskId) return;
        Nexus.confirm(`هل أنت تأكد من حذف المهمة #${currentDetailTaskId}؟`, function() {
            TaskAPI.delete(`/tasks/${currentDetailTaskId}`).done(function() {
                Nexus.notify(`تم حذف المهمة #${currentDetailTaskId}`, 'success');
                loadBoard();
                bootstrap.Modal.getInstance(document.getElementById('modal-task-details'))?.hide();
            });
        });
    });

    // Keyboard Shortcuts
    $(document).on('keydown', function(e) {
        if ($(e.target).is('input, select, textarea')) {
            if (e.key === 'Escape' && $(e.target).is('#board-search')) {
                $('#btn-clear-search').click();
            }
            return;
        }
        let key = e.key.toLowerCase();
        if (key === 'r') { $('#btn-refresh-board').click(); }
        else if (key === 'f') { e.preventDefault(); $('#board-search').focus(); }
        else if (key === 'x') { $('#btn-toggle-card-flip').click(); }
        else if (key === '+') { $('#btn-zoom-in').click(); }
        else if (key === '-') { $('#btn-zoom-out').click(); }
        else if (key === '0') { $('#btn-zoom-reset').click(); }
    });

    // Initialize if active tab
    if ($('#content-board').hasClass('active')) {
        loadBoard();
        initSortable();
    } else {
        $('a[data-bs-toggle="tab"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('data-bs-target') === '#content-board') {
                if(boardTasks.length === 0) loadBoard();
                initSortable();
            }
        });
    }
});
</script>
