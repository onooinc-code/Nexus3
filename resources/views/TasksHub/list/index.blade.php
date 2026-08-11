<div class="d-flex flex-column h-100 position-relative">
    
    <!-- Filters Panel (F10) -->
    <div id="tasks-filter-panel" class="tasks-glass-card p-3 mb-3" style="display: none;">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="font-mono text-muted" style="font-size: 0.65rem;">Status</label>
                <select id="filter-status" class="form-select form-select-sm bg-dark text-light border-secondary">
                    <option value="">All</option>
                    <option value="todo">Todo</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="blocked">Blocked</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="font-mono text-muted" style="font-size: 0.65rem;">Type</label>
                <select id="filter-type" class="form-select form-select-sm bg-dark text-light border-secondary">
                    <option value="">All</option>
                    <option value="manual">Manual</option>
                    <option value="agent">Agent</option>
                    <option value="system">System</option>
                    <option value="code">Code</option>
                    <option value="api">API</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="font-mono text-muted" style="font-size: 0.65rem;">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="filter-search" class="form-control bg-dark text-light border-secondary" placeholder="ID, Title, Payload...">
                </div>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-sm btn-outline-secondary me-2" id="btn-reset-filters">Reset</button>
                <button class="btn btn-sm btn-primary" id="btn-apply-filters">Apply Filters</button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions (F11) -->
    <div id="tasks-bulk-actions" class="tasks-glass-card p-2 mb-3 d-flex align-items-center justify-content-between" style="display: none !important;">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary text-light font-mono" id="bulk-selected-count">0 Selected</span>
            <div style="width: 1px; height: 15px; background: rgba(255,255,255,0.2);"></div>
            <button class="btn btn-sm btn-outline-success bulk-btn" data-action="execute"><i class="fa-solid fa-play me-1"></i>Execute</button>
            <button class="btn btn-sm btn-outline-warning bulk-btn" data-action="pause"><i class="fa-solid fa-pause me-1"></i>Pause</button>
            <button class="btn btn-sm btn-outline-info bulk-btn" data-action="retry"><i class="fa-solid fa-rotate-right me-1"></i>Retry</button>
            <button class="btn btn-sm btn-outline-secondary bulk-btn" data-action="cancel"><i class="fa-solid fa-xmark me-1"></i>Cancel</button>
        </div>
        <button class="btn btn-sm btn-outline-danger bulk-btn" data-action="delete"><i class="fa-solid fa-trash me-1"></i>Delete</button>
    </div>

    <!-- DataTable (F09) -->
    <div class="tasks-glass-card p-0 flex-grow-1 overflow-hidden d-flex flex-column">
        <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center bg-dark bg-opacity-50">
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="btn-toggle-filters" title="Toggle Filters (F)"><i class="fa-solid fa-filter"></i></button>
                <button class="btn btn-sm btn-outline-secondary" id="btn-refresh-table" title="Refresh (R)"><i class="fa-solid fa-rotate"></i></button>
            </div>
            <div class="text-muted font-mono" style="font-size: 0.7rem;">Live Updates: ON</div>
        </div>
        
        <div class="table-responsive flex-grow-1 p-2">
            <table id="tasks-datatable" class="table table-dark table-hover align-middle mb-0 w-100" style="font-size: 0.85rem;">
                <thead class="text-muted font-mono" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="dt-select-all" class="form-check-input"></th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Agent</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated by DataTables AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* DataTables Theme Overrides */
#tasks-datatable_wrapper { height: 100%; display: flex; flex-direction: column; }
#tasks-datatable_wrapper .dataTables_scroll { flex-grow: 1; overflow: hidden; }
#tasks-datatable_wrapper .dataTables_info, #tasks-datatable_wrapper .dataTables_paginate { margin-top: 10px; color: var(--text-muted) !important; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; }
#tasks-datatable_wrapper .page-link { background-color: var(--tasks-surface); border-color: var(--tasks-glass-border); color: #fff; }
#tasks-datatable_wrapper .page-item.active .page-link { background-color: var(--tasks-primary); border-color: var(--tasks-primary); }
table.dataTable.table-hover > tbody > tr:hover > * { box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.05); cursor: pointer; }
.editable-cell:hover { background: rgba(255,255,255,0.1); border-radius: 4px; padding-left: 5px; outline: 1px dashed rgba(255,255,255,0.3); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let dtTable;
    
    // Type badges map
    const typeBadges = {
        'manual': '<span class="badge" style="background: gray;">Manual</span>',
        'agent': '<span class="badge" style="background: var(--tasks-purple);">Agent</span>',
        'system': '<span class="badge" style="background: var(--tasks-warning); color: #000;">System</span>',
        'code': '<span class="badge" style="background: var(--tasks-info);">Code</span>',
        'api': '<span class="badge" style="background: var(--tasks-cyan); color: #000;">API</span>',
    };

    // Status badges map
    const statusBadges = {
        'todo': '<span class="task-status-badge task-status-todo">● Todo</span>',
        'in-progress': '<span class="task-status-badge task-status-running"><i class="fa-solid fa-play"></i> Running</span>',
        'completed': '<span class="task-status-badge task-status-completed"><i class="fa-solid fa-check"></i> Done</span>',
        'failed': '<span class="task-status-badge task-status-failed"><i class="fa-solid fa-xmark"></i> Failed</span>',
        'blocked': '<span class="task-status-badge task-status-blocked"><i class="fa-solid fa-ban"></i> Blocked</span>',
        'cancelled': '<span class="task-status-badge task-status-cancelled"><i class="fa-solid fa-ban"></i> Cancelled</span>',
    };

    function initDataTable() {
        dtTable = $('#tasks-datatable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '/api/v1/tasks',
                type: 'GET',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                data: function (d) {
                    // Laravel pagination mapping
                    d.page = (d.start / d.length) + 1;
                    d.per_page = d.length;
                    if(d.order.length > 0) {
                        d.sort = d.columns[d.order[0].column].data;
                        d.dir = d.order[0].dir;
                    }
                    // Custom filters
                    d.status = $('#filter-status').val();
                    d.type = $('#filter-type').val();
                    d.search = $('#filter-search').val(); // Override default search
                },
                dataFilter: function(data) {
                    var json = JSON.parse(data);
                    // Map Laravel LengthAwarePaginator or Resource format to DataTables
                    let total = json.meta ? json.meta.total : (json.total !== undefined ? json.total : (json.data ? json.data.length : 0));
                    let mapped = {
                        recordsTotal: total,
                        recordsFiltered: total,
                        data: json.data || []
                    };
                    return JSON.stringify(mapped);
                }
            },
            columns: [
                { data: null, orderable: false, render: function(data) { return '<input type="checkbox" class="form-check-input dt-row-cb" value="'+data.id+'">'; } },
                { data: 'id', render: function(data) { return '<span class="font-mono text-muted">#'+data+'</span>'; } },
                { data: 'title', render: function(data) { return '<span class="editable-cell" data-field="title">'+data+'</span>'; } },
                { data: 'type', render: function(data) { return typeBadges[data] || '<span class="badge bg-secondary">'+data+'</span>'; } },
                { data: 'status', render: function(data) { return statusBadges[data] || data; } },
                { data: 'priority', render: function(data) { 
                    let color = data > 7 ? 'var(--tasks-danger)' : (data > 4 ? 'var(--tasks-warning)' : 'var(--tasks-success)');
                    return `<div class="d-flex align-items-center gap-2"><div style="width: 40px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px;"><div style="width: ${data*10}%; height: 100%; background: ${color};"></div></div><span class="font-mono" style="font-size: 0.65rem;">${data}</span></div>`;
                }},
                { data: 'agent_id', render: function(data, type, row) { return data ? '<i class="fa-solid fa-robot text-primary me-1"></i> Agent' : '-'; } },
                { data: 'created_at', render: function(data) { return new Date(data).toLocaleDateString(); } },
                { data: null, orderable: false, className: 'text-end', render: function(data) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary dt-action" data-id="${data.id}" data-act="play"><i class="fa-solid fa-play text-success"></i></button>
                            <button class="btn btn-outline-secondary dt-action" data-id="${data.id}" data-act="view"><i class="fa-solid fa-eye"></i></button>
                        </div>
                    `;
                }}
            ],
            order: [[7, 'desc']], // Sort by created_at by default
            pageLength: 20,
            lengthMenu: [20, 50, 100],
            searching: false, // Using custom search
            scrollY: 'calc(100vh - 350px)', // Rough calculation for flexible height
            scrollCollapse: true,
            language: { emptyTable: "No tasks found matching current filters." }
        });
    }

    // Toggle Filters
    $('#btn-toggle-filters').on('click', function() {
        $('#tasks-filter-panel').slideToggle('fast');
    });

    // Apply Filters
    $('#btn-apply-filters').on('click', function() {
        dtTable.ajax.reload();
    });

    // Reset Filters
    $('#btn-reset-filters').on('click', function() {
        $('#filter-status, #filter-type, #filter-search').val('');
        dtTable.ajax.reload();
    });

    // Refresh
    $('#btn-refresh-table').on('click', function() {
        dtTable.ajax.reload(null, false); // false = keep paging
    });

    // Checkbox logic
    $('#dt-select-all').on('change', function() {
        $('.dt-row-cb').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });

    $('#tasks-datatable').on('change', '.dt-row-cb', function() {
        updateBulkActions();
    });

    function updateBulkActions() {
        let count = $('.dt-row-cb:checked').length;
        if(count > 0) {
            $('#bulk-selected-count').text(count + ' Selected');
            $('#tasks-bulk-actions').attr('style', 'display: flex !important;');
        } else {
            $('#tasks-bulk-actions').attr('style', 'display: none !important;');
        }
    }

    // Initialize Table & handle Tab switching
    if ($('#content-list').hasClass('active')) {
        initDataTable();
    }
    $('a[data-bs-toggle="tab"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('data-bs-target') === '#content-list') {
            if (!dtTable) {
                initDataTable();
            } else {
                dtTable.columns.adjust().draw(false);
            }
        }
    });

    // Open Quick View on Row Click
    $('#tasks-datatable').on('click', 'tbody tr', function(e) {
        if($(e.target).closest('input[type="checkbox"]').length || $(e.target).closest('.btn-group').length) return; // Don't trigger if clicking checkbox or action buttons
        
        let data = dtTable.row(this).data();
        if(data) {
            window.openQuickView(data.id);
        }
    });

    // Handle single actions
    $('#tasks-datatable').on('click', '.dt-action', function(e) {
        e.stopPropagation();
        let id = $(this).data('id');
        let act = $(this).data('act');
        
        if(act === 'play') {
            TaskAPI.post(`/tasks/${id}/execute`).done(() => {
                Nexus.notify('Execution triggered', 'success');
                dtTable.ajax.reload(null, false);
            });
        } else if (act === 'view') {
            window.openQuickView(id);
        }
    });

    // Bulk actions
    $('.bulk-btn').on('click', function() {
        let action = $(this).data('action');
        let ids = [];
        $('.dt-row-cb:checked').each(function() { ids.push($(this).val()); });
        
        if(ids.length === 0) return;
        
        if(action === 'delete') {
            if(!confirm(`Are you sure you want to delete ${ids.length} tasks?`)) return;
        }

        let promises = [];
        ids.forEach(id => {
            if(action === 'delete') promises.push(TaskAPI.delete(`/tasks/${id}`));
            else promises.push(TaskAPI.post(`/tasks/${id}/${action}`));
        });

        Promise.all(promises).then(() => {
            Nexus.notify(`Successfully applied ${action} to ${ids.length} tasks`, 'success');
            dtTable.ajax.reload(null, false);
            $('.dt-row-cb').prop('checked', false);
            $('#dt-select-all').prop('checked', false);
            updateBulkActions();
        });
    });

    // Polling every 15s if List is active tab
    setInterval(function() {
        if ($('#content-list').hasClass('active') && dtTable) {
            // Only poll if no checkboxes are checked (don't mess up user selection)
            if ($('.dt-row-cb:checked').length === 0) {
                dtTable.ajax.reload(null, false);
            }
        }
    }, 15000);
});
</script>
