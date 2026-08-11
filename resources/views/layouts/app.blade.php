<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>@yield('page_title', 'Nexus Hub') — Nexus V3</title>

    <!-- Local Vendor Styles (Offline-first) -->
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/nprogress/nprogress.min.css') }}">
    <!-- Nexus Design System -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <!-- System Font Stack (Inter / Outfit / JetBrains Mono fallbacks) -->
    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-display: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', 'Cascadia Code', 'Fira Code', 'Consolas', 'Courier New', monospace;
        }
        body { font-family: var(--font-sans); }
        .font-mono, [style*="JetBrains Mono"] { font-family: var(--font-mono) !important; }

        /* Ensure Notification Center floats above ALL page elements across all hubs */
        #main-topbar, .navbar {
            position: relative !important;
            z-index: 99990 !important;
            overflow: visible !important;
        }
        #page-content-wrapper {
            position: relative !important;
            z-index: 1030 !important;
            overflow: visible !important;
        }
        #notification-hub {
            position: relative !important;
            z-index: 99995 !important;
            overflow: visible !important;
        }
        #notification-dropdown {
            position: absolute !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            z-index: 999999 !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <script>
        // Immediately restore main sidebar state before render to prevent FOUC
        if (localStorage.getItem('nexus_main_sidebar_toggled') === '1') {
            document.body.classList.add('toggled');
        }
    </script>
    <!-- ── Autopilot Warning Banner (Hidden) ── -->
    <div id="autopilot-banner" class="autopilot-warning-banner nx-autopilot-warning-pulse" style="display: none;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        ⚠️ SYSTEM AUTOPILOT ENGAGED GLOBALLY — ALL RESPONSES ARE AI-AUTOMATED
        <i class="fa-solid fa-triangle-exclamation ms-2"></i>
    </div>

    <!-- ── Global Loading Overlay (Hidden) ── -->
    <div id="nexus-global-loader" style="display: none;">
        <div class="text-center">
            <div style="width: 48px; height: 48px; border: 3px solid rgba(59,130,246,0.2); border-top-color: hsl(217,91%,60%); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px;"></div>
            <div class="loader-title" id="nexus-loader-text">Processing...</div>
            <div class="mt-3" style="width: 200px; height: 3px; background: rgba(255,255,255,0.08); border-radius: 2px; overflow: hidden;">
                <div id="nexus-loader-progress" style="height: 100%; background: linear-gradient(90deg, hsl(217,91%,60%), hsl(174,90%,41%)); width: 0%; transition: width 0.3s ease; border-radius: 2px;"></div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 0.7rem; font-family: 'JetBrains Mono', monospace;" id="nexus-loader-sub">Please wait...</div>
        </div>
    </div>

    <div class="d-flex" id="wrapper">

        <!-- ═══════════════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════════════ -->
        <div id="sidebar-wrapper">
            <!-- Brand Header -->
            <div class="sidebar-heading">
                <div class="brand-icon">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <span>Nexus</span>
                <span class="brand-version">V3</span>
            </div>

            <!-- Navigation -->
            <div class="sidebar-nav list-group list-group-flush">

                <div class="nav-section-label">Core</div>

                <a href="{{ url('/hub/dashboard') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>NexusHub</span>
                </a>

                <div class="nav-section-label mt-2">People & Comms</div>

                <a href="{{ url('/hub/people-connect') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/people-connect') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments"></i>
                    <span>People Connect</span>
                </a>

                <a href="{{ url('/hub/contacts') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/contacts') || request()->is('hub/contacts/*') ? 'active' : '' }}">
                    <i class="fa-solid fa-address-book"></i>
                    <span>ContactsHub</span>
                </a>

                <a href="{{ url('/hub/waha') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/waha') ? 'active' : '' }}">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Waha Manager</span>
                </a>

                <div class="nav-section-label mt-2">AI & Intelligence</div>

                <a href="{{ url('/hub/hedra-soul') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/hedra-soul') ? 'active' : '' }}">
                    <i class="fa-solid fa-brain"></i>
                    <span>Hedra Soul</span>
                </a>

                <a href="{{ url('/hub/proactive-ai') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/proactive-ai') ? 'active' : '' }}">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Proactive AI</span>
                </a>

                <a href="{{ url('/hub/agents') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/agents') ? 'active' : '' }}">
                    <i class="fa-solid fa-robot"></i>
                    <span>AgentsHub</span>
                </a>

                <a href="{{ url('/hub/memory') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/memory') ? 'active' : '' }}">
                    <i class="fa-solid fa-database"></i>
                    <span>MemoryHub</span>
                </a>

                <div class="nav-section-label mt-2">Automation</div>

                <a href="{{ url('/hub/workflows') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/workflows') ? 'active' : '' }}">
                    <i class="fa-solid fa-diagram-project"></i>
                    <span>WorkflowsHub</span>
                </a>

                <a href="{{ url('/hub/tasks') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/tasks') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Task Objectives</span>
                </a>

                <a href="{{ url('/hub/scheduler') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/scheduler') ? 'active' : '' }}">
                    <i class="fa-regular fa-clock"></i>
                    <span>Scheduler</span>
                </a>

                <div class="nav-section-label mt-2">System</div>

                <a href="{{ route('hub.credentials') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/credentials*') ? 'active' : '' }}">
                    <i class="fa-solid fa-key"></i>
                    <span>Credentials Hub</span>
                </a>

                <a href="{{ url('/hub/logs') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/logs') ? 'active' : '' }}">
                    <i class="fa-solid fa-terminal"></i>
                    <span>LogsHub</span>
                </a>

                <a href="{{ url('/hub/models') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/models') ? 'active' : '' }}">
                    <i class="fa-solid fa-microchip"></i>
                    <span>AIModelsHub</span>
                </a>

                <a href="{{ url('/hub/apis') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/apis') ? 'active' : '' }}">
                    <i class="fa-solid fa-plug"></i>
                    <span>APIs & MCP</span>
                </a>

                <a href="{{ url('/hub/admin') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/admin') ? 'active' : '' }}">
                    <i class="fa-solid fa-server"></i>
                    <span>Admin</span>
                </a>

                <a href="{{ url('/hub/settings') }}"
                   class="list-group-item list-group-item-action {{ request()->is('hub/settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>

            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <span class="agent-status-orb online"></span>
                <span>Souly Online</span>
                <span class="ms-auto text-muted" style="font-family: 'JetBrains Mono'; font-size: 0.6rem;">v2.1.0</span>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- ═══════════════════════════════════════════════════
             PAGE CONTENT
        ═══════════════════════════════════════════════════ -->
        <div id="page-content-wrapper" class="w-100 d-flex flex-column">

            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark px-4" id="main-topbar">
                <button class="btn btn-sm me-3" id="menu-toggle"
                        style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); color: var(--text-secondary); border-radius: 8px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-bars" style="font-size: 0.85rem;"></i>
                </button>

                <!-- Breadcrumb / Page Title -->
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size: 0.75rem; font-family: 'JetBrains Mono';">nexus /</span>
                    <span class="text-light fw-semibold" style="font-size: 0.85rem;">@yield('page_title', 'Dashboard')</span>
                </div>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <!-- Queue Status Pill (Clickable link to Horizon) -->
                    <a href="/horizon" target="_blank" id="queue-status-pill" class="d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded-pill text-decoration-none"
                         style="background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); font-size: 0.72rem; font-family: 'JetBrains Mono', monospace; cursor: pointer; transition: all 0.2s ease;"
                         title="Click to view Laravel Horizon Queue Dashboard">
                        <span id="queue-status-orb" class="agent-status-orb online" style="width: 6px; height: 6px;"></span>
                        <span id="queue-status-text" class="text-light">Queue Active</span>
                    </a>

                    <!-- Notifications Hub -->
                    @include('components.notification-hub')

                    <!-- Horizon Dashboard Link -->
                    <a href="/horizon" target="_blank" id="horizon-btn" class="btn btn-sm d-none d-md-flex align-items-center gap-2"
                       style="background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.3); border-radius: 8px; color: #a5b4fc; font-size: 0.78rem; padding: 6px 12px; text-decoration: none; transition: all 0.2s ease;"
                       title="Open Laravel Horizon Dashboard">
                        <i class="fa-solid fa-gauge-high text-primary" style="font-size: 0.8rem;"></i>
                        <span>Horizon</span>
                        <span id="horizon-status-dot" class="agent-status-orb online" style="width: 6px; height: 6px;"></span>
                    </a>

                    <!-- Quick Clear Fast Action Button (No Modal) -->
                    <button type="button" id="quick-clear-btn" class="btn btn-sm d-flex align-items-center gap-1.5"
                            style="background: rgba(234,179,8,0.15); border: 1px solid rgba(234,179,8,0.35); border-radius: 8px; color: #fef08a; font-size: 0.78rem; padding: 6px 10px; transition: all 0.2s ease;"
                            title="Quick Clear Cache & Hard Refresh (One-Click Silent Mode)">
                        <i class="fa-solid fa-bolt text-warning" style="font-size: 0.78rem;"></i>
                        <span class="d-none d-xl-inline fw-medium">Quick Clear</span>
                    </button>

                    <!-- System Optimize & Clear Cache Button (Modal Mode) -->
                    <button type="button" id="system-optimize-btn" class="btn btn-sm d-flex align-items-center gap-2"
                            style="background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(168,85,247,0.25)); border: 1px solid rgba(168,85,247,0.4); border-radius: 8px; color: #e2e8f0; font-size: 0.78rem; padding: 6px 12px; transition: all 0.2s ease;"
                            title="System Full Optimization & Cache Clear (Detailed Modal Mode)">
                        <i class="fa-solid fa-broom text-warning" style="font-size: 0.8rem;"></i>
                        <span class="d-none d-md-inline fw-medium">Clear & Optimize</span>
                    </button>

                    <!-- User -->
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2"
                                style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: var(--text-primary); font-size: 0.82rem; padding: 6px 12px;"
                                data-bs-toggle="dropdown">
                            <div style="width: 22px; height: 22px; background: var(--nexus-blue-dim); border: 1px solid var(--nexus-blue-glow); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-user" style="font-size: 0.6rem; color: var(--nexus-blue);"></i>
                            </div>
                            {{ auth()->user()->name ?? 'Admin' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="background: rgba(9,15,25,0.97); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 8px;">
                            <li><a class="dropdown-item rounded" href="{{ route('hub.settings') }}" style="color: var(--text-secondary); font-size: 0.83rem; padding: 8px 12px;"><i class="fa-regular fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item rounded" href="{{ route('hub.settings') }}" style="color: var(--text-secondary); font-size: 0.83rem; padding: 8px 12px;"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider" style="border-color: rgba(255,255,255,0.06);"></li>
                            <li>
                                <a class="dropdown-item rounded text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="font-size: 0.83rem; padding: 8px 12px;">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                        <form id="logout-form" action="{{ route('hub.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="main-content" id="main-content">
                @yield('content')
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- ═══════════════════════════════════════════════════
         STATUS BAR (Fixed Bottom)
    ═══════════════════════════════════════════════════ -->
    <div id="nexus-statusbar">
        <!-- Left Section: Connections -->
        <div class="statusbar-section">
            <div class="statusbar-item">
                <span class="agent-status-orb online" style="width: 6px; height: 6px;"></span>
                <span id="sb-agent-status">Souly</span>
            </div>
            <div class="statusbar-item">
                <i class="fa-brands fa-whatsapp" style="color: hsl(142,76%,55%); font-size: 0.8rem;"></i>
                <span id="sb-waha-status">WAHA</span>
                <div class="statusbar-item">
                </div>
            </div>
            <div class="statusbar-item d-none d-md-flex">
                <i class="fa-solid fa-diagram-project" style="color: var(--nexus-blue); font-size: 0.75rem;"></i>
                <span id="sb-queue-count">0 Jobs</span>
            </div>
        </div>

        <!-- Center: Dynamic Task Status -->
        <div class="statusbar-center" id="statusbar-task-status">
            <i class="fa-solid fa-circle-check me-1" style="color: var(--nexus-teal); font-size: 0.65rem;"></i>
            Idle — System Ready
        </div>

        <!-- Right Section: System Metrics -->
        <div class="statusbar-section">
            <div class="statusbar-item d-none d-md-flex">
                <i class="fa-solid fa-memory" style="color: var(--nexus-blue); font-size: 0.75rem;"></i>
                <span id="sb-memory">— MB</span>
            </div>
            <div class="statusbar-item">
                <i class="fa-regular fa-clock" style="font-size: 0.75rem;"></i>
                <span id="sb-time">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         SCRIPTS
        ═══════════════════════════════════════════════════ -->
    <!-- Local Vendor Scripts (Offline-first — order matters) -->
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-ui/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/nprogress/nprogress.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <!-- Pusher & Laravel Echo (local) -->
    <script src="{{ asset('vendor/pusher/pusher.min.js') }}"></script>
    <script src="{{ asset('vendor/laravel-echo/echo.iife.js') }}"></script>

    <script>
        // Global Nexus Utilities
        window.Nexus = {
            notify: function(message, type = 'info') {
                if (window.notificationHub && typeof window.notificationHub.addNotification === 'function') {
                    window.notificationHub.addNotification({
                        id: 'notif-' + Date.now(),
                        title: type.charAt(0).toUpperCase() + type.slice(1),
                        body: message,
                        type: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info')
                    });
                } else {
                    console.log(`[Nexus Notify] ${type.toUpperCase()}: ${message}`);
                    alert(`${type.toUpperCase()}: ${message}`);
                }
            },
            
            showTaskLoader: function(title = 'Processing...', subtitle = 'Please wait...') {
                const loader = document.getElementById('nexus-global-loader');
                if (loader) {
                    const titleEl = document.getElementById('nexus-loader-text');
                    const subEl = document.getElementById('nexus-loader-sub');
                    if (titleEl) titleEl.innerText = title;
                    if (subEl) subEl.innerText = subtitle;
                    loader.style.display = 'flex';
                }
            },
            
            hideTaskLoader: function() {
                const loader = document.getElementById('nexus-global-loader');
                if (loader) {
                    loader.style.display = 'none';
                }
            }
        };
    </script>

    <script>
        // Enable Reverb/Pusher
        try {
            window.Pusher = Pusher;
            const EchoConstructor = (typeof Echo !== 'undefined')
                ? (Echo && typeof Echo.default === 'function' ? Echo.default : Echo)
                : null;

            if (!EchoConstructor || typeof EchoConstructor !== 'function') {
                throw new Error('Echo constructor not found');
            }

            window.Echo = new EchoConstructor({
                broadcaster: 'reverb',
                key: '{{ config("broadcasting.connections.reverb.key") }}',
                wsHost: '{{ config("broadcasting.connections.reverb.options.host", "") }}' || window.location.hostname,
                wsPort: 443,
                wssPort: 443,
                forceTLS: true,
                enabledTransports: ['ws', 'wss'],
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            });
        } catch (echoErr) {
            window.Echo = null;
        }

        const registerFcmToken = async (token) => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            await fetch('/api/v1/notifications/fcm-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    token,
                    device_name: navigator.userAgent,
                    platform: 'web',
                }),
            });
        };

        const handleFcmMessage = (payload) => {
            const notification = payload || {};
            const data = payload.data || {};
            const message = {
                id: data.id || payload.messageId || `notif-${Date.now()}`,
                title: notification.title || 'Notification',
                body: notification.body || '',
                icon: notification.icon || null,
                badge: notification.badge || null,
                type: data.type || 'info',
                actions: notification.actions || [],
                data,
                requireInteraction: notification.requireInteraction || false,
                timestamp: new Date().toISOString(),
            };

            if (window.notificationHub && typeof window.notificationHub.addNotification === 'function') {
                window.notificationHub.addNotification(message);
            }

            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    new Notification(message.title, {
                        body: message.body,
                        icon: notification.icon || '/favicon.ico',
                        badge: notification.badge || '/favicon.ico',
                        tag: message.id,
                        requireInteraction: notification.requireInteraction || false,
                        timestamp: new Date().toISOString(),
                    });
                } catch (error) {
                } 
            }
        };

        const initFcm = async () => {
            if (!('serviceWorker' in navigator)) return;
            try {
                await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            } catch (e) {
                console.error('FCM registration failed', e);
            }
        };

        document.addEventListener('DOMContentLoaded', initFcm);

        NProgress.configure({ showSpinner: false, minimum: 0.1 });
        $(document).ajaxStart(() => NProgress.start());
        $(document).ajaxStop(() => NProgress.done());
        window.addEventListener('load', () => NProgress.done());
        document.addEventListener('DOMContentLoaded', () => NProgress.start());

        $('#menu-toggle').on('click', function() {
            $('body').toggleClass('toggled');
            const isToggled = $('body').hasClass('toggled');
            localStorage.setItem('nexus_main_sidebar_toggled', isToggled ? '1' : '0');
        });

        // Global Keyboard Shortcut: Shift + 1 to toggle main Nexus sidebar
        $(document).on('keydown', function(e) {
            if (e.shiftKey && (e.key === '!' || e.key === '1' || e.code === 'Digit1')) {
                const targetTag = e.target ? e.target.tagName.toLowerCase() : '';
                if (targetTag !== 'input' && targetTag !== 'textarea') {
                    e.preventDefault();
                    $('#menu-toggle').trigger('click');
                }
            }
        });

        (function() {
            const currentPath = window.location.pathname;
            $('#sidebar-wrapper .list-group-item').each(function() {
                const href = $(this).attr('href');
                if (href && currentPath.startsWith(href) && href !== '/') {
                    $(this).addClass('active');
                }
            });
        })();

        // Dynamic Live Statusbar & System Telemetry Updater
        (function updateLiveTelemetry() {
            function updateClock() {
                const clock = document.getElementById('sb-time');
                if (clock) {
                    const now = new Date();
                    clock.innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
            }
            updateClock();
            setInterval(updateClock, 30000);

            async function fetchMetrics() {
                try {
                    const res = await fetch('/hub/dashboard/health', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data) {
                            const memEl = document.getElementById('sb-memory');
                            if (memEl && (data.memory_usage_mb || data.memory)) {
                                memEl.innerText = (data.memory_usage_mb || data.memory) + ' MB';
                            }
                            
                            const jobs = data.queue_jobs || data.active_jobs || 0;
                            const queueEl = document.getElementById('sb-queue-count');
                            if (queueEl) queueEl.innerText = jobs + ' Jobs';

                            const qText = document.getElementById('queue-status-text');
                            const qOrb = document.getElementById('queue-status-orb');
                            const qPill = document.getElementById('queue-status-pill');
                            const hDot = document.getElementById('horizon-status-dot');

                            if (qText) {
                                if (jobs > 0) {
                                    qText.innerText = `Queue Active (${jobs} Jobs)`;
                                    if (qOrb) qOrb.className = 'agent-status-orb busy';
                                    if (hDot) hDot.className = 'agent-status-orb busy';
                                    if (qPill) {
                                        qPill.style.background = 'rgba(234,179,8,0.15)';
                                        qPill.style.borderColor = 'rgba(234,179,8,0.4)';
                                    }
                                } else {
                                    qText.innerText = 'Queue Idle (0 Jobs)';
                                    if (qOrb) qOrb.className = 'agent-status-orb online';
                                    if (hDot) hDot.className = 'agent-status-orb online';
                                    if (qPill) {
                                        qPill.style.background = 'rgba(34,197,94,0.12)';
                                        qPill.style.borderColor = 'rgba(34,197,94,0.3)';
                                    }
                                }
                            }
                            
                            const wahaEl = document.getElementById('sb-waha-status');
                            if (wahaEl && data.waha_status) {
                                wahaEl.innerText = 'WAHA ' + (data.waha_status.toUpperCase());
                            }
                        }
                    }
                } catch(e) {}
            }
            fetchMetrics();
            setInterval(fetchMetrics, 30000);
        })();
    </script>

    <!-- System Full Optimize & Cache Clear Modal -->
    @include('components.system-optimize-modal')

    <!-- Queue & Workers Telemetry Modal -->
    @include('components.queue-details-modal')

    @stack('scripts')
</body>
</html>