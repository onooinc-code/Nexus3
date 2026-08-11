/**
 * Nexus Ertugrul — Dashboard Controller
 * Version: 1.2.0
 *
 * Fixes:
 *  [BUG-05] Status case mismatch fixed — compares .toUpperCase()
 *  [BUG-07] Task ID always from msg.task_id field, no regex parsing
 *  [Task-11] Pagination — 20 tasks per page
 *  [Task-12] Manual controls — Pause/Resume, Force Poll
 *  [Task-13] Posts Preview modal
 *  [Task-12] WebSocket/SW health status indicator
 */
'use strict';

// ─── State ────────────────────────────────────────────────────────────────────
const tasksMap = new Map();   // taskId (string) → TaskObject
let isPaused = false;
let currentPage = 1;
const PAGE_SIZE = 20;

// API Config
let apiBaseUrl = '';
let apiToken = '';

// ─── Toast Notifications ──────────────────────────────────────────────────────
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;

  let icon = 'ℹ️';
  if (type === 'success') icon = '✅';
  if (type === 'error') icon = '❌';

  toast.innerHTML = `
    <div class="toast-icon">${icon}</div>
    <div class="toast-content">${message}</div>
    <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
  `;

  container.appendChild(toast);

  // Trigger animation
  requestAnimationFrame(() => {
    toast.classList.add('show');
  });

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// ─── DOM References ───────────────────────────────────────────────────────────
const taskListEl    = document.getElementById('taskList');
const searchInput   = document.getElementById('searchInput');
const statusFilter  = document.getElementById('statusFilter');
const prevPageBtn   = document.getElementById('prevPage');
const nextPageBtn   = document.getElementById('nextPage');
const pageInfoEl    = document.getElementById('pageInfo');
const statTotal     = document.getElementById('statTotal');
const statSuccess   = document.getElementById('statSuccess');
const statError     = document.getElementById('statError');
const statRunning   = document.getElementById('statRunning');
const statPage      = document.getElementById('statPage');
const statusDot     = document.getElementById('statusDot');
const statusText    = document.getElementById('statusText');
const postsModal    = document.getElementById('postsModal');
const postsModalBody = document.getElementById('postsModalBody');

const taskManageModal = document.getElementById('taskManageModal');
const manageTaskId = document.getElementById('manageTaskId');
const manageTaskInstruction = document.getElementById('manageTaskInstruction');
const taskModalTitle = document.getElementById('taskModalTitle');

// ─── Utility ──────────────────────────────────────────────────────────────────
function escapeHtml(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(String(str || '')));
  return d.innerHTML;
}

function fmtTime(ts) {
  return new Date(ts).toLocaleTimeString('ar-EG');
}

function levelColor(level) {
  const map = {
    SUCCESS: 'level-SUCCESS',
    ERROR:   'level-ERROR',
    INFO:    'level-INFO',
    TASK:    'level-TASK',
    WARN:    'level-WARN',
  };
  return map[(level || '').toUpperCase()] || 'level-INFO';
}

// ─── Stats ────────────────────────────────────────────────────────────────────
function updateStats(tasks) {
  let success = 0, error = 0, running = 0;
  for (const t of tasks) {
    const s = (t.status || '').toUpperCase();
    if (s === 'SUCCESS') success++;
    else if (s === 'ERROR') error++;
    else if (s === 'IN_PROGRESS') running++;
  }
  statTotal.textContent   = tasks.length;
  statSuccess.textContent = success;
  statError.textContent   = error;
  statRunning.textContent = running;
  statPage.textContent    = currentPage;
}

// ─── Filter & Pagination ──────────────────────────────────────────────────────
function getFilteredTasks() {
  const query  = searchInput.value.trim().toLowerCase();
  const filter = statusFilter.value.toUpperCase();

  let tasks = Array.from(tasksMap.values())
    .sort((a, b) => b.updatedAt - a.updatedAt);

  if (query) {
    tasks = tasks.filter(t =>
      (t.title || '').toLowerCase().includes(query) ||
      String(t.id).includes(query)
    );
  }

  if (filter !== 'ALL') {
    tasks = tasks.filter(t => (t.status || '').toUpperCase() === filter);
  }

  return tasks;
}

// ─── Render ───────────────────────────────────────────────────────────────────
function renderTasks() {
  const allFiltered = getFilteredTasks();
  const totalPages  = Math.max(1, Math.ceil(allFiltered.length / PAGE_SIZE));

  // Clamp page
  if (currentPage > totalPages) currentPage = totalPages;

  const start = (currentPage - 1) * PAGE_SIZE;
  const page  = allFiltered.slice(start, start + PAGE_SIZE);

  updateStats(allFiltered);

  // Pagination controls
  prevPageBtn.disabled = currentPage <= 1;
  nextPageBtn.disabled = currentPage >= totalPages;
  pageInfoEl.textContent = `صفحة ${currentPage} / ${totalPages}`;

  // Render cards
  taskListEl.innerHTML = '';

  if (page.length === 0) {
    taskListEl.innerHTML = `
      <div class="empty-state">
        <div class="icon">📭</div>
        <div>لا توجد مهام حتى الآن. في انتظار التعليمات من Nexus...</div>
      </div>`;
    return;
  }

  for (const task of page) {
    const statusUpper = (task.status || 'TASK').toUpperCase();
    const card = document.createElement('div');
    card.className = `task-card status-${statusUpper}${task.expanded ? ' expanded' : ''}`;
    card.dataset.taskId = task.id;

    let logsHtml = '';
    
    if (task.reactSteps && task.reactSteps.length > 0) {
      logsHtml = task.reactSteps.map(step => {
        const actionHtml = step.action_sent ? `<div class="react-action">⚡ <strong>${step.action_sent.command}</strong>: ${escapeHtml(JSON.stringify(step.action_sent))}</div>` : '';
        const obsHtml = step.observation_received ? `<div class="react-obs">👁️ <strong>Observation</strong>: <pre>${escapeHtml(JSON.stringify(step.observation_received, null, 2)).slice(0, 500)}</pre></div>` : '';
        return `
          <div class="react-step">
            <div class="react-thought">🧠 <strong>Step ${step.step_number}:</strong> ${escapeHtml(step.thought)}</div>
            ${actionHtml}
            ${obsHtml}
          </div>
        `;
      }).join('');
    } else {
      logsHtml = (task.logs || []).map(log => {
        const lvl = (log.level || 'INFO').toUpperCase();
        const dataHtml = log.data
          ? `<div class="log-data"><pre>${escapeHtml(JSON.stringify(log.data, null, 2)).slice(0, 2000)}</pre></div>`
          : '';
        return `
          <div class="log-entry">
            <span class="log-time">${fmtTime(log.timestamp)}</span>
            <span class="log-level ${levelColor(lvl)}">${lvl}</span>
            <span class="log-msg">${escapeHtml(log.message)}</span>
          </div>${dataHtml}`;
      }).join('');
    }

    const hasScrapedPosts = task.scrapedPosts && task.scrapedPosts.length > 0;

    card.innerHTML = `
      <div class="task-header">
        <span class="task-title-text">مهمة #${escapeHtml(task.id)}: ${escapeHtml(task.title)}</span>
        <div class="task-meta">
          <span>${task.logs.length} خطوة</span>
          <span class="badge badge-${statusUpper}">${statusUpper}</span>
          <span class="chevron">▼</span>
        </div>
      </div>
      <div class="task-logs">${logsHtml || '<div style="color:var(--muted)">لا توجد سجلات بعد.</div>'}</div>
      <div class="task-actions">
        ${hasScrapedPosts
          ? `<button class="btn btn-primary btn-posts" data-task-id="${task.id}">📋 عرض البوستات (${task.scrapedPosts.length})</button>`
          : ''}
        <button class="btn btn-ghost btn-collapse" data-task-id="${task.id}">إغلاق ▲</button>
        <div style="flex: 1;"></div>
        <button class="btn btn-warn btn-edit-task" data-task-id="${task.id}">تعديل</button>
        <button class="btn btn-danger btn-delete-task" data-task-id="${task.id}">حذف</button>
      </div>`;

    // Toggle expand/collapse on header click
    card.querySelector('.task-header').addEventListener('click', () => {
      task.expanded = !task.expanded;
      renderTasks();
    });

    // Collapse button
    card.querySelector('.btn-collapse')?.addEventListener('click', (e) => {
      e.stopPropagation();
      task.expanded = false;
      renderTasks();
    });

    // Posts preview button
    if (hasScrapedPosts) {
      card.querySelector('.btn-posts')?.addEventListener('click', (e) => {
        e.stopPropagation();
        openPostsModal(task.scrapedPosts);
      });
    }

    // Edit button
    card.querySelector('.btn-edit-task')?.addEventListener('click', (e) => {
      e.stopPropagation();
      openTaskModal(task.id);
    });

    // Delete button
    card.querySelector('.btn-delete-task')?.addEventListener('click', (e) => {
      e.stopPropagation();
      deleteTask(task.id);
    });

    taskListEl.appendChild(card);
  }
}

// ─── Posts Modal ──────────────────────────────────────────────────────────────
function openPostsModal(posts) {
  postsModalBody.innerHTML = '';
  posts.forEach((post, i) => {
    const item = document.createElement('div');
    item.className = 'post-item';
    const text = typeof post === 'object' ? post.text : post;
    const url  = typeof post === 'object' ? post.url  : '';
    item.innerHTML = `
      <div><strong>#${i + 1}</strong></div>
      <div>${escapeHtml(text)}</div>
      ${url ? `<div class="post-url">${escapeHtml(url)}</div>` : ''}`;
    postsModalBody.appendChild(item);
  });
  postsModal.classList.add('open');
}

document.getElementById('closeModal').addEventListener('click', () => {
  postsModal.classList.remove('open');
});

postsModal.addEventListener('click', (e) => {
  if (e.target === postsModal) postsModal.classList.remove('open');
});

// ─── Task Management Modal ────────────────────────────────────────────────────
function openTaskModal(taskId = null) {
  if (taskId) {
    const task = tasksMap.get(taskId);
    if (task) {
      taskModalTitle.textContent = '✏️ تعديل المهمة';
      manageTaskId.value = task.id;
      manageTaskInstruction.value = task.title;
    }
  } else {
    taskModalTitle.textContent = '✨ إضافة مهمة جديدة';
    manageTaskId.value = '';
    manageTaskInstruction.value = '';
  }
  taskManageModal.classList.add('open');
}

function closeTaskModal() {
  taskManageModal.classList.remove('open');
}

document.getElementById('createTaskBtn').addEventListener('click', () => openTaskModal());
document.getElementById('closeTaskModal').addEventListener('click', closeTaskModal);
document.getElementById('cancelTaskBtn').addEventListener('click', closeTaskModal);

taskManageModal.addEventListener('click', (e) => {
  if (e.target === taskManageModal) closeTaskModal();
});

document.getElementById('saveTaskBtn').addEventListener('click', async () => {
  const instruction = manageTaskInstruction.value.trim();
  const taskId = manageTaskId.value;
  if (!instruction) return showToast('برجاء كتابة التعليمة!', 'error');
  if (!apiBaseUrl || !apiToken) return showToast('لم يتم تحميل إعدادات الـ API بعد.', 'error');

  const saveBtn = document.getElementById('saveTaskBtn');
  const ogText = saveBtn.textContent;
  saveBtn.disabled = true;
  saveBtn.textContent = 'جاري الحفظ...';

  try {
    const isEdit = !!taskId;
    const url = isEdit ? `${apiBaseUrl}/api/v1/tasks/${taskId}` : `${apiBaseUrl}/api/v1/agent-tasks`;
    const method = isEdit ? 'PUT' : 'POST';

    const payload = {
      title: instruction,
      target_agent_id: 'ertugrul_browser_agent'
    };

    const res = await fetch(url, {
      method: method,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiToken}`
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      closeTaskModal();
      showToast('تم حفظ المهمة بنجاح!', 'success');
      await hydrateDashboard(); // Refresh tasks
    } else {
      const err = await res.json();
      showToast('فشل حفظ المهمة: ' + (err.message || err.error || 'خطأ غير معروف'), 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('حدث خطأ أثناء الاتصال بالخادم.', 'error');
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = ogText;
  }
});

async function deleteTask(taskId) {
  if (!confirm('هل أنت متأكد من حذف هذه المهمة نهائياً؟')) return;
  if (!apiBaseUrl || !apiToken) return showToast('لم يتم تحميل إعدادات الـ API بعد.', 'error');

  try {
    const res = await fetch(`${apiBaseUrl}/api/v1/tasks/${taskId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${apiToken}`
      }
    });
    if (res.ok || res.status === 404) {
      tasksMap.delete(taskId);
      renderTasks();
      showToast('تم حذف المهمة بنجاح.', 'success');
    } else {
      const err = await res.json();
      showToast('فشل حذف المهمة: ' + (err.message || 'خطأ غير معروف'), 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('حدث خطأ أثناء الحذف.', 'error');
  }
}

// ─── SW Status Checker ────────────────────────────────────────────────────────
function checkSWStatus() {
  chrome.runtime.sendMessage({ source: 'NEXUS_POPUP', type: 'GET_STATUS' }, (res) => {
    if (chrome.runtime.lastError || !res) {
      statusDot.className = 'dot red';
      statusText.textContent = 'غير متصل';
      return;
    }
    statusDot.className = 'dot green';
    statusText.textContent = `متصل — تبويب: ${res.agentTabId || 'لا يوجد'} | قيد التنفيذ: ${res.inFlightCount || 0}`;
  });
}

// Check status every 5 seconds
setInterval(checkSWStatus, 5000);
checkSWStatus(); // Immediate

// ─── Message Listener (BUG-05 Fixed) ─────────────────────────────────────────
chrome.runtime.onMessage.addListener((msg) => {
  if (isPaused) return;
  if (msg.source !== 'NEXUS_AGENT_TASK') return;

  // [BUG-07] Always use msg.task_id — never parse from title string
  const taskId = String(msg.task_id || msg.data?.task_id || 'SYSTEM_' + Date.now());
  const level  = (msg.level || 'INFO').toUpperCase(); // [BUG-05] Normalize to uppercase

  let task = tasksMap.get(taskId);
  if (!task) {
    task = {
      id: taskId,
      title: msg.title || 'مهمة بدون عنوان',
      status: 'TASK',
      logs: [],
      scrapedPosts: null,
      updatedAt: Date.now(),
      expanded: true
    };
    tasksMap.set(taskId, task);
  }

  // [BUG-05] Proper uppercase comparison for status transitions
  task.updatedAt = msg.timestamp || Date.now();
  if (level === 'SUCCESS') { task.status = 'SUCCESS'; task.expanded = false; }
  if (level === 'ERROR')   { task.status = 'ERROR'; }
  if (level === 'TASK' && task.status !== 'SUCCESS' && task.status !== 'ERROR') {
    task.status = 'IN_PROGRESS';
  }

  // [Task-13] Extract scraped posts from proof data
  if (msg.data?.scraped_posts) {
    task.scrapedPosts = msg.data.scraped_posts;
  }
  
  if (msg.data && Array.isArray(msg.data) && msg.data[0] && msg.data[0].thought) {
    task.reactSteps = msg.data;
  } else if (msg.data?.execution_proof && Array.isArray(msg.data.execution_proof) && msg.data.execution_proof[0]?.thought) {
    task.reactSteps = msg.data.execution_proof;
  }

  task.logs.push({
    level: level,
    message: msg.title || '',
    data: msg.data || null,
    timestamp: msg.timestamp || Date.now()
  });

  // Keep only last 200 log entries per task (prevent DOM overload)
  if (task.logs.length > 200) {
    task.logs = task.logs.slice(-200);
  }

  renderTasks();
});

// ─── Controls ─────────────────────────────────────────────────────────────────
document.getElementById('clearBtn').addEventListener('click', () => {
  if (confirm('هل تريد مسح كل السجلات؟')) {
    tasksMap.clear();
    currentPage = 1;
    renderTasks();
  }
});

document.getElementById('pauseBtn').addEventListener('click', function () {
  isPaused = !isPaused;
  this.textContent = isPaused ? '▶ استئناف' : '⏸ إيقاف مؤقت';
  this.classList.toggle('paused', isPaused);
});

document.getElementById('forcePolBtn').addEventListener('click', () => {
  chrome.runtime.sendMessage({ source: 'NEXUS_POPUP', type: 'FORCE_POLL' }, (res) => {
    if (res?.status === 'OK') {
      console.log('[Dashboard] Force poll triggered.');
    }
  });
});

// ─── Pagination ───────────────────────────────────────────────────────────────
prevPageBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTasks(); } });
nextPageBtn.addEventListener('click', () => { currentPage++; renderTasks(); });

// ─── Filter Listeners ─────────────────────────────────────────────────────────
searchInput.addEventListener('input',  () => { currentPage = 1; renderTasks(); });
statusFilter.addEventListener('change', () => { currentPage = 1; renderTasks(); });

// ─── Hydration ──────────────────────────────────────────────────────────────────
async function hydrateDashboard() {
  try {
    const configRes = await fetch(chrome.runtime.getURL('config.json'));
    const config = await configRes.json();
    apiToken = config.NEXUS_API_TOKEN;
    apiBaseUrl = config.NEXUS_BASE_URL;

    const res = await fetch(`${apiBaseUrl}/api/v1/agent-tasks/pending?target_agent_id=ertugrul_browser_agent`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${apiToken}`
      }
    });
    
    if (res.ok) {
      const payload = await res.json();
      const tasks = payload.data || [];
      tasks.forEach(task => {
        if (!tasksMap.has(String(task.id))) {
            let reactSteps = [];
            let logsArray = [];
            if (Array.isArray(task.execution_proof) && task.execution_proof.length > 0 && task.execution_proof[0].thought) {
                reactSteps = task.execution_proof;
            } else if (task.execution_proof && task.execution_proof.logs) {
                logsArray = task.execution_proof.logs;
            }

            tasksMap.set(String(task.id), {
              id: String(task.id),
              title: task.title,
              status: task.status === 'in-progress' || task.status === 'in_progress' ? 'IN_PROGRESS' : task.status.toUpperCase(),
              reactSteps: reactSteps,
              logs: logsArray.map(msg => ({
                level: 'INFO',
                message: msg,
                data: null,
                timestamp: new Date(task.updated_at).getTime()
              })),
              scrapedPosts: task.result_data?.scraped_posts || task.execution_proof?.scraped_posts || null,
              updatedAt: new Date(task.updated_at).getTime(),
              expanded: false
            });
        }
      });
      renderTasks();
    }
  } catch (err) {
    console.warn('[Dashboard] Hydration failed:', err);
  }
}

// ─── Initial Render ───────────────────────────────────────────────────────────
renderTasks();
hydrateDashboard();
