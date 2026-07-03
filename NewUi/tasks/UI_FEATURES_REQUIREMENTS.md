# وثيقة متطلبات وميزات واجهة المستخدم لإدارة المهام (Nexus Hybrid Architecture)

## 1. نظرة عامة
تم تصميم هذه الوثيقة بناءً على البنية المعمارية المعتمدة في مشروع **Nexus** وهي **(Vue 3 + Blade Hybrid Approach)**. 
تهدف هذه البنية للاستفادة من سرعة تحميل صفحات (Blade) وقوة التفاعلية والوقت الفعلي (Real-time) التي يوفرها (Vue 3 + Laravel Echo + Reverb) دون الحاجة لبناء التطبيق كـ (SPA) معقد.

## 2. الهيكلية المعمارية للواجهة (Hybrid UI Architecture)
* **القالب الأساسي (Layout):** استخدام Blade (`app.blade.php`) و Tailwind CSS لبناء الهيكل، الشريط العلوي، شريط الحالة، والقوائم الجانبية.
* **المكونات التفاعلية (Interactive Vue Components):** سيتم تركيب (Mount) مكونات Vue 3 فقط في الأماكن التي تتطلب تفاعلاً معقداً أو تحديثات حية، مثل:
  - لوحة الكانبان للمهام (`<div id="tasks-kanban-app"></div>`).
  - عارض سجلات التنفيذ الحي (`<div id="task-logs-app"></div>`).
  - الإحصائيات الحية (`<div id="tasks-stats-app"></div>`).

## 3. تكامل ميزات الوقت الفعلي والإشعارات
* **Laravel Reverb & Echo:** للاستماع لأحداث تغير حالة المهام عبر قنوات (Channels) وتحديث مكونات Vue 3 لحظياً.
* **NotificationHub:** النظام الموجود مسبقاً في `resources/views/components/notification-hub.blade.php` سيستمر في العمل لاستقبال الإشعارات المتعلقة بالمهام وتحديث عداد الإشعارات.
* **Firebase FCM:** إرسال إشعارات دفع (Push Notifications) عبر نظام Laravel Notifications للمهام الحرجة (مثال: Task Failed).

## 4. الميزات والمكونات التفصيلية

### أ. صفحات Blade (Blade Views)
* `resources/views/tasks/index.blade.php`: الصفحة الرئيسية، تحتوي على عرض جدول المهام (طريقة العرض الكلاسيكية) أو استدعاء مكون Vue للكانبان.
* `resources/views/tasks/show.blade.php`: صفحة تفاصيل المهمة، تعرض بيانات الـ Payload والمحتوى الثابت عبر Blade.

### ب. مكونات Vue 3 (Mounted Components)
* **TasksKanban.vue:** لوحة سحب وإفلات للمهام، ترتبط بـ `api/tasks`. تستمع عبر Echo لـ `TaskUpdated` لتحديث مواقع المهام في اللوحة للجميع.
* **LiveLogViewer.vue:** نافذة (Terminal) داخل صفحة تفاصيل المهمة. تستمع لـ `Echo.private('task.{id}')` وتضيف أسطر السجلات (Logs) فور وصولها.
* **TaskStats.vue:** شريط صغير أسفل الصفحة أو أعلى الجدول يجلب من `api/tasks/stats` ويستمع لتحديثات المهام لتغيير العدادات (Pending/Running) دون تحديث الصفحة.

### ج. أزرار التحكم في المهام (Blade/Vue)
* ▶️ **Run**, ⏸️ **Pause**, ⏹️ **Cancel**: يمكن تنفيذها عبر (Blade Forms with Alpine.js/AJAX) أو داخل مكون الكانبان في Vue للسرعة. ترسل طلبات لـ `api/tasks/{task}/execute` وغيرها.
