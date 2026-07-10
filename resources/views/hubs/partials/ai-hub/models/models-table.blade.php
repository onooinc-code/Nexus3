<div class="card card-dashboard border-secondary p-0">
    <div class="table-responsive">
        <table class="table table-dark-custom table-hover align-middle mb-0" id="modelsTable">
            <thead>
                <tr>
                    <th class="ps-4">Model Name</th>
                    <th>Provider</th>
                    <th>Context Window</th>
                    <th>Input Cost (1M)</th>
                    <th>Output Cost (1M)</th>
                    <th>Tier</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($models as $model)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-regular fa-star text-muted cursor-pointer" onclick="$(this).toggleClass('fa-regular fa-solid text-warning text-muted')"></i>
                                <span class="fw-bold text-light">{{ $model->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-opacity-25 border border-opacity-50 
                                {{ str_contains(strtolower($model->provider?->name ?? ''), 'openai') ? 'bg-success text-success border-success' : 'bg-primary text-primary border-primary' }}">
                                {{ $model->provider?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $windowVal = $model->context_window ?? 0;
                                $progressWidth = 50;
                                $labelText = $windowVal ? $windowVal : 'N/A';
                                if ($windowVal >= 1000000) {
                                    $labelText = ($windowVal / 1000000) . 'M';
                                    $progressWidth = 100;
                                } elseif ($windowVal >= 1000) {
                                    $labelText = ($windowVal / 1000) . 'K';
                                    $progressWidth = 70;
                                }
                            @endphp
                            <div class="d-flex align-items-center gap-2" style="width: 150px;">
                                <div class="progress-thin flex-grow-1 bg-secondary">
                                    <div class="progress-bar bg-info" style="width: {{ $progressWidth }}%"></div>
                                </div>
                                <span class="small text-muted" style="font-family: 'JetBrains Mono';">{{ $labelText }}</span>
                            </div>
                        </td>
                        <td class="font-monospace text-muted">${{ number_format((float) ($model->input_cost_per_m ?? 0.0), 2) }}</td>
                        <td class="font-monospace text-muted">${{ number_format((float) ($model->output_cost_per_m ?? 0.0), 2) }}</td>
                        <td>
                            @php
                                $tier = strtolower($model->quality_tier ?? 'standard');
                                $tierClass = 'bg-success text-success border-success';
                                if ($tier === 'premium') {
                                    $tierClass = 'bg-opacity-25 text-purple border-opacity-50';
                                } elseif ($tier === 'basic') {
                                    $tierClass = 'bg-secondary text-muted border-secondary';
                                }
                            @endphp
                            <span class="badge bg-opacity-25 border {{ $tierClass }}" 
                                  @if($tier === 'premium') style="color:#b57edd;border-color:#b57edd;background-color:rgba(181,126,221,0.25);" @endif>
                                {{ ucfirst($tier) }}
                            </span>
                        </td>
                        <td>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input toggle-model-checkbox" type="checkbox" role="switch" 
                                       data-id="{{ $model->id }}" {{ $model->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-link text-info p-0 mx-1"><i class="fa-solid fa-chart-line"></i></button>
                            <button class="btn btn-sm btn-link text-muted p-0 mx-1"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No models configured. Run model sync or database seeders.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (!$.fn.DataTable.isDataTable('#modelsTable')) {
            $('#modelsTable').DataTable({
                paging: true,
                searching: false,
                info: true,
                lengthChange: false,
                pageLength: 10,
                dom: '<"top">rt<"bottom d-flex justify-content-between p-3"ip><"clear">',
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ models",
                    paginate: {
                        previous: "<i class='fa-solid fa-chevron-left'></i>",
                        next: "<i class='fa-solid fa-chevron-right'></i>"
                    }
                }
            });
        }

        $('.toggle-model-checkbox').on('change', function() {
            const id = $(this).data('id');
            const isActive = $(this).is(':checked') ? 1 : 0;
            const url = '{{ route("hub.models.toggle", ["id" => "__ID__"]) }}'.replace('__ID__', id);
            
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
                    window.Nexus.notify('Model status updated successfully', 'success');
                },
                error: function(xhr) {
                    window.Nexus.hideTaskLoader();
                    window.Nexus.notify('Failed to toggle model status', 'error');
                }
            });
        });
    });
</script>
@endpush
