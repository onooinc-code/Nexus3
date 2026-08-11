<div class="tasks-glass-panel px-3 d-flex align-items-center" style="height: var(--tasks-topnav-height); overflow-x: auto; white-space: nowrap; gap: 4px;">
    <!-- Brand/Title -->
    <div class="d-flex align-items-center me-4">
        <i class="fa-solid fa-list-check me-2" style="color: var(--tasks-primary);"></i>
        <span class="fw-bold font-inter" style="color: #fff; letter-spacing: -0.5px; font-size: 1.1rem;">TasksHub</span>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-pills" id="taskhub-nav" role="tablist" style="flex-wrap: nowrap;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active btn-sm text-light px-3 py-1 me-1" id="tab-dashboard" data-bs-toggle="tab" data-bs-target="#content-dashboard" type="button" role="tab" style="border-radius: 6px;">
                <i class="fa-solid fa-chart-pie me-1" style="font-size: 0.8rem; color: var(--text-muted);"></i> Dashboard
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm text-light px-3 py-1 me-1" id="tab-board" data-bs-toggle="tab" data-bs-target="#content-board" type="button" role="tab" style="border-radius: 6px;">
                <i class="fa-solid fa-chess-board me-1" style="font-size: 0.8rem; color: var(--text-muted);"></i> Board
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm text-light px-3 py-1 me-1" id="tab-list" data-bs-toggle="tab" data-bs-target="#content-list" type="button" role="tab" style="border-radius: 6px;">
                <i class="fa-solid fa-list-ul me-1" style="font-size: 0.8rem; color: var(--text-muted);"></i> List
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link btn-sm text-light px-3 py-1 me-1" id="tab-automations" data-bs-toggle="tab" data-bs-target="#content-automations" type="button" role="tab" style="border-radius: 6px;">
                <i class="fa-solid fa-robot me-1" style="font-size: 0.8rem; color: var(--tasks-purple);"></i> Automations
            </button>
        </li>
        <li class="nav-item ms-auto" role="presentation">
            <button class="nav-link btn-sm text-light px-3 py-1" id="tab-queue" data-bs-toggle="tab" data-bs-target="#content-queue" type="button" role="tab" style="border-radius: 6px;">
                <i class="fa-solid fa-server me-1" style="font-size: 0.8rem; color: var(--tasks-warning);"></i> Queue Monitor
            </button>
        </li>
    </ul>

    <!-- Fullscreen Toggle Button -->
    <button class="btn btn-sm text-light px-2.5 py-1 ms-2" id="btn-toggle-fullscreen" title="Toggle Fullscreen Mode" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 6px; transition: all 0.2s ease;">
        <i class="fa-solid fa-expand" id="fullscreen-icon" style="font-size: 0.85rem; color: #94A3B8;"></i>
    </button>

    <style>
        #taskhub-nav .nav-link {
            transition: all 0.2s ease;
            opacity: 0.8;
            font-size: 0.85rem;
        }
        #taskhub-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            opacity: 1;
        }
        #taskhub-nav .nav-link.active {
            background: transparent;
            color: #fff !important;
            opacity: 1;
            box-shadow: inset 0 -2px 0 var(--tasks-primary);
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
        #taskhub-nav .nav-link.active i {
            color: var(--tasks-primary) !important;
        }
    </style>
</div>
