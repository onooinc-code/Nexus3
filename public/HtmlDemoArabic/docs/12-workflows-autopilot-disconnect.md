# ١٢. تسليم التحكم البشري وقاطع الأمان الذاتي

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
