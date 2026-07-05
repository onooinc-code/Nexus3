<template>
  <div class="row g-3">
    <!-- To Do Column -->
    <div class="col-12 col-md-3">
      <div class="kanban-col">
        <div class="kanban-col-header">
          <span class="kanban-col-title text-muted">
            <i class="fa-regular fa-circle me-2"></i>To Do
          </span>
          <span class="kanban-col-count">{{ tasks.todo.length }}</span>
        </div>
        <draggable
          v-model="tasks.todo"
          group="tasks"
          item-key="id"
          class="flex-1 min-h-200"
          @change="onTaskChange($event, 'todo')"
        >
          <template #item="{ element }">
            <TaskCard :task="element" @action="handleAction" />
          </template>
        </draggable>
      </div>
    </div>

    <!-- In Progress Column -->
    <div class="col-12 col-md-3">
      <div class="kanban-col border-info-subtle">
        <div class="kanban-col-header">
          <span class="kanban-col-title text-info">
            <i class="fa-solid fa-spinner fa-spin me-2"></i>In Progress
          </span>
          <span class="kanban-col-count bg-info-subtle text-info">{{ tasks['in-progress'].length }}</span>
        </div>
        <draggable
          v-model="tasks['in-progress']"
          group="tasks"
          item-key="id"
          class="flex-1 min-h-200"
          @change="onTaskChange($event, 'in-progress')"
        >
          <template #item="{ element }">
            <TaskCard :task="element" @action="handleAction" />
          </template>
        </draggable>
      </div>
    </div>
    
    <!-- Blocked Column -->
    <div class="col-12 col-md-3">
      <div class="kanban-col border-warning-subtle">
        <div class="kanban-col-header">
          <span class="kanban-col-title text-warning">
            <i class="fa-solid fa-ban me-2"></i>Blocked
          </span>
          <span class="kanban-col-count bg-warning-subtle text-warning">{{ tasks.blocked.length }}</span>
        </div>
        <draggable
          v-model="tasks.blocked"
          group="tasks"
          item-key="id"
          class="flex-1 min-h-200"
          @change="onTaskChange($event, 'blocked')"
        >
          <template #item="{ element }">
            <TaskCard :task="element" @action="handleAction" />
          </template>
        </draggable>
      </div>
    </div>

    <!-- Completed Column -->
    <div class="col-12 col-md-3">
      <div class="kanban-col border-success-subtle">
        <div class="kanban-col-header">
          <span class="kanban-col-title text-success">
            <i class="fa-solid fa-circle-check me-2"></i>Completed
          </span>
          <span class="kanban-col-count bg-success-subtle text-success">{{ tasks.completed.length }}</span>
        </div>
        <draggable
          v-model="tasks.completed"
          group="tasks"
          item-key="id"
          class="flex-1 min-h-200"
          @change="onTaskChange($event, 'completed')"
        >
          <template #item="{ element }">
            <TaskCard :task="element" @action="handleAction" />
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import TaskCard from './TaskCard.vue'
import axios from 'axios'

export default {
  components: {
    draggable,
    TaskCard
  },
  props: {
    initialTasks: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      tasks: {
        'todo': [],
        'in-progress': [],
        'blocked': [],
        'completed': []
      }
    }
  },
  mounted() {
    this.processTasks(this.initialTasks);
    
    // Listen for custom event from Create Modal
    window.addEventListener('task-created', this.fetchTasks);
    
    // Setup Laravel Echo listener for real-time updates
    if (window.Echo) {
      window.Echo.channel('tasks')
        .listen('TaskStatusUpdated', (e) => {
          this.fetchTasks();
        });
    }
  },
  beforeUnmount() {
    window.removeEventListener('task-created', this.fetchTasks);
    if (window.Echo) {
      window.Echo.leave('tasks');
    }
  },
  methods: {
    processTasks(rawTasks) {
      this.tasks = {
        'todo': [],
        'in-progress': [],
        'blocked': [],
        'completed': []
      };
      
      rawTasks.forEach(task => {
        const status = (task.status || 'todo').toLowerCase();
        
        if (['todo', 'pending', 'queued'].includes(status)) {
          this.tasks['todo'].push(task);
        } else if (['in-progress', 'in_progress', 'running', 'active'].includes(status)) {
          this.tasks['in-progress'].push(task);
        } else if (['blocked', 'paused'].includes(status)) {
          this.tasks.blocked.push(task);
        } else if (['completed', 'done', 'success'].includes(status)) {
          this.tasks.completed.push(task);
        }
      });
    },
    fetchTasks() {
      axios.get('/api/tasks?per_page=100')
        .then(response => {
            const data = response.data.data || response.data;
            this.processTasks(Array.isArray(data) ? data : data.data);
        })
        .catch(error => console.error("Error fetching tasks:", error));
    },
    onTaskChange(evt, newStatus) {
      if (evt.added) {
        const task = evt.added.element;
        let apiStatus = newStatus;
        if(apiStatus === 'todo') apiStatus = 'todo';
        if(apiStatus === 'in-progress') apiStatus = 'in-progress';
        
        axios.patch(`/api/tasks/${task.id}/status`, { status: apiStatus })
          .then(res => {
            if(window.Nexus && window.Nexus.notify) {
                Nexus.notify(`Task ${task.id} moved to ${newStatus}`, 'success');
            }
          })
          .catch(err => {
            console.error(err);
            if(window.Nexus && window.Nexus.notify) {
                Nexus.notify(`Failed to move task.`, 'error');
            }
            // Revert on failure
            this.fetchTasks();
          });
      }
    },
    handleAction({ action, task }) {
        if(action === 'execute') {
            // Open the Live Log Viewer Modal
            window.dispatchEvent(new CustomEvent('open-live-log', { detail: { taskId: task.id } }));
        }

        let endpoint = '';
        if(action === 'execute') endpoint = `/api/tasks/${task.id}/execute`;
        if(action === 'pause') endpoint = `/api/tasks/${task.id}/pause`;
        if(action === 'resume') endpoint = `/api/tasks/${task.id}/resume`;
        if(action === 'cancel') endpoint = `/api/tasks/${task.id}/cancel`;
        
        if(endpoint) {
            axios.post(endpoint)
                .then(res => {
                    if(window.Nexus && window.Nexus.notify) {
                        Nexus.notify(`Task action ${action} successful`, 'success');
                    }
                    this.fetchTasks();
                })
                .catch(err => {
                    console.error(err);
                    if(window.Nexus && window.Nexus.notify) {
                        Nexus.notify(`Action failed`, 'error');
                    }
                });
        }
        
        if (action === 'delete') {
            if(confirm('Are you sure you want to delete this task?')) {
                axios.delete(`/api/tasks/${task.id}`)
                    .then(() => this.fetchTasks())
                    .catch(err => console.error(err));
            }
        }
    }
  }
}
</script>

<style scoped>
.kanban-col {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    padding: 16px;
    min-height: 500px;
    display: flex;
    flex-direction: column;
}
.kanban-col-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--glass-border);
    margin-bottom: 14px;
}
.kanban-col-title {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 600;
}
.kanban-col-count {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.65rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
    background: rgba(255,255,255,0.06);
    color: var(--text-muted);
}
.min-h-200 {
    min-height: 200px;
}
</style>
