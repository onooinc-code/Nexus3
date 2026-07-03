# المرحلة الأولى: تجهيز بنية Blade (Phase 1: Blade Layout & Routes)

## نظرة عامة
الاعتماد على Blade لتقديم الصفحات بشكل سريع، مع تجهيز (DOM elements) محددة لزرع مكونات Vue بداخلها.

## المهام (Tasks)
1. **إنشاء Controllers للواجهة الأمامية (Web Controllers):**
   - `WebTaskController` لإرجاع الـ Views (مثال: `return view('tasks.index', compact('stats'))`).
2. **بناء صفحة `tasks.index.blade.php`:**
   - تصميم הـ Header باستخدام Tailwind CSS.
   - إضافة فلاتر البحث وإحصائيات بسيطة تُعرض كـ Blade components ثابتة (مبدئياً).
   - وضع `<div id="tasks-kanban-app"></div>` في منتصف الصفحة.
3. **بناء صفحة `tasks.show.blade.php`:**
   - عرض البيانات الأساسية للمهمة (العنوان، الأولوية، الـ Payload).
   - وضع `<div id="task-logs-app" data-task-id="{{ $task->id }}"></div>`.
4. **ربط الـ NotificationHub:**
   - التأكد من استدعاء `@include('components.notification-hub')` في الـ Layout.

## قائمة التحقق (Checklist)
- [ ] مسارات الواجهة (`web.php`) تعمل وصفحات Blade تفتح بنجاح.
- [ ] الحاويات الخاصة بـ Vue (div ids) موجودة في الـ DOM الجاهز.
