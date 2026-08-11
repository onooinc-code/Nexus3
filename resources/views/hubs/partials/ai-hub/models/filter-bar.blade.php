<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-3">
        <div class="input-group input-group-sm" style="width: 280px;">
            <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
            <input type="text" id="models-search-input" class="form-control bg-dark border-secondary text-light" placeholder="Search models by name or ID...">
        </div>
        <select id="models-provider-filter" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
            <option value="">All Providers</option>
            @foreach($providers as $p)
                <option value="{{ $p->name }}">{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-info" id="btn-sync-all-models"><i class="fa-solid fa-rotate me-1"></i> Sync All Providers</button>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#models-search-input').on('keyup input', function() {
            const query = $(this).val();
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#modelsTable')) {
                $('#modelsTable').DataTable().search(query).draw();
            } else {
                const lowerQuery = query.toLowerCase();
                $('#models-table-body tr').each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(lowerQuery) > -1);
                });
            }
        });

        $('#models-provider-filter').on('change', function() {
            const provider = $(this).val();
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#modelsTable')) {
                // Column 1 is Provider column in modelsTable
                $('#modelsTable').DataTable().column(1).search(provider ? '^' + provider + '$' : '', true, false).draw();
            } else {
                const lowerProvider = provider.toLowerCase();
                $('#models-table-body tr').each(function() {
                    if (!lowerProvider) {
                        $(this).show();
                    } else {
                        const rowProvider = $(this).find('.model-provider-name').text().trim().toLowerCase();
                        $(this).toggle(rowProvider.indexOf(lowerProvider) > -1);
                    }
                });
            }
        });

        $('#btn-sync-all-models').on('click', function() {
            if (window.Nexus && window.Nexus.showTaskLoader) {
                window.Nexus.showTaskLoader('Syncing all providers...', 'Fetching remote model catalogs...');
            }
            let completed = 0;
            const providers = @json($providers->pluck('id'));
            if (!providers.length) {
                if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                if (window.Nexus && window.Nexus.notify) window.Nexus.notify('No providers configured to sync', 'warning');
                return;
            }

            providers.forEach(function(id) {
                $.ajax({
                    url: `/hub/models/providers/${id}/sync`,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    complete: function() {
                        completed++;
                        if (completed === providers.length) {
                            if (window.Nexus && window.Nexus.hideTaskLoader) window.Nexus.hideTaskLoader();
                            if (window.Nexus && window.Nexus.notify) window.Nexus.notify('All providers synced successfully!', 'success');
                            setTimeout(() => location.reload(), 1000);
                        }
                    }
                });
            });
        });
    });
</script>
@endpush
