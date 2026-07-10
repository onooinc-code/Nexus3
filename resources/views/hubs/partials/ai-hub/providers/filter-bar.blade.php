<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <div class="input-group input-group-sm" style="width: 250px;">
            <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
            <input type="text" id="providers-search" class="form-control bg-dark border-secondary text-light" placeholder="Search providers...">
        </div>
        
        <select id="providers-status-filter" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="disabled">Disabled</option>
        </select>

        <select id="providers-health-filter" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
            <option value="">All Health</option>
            <option value="healthy">Healthy</option>
            <option value="degraded">Degraded</option>
            <option value="offline">Unreachable</option>
            <option value="no_ping">No Ping Yet</option>
        </select>

        <select id="providers-sort-dropdown" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
            <option value="sort_order-asc">Custom Order</option>
            <option value="name-asc">Name (A-Z)</option>
            <option value="name-desc">Name (Z-A)</option>
            <option value="cost-desc">Cost (High to Low)</option>
            <option value="models-desc">Models (High to Low)</option>
            <option value="synced-desc">Last Synced (Newest)</option>
        </select>

        <button id="providers-favorite-filter" class="btn btn-sm btn-outline-warning" title="Show Favorites Only">
            <i class="fa-regular fa-star"></i>
        </button>

        <button id="providers-sync-all" class="btn btn-sm btn-outline-info" title="Sync All Active">
            <i class="fa-solid fa-rotate me-1"></i> Sync All
        </button>

        <div class="btn-group btn-group-sm ms-2">
            <button id="btn-view-cards" class="btn btn-secondary active" title="Card View">
                <i class="fa-solid fa-grip"></i>
            </button>
            <button id="btn-view-table" class="btn btn-outline-secondary" title="Table View">
                <i class="fa-solid fa-list"></i>
            </button>
        </div>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3" data-bs-toggle="offcanvas" data-bs-target="#addProviderOffcanvas">
        <i class="fa-solid fa-plus me-1"></i> Add Provider
    </button>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let showFavoritesOnly = false;

    function applyFilters() {
        const q = $('#providers-search').val().toLowerCase();
        const statusFilter = $('#providers-status-filter').val();
        const healthFilter = $('#providers-health-filter').val();

        // Filter Cards
        $('#providers-cards-view .provider-card-wrapper').each(function() {
            const $card = $(this);
            const text = $card.text().toLowerCase();
            const status = $card.data('status');
            const health = $card.data('health');
            const isFav = $card.data('favorite') === 1;

            let show = true;
            if (q.length > 0 && !text.includes(q)) show = false;
            if (statusFilter && status !== statusFilter) show = false;
            if (healthFilter && health !== healthFilter) show = false;
            if (showFavoritesOnly && !isFav) show = false;

            $card.toggleClass('d-none', !show);
        });

        // Filter Table Rows
        $('#providers-table-view tbody tr.provider-row').each(function() {
            const $row = $(this);
            const text = $row.text().toLowerCase();
            const status = $row.data('status');
            const health = $row.data('health');
            const isFav = $row.data('favorite') === 1;

            let show = true;
            if (q.length > 0 && !text.includes(q)) show = false;
            if (statusFilter && status !== statusFilter) show = false;
            if (healthFilter && health !== healthFilter) show = false;
            if (showFavoritesOnly && !isFav) show = false;

            $row.toggleClass('d-none', !show);
        });
    }

    function applySort() {
        const sortVal = $('#providers-sort-dropdown').val();
        const [sortKey, sortDir] = sortVal.split('-');

        const sortElements = function($container, itemSelector) {
            const items = $container.find(itemSelector).get();
            items.sort(function(a, b) {
                let valA = $(a).data(sortKey);
                let valB = $(b).data(sortKey);
                
                // convert to number if possible
                if (!isNaN(valA) && !isNaN(valB)) {
                    valA = parseFloat(valA);
                    valB = parseFloat(valB);
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }

                if (valA < valB) return sortDir === 'asc' ? -1 : 1;
                if (valA > valB) return sortDir === 'asc' ? 1 : -1;
                return 0;
            });
            $.each(items, function(i, item) {
                $container.append(item);
            });
        };

        sortElements($('#providers-cards-view'), '.provider-card-wrapper');
        sortElements($('#providers-table-view tbody'), 'tr.provider-row');
    }

    // Event Listeners
    $('#providers-search').on('input', applyFilters);
    $('#providers-status-filter, #providers-health-filter').on('change', applyFilters);
    
    $('#providers-sort-dropdown').on('change', applySort);

    $('#providers-favorite-filter').on('click', function() {
        showFavoritesOnly = !showFavoritesOnly;
        $(this).toggleClass('btn-outline-warning btn-warning');
        $(this).find('i').toggleClass('fa-regular fa-solid');
        applyFilters();
    });

    $('#providers-sync-all').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Syncing...');
        
        $.post('/api/v1/ai/providers/sync-all', {}, function(res) {
            showToast('Success', res.message, 'success');
            setTimeout(() => {
                btn.prop('disabled', false).html('<i class="fa-solid fa-rotate me-1"></i> Sync All');
            }, 2000);
        }).fail(function(err) {
            showToast('Error', err.responseJSON?.message || 'Failed to dispatch sync', 'danger');
            btn.prop('disabled', false).html('<i class="fa-solid fa-rotate me-1"></i> Sync All');
        });
    });

    // Initial Sort & Filter
    applyFilters();
    applySort();
});
</script>
@endpush
