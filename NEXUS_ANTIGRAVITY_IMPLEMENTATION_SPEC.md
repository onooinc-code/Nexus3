# 🚀 NexusV3 Architecture & Feature Additions Specification for Antigravity Agent

> **Target Core Codebase Path:** `/www/wwwroot/Nexus/core/Nexus3`  
> **Framework:** Laravel 13 (Monolith)  
> **Database:** MySQL 8.0+  
> **Real-time Engine:** Laravel Reverb (WebSockets on port 8080)

---

## 📋 Table of Contents
1. **Overview & Integration Architecture** (OpenClaw <-> Nexus <-> Extension)
2. **Database Migrations Required**
3. **Model & Fillables Updates**
4. **API Endpoints & Controllers**
5. **Real-Time WebSockets & Reverb Events**
6. **Agent Registration Seeder**

---

## 1. 🌐 Integration Architecture (كيف يتم الربط بين الوكلاء)

### **ألية الربط بين OpenClaw Agent و Nexus Agent:**
- **Nexus** يحتفظ بجدول الوكلاء (`agents`). ننشئ سريعا وكيل باسم **`ertugrul_browser_agent`**.
- عند رغبة **سولي (Souly)** أو **بولا (Pola)** أو النظام بفتح مهمة متصفح، يتم استدعاء API الـ Tasks في Nexus وإعطائها:
  - `origin_agent_id`: `souly` / `pola`
  - `target_agent_id`: `ertugrul_browser_agent`
  - `task_type`: `immediate` | `recurring` | `event_driven` | `pipeline`
  - `dynamic_system_instruction`: التعليمات البرمجية المخصصة لطبيعة المهمة المطلوبة.
- **أرطغرل (OpenClaw Browser Agent)** يتصنّت على أحداث Reverb/WebSockets عبر القناة `nexus-agent-tasks.ertugrul_browser_agent` أو يقرأ الـ Pending Tasks من API Nexus.
- **Chrome Extension** متصلة بـ Nexus Reverb و background worker، وتتلقى الأوامر التنفيذية من أرطغرل، وتنفذها على المتصفح الحقيقي، ثم تبعث بالـ `execution_proof` والتحديثات لـ Nexus.

---

## 2. 🗄️ Database Migrations Required (التعديلات في قواعد البيانات)

قم بإنشاء Migration جديدة: `php artisan make:migration add_browser_agent_fields_to_agent_tasks_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_tasks', 'origin_agent_id')) {
                $table->string('origin_agent_id')->nullable()->after('agent_id')->index();
            }
            if (!Schema::hasColumn('agent_tasks', 'target_agent_id')) {
                $table->string('target_agent_id')->default('ertugrul_browser_agent')->after('origin_agent_id')->index();
            }
            if (!Schema::hasColumn('agent_tasks', 'task_type')) {
                $table->enum('task_type', ['immediate', 'recurring', 'event_driven', 'pipeline'])->default('immediate')->after('target_agent_id');
            }
            if (!Schema::hasColumn('agent_tasks', 'dynamic_system_instruction')) {
                $table->longText('dynamic_system_instruction')->nullable()->after('task_type');
            }
            if (!Schema::hasColumn('agent_tasks', 'execution_proof')) {
                $table->json('execution_proof')->nullable()->after('dynamic_system_instruction');
            }
            if (!Schema::hasColumn('agent_tasks', 'dom_event_trigger')) {
                $table->json('dom_event_trigger')->nullable()->after('execution_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'origin_agent_id',
                'target_agent_id',
                'task_type',
                'dynamic_system_instruction',
                'execution_proof',
                'dom_event_trigger'
            ]);
        });
    }
};
```

---

## 3. 📦 Model Updates (`app/Models/AgentTask.php` أو `Task.php`)

أضف الحقول الجديدة في الـ `$fillable` والـ `$casts`:

```php
protected $fillable = [
    'title',
    'description',
    'status',
    'agent_id',
    'origin_agent_id',
    'target_agent_id',
    'task_type',
    'dynamic_system_instruction',
    'execution_proof',
    'dom_event_trigger',
    // ... rest of fillable fields
];

protected $casts = [
    'execution_proof' => 'array',
    'dom_event_trigger' => 'array',
];
```

---

## 4. 🔌 API Endpoints & Controllers (`app/Http/Controllers/Api/V1/`)

### **أ. تحديث `TaskController.php`:**
- **`POST /api/v1/agent-tasks`**: إنشاء مهمة مع دعم إرسال `origin_agent_id`, `target_agent_id`, `dynamic_system_instruction`, `task_type`.
- **`GET /api/v1/agent-tasks/pending`**: جلب المهام المعلقة الخاصة بـ `target_agent_id` (مفلترة بـ `target_agent_id=ertugrul_browser_agent` وحالة `pending`).
- **`POST /api/v1/agent-tasks/{id}/status`**: تحديث حالة المهمة (`in_progress`, `completed`, `failed`) وتخزين الـ `execution_proof` ورابط لقطة الشاشة أو السجلات.

### **ب. إنشاء `DomEventTriggerController.php` (في Proactive AI / Events):**
- **`POST /api/v1/events/dom-trigger`**:
  - يستقبل الأحداث القادمة من Chrome Extension (`MutationObserver` events مثل رسائل فيسبوك الجديدة).
  - يقوم بإنشاء حدث في المنظومة وبثه عبر Laravel Reverb.
  - إذا وجد قاعدة في Proactive AI Hub مطابقة للحدث، يقوم تلقائياً بإنشاء Task جديدة موجهة لـ `ertugrul_browser_agent`.

---

## 5. 📡 Real-Time WebSockets & Reverb Events

قم بإنشاء Events جديدة في Laravel بثّية (ShouldBroadcastNow):
1. **`TaskDispatchedToBrowserAgent`**:
   - Channel: `nexus-browser-agent`
   - Payload: بيانات الـ Task كاملة + `dynamic_system_instruction`.
2. **`DOMEventDispatched`**:
   - Channel: `nexus-dom-events`
   - Payload: بيانات التغيير في الـ DOM الواردة من الـ Extension.

---

## 6. 🤖 Agent Registration (Seeder)

إنشاء Seeder أو تنفيذه لإضافة أرطغرل في جدول الـ Agents:

```php
use App\Models\Agent;

Agent::updateOrCreate(
    ['code' => 'ertugrul_browser_agent'],
    [
        'name' => 'Ertugrul Browser Orchestrator',
        'type' => 'browser_automation',
        'description' => 'Autonomous Chrome browser controller, DOM observer, and Vision Captcha solver.',
        'is_active' => true,
        'metadata' => json_encode([
            'extension_bridge' => 'Nexus Agentic Browser Bridge v1.0',
            'capabilities' => ['dom_mutation_listen', 'vision_captcha_solve', 'human_typing_emulation']
        ])
    ]
);
```

---
