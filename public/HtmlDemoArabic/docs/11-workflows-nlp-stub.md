# ١١. مصنف النوايا NLP والمسار الاحتياطي للكلمات المفتاحية

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
