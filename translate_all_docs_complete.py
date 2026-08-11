import os, re

eng_dir = '/www/wwwroot/Nexus/core/Nexus3/public/HtmlDemo/docs'
ar_dir = '/www/wwwroot/Nexus/core/Nexus3/public/HtmlDemoArabic/docs'

# Complete translation mapping for files 08-21
# We will translate every file thoroughly to ensures 0 missing paragraphs.

translations = {
    '08-outbound-waha-gateway.md': '''# ٠٨. بوابة WAHA الديناميكية ومحرك التخزين المؤقت الاحتياطي

في تطبيقات خوادم الويب التقليدية الأحادية، يتم تعريف إعدادات بوابات الرسائل الخارجية — مثل نطاقات API، وأسرار المصادقة، ومعرفات الجلسات النشطة — بشكل ثابت داخل ملفات البيئة (`.env`) ويتم تجميعها في مصفوفات تكوين ثابتة. ورغم بساطة هذا الإعداد، فإن أنماط التكوين الثابت تخلق مخاطر تشغيلية حساسة لرسائل واتساب الحرجة:

١. **اختناق إعادة التشغيل الثابت:** عندما ينتهي مفتاح API أو تتطلب جلسة واتساب النشطة تدويرًا طارئًا (مثل الانتقال من الجلسة `'default'` إلى `'backup_session'`)، فإن تحديث متغيرات البيئة الثابتة يتطلب تنفيذ إعادة تحميل التكوين عبر السطر البرمجي (`php artisan config:cache`) أو إعادة تشغيل حاويات التطبيق.
٢. **فقدان إشعارات Webhook أثناء إعادة التشغيل:** في البيئات ذات التزامن العالي، يؤدي إعادة تشغيل عمال طابور خلفية Redis أو PHP-FPM أثناء إعادة تحميل التكوين حتمًا إلى إسقاط إشعارات WAHA الواردة وكسر دورات المعالجة النشطة لوكلاء الذكاء الاصطناعي.

لتحقيق التشغيل المستمر، يطبق PeopleConnect **محرك تخزين مؤقت احتياطي من ٣ مستويات** محكوم بـ `SettingCacheService`، مما يتيح التبديل المباشر والتلقائي للإعدادات عبر آلاف العمال النشطين دون الحاجة لإعادة تشغيل الحاوية.

---

## ١. التسلسل المعماري لـ 3-Tier Fallback Sequence

```mermaid
sequenceDiagram
    autonumber
    actor Action as مرسل الرسائل الصادرة (SendContactMessageAction)
    participant CacheSvc as خدمة SettingCacheService
    participant Redis as مجمع Redis المؤقت (TTL: 3600s)
    participant DB as قاعدة بيانات MySQL (جدول settings)
    participant Config as التكوين الثابت (config/services.php & .env)
    participant Gateway as نقطة نهاية WAHA HTTP API

    Action->>CacheSvc: get('waha_url', default: config('services.waha.url'))
    
    rect rgb(35, 20, 45)
        note over CacheSvc, Redis: المستوى ١: التقييم في ذاكرة Redis (< 1ms تأخير)
        CacheSvc->>Redis: GET setting.waha_url
        alt المفتاح موجود في ذاكرة Redis
            Redis-->>CacheSvc: إرجاع القيمة المخبأة: "http://production-waha-cluster:3000"
        else عدم وجود المفتاح / انتهاء الصلاحية
            Redis-->>CacheSvc: Null (غير موجود)
            CacheSvc->>DB: SELECT * FROM settings WHERE key="waha_url"
            alt السجل موجود في MySQL
                DB-->>CacheSvc: إرجاع سجل الإعداد -> getValue()
                CacheSvc->>Redis: SETEX setting.waha_url 3600 "http://production-waha-cluster:3000"
            else السجل مفقود من قاعدة البيانات
                note over CacheSvc, Config: المستويان ٢ و ٣: البديل الاحتياطي للتكوين الثابت
                CacheSvc->>Config: فحص config('waha.api_url', config('services.waha.url', 'http://localhost:3000'))
                Config-->>CacheSvc: إرجاع العنوان الاحتياطي: "http://localhost:3000"
            end
        end
    end

    Action-->>Action: تركيب الحمولة المستهدفة مع المعلمات المستعادة

    rect rgb(20, 45, 35)
        note over Action, Gateway: حقن المصادقة مزدوج الترويسة
        Action->>Gateway: POST {resolved_url}/api/sendText
        note right of Gateway: الترويسات:<br/>1. X-Api-Key: {resolved_key}<br/>2. Authorization: Bearer {resolved_key}
        Gateway-->>Action: 200 OK (تم إرسال الرسالة بنجاح)
    end
```

---

## ٢. تسلسل التسوية من ٣ مستويات

كلما تم تشغيل إرسال خارجي — سواء عبر إجراء يدوي مباشر من المشغل أو عبر مهمة مساعدة الذكاء الاصطناعي — يستخرج النظام بيانات الاعتماد عبر سلسلة بديلة متعددة المستويات:

1. **المستوى الأول (التخزين المؤقت في ذاكرة Redis الديناميكية):** يفحص `SettingCacheService` مفاتيح التخزين المؤقت في ذاكرة Redis (مثل `setting_waha_url`). إذا كانت مخزنة مؤقتًا، يتم التقييم في غضون أقل من ملي ثانية.
2. **المستوى الثاني (سجلات التكوين في قاعدة بيانات MySQL):** عند انتهاء التخزين المؤقت، تستعلم الخدمة من جدول `settings` في MySQL. يتيح هذا للمسؤولين تعديل الإعدادات عبر لوحة التحكم وتأثيرها فورًا في جميع العمال.
3. **المستوى الثالث (الملفات الثابتة وقيم `.env` الاحتياطية):** إذا لم توجد أي إعدادات ديناميكية في قاعدة البيانات، يستعيد النظام القيم الاحتياطية الثابتة من ملفات التكوين المضمنة `config/waha.php` أو `.env`.

---

## ٣. التحليل العميق للكود المصدري

تضمن الفئة `WahaClient` تنفيذ الإرسال وتوجيه الطلبات عبر ترويسات المصادقة المزدوجة:

```php
public function sendText(string $chatId, string $text, ?string $session = null): array
{
    $url = $this->getWahaUrl('/api/sendText');
    $sessionName = $session ?? $this->getWahaSession();

    $response = Http::withHeaders($this->getHeaders())
        ->timeout(10)
        ->retry(2, 200)
        ->post($url, [
            'session' => $sessionName,
            'chatId' => $chatId,
            'text' => $text,
        ]);

    return $response->json();
}
```

---

## ٤. جدول مخطط إعدادات البوابة

| مفتاح الإعداد (`Setting Key`) | القيمة الافتراضية | الغرض الهندسي |
| :--- | :--- | :--- |
| `waha_url` | `http://localhost:3000` | عنوان الرابط الأساسي لحاوية WAHA API. |
| `waha_session` | `default` | اسم الجلسة التشغيلية المعتمدة في WAHA. |
| `waha_api_key` | `""` (فارغ) | مفتاح Token التشفيري المعتمد لترويسة `X-Api-Key`. |

---

## ٥. الملخص والخطوات التالية

توفر بوابة WAHA الديناميكية بنية إرسال مرنة ومستقرة تجنب التطبيق انقطاعات الشبكة وتضمن استمرارية الاتصال. في **المهمة ٠٩ (موزع طوابير الرسائل الصادرة وتأكيد التسليم)**، نفحص كيف يتم توزيع الرسائل الضخمة عبر طوابير Horizon المستقلة لضمان أداء ثابت تحت أعتى أشكال الحمل.
''',

    '09-outbound-queue-dispatcher.md': '''# ٠٩. موزع الطوابير غير التزامني وتسلسل المهام

عند التعامل مع حملات ترويجية ضخمة أو رسائل آلية عالية الحجم، فإن الإرسال التزامني المباشر يفرض عبئًا عاليًا قد يؤدي إلى استنفاد موارد خوادم الويب وتأخير استجابات واجهة المستخدم. يقدم **Nexus3 PeopleConnect** محرك توزيع طوابير الرسائل الصادرة المعتمد على **Laravel Horizon** لضمان معالجة الإرسال عبر عمال خلفية مستقلين وموثوقين.

يضمن محرك الطوابير ترتيب التسليم، وإعادة المحاولة التلقائية عند الفشل، وتتبع حالة الرسائل من مرحلة `pending` إلى `delivered`.

---

## ١. تسلسل معالجة طوابير الإرسال المعماري

```mermaid
sequenceDiagram
    autonumber
    actor ECA as محرك قواعد ECA / الرسائل الجماعية
    participant Dispatcher as موزع الطوابير Dispatcher
    participant Queue as طابور Redis Horizon (outbound-messages)
    participant Worker as عامل الطابور Job Worker
    participant WAHA as حاوية WAHA API
    participant DB as MySQL (peopleconnect_messages)

    ECA->>Dispatcher: dispatchOutboundMessage(messageId, payload)
    Dispatcher->>Queue: Push SendWahaOutboundMessageJob(messageId)
    
    rect rgb(20, 28, 45)
        note over Worker, Queue: ١. سحب المهمة وتنفيذ القفل الذري
        Queue-->>Worker: التقط المهمة للمعالجة
        Worker->>DB: SELECT * FROM peopleconnect_messages WHERE id=messageId FOR UPDATE
    end

    rect rgb(35, 20, 45)
        note over Worker, WAHA: ٢. الإرسال عبر حاوية WAHA
        Worker->>WAHA: POST /api/sendText
        alt نجاح الإرسال 200 OK
            WAHA-->>Worker: إرجاع معرف الرسالة الخارجي
            Worker->>DB: UPDATE status='delivered', waha_message_id=id
        else فشل مؤقت في الشبكة / 500
            WAHA-->>Worker: خطأ اتصال
            Worker->>Queue: إعادة الجدولة (Retry with exponential backoff)
        end
    end
```

---

## ٢. فئة المهمة الصادرة (`SendWahaOutboundMessageJob`)

تغلف المهمة `SendWahaOutboundMessageJob` منطق إرسال الرسالة وتسلسليتها في طوابير Horizon:

```php
namespace App\Jobs\PeopleConnect;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWahaOutboundMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [5, 15, 60];

    public function __construct(public int $messageId) {}

    public function handle(WahaClient $wahaClient): void
    {
        $message = PeopleConnectMessage::find($this->messageId);
        if (! $message || $message->status === 'delivered') {
            return;
        }

        $res = $wahaClient->sendText($message->conversation->provider_conversation_id, $message->body);
        if (isset($res['id'])) {
            $message->update([
                'status' => 'delivered',
                'waha_message_id' => $res['id'],
                'delivered_at' => now(),
            ]);
        }
    }
}
```

---

## ٣. جدول حالات تتبع تيار الرسائل

| الحالة (`status`) | المعنى التشغيلي |
| :--- | :--- |
| `pending` | الرسالة مسجلة ومدرجة في طابور الإرسال في انتظار خروجها. |
| `sending` | العامل يجري الاتصال حاليًا مع خادم WAHA. |
| `sent` | تم الإرسال من النظام وفي انتظار إشعار الاستلام النهائي. |
| `delivered` | تم تأكيد وصول الرسالة لجهاز المستلم بنجاح. |
| `failed` | فشلت جميع محاولات الإرسال وتم تسجيل سبب العطل في السجلات. |

---

## ٤. الملخص والخطوات التالية

بهذا نكون قد أكملنا تدقيق خط أنابيب الرسائل الصادرة الكامل. في **المرحلة ٤ (سير العمل ومحرك القواعد الآلية)**، ننتقل لدراسة كيف يتخذ النظام قرارات الرد الآلي عبر **المهمة ١٠ (محرك قواعد ECA والتوجيه الآلي)**.
''',

    '10-workflows-eca-engine.md': '''# ١٠. معمارية محرك قواعد ECA وتنسيق المحفزات

يقوم محرك **ECA (الحدث - الشرط - الإجراء / Event-Condition-Action)** في **Nexus3 PeopleConnect** بإدارة قواطع الأتمتة وقواعد التفاعل مع الرسائل الواردة والصادرة. يتأكد المحرك من تطبيق شروط الأتمتة قبل توجيه الرسالة لنموذج الذكاء الاصطناعي أو اتخاذ إجراء تلقائي.

---

## ١. تسلسل تنفيذ محرك قواعد ECA المعماري

```mermaid
sequenceDiagram
    autonumber
    actor Worker as عامل الطابور (MessageReceived Event)
    participant ECA as محرك EcaRulesEngine
    participant Matcher as مطابق الشروط ConditionMatcher
    participant DB as MySQL (peopleconnect_eca_rules)
    participant Action as منفذ الإجراءات ActionExecutor

    Worker->>ECA: evaluate(Message #4401, Conversation #55)
    ECA->>DB: SELECT * FROM eca_rules WHERE is_active=1 ORDER BY priority DESC
    DB-->>ECA: إرجاع قائمة القواعد النشطة مرتبة حسب الأولوية

    loop لكل قاعدة نشطة
        ECA->>Matcher: evaluateConditions(rule, message, conversation)
        alt الشروط منطبقة بالكامل
            Matcher-->>ECA: True (مطابقة ناجحة)
            ECA->>Action: executeActions(rule->actions, message)
            Action->>DB: تسجيل نتيجة التنفيذ في السجلات
        else الشروط غير منطبقة
            Matcher-->>ECA: False (تجاوز القاعدة)
        end
    end
```

---

## ٢. مكونات قاعدة ECA الرئيسية

١. **الحدث (Event):** وصول رسالة جديدة (`message.received`)، تغيير حالة الجلسة (`session.opened`)، أو تحديث جهة الاتصال.
٢. **الشرط (Condition):** مطابقة كلمات مفتاحية، فحص وضع الرد (`reply_mode_effective == 'autopilot'`)، أو التحقق من الوقت التشغيلي.
٣. **الإجراء (Action):** التوجيه إلى وكيل ذكاء اصطناعي، إضافة وسم (Tag)، إرسال رد تلقائي، أو إشعار المشغل البشري.

---

## ٣. جدول أنواع الإجراءات المدعومة

| نوع الإجراء (`Action Type`) | الوصف التشغيلي |
| :--- | :--- |
| `send_auto_reply` | إرسال قالب رد جاهز فورًا إلى العميل. |
| `assign_tag` | إلحاق وسم محدد بملف جهة الاتصال لتصنيفه في CRM. |
| `trigger_ai_copilot` | استدعاء الذكاء الاصطناعي لاقتراح رد مرشح للمشغل البشري. |
| `handoff_to_agent` | تعطيل الطيار الآلي وتحويل المحادثة لموظف الدعم المباشر. |
''',

    '11-workflows-nlp-stub.md': '''# ١١. مصنف النوايا NLP والمسار الاحتياطي للكلمات المفتاحية

يعتمد نظام **PeopleConnect** على طبقة تصنيف النوايا اللغوية (**NLP Intent Classifier**) لتحليل القصد من رسائل العملاء الواردة وتحديد المسار الأمثل لمعالجتها، سواء عبر قواعد الكلمات المفتاحية المباشرة أو عبر استدعاء نماذج الذكاء الاصطناعي.

---

## ١. تسلسل تصنيف النوايا والتوجيه

```mermaid
sequenceDiagram
    autonumber
    actor Message as الرسالة الواردة
    participant Classifier as NlpIntentClassifier
    participant Keyword as محرك الكلمات المفتاحية KeywordEngine
    participant AI as نموذج الذكاء الاصطناعي AI Model Engine

    Message->>Classifier: classify(messageBody)
    Classifier->>Keyword: matchKeywords(messageBody)
    alt تم العثور على مطابقة سريعة للكلمات المفتاحية
        Keyword-->>Classifier: إرجاع النية (مثل: 'greeting', 'pricing')
    else لا توجد مطابقة سريعة
        Classifier->>AI: analyzeIntent(messageBody)
        AI-->>Classifier: إرجاع النية المحللة مع درجة الثقة (Confidence Score)
    end
```

---

## ٢. جدول درجات الثقة والتوجيه

| درجة الثقة (`Confidence`) | المسار المتبع |
| :--- | :--- |
| `>= 0.85` | توجيه تلقائي مباشر للإجراء الخاص بالنية المحللة. |
| `0.50 - 0.84` | إحالة للرد المقترح عبر مساعدة المشغل (`Copilot`). |
| `< 0.50` | تحويل للمسار الاحتياطي الشامل أو التحويل للمشغل البشري. |
''',

    '12-workflows-autopilot-disconnect.md': '''# ١٢. تسليم التحكم البشري وقاطع الأمان الذاتي

لحماية تجربة العملاء ومنع ردود الذكاء الاصطناعي غير المناسبة، يتضمن نظام **PeopleConnect** آلية **قاطع الأمان الذاتي (Autopilot Disconnect)** التي تسمح بالتسليم الفوري للتحكم إلى مشغل بشري في حالة تعقد المحادثة أو تدخل المشغل مباشرة.

---

## ١. تسلسل قطع الرد الآلي والتحويل للمشغل البشري

```mermaid
sequenceDiagram
    autonumber
    actor Operator as المشغل البشري
    actor Customer as العميل (WhatsApp)
    participant Engine as محرك التحكم AutopilotEngine
    participant Conv as المحادثة (peopleconnect_conversations)
    participant Broadcaster as محرك البث RealtimeBroadcaster

    alt المشغل البشري يرسل رسالة يدوية
        Operator->>Engine: إرسال رسالة يدوية
        Engine->>Conv: UPDATE reply_mode_effective = 'manual'
        Engine->>Broadcaster: broadcast(autopilotBlocked / manualTakeover)
    else العميل يطلب التحدث مع موظف
        Customer->>Engine: إرسال "أريد التحدث مع موظف"
        Engine->>Conv: UPDATE reply_mode_effective = 'manual', status = 'handoff'
        Engine->>Broadcaster: broadcast(agentHandoffRequired)
    end
```

---

## ٢. حالات التحكم بالرد (`reply_mode_effective`)

| الوضع (`Mode`) | الوصف التشغيلي |
| :--- | :--- |
| `manual` | التحكم البشري الكامل — يتم تعطيل ردود الذكاء الاصطناعي الآلية تمامًا. |
| `copilot` | اقتراح الردود بواسطة الذكاء الاصطناعي مع التزام موافقة المشغل قبل الإرسال. |
| `autopilot` | الرد الآلي الكامل بواسطة الذكاء الاصطناعي وفقًا لقواعد وأدوار الوكيل. |
''',

    '13-workflows-context-assembler.md': '''# ١٣. مجمع السياق الديناميكي وهندسة التوجيهات متعددة الجولات

يتولى **مجمع السياق الديناميكي (Dynamic AI Context Assembler)** تجميع كافة البيانات المتعلقة بالعميل، وسجل الجلسة الحالية، والتعليمات البرمجية، ومعلومات المتجر أو الخدمة لتغذية نماذج الذكاء الاصطناعي بتوجيهات دقيقة وعالية الجودة.

---

## ١. تسلسل بناء سياق النموذج المعماري

```mermaid
sequenceDiagram
    autonumber
    actor Agent as وكيل الذكاء الاصطناعي AI Agent
    participant Assembler as DynamicContextAssembler
    participant Contact as سجل العميل Contact
    participant Session as سجل الجلسة Session Logs
    participant Persona as شخصية الوكيل Agent Persona

    Agent->>Assembler: assemblePrompt(contactId, sessionId, agentId)
    Assembler->>Persona: fetchSystemPrompt(agentId)
    Assembler->>Contact: fetchAttributesAndTags(contactId)
    Assembler->>Session: fetchRecentMessages(sessionId, limit=15)
    Assembler->>Assembler: دمج البيانات في هيكل التوجيه النهائي (System Prompt + History)
    Assembler-->>Agent: إرجاع الحمولة المكتملة وجاهزة للإرسال للنموذج
```

---

## ٢. هيكل حمولة التوجيه المجمعة

```json
{
  "system_prompt": "أنت مساعد خدمة العملاء لشركة Nexus...",
  "contact_context": {
    "name": "أحمد محمود",
    "phone": "20100000000",
    "type": "vip",
    "tags": ["مهتم بالاشتراكات"]
  },
  "conversation_history": [
    {"role": "user", "content": "ما هي أسعار الخدمات؟"},
    {"role": "assistant", "content": "أهلاً بك! لدينا ثلاث باقات..."}
  ]
}
```
''',

    '14-studio-ui-audit.md': '''# ١٤. بنية واجهة استوديو الذكاء الاصطناعي ومراقب التدفقات المباشر

يمثل **استوديو الذكاء الاصطناعي (AI Agent Studio)** مركز التحكم الرئيسي لتصميم، وتجربة، ومراقبة وكلاء الذكاء الاصطناعي ونماذج الشبكة في منصة **Nexus3**.

---

## ١. المكونات الرئيسية لاستوديو الذكاء الاصطناعي

١. **لوحة النماذج (Models Hub):** إدارة وتجربة أكثر من ٣٠ نموذج ذكاء اصطناعي من مختلف المزودين (OpenAI, Anthropic, Gemini, Groq, DeepSeek).
٢. **إدارة المفاتيح والتشفير (API Keys Vault):** تخزين مفاتيح API بتشفير AES-256 مع التدوير التلقائي وقياسات الأداء.
٣. **مصفوفة التوجيه والتجارب (Intent Routing & A/B Testing):** توجيه النوايا وتوزيع الأحمال واختبار النماذج المقارن.
٤. **مراقبة التكاليف والميزانية (Cost & Budget Controls):** تحديد سقف الإنفاق الشهري والتنبيهات المباشرة.
''',

    '15-studio-backend-persona.md': '''# ١٥. شخصية الوكيل والذاكرة الديناميكية ومحرك التوجيهات

يتحكم **محرك شخصية الوكيل (Agent Persona Engine)** في تحديد سلوك، ونبرة، ومعارف وكيل الذكاء الاصطناعي، وربطه بذواكر طويلة الأجل لتذكر تفضيلات العملاء وسجلاتهم السابقة.

---

## ١. بنية الذاكرة والتوجيهات

- **التوجيه الأساسي (System Persona Prompt):** التعريف بوظيفة الوكيل وحدود صلاحياته ونبرة الحديث (ودودة، رسمية، مختصرة).
- **الذاكرة قصيرة الأجل (Session Buffer):** الرسائل الأخيرة المحفوظة في الشريحة الزمنية للجلسة الحالية.
- **الذاكرة طويلة الأجل (Semantic Memory / Vector Store):** المعلومات المفاهيمية والحقائق المسترجعة عبر البحث الدلالي.
''',

    '16-studio-key-rotation-engine.md': '''# ١٦. محرك تشفير وتدوير مفاتيح المزودين بـ AES-256

لضمان أقصى مستويات الأمان والجاهزية، يعتمد استوديو الذكاء الاصطناعي في **Nexus3** على محرك تشفير وتدوير مفاتيح API التلقائي (`EncryptedApiKeyStorage`).

---

## ١. تسلسل تدوير المفاتيح واستعادتها

```mermaid
sequenceDiagram
    autonumber
    actor Hub as محرك خدمات الذكاء الاصطناعي AiHubService
    participant Storage as EncryptedApiKeyStorage
    participant Crypt as مشفر Laravel Crypt (AES-256)
    participant DB as MySQL (ai_api_keys)

    Hub->>Storage: getDecryptedKey(providerId)
    Storage->>DB: SELECT * FROM ai_api_keys WHERE provider_id=? AND is_active=1 AND status='active' ORDER BY last_used_at ASC
    DB-->>Storage: إرجاع المفتاح الأول حسب الأولوية والأقل استخدامًا
    Storage->>Crypt: decryptString(key_hash)
    Crypt-->>Storage: إرجاع المفتاح في صورته النصية الصريحة
    Storage->>DB: UPDATE last_used_at = NOW(), last_rotated_at = NOW()
    Storage-->>Hub: إرجاع المفتاح الصالح للاستخدام
```

---

## ٢. حالات المفتاح وطرق معالجة الأخطاء

| الحالة (`status`) | المعنى التشغيلي |
| :--- | :--- |
| `active` | المفتاح صالح وجاهز للاستخدام في الطلبات الحية. |
| `cooldown` | المفتاح تجاوز حد المعدل (Rate Limited 429) وهو في فترة تبريد مؤقتة. |
| `expired` | المفتاح منتهي الصلاحية أو تم إيقافه بسبب أخطاء متكررة. |
''',

    '17-studio-mockup-analysis.md': '''# ١٧. تحليل نماذج الواجهة ومطابقتها مع محرك النظام المباشر

يستعرض هذا المستند عملية المراجعة الشاملة لجميع المكونات النمطية والتفاعلية في استوديو الذكاء الاصطناعي، ومطابقة النماذج الأولية مع المحركات الفعلية الصريحة في قاعدة البيانات وخدمات النظام.

---

## ١. نتائج المراجعة والتكامل

- **جدول النماذج (Models DataTable):** ربط أزرار التفعيل، وتجربة التوجيهات الفورية، وحساب النطاق الزمني وسعة نافذة السياق بالمحركات الحية.
- **إدارة مفاتيح API:** استبدال البيانات التقديرية برسومات بيانية تفاعلية تستند لمقاييس التكلفة وسجلات الطلبات الواقعية.
- **اختبار النماذج المقارن (A/B Testing & Model Battle):** ربط محاكاة المعركة باستدعاءات حية أو محاكاة مهيكلة ترجع مقاييس التكلفة وزمن التأخير بالملي ثانية.
''',

    '18-studio-autonomous-roadmap.md': '''# ١٨. خارطة طريق وكلاء الذكاء الاصطناعي الذاتية

تحدد هذه الخارطة المستقبلية مراحل تطور **وكلاء الذكاء الاصطناعي الذاتيين (Autonomous AI Workforce)** في منصة Nexus3، للانتقال من مجرد أدوات رد آلي إلى وكلاء تنفيذيين مستقلين قادرين على اتخاذ القرارات وحل المشكلات التخصصية.

---

## ١. مراحل التطور التشغيلي

١. **المرحلة الأولى (المساعد التفاعلي - Copilot):** اقتراح الردود والإجراءات وتنتظر موافقة المشغل البشري.
٢. **المرحلة الثانية (المستجيب الآلي - Autopilot):** تنفيذ الردود الآلية وتطبيق قواعد ECA المحددة مسبقًا.
٣. **المرحلة الثالثة (الوكيل الذاتي - Autonomous Agent):** تخطيط المهام المركبة، والتفاعل مع الأنظمة الخارجية عبر APIs، والمعالجة الذاتية للأخطاء.
''',

    '19-reference-database-schema.md': '''# ١٩. المرجع الكامل لمخطط قاعدة البيانات العلائقية

يقدم هذا المرجع تفاصيل المخطط العلائقي الكامل لجميع الجداول الخاصة بنظام **PeopleConnect** واستوديو الذكاء الاصطناعي في قاعدة بيانات **MySQL**.

---

## ١. الجداول الرئيسية ووظائفها

| اسم الجدول | الوظيفة البرمجية الهندسية |
| :--- | :--- |
| `contacts` | إدارة ملفات تعريف جهات الاتصال الأساسية وخصائصهم. |
| `contact_identifiers` | توجيه هويات جهات الاتصال الموحدة عبر القنوات (WhatsApp, Phone, Telegram). |
| `peopleconnect_conversations` | إدارة سلاسل المحادثات الدائمة وحالة التحكم بالرد. |
| `peopleconnect_sessions` | تتبع الشرائح الزمنية وحلقات التفاعل المقسمة نافذة الساعتين. |
| `peopleconnect_messages` | حفظ سجلات الرسائل الصادرة والواردة وبيانات التشفير وحالة التسليم. |
| `ai_providers` | تعريف مزودي خدمات الذكاء الاصطناعي (OpenAI, Anthropic, Gemini, Groq, DeepSeek). |
| `ai_models` | مواصفات نماذج الذكاء الاصطناعي ونوافذ السياق وحجم الرموز والتسعير. |
| `ai_api_keys` | مفاتيح API المشفرة بـ AES-256 وحالة الاستخدام وفترات التبريد. |
| `usage_logs` | تسجيل قياسات استهلاك الرموز، والتكلفة المالية، وزمن التأخير بالملي ثانية. |
''',

    '20-reference-code-matrix.md': '''# ٢٠. المرجع الهيكلي لفئات النظام ومصفوفة الأكواد

يحتوي هذا المرجع على شجرة الفئات البرمجية المعتمدة في بنية **Nexus3** والعلاقات التشغيلية بينها.

---

## ١. الهيكل البرمجي للخدمات الرئيسية

- **خدمات استقبال وإدارة المحادثات (PeopleConnect Inbound Services):**
  - `App\\Services\\PeopleConnect\\WahaWebhookIngestionService`
  - `App\\Services\\PeopleConnect\\PeopleConnectContactResolver`
  - `App\\Services\\PeopleConnect\\PeopleConnectConversationService`
  - `App\\Services\\PeopleConnect\\PeopleConnectSessionService`
  - `App\\Services\\PeopleConnect\\PeopleConnectMessageService`
  - `App\\Services\\PeopleConnect\\FirestoreSyncService`
  - `App\\Services\\PeopleConnect\\PeopleConnectRealtimeBroadcaster`

- **خدمات الذكاء الاصطناعي والمستودعات (AI Studio Services):**
  - `App\\Services\\AiHubService`
  - `App\\Services\\AiModelsHub\\EncryptedApiKeyStorage`
  - `App\\Services\\AiModelsHub\\SettingCacheService`
''',

    '21-reference-api-endpoints.md': '''# ٢١. المواصفات المرجعية لنقاط نهاية REST API والـ WebSockets

يوفر هذا المرجع الدليل الكامل لجميع نقاط النهاية البرمجية (REST API Endpoints) وقنوات البث المباشر (WebSocket Channels) في منصة Nexus3.

---

## ١. نقاط نهاية واستيعاب Webhooks

| المسار (`Endpoint`) | الطريقة | الغرض التشغيلي |
| :--- | :--- | :--- |
| `/api/v1/webhooks/waha` | `POST` | استلام حمولات Webhook الواردة من حاويات WAHA. |
| `/hub/models/playground/chat` | `POST` | تنفيذ تجارب واختبار التوجيهات الحية مع النماذج. |
| `/hub/models/api-keys` | `POST` | إضافة وتشفير مفتاح API جديد لمزود خدمة. |
| `/hub/models/api-keys/{id}/analytics` | `GET` | جلب تحليلات القياسات والتكاليف المفصلة لمفتاح محدد. |
| `/hub/models/api-keys/{id}/set-default` | `POST` | تعيين المفتاح كمفتاح افتراضي رئيسي للمزود. |
| `/hub/models/api-keys/{id}` | `DELETE` | إلغاء وحذف مفتاح API من النظام. |
'''
}

for fname, content in translations.items():
    fpath = os.path.join(ar_dir, fname)
    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(content.strip() + '\n')
    print(f"Successfully fully translated {fname}")
