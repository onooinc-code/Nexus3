/**
 * Nexus Agentic Browser Bridge — Content Script
 * Autonomous ReAct Node
 * Version: 1.3.0
 */
(function () {
  'use strict';

  let isReady = false;

  // ─── Semantic DOM Extraction ──────────────────────────────────────────────────
  function getDomSnapshot() {
    const clonedBody = document.body.cloneNode(true);
    
    // Remove unwanted elements
    const removeSelectors = ['script', 'style', 'svg', 'iframe', 'noscript', 'canvas', 'video', 'audio'];
    clonedBody.querySelectorAll(removeSelectors.join(',')).forEach(el => el.remove());

    // Clean up attributes but keep structure and text
    const keepAttrs = ['id', 'class', 'href', 'placeholder', 'type', 'name', 'value', 'aria-label', 'role'];
    const interactiveTags = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'];
    
    const elements = clonedBody.querySelectorAll('*');
    const interactiveMap = [];
    let counter = 1;

    for (const el of elements) {
      if (el.style.display === 'none' || el.style.visibility === 'hidden') {
        el.remove();
        continue;
      }
      
      const isInteractive = interactiveTags.includes(el.tagName) || el.getAttribute('role') === 'button' || el.getAttribute('role') === 'link';
      
      if (isInteractive) {
        el.setAttribute('data-nexus-id', counter);
        interactiveMap.push({
          id: counter,
          tag: el.tagName.toLowerCase(),
          text: (el.innerText || el.getAttribute('aria-label') || el.getAttribute('placeholder') || el.value || '').trim().slice(0, 50),
          href: el.getAttribute('href') || null
        });
        counter++;
      }

      // Remove non-essential attributes
      const attrs = Array.from(el.attributes);
      for (const attr of attrs) {
        if (!keepAttrs.includes(attr.name) && attr.name !== 'data-nexus-id') {
          el.removeAttribute(attr.name);
        }
      }
    }

    // Attempt to return a simplified text representation
    // E.g. [1] Button: "Like", [2] Link: "Profile"
    const snapshotText = interactiveMap
      .filter(item => item.text)
      .map(item => `[${item.id}] ${item.tag}: "${item.text}"`)
      .join('\n');

    return {
      snapshotText: snapshotText || 'No interactive elements found.',
      interactiveMap
    };
  }

  // ─── Message Listener ────────────────────────────────────────────────────────
  chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (message.target !== 'NEXUS_CONTENT_SCRIPT') return;

    if (message.action === 'PING') {
      sendResponse({ status: 'PONG', url: window.location.href, ready: isReady });
      return;
    }

    if (message.action === 'EXECUTE_REACT_ACTION') {
      const { command, args } = message.data || {};

      if (command === 'scroll') {
        const amount = args?.amount || 500;
        const direction = args?.direction || 'down';
        const y = direction === 'down' ? amount : -amount;
        window.scrollBy({ top: y, behavior: 'smooth' });
        
        setTimeout(() => {
          sendResponse({ status: 'SUCCESS', result: `Scrolled ${direction} by ${amount}px`, yOffset: window.scrollY });
        }, 800);
        return true;
      }

      if (command === 'get_dom_snapshot') {
        try {
          const snapshot = getDomSnapshot();
          sendResponse({ status: 'SUCCESS', result: snapshot });
        } catch (err) {
          sendResponse({ status: 'ERROR', error: err.message });
        }
        return;
      }

      if (command === 'wait') {
        const ms = args?.ms || 2000;
        setTimeout(() => {
          sendResponse({ status: 'SUCCESS', result: `Waited ${ms}ms` });
        }, ms);
        return true;
      }

      sendResponse({ status: 'ERROR', error: `Unknown command: ${command}` });
    }
  });

  // ─── Bridge Injection ────────────────────────────────────────────────────────
  function injectBridgeScript() {
    try {
      const script = document.createElement('script');
      script.src = chrome.runtime.getURL('injected-bridge.js');
      script.onload = function() {
        this.remove();
      };
      (document.head || document.documentElement).appendChild(script);
    } catch (err) {}
  }

  isReady = true;
  injectBridgeScript();

})();
