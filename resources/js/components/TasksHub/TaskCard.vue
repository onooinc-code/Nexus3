<template>
  <div class="task-card" :class="priorityClass">
    <div class="d-flex justify-content-between align-items-start mb-2">
      <span class="priority-badge" :class="task.priority_name">{{ task.priority_name || 'NORMAL' }}</span>
      <div class="dropdown">
        <button class="btn btn-sm text-muted p-0 border-0" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm bg-dark border-secondary">
          <li><a class="dropdown-item text-light" href="#" @click.prevent="viewTask"><i class="fa-solid fa-eye me-2 text-primary"></i>View Details</a></li>
          <li><hr class="dropdown-divider bg-secondary"></li>
          <li><a class="dropdown-item text-light" href="#" @click.prevent="$emit('action', {action: 'execute', task})"><i class="fa-solid fa-play me-2 text-success"></i>Run Now</a></li>
          <li><a class="dropdown-item text-light" href="#" @click.prevent="$emit('action', {action: 'pause', task})"><i class="fa-solid fa-pause me-2 text-warning"></i>Pause</a></li>
          <li><a class="dropdown-item text-light" href="#" @click.prevent="$emit('action', {action: 'resume', task})"><i class="fa-solid fa-forward-step me-2 text-info"></i>Resume</a></li>
          <li><hr class="dropdown-divider bg-secondary"></li>
          <li><a class="dropdown-item text-light" href="#" @click.prevent="$emit('action', {action: 'cancel', task})"><i class="fa-solid fa-stop me-2 text-warning"></i>Cancel</a></li>
          <li><a class="dropdown-item text-danger" href="#" @click.prevent="$emit('action', {action: 'delete', task})"><i class="fa-solid fa-trash me-2"></i>Delete</a></li>
        </ul>
      </div>
    </div>
    
    <h6 class="text-white mb-1" style="font-size: 0.85rem;">{{ task.title }}</h6>
    <p class="text-muted mb-2" style="font-size: 0.7rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
      {{ task.description || 'No description provided.' }}
    </p>
    
    <div class="d-flex justify-content-between align-items-center mt-3">
        <span class="badge bg-secondary text-light" style="font-size: 0.6rem;">{{ task.type }}</span>
        <small class="text-muted" style="font-size: 0.65rem;">
            <i class="fa-regular fa-clock me-1"></i>
            {{ formatDate(task.created_at) }}
        </small>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    task: Object
  },
  computed: {
    priorityClass() {
      const p = this.task.priority || 5;
      if (p >= 8) return 'priority-critical';
      if (p >= 5) return 'priority-medium';
      return 'priority-low';
    }
  },
  methods: {
    formatDate(dateStr) {
      if(!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    },
    viewTask() {
        window.location.href = `/hub/tasks/${this.task.id}`;
    }
  }
}
</script>

<style scoped>
.task-card {
    background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(30,41,59,0.7) 100%);
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    position: relative;
    cursor: pointer;
}
.task-card:hover {
    border-color: var(--nexus-blue, #3b82f6);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}
.priority-critical { border-left: 3px solid #ef4444; }
.priority-high     { border-left: 3px solid #f97316; }
.priority-medium   { border-left: 3px solid #eab308; }
.priority-low      { border-left: 3px solid #14b8a6; }
.priority-none     { border-left: 3px solid var(--glass-border); }

.priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 0.6rem;
    font-family: 'JetBrains Mono', monospace;
    text-transform: uppercase;
    font-weight: 600;
    background: rgba(255,255,255,0.1);
}
</style>
