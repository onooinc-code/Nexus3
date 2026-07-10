@extends('layouts.app')

@section('page_title', 'SettingsHub Developer Reference')

@section('content')
<style>
    /* Glassmorphism Styles */
    .glass-container {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        color: #f8fafc;
    }
    
    .glass-header {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(59, 130, 246, 0.15));
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 15px 20px;
        border-radius: 16px 16px 0 0;
        margin: -20px -20px 20px -20px;
    }

    /* Emergency Glassmorphism */
    .glass-container-danger {
        background: rgba(127, 29, 29, 0.4);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 30px rgba(220, 38, 38, 0.1);
        color: #f8fafc;
    }
    .glass-header-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(185, 28, 28, 0.2));
        border-bottom: 1px solid rgba(239, 68, 68, 0.1);
        padding: 15px 20px;
        border-radius: 16px 16px 0 0;
        margin: -20px -20px 20px -20px;
    }

    /* jQuery UI Customization */
    .ui-tabs .ui-tabs-nav {
        background: transparent;
        border: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 0;
    }
    .ui-tabs .ui-tabs-nav li {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        margin: 0 0 0 4px; /* RTL Margin */
    }
    .ui-tabs .ui-tabs-nav li.ui-tabs-active {
        background: rgba(59, 130, 246, 0.3);
    }
    .ui-tabs .ui-tabs-nav li a {
        color: #e2e8f0;
        font-weight: 500;
        padding: 10px 20px;
    }
    .ui-tabs .ui-tabs-panel {
        background: transparent;
        border: none;
        padding: 20px 0;
    }

    /* Dialog Customization */
    .ui-dialog {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
        color: #f8fafc !important;
        direction: rtl;
        z-index: 9999 !important;
    }
    .ui-dialog .ui-dialog-titlebar {
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(59, 130, 246, 0.15)) !important;
        border: none !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        border-radius: 16px 16px 0 0 !important;
        color: #fff !important;
        padding: 15px 20px !important;
    }
    .ui-dialog .ui-dialog-titlebar-close {
        right: auto !important;
        left: 15px !important;
        filter: invert(1);
        top: 50% !important;
        transform: translateY(-50%) !important;
        margin: 0 !important;
    }
    .ui-dialog .ui-dialog-content {
        background: transparent !important;
        color: #cbd5e1 !important;
        padding: 20px !important;
    }
    .ui-widget-overlay {
        background: #000 !important;
        opacity: 0.7 !important;
        z-index: 9998 !important;
    }

    /* Accordion Customization */
    .ui-accordion .ui-accordion-header {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        border-radius: 8px;
        margin-top: 8px;
        padding: 12px 15px;
        direction: rtl; /* Ensure header is RTL */
    }
    .ui-accordion .ui-accordion-header-active {
        background: rgba(59, 130, 246, 0.2);
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    .ui-accordion .ui-accordion-content {
        background: rgba(30, 41, 59, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-top: none;
        border-radius: 0 0 8px 8px;
        color: #cbd5e1;
        padding: 15px;
    }

    /* Endpoint Badges */
    .method-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: bold;
        margin-left: 10px;
    }
    .method-GET { background: #10b981; color: #fff; }
    .method-POST { background: #3b82f6; color: #fff; }
    .method-PUT { background: #f59e0b; color: #fff; }
    .method-DELETE { background: #ef4444; color: #fff; }

    /* Live Logs Terminal */
    .terminal-container {
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 15px;
        font-family: 'Courier New', Courier, monospace;
        height: 350px;
        overflow-y: auto;
        color: #4ade80;
        margin-top: 20px;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
        direction: ltr; /* Terminal is LTR */
        text-align: left;
    }
    .terminal-line { margin-bottom: 5px; border-bottom: 1px dotted rgba(255,255,255,0.1); padding-bottom: 5px;}
    .terminal-time { color: #94a3b8; font-size: 0.9em; margin-right: 10px; }
    .terminal-action { font-weight: bold; color: #38bdf8; margin-right: 10px; }

    pre.code-block {
        background: #0f172a;
        padding: 10px;
        border-radius: 6px;
        overflow-x: auto;
        border: 1px solid #334155;
        color: #e2e8f0;
        direction: ltr;
        text-align: left;
    }
    
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    /* Page Background for Glassmorphism */
    .settings-hub-bg {
        background: radial-gradient(circle at top right, #0f172a 0%, #1e1b4b 50%, #020617 100%);
        min-height: 100vh;
        margin: -1.5rem; /* negate default padding if any */
        padding: 1.5rem;
    }
</style>

<!-- Main Container (RTL enabled) -->
<div class="settings-hub-bg">
    <div class="container-fluid py-4" dir="rtl">
    
    <div class="glass-container mb-4" id="main-content-wrapper">
        <div class="glass-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="m-0"><i class="fas fa-book-open me-2 ms-2"></i> SettingsHub Developer Reference</h2>
            <div class="d-flex gap-2 align-items-center">
                <!-- UI/UX Advanced Controls (Phase 6) -->
                <button class="btn btn-sm btn-outline-light" id="btn-toggle-theme" title="تبديل الوضع (Dark/Light)"><i class="fas fa-moon"></i></button>
                <button class="btn btn-sm btn-outline-light" id="btn-toggle-fullscreen" title="وضع ملء الشاشة (Fullscreen)"><i class="fas fa-expand"></i></button>
                <div class="vr bg-secondary mx-1"></div>
                <span class="badge bg-primary rounded-pill p-2 fs-6">إجمالي الإعدادات: {{ $metrics['total_settings'] ?? 0 }}</span>
            </div>
        </div>
        
        <p class="text-light opacity-75">هذا القسم مخصص للمطورين للاطلاع على واجهات برمجة التطبيقات (APIs) الخاصة بإعدادات النظام، الخدمات المرتبطة، والأحداث الحية بشكل فوري.</p>
        
        <!-- Tabs Section -->
        <div id="settings-tabs">
            <ul>
                <li><a href="#tab-dashboard"><i class="fas fa-chart-bar me-1 ms-1"></i> لوحة التحكم</a></li>
                <li><a href="#tab-apis"><i class="fas fa-network-wired me-1 ms-1"></i> الـ APIs</a></li>
                <li><a href="#tab-services"><i class="fas fa-cogs me-1 ms-1"></i> الـ Services</a></li>
                <li><a href="#tab-jobs"><i class="fas fa-tasks me-1 ms-1"></i> الـ Jobs</a></li>
                <li><a href="#tab-search"><i class="fas fa-search me-1 ms-1"></i> البحث والتصدير</a></li>
                <li><a href="#tab-security"><i class="fas fa-shield-alt me-1 ms-1"></i> الأمان والتدقيق</a></li>
                <li><a href="#tab-emergency" class="text-danger"><i class="fas fa-exclamation-triangle me-1 ms-1"></i> الطوارئ</a></li>
            </ul>
            
            <!-- Dashboard Tab -->
            <div id="tab-dashboard">
                <!-- Row 1: 4 Stat Cards -->
                <div class="row mt-3">
                    <div class="col-md-3 mb-3">
                        <div class="glass-container text-center">
                            <div class="fs-1 fw-bold text-info" id="stat-total">{{ $stats['total_settings'] ?? 0 }}</div>
                            <div class="opacity-75"><i class="fas fa-sliders-h"></i> إجمالي الإعدادات</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="glass-container text-center">
                            <div class="fs-1 fw-bold text-warning" id="stat-encrypted">{{ $stats['encrypted_count'] ?? 0 }}</div>
                            <div class="opacity-75"><i class="fas fa-lock"></i> إعدادات مشفرة</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="glass-container text-center">
                            <div class="fs-1 fw-bold text-success" id="stat-public">{{ $stats['public_count'] ?? 0 }}</div>
                            <div class="opacity-75"><i class="fas fa-globe"></i> إعدادات عامة</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="glass-container text-center">
                            <div class="fs-1 fw-bold text-danger" id="stat-private">{{ $stats['private_count'] ?? 0 }}</div>
                            <div class="opacity-75"><i class="fas fa-user-shield"></i> إعدادات خاصة</div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Charts -->
                <div class="row mt-3">
                    <div class="col-md-8 mb-3">
                        <div class="glass-container">
                            <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i> تعديلات الإعدادات (آخر 7 أيام)</h5>
                            <div id="chart-changes"></div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="glass-container">
                            <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i> مشفر مقابل عادي</h5>
                            <div id="chart-donut"></div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Group Bar Chart -->
                <div class="row mt-3">
                    <div class="col-12 mb-3">
                        <div class="glass-container">
                            <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i> كثافة الإعدادات حسب المجموعة</h5>
                            <div id="chart-groups"></div>
                        </div>
                </div>
            </div>

            <!-- Security & Audit Tab -->
            <div id="tab-security">
                <div class="row mt-3">
                    <div class="col-md-8 mb-3">
                        <div class="glass-container h-100">
                            <h5 class="mb-3"><i class="fas fa-history me-2 text-info"></i> سجل التدقيق (Audit Trail)</h5>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm table-bordered text-center align-middle">
                                    <thead>
                                        <tr>
                                            <th>الحدث</th>
                                            <th>المفتاح (Key)</th>
                                            <th>المستخدم</th>
                                            <th>الوقت</th>
                                        </tr>
                                    </thead>
                                    <tbody id="audit-trail-body">
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="spinner-border text-info spinner-border-sm me-2" role="status"></div>
                                                جاري تحميل سجل التدقيق...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="glass-container h-100 text-center">
                            <h5 class="mb-3"><i class="fas fa-shield-check text-success me-2"></i> نقاط الأمان (Security Score)</h5>
                            <div class="fs-1 fw-bold text-success mb-2">92%</div>
                            <p class="opacity-75 fs-6">النظام محمي ومطابق لمعايير الأمان (Compliance).</p>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between text-start mb-2 px-3">
                                <span><i class="fas fa-check-circle text-success me-2"></i> تشفير البيانات</span>
                                <span class="badge bg-success">مفعل</span>
                            </div>
                            <div class="d-flex justify-content-between text-start mb-2 px-3">
                                <span><i class="fas fa-check-circle text-success me-2"></i> 2FA إجباري</span>
                                <span class="badge bg-success">مفعل</span>
                            </div>
                            <div class="d-flex justify-content-between text-start px-3">
                                <span><i class="fas fa-exclamation-triangle text-warning me-2"></i> محاولات فاشلة</span>
                                <span class="badge bg-warning text-dark">3 (اليوم)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <div class="glass-container h-100">
                            <h5 class="mb-3"><i class="fas fa-network-wired me-2 text-info"></i> إدارة الـ IP Whitelist</h5>
                            <ul class="list-group list-group-flush" style="background: transparent;">
                                <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center">
                                    <span dir="ltr">192.168.1.100</span>
                                    <span class="badge bg-success rounded-pill">مسموح</span>
                                </li>
                                <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center">
                                    <span dir="ltr">10.0.0.5</span>
                                    <span class="badge bg-success rounded-pill">مسموح</span>
                                </li>
                            </ul>
                            <button class="btn btn-sm btn-outline-info mt-3 w-100"><i class="fas fa-plus"></i> إضافة IP</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="glass-container h-100">
                            <h5 class="mb-3"><i class="fas fa-user-lock me-2 text-info"></i> نظرة عامة على الصلاحيات (RBAC)</h5>
                            <p class="opacity-75 fs-6 mb-3">الصلاحيات الخاصة بإدارة الإعدادات حسب الأدوار.</p>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm table-bordered text-center align-middle">
                                    <thead>
                                        <tr>
                                            <th>الدور</th>
                                            <th>عرض</th>
                                            <th>تعديل</th>
                                            <th>حذف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Super Admin</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                        </tr>
                                        <tr>
                                            <td>Manager</td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-check text-success"></i></td>
                                            <td><i class="fas fa-times text-danger"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Export Tab -->
            <div id="tab-search">
                <div class="row mt-3">
                    <div class="col-12 mb-3">
                        <div class="glass-container">
                            <h5 class="mb-3"><i class="fas fa-search text-info me-2"></i> البحث المتقدم والفلترة</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark text-light border-secondary"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control bg-dark text-light border-secondary" placeholder="البحث في مفاتيح وقيم الإعدادات (Full-text)...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select bg-dark text-light border-secondary">
                                        <option value="">جميع المجموعات (Groups)</option>
                                        <option value="general">General</option>
                                        <option value="security">Security</option>
                                        <option value="ui">UI</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="regexSearch">
                                        <label class="form-check-label text-light" for="regexSearch">تفعيل البحث بالـ Regex</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 text-center opacity-50">
                                <i class="fas fa-database mb-2" style="font-size:2rem;"></i>
                                <p>نتائج البحث ستظهر هنا...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <div class="glass-container h-100 text-center">
                            <h5 class="mb-3"><i class="fas fa-file-export text-success me-2"></i> تصدير الإعدادات</h5>
                            <p class="opacity-75 fs-6 mb-4">قم بتصدير جميع الإعدادات الحالية كنسخة احتياطية.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <button id="btn-export-csv" class="btn btn-outline-success w-50"><i class="fas fa-file-csv"></i> CSV</button>
                                <button id="btn-export-json" class="btn btn-outline-warning w-50"><i class="fas fa-file-code"></i> JSON</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="glass-container h-100 text-center">
                            <h5 class="mb-3"><i class="fas fa-file-import text-info me-2"></i> استيراد الإعدادات</h5>
                            <p class="opacity-75 fs-6 mb-4">استيراد إعدادات من ملف JSON/CSV باستخدام المعالج.</p>
                            <input type="file" id="import-file" accept=".json,.csv" style="display:none;">
                            <button id="btn-import" class="btn btn-outline-info w-100"><i class="fas fa-upload"></i> تشغيل معالج الاستيراد</button>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="glass-container h-100 text-center">
                            <h5 class="mb-3"><i class="fas fa-exchange-alt text-primary me-2"></i> مقارنة الإصدارات (Diff)</h5>
                            <p class="opacity-75 fs-6 mb-4">قارن بين نسختين مختلفتين من الإعدادات لاكتشاف التغييرات.</p>
                            <button class="btn btn-outline-primary w-100"><i class="fas fa-columns"></i> فتح عارض المقارنة</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- APIs Tab -->
            <div id="tab-apis">
                @if(isset($apis) && count($apis) > 0)
                    @foreach($apis as $apiGroup)
                        <h4 class="mt-4 mb-3"><i class="fas {{ $apiGroup['icon'] ?? 'fa-folder' }} me-2 ms-2"></i> {{ $apiGroup['group'] }}</h4>
                        <div class="accordion-group">
                            @foreach($apiGroup['endpoints'] as $endpoint)
                                <h3>
                                    <span class="method-badge method-{{ $endpoint['method'] }}">{{ $endpoint['method'] }}</span>
                                    <span dir="ltr" class="d-inline-block">{{ $endpoint['path'] }}</span>
                                    <span class="float-end fs-6 opacity-75 d-none d-md-inline">{{ $endpoint['description'] }}</span>
                                    <button class="btn btn-sm btn-outline-info float-end me-2 try-it-out-btn" 
                                        data-method="{{ $endpoint['method'] }}" 
                                        data-path="{{ $endpoint['path'] }}"
                                        data-params="{{ isset($endpoint['parameters']) ? json_encode($endpoint['parameters']) : '[]' }}">
                                        <i class="fas fa-play"></i> تجربة API
                                    </button>
                                </h3>
                                <div>
                                    <p><strong>الوصف:</strong> {{ $endpoint['description'] }}</p>
                                    <p><strong>Controller:</strong> <code dir="ltr">{{ $endpoint['controller'] }}</code></p>
                                    
                                    @if(isset($endpoint['parameters']) && count($endpoint['parameters']) > 0)
                                        <h5>المعاملات (Parameters):</h5>
                                        <div class="table-responsive">
                                            <table class="table table-dark table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>الاسم</th>
                                                        <th>النوع</th>
                                                        <th>مطلوب</th>
                                                        <th>الوصف</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($endpoint['parameters'] as $param)
                                                    <tr>
                                                        <td dir="ltr">{{ $param['name'] }}</td>
                                                        <td dir="ltr">{{ $param['type'] }}</td>
                                                        <td>
                                                            @if($param['required'])
                                                                <span class="badge bg-danger">نعم</span>
                                                            @else
                                                                <span class="badge bg-secondary">لا</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $param['description'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    
                                    <h5>مثال للرد (Response Example):</h5>
                                    <pre class="code-block">{{ $endpoint['response_example'] }}</pre>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">لا توجد بيانات API متاحة في الملفات المخصصة.</div>
                @endif
            </div>
            
            <!-- Services Tab -->
            <div id="tab-services">
                @if(isset($services) && count($services) > 0)
                    @foreach($services as $service)
                        <h4 class="mt-4 mb-3"><i class="fas {{ $service['icon'] ?? 'fa-cogs' }} me-2 ms-2"></i> {{ $service['class'] ?? $service['name'] ?? 'Service' }}</h4>
                        <div class="accordion-group">
                            @foreach($service['methods'] as $method)
                                <h3>
                                    <span dir="ltr">{{ $method['name'] }}</span>
                                    <span class="float-end opacity-75 fs-6 d-none d-md-inline">{{ $method['description'] }}</span>
                                </h3>
                                <div>
                                    <p>{{ $method['description'] }}</p>
                                    @if(isset($method['usage_example']))
                                        <h5>مثال للاستخدام:</h5>
                                        <pre class="code-block">{{ $method['usage_example'] }}</pre>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">لا توجد بيانات Services متاحة.</div>
                @endif
            </div>
            
            <!-- Jobs Tab -->
            <div id="tab-jobs">
                @if(isset($jobs) && count($jobs) > 0)
                    <div class="accordion-group">
                        @foreach($jobs as $job)
                            <h3>
                                <span dir="ltr">{{ $job['class'] ?? $job['name'] ?? 'Job' }}</span>
                                <span class="float-end opacity-75 fs-6 d-none d-md-inline">{{ $job['description'] ?? '' }}</span>
                            </h3>
                            <div>
                                <p>{{ $job['description'] ?? 'لا يوجد وصف' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">لا توجد بيانات Jobs متاحة.</div>
                @endif
            </div>

            <!-- Emergency Controls Tab -->
            <div id="tab-emergency">
                <div class="glass-container-danger mt-3">
                    <div class="glass-header-danger">
                        <h4 class="m-0 text-white"><i class="fas fa-shield-alt text-warning me-2 ms-2"></i> لوحة تحكم الطوارئ (المنطقة الحمراء)</h4>
                    </div>
                    <div class="row">
                        <!-- Agent Pause -->
                        <div class="col-md-4 mb-3">
                            <div class="glass-container border-warning h-100 text-center">
                                <i class="fas fa-robot text-warning mb-3" style="font-size:3rem;"></i>
                                <h5 class="text-warning">إيقاف الـ Agents</h5>
                                <p class="opacity-75 fs-6 mb-4">يوقف جميع المهام الاستباقية للوكلاء مؤقتاً.</p>
                                <button id="btn-agent-pause" class="btn btn-outline-warning w-100 fw-bold">إيقاف مؤقت للوكلاء</button>
                            </div>
                        </div>
                        
                        <!-- Maintenance Mode -->
                        <div class="col-md-4 mb-3">
                            <div class="glass-container border-info h-100 text-center">
                                <i class="fas fa-tools text-info mb-3" style="font-size:3rem;"></i>
                                <h5 class="text-info">وضع الصيانة</h5>
                                <p class="opacity-75 fs-6 mb-4">إدخال النظام في وضع الصيانة لإصلاح الأعطال.</p>
                                <button id="btn-maintenance-mode" class="btn btn-outline-info w-100 fw-bold">تفعيل الصيانة</button>
                            </div>
                        </div>

                        <!-- Factory Reset -->
                        <div class="col-md-4 mb-3">
                            <div class="glass-container border-danger h-100 text-center" style="background: rgba(185, 28, 28, 0.4);">
                                <i class="fas fa-skull-crossbones text-white mb-3" style="font-size:3rem;"></i>
                                <h5 class="text-white">إعادة ضبط المصنع</h5>
                                <p class="opacity-75 fs-6 text-light mb-4">مسح جميع الإعدادات الحالية واستعادة الافتراضية.</p>
                                <button id="btn-factory-reset" class="btn btn-danger w-100 fw-bold shadow-lg">إعادة الضبط الشامل</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Live Logs Terminal -->
    <div class="glass-container mt-5">
        <div class="glass-header d-flex justify-content-between align-items-center">
            <h3 class="m-0"><i class="fas fa-terminal me-2 ms-2"></i> Settings Live Logs</h3>
            <span class="badge bg-success pulse-animation">Listening <i class="fas fa-wifi"></i></span>
        </div>
        
        <p class="text-light opacity-75 mb-0">سجل الأحداث الحية لأي تغيير يحدث في إعدادات النظام عبر جميع الـ Users والـ Workspaces.</p>
        
        <div class="terminal-container" id="live-logs-terminal">
            <div class="terminal-line">
                <span class="terminal-time">[{{ now()->format('H:i:s') }}]</span> 
                <span class="text-white">System:</span> Waiting for incoming events on channel 'settings.activity'...
            </div>
            <!-- Logs will be appended here -->
        </div>
    </div>
</div>

<!-- Try API Dialog (Advanced API Explorer) -->
<div id="try-api-dialog" title="تجربة API (المستكشف المتقدم)" style="display:none;" dir="rtl">
    <div class="glass-container" style="padding:15px; margin:0; border: none; box-shadow: none;">
        
        <!-- API URL Banner -->
        <div class="alert alert-dark border-secondary mb-3 d-flex justify-content-between align-items-center">
            <span id="try-api-full-url" class="font-monospace text-info" dir="ltr"></span>
            <span id="try-api-method-badge" class="badge"></span>
        </div>

        <!-- Inner Tabs for Advanced Features -->
        <ul class="nav nav-pills mb-3" id="tryApiTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active bg-transparent text-light border-secondary" id="request-tab" data-bs-toggle="pill" data-bs-target="#request-panel" type="button" role="tab">الطلب (Request)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link bg-transparent text-light border-secondary" id="code-tab" data-bs-toggle="pill" data-bs-target="#code-panel" type="button" role="tab">Code Snippets</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link bg-transparent text-light border-secondary" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-panel" type="button" role="tab">سجل الطلبات</button>
            </li>
        </ul>

        <div class="tab-content" id="tryApiTabsContent">
            <!-- Request Builder Panel -->
            <div class="tab-pane fade show active" id="request-panel" role="tabpanel">
                <form id="try-api-form">
                    <div id="try-api-params" class="mb-3"></div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <div class="form-check form-switch text-warning">
                            <input class="form-check-input" type="checkbox" id="schema-validator-toggle" checked>
                            <label class="form-check-label" for="schema-validator-toggle"><i class="fas fa-check-double me-1"></i> تفعيل الـ Validator</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btn-mock-data"><i class="fas fa-magic"></i> Mock Data</button>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-paper-plane me-2"></i> إرسال الطلب (Send)</button>
                </form>
            </div>

            <!-- Code Snippets Panel -->
            <div class="tab-pane fade" id="code-panel" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <select id="snippet-language" class="form-select form-select-sm bg-dark text-light border-secondary w-50">
                        <option value="curl">cURL</option>
                        <option value="php">PHP (Guzzle)</option>
                        <option value="js">JavaScript (Axios)</option>
                        <option value="python">Python (Requests)</option>
                    </select>
                    <button class="btn btn-sm btn-outline-success" onclick="copySnippet()"><i class="fas fa-copy"></i> Copy</button>
                </div>
                <pre id="code-snippet-viewer" class="code-block" style="font-size:12px; white-space: pre-wrap;" dir="ltr"></pre>
            </div>

            <!-- History Panel -->
            <div class="tab-pane fade" id="history-panel" role="tabpanel">
                <ul id="request-history-list" class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-light border-secondary text-center opacity-50">لا يوجد سجل طلبات حالي.</li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary mt-4">

        <!-- Response Section -->
        <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="m-0"><i class="fas fa-reply text-success me-2"></i> الاستجابة (Response):</h6>
                <div class="d-flex gap-2 align-items-center">
                    <span id="response-time-badge" class="badge bg-secondary d-none">Time: 0ms</span>
                    <button class="btn btn-sm btn-outline-primary" id="btn-diff-viewer"><i class="fas fa-columns"></i> Diff View</button>
                </div>
            </div>
            <pre id="try-api-response" class="code-block border-secondary" style="max-height: 250px; overflow-y: auto; font-size:12px; white-space: pre-wrap;" dir="ltr">في انتظار الطلب...</pre>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(document).ready(function() {
        // Initialize Tabs
        $("#settings-tabs").tabs();
        
        // Initialize Accordions
        $(".accordion-group").accordion({
            collapsible: true,
            active: false,
            heightStyle: "content"
        });

        // ── Dashboard Charts (ApexCharts) ──────────────────────────────────
        const changesDates  = @json(array_column($stats['recent_changes'] ?? [], 'date'));
        const changesCounts = @json(array_column($stats['recent_changes'] ?? [], 'count'));
        const groupNames    = @json(array_column($stats['groups'] ?? [], 'group'));
        const groupCounts   = @json(array_column($stats['groups'] ?? [], 'count'));
        const encryptedCount = {{ $stats['encrypted_count'] ?? 0 }};
        const totalCount     = {{ $stats['total_settings'] ?? 0 }};

        // Line/Area Chart — Changes over 7 days
        const changesChart = new ApexCharts(document.querySelector('#chart-changes'), {
            chart: { type: 'area', height: 220, background: 'transparent', foreColor: '#e2e8f0', toolbar: { show: false } },
            series: [{ name: 'تعديلات', data: changesCounts }],
            xaxis: { categories: changesDates },
            colors: ['#38bdf8'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 },
            grid: { borderColor: 'rgba(255,255,255,0.1)' },
            tooltip: { theme: 'dark' },
        });
        changesChart.render();

        // Donut Chart — Encrypted vs Plain
        const donutChart = new ApexCharts(document.querySelector('#chart-donut'), {
            chart: { type: 'donut', height: 220, background: 'transparent', foreColor: '#e2e8f0' },
            series: [encryptedCount, Math.max(0, totalCount - encryptedCount)],
            labels: ['مشفر', 'عادي'],
            colors: ['#f59e0b', '#38bdf8'],
            legend: { position: 'bottom' },
            tooltip: { theme: 'dark' },
            plotOptions: { pie: { donut: { size: '65%' } } },
        });
        donutChart.render();

        // Bar Chart — Settings per Group
        const groupsChart = new ApexCharts(document.querySelector('#chart-groups'), {
            chart: { type: 'bar', height: 200, background: 'transparent', foreColor: '#e2e8f0', toolbar: { show: false } },
            series: [{ name: 'عدد الإعدادات', data: groupCounts }],
            xaxis: { categories: groupNames },
            colors: ['#818cf8'],
            grid: { borderColor: 'rgba(255,255,255,0.1)' },
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            tooltip: { theme: 'dark' },
        });
        groupsChart.render();
        // ─────────────────────────────────────────────────────────────────

        // Initialize Laravel Echo listener for Live Logs
        if (typeof window.Echo !== 'undefined' && typeof window.Echo.private === 'function') {
            window.Echo.private('settings.activity')
                .listen('SettingsActivityLogged', (e) => {
                    const terminal = $('#live-logs-terminal');
                    
                    // Format time
                    const date = new Date(e.timestamp || Date.now());
                    const timeStr = date.toLocaleTimeString();
                    
                    // Format action and message
                    const action = e.action || e.actionType || 'UNKNOWN';
                    let color = '#4ade80'; // default green
                    if(action.toUpperCase() === 'DELETED') color = '#ef4444';
                    if(action.toUpperCase() === 'UPDATED') color = '#f59e0b';
                    if(action.toUpperCase() === 'BULK_UPDATED') color = '#a855f7';
                    
                    const msg = e.message || 'Settings activity detected';
                    
                    // Add new line
                    const line = `
                        <div class="terminal-line" style="display:none;">
                            <span class="terminal-time">[${timeStr}]</span> 
                            <span class="terminal-action" style="color:${color};">[${action.toUpperCase()}]</span>
                            <span class="text-white ms-2">${msg}</span>
                            <br>
                            <span class="text-muted" style="font-size:0.85em; margin-left: 85px;">
                                Context: ${JSON.stringify(e.context || {})}
                            </span>
                        </div>
                    `;
                    
                    const $line = $(line);
                    terminal.append($line);
                    $line.fadeIn(500);
                    
                    // Auto scroll to bottom
                    terminal.scrollTop(terminal[0].scrollHeight);
                });
        } else {
            console.warn("Laravel Echo is not defined on window. Make sure it is compiled.");
            $('#live-logs-terminal').append('<div class="terminal-line text-warning">Warning: Laravel Echo not found. Live logs will not connect.</div>');
        }

        // Try It Out API Functionality
        let currentMethod = '';
        let currentPath = '';

        $('.try-it-out-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // prevent accordion from toggling
            
            currentMethod = $(this).data('method');
            currentPath = $(this).data('path');
            const params = $(this).data('params');
            
            let paramsHtml = '';
            
            // Extract URL variables like {workspaceId} or {settingId}
            const pathVars = [...currentPath.matchAll(/\{([^}]+)\}/g)].map(m => m[1]);
            
            if (pathVars.length > 0) {
                paramsHtml += `<h6 class="text-info mt-2">متغيرات المسار (Path Variables)</h6>`;
                pathVars.forEach(v => {
                    paramsHtml += `
                        <div class="form-group mb-2">
                            <label class="form-label text-light">${v} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-dark text-light border-secondary try-api-input path-var" data-var="${v}" required>
                        </div>
                    `;
                });
            }

            if (params && params.length > 0) {
                paramsHtml += `<h6 class="text-info mt-2">متغيرات الطلب (Query/Body)</h6>`;
                params.forEach(p => {
                    const reqHtml = p.required ? '<span class="text-danger">*</span>' : '';
                    const reqAttr = p.required ? 'required' : '';
                    paramsHtml += `
                        <div class="form-group mb-2">
                            <label class="form-label text-light" dir="ltr">${p.name} ${reqHtml} <small class="text-muted">(${p.type})</small></label>
                            <input type="text" class="form-control bg-dark text-light border-secondary try-api-input query-var" name="${p.name}" ${reqAttr}>
                        </div>
                    `;
                });
            }
            
            if (!paramsHtml) {
                paramsHtml = '<p class="text-muted">لا يوجد معاملات إضافية مطلوبة.</p>';
            }
            
            $('#try-api-params').html(paramsHtml);
            $('#try-api-response').text('في انتظار الطلب...').removeClass('text-danger text-success');
            
            $('#try-api-dialog').dialog({
                width: 500,
                modal: true,
                resizable: true,
                dialogClass: 'custom-glass-dialog'
            });
        });

        $('#try-api-form').on('submit', function(e) {
            e.preventDefault();
            
            let finalPath = currentPath;
            
            // Replace path variables
            $('.path-var').each(function() {
                const varName = $(this).data('var');
                const val = $(this).val();
                finalPath = finalPath.replace('{' + varName + '}', val);
            });
            
            // Collect query/body data
            let data = {};
            $('.query-var').each(function() {
                const name = $(this).attr('name');
                const val = $(this).val();
                if (val !== '') {
                    data[name] = val;
                }
            });
            
            $('#try-api-response').text('جاري الإرسال...').removeClass('text-danger text-success');
            
            // Prefix the URL to ensure it reaches the right host if needed.
            // Using relative path assuming this view is hosted on the same domain as the APIs.
            if (!finalPath.startsWith('/')) {
                finalPath = '/' + finalPath;
            }

            const token = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

            $.ajax({
                url: finalPath,
                type: currentMethod,
                data: (currentMethod === 'GET' || currentMethod === 'DELETE') ? data : JSON.stringify(data),
                contentType: (currentMethod === 'GET' || currentMethod === 'DELETE') ? 'application/x-www-form-urlencoded; charset=UTF-8' : 'application/json',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    $('#try-api-response').removeClass('text-danger').addClass('text-success').text(JSON.stringify(response, null, 4));
                },
                error: function(xhr) {
                    let errStr = "Error: " + xhr.status + " " + (xhr.statusText || '') + "\n";
                    try {
                        errStr += JSON.stringify(xhr.responseJSON, null, 4);
                    } catch (err) {
                        errStr += xhr.responseText;
                    }
                    $('#try-api-response').removeClass('text-success').addClass('text-danger').text(errStr);
                }
            });
        });

        // Emergency Controls Logic
        
        // 1. Agent Pause
        let agentPauseCountdown = null;
        $('#btn-agent-pause').on('click', function() {
            const btn = $(this);
            if(btn.data('counting')) return;
            
            let seconds = 5;
            btn.data('counting', true);
            btn.removeClass('btn-outline-warning').addClass('btn-warning text-dark');
            
            agentPauseCountdown = setInterval(() => {
                btn.text(`تأكيد الإيقاف في (${seconds})... انقر للإلغاء`);
                if(seconds <= 0) {
                    clearInterval(agentPauseCountdown);
                    btn.text('جاري الإيقاف...');
                    // Fire Ajax
                    $.post('/api/v1/settings/system/agent-pause', { 
                        enabled: 1, 
                        reason: 'Admin requested global pause',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).done(function(res) {
                        alert(res.message || 'تم الإيقاف بنجاح');
                        btn.removeClass('btn-warning text-dark').addClass('btn-outline-warning').text('إيقاف مؤقت للوكلاء');
                        btn.data('counting', false);
                    }).fail(function(xhr) {
                        alert('حدث خطأ: ' + (xhr.responseJSON?.message || xhr.statusText));
                        btn.removeClass('btn-warning text-dark').addClass('btn-outline-warning').text('إيقاف مؤقت للوكلاء');
                        btn.data('counting', false);
                    });
                }
                seconds--;
            }, 1000);
        });
        
        // Cancel countdown if clicked again during countdown
        $('#btn-agent-pause').on('dblclick', function() {
            if($(this).data('counting')) {
                clearInterval(agentPauseCountdown);
                $(this).removeClass('btn-warning text-dark').addClass('btn-outline-warning').text('إيقاف مؤقت للوكلاء');
                $(this).data('counting', false);
            }
        });

        // 2. Factory Reset Wizard
        $('#btn-factory-reset').on('click', function() {
            const confirmText = prompt('تحذير خطير: سيتم مسح جميع إعدادات النظام الحالية. لتأكيد الإجراء، اكتب: RESET ALL');
            
            if (confirmText === 'RESET ALL') {
                const reason = prompt('يرجى كتابة سبب إعادة الضبط (إجباري لتسجيل الحدث):');
                if(!reason) {
                    alert('تم الإلغاء. يجب كتابة سبب إعادة الضبط.');
                    return;
                }
                
                $(this).text('جاري إعادة الضبط...').prop('disabled', true);
                
                $.ajax({
                    url: '/api/v1/settings/factory-reset',
                    type: 'POST',
                    data: JSON.stringify({ confirmation: confirmText, reason: reason }),
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        alert('تم إعادة ضبط المصنع بنجاح!');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        alert('حدث خطأ: ' + (xhr.responseJSON?.error || xhr.statusText));
                        $('#btn-factory-reset').text('إعادة الضبط الشامل').prop('disabled', false);
                    }
                });
            } else if(confirmText !== null) {
                alert('النص غير متطابق. تم الإلغاء.');
            }
        });

        // 3. Maintenance Mode
        $('#btn-maintenance-mode').on('click', function() {
            if(confirm('هل أنت متأكد من إدخال النظام في وضع الصيانة؟ هذا سيمنع وصول المستخدمين.')) {
                $.post('/api/v1/settings/system/maintenance-mode', { 
                    enabled: 1, 
                    reason: 'Admin requested maintenance mode',
                    _token: $('meta[name="csrf-token"]').attr('content')
                }).done(function(res) {
                    alert(res.message);
                }).fail(function(xhr) {
                    alert('حدث خطأ: ' + (xhr.responseJSON?.message || xhr.statusText));
                });
            }
        });
        // 4. Audit Trail Fetch
        function loadAuditTrail() {
            $.get('/api/v1/settings/admin/audit-trail', { limit: 10 })
                .done(function(res) {
                    const tbody = $('#audit-trail-body');
                    tbody.empty();
                    if(res.data && res.data.length > 0) {
                        res.data.forEach(log => {
                            let badgeClass = 'bg-info';
                            if(log.message.includes('created')) badgeClass = 'bg-success';
                            else if(log.message.includes('deleted')) badgeClass = 'bg-danger';
                            else if(log.message.includes('updated')) badgeClass = 'bg-warning text-dark';
                            
                            // Extract key from context if available
                            const key = log.context?.key || '-';
                            const userId = log.user_id || 'System';
                            
                            tbody.append(`
                                <tr>
                                    <td><span class="badge ${badgeClass}">${log.message}</span></td>
                                    <td dir="ltr">${key}</td>
                                    <td>${userId}</td>
                                    <td dir="ltr" class="opacity-75">${new Date(log.timestamp).toLocaleString()}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="4" class="text-center opacity-50">لا توجد حركات مسجلة مؤخراً</td></tr>');
                    }
                })
                .fail(function() {
                    $('#audit-trail-body').html('<tr><td colspan="4" class="text-center text-danger">فشل في تحميل سجل التدقيق</td></tr>');
                });
        }
        
        // Load audit trail on Security Tab click
        $('a[href="#tab-security"]').on('shown.bs.tab', function (e) {
            loadAuditTrail();
        });
        
        // 5. Export JSON
        $('#btn-export-json').on('click', function() {
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحضير');
            $.ajax({
                url: '/api/v1/settings/admin/export',
                type: 'POST',
                data: JSON.stringify({ format: 'json' }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(res.data, null, 2));
                    const downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href",     dataStr);
                    downloadAnchorNode.setAttribute("download", "settings_export.json");
                    document.body.appendChild(downloadAnchorNode); // required for firefox
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                    $('#btn-export-json').prop('disabled', false).html('<i class="fas fa-file-code"></i> JSON');
                },
                error: function(xhr) {
                    alert('خطأ في تصدير البيانات');
                    $('#btn-export-json').prop('disabled', false).html('<i class="fas fa-file-code"></i> JSON');
                }
            });
        });

        // 6. Export CSV
        $('#btn-export-csv').on('click', function() {
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التحضير');
            $.ajax({
                url: '/api/v1/settings/admin/export',
                type: 'POST',
                data: JSON.stringify({ format: 'csv' }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    const dataStr = "data:text/csv;charset=utf-8," + encodeURIComponent(res.data);
                    const downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href",     dataStr);
                    downloadAnchorNode.setAttribute("download", "settings_export.csv");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                    $('#btn-export-csv').prop('disabled', false).html('<i class="fas fa-file-csv"></i> CSV');
                },
                error: function(xhr) {
                    alert('خطأ في تصدير البيانات');
                    $('#btn-export-csv').prop('disabled', false).html('<i class="fas fa-file-csv"></i> CSV');
                }
            });
        });

        // 7. Import settings
        $('#btn-import').on('click', function() {
            $('#import-file').click();
        });
        
        $('#import-file').on('change', function(e) {
            const file = e.target.files[0];
            if(!file) return;
            
            if(!confirm('تحذير: استيراد الإعدادات قد يقوم بتحديث أو استبدال الإعدادات الحالية. هل تود المتابعة؟')) {
                $(this).val('');
                return;
            }
            
            const formData = new FormData();
            formData.append('file', file);
            
            $('#btn-import').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الاستيراد...');
            
            $.ajax({
                url: '/api/v1/settings/admin/import',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    alert(res.message || 'تم الاستيراد بنجاح');
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('حدث خطأ أثناء الاستيراد: ' + (xhr.responseJSON?.message || xhr.statusText));
                    $('#btn-import').prop('disabled', false).html('<i class="fas fa-upload"></i> تشغيل معالج الاستيراد');
                    $('#import-file').val('');
                }
            });
        });
    });
</script>
@endpush
@endsection
