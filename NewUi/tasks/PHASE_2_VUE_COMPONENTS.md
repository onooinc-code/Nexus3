# المرحلة الثانية: بناء مكونات Vue 3 (Phase 2: Interactive Vue Components)

## نظرة عامة
تطوير ميزات إدارة المهام التي تتطلب تفاعلاً مستمراً (سحب وإفلات، تحديث حي) كمكونات Vue 3 مستقلة يتم تشغيلها داخل Blade.

## المهام (Tasks)
1. **مكون لوحة الكانبان (`TasksKanban.vue`):**
   - استهلاك `GET /api/tasks` عبر Axios لملء أعمدة الكانبان (Todo, In-progress, Blocked, Completed).
   - تطبيق مكتبة السحب والإفلات (مثل `vuedraggable` أو `Sortable.js`).
   - إرسال طلب `PATCH /api/tasks/{id}/status` عند إسقاط بطاقة مهمة في عمود جديد.
   - تركيب المكون في Blade عبر: `createApp(TasksKanban).mount('#tasks-kanban-app')`.
2. **مكون عارض السجلات (`LiveLogViewer.vue`):**
   - جلب الـ `taskId` من الـ `data-task-id` في الـ DOM الخاص بـ Blade.
   - جلب السجلات القديمة من `GET /api/tasks/{id}/logs`.
   - تصميم الواجهة باللون الأسود وخط الـ Monospace.
   - تركيب المكون في Blade: `createApp(LiveLogViewer).mount('#task-logs-app')`.
3. **أزرار التحكم الديناميكية:**
   - تضمين أزرار الإيقاف/الاستئناف ضمن بطاقة الكانبان لسهولة الوصول.

## قائمة التحقق (Checklist)
- [ ] السحب والإفلات في الكانبان يعمل ويُحدّث الحالة في قاعدة البيانات.
- [ ] مكون سجلات المهام يجلب البيانات القديمة بنجاح ويعرضها.
