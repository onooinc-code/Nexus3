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
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-regular fa-star text-muted cursor-pointer" onclick="$(this).toggleClass('fa-regular fa-solid text-warning text-muted')"></i>
                            <span class="fw-bold text-light">gpt-4o</span>
                        </div>
                    </td>
                    <td><span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">OpenAI</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2" style="width: 150px;">
                            <div class="progress-thin flex-grow-1 bg-secondary"><div class="progress-bar bg-info" style="width: 100%"></div></div>
                            <span class="small text-muted" style="font-family: 'JetBrains Mono';">128K</span>
                        </div>
                    </td>
                    <td class="font-monospace text-muted">$5.00</td>
                    <td class="font-monospace text-muted">$15.00</td>
                    <td><span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50" style="color:#b57edd;border-color:#b57edd;">Premium</span></td>
                    <td>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" checked>
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0 mx-1"><i class="fa-solid fa-chart-line"></i></button>
                        <button class="btn btn-sm btn-link text-muted p-0 mx-1"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-regular fa-star text-muted cursor-pointer" onclick="$(this).toggleClass('fa-regular fa-solid text-warning text-muted')"></i>
                            <span class="fw-bold text-light">gpt-4o-mini</span>
                        </div>
                    </td>
                    <td><span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">OpenAI</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2" style="width: 150px;">
                            <div class="progress-thin flex-grow-1 bg-secondary"><div class="progress-bar bg-info" style="width: 100%"></div></div>
                            <span class="small text-muted" style="font-family: 'JetBrains Mono';">128K</span>
                        </div>
                    </td>
                    <td class="font-monospace text-muted">$0.15</td>
                    <td class="font-monospace text-muted">$0.60</td>
                    <td><span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">Budget</span></td>
                    <td>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" checked>
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0 mx-1"><i class="fa-solid fa-chart-line"></i></button>
                        <button class="btn btn-sm btn-link text-muted p-0 mx-1"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </td>
                </tr>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-star text-warning cursor-pointer" onclick="$(this).toggleClass('fa-regular fa-solid text-warning text-muted')"></i>
                            <span class="fw-bold text-light">gemini-1.5-pro</span>
                        </div>
                    </td>
                    <td><span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">Google</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2" style="width: 150px;">
                            <div class="progress-thin flex-grow-1 bg-secondary"><div class="progress-bar bg-warning" style="width: 100%"></div></div>
                            <span class="small text-muted" style="font-family: 'JetBrains Mono';">2M</span>
                        </div>
                    </td>
                    <td class="font-monospace text-muted">$3.50</td>
                    <td class="font-monospace text-muted">$10.50</td>
                    <td><span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50" style="color:#b57edd;border-color:#b57edd;">Premium</span></td>
                    <td>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" checked>
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-link text-info p-0 mx-1"><i class="fa-solid fa-chart-line"></i></button>
                        <button class="btn btn-sm btn-link text-muted p-0 mx-1"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Initialize DataTable on the models table when tab is loaded
    document.addEventListener("DOMContentLoaded", function() {
        if (!$.fn.DataTable.isDataTable('#modelsTable')) {
            $('#modelsTable').DataTable({
                paging: true,
                searching: false, // Handled by our custom input
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
    });
</script>
@endpush
