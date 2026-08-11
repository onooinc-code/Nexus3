<!-- ═════════════════════════════════════════════════════════════════════
     NEXUS QUEUE & WORKERS DETAILS MODAL
     ═════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="queueDetailsModal" tabindex="-1" aria-labelledby="queueDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: rgba(11, 17, 28, 0.97); backdrop-filter: blur(16px); border: 1px solid rgba(6, 182, 212, 0.35); border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 35px rgba(6, 182, 212, 0.15);">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: rgba(255, 255, 255, 0.02);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 40px; height: 40px; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-diagram-project text-info fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-light mb-0" id="queueDetailsModalLabel" style="font-size: 1.05rem; letter-spacing: -0.2px;">
                            Nexus Queue Telemetry & Workers
                        </h5>
                        <span class="text-muted" style="font-size: 0.76rem; font-family: 'JetBrains Mono', monospace;">
                            Real-time Redis job queues, workers, & Horizon supervisors
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                
                <!-- KPI Status Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card bg-dark bg-opacity-50 border-secondary border-opacity-25 p-3 rounded-3 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.72rem;">Horizon Status</div>
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                                <span id="qm-horizon-orb" class="agent-status-orb online" style="width: 8px; height: 8px;"></span>
                                <span id="qm-horizon-status" class="fw-bold text-success font-mono" style="font-size: 0.95rem;">Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-dark bg-opacity-50 border-secondary border-opacity-25 p-3 rounded-3 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.72rem;">Pending Jobs</div>
                            <div class="fw-bold text-info font-mono mt-1" style="font-size: 1.15rem;" id="qm-total-jobs">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-dark bg-opacity-50 border-secondary border-opacity-25 p-3 rounded-3 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.72rem;">Failed Jobs</div>
                            <div class="fw-bold text-danger font-mono mt-1" style="font-size: 1.15rem;" id="qm-failed-jobs">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-dark bg-opacity-50 border-secondary border-opacity-25 p-3 rounded-3 text-center">
                            <div class="text-muted font-mono" style="font-size: 0.72rem;">Monitored Queues</div>
                            <div class="fw-bold text-light font-mono mt-1" style="font-size: 1.15rem;" id="qm-queues-count">5</div>
                        </div>
                    </div>
                </div>

                <!-- Queues Breakdown Card -->
                <div class="card bg-dark border-secondary border-opacity-25 rounded-3 overflow-hidden mb-3">
                    <div class="card-header bg-black bg-opacity-40 border-bottom border-secondary border-opacity-25 py-2 px-3 d-flex align-items-center justify-content-between">
                        <span class="text-muted font-mono" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-list-check me-1 text-info"></i> Redis Queue Workload Breakdown
                        </span>
                        <button type="button" class="btn btn-link text-info p-0 font-mono text-decoration-none" style="font-size: 0.72rem;" onclick="queueDetailsModalController.fetchTelemetry()">
                            <i class="fa-solid fa-rotate me-1"></i> Refresh Now
                        </button>
                    </div>
                    <div class="list-group list-group-flush" id="qm-queues-list">
                        <!-- Dynamic Queue Rows rendered here -->
                        <div class="p-3 text-center text-muted">Loading queue metrics...</div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3 d-flex align-items-center justify-content-between" style="background: rgba(255, 255, 255, 0.01);">
                <span class="text-muted font-mono" style="font-size: 0.72rem;" id="qm-last-updated">Updated: Just now</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal" style="font-size: 0.8rem;">Close</button>
                    <a href="/horizon" target="_blank" class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #06b6d4, #3b82f6); border: none; font-size: 0.82rem;">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Open Horizon Dashboard</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
window.queueDetailsModalController = {
    modalEl: null,
    bootstrapModal: null,
    pollInterval: null,

    init() {
        this.modalEl = document.getElementById('queueDetailsModal');
        const pillBtn = document.getElementById('queue-status-pill');

        if (pillBtn && this.modalEl) {
            pillBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!this.bootstrapModal) {
                    this.bootstrapModal = new bootstrap.Modal(this.modalEl);
                }
                this.bootstrapModal.show();
                this.fetchTelemetry();
                this.startPolling();
            });
        }

        if (this.modalEl) {
            this.modalEl.addEventListener('hidden.bs.modal', () => {
                this.stopPolling();
            });
        }
    },

    startPolling() {
        this.stopPolling();
        this.pollInterval = setInterval(() => this.fetchTelemetry(), 5000);
    },

    stopPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);
        this.pollInterval = null;
    },

    async fetchTelemetry() {
        try {
            const res = await fetch('/system/queue-details', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    this.render(data);
                }
            }
        } catch(e) {
            console.warn('[QueueDetailsModal] Telemetry fetch error:', e);
        }
    },

    render(data) {
        const horizonOrb = document.getElementById('qm-horizon-orb');
        const horizonStatus = document.getElementById('qm-horizon-status');
        const totalJobs = document.getElementById('qm-total-jobs');
        const failedJobs = document.getElementById('qm-failed-jobs');
        const queuesCount = document.getElementById('qm-queues-count');
        const queuesList = document.getElementById('qm-queues-list');
        const lastUpdated = document.getElementById('qm-last-updated');

        if (totalJobs) totalJobs.innerText = data.total_jobs || 0;
        if (failedJobs) failedJobs.innerText = data.failed_jobs || 0;
        if (queuesCount) queuesCount.innerText = (data.queues || []).length;

        if (horizonStatus && horizonOrb) {
            const isRunning = data.status === 'running' || data.total_jobs >= 0;
            horizonStatus.innerText = isRunning ? 'Active' : 'Offline';
            horizonStatus.className = isRunning ? 'fw-bold text-success font-mono' : 'fw-bold text-danger font-mono';
            horizonOrb.className = isRunning ? 'agent-status-orb online' : 'agent-status-orb offline';
        }

        if (lastUpdated) {
            lastUpdated.innerText = 'Updated: ' + new Date().toLocaleTimeString();
        }

        if (queuesList && Array.isArray(data.queues)) {
            queuesList.innerHTML = data.queues.map(q => {
                const isBusy = q.size > 0;
                const statusBadge = isBusy 
                    ? '<span class="badge bg-warning bg-opacity-25 text-warning font-mono" style="font-size:0.7rem;"><i class="fa-solid fa-spinner fa-spin me-1"></i> Processing</span>'
                    : '<span class="badge bg-success bg-opacity-25 text-success font-mono" style="font-size:0.7rem;"><i class="fa-solid fa-check me-1"></i> Idle</span>';
                
                return `
                    <div class="list-group-item bg-transparent text-light border-secondary border-opacity-10 d-flex align-items-center justify-content-between py-3 px-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-layer-group text-info" style="font-size: 0.85rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-light font-mono" style="font-size: 0.85rem;">queues:${q.name}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Horizon Worker Group Queue</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end">
                                <div class="fw-bold font-mono text-light" style="font-size: 0.9rem;">${q.size} <span class="text-muted" style="font-size: 0.7rem;">Jobs</span></div>
                            </div>
                            ${statusBadge}
                        </div>
                    </div>`;
            }).join('');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.queueDetailsModalController.init();
});
</script>
