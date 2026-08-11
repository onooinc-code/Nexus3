# ٢٠. المرجع الهيكلي لفئات النظام ومصفوفة الأكواد

يحتوي هذا المرجع على شجرة الفئات البرمجية المعتمدة في بنية **Nexus3** والعلاقات التشغيلية بينها.

---

## ١. الهيكل البرمجي للخدمات الرئيسية

- **خدمات استقبال وإدارة المحادثات (PeopleConnect Inbound Services):**
  - `App\Services\PeopleConnect\WahaWebhookIngestionService`
  - `App\Services\PeopleConnect\PeopleConnectContactResolver`
  - `App\Services\PeopleConnect\PeopleConnectConversationService`
  - `App\Services\PeopleConnect\PeopleConnectSessionService`
  - `App\Services\PeopleConnect\PeopleConnectMessageService`
  - `App\Services\PeopleConnect\FirestoreSyncService`
  - `App\Services\PeopleConnect\PeopleConnectRealtimeBroadcaster`

- **خدمات الذكاء الاصطناعي والمستودعات (AI Studio Services):**
  - `App\Services\AiHubService`
  - `App\Services\AiModelsHub\EncryptedApiKeyStorage`
  - `App\Services\AiModelsHub\SettingCacheService`
