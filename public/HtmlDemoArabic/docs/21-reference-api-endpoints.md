# ٢١. المواصفات المرجعية لنقاط نهاية REST API والـ WebSockets

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
