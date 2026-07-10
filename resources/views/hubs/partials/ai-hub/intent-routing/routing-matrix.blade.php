<div class="card card-dashboard border-secondary p-0 mb-4">
    <div class="card-header bg-transparent border-secondary py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-light fw-bold">Active Routing Matrix</h6>
        <div class="text-muted small">Showing {{ $routingRules->count() }} rules</div>
    </div>
    <div class="table-responsive">
        <table class="table table-dark-custom table-hover align-middle mb-0" id="routingTable">
            <thead>
                <tr>
                    <th class="ps-4">Intent</th>
                    <th>Primary Route</th>
                    <th>Fallback Route</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routingRules as $rule)
                    <tr>
                        <td class="ps-4 fw-bold text-light">{{ $rule->intent_name }}</td>
                        <td>
                            @if($rule->defaultModel)
                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">
                                    {{ $rule->defaultModel->name }}
                                </span>
                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">via {{ $rule->defaultProvider?->name ?? 'N/A' }}</div>
                            @else
                                <span class="text-muted small">None</span>
                            @endif
                        </td>
                        <td>
                            @if($rule->fallbackModel)
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">
                                    {{ $rule->fallbackModel->name }}
                                </span>
                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">via {{ $rule->fallbackProvider?->name ?? 'N/A' }}</div>
                            @else
                                <span class="text-muted small">None</span>
                            @endif
                        </td>
                        <td>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input toggle-routing-checkbox" type="checkbox" role="switch" 
                                       data-id="{{ $rule->id }}" {{ $rule->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-link text-info p-0 mx-1"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button class="btn btn-sm btn-link text-danger p-0 mx-1 delete-routing-btn" data-id="{{ $rule->id }}"><i class="fa-solid fa-trash-can"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No intent routing rules configured. Add a rule to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $('.toggle-routing-checkbox').on('change', function() {
            const id = $(this).data('id');
            const isActive = $(this).is(':checked') ? 1 : 0;
            const url = '{{ route("hub.models.routing.toggle", ["id" => "__ID__"]) }}'.replace('__ID__', id);
            
            window.Nexus.showTaskLoader();
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: isActive
                },
                success: function(res) {
                    window.Nexus.hideTaskLoader();
                    if(res.success) {
                        window.Nexus.notify(res.message, 'success');
                    }
                },
                error: function(xhr) {
                    window.Nexus.hideTaskLoader();
                    window.Nexus.notify('Failed to toggle routing rule status', 'error');
                }
            });
        });

        $('.delete-routing-btn').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const url = '{{ route("hub.models.routing.delete", ["id" => "__ID__"]) }}'.replace('__ID__', id);
            
            if(!confirm('Are you sure you want to delete this routing rule?')) {
                return;
            }
            
            window.Nexus.showTaskLoader();
            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    window.Nexus.hideTaskLoader();
                    if(res.success) {
                        window.Nexus.notify(res.message, 'success');
                        setTimeout(() => location.reload(), 500);
                    }
                },
                error: function(xhr) {
                    window.Nexus.hideTaskLoader();
                    window.Nexus.notify('Failed to delete routing rule', 'error');
                }
            });
        });
    });
</script>
@endpush
