@extends('layouts.app')

@section('page_title', 'Credentials & Environment Control Hub')

@push('styles')
<style>
    .credentials-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    .glass-card {
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .glass-card:hover {
        border-color: rgba(34, 197, 94, 0.3);
    }
    .category-pill-btn {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #94a3b8;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 500;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .category-pill-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
    }
    .category-pill-btn.active {
        background: rgba(34, 197, 94, 0.15) !important;
        border-color: rgba(34, 197, 94, 0.4) !important;
        color: #4ade80 !important;
    }
    .badge-live {
        background: rgba(34, 197, 94, 0.15);
        color: #4ade80;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .badge-warn {
        background: rgba(234, 179, 8, 0.15);
        color: #facc15;
        border: 1px solid rgba(234, 179, 8, 0.3);
    }
    .badge-danger {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .field-row-item {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 6px 10px;
    }
    .field-row-item:hover {
        background: rgba(30, 41, 59, 0.95);
        border-color: rgba(34, 197, 94, 0.2);
    }
    .agent-floating-window {
        position: fixed;
        z-index: 1060;
        bottom: 24px;
        right: 24px;
        width: 460px;
        max-width: calc(100vw - 48px);
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 20px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255,255,255,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .agent-chat-message {
        animation: fadeIn 0.3s ease forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Scrollbar for chat */
    .chat-scroll::-webkit-scrollbar { width: 6px; }
    .chat-scroll::-webkit-scrollbar-track { background: transparent; }
    .chat-scroll::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.3); border-radius: 10px; }
    .chat-scroll::-webkit-scrollbar-thumb:hover { background: rgba(139, 92, 246, 0.5); }
    .agent-drag-handle {
        cursor: move;
        user-select: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-3 p-md-4 credentials-wrapper" id="credentials-hub-root">

    <!-- Top Header Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-2 text-dark font-mono fw-bold d-flex align-items-center justify-content-center" 
                style="background: linear-gradient(135deg, #10b981, #059669); width: 44px; height: 44px; font-size: 1.4rem;">
                🔑
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-light">Nexus Credentials Hub</h4>
                <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
                    <span>Centralized Environment & System Vault</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-mono ms-1">MySQL Active</span>
                    <span class="badge bg-dark text-muted border border-secondary font-mono" title="Global Keyboard Shortcut">Shift + 1 Sidebar</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
            <!-- Search Box -->
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="search-input" placeholder="Search title, URL, IP..." 
                    class="form-control form-control-sm bg-dark text-light border-secondary ps-5 rounded-3" style="width: 200px;">
            </div>

            <!-- Activity Logs Button -->
            <button type="button" class="btn btn-sm btn-dark border-secondary text-gray-300 d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#activityLogsModal">
                <i class="fa-solid fa-clock-rotate-left text-warning"></i>
                <span>Logs</span>
            </button>

            <!-- Agent Toggle -->
            <button type="button" id="toggle-agent-btn" class="btn btn-sm text-purple-300 border-purple-500 d-flex align-items-center gap-2"
                style="background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.4); color: #c084fc;">
                <i class="fa-solid fa-robot text-purple-400"></i>
                <span class="d-none d-sm-inline">nexus-manager Agent</span>
            </button>

            <!-- Test All -->
            <button type="button" id="test-all-btn" class="btn btn-sm btn-success d-flex align-items-center gap-2 font-mono">
                <i class="fa-solid fa-vial-circle-check"></i>
                <span>Test All</span>
            </button>

            <!-- Add Button -->
            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addCredentialModal">
                <i class="fa-solid fa-plus"></i>
                <span>Add</span>
            </button>
        </div>
    </div>

    <!-- Metrics Bar -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small">Total Vault Items</span>
                    <h3 class="fw-bold text-light mb-0 mt-1" id="metric-total">{{ $credentials->count() }}</h3>
                </div>
                <div class="rounded-3 p-3 text-primary bg-primary-subtle border border-primary-subtle fs-4">
                    <i class="fa-solid fa-key"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small">Tested Active</span>
                    <h3 class="fw-bold text-success mb-0 mt-1" id="metric-active">{{ $testedActiveCount }}</h3>
                </div>
                <div class="rounded-3 p-3 text-success bg-success-subtle border border-success-subtle fs-4">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small">AI & LLMs</span>
                    <h3 class="fw-bold text-purple-400 mb-0 mt-1" id="metric-ai" style="color: #c084fc;">{{ $aiProvidersCount }}</h3>
                </div>
                <div class="rounded-3 p-3 text-purple bg-purple-subtle border border-purple-subtle fs-4">
                    <i class="fa-solid fa-brain" style="color: #c084fc;"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small">Database Status</span>
                    <h3 class="fw-bold text-success mb-0 mt-1 fs-6 font-mono">
                        <span class="spinner-grow spinner-grow-sm text-success me-1"></span>
                        <span>MySQL Active</span>
                    </h3>
                </div>
                <div class="rounded-3 p-3 text-success bg-success-subtle border border-success-subtle fs-4">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Horizontal Category Filter Pill Bar -->
    <div class="glass-card p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 overflow-auto py-1 w-100 w-md-auto" id="category-pills" style="scrollbar-width: thin;">
                @foreach($categories as $cat)
                    <button type="button" class="category-pill-btn d-flex align-items-center gap-2 {{ $loop->first ? 'active' : '' }}" 
                        data-category="{{ $cat['id'] }}">
                        <i class="{{ $cat['icon'] }}"></i>
                        <span>{{ $cat['name'] }}</span>
                        <span class="badge bg-dark text-muted font-mono rounded-pill" id="cat-count-{{ $cat['id'] }}">
                            {{ $cat['id'] === 'all' ? $credentials->count() : $credentials->where('category', $cat['id'])->count() }}
                        </span>
                    </button>
                @endforeach
            </div>

            <button type="button" id="copy-json-btn" class="btn btn-sm btn-dark border-secondary text-gray-300 ms-auto">
                <i class="fa-solid fa-copy text-success me-1"></i> Copy View as JSON
            </button>
        </div>
    </div>

    <!-- Credentials Cards Responsive Grid -->
    <div class="row g-3" id="credentials-cards-container">
        @foreach($credentials as $item)
            <div class="col-12 col-md-6 col-xxl-4 credential-card-wrapper" data-category="{{ $item->category }}" data-title="{{ strtolower($item->title) }}" data-fields="{{ strtolower(json_encode($item->fields)) }}">
                <div class="glass-card p-3 p-md-4 h-100 d-flex flex-column justify-content-between overflow-hidden">
                    
                    <!-- Card Header -->
                    <div class="mb-3">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2.5 overflow-hidden" style="max-width: calc(100% - 95px);">
                                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0 border {{ $item->icon_bg ?? 'bg-success-subtle text-success border-success-subtle' }}" style="width: 40px; height: 40px;">
                                    <i class="{{ $item->icon }} fs-5"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="fw-bold text-light mb-0 text-truncate" title="{{ $item->title }}">{{ $item->title }}</h6>
                                    <small class="text-muted font-mono text-truncate d-block" title="{{ $item->subtitle }}" style="font-size: 0.72rem;">{{ $item->subtitle }}</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <span class="badge {{ $item->test_status === 'success' ? 'badge-live' : ($item->test_status === 'warn' ? 'badge-warn' : 'badge-danger') }} font-mono" style="font-size: 0.72rem;">
                                    <i class="fa-solid {{ $item->test_status === 'success' ? 'fa-check' : 'fa-triangle-exclamation' }} me-1"></i>
                                    {{ $item->test_code ?? $item->test_status }}
                                </span>

                                <button type="button" class="btn btn-sm btn-dark border-secondary btn-test-single" data-id="{{ $item->id }}" title="Run Health Check">
                                    <i class="fa-solid fa-play text-muted"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Key-Value Fields List -->
                        <div class="d-flex flex-column gap-2 mb-2">
                            @foreach($item->fields ?? [] as $k => $v)
                                <div class="field-row-item d-flex align-items-center justify-content-between gap-2 field-row"
                                    data-item-id="{{ $item->id }}" data-key="{{ $k }}" data-value="{{ $v }}">
                                    
                                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                        <button type="button" class="btn btn-sm btn-link p-0 text-muted hover-text-success btn-copy-text" data-text="{{ $v }}" title="Copy {{ $k }}">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                        <span class="text-muted font-sans text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $k }}:</span>
                                    </div>

                                    <div class="font-mono text-light text-truncate text-end flex-grow-1 field-value-display" style="font-size: 0.8rem;" title="{{ $v }}">
                                        {{ $v }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 small text-muted">
                        <span style="font-size: 0.7rem;">MySQL Persistence</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-dark border-secondary py-0 px-2 btn-copy-card" data-item-id="{{ $item->id }}" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-copy me-1"></i> Copy Card
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-item" data-id="{{ $item->id }}" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Draggable Floating AI Agent Window (`nexus-manager Agent`) -->
    <div id="agent-modal-window" class="agent-floating-window" style="display: none;">
        <!-- Header (Drag Handle) -->
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between agent-drag-handle" style="background: rgba(139, 92, 246, 0.15); border-color: rgba(139, 92, 246, 0.2) !important;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-grip-dots-vertical text-purple-400 me-2" title="Drag Window" style="opacity: 0.7;"></i>
                <div class="position-relative d-flex align-items-center justify-content-center bg-purple-500 rounded-circle" style="width: 28px; height: 28px;">
                    <i class="fa-solid fa-robot text-white" style="font-size: 0.75rem;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-dark rounded-circle"></span>
                </div>
                <h6 class="fw-bold text-light mb-0" style="font-size: 0.9rem; letter-spacing: 0.3px;">nexus-manager Agent</h6>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" id="agent-min-btn" class="btn btn-sm btn-link text-purple-200 hover-text-white text-decoration-none px-2" title="Minimize"><i class="fa-solid fa-minus"></i></button>
                <button type="button" id="agent-close-btn" class="btn btn-sm btn-link text-purple-200 hover-text-white text-decoration-none px-2"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <!-- Chat Body with Restored History -->
        <div class="p-3 overflow-auto font-sans chat-scroll" id="agent-chat-body" style="height: 380px; background: rgba(15, 23, 42, 0.4);">
            
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-start gap-2 agent-chat-message">
                    <div class="bg-purple-500 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px;">
                        <i class="fa-solid fa-robot text-white" style="font-size: 0.7rem;"></i>
                    </div>
                    <div class="p-2.5 rounded-3 text-purple-100" style="background: rgba(139, 92, 246, 0.2); border: 1px solid rgba(139, 92, 246, 0.25); border-top-left-radius: 4px !important; font-size: 0.85rem; line-height: 1.5;">
                        أهلاً يا هدرا! ابعتلي بيانات أي سيرفر أو منصة هنا في مربع الشات، وسأقوم بتحليلها وحفظها فوراً في MySQL! يمكنك أيضاً أن تطلب مني "تحديث الصفحة" أو "قراءة السجلات".
                    </div>
                </div>

                <!-- Restored Chat History -->
                @foreach($chatHistory as $msg)
                    @if($msg->role === 'user')
                        <div class="d-flex align-items-start justify-content-end gap-2 agent-chat-message">
                            <div class="p-2.5 rounded-3 text-light" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.1); border-top-right-radius: 4px !important; font-size: 0.85rem; line-height: 1.5; max-width: 85%;">
                                <div style="white-space: pre-wrap;">{{ $msg->content }}</div>
                            </div>
                            <div class="bg-secondary rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px;">
                                <i class="fa-solid fa-user text-white" style="font-size: 0.7rem;"></i>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-start gap-2 agent-chat-message">
                            <div class="bg-purple-500 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px;">
                                <i class="fa-solid fa-robot text-white" style="font-size: 0.7rem;"></i>
                            </div>
                            <div class="p-2.5 rounded-3 text-purple-100" style="background: rgba(139, 92, 246, 0.2); border: 1px solid rgba(139, 92, 246, 0.25); border-top-left-radius: 4px !important; font-size: 0.85rem; line-height: 1.5; max-width: 85%;">
                                <div style="white-space: pre-wrap;">{{ $msg->content }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Footer Input (Ctrl+Enter to Send, Enter for newline) -->
        <div id="agent-input-container" class="p-3 border-top" style="background: rgba(15, 23, 42, 0.8); border-color: rgba(255,255,255,0.05) !important;">
            <div class="position-relative">
                <textarea id="agent-prompt-input" rows="2" class="form-control bg-dark text-light border-secondary chat-scroll pe-5" placeholder="Type a message or paste credentials..." style="border-radius: 12px; resize: none; font-size: 0.85rem; box-shadow: none;"></textarea>
                <button type="button" id="agent-send-btn" class="btn btn-sm btn-purple position-absolute bottom-0 end-0 m-2 rounded-circle d-flex align-items-center justify-content-center" style="background: #8b5cf6; border: none; width: 28px; height: 28px; color: white; transition: 0.2s;">
                    <i class="fa-solid fa-paper-plane" style="font-size: 0.7rem;"></i>
                </button>
            </div>
            <div class="mt-2 text-center text-muted font-mono" style="font-size: 0.65rem;">
                <kbd class="bg-secondary text-light px-1">Ctrl+Enter</kbd> to Send &bull; <kbd class="bg-secondary text-light px-1">Enter</kbd> for Newline
            </div>
        </div>
    </div>

    <!-- Activity Logs Modal -->
    <div class="modal fade" id="activityLogsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-card border-secondary text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>System Activity Logs</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-dark table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-muted font-mono">
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Title</th>
                                    <th>Details</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLogs as $log)
                                    <tr>
                                        <td class="font-mono text-muted" style="font-size: 0.75rem;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <span class="badge {{ $log->action === 'created' ? 'bg-success-subtle text-success border border-success' : ($log->action === 'updated' ? 'bg-warning-subtle text-warning border border-warning' : ($log->action === 'deleted' ? 'bg-danger-subtle text-danger border border-danger' : 'bg-info-subtle text-info border border-info')) }}">
                                                {{ strtoupper($log->action) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-light">{{ $log->title }}</td>
                                        <td class="text-muted text-truncate" style="max-width: 260px;" title="{{ $log->details }}">{{ $log->details }}</td>
                                        <td class="font-mono text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">No activity logs recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Credential Modal -->
    <div class="modal fade" id="addCredentialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-secondary text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-success me-2"></i>Add Custom Credential</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body space-y-3">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Title</label>
                        <input type="text" id="new-item-title" class="form-control bg-dark text-light border-secondary" placeholder="e.g. aaPanel Server (Servertest)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Category</label>
                        <select id="new-item-category" class="form-select bg-dark text-light border-secondary">
                            @foreach($categories as $c)
                                @if($c['id'] !== 'all')
                                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Fields (Key: Value lines)</label>
                        <textarea id="new-item-fields" rows="5" class="form-control bg-dark text-light border-secondary font-mono small" placeholder="Internet URL: https://...\nInternal URL: https://...\nUsername: admin\nPassword: secret"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-sm btn-dark border-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="save-new-item-btn" class="btn btn-sm btn-success fw-bold">Save Credential to MySQL</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Make Agent Window Draggable using jQuery UI if available or Pointer Dragging
    const agentModal = document.getElementById('agent-modal-window');
    if (typeof $ !== 'undefined' && $.fn.draggable) {
        $(agentModal).draggable({ handle: '.agent-drag-handle' });
    } else {
        let isDragging = false, startX, startY, initialLeft, initialTop;
        const dragHandle = agentModal.querySelector('.agent-drag-handle');
        
        dragHandle.addEventListener('mousedown', function(e) {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            const rect = agentModal.getBoundingClientRect();
            initialLeft = rect.left;
            initialTop = rect.top;
            agentModal.style.bottom = 'auto';
            agentModal.style.right = 'auto';
            agentModal.style.left = initialLeft + 'px';
            agentModal.style.top = initialTop + 'px';
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            agentModal.style.left = (initialLeft + dx) + 'px';
            agentModal.style.top = (initialTop + dy) + 'px';
        });

        document.addEventListener('mouseup', function() { isDragging = false; });
    }

    // Toggle Agent Window
    document.getElementById('toggle-agent-btn').addEventListener('click', () => {
        agentModal.style.display = agentModal.style.display === 'none' ? 'flex' : 'none';
        if (agentModal.style.display === 'flex') {
            const chatBody = document.getElementById('agent-chat-body');
            if (chatBody) {
                // Use a slight timeout to ensure display:flex rendering is complete before calculating scrollHeight
                setTimeout(() => chatBody.scrollTop = chatBody.scrollHeight, 50);
            }
        }
    });
    document.getElementById('agent-close-btn').addEventListener('click', () => {
        agentModal.style.display = 'none';
    });
    
    // Minimize / Expand Agent Window
    let isMinimized = false;
    document.getElementById('agent-min-btn').addEventListener('click', function() {
        const body = document.getElementById('agent-chat-body');
        const input = document.getElementById('agent-input-container');
        isMinimized = !isMinimized;
        body.style.display = isMinimized ? 'none' : 'block';
        input.style.display = isMinimized ? 'none' : 'block';
        this.innerHTML = isMinimized ? '<i class="fa-regular fa-square"></i>' : '<i class="fa-solid fa-minus"></i>';
    });

    // Search and Category Filtering
    let currentCategory = 'all';

    function filterCredentials() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.credential-card-wrapper');

        cards.forEach(card => {
            const cat = card.getAttribute('data-category');
            const title = card.getAttribute('data-title') || '';
            const fields = card.getAttribute('data-fields') || '';

            const matchesCategory = currentCategory === 'all' || cat === currentCategory;
            const matchesSearch = !query || title.includes(query) || fields.includes(query);

            if (matchesCategory && matchesSearch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    document.getElementById('search-input').addEventListener('input', filterCredentials);

    document.querySelectorAll('#category-pills button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#category-pills button').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');

            currentCategory = this.getAttribute('data-category');
            filterCredentials();
        });
    });

    // Copy text handler
    document.addEventListener('click', function (e) {
        const copyBtn = e.target.closest('.btn-copy-text');
        if (copyBtn) {
            const text = copyBtn.getAttribute('data-text');
            navigator.clipboard.writeText(text);
            Nexus.notify('Copied: ' + text.substring(0, 25) + '...', 'success');
        }
    });

    // Copy entire card
    document.addEventListener('click', function (e) {
        const copyCardBtn = e.target.closest('.btn-copy-card');
        if (copyCardBtn) {
            const cardWrapper = copyCardBtn.closest('.credential-card-wrapper');
            const title = cardWrapper.querySelector('h6').innerText;
            let output = `=== ${title} ===\n`;

            cardWrapper.querySelectorAll('.field-row').forEach(row => {
                const k = row.getAttribute('data-key');
                const v = row.getAttribute('data-value');
                output += `${k}: ${v}\n`;
            });

            navigator.clipboard.writeText(output);
            Nexus.notify('Card copied to clipboard!', 'success');
        }
    });

    // Copy view as JSON
    document.getElementById('copy-json-btn').addEventListener('click', function () {
        const visibleCards = [];
        document.querySelectorAll('.credential-card-wrapper').forEach(card => {
            if (card.style.display !== 'none') {
                const title = card.querySelector('h6').innerText;
                const fields = {};
                card.querySelectorAll('.field-row').forEach(row => {
                    fields[row.getAttribute('data-key')] = row.getAttribute('data-value');
                });
                visibleCards.push({ title, fields });
            }
        });
        navigator.clipboard.writeText(JSON.stringify(visibleCards, null, 2));
        Nexus.notify('JSON view copied!', 'success');
    });

    // Inline Double Click Field Editor
    document.addEventListener('dblclick', function (e) {
        const fieldRow = e.target.closest('.field-row');
        if (fieldRow && !fieldRow.querySelector('input')) {
            const itemId = fieldRow.getAttribute('data-item-id');
            const key = fieldRow.getAttribute('data-key');
            const oldValue = fieldRow.getAttribute('data-value');
            const displayEl = fieldRow.querySelector('.field-value-display');

            displayEl.innerHTML = `
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control form-control-sm bg-dark text-success border-success font-mono" value="${oldValue}">
                    <button class="btn btn-sm btn-success btn-save-inline">✓</button>
                    <button class="btn btn-sm btn-secondary btn-cancel-inline">✕</button>
                </div>
            `;

            const input = displayEl.querySelector('input');
            input.focus();

            const saveInline = async () => {
                const newValue = input.value;
                try {
                    const res = await fetch(`/hub/credentials/${itemId}/field`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ key: key, value: newValue })
                    });
                    if (res.ok) {
                        fieldRow.setAttribute('data-value', newValue);
                        displayEl.innerText = newValue;
                        Nexus.notify(`Updated ${key} in MySQL!`, 'success');
                    }
                } catch (err) {
                    displayEl.innerText = oldValue;
                }
            };

            displayEl.querySelector('.btn-save-inline').addEventListener('click', saveInline);
            displayEl.querySelector('.btn-cancel-inline').addEventListener('click', () => {
                displayEl.innerText = oldValue;
            });
        }
    });

    // Add Credential Modal Save
    document.getElementById('save-new-item-btn').addEventListener('click', async function () {
        const title = document.getElementById('new-item-title').value.trim();
        const category = document.getElementById('new-item-category').value;
        const rawFields = document.getElementById('new-item-fields').value;

        if (!title) {
            Nexus.notify('Please enter a title', 'error');
            return;
        }

        try {
            const res = await fetch('/hub/credentials', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ title, category, raw_fields: rawFields })
            });

            if (res.ok) {
                Nexus.notify('New credential saved to MySQL!', 'success');
                location.reload();
            }
        } catch (err) {
            Nexus.notify('Failed to save credential', 'error');
        }
    });

    // Delete Credential
    document.addEventListener('click', async function (e) {
        const delBtn = e.target.closest('.btn-delete-item');
        if (delBtn && confirm('Are you sure you want to delete this credential from MySQL?')) {
            const id = delBtn.getAttribute('data-id');
            try {
                const res = await fetch(`/hub/credentials/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (res.ok) {
                    delBtn.closest('.credential-card-wrapper').remove();
                    Nexus.notify('Credential deleted from MySQL!', 'success');
                }
            } catch (err) {}
        }
    });

    // Single Health Test
    document.addEventListener('click', async function (e) {
        const testBtn = e.target.closest('.btn-test-single');
        if (testBtn) {
            const id = testBtn.getAttribute('data-id');
            testBtn.querySelector('i').className = 'fa-solid fa-spinner animate-spin text-success';
            try {
                const res = await fetch(`/hub/credentials/${id}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (res.ok) {
                    Nexus.notify('Health check completed!', 'success');
                    setTimeout(() => location.reload(), 500);
                }
            } catch (err) {
                testBtn.querySelector('i').className = 'fa-solid fa-play text-muted';
            }
        }
    });

    // Test All Credentials
    document.getElementById('test-all-btn').addEventListener('click', async function () {
        const btn = this;
        btn.disabled = true;
        btn.querySelector('i').className = 'fa-solid fa-spinner animate-spin';
        try {
            const res = await fetch('/hub/credentials/test-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (res.ok) {
                Nexus.notify('Tested all credentials in MySQL!', 'success');
                setTimeout(() => location.reload(), 600);
            }
        } catch (err) {
            btn.disabled = false;
            btn.querySelector('i').className = 'fa-solid fa-vial-circle-check';
        }
    });

    // AI Agent Chat Send Logic (Ctrl+Enter to Send, Enter for newline)
    let agentHistory = [];
    
    // Auto-scroll to bottom initially
    const initChatBody = document.getElementById('agent-chat-body');
    if (initChatBody) initChatBody.scrollTop = initChatBody.scrollHeight;

    async function sendAgentPrompt() {
        const input = document.getElementById('agent-prompt-input');
        const prompt = input.value.trim();
        if (!prompt) return;

        const chatBody = document.getElementById('agent-chat-body');
        const chatWrapper = chatBody.querySelector('.d-flex.flex-column');
        
        chatWrapper.innerHTML += `
            <div class="d-flex align-items-start justify-content-end gap-2 agent-chat-message">
                <div class="p-2.5 rounded-3 text-light" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.1); border-top-right-radius: 4px !important; font-size: 0.85rem; line-height: 1.5; max-width: 85%;">
                    <div style="white-space: pre-wrap;">${prompt}</div>
                </div>
                <div class="bg-secondary rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px;">
                    <i class="fa-solid fa-user text-white" style="font-size: 0.7rem;"></i>
                </div>
            </div>
        `;
        input.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;

        chatWrapper.innerHTML += `
            <div id="agent-thinking-indicator" class="d-flex align-items-center gap-2 agent-chat-message">
                <div class="bg-purple-500 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px;">
                    <i class="fa-solid fa-robot text-white" style="font-size: 0.7rem;"></i>
                </div>
                <div class="text-purple-300 font-mono small py-2 px-1"><i class="fa-solid fa-spinner animate-spin me-2"></i>nexus-manager Agent is processing...</div>
            </div>
        `;
        chatBody.scrollTop = chatBody.scrollHeight;

        try {
            const res = await fetch('/hub/credentials/agent/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ prompt, history: agentHistory })
            });

            document.getElementById('agent-thinking-indicator')?.remove();

            if (res.ok) {
                const data = await res.json();
                agentHistory.push({ role: 'user', content: prompt });
                agentHistory.push({ role: 'assistant', content: data.reply });

                chatWrapper.innerHTML += `
                    <div class="d-flex align-items-start gap-2 agent-chat-message">
                        <div class="bg-purple-500 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center mt-1" style="width: 28px; height: 28px;">
                            <i class="fa-solid fa-robot text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="p-2.5 rounded-3 text-purple-100" style="background: rgba(139, 92, 246, 0.2); border: 1px solid rgba(139, 92, 246, 0.25); border-top-left-radius: 4px !important; font-size: 0.85rem; line-height: 1.5; max-width: 85%;">
                            <div style="white-space: pre-wrap;">${data.reply}</div>
                        </div>
                    </div>
                `;
                chatBody.scrollTop = chatBody.scrollHeight;

                if (data.refresh) {
                    Nexus.notify('Refreshing to apply latest data...', 'info');
                    setTimeout(() => location.reload(), 2000);
                }
            }
        } catch (err) {
            document.getElementById('agent-thinking-indicator')?.remove();
        }
    }

    document.getElementById('agent-send-btn').addEventListener('click', sendAgentPrompt);

    // Keydown Handler for Agent Input: Ctrl + Enter sends message, Enter alone inserts newline
    document.getElementById('agent-prompt-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                sendAgentPrompt();
            }
        }
    });
});
</script>
@endpush
