# 🤖 AI Models Hub — توثيق شامل لجميع الملفات

> **تاريخ التوثيق:** 2026-07-04
> **المسؤول:** Hedra (Technical Lead)
> **المشروع:** Nexus v3

---

## 📋 فهرس المحتويات

| القسم | الوصف |
|-------|-------|
| [1. نظرة عامة](#1-نظرة-عامة) | مقدمة وهيكل الـ Hub |
| [2. توثيق الـ Documentation](#2-توثيق-documentation) | ملفات الـ Markdown التوثيقية |
| [3. نماذج البيانات (Models)](#3-نماذج-البيانات-models) | Eloquent Models |
| [4. المتحكمات (Controllers)](#4-المتحكمات-controllers) | HTTP Controllers |
| [5. طبقة الخدمات (Services)](#5-طبقة-الخدمات-services) | Business Logic Services |
| [6. الوظائف الخلفية (Jobs)](#6-الوظائف-الخلفية-jobs) | Queue Jobs |
| [7. قواعد البيانات (Migrations)](#7-قواعد-البيانات-migrations) | Database Migrations |
| [8. واجهة المستخدم (Frontend)](#8-واجهة-المستخدم-frontend) | Blade Views |

---

## 1. نظرة عامة

**AI Models Hub** هو **المحرك المركزي للتوجيه الذكي** لجميع طلبات الذكاء الاصطناعي داخل منصة Nexus. يعمل كطبقة وساطة (abstraction layer) كاملة بين كل أجزاء النظام وبين مزودي LLM الخارجيين.

### المهام الأساسية
- 🔀 **Dynamic Routing** — توجيه كل طلب AI إلى المزود والنموذج المناسب بناءً على الـ Intent
- 🔑 **Encrypted Key Management** — تخزين وفك تشفير مفاتيح API بطريقة آمنة (AES-256)
- 💰 **Usage & Cost Tracking** — تتبع الاستهلاك والتكاليف بالتفصيل
- 🛡️ **Circuit Breaker** — حماية النظام من تعطل مزود معين عبر آلية الـ circuit breaker
- 📊 **Telemetry Dashboard** — مراقبة الأداء في الوقت الفعلي
- 🔄 **Fallback Chains** — التحويل التلقائي لمزود احتياطي عند الفشل

### خريطة تدفق البيانات

```
Any Controller/Job/Service
         │
         ▼
  AiRouteController (Primary Gateway)
         │
         ├──► IntentRoutingEngine ──► intent_routing Table
         │
         ├──► DynamicProviderRegistry ──► ai_providers Table
         │
         ├──► EncryptedApiKeyStorage ──► ai_api_keys Table (AES-256)
         │
         ├──► CircuitBreaker ──► Redis Cache
         │
         ├──► PayloadAdapterFactory (OpenAI/Anthropic/Gemini/Groq format)
         │
         ▼
  External LLM API (HTTP Request)
         │
         ▼
  UsageTracker ──► usage_logs Table
         │
  AiAuditTrail ──► ai_audit_trails Table
```

---

## 2. توثيق Documentation

### 📄 `Documentation/The-Hubs/AI Models Hub/Architecture.md`
**المسار الكامل:** `c:/Users/hedra/Desktop/N-V3/Nexus/Documentation/The-Hubs/AI Models Hub/Architecture.md`
**الحجم:** 5,584 bytes | **الأسطر:** 178

**الغرض:** ملف التوثيق المعماري الرئيسي للـ Hub. يشرح كيفية عمل الـ Hub من الداخل ويتضمن Mermaid diagrams توضيحية.

**المحتوى الرئيسي:**
- Architecture diagram كامل بالـ Mermaid
- شرح كل Component الرئيسي
- Provider Architecture وآلية Universal Adapter
- Intent Routing System بالتفصيل
- Data Flow للطلب الكامل من البداية للنهاية

```markdown
# AI Models Hub — Architecture

## 1. Overview

The AI Models Hub is the **central intelligence router** for all AI requests within Nexus.
It dynamically routes AI requests to the correct provider and model based on intent,
manages encrypted API keys, tracks usage and cost, and provides a circuit breaker for reliability.

## 4. Provider Architecture

Each AI provider is registered as a configuration record in the `ai_providers` table.
The system uses a **Universal Adapter** pattern via `DynamicRestProvider` — a single PHP class
that makes requests to any OpenAI-compatible API by reading the `base_url` from the database.

## 6. Key Models

### AIProvider
Fields: id, name, slug, type, base_url, test_endpoint, is_active, last_synced_at
Relationships: hasMany AIApiKey, AIModel, UsageLog, AiAuditTrail

### AIModel
Fields: id, provider_id, name, slug, context_window, is_active, routing_profiles(json)

### IntentRouting
Fields: id, intent_name, default_provider_id, default_model_id, fallback_provider_id,
        conditions(json), priority
```

---

### 📄 `Documentation/The-Hubs/AI Models Hub/Requirements.md`
**المسار الكامل:** `c:/Users/hedra/Desktop/N-V3/Nexus/Documentation/The-Hubs/AI Models Hub/Requirements.md`
**الحجم:** 3,161 bytes | **الأسطر:** 69

**الغرض:** يحدد المتطلبات الوظيفية وغير الوظيفية الكاملة للـ Hub.

**المتطلبات الوظيفية الرئيسية:**

| المتطلب | الوصف |
|---------|-------|
| Provider Registry | تسجيل أي مزود AI ديناميكياً بدون كود |
| API Key Management | تشفير AES-256 مع دعم rotation |
| Connectivity Testing | اختبار الاتصال بكل مزود |
| Intent Routing | جدول توجيه ذكي مع Fallback |
| Circuit Breaker | فصل دائرة الاتصال عند تعطل المزود |
| Usage & Cost Tracking | تتبع التكاليف والـ Tokens |
| Telemetry Dashboard | لوحة مراقبة مجمعة |
| AI Instances | كيانات قابلة لإعادة الاستخدام |

**المتطلبات غير الوظيفية:**
- مفاتيح API لا تُرجع أبداً في plaintext
- Circuit breaker لمنع Cascading Failures
- إضافة مزود جديد = إضافة Database Record فقط

---

## 3. نماذج البيانات (Models)

### 📄 `app/Models/AIProvider.php`
**الحجم:** 859 bytes | **الأسطر:** 46

**الغرض:** يمثل مزود AI خارجي (OpenAI, Anthropic, Google Gemini, Groq, إلخ). يحتوي على إعدادات الاتصال ونقاط النهاية.

**الخصائص الرئيسية:**
- `$incrementing = false` → يستخدم UUID كـ Primary Key
- `resolved_api_key` → خاصية PHP عامة (ليست Eloquent attribute) لتخزين المفتاح المفكوك مؤقتاً

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AIProvider extends BaseModel
{
    public $resolved_api_key;

    protected $table = 'ai_providers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'base_url',
        'models_fetch_endpoint',
        'generate_endpoint',
        'test_endpoint',
        'auth_header_format',
        'payload_format',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function getApiKeyAttribute()
    {
        return $this->resolved_api_key;
    }

    public function models(): HasMany
    {
        return $this->hasMany(AIModel::class, 'provider_id');
    }
}
```

---

### 📄 `app/Models/AIModel.php`
**الحجم:** 936 bytes | **الأسطر:** 45

**الغرض:** يمثل نموذج ذكاء اصطناعي محدد (مثل: `gpt-4o`, `claude-3-opus`, `gemini-1.5-pro`). مرتبط بـ Provider.

**الحقول المهمة:**
| الحقل | النوع | الوصف |
|-------|-------|-------|
| `quality_tier` | string | تصنيف الجودة (budget/standard/premium) |
| `cost_profile` | string | ملف التكلفة (low/medium/high) |
| `latency_profile` | string | ملف الكمون (fast/balanced/safe) |
| `language_support` | JSON | قائمة اللغات المدعومة |
| `presets` | JSON | إعدادات مسبقة مخصصة |

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIModel extends BaseModel
{
    protected $table = 'ai_models';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'provider_id', 'context_window',
        'input_cost_per_m', 'output_cost_per_m', 'description',
        'last_synced_at', 'quality_tier', 'cost_profile',
        'latency_profile', 'security_class', 'language_support',
        'version_tag', 'presets',
    ];

    protected $casts = [
        'language_support' => 'array',
        'presets'          => 'array',
        'last_synced_at'   => 'datetime',
        'context_window'   => 'integer',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AIProvider::class, 'provider_id');
    }
}
```

---

### 📄 `app/Models/AIApiKey.php`
**الحجم:** 484 bytes | **الأسطر:** 30

**الغرض:** يخزن مفاتيح API المشفرة لكل مزود. الحقل `key_hash` يحتوي على المفتاح المشفر بـ AES-256 عبر `EncryptedApiKeyStorage`.

```php
<?php

namespace App\Models;

class AIApiKey extends BaseModel
{
    protected $table = 'ai_api_keys';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'provider_id', 'key_hash', 'name', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(AIProvider::class, 'provider_id');
    }
}
```

---

### 📄 `app/Models/IntentRouting.php`
**الحجم:** 966 bytes | **الأسطر:** 44

**الغرض:** يمثل سجل توجيه Intent. يربط اسم Intent بمزود ونموذج افتراضي وآخر احتياطي (fallback).

**العلاقات:**
```
intent_name → default_provider_id → AIProvider
              default_model_id    → AIModel
              fallback_provider_id → AIProvider
              fallback_model_id   → AIModel
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentRouting extends BaseModel
{
    protected $table = 'intent_routing';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'intent_name', 'default_provider_id',
        'default_model_id', 'fallback_provider_id', 'fallback_model_id',
    ];

    public function defaultProvider(): BelongsTo
    {
        return $this->belongsTo(AIProvider::class, 'default_provider_id');
    }

    public function defaultModel(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'default_model_id');
    }

    public function fallbackProvider(): BelongsTo
    {
        return $this->belongsTo(AIProvider::class, 'fallback_provider_id');
    }

    public function fallbackModel(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'fallback_model_id');
    }
}
```

---

### 📄 `app/Models/UsageLog.php`
**الحجم:** 876 bytes | **الأسطر:** 42

**الغرض:** يسجل كل طلب AI بالتفصيل لأغراض التكلفة والإحصاء. يُكتب بعد كل استجابة ناجحة.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends BaseModel
{
    protected $table = 'usage_logs';

    protected $fillable = [
        'provider_id', 'model_id', 'intent_name',
        'input_tokens', 'output_tokens',
        'input_cost', 'output_cost', 'total_cost', 'timestamp',
    ];

    protected $casts = [
        'input_tokens'  => 'integer',
        'output_tokens' => 'integer',
        'input_cost'    => 'float',
        'output_cost'   => 'float',
        'total_cost'    => 'float',
        'timestamp'     => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AIProvider::class, 'provider_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'model_id');
    }
}
```

---

### 📄 `app/Models/AiAuditTrail.php`
**الحجم:** 714 bytes | **الأسطر:** 35

**الغرض:** سجل مراجعة شامل لكل حدث في منظومة AI (تنفيذ، فشل، fallback، تجاوز ميزانية). يُستخدم في لوحة الـ Telemetry.

**أنواع الأحداث:** `route_executed`, `fallback_triggered`, `key_accessed`, `budget_exceeded`, `rate_limited`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAuditTrail extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'event_type', 'provider_id', 'model_id', 'intent',
        'status', 'latency_ms', 'fallback_triggered',
        'fallback_sequence', 'estimated_cost',
        'input_tokens', 'output_tokens', 'error_type',
        'error_message', 'workspace_id', 'user_id', 'metadata',
    ];

    protected $casts = [
        'fallback_triggered' => 'boolean',
        'metadata'           => 'array',
        'estimated_cost'     => 'decimal:6',
    ];
}
```

---

### 📄 `app/Models/AiInstance.php`
**الحجم:** 571 bytes | **الأسطر:** 33

**الغرض:** يمثل كياناً قابلاً لإعادة الاستخدام يجمع Provider + Model + إعدادات. تستخدمه الـ Agents للإشارة إلى نموذج AI محدد.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'provider', 'model_name',
        'is_active', 'status', 'config',
        'routing_tag', 'workspace_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config'    => 'array',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

---

## 4. المتحكمات (Controllers)

### 📄 `app/Http/Controllers/AiProviderController.php`
**الحجم:** 14,935 bytes | **الأسطر:** 396

**الغرض:** CRUD كامل لمزودي AI. نقطة الدخول لإدارة المزودين من الـ API.

**Endpoints التي يتحكم فيها:**
| Method | Route | الوصف |
|--------|-------|-------|
| `GET` | `/api/v1/ai/providers` | قائمة كل المزودين |
| `POST` | `/api/v1/ai/providers` | تسجيل مزود جديد |
| `GET` | `/api/v1/ai/providers/{id}` | تفاصيل مزود |
| `PUT` | `/api/v1/ai/providers/{id}` | تحديث مزود |
| `DELETE` | `/api/v1/ai/providers/{id}` | حذف مزود وكل مفاتيحه ونماذجه |
| `POST` | `/api/v1/ai/providers/{id}/sync-models` | مزامنة النماذج من API المزود |
| `POST` | `/api/v1/ai/providers/{id}/test` | اختبار الاتصال |
| `PATCH` | `/api/v1/ai/providers/{id}/toggle-active` | تفعيل/تعطيل |

**الـ Dependencies:**
- `DynamicProviderRegistry` — للتسجيل والبحث
- `EncryptedApiKeyStorage` — لتخزين المفتاح المشفر
- `DynamicRestProvider` — لجلب النماذج من API المزود

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiModelsHub\DynamicProviderRegistry;
use App\Services\AiModelsHub\DynamicRestProvider;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;

class AiProviderController extends Controller
{
    public function __construct(
        DynamicProviderRegistry $providerRegistry,
        EncryptedApiKeyStorage $keyStorage
    ) { /* ... */ }

    // Store a new provider + save encrypted key + sync models
    public function store(Request $request) { /* ... */ }

    // Sync models from provider's /models API endpoint
    public function syncModels(Request $request, $id) { /* ... */ }

    // Test connectivity via health check
    public function test(Request $request, $id) { /* ... */ }

    // Toggle is_active status
    public function toggleActive(Request $request, $id) { /* ... */ }
}
```

---

### 📄 `app/Http/Controllers/AiModelController.php`
**الحجم:** 7,722 bytes | **الأسطر:** 231

**الغرض:** CRUD لنماذج AI الفردية مع إمكانية اختبار كل نموذج.

**Endpoints:**
| Method | Route | الوصف |
|--------|-------|-------|
| `GET` | `/api/v1/ai/models` | قائمة النماذج (فلترة بـ provider_id/search) |
| `POST` | `/api/v1/ai/models` | إضافة نموذج يدوياً |
| `GET` | `/api/v1/ai/models/{id}` | تفاصيل نموذج |
| `PUT` | `/api/v1/ai/models/{id}` | تحديث نموذج |
| `DELETE` | `/api/v1/ai/models/{id}` | حذف نموذج |
| `POST` | `/api/v1/ai/models/{id}/test` | إرسال prompt اختباري |

```php
<?php

namespace App\Http\Controllers;

use App\Models\AIModel;
use App\Jobs\ExecuteAiModelJob;
use App\Services\LogService;
use App\Services\AI\ModelSelector;
use App\Services\AiModelsHub\DynamicRestProvider;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;

class AiModelController extends Controller
{
    public function __construct(
        protected LogService $logService,
        protected ModelSelector $modelSelector,
        // ... other services
    ) {}

    // Test a specific model with a prompt
    public function test(Request $request, $id)
    {
        $model = AIModel::findOrFail($id);
        $provider = $model->provider;
        $providerInstance = $this->resolveProvider($provider->id, $apiKey);
        $result = $providerInstance->execute($testRequest);
        return response()->json($result);
    }
}
```

---

### 📄 `app/Http/Controllers/AiRequestController.php`
**الحجم:** 19,918 bytes | **الأسطر:** 478

**الغرض:** البوابة الأساسية لمعالجة طلبات AI. يطبق كامل pipeline الـ Intent → Route → Execute → Track.

**Endpoints:**
| Method | Route | الوصف |
|--------|-------|-------|
| `POST` | `/api/v1/ai/request` | تنفيذ طلب AI بالـ intent |
| `GET` | `/api/v1/ai/routing-matrix` | عرض جدول التوجيه الكامل |
| `POST` | `/api/v1/ai/route-intent` | تحديث قاعدة توجيه intent |

**Pipeline التنفيذ:**
```
1. Resolve intent → IntentRoutingEngine
2. Get provider config → DynamicProviderRegistry
3. Decrypt API key → EncryptedApiKeyStorage
4. Adapt payload → PayloadAdapterFactory (OpenAI/Anthropic/Gemini format)
5. Execute with fallback → CircuitBreaker
6. Normalize response → PayloadAdapterFactory
7. Track usage → UsageTracker
```

```php
<?php

namespace App\Http\Controllers;

use App\Services\AiModelsHub\IntentRoutingEngine;
use App\Services\AiModelsHub\DynamicProviderRegistry;
use App\Services\AiModelsHub\PayloadAdapterFactory;
use App\Services\AiModelsHub\EncryptedApiKeyStorage;
use App\Services\AiModelsHub\CircuitBreaker;
use App\Services\AiModelsHub\UsageTracker;

class AiRequestController extends Controller
{
    public function handleRequest(Request $request): JsonResponse
    {
        $routing = $this->intentRoutingEngine->resolveIntent($request->intent_name);
        $provider = $this->providerRegistry->getProvider($routing['default_provider_id']);
        $apiKey = $this->encryptedKeyStorage->getDecryptedKey($provider->id);
        $adaptedRequest = $this->payloadAdapterFactory->adaptPayload($provider->payload_format, [...]);
        $result = $this->circuitBreaker->executeWithFallback($primary, $fallbacks);
        $this->usageTracker->trackUsage(...);
        return response()->json($result);
    }
}
```

---

### 📄 `app/Http/Controllers/AiRouteController.php`
**الحجم:** 16,482 bytes | **الأسطر:** 415

**الغرض:** الـ Controller الأكثر تطوراً. يضيف Semantic Caching وملفات التعريف (profiles) وسجل المراجعة الكامل فوق الوظائف الأساسية.

**Endpoints:**
| Method | Route | الوصف |
|--------|-------|-------|
| `POST` | `/api/v1/ai-hub/route` | تنفيذ طلب مع caching + profiles + audit |
| `GET` | `/api/v1/ai-hub/provider-health` | بطاقة صحة المزودين |
| `GET` | `/api/v1/ai-hub/audit-trail` | سجل المراجعة مع فلترة |
| `GET` | `/api/v1/ai-hub/telemetry` | لوحة التليميتري (24h stats) |

**الإضافات على AiRequestController:**
- 📦 **SemanticCache** — تحقق من cache أولاً قبل إرسال طلب للـ API
- 🏷️ **Routing Profiles** — `cost_profile`, `latency_profile`, `security_class`, `language`
- 📝 **AuditTrail** — تسجيل كل حدث في `ai_audit_trails`

```php
<?php

// Route with semantic caching + profiles
public function route(Request $request)
{
    // 1. Check semantic cache first
    $cached = $this->semanticCache->get($intent, $prompt, $parameters);
    if ($cached) return cachedResponse($cached);

    // 2. Resolve intent with profiles (cost, latency, security, language)
    $routing = $this->intentRoutingEngine->resolveIntentWithProfiles($intent, $profiles);

    // 3. Execute with circuit breaker + fallbacks
    $result = $this->circuitBreaker->executeWithFallback($primary, $fallbacks);

    // 4. Track usage + store in cache + record audit trail
    $this->usageTracker->trackUsage(...);
    $this->semanticCache->put(...);
    $this->recordAudit('route_executed', ...);
}
```

---

### 📄 `app/Http/Controllers/AiCostAnalyticsController.php`
**الحجم:** 3,347 bytes | **الأسطر:** 104

**الغرض:** إدارة ميزانية الـ AI والتنبؤ بالتكاليف.

**Endpoints:**
| Method | Route | الوصف |
|--------|-------|-------|
| `GET` | `/api/v1/ai/cost/forecast` | التنبؤ بالتكلفة للشهر الحالي |
| `POST` | `/api/v1/ai/cost/budget` | تعيين حد ميزانية شهري |

```php
<?php

class AiCostAnalyticsController extends Controller
{
    // Returns: current_spend, monthly_limit, forecasted_total, daily_average, status
    public function forecast(Request $request): JsonResponse
    {
        $dailyAverage = $currentSpend / $currentDay;
        $forecastedTotal = $dailyAverage * $daysInMonth;
        $status = $forecastedTotal > $monthlyLimit ? 'over_budget_predicted' : 'healthy';
        // ...
    }

    // Upserts cost_budgets table
    public function setBudget(Request $request): JsonResponse { /* ... */ }
}
```

---

## 5. طبقة الخدمات (Services)

### 📄 `app/Services/AiModelsHub/AiProviderInterface.php`
**الحجم:** 1,143 bytes | **الأسطر:** 51

**الغرض:** العقد الرئيسي (Contract/Interface) الذي يجب أن يلتزم به أي مزود AI.

```php
<?php

namespace App\Services\AiModelsHub;

interface AiProviderInterface
{
    public function getProviderName(): string;
    public function getAvailableModels(): array;
    public function getDefaultModel(): string;
    public function generateText(string $prompt, array $options = []): array;
    public function generateEmbeddings(string $text, array $options = []): array;
    public function validateRequest(array $request): array;
    public function estimateCost(string $model, int $inputTokens, int $outputTokens = 0): float;
    public function getHealthStatus(): array;
    public function getRateLimitStatus(): array;
}
```

---

### 📄 `app/Services/AiModelsHub/DynamicRestProvider.php`
**الحجم:** 11,022 bytes | **الأسطر:** 305

**الغرض:** التنفيذ الأساسي للـ `AiProviderInterface`. يقرأ إعدادات المزود من قاعدة البيانات ويُنفذ الطلبات على أي API متوافق. هذا هو "المحول العالمي" الذي يتيح إضافة أي مزود جديد بدون كود PHP جديد.

**المميزات:**
- يدعم أي `auth_header_format` (Bearer, x-api-key, إلخ)
- يُطبّع استجابات نماذج OpenAI/Anthropic/Ollama تلقائياً
- يدعم `generateText` و `generateEmbeddings`
- يُرجع `healthStatus` بالـ latency وكود HTTP

```php
<?php

class DynamicRestProvider implements AiProviderInterface
{
    public function __construct(string $providerId, EncryptedApiKeyStorage $keyStorage)
    {
        $this->providerRecord = DB::table('ai_providers')->where('id', $providerId)->first();
    }

    // Reads base_url + auth_header_format from DB → builds headers dynamically
    protected function buildHeaders(): array { /* ... */ }

    // Fetches models list from models_fetch_endpoint, normalizes OpenAI/Anthropic/Ollama formats
    public function getAvailableModels(): array { /* ... */ }

    // Sends text generation request to generate_endpoint
    public function generateText(string $prompt, array $options = []): array { /* ... */ }

    // Returns: status (healthy/unhealthy/offline/no_key), latency, http_status
    public function getHealthStatus(): array { /* ... */ }
}
```

---

### 📄 `app/Services/AiModelsHub/UniversalAiGatewayService.php`
**الحجم:** 4,629 bytes | **الأسطر:** 132

**الغرض:** نقطة الدخول الموحدة لتنفيذ طلبات LLM للـ Agents تحديداً. يحل النموذج المناسب بأولوية: Agent Override → Gemini → IntentRouting → أي نموذج نشط.

**ترتيب الأولوية في resolveModel:**
```
1. Agent settings['ai_model_id']  ← Agent-specific override
2. Gemini provider (by name)       ← Platform default
3. IntentRouting 'agent_execution' ← DB routing config
4. First active model              ← Last resort
```

```php
<?php

class UniversalAiGatewayService
{
    // Execute agent prompt using the best available model
    public function executeWithAgent(Agent $agent, array $context): array
    {
        $model = $this->resolveModel($agent);
        $provider = new DynamicRestProvider($model->provider->id, $this->keyStorage);
        $result = $provider->generateText($prompt, $options);
        return $result;
    }

    // Generate embeddings for vector search
    public function generateEmbeddings(string $text, ?Agent $agent = null): array
    {
        $provider = new DynamicRestProvider(...);
        return $provider->generateEmbeddings($text, ['model' => 'text-embedding-3-small']);
    }
}
```

---

### 📄 `app/Services/AiModelsHub/DynamicProviderRegistry.php`
**الحجم:** 11,556 bytes | **الأسطر:** 340

**الغرض:** سجل ديناميكي لكل مزودي AI. يُدار من خلاله CRUD المزودين مع دعم Cache أوتوماتيكي.

```php
<?php

class DynamicProviderRegistry
{
    // Get provider with API key attached as PHP property (not Eloquent attribute)
    public function getProvider($providerId): ?AIProvider
    {
        return $this->cacheManager->cacheProvider($providerId, function() use ($providerId) {
            $provider = AIProvider::find($providerId);
            $provider->resolved_api_key = $this->keyStorage->getDecryptedKey($providerId);
            return $provider;
        });
    }

    // Register new provider
    public function registerProvider(array $data, ?string $apiKey = null): AIProvider { /* ... */ }

    // Sync models: fetches from provider API → upserts to ai_models table
    public function syncModels($providerId): array { /* ... */ }

    // Normalizes OpenAI { data:[] } / Anthropic { models:[] } / direct array formats
    protected function normalizeModelsResponse($data): array { /* ... */ }
}
```

---

### 📄 `app/Services/AiModelsHub/IntentRoutingEngine.php`
**الحجم:** 6,365 bytes | **الأسطر:** 210

**الغرض:** محرك توجيه الـ Intents. يحول اسم الـ Intent إلى مزود ونموذج محدد، مع دعم الـ Profiles (cost/latency/security/language).

```php
<?php

class IntentRoutingEngine
{
    // Simple: intent name → { provider, model, fallbacks }
    public function resolveIntent($intentName): ?IntentRouting
    {
        return $this->cacheManager->cacheIntentRouting("intent:{$intentName}", function() use ($intentName) {
            return IntentRouting::with(['defaultProvider', 'defaultModel', ...])->where('intent_name', $intentName)->first();
        });
    }

    // Advanced: intent + profiles → best matching model
    public function resolveIntentWithProfiles($intentName, array $profiles): ?array
    {
        $query = AIModel::with('provider')
            ->whereHas('provider', fn($q) => $q->where('is_active', true))
            ->where('status', 'active');

        if ($profiles['cost_profile']) $query->where('cost_profile', $profiles['cost_profile']);
        if ($profiles['language']) $query->whereJsonContains('language_support', $profiles['language']);

        $primaryModel = $query->first();
        return ['primary' => ['provider' => $primaryModel->provider, 'model' => $primaryModel], 'fallbacks' => [...] ];
    }
}
```

---

### 📄 `app/Services/AiModelsHub/EncryptedApiKeyStorage.php`
**الحجم:** 2,945 bytes | **الأسطر:** 113

**الغرض:** إدارة آمنة لمفاتيح API. يشفر المفاتيح بـ `AES-256-CBC` قبل التخزين ويفكها عند الاسترجاع.

**العمليات:**
| الدالة | الوصف |
|--------|-------|
| `storeKey()` | تشفير وحفظ مفتاح جديد |
| `getDecryptedKey()` | استرجاع وفك تشفير المفتاح |
| `hasKey()` | التحقق من وجود مفتاح |
| `updateKey()` | تحديث مفتاح موجود |
| `deactivateKey()` | تعطيل مفتاح |

```php
<?php

class EncryptedApiKeyStorage
{
    public function storeKey($providerId, $key, $name = null): AIApiKey
    {
        $encryptedKey = Crypt::encryptString($key); // AES-256-CBC
        return AIApiKey::create(['key_hash' => $encryptedKey, ...]);
    }

    public function getDecryptedKey($providerId): ?string
    {
        $apiKey = AIApiKey::where('provider_id', $providerId)->where('is_active', true)->first();
        return Crypt::decryptString($apiKey->key_hash);
    }
}
```

---

### 📄 `app/Services/AiModelsHub/CircuitBreaker.php`
**الحجم:** 5,546 bytes | **الأسطر:** 164

**الغرض:** تنفيذ نمط Circuit Breaker لحماية النظام من تعطل مزود AI معين. يستخدم Redis للحالة.

**حالات الدائرة:**
```
closed  →  يعمل بشكل طبيعي
open    →  مُغلق بسبب فشل متكرر، يحول لـ Fallback
half-open → يجرب الاتصال مجدداً بعد انتهاء timeout
```

```php
<?php

class CircuitBreaker
{
    protected $failureThreshold = 5;   // Open circuit after 5 failures
    protected $recoveryTimeout = 60;   // Retry after 60 seconds

    public function executeWithFallback(callable $primaryCallback, array $fallbackCallbacks = []): array
    {
        try {
            $result = $primaryCallback();
            $result['fallback_triggered'] = false;
            return $result;
        } catch (Exception $e) {
            // Try each fallback in sequence
            foreach ($fallbackCallbacks as $fallback) {
                try {
                    $result = $fallback();
                    $result['fallback_triggered'] = true;
                    return $result;
                } catch (Exception $fe) { continue; }
            }
            return ['success' => false, 'errors' => [...], 'message' => 'All providers failed.'];
        }
    }
}
```

---

### 📄 `app/Services/AiModelsHub/PayloadAdapterFactory.php`
**الحجم:** 6,812 bytes | **الأسطر:** 214

**الغرض:** مصنع محولات الـ Payload. يحول الطلب العام إلى التنسيق الخاص بكل مزود، ويحول الاستجابة مجدداً إلى تنسيق موحد.

**تنسيقات الـ Request المدعومة:**
| المزود | التنسيق |
|--------|---------|
| OpenAI / Groq | `{ model, messages: [{role, content}], temperature, max_tokens }` |
| Anthropic | `{ model, max_tokens, messages, system }` |
| Google Gemini | `{ model, contents: [{parts: [{text}]}], generationConfig }` |

```php
<?php

class PayloadAdapterFactory
{
    public function adaptPayload($format, array $data): array
    {
        return match($format) {
            'openai'   => $this->adaptForOpenAI($data),
            'anthropic' => $this->adaptForAnthropic($data),
            'google', 'gemini' => $this->adaptForGoogle($data),
            'groq'     => $this->adaptForGroq($data),
            default    => $this->adaptForOpenAI($data), // Default
        };
    }

    // Normalizes all provider responses to: { content, usage: {input_tokens, output_tokens} }
    public function adaptResponse($format, $response): array { /* ... */ }
}
```

---

### 📄 `app/Services/AiModelsHub/UsageTracker.php`
**الحجم:** 5,116 bytes | **الأسطر:** 166

**الغرض:** تتبع استخدام AI وحساب التكاليف الفعلية وتحديث الميزانية تلقائياً.

```php
<?php

class UsageTracker
{
    public function trackUsage($providerId, $modelId, $inputTokens, $outputTokens, $workspaceId = null): void
    {
        $model = AIModel::find($modelId);
        $inputCost = ($inputTokens / 1_000_000) * $model->input_cost_per_m;
        $outputCost = ($outputTokens / 1_000_000) * $model->output_cost_per_m;
        $totalCost = $inputCost + $outputCost;

        UsageLog::create([...]);

        // Auto-decrement workspace and global budget
        DB::table('cost_budgets')->where('workspace_id', $workspaceId)->increment('current_spend', $totalCost);
    }

    public function checkBudget($workspaceId, $estimatedCost): bool { /* ... */ }
    public function getProviderTotalCost($providerId, $start, $end): float { /* ... */ }
}
```

---

### 📄 `app/Services/AiModelsHub/ProviderHealthMonitor.php`
**الحجم:** 3,035 bytes | **الأسطر:** 90

**الغرض:** مراقبة صحة مزودي AI عبر polling دوري. يحفظ نتائج الـ latency والـ status في جدول `provider_health_metrics`.

**حالات الصحة:** `healthy` (< 2000ms) | `degraded` (> 2000ms أو 429) | `offline` (connection failed)

```php
<?php

class ProviderHealthMonitor
{
    public function pollAllProviders(): void
    {
        AIProvider::where('is_active', true)->get()->each(fn($p) => $this->pollProvider($p));
    }

    public function pollProvider(AIProvider $provider): array
    {
        $response = Http::timeout(10)->get($provider->base_url . '/' . $provider->models_fetch_endpoint);
        $status = $response->successful() ? ($latencyMs > 2000 ? 'degraded' : 'healthy') : 'offline';
        DB::table('provider_health_metrics')->insert(['provider_id' => $provider->id, 'status' => $status, 'latency_ms' => $latencyMs, ...]);
        return ['status' => $status, 'latency_ms' => $latencyMs];
    }

    public function getScorecard(): array
    {
        return DB::table('provider_health_metrics')
            ->select('provider_id', 'status', DB::raw('AVG(latency_ms) as avg_latency'))
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->groupBy('provider_id', 'status')
            ->get()->keyBy('provider_id')->toArray();
    }
}
```

---

### 📄 `app/Services/AiModelsHub/SemanticCache.php`
**الحجم:** 2,142 bytes | **الأسطر:** 75

**الغرض:** تخزين استجابات AI مؤقتاً بناءً على intent + prompt + parameters لتجنب طلبات API متكررة وتوفير التكلفة.

**آلية الـ Key:** `md5(json_encode({intent, prompt, parameters}))` → `semantic_cache:{intent}:{hash}`

```php
<?php

class SemanticCache
{
    const CACHE_TTL = 3600; // 1 hour

    public function get(string $intent, string $prompt, array $parameters = []): ?array
    {
        $key = $this->buildCacheKey($intent, $prompt, $parameters);
        return Cache::get($key); // Returns null on MISS
    }

    public function put(string $intent, string $prompt, array $parameters, array $result, int $ttl = null): void
    {
        Cache::put($this->buildCacheKey($intent, $prompt, $parameters), $result, $ttl ?? self::CACHE_TTL);
    }

    protected function buildCacheKey(string $intent, string $prompt, array $parameters): string
    {
        ksort($parameters); // Normalize for determinism
        return "semantic_cache:{$intent}:" . md5(json_encode(compact('intent', 'prompt', 'parameters')));
    }
}
```

---

### 📄 `app/Services/AiModelsHub/CacheManager.php`
**الحجم:** 3,154 bytes | **الأسطر:** 111

**الغرض:** مدير مركزي للـ Cache في الـ Hub. يوفر TTL ثوابت ودوال invalidation موحدة.

**TTL الثوابت:**
| النوع | المدة |
|-------|-------|
| Provider Cache | 3600 ثانية (1 ساعة) |
| Intent Routing Cache | 1800 ثانية (30 دقيقة) |
| Models Cache | 3600 ثانية (1 ساعة) |

```php
<?php

class CacheManager
{
    const PROVIDER_TTL = 3600;
    const INTENT_TTL   = 1800;
    const MODELS_TTL   = 3600;

    public function cacheProvider(string $cacheKey, callable $callback, int $ttl = null): mixed
    {
        return Cache::remember("ai_provider:{$cacheKey}", $ttl ?? self::PROVIDER_TTL, $callback);
    }

    public function cacheIntentRouting(string $intentName, callable $callback, int $ttl = null): mixed
    {
        return Cache::remember("intent:{$intentName}", $ttl ?? self::INTENT_TTL, $callback);
    }

    public function invalidateProvider(string $providerId): void
    {
        Cache::forget("ai_provider:{$providerId}");
    }
}
```

---

### 📄 `app/Services/AiModelsHub/UsageCalculator.php`
**الحجم:** 2,254 bytes | **الأسطر:** 77

**الغرض:** حاسبة التكلفة الثابتة (Static). تحسب التكلفة بناءً على عدد التوكنز وسعر النموذج.

```php
<?php

class UsageCalculator
{
    public static function calculateCost(string $modelId, int $inputTokens, int $outputTokens = 0): float
    {
        $aiModel = AIModel::find($modelId);
        $inputCost  = ($inputTokens / 1000)  * ($aiModel->input_cost_per_m ?? 0);
        $outputCost = ($outputTokens / 1000) * ($aiModel->output_cost_per_m ?? 0);
        return round($inputCost + $outputCost, 6);
    }

    public static function formatUsage(array $usage, string $modelId): array
    {
        return [
            'input_tokens' => $usage['input_tokens'] ?? 0,
            'output_tokens' => $usage['output_tokens'] ?? 0,
            'total_tokens'  => $usage['total_tokens'] ?? 0,
            'cost' => self::calculateCost($modelId, ...),
        ];
    }
}
```

---

## 6. الوظائف الخلفية (Jobs)

### 📄 `app/Jobs/ProcessAiInferenceJob.php`
**الحجم:** 9,592 bytes | **الأسطر:** 286

**الغرض:** وظيفة AI الاستدلالية الرئيسية في الـ Queue. تُنفذ طلبات LLM بشكل غير متزامن وتبث التوكنز Token-by-Token عبر Laravel Reverb (WebSockets).

**إعدادات الـ Queue:**
| الإعداد | القيمة |
|---------|--------|
| `$queue` | `llm-inference` |
| `$timeout` | 600 ثانية (10 دقائق) |
| `$tries` | 3 محاولات |

**سير التنفيذ:**
```
1. Check idempotency (prevent duplicates)
2. Load Conversation + Message + AIModel from DB
3. Get API key for provider
4. Check rate limits
5. Execute inference via provider
6. Stream tokens via TokenStreamed event (WebSockets)
7. Update message with response content
8. Broadcast MessageCompleted event
9. Mark as processed (idempotency)
```

```php
<?php

class ProcessAiInferenceJob extends BaseJob
{
    public $queue = 'llm-inference';
    public int $timeout = 600;
    public int $tries = 3;

    public function handle(): void
    {
        $result = $provider->execute($execRequest);
        $tokens = preg_split('/(\s+)/', $result['output']);

        foreach ($tokens as $token) {
            event(new TokenStreamed($this->conversationId, $this->messageId, $token . ' '));
            usleep(10000); // 10ms streaming delay
        }

        $responseMessage->update(['content' => $responseContent, 'status' => 'completed']);
        event(new MessageCompleted($this->conversationId, $this->messageId, $responseContent));
    }
}
```

---

### 📄 `app/Jobs/ExecuteAiModelJob.php`
**الحجم:** 4,008 bytes | **الأسطر:** 130

**الغرض:** وظيفة تنفيذ نموذج AI عامة (من واجهة المستخدم مثلاً). تُطلق حدث `AiModelExecutionCompleted` عند الانتهاء.

```php
<?php

class ExecuteAiModelJob extends BaseJob
{
    public $queue = 'llm-inference';
    public int $timeout = 600;
    public int $tries = 3;

    public function handle(): void
    {
        $provider = $this->resolveProvider($this->provider, $apiKey);
        $result = $provider->execute($request);

        event(new AiModelExecutionCompleted($this->userId, $this->executionId, $payload));
        $this->markAsProcessed($payload);
    }

    // Supports: google_gemini, openai, anthropic, groq
    protected function resolveProvider(string $provider, string $apiKey): object
    {
        return match ($provider) {
            'google_gemini' => new GoogleGeminiProvider($apiKey),
            'openai'        => new OpenAIProvider($apiKey),
            'anthropic'     => new AnthropicProvider($apiKey),
            'groq'          => new GroqProvider($apiKey),
            default         => throw new Exception("Unknown provider: {$provider}"),
        };
    }
}
```

---

## 7. قواعد البيانات (Migrations)

### 📄 `database/migrations/2026_05_19_000001_create_ai_providers_table.php`

**الجدول:** `ai_providers`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | UUID (PK) | معرف فريد |
| `name` | string | اسم المزود (OpenAI, Gemini...) |
| `base_url` | string | رابط API الأساسي |
| `models_fetch_endpoint` | string? | نقطة نهاية جلب النماذج |
| `generate_endpoint` | string? | نقطة نهاية التوليد |
| `auth_header_format` | string? | صيغة header المصادقة |
| `payload_format` | string? | صيغة الـ payload (openai/anthropic/gemini) |
| `is_active` | boolean | هل المزود نشط |
| `timestamps` | - | created_at, updated_at |

```php
Schema::create('ai_providers', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('base_url');
    $table->string('models_fetch_endpoint')->nullable();
    $table->string('generate_endpoint')->nullable();
    $table->string('auth_header_format')->nullable();
    $table->string('payload_format')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index(['name']);
    $table->index(['is_active']);
});
```

---

### 📄 `database/migrations/2026_05_19_000002_update_ai_models_table.php`

**الجدول:** `ai_models` (تحديث)

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | UUID (PK) | معرف فريد |
| `name` | string | اسم النموذج (gpt-4o, gemini-1.5-pro...) |
| `provider_id` | UUID (FK) | → `ai_providers.id` |
| `context_window` | integer? | حجم النافذة السياقية (tokens) |
| `input_cost_per_m` | decimal(10,6)? | تكلفة المليون token للإدخال |
| `output_cost_per_m` | decimal(10,6)? | تكلفة المليون token للإخراج |
| `last_synced_at` | timestamp? | آخر مزامنة مع API المزود |

---

### 📄 `database/migrations/2026_05_19_000003_create_ai_api_keys_table.php`

**الجدول:** `ai_api_keys`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | UUID (PK) | معرف فريد |
| `provider_id` | UUID (FK) | → `ai_providers.id` (cascade delete) |
| `key_hash` | string | المفتاح مشفر AES-256 |
| `name` | string | وصف للمفتاح |
| `is_active` | boolean | هل المفتاح نشط |

```php
Schema::create('ai_api_keys', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('provider_id');
    $table->string('key_hash'); // Encrypted via Crypt::encryptString()
    $table->string('name');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('provider_id')->references('id')->on('ai_providers')->onDelete('cascade');
});
```

---

### 📄 `database/migrations/2026_05_19_000004_create_intent_routing_table.php`

**الجدول:** `intent_routing`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | UUID (PK) | معرف فريد |
| `intent_name` | string (unique) | اسم الـ Intent (e.g., `general_chat`) |
| `default_provider_id` | UUID (FK)? | المزود الافتراضي |
| `default_model_id` | UUID (FK)? | النموذج الافتراضي |
| `fallback_provider_id` | UUID (FK)? | مزود الـ Fallback |
| `fallback_model_id` | UUID (FK)? | نموذج الـ Fallback |

**Intents الأساسية في النظام:**
`general_chat`, `data_extraction`, `summarization`, `embedding`, `fast_response`, `reasoning`, `contact_extraction`, `intent_classification`, `agent_execution`

---

### 📄 `database/migrations/2026_05_19_000005_create_usage_logs_table.php`

**الجدول:** `usage_logs`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `provider_id` | UUID (FK)? | المزود المستخدم |
| `model_id` | UUID (FK)? | النموذج المستخدم |
| `intent_name` | string? | الـ Intent |
| `input_tokens` | integer | عدد توكنز الإدخال |
| `output_tokens` | integer | عدد توكنز الإخراج |
| `input_cost` | decimal(14,6) | تكلفة الإدخال |
| `output_cost` | decimal(14,6) | تكلفة الإخراج |
| `total_cost` | decimal(14,6) | التكلفة الإجمالية |
| `timestamp` | timestamp | وقت الطلب |

---

### 📄 `database/migrations/2026_05_27_090608_create_ai_audit_trails_table.php`

**الجدول:** `ai_audit_trails`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `event_type` | string | نوع الحدث |
| `provider_id` | UUID? | المزود |
| `model_id` | UUID? | النموذج |
| `intent` | string? | الـ Intent |
| `status` | string | success/failed/fallback |
| `latency_ms` | integer? | زمن الاستجابة |
| `fallback_triggered` | boolean | هل تم تفعيل الـ Fallback |
| `estimated_cost` | decimal(12,6)? | التكلفة المقدرة |
| `input_tokens` | integer? | توكنز الإدخال |
| `output_tokens` | integer? | توكنز الإخراج |
| `error_type` | string? | نوع الخطأ |
| `error_message` | text? | رسالة الخطأ |
| `workspace_id` | UUID? | الـ Workspace |
| `user_id` | UUID? | المستخدم |
| `metadata` | JSON? | بيانات إضافية (cache_hit, profiles...) |

---

## 8. واجهة المستخدم (Frontend)

### 📄 `resources/views/hubs/models.blade.php`
**الحجم:** 8,493 bytes | **الأسطر:** 168

**الغرض:** واجهة Blade الرئيسية لعرض وإدارة مزودي AI من خلال المتصفح. تعرض قائمة المزودين، اختبار الاتصال (Ping)، ورسم بياني للـ Latency.

**المكونات المرئية:**
- **Provider List Card**: قائمة المزودين مع Toggle تفعيل/تعطيل وزر Ping
- **Latency Chart (Chart.js)**: رسم بياني شريطي للـ Average Latency
- **Model Distribution**: نسبة استخدام كل نموذج

**الـ JavaScript المدمج:**
```javascript
// Toggle Provider Active Status
$('.form-check-input[role="switch"]').change(function() {
    const providerId = $(this).closest('.list-group-item').data('id');
    $.ajax({ url: `/hub/models/${providerId}/toggle`, method: 'POST', ... });
});

// Ping Provider (simulated with random latency)
$('.btn-ping').click(function() {
    btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Pinging...');
    setTimeout(() => {
        btn.html(`<i class="fa-solid fa-check"></i> ${Math.floor(Math.random() * 100 + 50)}ms`);
    }, 1000);
});

// Chart.js Latency Bar Chart
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Gemini 1.5 Pro', 'Gemini Flash', 'Claude 3 Opus'],
        datasets: [{ label: 'Avg Latency (ms)', data: [850, 320, 1200], ... }]
    }
});
```

```html
<!-- Provider Card Structure -->
<div class="list-group-item bg-dark" data-id="{{ $provider->id }}">
    <div>{{ $provider->name }} API</div>
    <input type="checkbox" role="switch" {{ $provider->is_active ? 'checked' : '' }}>
    <input type="text" value="{{ $provider->api_base_url }}">
    <!-- Health Progress Bar -->
    <div class="progress"><div class="progress-bar bg-success" style="width: 98%"></div></div>
    <button class="btn-ping" data-provider="{{ $provider->name }}">Ping</button>
</div>
```

> **ملاحظة:** الواجهة الحالية تستخدم بيانات Hardcoded للـ Latency Chart وـ Ping. التكامل الكامل مع الـ API يحتاج إلى تطوير.

---

## 📊 ملخص الملفات

| التصنيف | عدد الملفات | الحجم الإجمالي |
|---------|-------------|----------------|
| Documentation | 2 | ~8.7 KB |
| Models | 6 | ~5.4 KB |
| Controllers | 5 | ~62 KB |
| Services (AiModelsHub) | 10 | ~58 KB |
| Jobs | 2 | ~13.6 KB |
| Migrations | 5 | ~8.3 KB |
| Frontend (Blade) | 1 | ~8.5 KB |
| **المجموع** | **31 ملف** | **~164.5 KB** |

---

## 🔗 خريطة الاعتماديات (Dependency Map)

```
AiRouteController
  ├── IntentRoutingEngine ──→ CacheManager ──→ Redis
  │     └── IntentRouting (Model)
  ├── DynamicProviderRegistry ──→ CacheManager
  │     └── AIProvider (Model)
  ├── EncryptedApiKeyStorage
  │     └── AIApiKey (Model) ──→ Crypt (AES-256)
  ├── CircuitBreaker ──→ Redis Cache
  ├── PayloadAdapterFactory
  ├── UsageTracker
  │     ├── UsageLog (Model)
  │     └── cost_budgets Table
  └── ProviderHealthMonitor ──→ provider_health_metrics Table

DynamicRestProvider (implements AiProviderInterface)
  ├── EncryptedApiKeyStorage
  └── ai_providers Table (raw DB query)

UniversalAiGatewayService
  ├── DynamicRestProvider
  └── IntentRouting (Model)
```

---

*توثيق تم إنشاؤه بواسطة Antigravity AI Assistant | Nexus v3 Project*
