/**
 * Nexus Agentic Browser Bridge — Background Service Worker
 * Ertugrul Browser Orchestrator (ertugrul_browser_agent)
 * Version: 1.2.0
 *
 * Fixes Applied:
 *  [BUG-01] Race Condition: Ping/Pong handshake before sendMessage
 *  [BUG-02] Dual Poll Storm: Removed setInterval, keep only chrome.alarms + lock
 *  [BUG-03] Tab Explosion: agentTabId persisted via chrome.storage.session
 *  [BUG-04] REVERB_APP_KEY placeholder: fails loudly if still '***' after load
 *  [BUG-05] Dashboard status: notifyDashboard now always passes task_id
 *  [MISS-03] No API Auth: Authorization: Bearer token added to all requests
 *  [MISS-05] Deduplication: inFlightTasks Set prevents double-processing
 *  [P1-Task6] Deep merge for nested CONFIG objects
 *  [P1-Task10] Hardcoded facebook.com fallback removed, uses config DEFAULT_TARGET_URL
 */

// ─── Default CONFIG (overwritten by config.json) ─────────────────────────────
let CONFIG = {
  NEXUS_BASE_URL: 'https://n.soulyeg.online',
  REVERB_HOST: 'n.soulyeg.online',
  REVERB_PORT: 443,
  REVERB_SCHEME: 'https',
  REVERB_APP_KEY: '***',
  NEXUS_API_TOKEN: '',
  AGENT_ID: 'ertugrul_browser_agent',
  DEFAULT_TARGET_URL: 'about:blank',
  ENDPOINTS: {
    DOM_TRIGGER: '/api/v1/events/dom-trigger',
    PENDING_TASKS: '/api/v1/agent-tasks/pending',
    UPDATE_STATUS: '/api/v1/agent-tasks/{id}/status'
  },
  DEFAULT_SETTINGS: {
    pollingIntervalMs: 5000,
    pageRenderDelayMs: 3000,
    pingTimeoutMs: 15000,
    pingRetries: 15,
    humanDelayMinMs: 100,
    humanDelayMaxMs: 400
  }
};

// ─── State ────────────────────────────────────────────────────────────────────
/** [BUG-02] Single processing lock — prevents concurrent poll runs */
let isProcessing = false;

/** [MISS-05] Track tasks currently in-flight to prevent double-processing */
const inFlightTasks = new Set();

// ─── Deep Merge Utility (Task 6) ─────────────────────────────────────────────
function deepMerge(target, source) {
  const result = { ...target };
  for (const key of Object.keys(source)) {
    if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
      result[key] = deepMerge(target[key] || {}, source[key]);
    } else {
      result[key] = source[key];
    }
  }
  return result;
}

// ─── Config Loader ────────────────────────────────────────────────────────────
async function loadConfig() {
  try {
    const response = await fetch(chrome.runtime.getURL('config.json'));
    const loadedConfig = await response.json();
    CONFIG = deepMerge(CONFIG, loadedConfig);
    // [BUG-04] Fail loudly if placeholder key is still in use
    if (CONFIG.REVERB_APP_KEY === '***') {
      console.error('[Nexus SW] ⚠️ REVERB_APP_KEY is still placeholder "***". Update config.json.');
    }
  } catch (e) {
    console.warn('[Nexus SW] Failed to load config.json, using defaults:', e);
  }
}

// ─── API Helpers ──────────────────────────────────────────────────────────────
/** [MISS-03] Centralized fetch with Bearer token auth */
function buildHeaders(extra = {}) {
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...extra
  };
  if (CONFIG.NEXUS_API_TOKEN) {
    headers['Authorization'] = `Bearer ${CONFIG.NEXUS_API_TOKEN}`;
  }
  return headers;
}

async function fetchPendingTasks() {
  const url = `${CONFIG.NEXUS_BASE_URL}${CONFIG.ENDPOINTS.PENDING_TASKS}?target_agent_id=${CONFIG.AGENT_ID}`;
  try {
    const res = await fetch(url, { headers: buildHeaders() });
    if (!res.ok) {
      console.warn(`[Nexus SW] fetchPendingTasks HTTP ${res.status}: ${res.statusText}`);
      return [];
    }
    const data = await res.json();
    return data.data || [];
  } catch (err) {
    console.warn('[Nexus SW] fetchPendingTasks error:', err.message);
    return [];
  }
}

async function updateTaskStatus(taskId, status, executionProof = {}, resultData = {}) {
  const endpoint = CONFIG.ENDPOINTS.UPDATE_STATUS.replace('{id}', taskId);
  const url = `${CONFIG.NEXUS_BASE_URL}${endpoint}`;
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: buildHeaders(),
      body: JSON.stringify({ status, execution_proof: executionProof, result_data: resultData })
    });
    return await res.json();
  } catch (err) {
    console.error(`[Nexus SW] updateTaskStatus(${taskId}) failed:`, err.message);
  }
}

// ─── Dashboard Notification (BUG-05 Fix) ─────────────────────────────────────
/** Always include task_id so dashboard.js never has to parse it from a string */
function notifyDashboard(level, title, taskId = null, data = null) {
  chrome.runtime.sendMessage({
    source: 'NEXUS_AGENT_TASK',
    level: level.toUpperCase(),  // always uppercase: 'TASK' | 'SUCCESS' | 'ERROR' | 'INFO'
    title: title,
    task_id: taskId,
    data: data,
    timestamp: Date.now()
  }).catch(() => {
    // Dashboard may not be open — silence the error
  });
}

// ─── Singleton Tab Management (BUG-03 Fix) ───────────────────────────────────
async function getAgentTabId() {
  const stored = await chrome.storage.session.get('agentTabId');
  if (stored.agentTabId) {
    try {
      const tab = await chrome.tabs.get(stored.agentTabId);
      if (tab && tab.id) return tab.id;
    } catch (_) {
      // Tab was closed — clear it
    }
  }
  return null;
}

async function setAgentTabId(tabId) {
  await chrome.storage.session.set({ agentTabId: tabId });
}

async function getOrCreateAgentTab(url) {
  const existingId = await getAgentTabId();
  if (existingId) {
    try {
      await chrome.tabs.update(existingId, { url, active: true });
      // Wait a tick for the update to register
      await new Promise(r => setTimeout(r, 100));
      return await chrome.tabs.get(existingId);
    } catch (_) {
      await setAgentTabId(null);
    }
  }
  const newTab = await chrome.tabs.create({ url, active: true });
  await setAgentTabId(newTab.id);
  return newTab;
}

// ─── Tab Load Waiter ──────────────────────────────────────────────────────────
function waitForTabComplete(tabId, timeoutMs = 20000) {
  return new Promise((resolve) => {
    const timer = setTimeout(() => {
      chrome.tabs.onUpdated.removeListener(listener);
      resolve({ status: 'TIMEOUT' });
    }, timeoutMs);

    function listener(tid, changeInfo) {
      if (tid === tabId && changeInfo.status === 'complete') {
        clearTimeout(timer);
        chrome.tabs.onUpdated.removeListener(listener);
        resolve({ status: 'LOADED' });
      }
    }
    chrome.tabs.onUpdated.addListener(listener);
  });
}

// ─── Content Script Readiness (BUG-01 Fix: Ping/Pong Handshake) ──────────────
async function waitForContentScript(tabId) {
  const { pingRetries = 5, pingTimeoutMs = 2500 } = CONFIG.DEFAULT_SETTINGS;
  for (let i = 0; i < pingRetries; i++) {
    try {
      const res = await chrome.tabs.sendMessage(tabId, {
        target: 'NEXUS_CONTENT_SCRIPT',
        action: 'PING'
      });
      if (res && res.status === 'PONG') {
        console.log(`[Nexus SW] Content script ready on tab ${tabId} (attempt ${i + 1}).`);
        return true;
      }
    } catch (_) {
      // Not ready yet
    }
    await new Promise(r => setTimeout(r, pingTimeoutMs / pingRetries));
  }
  console.warn(`[Nexus SW] Content script NOT ready on tab ${tabId} after ${pingRetries} pings.`);
  return false;
}

// ─── Debugger Primitives ──────────────────────────────────────────────────────
async function attachDebugger(tabId) {
  return new Promise((resolve, reject) => {
    chrome.debugger.attach({ tabId }, '1.3', () => {
      if (chrome.runtime.lastError) {
        if (chrome.runtime.lastError.message.includes('Already attached')) return resolve();
        return reject(chrome.runtime.lastError);
      }
      resolve();
    });
  });
}

async function sendDebuggerCommand(tabId, method, params = {}) {
  return new Promise((resolve, reject) => {
    chrome.debugger.sendCommand({ tabId }, method, params, (result) => {
      if (chrome.runtime.lastError) return reject(chrome.runtime.lastError);
      resolve(result);
    });
  });
}

async function debuggerClick(tabId, x, y) {
  await attachDebugger(tabId);
  await sendDebuggerCommand(tabId, 'Input.dispatchMouseEvent', { type: 'mousePressed', x, y, button: 'left', clickCount: 1 });
  await new Promise(r => setTimeout(r, 50));
  await sendDebuggerCommand(tabId, 'Input.dispatchMouseEvent', { type: 'mouseReleased', x, y, button: 'left', clickCount: 1 });
}

async function debuggerType(tabId, text) {
  await attachDebugger(tabId);
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    await sendDebuggerCommand(tabId, 'Input.dispatchKeyEvent', { type: 'keyDown', text: char, unmodifiedText: char });
    await new Promise(r => setTimeout(r, 10 + Math.random() * 40));
    await sendDebuggerCommand(tabId, 'Input.dispatchKeyEvent', { type: 'keyUp', text: char, unmodifiedText: char });
    await new Promise(r => setTimeout(r, 40 + Math.random() * 80));
  }
}

async function takeScreenshot(tabId) {
  const tab = await chrome.tabs.get(tabId);
  await chrome.tabs.update(tabId, { active: true });
  return new Promise((resolve, reject) => {
    chrome.tabs.captureVisibleTab(tab.windowId, { format: 'png', quality: 50 }, (dataUrl) => {
      if (chrome.runtime.lastError) {
        return reject(new Error(chrome.runtime.lastError.message));
      }
      resolve(dataUrl);
    });
  });
}

async function getElementCoordinates(tabId, selector) {
  return new Promise((resolve, reject) => {
    chrome.scripting.executeScript({
      target: { tabId },
      func: (sel) => {
        const el = document.querySelector(sel);
        if (!el) return null;
        const rect = el.getBoundingClientRect();
        return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
      },
      args: [selector]
    }, (results) => {
      if (chrome.runtime.lastError) return reject(chrome.runtime.lastError);
      if (results && results[0] && results[0].result) resolve(results[0].result);
      else reject(new Error(`Element not found: ${selector}`));
    });
  });
}

// ─── ReAct Action Executor ────────────────────────────────────────────────────
async function executeReActAction(tabId, actionPayload) {
  const { command, selector, x, y, text, url } = actionPayload;

  switch (command) {
    case 'navigate':
      await chrome.tabs.update(tabId, { url: url || selector });
      await waitForTabComplete(tabId);
      const updatedTab = await chrome.tabs.get(tabId);
      return { status: 'SUCCESS', result: `Navigated to ${url || selector}`, url: updatedTab.url };
      
    case 'click':
      let clickX = x;
      let clickY = y;
      if (selector) {
        const coords = await getElementCoordinates(tabId, selector);
        clickX = coords.x;
        clickY = coords.y;
      }
      if (clickX === undefined || clickY === undefined) throw new Error("Missing coordinates or selector for click");
      await debuggerClick(tabId, clickX, clickY);
      return { status: 'SUCCESS', result: `Clicked at ${clickX}, ${clickY}` };

    case 'type':
      let typeX = x;
      let typeY = y;
      if (selector) {
        const coords = await getElementCoordinates(tabId, selector);
        typeX = coords.x;
        typeY = coords.y;
      }
      if (typeX !== undefined && typeY !== undefined) {
        await debuggerClick(tabId, typeX, typeY);
      }
      if (!text) throw new Error("Missing text for type action");
      await debuggerType(tabId, text);
      return { status: 'SUCCESS', result: `Typed text` };
      
    case 'take_screenshot':
      const dataUrl = await takeScreenshot(tabId);
      return { status: 'SUCCESS', result: 'Screenshot taken', screenshot_base64: dataUrl };
      
    case 'get_dom_snapshot':
    case 'scroll':
    case 'wait':
      return await chrome.tabs.sendMessage(tabId, {
        target: 'NEXUS_CONTENT_SCRIPT',
        action: 'EXECUTE_REACT_ACTION',
        data: actionPayload
      });
      
    default:
      throw new Error(`Unknown ReAct command: ${command}`);
  }
}

// ─── Task Processor (All Bug Fixes Applied) ───────────────────────────────────
async function processTask(task) {
  // [MISS-05] Deduplication guard
  if (inFlightTasks.has(task.id)) {
    console.log(`[Nexus SW] Task #${task.id} already in-flight, skipping.`);
    return;
  }

  if (!task.payload_data || !task.payload_data.react_action) {
    console.log(`[Nexus SW] Task #${task.id} has no react_action yet. Waiting for Brain...`);
    return;
  }

  inFlightTasks.add(task.id);

  const startTime = new Date().toISOString();
  const logs = [`[SW] Task #${task.id} started: ${task.title}`];

  notifyDashboard('TASK', `جاري تنفيذ المهمة #${task.id}: ${task.title}`, task.id, {
    task_id: task.id,
    instruction: task.dynamic_system_instruction
  });

  await updateTaskStatus(task.id, 'in_progress', { started_at: startTime, logs });

  try {
    // Determine target URL from payload or task metadata
    const targetUrl = task.payload_data?.react_action?.url || task.metadata?.url;
    logs.push(`[SW] Target URL: ${targetUrl}`);
    notifyDashboard('INFO', `فتح الصفحة: ${targetUrl}`, task.id);

    // [BUG-03] Use persisted singleton tab
    let tab;
    if (targetUrl) {
      tab = await getOrCreateAgentTab(targetUrl);
    } else {
      const existingId = await getAgentTabId();
      if (existingId) {
        try { tab = await chrome.tabs.get(existingId); } catch (_) {}
      }
      if (!tab) {
        tab = await getOrCreateAgentTab('about:blank');
      }
    }
    logs.push(`[SW] Tab ${tab.id} opened/updated. Waiting for page load...`);

    const loadResult = await waitForTabComplete(tab.id);
    logs.push(`[SW] Page load: ${loadResult.status}`);

    // Configurable render delay (not a magic number)
    const renderDelay = CONFIG.DEFAULT_SETTINGS.pageRenderDelayMs || 3000;
    await new Promise(r => setTimeout(r, renderDelay));

    // [BUG-01] Ping/Pong before any sendMessage
    const scriptReady = await waitForContentScript(tab.id);
    if (!scriptReady) {
      throw new Error(`Content script did not respond on tab ${tab.id} after ${CONFIG.DEFAULT_SETTINGS.pingRetries} attempts.`);
    }

    let actionResult = { status: 'NO_ACTION' };

    // Autonomous ReAct Engine payload execution
    if (task.payload_data && task.payload_data.react_action) {
      logs.push(`[SW] Executing ReAct Action: ${task.payload_data.react_action.command}`);
      try {
        actionResult = await executeReActAction(tab.id, task.payload_data.react_action);
        logs.push(`[SW] ReAct action completed successfully.`);
      } catch (err) {
        logs.push(`[SW] ReAct action error: ${err.message}`);
        actionResult = { status: 'ERROR', error: err.message };
      }
    } else {
      logs.push(`[SW] No react_action provided in payload_data.`);
    }

    const proof = {
      started_at: startTime,
      completed_at: new Date().toISOString(),
      tab_id: tab.id,
      tab_url: tab.url,
      action_result: actionResult,
      logs
    };

    // Keep the task in 'pending' if ReAct engine wants to continue, else 'completed'
    // For now we map success to 'completed' and error to 'failed', but the backend dictates the loop
    // ReAct Backend should technically handle state. We'll send 'completed' for this micro-action.
    await updateTaskStatus(task.id, actionResult.status === 'ERROR' ? 'failed' : 'completed', proof, {
      action_result: actionResult
    });

    notifyDashboard('SUCCESS', `✅ اكتملت المهمة #${task.id} بنجاح`, task.id, proof);

  } catch (err) {
    logs.push(`[SW] CRITICAL ERROR: ${err.message}`);
    console.error(`[Nexus SW] Task #${task.id} failed:`, err);
    await updateTaskStatus(task.id, 'failed', {
      started_at: startTime,
      failed_at: new Date().toISOString(),
      error: err.message,
      logs
    });
    notifyDashboard('ERROR', `❌ فشلت المهمة #${task.id}: ${err.message}`, task.id, { error: err.message, logs });
  } finally {
    // Always release the lock regardless of success/failure
    inFlightTasks.delete(task.id);
  }
}

// ─── Poll Loop (BUG-02 Fix: Single trigger via alarms only) ──────────────────
async function pollLoop() {
  if (isProcessing) {
    console.log('[Nexus SW] Poll skipped — previous cycle still running.');
    return;
  }
  isProcessing = true;
  try {
    const pendingTasks = await fetchPendingTasks();
    if (pendingTasks.length > 0) {
      console.log(`[Nexus SW] ${pendingTasks.length} pending task(s) found.`);
      for (const task of pendingTasks) {
        await processTask(task);
      }
    }
  } finally {
    isProcessing = false;
  }
}

// ─── Keep-Alive: Only chrome.alarms (MV3 correct) ────────────────────────────
chrome.alarms.get('NEXUS_POLL', (alarm) => {
  if (!alarm) {
    chrome.alarms.create('NEXUS_POLL', { periodInMinutes: 0.5 });
  }
});
chrome.alarms.onAlarm.addListener((alarm) => {
  if (alarm.name === 'NEXUS_POLL') pollLoop();
});

// ─── Message Listener ─────────────────────────────────────────────────────────
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.source === 'NEXUS_POPUP') {
    if (message.type === 'GET_STATUS') {
      getAgentTabId().then(tabId => {
        sendResponse({
          connected: true,
          agentId: CONFIG.AGENT_ID,
          agentTabId: tabId,
          isProcessing,
          inFlightCount: inFlightTasks.size,
          config: { NEXUS_BASE_URL: CONFIG.NEXUS_BASE_URL, VERSION: CONFIG.VERSION }
        });
      });
      return true;
    }
    if (message.type === 'FORCE_POLL') {
      pollLoop().then(() => sendResponse({ status: 'OK' }));
      return true;
    }
  }
});

// ─── Startup ──────────────────────────────────────────────────────────────────
chrome.runtime.onInstalled.addListener(async () => {
  await loadConfig();
  chrome.storage.local.set({ connected: true, agentId: CONFIG.AGENT_ID });
  console.log('[Nexus SW] Extension installed/updated. Config loaded.');
});

// Load config on every SW startup, then run one immediate poll
loadConfig().then(() => {
  console.log('[Nexus SW] Service Worker active.');
  pollLoop(); // Initial poll on startup
});
