<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusV3 — System Readme & Specification Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0b0f19; color: #e2e8f0; }
        .glass-card { background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .font-mono { font-family: 'Fira Code', monospace; }
        
        /* Markdown Render Styling */
        .markdown-body { color: #cbd5e1; line-height: 1.7; }
        .markdown-body h1 { font-size: 2.25rem; font-weight: 800; color: #ffffff; margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem; }
        .markdown-body h2 { font-size: 1.6rem; font-weight: 700; color: #38bdf8; margin-top: 2rem; margin-bottom: 0.85rem; border-bottom: 1px solid rgba(56, 189, 248, 0.2); padding-bottom: 0.4rem; }
        .markdown-body h3 { font-size: 1.25rem; font-weight: 600; color: #818cf8; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .markdown-body p { margin-bottom: 1rem; font-size: 0.95rem; }
        .markdown-body ul, .markdown-body ol { margin-left: 1.5rem; margin-bottom: 1rem; }
        .markdown-body ul { list-style-type: disc; }
        .markdown-body ol { list-style-type: decimal; }
        .markdown-body li { margin-bottom: 0.35rem; }
        .markdown-body blockquote { border-left: 4px solid #6366f1; background: rgba(99, 102, 241, 0.08); padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; color: #a5b4fc; }
        .markdown-body code { background: rgba(30, 41, 59, 0.9); color: #f43f5e; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-family: 'Fira Code', monospace; font-size: 0.85em; }
        .markdown-body pre { background: #0f172a; padding: 1.25rem; border-radius: 0.75rem; overflow-x: auto; margin-bottom: 1.25rem; border: 1px solid rgba(255,255,255,0.08); }
        .markdown-body pre code { background: transparent; color: #38bdf8; padding: 0; }
        .markdown-body table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; overflow-x: auto; display: block; }
        .markdown-body th { background: rgba(30, 41, 59, 0.8); color: #f8fafc; text-align: left; padding: 0.75rem 1rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); }
        .markdown-body td { padding: 0.75rem 1rem; border: 1px solid rgba(255,255,255,0.06); font-size: 0.9rem; }
        .markdown-body tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        .markdown-body a { color: #38bdf8; text-decoration: underline; }
        .markdown-body a:hover { color: #7dd3fc; }
        .markdown-body hr { border: 0; height: 1px; background: rgba(255,255,255,0.1); margin: 2rem 0; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 glass-card border-b border-slate-800 px-6 py-4">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="fa-solid fa-book-open text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-slate-400">
                        NexusV3 System Readme
                    </h1>
                    <p class="text-xs text-slate-400">Unified Architecture Specification for Humans & AI Agents</p>
                </div>
            </div>

            <!-- Header Quick Links -->
            <div class="flex items-center space-x-2">
                <a href="/system" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white text-xs font-medium transition flex items-center gap-1.5 border border-slate-700">
                    <i class="fa-solid fa-gauge text-indigo-400"></i> Explorer Dashboard
                </a>
                <a href="/api/v1/system/readme" target="_blank" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600/30 hover:text-indigo-300 text-xs font-medium transition flex items-center gap-1.5 border border-indigo-500/30">
                    <i class="fa-solid fa-code text-indigo-400"></i> JSON API Response
                </a>
                <a href="/system/readme?format=raw" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600/30 hover:text-emerald-300 text-xs font-medium transition flex items-center gap-1.5 border border-emerald-500/30">
                    <i class="fa-solid fa-file-lines text-emerald-400"></i> Raw Text
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
        
        <!-- Metadata Header Card -->
        <div class="glass-card rounded-2xl p-6 mb-8 border border-slate-800 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <i class="fa-solid fa-circle-check text-[10px] mr-1"></i> Task 6: Public Spec API & Web
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono bg-slate-800 text-slate-300 border border-slate-700">
                            No Auth Required
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-1">Project Specification & Architecture Guide</h2>
                    <p class="text-sm text-slate-400">
                        Complete overview of project purpose, hubs, technology stack, database schema, documentation index, and API specifications.
                    </p>
                </div>

                <!-- Meta Badges -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-slate-900/80 px-3 py-2 rounded-xl border border-slate-800 text-center">
                        <span class="text-[10px] uppercase text-slate-400 block font-semibold">File Size</span>
                        <span class="text-sm font-mono font-bold text-indigo-400">{{ number_format($readme['size_bytes'] ?? 0) }} Bytes</span>
                    </div>
                    <div class="bg-slate-900/80 px-3 py-2 rounded-xl border border-slate-800 text-center">
                        <span class="text-[10px] uppercase text-slate-400 block font-semibold">Last Modified</span>
                        <span class="text-sm font-mono font-bold text-slate-200">{{ $readme['last_modified'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- View Mode Tabs -->
            <div class="flex items-center justify-between mt-6 pt-6 border-t border-slate-800/80">
                <div class="flex items-center space-x-2 bg-slate-900/90 p-1.5 rounded-xl border border-slate-800">
                    <button onclick="switchTab('rendered')" id="tab-rendered" class="px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-2 bg-indigo-600 text-white shadow">
                        <i class="fa-solid fa-eye"></i> Rendered View (Human)
                    </button>
                    <button onclick="switchTab('raw')" id="tab-raw" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition flex items-center gap-2">
                        <i class="fa-solid fa-code"></i> Raw Markdown
                    </button>
                    <button onclick="switchTab('json')" id="tab-json" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition flex items-center gap-2">
                        <i class="fa-solid fa-robot"></i> AI Agent JSON
                    </button>
                </div>

                <button onclick="copyContent()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium transition flex items-center gap-2 border border-slate-700">
                    <i class="fa-regular fa-copy text-indigo-400" id="copy-icon"></i>
                    <span id="copy-text">Copy Content</span>
                </button>
            </div>
        </div>

        <!-- Rendered Tab Content -->
        <div id="content-rendered" class="glass-card rounded-2xl p-8 border border-slate-800 shadow-xl">
            <div id="markdown-container" class="markdown-body">
                <!-- Rendered dynamically by Marked.js -->
            </div>
        </div>

        <!-- Raw Markdown Tab Content -->
        <div id="content-raw" class="glass-card rounded-2xl p-6 border border-slate-800 shadow-xl hidden">
            <pre class="font-mono text-xs text-emerald-400 bg-slate-950 p-6 rounded-xl overflow-x-auto whitespace-pre-wrap leading-relaxed border border-slate-800" id="raw-markdown-text">{{ $readme['content'] ?? '' }}</pre>
        </div>

        <!-- AI Agent JSON Tab Content -->
        <div id="content-json" class="glass-card rounded-2xl p-6 border border-slate-800 shadow-xl hidden">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-mono">GET /api/v1/system/readme</span>
                <span class="text-xs text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 font-mono">200 OK</span>
            </div>
            <pre class="font-mono text-xs text-indigo-300 bg-slate-950 p-6 rounded-xl overflow-x-auto whitespace-pre-wrap leading-relaxed border border-slate-800" id="json-preview-text">{{ json_encode(['success' => true, 'data' => $readme], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

    </main>

    <!-- Footer -->
    <footer class="glass-card border-t border-slate-800 mt-12 py-6 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 gap-3">
            <div>
                NexusV3 System Metadata & Observability Hub &copy; {{ date('Y') }}
            </div>
            <div class="flex items-center space-x-4">
                <a href="/system/routes" class="hover:text-slate-300">Routes</a>
                <a href="/system/schema" class="hover:text-slate-300">Schema</a>
                <a href="/system/codebase" class="hover:text-slate-300">Codebase</a>
                <a href="/system/docs" class="hover:text-slate-300">Docs</a>
                <a href="/system/views" class="hover:text-slate-300">Views</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        const rawContent = @json($readme['content'] ?? '');

        // Render Markdown on load
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof marked !== 'undefined') {
                document.getElementById('markdown-container').innerHTML = marked.parse(rawContent);
            } else {
                document.getElementById('markdown-container').innerText = rawContent;
            }
        });

        // Tab Switching Logic
        function switchTab(tab) {
            const tabs = ['rendered', 'raw', 'json'];
            tabs.forEach(t => {
                const btn = document.getElementById(`tab-${t}`);
                const content = document.getElementById(`content-${t}`);

                if (t === tab) {
                    btn.className = "px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-2 bg-indigo-600 text-white shadow";
                    content.classList.remove('hidden');
                } else {
                    btn.className = "px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition flex items-center gap-2";
                    content.classList.add('hidden');
                }
            });
        }

        // Copy Content Function
        function copyContent() {
            navigator.clipboard.writeText(rawContent).then(() => {
                const textSpan = document.getElementById('copy-text');
                const icon = document.getElementById('copy-icon');
                
                textSpan.innerText = 'Copied!';
                icon.className = 'fa-solid fa-check text-emerald-400';
                
                setTimeout(() => {
                    textSpan.innerText = 'Copy Content';
                    icon.className = 'fa-regular fa-copy text-indigo-400';
                }, 2000);
            });
        }
    </script>
</body>
</html>
