# 🤖 AI Models Hub — Enterprise UI/UX Complete Specification
## المواصفات الشاملة والمفصلة لكل قسم

> **المرجعية:** Langfuse، Portkey، Kong AI Gateway، LiteLLM، Agenta، OpenAI Platform
> **المستوى:** Enterprise-Grade / Production-Ready
> **الهدف:** مواصفات دقيقة وكاملة لكل عنصر في الواجهة بما يكفي لبناء نظام احترافي متكامل

---

## 📐 هيكل التنقل العام (Global Navigation Architecture)

```
AI Models Hub
├── 📊 Dashboard (Overview)
├── 🏢 Providers
│   ├── List & Health
│   ├── Add / Edit Provider
│   └── Sync & Test
├── 🤖 Models
│   ├── Model Library
│   ├── Add / Edit Model
│   ├── Pricing Manager
│   └── Model Comparison
├── 🔑 API Keys
│   ├── All Keys (with filters)
│   ├── Add New Key
│   └── Key Analytics (per-key)
├── 🔀 Intent Routing
│   ├── Routing Matrix
│   ├── Route Rules Builder
│   ├── Fallback Chains
│   └── A/B Traffic Splits
├── 💰 Cost & Budget
│   ├── Cost Analytics
│   ├── Budget Manager
│   └── Alerts & Thresholds
├── 🧪 Playground
│   ├── Chat Tester
│   ├── Multi-Model Battle
│   ├── Prompt Registry
│   └── Job Simulator
└── 📋 Logs & Audit
    ├── Request Logs
    ├── Audit Trail
    ├── Trace Viewer
    └── Cache Inspector
```

---

## 1️⃣ لوحة القيادة الشاملة (Global Dashboard)

### 1.1 شريط الصحة اللحظي (Live Health Bar)
شريط ثابت أعلى الصفحة دائماً يعرض:
- 🟢/🟡/🔴 **System Status** — `Healthy | Degraded | Outage` (مع وقت الانتقال الأخير)
- **Active Requests** — عداد حي يتحرك (WebSocket)
- **Total Tokens/Min** — معدل الاستهلاك الحالي
- **Est. Today's Cost** — التكلفة المتراكمة لليوم
- زر **Emergency Kill Switch** — إيقاف جميع الطلبات الجديدة فوراً

### 1.2 بطاقات الملخص (Summary Cards) — صف علوي
| البطاقة | المحتوى | الرسم | التفاعل |
|---------|---------|-------|---------|
| Total Requests (24h) | العدد + نسبة التغيير عن أمس | Sparkline صغير | Click → فتح Logs مفلترة بـ 24h |
| Success Rate | النسبة المئوية (مثل 98.7%) | دائرة ملونة | Click → Error breakdown |
| Avg Latency (P50) | زمن بالمللي ثانية | Gauge | Click → Latency distribution histogram |
| Total Cost (Month) | بالدولار مع نسبة من الميزانية | Progress Bar رفيع | Click → Cost Analytics |
| Active Providers | عدد النشطة / الإجمالي | Dots مرئية | Click → Providers page |
| Cache Hit Rate | % نجاح الـ Cache | دائرة | Click → Cache Inspector |

### 1.3 الرسوم البيانية الرئيسية (Main Charts)
- **Token Consumption Timeline** (عرض كامل، 7 أيام افتراضياً):
  - خطان: Input Tokens (أزرق) + Output Tokens (برتقالي)
  - فلاتر: Hour / Day / Week / Month / Custom Range
  - Hover: عرض تفاصيل كل نقطة (cost, count, model breakdown)
- **Cost by Provider** (Stacked Bar Chart):
  - كل مزود بلون مختلف
  - Drill-down عند النقر لرؤية تفاصيل النماذج داخل المزود
- **Request Volume Heatmap** (GitHub-style calendar):
  - خلايا 7 أيام × 24 ساعة توضح كثافة الطلبات
  - ألوان: أخضر فاتح (قليل) → أخضر داكن (كثير) → أحمر (peak)
- **Routing Distribution** (Sankey Diagram):
  - يوضح تدفق الطلبات: Intent → Provider → Model
  - سمك السهم = حجم الترافيك
  - نقر على أي node → فلترة Logs

### 1.4 جدول أسرع/أبطأ النماذج (Performance Leaderboard)
جدول صغير يوضح:
- النماذج الأسرع استجابةً (Top 3)
- النماذج الأغلى تكلفةً (Top 3)
- النماذج بأعلى معدل خطأ (Top 3 للتحقيق)

### 1.5 لوحة التنبيهات النشطة (Active Alerts Panel)
يسار الصفحة أو في Sidebar قابل للطي:
- قائمة بالتنبيهات النشطة مع (Severity: Critical / Warning / Info)
- وقت التنبيه + نوعه + الإجراء الموصى به
- أزرار: `Investigate` (يفتح الـ Log المرتبط) + `Snooze` (15 دقيقة)

---

## 2️⃣ إدارة المزودين (Providers Management)

### 2.1 صفحة قائمة المزودين (Provider List)
**تخطيط:** شبكة بطاقات (Card Grid) + خيار عرض جدول (Table View)

#### البطاقة الواحدة للمزود تحتوي:
```
┌─────────────────────────────────────────┐
│ [Logo] OpenAI              ● Active  [⋮]│
│ ─────────────────────────────────────── │
│ Base URL: api.openai.com                 │
│ Models: 12 synced  │  Keys: 3 active     │
│                                          │
│ Health: ████████░░ 84ms avg (Last 1h)   │
│                                          │
│ Today:  $4.20 │ 1.2M tokens │ 340 reqs  │
│ Month:  $98.4 (62% of $160 budget)       │
│ ████████████████████░░░░ 62%            │
│                                          │
│ [Ping] [Sync Models] [Edit] [Disable]   │
└─────────────────────────────────────────┘
```

**العناصر التفصيلية في البطاقة:**
- **Status Badge:** `Active` (أخضر) | `Degraded` (أصفر) | `Unreachable` (أحمر) | `Disabled` (رمادي)
- **Health Sparkline:** رسم بياني مصغر يوضح تاريخ الـ Latency للساعة الماضية (24 نقطة)
- **Budget Progress Bar:** تلوين ذكي (أخضر < 60%، أصفر 60-80%، أحمر > 80%)
- **Quick Stats:** اليوم + الشهر (Tokens + Cost + Requests)
- **Action Buttons Row:**
  - `Ping` → Modal يظهر: HTTP Status Code، زمن الاستجابة، تفاصيل الـ Headers
  - `Sync Models` → Drawer يفتح بجانب الشاشة يعرض progress ثم قائمة النماذج المُستردة
  - `Edit` → فتح نموذج التعديل
  - `Disable` → يطلب تأكيد مع تحذير "هذا سيوقف X من الـ Intent Routes المرتبطة"

**شريط الفلترة والبحث:**
- فلتر: `Status: All / Active / Degraded / Disabled`
- فلتر: `Health: All / Healthy / Warning / Critical`
- بحث نصي: اسم المزود
- ترتيب: `By Name / By Cost / By Latency / By Request Count`

### 2.2 نموذج إضافة/تعديل المزود (Add/Edit Provider Form)
**تخطيط:** Modal واسع أو Fullscreen Drawer من اليمين

**الحقول المطلوبة:**

*القسم الأول — الهوية والاتصال:*
- `Provider Name` (مع اقتراحات شائعة: OpenAI, Anthropic, Google Gemini, Groq, Ollama)
- `Base URL` (مع زر `Test Connection` بجانبه مباشرة)
- `Models Fetch Endpoint` (مع مثال placeholder: `/v1/models`)
- `Generate Endpoint` (مع مثال: `/v1/chat/completions`)
- `Test/Health Endpoint` (اختياري)
- `Auth Header Format` (Dropdown: `Bearer {key}` / `x-api-key: {key}` / Custom)

*القسم الثاني — الـ Payload Format:*
- Dropdown اختيار: `OpenAI-Compatible / Anthropic / Google Gemini / Custom`
- عند اختيار Custom → Text Area لكتابة JSON Template المخصص

*القسم الثالث — الأمان والمفتاح:*
- `API Key` (حقل Password، يُشفر فوراً عند الحفظ)
- `Key Name/Label` (لتمييزه لاحقاً)
- `Monthly Budget Cap ($)` (اختياري)

*القسم الرابع — الإعدادات المتقدمة:*
- `Auto-Sync Models` (Toggle) + Frequency: `Daily / Weekly / Manual`
- `Circuit Breaker Threshold` (عدد الفشل المتتالي قبل إغلاق الدائرة)
- `Request Timeout (ms)` (الحد الأقصى للانتظار)
- `Max Retries` (عدد مرات إعادة المحاولة)

*قسم اختبار الاتصال (قبل الحفظ):*
```
[Test Connection] → spinner → ✅ Connected (127ms) | ❌ Failed: Connection Timeout
[Send Test Prompt] → "Hello!" → يعرض الرد مباشرة
```

### 2.3 صفحة تفاصيل المزود (Provider Detail Page)
صفحة مخصصة عند النقر على المزود تحتوي على Tabs:

**Tab 1 — Overview:**
- نفس البطاقة لكن موسعة + رسم بياني للـ Latency (7 أيام)
- Uptime Timeline: شريط مرئي أخضر/أحمر لآخر 90 يوم

**Tab 2 — Models:**
- جدول بكل النماذج التابعة لهذا المزود مع (Name, Context Window, Input Cost, Output Cost, Last Synced, Status)
- زر `Sync Now` + `Add Model Manually`

**Tab 3 — API Keys:**
- قائمة المفاتيح الخاصة بهذا المزود فقط (مع جميع تفاصيل قسم إدارة المفاتيح)

**Tab 4 — Usage Analytics:**
- رسوم بيانية مفصلة: Cost، Tokens، Request Count — مفلترة بهذا المزود فقط
- مقارنة بالأشهر السابقة

**Tab 5 — Logs:**
- سجل الطلبات الخاصة بهذا المزود مع فلاتر كاملة

---

## 3️⃣ إدارة النماذج (Models Management)

### 3.1 صفحة مكتبة النماذج (Model Library)
**تخطيط:** جدول كثيف (Data-Dense Table) مع إمكانية التبديل لـ Card View

**أعمدة الجدول:**
| العمود | التفاصيل |
|--------|---------|
| Model Name | اسم + أيقونة المزود بجانبه |
| Provider | اسم المزود كـ Badge ملون |
| Context Window | شريط مرئي صغير (مثال: `▓▓▓▓▓▓░░ 128K`) |
| Input Cost | سعر الـ M tokens (مع آخر تحديث) |
| Output Cost | سعر الـ M tokens |
| Quality Tier | Badge: `Budget / Standard / Premium` |
| Status | `Active / Inactive` Toggle مباشرة في الجدول |
| Last Synced | تاريخ نسبي (مثال: `2 hours ago`) |
| Actions | `Edit / Test / View Stats / Delete` |

**شريط الفلترة الشامل:**
- `Provider` (Multi-select dropdown)
- `Quality Tier` (Budget / Standard / Premium)
- `Context Window` (Range Slider: 4K → 2M)
- `Input Cost Range` (Range Slider بالدولار)
- `Status` (Active / Inactive / All)
- `Last Synced` (Today / This Week / Over a Week / Never)
- `Search by Name` (نص)
- `My Favorites` (النماذج المميزة بـ ⭐)

### 3.2 صفحة تفاصيل النموذج (Model Detail)
صفحة مخصصة تحتوي:

**Header:**
```
[Provider Logo] gpt-4o-mini        ● Active
OpenAI  •  Standard Tier  •  128K Context
```

**بطاقات الإحصاء الخاصة بالنموذج:**
| البطاقة | المحتوى |
|---------|---------|
| Total Cost (Month) | $45.2 مع trend arrow |
| Total Requests (Month) | 8,432 |
| Avg Latency | 820ms P50 / 1,340ms P95 |
| Avg Input Tokens | 342 per request |
| Avg Output Tokens | 812 per request |
| Monthly Budget Cap | Progress Bar: $45.2 / $80 (56%) |

**رسوم بيانية تفصيلية:**
- Cost per Day (Bar Chart, 30 يوم)
- Avg Latency per Day (Line Chart)
- Token Distribution: Histogram يوضح توزيع أطوال الردود

**Context Window Visualizer:**
```
Model Limit:     ████████████████████████████████████ 128,000 tokens
Avg. Usage:      ███░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   8,432 tokens (6.5%)
Max. Seen:       ████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░  32,100 tokens (25%)
```

**جدول السعر التاريخي (Pricing History):**
| التاريخ | Input (per 1M) | Output (per 1M) | ملاحظة |
|---------|----------------|-----------------|--------|
| 2026-07-01 | $0.15 | $0.60 | Current |
| 2026-03-15 | $0.20 | $0.80 | Price drop |
| 2025-11-01 | $0.30 | $1.20 | Launch price |

**قسم الـ Intent Routes المرتبطة:**
- قائمة بكل الـ Intent Routes التي تستخدم هذا النموذج (Primary أو Fallback)
- رابط مباشر للذهاب لكل Route

**بطاقة الإعدادات المتقدمة (Settings):**
- Monthly Cap ($): حقل قابل للتعديل inline
- Tags: إضافة/حذف tags (Fast, Reasoning, Coding, etc.)
- Default Parameters: Temperature, Max Tokens, Top-P للاستخدام الافتراضي
- Deprecation Date: تاريخ التقادم المتوقع إن وجد

---

## 4️⃣ إدارة مفاتيح الـ API (Advanced API Key Management)

### 4.1 صفحة كل المفاتيح (All Keys)
**تخطيط:** جدول كامل مع لوحة إحصائية مصغرة (Mini Summary Bar) أعلاه

**Mini Summary Bar:**
```
Total Keys: 18  |  Active: 14  |  Expiring Soon: 2  |  Exhausted: 1  |  Revoked: 1
```

**بطاقة المفتاح في الجدول — تصميم موسع:**

كل صف في الجدول يعرض:
```
┌────────────────────────────────────────────────────────────────────────────────┐
│ ⭐ Production Key #1        sk-...****8f2a    [OpenAI]  ● Active               │
│ ───────────────────────────────────────────────────────────────────────────── │
│ Budget Usage:  ████████████████░░░░░░░░  $48.20 / $80.00  (60.3%)            │
│ Token Usage:   ████████░░░░░░░░░░░░░░░░  3.2M / 5M tokens (64%)             │
│                                                                                │
│ Today: 1,240 reqs │ 890K tokens │ $8.20    Last Used: 3 mins ago             │
│ Month: 18,430 reqs │ 3.2M tokens │ $48.20   Created: 2026-05-01              │
│                                                                                │
│ Success Rate: 98.4% ████████████████████ │ Errors: 23 (429: 18, 500: 5)      │
│                                                                                │
│ [Analytics] [Edit Budget] [Rotate] [Revoke] [⋮ More]                         │
└────────────────────────────────────────────────────────────────────────────────┘
```

**شريط الفلترة المتقدم جداً:**
- **Provider Filter:** Dropdown متعدد الاختيار (OpenAI / Anthropic / Gemini / All)
- **Status Filter:** Active / Exhausted / Expiring Soon / Revoked / All
- **Budget Status:** Under 50% / 50-80% / Over 80% / Unlimited
- **Usage Filter:** High Usage (>1000 req/day) / Medium / Low / Unused (0 reqs)
- **Date Range:** تاريخ الإنشاء (From / To)
- **Search:** بحث في اسم المفتاح أو الـ Label
- **Sort By:** Budget Usage % / Requests Today / Created Date / Success Rate / Last Used

**فلاتر السريعة (Quick Filters) — Tabs أفقية:**
`All` | `Active` | `⚠ Near Limit` | `❌ Exhausted` | `🔒 Revoked` | `⭐ Favorites`

### 4.2 صفحة Analytics المفتاح الفردي (Per-Key Analytics)
تفتح عند النقر على `Analytics` لأي مفتاح — صفحة مستقلة أو Drawer واسع:

**Header:**
```
Production Key #1 (sk-...****8f2a)
OpenAI  •  Active  •  Created: 2026-05-01
```

**بطاقات الإحصاء:**
| البطاقة | القيمة | Trend |
|---------|--------|-------|
| Total Requests (Month) | 18,430 | ↑ 12% vs last month |
| Successful | 18,107 (98.2%) | ↑ 0.4% |
| Failed | 323 (1.8%) | ↓ 0.4% |
| Input Tokens | 2.1M | ↑ 8% |
| Output Tokens | 1.1M | ↑ 15% |
| Total Cost | $48.20 | ↑ 22% |
| Avg Cost/Request | $0.0026 | — |
| Last Used | 3 minutes ago | — |

**رسوم بيانية مخصصة للمفتاح:**
- **Requests per Hour (Today)** — Bar Chart يوضح ساعات الذروة
- **Daily Cost (Last 30 Days)** — Line Chart لمراقبة الاتجاه
- **Error Distribution** — Doughnut: 429 Rate Limit / 500 Server Error / Timeout / Other
- **Token Breakdown per Day** — Stacked Bar: Input vs Output

**جدول آخر 50 طلب (لهذا المفتاح فقط):**
- Timestamp / Intent / Model / Tokens / Cost / Status / Actions(Replay)

**قسم إعدادات الحدود والتنبيهات:**
```
Monthly Budget Cap:    [ $80.00      ] [Save]
Daily Limit:           [ $5.00       ] [Save]
Alert at Budget %:     [ 80% ] [70%] [50%] (Multi-threshold toggles)
Alert Method:          [Email] [In-App] [Both]
Auto-Action at 100%:   [Disable Key] [Route to Fallback Key] [Just Alert]
```

### 4.3 نموذج إضافة مفتاح جديد (Add Key Modal)
Multi-step Modal:

**Step 1 — Key Identity:**
- `Provider` (Dropdown مع أيقونات)
- `Key Label/Name` (نص وصفي مثل "Production Key #1")
- `API Key Value` (حقل Password + زر Reveal مؤقت + زر Paste)
- نقر `Continue` → يتحقق من صحة المفتاح فوراً (اختبار اتصال)

**Step 2 — Budget & Limits:**
- `Monthly Budget Cap ($)` — اختياري
- `Daily Limit ($)` — اختياري
- `Rate Limit (Requests/Min)` — اختياري
- `Expiry Date` — اختياري

**Step 3 — Alerts:**
- Alert عند 50% / 70% / 90% / 100% (Checkboxes)
- Alert Method: In-App / Email

**Step 4 — Review & Save:**
- ملخص كل الإعدادات
- زر `Save & Encrypt Key`

### 4.4 إدارة دورة حياة المفتاح (Key Lifecycle)
- **Rotate Key:** Modal يطلب المفتاح الجديد → يحفظه مشفراً → يبقي القديم "Grace Period 24h" قبل الحذف
- **Revoke Key:** يطلب تأكيد + يعرض تحذير "سيؤثر على X route(s)"
- **Archive Key:** إخفاء من القائمة مع الاحتفاظ بسجلاته التاريخية
- **Export Key Report:** PDF/CSV بإحصائيات المفتاح للمحاسبة

---

## 5️⃣ محرك التوجيه الذكي (Intent Routing Engine)

### 5.1 صفحة مصفوفة التوجيه (Routing Matrix)
**تخطيط:** جدول كبير يعرض كل الـ Intents

**صف كل Intent:**
```
┌─────────────────────────────────────────────────────────────────────┐
│ general_chat                                                         │
│ Primary:  [Gemini 1.5 Pro] via [Google]    Fallback: [GPT-4o] [OpenAI]│
│ Traffic Today: 4,320 reqs │ 98.1% via Primary │ 1.9% Fallback       │
│ Avg Cost: $0.0021/req │ Avg Latency: 720ms                           │
│ Rules: [Cost-Optimized] [Standard-Latency]                          │
│ [Edit Route] [View Logs] [Test This Intent]                         │
└─────────────────────────────────────────────────────────────────────┘
```

**Intents المعروفة مسبقاً:**
`general_chat` | `data_extraction` | `summarization` | `embedding` | `fast_response` | `reasoning` | `contact_extraction` | `intent_classification` | `agent_execution`

**شريط أدوات سريع:**
- `+ Add Intent Route`
- `Import from JSON`
- `Export all Routes`
- `Traffic Simulator` — محاكاة توجيه طلب وهمي لاختبار القواعد

### 5.2 محرر قاعدة التوجيه (Route Rule Builder)
**تخطيط:** Drawer واسع أو صفحة مستقلة

**Builder بالـ Drag & Drop:**

```
[If] prompt_length > 8000 tokens
[Then] Route to: Claude 3.5 Sonnet (Anthropic)
[Else] Route to: GPT-4o-mini (OpenAI)
[Fallback Chain]:
  1st: Claude 3.5 → 2nd: GPT-4o → 3rd: Gemini 1.5 Flash
```

**Conditions المتاحة:**
- `prompt_length` (>= / <= عدد من التوكنز)
- `cost_profile` (budget / standard / premium)
- `latency_profile` (fast / balanced)
- `language` (ar / en / fr / etc.)
- `security_class` (standard / high / critical)
- `time_of_day` (hour range — لتوجيه لمزود أرخص في أوقات الذروة)
- `workspace_id` (توجيه خاص لعميل معين)

**Visual Fallback Chain Editor:**
```
[Primary: Gemini 1.5 Pro] ──→ (fails) ──→ [Fallback 1: GPT-4o] ──→ (fails) ──→ [Fallback 2: Claude Haiku]
     ↓ hover                                     ↓ hover                              ↓ hover
  [Edit] [Remove]                            [Edit] [Remove] [Move Up/Down]        [Edit] [Remove]

[+ Add Fallback Level]
```

**A/B Traffic Splitting:**
```
Traffic Distribution:
  Gemini 1.5 Pro  [███████░░░] 70%  [−] [+]
  GPT-4o          [███░░░░░░░] 30%  [−] [+]
  
  ℹ️ Total: 100% | Purpose: Cost comparison experiment
  Duration: [June 1 - June 30] or [Indefinite]
  Goal Metric: [Lowest Cost] [Lowest Latency] [Highest Success Rate]
```

---

## 6️⃣ إدارة الميزانية والتكاليف (Cost & Budget Center)

### 6.1 لوحة التحليلات المالية (Cost Analytics Dashboard)
**صفحة مستقلة متخصصة:**

**أعلى الصفحة — خلاصة مالية:**
```
Month-to-Date Spend:  $148.20    
Monthly Budget:       $250.00    ████████████░░░░░░░░  59.3%
Remaining:            $101.80
Days Left in Month:   26
Projected Month End:  $185.50  ✅ Under Budget  (at current rate)
Daily Burn Rate:      $5.93/day
```

**رسوم بيانية:**
- **Cost Breakdown by Provider** (Doughnut + Legend مع الأرقام الدقيقة)
- **Cost by Intent** (Horizontal Bar Chart — الأغلى فالأرخص)
- **Cost by Model** (Treemap — مساحة كل مربع = تكلفته)
- **Daily Cost Trend (30 Days)** — Line Chart مع خطوط Budget Daily Limit و Projection
- **Unit Economics:**
  - Cost per Successful Request (بالعملة)
  - Cost per 1000 Tokens (لكل نموذج)

### 6.2 مدير الميزانيات (Budget Manager)
جدول لكل الميزانيات:

| النطاق | الحد الشهري | المُستهلك | % | الإجراء عند التجاوز | |
|--------|------------|----------|---|---------------------|---|
| Global (All) | $250 | $148.20 | 59% | Alert Only | Edit |
| Provider: OpenAI | $100 | $48.20 | 48% | Alert + Route Fallback | Edit |
| Provider: Anthropic | $80 | $62.10 | 78% ⚠️ | Alert | Edit |
| Model: gpt-4o | $50 | $15.30 | 31% | Block Requests | Edit |
| Intent: reasoning | $30 | $29.80 | 99% 🔴 | Block + Alert | Edit |

**عند `Edit` — نافذة تعديل الميزانية:**
- Monthly Cap
- Daily Cap
- Per-Request Max Cost Limit (رفض طلب يتجاوز هذا السعر المُقدر)
- Actions عند التجاوز: `Alert Only` / `Route to Cheapest Fallback` / `Block New Requests` / `Alert + Block`
- Alert Thresholds: 50% / 75% / 90% / 100% (checkboxes)

---

## 7️⃣ بيئة الاختبار الاحترافية (Pro AI Playground)

### 7.1 Chat Tester (اختبار النموذج بالمحادثة)
**التخطيط:** مشابه لـ Claude/ChatGPT لكن مع Overlay للبيانات

**الشريط الجانبي الأيسر (Settings Panel):**
```
──── Model Selection ─────────────────
Provider: [OpenAI ▼]
Model:    [gpt-4o-mini ▼]

──── OR Route by Intent ──────────────
Intent:   [general_chat ▼]

──── Parameters ──────────────────────
Temperature:   [0.7] ─────●───── [Slider]
Max Tokens:    [2048]
Top-P:         [1.0]
Frequency Penalty: [0.0]

──── System Prompt ───────────────────
[You are a helpful assistant...     ]
[                                   ]
[Load Preset ▼] [Save as Preset]

──── Session ─────────────────────────
[Clear Chat] [Export as JSON] [Share]
```

**منطقة المحادثة (Chat Area):**
- رسائل الـ User باليمين، رسائل الـ AI باليسار
- كل رد AI يعرض في أسفله مباشرة:
  ```
  📊 Model: gpt-4o-mini | ⏱️ TTFT: 340ms | Total: 2,340ms
  📝 Tokens: 123 in / 456 out | 💰 Cost: $0.00048
  📦 Intent Used: general_chat | 🔄 Fallback: No
  [📋 Copy] [🔄 Regenerate] [👍 Rate] [🔁 Replay in Battle]
  ```

**شريط الإدخال السفلي:**
- Auto-resize textarea
- أزرار: `[📎 Attach File]` `[🎤 Voice]` `[📋 Paste from Logs]`
- زر الإرسال مع مؤشر العدد: `Send (230 tokens estimated)`

### 7.2 Multi-Model Battle (المقارنة المتزامنة)
**تخطيط:** شاشة مقسمة (2 أو 3 أو 4 أعمدة)

**رأس كل عمود:**
```
[Gemini 1.5 Pro ▼]    [GPT-4o ▼]    [Claude 3.5 Sonnet ▼]    [+ Add Model]
Google                 OpenAI         Anthropic
[Parameters ⚙️]       [Parameters ⚙️] [Parameters ⚙️]
```

**قسم الإدخال المشترك (Shared Input):**
مربع نص واحد في الأسفل، الإرسال يذهب للجميع في آن واحد
- خيار: `Sync Parameters` (Toggle) — لجعل كل المعاملات متطابقة أو مستقلة

**جسم المقارنة — عند الإرسال:**
- البث الحي (Streaming) في كل عمود مستقل
- الأسرع يبدأ أولاً وتظهر الردود تباعاً
- بعد انتهاء الجميع → يظهر جدول المقارنة الآلية:

```
┌─────────────────────────────────────────────────────────────────────┐
│                     📊 Battle Results                                │
├───────────────┬────────────────┬─────────────────┬─────────────────┤
│ Metric        │ Gemini 1.5 Pro │ GPT-4o          │ Claude 3.5      │
├───────────────┼────────────────┼─────────────────┼─────────────────┤
│ ⏱️ TTFT       │ 🏆 240ms       │ 380ms           │ 510ms           │
│ Total Latency │ 1,820ms        │ 🏆 1,540ms      │ 2,100ms         │
│ Input Tokens  │ 123            │ 123             │ 123             │
│ Output Tokens │ 421            │ 🏆 356          │ 512             │
│ 💰 Cost       │ 🏆 $0.00040   │ $0.00110        │ $0.00230        │
│ Quality Score │ ★★★★☆ (4.0)   │ ★★★★★ (4.7) 🏆 │ ★★★★½ (4.5)   │
└───────────────┴────────────────┴─────────────────┴─────────────────┘
[Save Comparison] [Open in Playground] [Export PDF Report]
```

### 7.3 Prompt Registry (مستودع القوالب)
**صفحة مستقلة للقوالب المحفوظة:**

**قائمة القوالب:**
```
[Search prompts...]  [Filter: Category ▼]  [Sort: Recent ▼]  [+ New Prompt]

┌─────────────────────────────────────────────────────────────┐
│ 📝 Customer Support Template          v3    [Used 142 times] │
│ "You are a helpful support agent for..."                     │
│ Avg Quality: ★★★★½  │  Avg Cost: $0.0032  │  Last: 2h ago  │
│ [Open in Playground] [Compare Versions] [Edit] [Delete]      │
└─────────────────────────────────────────────────────────────┘
```

**تفاصيل القالب — نافذة:**
- عرض كل الإصدارات (v1، v2، v3) مع diff بين الإصدارين
- إحصائيات لكل إصدار (عدد الاستخدام، متوسط التكلفة والجودة)
- زر `Set as Default` لكل إصدار
- `Run Evaluation` — تشغيل مجموعة Inputs على هذا القالب وتقييم النتائج

### 7.4 Background Job Simulator (محاكي المهام الخلفية)
**تخطيط:** قسمان — الإرسال (يسار) والمراقبة (يمين)

**قسم الإرسال:**
```
──── Job Configuration ───────────────────────────────
Job Type: [ProcessAiInferenceJob ▼]

Payload:
{
  "conversation_id": "uuid",
  "message_id": "uuid", 
  "model_id": "uuid",
  "prompt": "Hello, test message"
}
[Format JSON] [Load from Logs]

Queue: [llm-inference ▼]
Delay: [Immediate ▼]

[🚀 Dispatch Job]
──────────────────────────────────────────────────────
```

**قسم المراقبة الحية:**
```
──── Live Job Monitor ────────────────────────────────
Job ID: #job_abc123         Status: ⏳ Processing...
Queue: llm-inference        Attempt: 1/3
Dispatched: 2 seconds ago

Progress:
[██████████░░░░░░░░░░] 50% — Sending to Provider...

Timeline:
● 00:00.000  Job dispatched to queue
● 00:00.230  Worker picked up job
● 00:00.415  API key decrypted
● 00:00.820  Request sent to OpenAI
⏳ 00:02.100  Waiting for response...

Live WebSocket Events:
  [stream] token: "Hello" 
  [stream] token: " there"
  [stream] token: "! How"
  ...

──────────────────────────────────────────────────────
[Cancel Job] [View Full Logs]
```

**سجل الـ Jobs السابقة (جدول):**
| Job ID | Type | Status | Duration | Cost | Timestamp |
|--------|------|--------|----------|------|-----------|
| #job_001 | ProcessAiInferenceJob | ✅ Completed | 2.3s | $0.0042 | 5m ago |
| #job_002 | ExecuteAiModelJob | ❌ Failed | 10.1s | $0 | 12m ago |

---

## 8️⃣ السجلات والتدقيق العميق (Logs & Audit)

### 8.1 جدول طلبات الـ AI (Request Logs)
**شريط الفلترة الشامل جداً:**
```
[Search by Intent, Model, Error...]
Provider: [All ▼]  Model: [All ▼]  Intent: [All ▼]  Status: [All ▼]
Date Range: [Last 24h ▼]
Latency: [All ▼ → <500ms / 500ms-2s / >2s]
Cost: [All ▼ → <$0.001 / $0.001-0.01 / >$0.01]
Fallback: [Show All] [Fallback Only] [Primary Only]
Cache: [All] [Cache Hit] [Cache Miss]
Has Error: [All] [With Errors Only]
[Apply Filters] [Reset] [Save Filter Preset]
```

**صف كل سجل في الجدول:**
```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 2026-07-04 14:32:15  │  general_chat  │  gpt-4o-mini (OpenAI)              │
│ 🟢 200 OK  │  ⏱️ 820ms  │  📝 123/456 tokens  │  💰 $0.00042  │  🔄 No fallback │
│ 📦 Cache: MISS  │  Workspace: Acme Corp                                    │
│                                                                             │
│ Decision: Primary route selected. No fallback needed.                       │
│                                                                             │
│ [🔍 View Details] [🔁 Replay in Playground] [📋 Copy as cURL] [🏷️ Tag]    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 8.2 Drawer التفاصيل الكاملة (Log Detail Drawer)
يفتح من اليمين عند النقر على أي سجل:

**Tabs داخل الـ Drawer:**

**Tab 1 — Overview:**
- كل بيانات الطلب (Timestamp, Intent, Provider, Model, Status, Cost, Latency)
- Decision Explainability Box:
  ```
  🔀 Routing Decision Tree:
  ├── Intent: general_chat → Matched Route #R001
  ├── Profile: cost_optimized → Prefer budget models
  ├── Provider: Google (Primary) → Circuit: CLOSED ✅
  └── Model: gemini-1.5-flash → Context OK (8,432 / 128,000)
  
  Result: Primary route used. No fallback triggered.
  ```

**Tab 2 — Request/Response:**
- **Request Payload:**
  ```json
  {
    "model": "gemini-1.5-flash",
    "messages": [{"role": "user", "content": "..."}],
    "temperature": 0.7,
    "max_tokens": 2048
  }
  ```
- **Response Body:**
  ```json
  {
    "choices": [{"message": {"content": "..."}}],
    "usage": {"prompt_tokens": 123, "completion_tokens": 456}
  }
  ```
- زر `📋 Copy Request` + `📋 Copy Response`

**Tab 3 — Performance:**
- Timeline Chart يوضح مراحل الطلب:
  ```
  Key Decrypt:       |█| 12ms
  Payload Adapt:     |█| 8ms
  API Call (OpenAI): |█████████████████████████| 782ms
  Response Parse:    |██| 18ms
  Usage Track:       |█| 5ms
  Total:                                           825ms
  ```

**Tab 4 — Related:**
- روابط لـ Job المرتبط (إن وجد) + Conversation ID + User ID

### 8.3 Cache Inspector (فاحص الـ Cache)
**صفحة مخصصة للـ Semantic Cache:**

**إحصائيات عامة:**
```
Cache Hit Rate:   43.2%  (Last 24h)
Total Hits:       1,842
Total Misses:     2,420
Cost Saved:       $12.40 (from cache hits)
Avg Cache Age:    22 minutes
```

**جدول الـ Cache Entries:**
| Cache Key (MD5) | Intent | Prompt Preview | Hits | Created | Expires | |
|-----------------|--------|----------------|------|---------|---------|---|
| a3f...2b1 | general_chat | "What is AI?" | 12 | 2h ago | 1h | Delete |
| b7c...9e4 | summarization | "Summarize the..." | 3 | 30m ago | 2.5h | Delete |

- زر `Flush All Cache`
- زر `Flush by Intent`
- `Cache Effectiveness Chart` — Line Chart يوضح تطور Hit Rate عبر الزمن

---

## 9️⃣ الإعدادات العامة للـ Hub (Hub Global Settings)

### 9.1 إعدادات الـ Circuit Breaker
```
Global Circuit Breaker Settings:
Failure Threshold:    [5]   consecutive failures to open circuit
Recovery Timeout:     [60]  seconds before trying again (half-open)
Success Threshold:    [3]   successes to close circuit again

Per-Provider Override: [Enabled / Disabled]
```

### 9.2 إعدادات الـ Semantic Cache
```
Semantic Cache: [Enabled ✅]
TTL (seconds): [3600]
Similarity Threshold: [0.92]  (0-1, higher = more strict matching)
Max Cache Size: [1,000] entries
Embedding Model: [text-embedding-3-small (OpenAI) ▼]
[Flush Cache Now]
```

### 9.3 إعدادات الإشعارات والتنبيهات
```
Alert Channels:
  [✅] In-App Notifications
  [ ] Email: admin@company.com
  [ ] Slack Webhook: [URL...]
  [ ] Discord Webhook: [URL...]

Alert Triggers:
  [✅] Provider goes offline
  [✅] Budget exceeds 80%
  [✅] Error rate > 5% in 5 minutes
  [✅] Fallback chain fully exhausted
  [ ] Latency P95 > 5000ms
  [ ] Unusual usage spike (> 3x daily average)
```

---

## 🎨 10. مواصفات التصميم البصري (Visual Design System)

### Color Palette (Dark Mode as Default)
```
Background:      #0a0a0f  (near-black)
Surface:         #13131a  (cards/panels)
Border:          #1e1e2e  (subtle)
Primary:         #6366f1  (indigo - CTA buttons)
Success:         #22c55e  (green - healthy/active)
Warning:         #f59e0b  (amber - degraded/warning)
Danger:          #ef4444  (red - error/critical)
Info:            #3b82f6  (blue - informational)
Text Primary:    #f1f5f9
Text Secondary:  #94a3b8
Text Muted:      #475569
```

### Typography
- **Headers:** Inter / Geist Sans — Bold
- **Body:** Inter — Regular
- **Monospace (JSON/Code):** JetBrains Mono / Fira Code

### Key UX Micro-interactions
- Switches تفعيل/تعطيل: animated toggle مع color transition (300ms)
- Progress Bars: shimmer animation عند التحميل
- كل النسب الصحية: Color-coded (Green < 60% / Amber 60-80% / Red > 80%)
- Hover على البطاقات: elevation + border glow باللون الأساسي
- Copy to Clipboard: مؤشر ✅ يظهر 2 ثانية ثم يختفي
- كل الأرقام المتغيرة: CountUp animation عند تحديثها

### Responsive Considerations
- **الـ Sidebar:** قابل للطي (Collapsible) لتوفير مساحة
- **الجداول الكبيرة:** Horizontal scroll على الشاشات الصغيرة
- **الـ Playground:** Column layout يتحول لـ stacked rows على Mobile
- **الـ Dashboard:** الشبكة تتكيف من 6 columns → 3 → 1 حسب حجم الشاشة

---

## 📦 11. ربط كامل بـ Backend APIs

| الصفحة/القسم | الـ Controller | الـ Endpoints المُستخدمة |
|--------------|----------------|--------------------------|
| Dashboard | `AiRouteController`, `AiCostAnalyticsController` | `GET /ai-hub/telemetry`, `GET /ai/cost/forecast` |
| Providers | `AiProviderController` | `GET/POST/PUT/DELETE /ai/providers`, `POST /sync-models`, `POST /test` |
| Models | `AiModelController` | `GET/POST/PUT/DELETE /ai/models`, `POST /test` |
| API Keys | `AiProviderController` (key endpoints) | `GET/POST/PUT/DELETE /ai/providers/{id}/keys` |
| Intent Routing | `AiRouteController` | `GET /ai-hub/routing-matrix`, `POST /ai/route-intent` |
| Cost & Budget | `AiCostAnalyticsController` | `GET /ai/cost/forecast`, `POST /ai/cost/budget` |
| Playground | `AiRouteController`, `AiRequestController` | `POST /ai-hub/route`, `POST /ai/request` |
| Logs/Audit | `AiRouteController` | `GET /ai-hub/audit-trail` |
| Provider Health | `AiRouteController` | `GET /ai-hub/provider-health` |
| Job Simulator | Queue System | `POST /ai/jobs/dispatch` (جديد) |

---

*وثيقة مواصفات شاملة — AI Models Hub v3 — Nexus Project*
*المرجعية: Langfuse, Portkey, Kong AI Gateway, LiteLLM, OpenAI Platform, Agenta*
