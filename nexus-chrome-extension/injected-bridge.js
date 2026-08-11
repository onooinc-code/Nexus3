/**
 * Nexus Injected Bridge — Page Context Script
 * Injected into the page's main world (NOT extension context)
 * Purpose: Relay page-level JS state and intercept XHR/fetch responses
 *
 * Communication channel: window.postMessage → content-observer.js
 */
(function () {
  'use strict';

  const BRIDGE_VERSION = '1.2.0';

  // Announce presence to content script via postMessage
  window.postMessage({ source: 'NEXUS_INJECTED_BRIDGE', type: 'READY', version: BRIDGE_VERSION }, '*');

  // Listen for commands from content script (relayed from background.js)
  window.addEventListener('message', (event) => {
    if (event.source !== window) return;
    if (!event.data || event.data.source !== 'NEXUS_CONTENT_BRIDGE') return;

    const { action, data, requestId } = event.data;

    // Reply helper
    function reply(payload) {
      window.postMessage({
        source: 'NEXUS_INJECTED_BRIDGE',
        type: 'RESPONSE',
        requestId: requestId,
        ...payload
      }, '*');
    }

    if (action === 'GET_PAGE_VARIABLES') {
      // Expose selected page-context globals safely
      const exposed = {};
      if (data && Array.isArray(data.keys)) {
        for (const key of data.keys) {
          try {
            exposed[key] = typeof window[key] !== 'undefined' ? String(window[key]).slice(0, 500) : undefined;
          } catch (_) {}
        }
      }
      reply({ status: 'OK', variables: exposed });
      return;
    }

    if (action === 'GET_REACT_FIBER_TEXT') {
      // Attempt to read React fiber text from a selector (for SPA-rendered text)
      try {
        const el = data?.selector ? document.querySelector(data.selector) : null;
        if (!el) { reply({ status: 'ERROR', error: 'Element not found' }); return; }
        // Walk React fiber to get rendered text
        const fiberKey = Object.keys(el).find(k => k.startsWith('__reactFiber') || k.startsWith('__reactInternalInstance'));
        reply({ status: 'OK', innerText: el.innerText || '', hasFiber: !!fiberKey });
      } catch (err) {
        reply({ status: 'ERROR', error: err.message });
      }
      return;
    }

    if (action === 'PING') {
      reply({ status: 'PONG', url: window.location.href });
    }
  });

})();