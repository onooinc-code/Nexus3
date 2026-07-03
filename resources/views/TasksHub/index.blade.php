@extends('TasksHub.layout')

@section('tasks_content')
    <!-- Vue Component TasksKanban will mount here -->
    <div id="tasks-kanban-app" data-initial-tasks="{{ json_encode($tasks) }}"></div>

    <!-- Live Log Viewer Modal -->
    <div class="modal fade" id="liveLogModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-light">Task Execution</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="live-log-viewer-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hook TasksKanban execute action to Live Log Viewer -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Vue component will mount and might trigger an event, or we can just patch it globally
            // Wait, we need to show the modal when 'execute' is clicked.
            // Since execute is handled inside Vue, we can dispatch a window event from Vue.
            window.addEventListener('open-live-log', function(e) {
                const taskId = e.detail.taskId;
                if(window.mountLiveLogViewer) {
                    window.mountLiveLogViewer(taskId);
                }
                const modal = new bootstrap.Modal(document.getElementById('liveLogModal'));
                modal.show();
            });
        });
    </script>
@endsection
