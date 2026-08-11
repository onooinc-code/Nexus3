/**
 * Nexus Popup — Live Status Controller
 * Version: 1.2.0
 */
'use strict';

const statusDot  = document.getElementById('statusDot');
const statusText = document.getElementById('statusText');
const agentTab   = document.getElementById('agentTab');
const inFlight   = document.getElementById('inFlight');
const baseUrl    = document.getElementById('baseUrl');
const swState    = document.getElementById('swState');

function refreshStatus() {
  chrome.runtime.sendMessage({ source: 'NEXUS_POPUP', type: 'GET_STATUS' }, (res) => {
    if (chrome.runtime.lastError || !res) {
      statusDot.className = 'dot red';
      statusText.textContent = 'Service Worker غير متصل';
      swState.textContent = 'OFFLINE';
      return;
    }
    statusDot.className = 'dot green';
    statusText.textContent = 'متصل بـ Service Worker';
    agentTab.textContent = res.agentTabId ? `Tab #${res.agentTabId}` : 'لا يوجد';
    inFlight.textContent = res.inFlightCount || 0;
    baseUrl.textContent  = res.config?.NEXUS_BASE_URL || '—';
    swState.textContent  = res.isProcessing ? 'يعمل ⚙️' : 'جاهز ✅';
  });
}

document.getElementById('forcePollBtn').addEventListener('click', () => {
  chrome.runtime.sendMessage({ source: 'NEXUS_POPUP', type: 'FORCE_POLL' }, () => {
    refreshStatus();
  });
});

document.getElementById('refreshBtn').addEventListener('click', refreshStatus);

// Initial load
refreshStatus();