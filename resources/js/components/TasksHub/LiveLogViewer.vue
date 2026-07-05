<template>
  <div class="live-log-viewer">
    <div class="log-header d-flex justify-content-between align-items-center">
      <span><i class="fa-solid fa-terminal me-2"></i>Task Execution Logs</span>
      <span class="badge" :class="status === 'running' ? 'bg-success' : 'bg-secondary'">{{ status }}</span>
    </div>
    <div class="log-body" ref="logBody">
      <div v-for="(log, idx) in logs" :key="idx" class="log-line">
        <span class="log-time">[{{ formatTime(log.created_at) }}]</span>
        <span class="log-level" :class="'level-' + (log.level || 'info').toLowerCase()">[{{ log.level || 'INFO' }}]</span>
        <span class="log-msg">{{ log.message }}</span>
      </div>
      <div v-if="logs.length === 0" class="text-muted text-center mt-4">
        No logs available for this task.
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    taskId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      logs: [],
      status: 'loading'
    }
  },
  mounted() {
    this.fetchLogs();
    
    // Subscribe to task channel
    if (window.Echo) {
      window.Echo.private(`task.${this.taskId}`)
        .listen('TaskLogAdded', (e) => {
          this.logs.push(e.log);
          this.scrollToBottom();
        })
        .listen('TaskStatusUpdated', (e) => {
          this.status = e.status;
        });
    }
  },
  beforeUnmount() {
    if (window.Echo) {
      window.Echo.leave(`task.${this.taskId}`);
    }
  },
  methods: {
    fetchLogs() {
      axios.get(`/api/tasks/${this.taskId}/logs`)
        .then(res => {
          this.logs = res.data.data || [];
          this.status = 'loaded';
          this.scrollToBottom();
        })
        .catch(err => {
          console.error(err);
          this.status = 'error';
        });
    },
    formatTime(dateStr) {
      if(!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleTimeString([], {hour12: false});
    },
    scrollToBottom() {
      this.$nextTick(() => {
        if (this.$refs.logBody) {
          this.$refs.logBody.scrollTop = this.$refs.logBody.scrollHeight;
        }
      });
    }
  }
}
</script>

<style scoped>
.live-log-viewer {
  background: #0f172a;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 400px;
}
.log-header {
  background: #1e293b;
  padding: 8px 16px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.8rem;
  color: #cbd5e1;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.log-body {
  flex: 1;
  padding: 12px;
  overflow-y: auto;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.75rem;
  line-height: 1.5;
  color: #e2e8f0;
}
.log-body::-webkit-scrollbar { width: 6px; }
.log-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

.log-line {
  margin-bottom: 4px;
}
.log-time { color: #64748b; margin-right: 8px; }
.log-level { margin-right: 8px; font-weight: bold; }
.level-info { color: #38bdf8; }
.level-error { color: #f87171; }
.level-warning { color: #fbbf24; }
.level-success { color: #34d399; }
</style>
