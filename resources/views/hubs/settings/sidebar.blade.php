<div class="col-md-3">
    <div class="h-100" style="background:rgba(22,27,34,0.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:12px;">
        <div class="px-1 mb-2" style="font-size:0.68rem;font-family:'JetBrains Mono',monospace;color:#8b949e;letter-spacing:.06em;text-transform:uppercase;">Categories</div>
        <div class="nav flex-column nav-pills w-100 settings-sortable-groups" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            @foreach($settings as $group => $items)
                <button class="nav-link settings-sidebar-btn d-flex align-items-center justify-content-between w-100 {{ $loop->first ? 'active' : '' }}"
                        id="v-pills-{{ $group }}-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#v-pills-{{ $group }}"
                        type="button" role="tab"
                        aria-controls="v-pills-{{ $group }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        style="display: flex !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; direction: ltr !important;">
                    <i class="fa-solid fa-grip-vertical drag-handle flex-shrink-0" style="font-size: 0.9rem; cursor: grab; color: #4b5563;margin-right: 10px;"></i>
                    <span style="margin-right: 5px;" class="btn-icon flex-shrink-0">
                        <i class="fa-solid {{ $loop->first ? 'fa-sliders' : 'fa-cog' }}"></i>
                    </span>
                    <span class="fw-bold text-start text-truncate flex-grow-1" style="font-size: 0.95rem;margin-right: 5px; min-width: 0;">{{ ucfirst($group) }}</span>
                    <span class="badge animated-badge flex-shrink-0 ms-auto" style="margin-left: auto !important;margin-right: 5px">{{ count($items) }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
