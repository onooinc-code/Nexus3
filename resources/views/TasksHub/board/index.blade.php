<div class="h-100 d-flex flex-column">
    <!-- Board Controls -->
    <div class="tasks-glass-card p-2 mb-3 d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-board"><i class="fa-solid fa-rotate me-1"></i>Refresh Board</button>
            <div class="input-group input-group-sm ms-3" style="width: 250px;">
                <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" id="board-search" class="form-control bg-dark text-light border-secondary" placeholder="Filter cards...">
            </div>
        </div>
        <div class="text-muted font-mono" style="font-size: 0.7rem;">Drag cards to update status</div>
    </div>

    <!-- Board Columns -->
    <div class="flex-grow-1 overflow-x-auto overflow-y-hidden pb-2" style="white-space: nowrap;">
        <div class="d-inline-flex gap-3 h-100 px-1" style="min-width: min-content;">
            
            <!-- Column: Todo -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="todo" style="width: 320px; max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.02);">
                    <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-circle text-secondary me-2" style="font-size: 0.6rem;"></i>Todo</h6>
                    <span class="badge bg-secondary rounded-pill kanban-count">0</span>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: In Progress -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="in-progress" style="width: 320px; max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(99, 102, 241, 0.05);">
                    <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-play text-primary me-2" style="font-size: 0.6rem;"></i>In Progress</h6>
                    <span class="badge bg-primary rounded-pill kanban-count">0</span>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: Blocked -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="blocked" style="width: 320px; max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(245, 158, 11, 0.05);">
                    <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-ban text-warning me-2" style="font-size: 0.6rem;"></i>Blocked</h6>
                    <span class="badge bg-warning text-dark rounded-pill kanban-count">0</span>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: Completed -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="completed" style="width: 320px; max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(16, 185, 129, 0.05);">
                    <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-check-double text-success me-2" style="font-size: 0.6rem;"></i>Completed</h6>
                    <span class="badge bg-success rounded-pill kanban-count">0</span>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

            <!-- Column: Failed -->
            <div class="kanban-column tasks-glass-panel d-flex flex-column rounded" data-status="failed" style="width: 320px; max-height: 100%;">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(239, 68, 68, 0.05);">
                    <h6 class="mb-0 text-light font-mono" style="font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation text-danger me-2" style="font-size: 0.6rem;"></i>Failed</h6>
                    <span class="badge bg-danger rounded-pill kanban-count">0</span>
                </div>
                <div class="kanban-cards-container flex-grow-1 p-2 overflow-y-auto" style="min-height: 200px;"></div>
            </div>

        </div>
    </div>
</div>

<style>
/* Kanban Card Styling */
.kanban-card {
    background: rgba(30, 41, 59, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    cursor: grab;
    transition: transform 0.1s, box-shadow 0.1s;
    white-space: normal;
}
.kanban-card:hover {
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.kanban-card:active { cursor: grabbing; }
.ui-sortable-helper {
    background: rgba(30, 41, 59, 0.95);
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

    function renderCard(task) {
        let priorityColor = task.priority > 7 ? 'var(--tasks-danger)' : (task.priority > 4 ? 'var(--tasks-warning)' : 'var(--tasks-success)');
        let agentBadge = task.agent_id ? `<span class="badge bg-primary" style="font-size: 0.6rem;"><i class="fa-solid fa-robot"></i> ${task.agent_id}</span>` : '';
        
        return `
        <div class="kanban-card" data-id="${task.id}" onclick="if(!$(this).hasClass('ui-sortable-helper')) window.openQuickView(${task.id})">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="font-mono text-muted" style="font-size: 0.65rem;">#${task.id}</span>
                <span class="badge bg-dark border border-secondary" style="font-size: 0.6rem;">${task.type}</span>
            </div>
            <h6 class="text-light mb-2" style="font-size: 0.85rem; line-height: 1.3;">${task.title}</h6>
            
            <div class="d-flex justify-content-between align-items-end mt-2">
                <div class="d-flex gap-1">
                    ${agentBadge}
                </div>
                <div class="d-flex align-items-center gap-1" title="Priority: ${task.priority}">
                    <div style="width: 25px; height: 3px; background: rgba(255,255,255,0.1); border-radius: 1px;">
                        <div style="width: ${task.priority*10}%; height: 100%; background: ${priorityColor};"></div>
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
            
            // Clear columns
            $('.kanban-cards-container').empty();
            
            // Render cards
            boardTasks.forEach(task => {
                let status = task.status;
                let column = $(`.kanban-column[data-status="${status}"] .kanban-cards-container`);
                if(column.length) {
                    column.append(renderCard(task));
                }
            });

            updateCounts();
            applySearch(); // re-apply search if exists
        });
    }

    function updateCounts() {
        $('.kanban-column').each(function() {
            let count = $(this).find('.kanban-card:visible').length;
            $(this).find('.kanban-count').text(count);
        });
    }

    function initSortable() {
        $('.kanban-cards-container').sortable({
            connectWith: '.kanban-cards-container',
            placeholder: 'ui-sortable-placeholder',
            delay: 100, // prevent accidental drags on click
            start: function(event, ui) {
                ui.item.data('start-status', ui.item.closest('.kanban-column').data('status'));
            },
            update: function(event, ui) {
                // update fires for both columns, only process on the receiving column
                if (this === ui.item.parent()[0]) {
                    let taskId = ui.item.data('id');
                    let newStatus = ui.item.closest('.kanban-column').data('status');
                    let oldStatus = ui.item.data('start-status');
                    
                    if (newStatus !== oldStatus) {
                        // Call API to update status
                        TaskAPI.patch(`/tasks/${taskId}/status`, { status: newStatus })
                            .done(() => {
                                Nexus.notify(`Task #${taskId} moved to ${newStatus}`, 'success');
                                updateCounts();
                            })
                            .fail((err) => {
                                // Revert drag if API fails
                                $(this).sortable('cancel');
                                updateCounts();
                            });
                    } else {
                        // Just sorting within same column, ignore or save order if supported
                    }
                }
            }
        }).disableSelection();
    }

    $('#btn-refresh-board').on('click', loadBoard);

    function applySearch() {
        let q = $('#board-search').val().toLowerCase();
        $('.kanban-card').each(function() {
            let text = $(this).text().toLowerCase();
            if (text.indexOf(q) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        updateCounts();
    }

    $('#board-search').on('input', applySearch);

    // Initialize if active
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
