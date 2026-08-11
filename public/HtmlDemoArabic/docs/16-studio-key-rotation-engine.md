# ١٦. محرك تشفير وتدوير مفاتيح المزودين بـ AES-256

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
