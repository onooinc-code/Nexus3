<template>
  <div class="d-flex align-items-center justify-content-between h-100 px-4 text-light" style="font-size: 0.75rem;">
    <div class="d-flex align-items-center gap-4">
      <div class="d-flex align-items-center gap-2">
        <div class="status-indicator" :class="healthClass"></div>
        <span class="text-muted text-uppercase tracking-wider">Queue Health:</span>
        <span class="fw-bold">{{ healthText }}</span>
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <span title="Running Tasks"><i class="fa-solid fa-spinner fa-spin text-info me-1"></i> {{ stats.running || 0 }}</span>
        <span title="Pending Tasks"><i class="fa-solid fa-hourglass-half text-warning me-1"></i> {{ stats.pending || 0 }}</span>
        <span title="Failed Tasks"><i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> {{ stats.failed || 0 }}</span>
      </div>
    </div>
    
    <div class="d-flex align-items-center gap-2 text-muted">
      <span><i class="fa-solid fa-bolt text-warning"></i> Real-time Active</span>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      stats: {
        pending: 0,
        running: 0,
        failed: 0,
        completed: 0
      },
      queueStats: {}
    }
  },
  computed: {
    healthClass() {
      if(this.stats.failed > 5 || this.stats.pending > 50) return 'bg-danger';
      if(this.stats.pending > 10) return 'bg-warning';
      return 'bg-success';
    },
    healthText() {
      if(this.stats.failed > 5 || this.stats.pending > 50) return 'CRITICAL';
      if(this.stats.pending > 10) return 'BUSY';
      return 'HEALTHY';
    }
  },
  mounted() {
    this.fetchStats();
    
    // Fallback polling just in case Echo isn't updating fast enough
    setInterval(this.fetchStats, 30000);
    
    // Subscribe to task channel
    if (window.Echo) {
      window.Echo.channel('tasks')
        .listen('TaskStatusUpdated', (e) => {
          this.fetchStats();
        });
    }
  },
  beforeUnmount() {
    if (window.Echo) {
      window.Echo.leave('tasks');
    }
  },
  methods: {
    fetchStats() {
      axios.get('/api/tasks/stats')
        .then(res => {
          this.stats = res.data.data;
          this.queueStats = res.data.data.queue_stats || {};
        })
        .catch(err => console.error("Failed fetching stats", err));
    }
  }
}
</script>

<style scoped>
.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  box-shadow: 0 0 8px currentColor;
}
.tracking-wider {
  letter-spacing: 0.05em;
}
</style>
