# خطة التنفيذ الشاملة للواجهة (Master Implementation Plan - Hybrid Vue/Blade)

## الهدف
بناء وتفعيل إدارة المهام باستخدام بنية (Vue 3 + Blade) وربطها بالـ WebSockets للإشعارات الحية في مشروع Nexus.

## التقسيم المرحلي (Phases Breakdown)

### المرحلة الأولى: تجهيز بنية Blade (Phase 1: Blade Layout & Routes)
* **الهدف:** بناء المسارات والصفحات الأساسية باستخدام Laravel Controllers و Blade Views و Tailwind CSS، لضمان تحميل الصفحة الأولية بسرعة.
* **التفاصيل:** إنشاء `tasks.index` و `tasks.show` وتجهيز (Containers) لزرع مكونات Vue بداخلها لاحقاً.

### المرحلة الثانية: بناء مكونات Vue 3 (Phase 2: Interactive Vue Components)
* **الهدف:** تطوير مكونات Vue المستقلة للميزات المعقدة (Kanban Board, Live Logs Viewer).
* **التفاصيل:** استخدام `createApp` لزرع كل مكون في الـ ID المخصص له في Blade.

### المرحلة الثالثة: الربط بالوقت الفعلي (Phase 3: Real-time with Echo & Reverb)
* **الهدف:** ربط المكونات (Vue & Blade) بأحداث Laravel Echo.
* **التفاصيل:** تحديث الكانبان، تحديث عارض السجلات، وإطلاق أحداث من الباك إند (TaskStatusUpdated, LogAdded).

### المرحلة الرابعة: الإشعارات وتكامل Firebase (Phase 4: Notifications Hub & FCM)
* **الهدف:** تفعيل إشعارات التطبيق (NotificationHub الموجود مسبقاً) وإشعارات الموبايل/المتصفح عبر Firebase عند تعطل المهام أو انتهائها.
