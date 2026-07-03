import './bootstrap';
import { createApp } from 'vue';

// Import TasksHub Components
import TasksKanban from './components/TasksHub/TasksKanban.vue';
import LiveLogViewer from './components/TasksHub/LiveLogViewer.vue';
import TasksStatusBar from './components/TasksHub/TasksStatusBar.vue';

// Mount Kanban
const kanbanAppElement = document.getElementById('tasks-kanban-app');
if (kanbanAppElement) {
    const app = createApp(TasksKanban, {
        initialTasks: JSON.parse(kanbanAppElement.dataset.initialTasks || '[]')
    });
    app.mount('#tasks-kanban-app');
}

// Mount Status Bar
const statusBarElement = document.getElementById('tasks-status-bar-app');
if (statusBarElement) {
    const app = createApp(TasksStatusBar);
    app.mount('#tasks-status-bar-app');
}

// Global function to mount Log Viewer dynamically when modal opens
window.mountLiveLogViewer = function(taskId) {
    const container = document.getElementById('live-log-viewer-container');
    if (container) {
        if (window.liveLogApp) {
            window.liveLogApp.unmount();
        }
        container.innerHTML = '<div id="live-log-viewer-app"></div>';
        const app = createApp(LiveLogViewer, { taskId: taskId });
        app.mount('#live-log-viewer-app');
        window.liveLogApp = app;
    }
};
