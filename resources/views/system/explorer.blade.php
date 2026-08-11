<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus System Explorer & Metadata Hub</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0b0f19;
            --bg-card: rgba(17, 24, 39, 0.75);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --accent: #06b6d4;
            --accent-green: #10b981;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Glassmorphism Classes */
        .glass {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
        }

        .glass-nav {
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
        }

        /* Navigation Header */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: white;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .brand-text h1 {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #fff, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Navigation Tabs */
        .nav-tabs {
            display: flex;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.35rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .tab-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .tab-btn.active {
            color: white;
            background: linear-gradient(135deg, var(--primary), #4338ca);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Main Container */
        main {
            max-width: 1600px;
            margin: 2rem auto;
            padding: 0 2rem;
            width: 100%;
            flex: 1;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .stat-info h3 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .stat-info .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Search Filter Bar */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 280px;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: white;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Method Badges */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            font-size: 0.725rem;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
        }

        .badge-get { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-post { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .badge-put, .badge-patch { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-delete { background: rgba(244, 63, 94, 0.2); color: #f87171; border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-purple { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }

        /* Tables & Lists */
        .content-card {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .data-table-wrapper {
            overflow-x: auto;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        table.data-table th {
            padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        table.data-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #d1d5db;
        }

        table.data-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .code-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.825rem;
            color: #a5b4fc;
        }

        /* Accordion for Database Schema */
        .schema-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
            gap: 1.25rem;
        }

        .schema-card {
            padding: 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
        }

        .schema-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .schema-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-tag {
            font-size: 0.75rem;
            padding: 0.15rem 0.5rem;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 6px;
            color: var(--text-muted);
        }

        /* Documentation Layout */
        .docs-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 1.5rem;
            min-height: 600px;
        }

        .docs-sidebar {
            max-height: 75vh;
            overflow-y: auto;
            padding: 1rem;
        }

        .docs-item {
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
        }

        .docs-item:hover, .docs-item.active {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
        }

        .docs-viewer {
            padding: 2rem;
            max-height: 75vh;
            overflow-y: auto;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .docs-viewer pre {
            background: rgba(0, 0, 0, 0.5);
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1rem 0;
            border: 1px solid var(--border-color);
        }

        .docs-viewer code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
        }

        /* Footer */
        footer {
            margin-top: auto;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 900px) {
            header { flex-direction: column; gap: 1rem; }
            .docs-layout { grid-template-columns: 1fr; }
            .schema-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="glass-nav">
        <a href="/system" class="brand">
            <div class="brand-logo">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div class="brand-text">
                <h1>Nexus System Explorer</h1>
                <p>Monolith Metadata & API Hub</p>
            </div>
        </a>

        <!-- Navigation Tabs -->
        <nav class="nav-tabs">
            <a href="/system" class="tab-btn {{ $activeTab === 'dashboard' ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Overview
            </a>
            <a href="/system/routes" class="tab-btn {{ $activeTab === 'routes' ? 'active' : '' }}">
                <i class="fa-solid fa-route"></i> Routes ({{ $routesData['summary']['total'] }})
            </a>
            <a href="/system/schema" class="tab-btn {{ $activeTab === 'schema' ? 'active' : '' }}">
                <i class="fa-solid fa-database"></i> DB Schema ({{ $schemaData['tables_count'] }})
            </a>
            <a href="/system/codebase" class="tab-btn {{ $activeTab === 'codebase' ? 'active' : '' }}">
                <i class="fa-solid fa-code"></i> Controllers & Services
            </a>
            <a href="/system/docs" class="tab-btn {{ $activeTab === 'docs' ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i> Docs ({{ $docsData['total_docs'] }})
            </a>
            <a href="/system/views" class="tab-btn {{ $activeTab === 'views' ? 'active' : '' }}">
                <i class="fa-solid fa-border-all"></i> Blade Views ({{ $viewsData['total_views'] }})
            </a>
        </nav>
    </header>

    <!-- Main Content Container -->
    <main>

        <!-- Global Quick Stats Bar -->
        <section class="stats-grid">
            <div class="glass stat-card">
                <div class="stat-info">
                    <h3>Total Routes</h3>
                    <div class="stat-value">{{ $routesData['summary']['total'] }}</div>
                </div>
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">
                    <i class="fa-solid fa-route"></i>
                </div>
            </div>

            <div class="glass stat-card">
                <div class="stat-info">
                    <h3>DB Schema Tables</h3>
                    <div class="stat-value">{{ $schemaData['tables_count'] }}</div>
                </div>
                <div class="stat-icon" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee;">
                    <i class="fa-solid fa-table"></i>
                </div>
            </div>

            <div class="glass stat-card">
                <div class="stat-info">
                    <h3>Controllers & Services</h3>
                    <div class="stat-value">{{ $codebaseData['summary']['controllers_count'] + $codebaseData['summary']['services_count'] }}</div>
                </div>
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>

            <div class="glass stat-card">
                <div class="stat-info">
                    <h3>Project Docs</h3>
                    <div class="stat-value">{{ $docsData['total_docs'] }}</div>
                </div>
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
            </div>

            <div class="glass stat-card">
                <div class="stat-info">
                    <h3>Blade Templates</h3>
                    <div class="stat-value">{{ $viewsData['total_views'] }}</div>
                </div>
                <div class="stat-icon" style="background: rgba(244, 63, 94, 0.15); color: #fb7185;">
                    <i class="fa-solid fa-desktop"></i>
                </div>
            </div>
        </section>

        {{-- TAB 1: OVERVIEW DASHBOARD --}}
        @if($activeTab === 'dashboard')
        <div class="glass content-card">
            <h2 style="font-size: 1.35rem; margin-bottom: 1rem; color: white;">
                <i class="fa-solid fa-compass" style="color: var(--primary);"></i> Nexus System Metadata Explorer
            </h2>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.6;">
                Welcome to the official live metadata explorer for the Nexus Monolithic AI Ecosystem. All metadata APIs are public (No Auth) and accessible programmatically or via browser.
            </p>

            <h3 style="font-size: 1.1rem; margin: 1.5rem 0 1rem; color: #a5b4fc;">
                <i class="fa-solid fa-network-wired"></i> Quick API & Browser Links (Public - No Auth)
            </h3>

            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Task / Feature</th>
                            <th>Browser Live Web URL</th>
                            <th>JSON API Endpoint</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Task 1: Routes</strong></td>
                            <td><a href="/system/routes" class="code-text" style="color: #60a5fa;">/system/routes</a></td>
                            <td><a href="/api/v1/system/routes" target="_blank" class="code-text">/api/v1/system/routes</a></td>
                            <td>All project routes separated by API (391) and Web (180).</td>
                        </tr>
                        <tr>
                            <td><strong>Task 2: DB Schema</strong></td>
                            <td><a href="/system/schema" class="code-text" style="color: #60a5fa;">/system/schema</a></td>
                            <td><a href="/api/v1/system/schema" target="_blank" class="code-text">/api/v1/system/schema</a></td>
                            <td>Complete database schema details for all 114 tables.</td>
                        </tr>
                        <tr>
                            <td><strong>Task 3: Codebase</strong></td>
                            <td><a href="/system/codebase" class="code-text" style="color: #60a5fa;">/system/codebase</a></td>
                            <td><a href="/api/v1/system/codebase" target="_blank" class="code-text">/api/v1/system/codebase</a></td>
                            <td>Controllers and Services list with methods and descriptions.</td>
                        </tr>
                        <tr>
                            <td><strong>Task 4: Docs</strong></td>
                            <td><a href="/system/docs" class="code-text" style="color: #60a5fa;">/system/docs</a></td>
                            <td><a href="/api/v1/system/docs" target="_blank" class="code-text">/api/v1/system/docs</a></td>
                            <td>Project documentation index and full markdown reader.</td>
                        </tr>
                        <tr>
                            <td><strong>Task 5: Blade Views</strong></td>
                            <td><a href="/system/views" class="code-text" style="color: #60a5fa;">/system/views</a></td>
                            <td><a href="/api/v1/system/views" target="_blank" class="code-text">/api/v1/system/views</a></td>
                            <td>Blade view template files list and purpose description.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- TAB 2: ROUTES EXPLORER --}}
        @if($activeTab === 'routes')
        <div class="toolbar">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="routeSearch" class="search-input" placeholder="Search routes by URI, name, or controller action...">
            </div>
        </div>

        <div class="glass content-card">
            <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: white;">
                <i class="fa-solid fa-plug" style="color: var(--accent);"></i> API Routes ({{ $routesData['summary']['api_count'] }})
            </h3>
            <div class="data-table-wrapper">
                <table class="data-table" id="routesTable">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>URI</th>
                            <th>Route Name</th>
                            <th>Action / Controller</th>
                            <th>Middleware</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routesData['api'] as $r)
                        <tr class="searchable-row">
                            <td>
                                @foreach($r['methods'] as $m)
                                    <span class="badge badge-{{ strtolower($m) }}">{{ $m }}</span>
                                @endforeach
                            </td>
                            <td class="code-text">{{ $r['uri'] }}</td>
                            <td>{{ $r['name'] ?: '-' }}</td>
                            <td style="font-size: 0.8rem; color: #9ca3af;">{{ $r['action'] }}</td>
                            <td>
                                <span style="font-size: 0.75rem; color: #6b7280;">{{ implode(', ', $r['middleware']) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- TAB 3: DATABASE SCHEMA --}}
        @if($activeTab === 'schema')
        <div class="toolbar">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="schemaSearch" class="search-input" placeholder="Search tables or columns...">
            </div>
        </div>

        <div class="schema-grid" id="schemaGrid">
            @foreach($schemaData['tables'] as $table)
            <div class="glass schema-card searchable-table" data-name="{{ $table['name'] }}">
                <div class="schema-header">
                    <div class="schema-title">
                        <i class="fa-solid fa-table" style="color: var(--accent-green);"></i> {{ $table['name'] }}
                    </div>
                    <div class="column-tag">{{ $table['columns_count'] }} Cols</div>
                </div>

                <div style="max-height: 280px; overflow-y: auto;">
                    <table class="data-table" style="font-size: 0.8rem;">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Type</th>
                                <th>Nullable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($table['columns'] as $col)
                            <tr>
                                <td class="code-text" style="color: #e0e7ff;">{{ $col['name'] }}</td>
                                <td style="color: #a7f3d0; font-family: 'JetBrains Mono', monospace;">{{ $col['type'] }}</td>
                                <td>
                                    @if($col['nullable'])
                                        <span class="badge badge-purple">NULL</span>
                                    @else
                                        <span class="badge badge-get">NOT NULL</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- TAB 4: CODEBASE (CONTROLLERS & SERVICES) --}}
        @if($activeTab === 'codebase')
        <div class="glass content-card" style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: white;">
                <i class="fa-solid fa-gears" style="color: var(--primary);"></i> Project Controllers ({{ count($codebaseData['controllers']) }})
            </h3>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Controller Class</th>
                            <th>Relative Path</th>
                            <th>Description</th>
                            <th>Public Methods Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($codebaseData['controllers'] as $c)
                        <tr>
                            <td class="code-text" style="font-weight: 600; color: #a5b4fc;">{{ $c['class_name'] }}</td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">{{ $c['relative_path'] }}</td>
                            <td>{{ $c['description'] }}</td>
                            <td><span class="badge badge-get">{{ $c['methods_count'] }} Methods</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass content-card">
            <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: white;">
                <i class="fa-solid fa-sliders" style="color: var(--accent);"></i> Project Services ({{ count($codebaseData['services']) }})
            </h3>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Service Class</th>
                            <th>Relative Path</th>
                            <th>Description</th>
                            <th>Public Methods Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($codebaseData['services'] as $s)
                        <tr>
                            <td class="code-text" style="font-weight: 600; color: #34d399;">{{ $s['class_name'] }}</td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">{{ $s['relative_path'] }}</td>
                            <td>{{ $s['description'] }}</td>
                            <td><span class="badge badge-post">{{ $s['methods_count'] }} Methods</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- TAB 5: DOCUMENTATION EXPLORER --}}
        @if($activeTab === 'docs')
        <div class="glass docs-layout">
            <div class="glass docs-sidebar">
                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: white;">Documentation Index</h3>
                @foreach($docsData['files'] as $d)
                <a href="/system/docs?file={{ urlencode($d['relative_path']) }}" 
                   class="docs-item {{ $docsData['requested_file'] === $d['relative_path'] ? 'active' : '' }}">
                    <i class="fa-regular fa-file-lines"></i> {{ $d['title'] }}
                    <div style="font-size: 0.7rem; color: #6b7280; margin-top: 2px;">{{ $d['category'] }}</div>
                </a>
                @endforeach
            </div>

            <div class="glass docs-viewer">
                <h2 style="margin-bottom: 1rem; color: #93c5fd;">{{ $docsData['requested_file'] }}</h2>
                @if($docsData['content'])
                    <pre><code>{{ $docsData['content'] }}</code></pre>
                @else
                    <p style="color: var(--text-muted);">Select a documentation file from the left sidebar to view its content.</p>
                @endif
            </div>
        </div>
        @endif

        {{-- TAB 6: BLADE VIEWS EXPLORER --}}
        @if($activeTab === 'views')
        <div class="glass content-card">
            <h3 style="font-size: 1.2rem; margin-bottom: 1rem; color: white;">
                <i class="fa-solid fa-desktop" style="color: var(--accent-rose);"></i> Project Blade Views ({{ $viewsData['total_views'] }})
            </h3>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Blade View Name</th>
                            <th>Relative Path</th>
                            <th>Category</th>
                            <th>Purpose / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viewsData['views'] as $v)
                        <tr>
                            <td class="code-text" style="color: #f472b6;">{{ $v['view_name'] }}</td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">{{ $v['relative_path'] }}</td>
                            <td><span class="badge badge-purple">{{ $v['category'] }}</span></td>
                            <td>{{ $v['purpose'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </main>

    <!-- Footer -->
    <footer>
        Nexus Monolithic Ecosystem &copy; 2026 | Powered by Laravel 13 & AI Core | Base URL: <code>n.soulyeg.online/</code>
    </footer>

    <!-- Interactive Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const routeSearch = document.getElementById('routeSearch');
            if (routeSearch) {
                routeSearch.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase();
                    document.querySelectorAll('#routesTable .searchable-row').forEach(row => {
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }

            const schemaSearch = document.getElementById('schemaSearch');
            if (schemaSearch) {
                schemaSearch.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase();
                    document.querySelectorAll('#schemaGrid .searchable-table').forEach(card => {
                        const text = card.getAttribute('data-name').toLowerCase() + ' ' + card.innerText.toLowerCase();
                        card.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>
