/**
 * Nexus3 Audit Portal — Desktop Engine v3
 */
document.addEventListener('DOMContentLoaded', () => {

// ── Mermaid ──────────────────────────────────────────────
if (typeof mermaid !== 'undefined') {
    mermaid.initialize({ startOnLoad: false, theme: 'dark', securityLevel: 'loose',
        fontFamily: "system-ui,-apple-system,'Segoe UI',Roboto,sans-serif",
        themeVariables: { darkMode:true, background:'#0B0E14', primaryColor:'#8b5cf6',
            primaryTextColor:'#fff', primaryBorderColor:'#6366f1', lineColor:'#22d3ee',
            secondaryColor:'#34d399', tertiaryColor:'#0f172a' },
        flowchart: { useMaxWidth:true, htmlLabels:true, curve:'basis' },
        sequence: { showSequenceNumbers:true, actorMargin:50, messageMargin:40 }
    });
}

// ── Marked ───────────────────────────────────────────────
if (typeof marked !== 'undefined') {
    const renderer = new marked.Renderer();
    renderer.code = (code, lang) => {
        if (lang === 'mermaid') return `<div class="mermaid-wrapper"><div class="mermaid">${code}</div></div>`;
        const vl = (typeof Prism !== 'undefined' && Prism.languages[lang]) ? lang : 'clike';
        return `<div class="pre-wrap"><button class="copy-btn"><i class="fa-regular fa-copy"></i> نسخ</button><pre class="language-${vl}"><code class="language-${vl}">${code}</code></pre></div>`;
    };
    renderer.heading = (text, level) => {
        const id = text.toLowerCase().replace(/[^\w]+/g,'-');
        return `<h${level} id="${id}">${text}<a class="heading-anchor" href="#${id}" title="رابط القسم">#</a></h${level}>`;
    };
    renderer.blockquote = (q) => {
        if (q.includes('[!NOTE]')||q.includes('[!INFO]')) return `<div class="alert-box info"><div><i class="fas fa-info-circle"></i></div><div>${q.replace(/\[!(NOTE|INFO)\]/g,'<strong>ملاحظة:</strong>')}</div></div>`;
        if (q.includes('[!WARNING]')) return `<div class="alert-box warning"><div><i class="fas fa-exclamation-triangle"></i></div><div>${q.replace(/\[!WARNING\]/g,'<strong>تحذير:</strong>')}</div></div>`;
        if (q.includes('[!IMPORTANT]')) return `<div class="alert-box"><div><i class="fas fa-exclamation-circle"></i></div><div>${q.replace(/\[!IMPORTANT\]/g,'<strong>هام جداً:</strong>')}</div></div>`;
        if (q.includes('[!CAUTION]')) return `<div class="alert-box danger"><div><i class="fas fa-shield-alt"></i></div><div>${q.replace(/\[!CAUTION\]/g,'<strong>تنبيه حرج:</strong>')}</div></div>`;
        return `<div class="alert-box"><div>${q}</div></div>`;
    };
    marked.setOptions({ gfm:true, breaks:true, headerIds:true, mangle:false });
    marked.use({ renderer });
}

// ── State ────────────────────────────────────────────────
const navLinks = Array.from(document.querySelectorAll('.nav-link[data-doc]'));
const sidebar = document.getElementById('portal-sidebar');
const portalMain = document.getElementById('portal-main');
const docViewer = document.getElementById('doc-viewer');
const docTitleEl = document.getElementById('active-doc-title');

let currentIdx = 0;
let currentDocText = '';

// ── Sidebar Toggle ────────────────────────────────────────
const sidebarToggleBtn = document.getElementById('sidebar-toggle');
function applySidebar(collapsed) {
    sidebar.classList.toggle('sidebar-collapsed', collapsed);
    portalMain.classList.toggle('sidebar-hidden', collapsed);
    sidebarToggleBtn && sidebarToggleBtn.setAttribute('aria-pressed', String(collapsed));
}
applySidebar(localStorage.getItem('nexus-sidebar-collapsed') === 'true');
sidebarToggleBtn && sidebarToggleBtn.addEventListener('click', () => {
    const c = !sidebar.classList.contains('sidebar-collapsed');
    localStorage.setItem('nexus-sidebar-collapsed', c);
    applySidebar(c);
});

// ── Reading Progress ──────────────────────────────────────
const progressBar = document.getElementById('reading-progress-bar');
const backToTop = document.getElementById('back-to-top');
window.addEventListener('scroll', () => {
    const el = document.documentElement;
    const pct = (el.scrollTop / (el.scrollHeight - el.clientHeight)) * 100;
    if (progressBar) progressBar.style.width = pct + '%';
    if (backToTop) backToTop.classList.toggle('visible', el.scrollTop > 400);
    updateTocActive();
}, { passive: true });
backToTop && backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ── TOC ───────────────────────────────────────────────────
const tocPanel = document.getElementById('toc-panel');
const tocNav = document.getElementById('toc-nav');
const tocToggleBtn = document.getElementById('toc-toggle-btn');
const tocCloseBtn = document.getElementById('toc-close-btn');

function buildToc() {
    if (!docViewer || !tocNav) return;
    const headings = docViewer.querySelectorAll('h2,h3,h4');
    if (!headings.length) { tocNav.innerHTML = '<p class="toc-empty">No sections found.</p>'; return; }
    tocNav.innerHTML = Array.from(headings).map(h => {
        const cls = `toc-link toc-link-${h.tagName.toLowerCase()}`;
        return `<a class="${cls}" href="#${h.id}" data-target="${h.id}">${h.childNodes[0].textContent.trim()}</a>`;
    }).join('');
    tocNav.querySelectorAll('.toc-link').forEach(a => {
        a.addEventListener('click', e => { e.preventDefault(); document.getElementById(a.dataset.target)?.scrollIntoView({ behavior:'smooth', block:'start' }); });
    });
}

function updateTocActive() {
    if (!tocNav) return;
    const links = tocNav.querySelectorAll('.toc-link');
    let active = null;
    docViewer && docViewer.querySelectorAll('h2,h3,h4').forEach(h => {
        if (h.getBoundingClientRect().top < 120) active = h.id;
    });
    links.forEach(a => a.classList.toggle('toc-active', a.dataset.target === active));
}

function toggleToc(force) {
    const open = force !== undefined ? force : !tocPanel.classList.contains('toc-open');
    tocPanel.classList.toggle('toc-open', open);
    portalMain.classList.toggle('toc-shifted', open);
}
tocToggleBtn && tocToggleBtn.addEventListener('click', () => toggleToc());
tocCloseBtn && tocCloseBtn.addEventListener('click', () => toggleToc(false));

// ── Font Size ─────────────────────────────────────────────
let fontState = localStorage.getItem('nexus-font') || 'md';
function applyFont(s) {
    document.body.classList.remove('font-sm','font-lg');
    if (s !== 'md') document.body.classList.add('font-'+s);
    ['sm','md','lg'].forEach(x => document.getElementById('font-'+x+'-btn')?.classList.toggle('tool-btn-active', x===s));
    localStorage.setItem('nexus-font', s);
    fontState = s;
}
applyFont(fontState);
['sm','md','lg'].forEach(s => document.getElementById('font-'+s+'-btn')?.addEventListener('click', () => applyFont(s)));

// ── Focus Mode ────────────────────────────────────────────
document.getElementById('focus-btn')?.addEventListener('click', () => {
    document.body.classList.toggle('focus-mode');
});

// ── Shortcuts Modal ───────────────────────────────────────
const shortcutsOverlay = document.getElementById('shortcuts-overlay');
document.getElementById('shortcuts-btn')?.addEventListener('click', () => shortcutsOverlay?.classList.add('active'));
document.getElementById('shortcuts-close')?.addEventListener('click', () => shortcutsOverlay?.classList.remove('active'));
shortcutsOverlay?.addEventListener('click', e => { if (e.target === shortcutsOverlay) shortcutsOverlay.classList.remove('active'); });

// ── Toast ─────────────────────────────────────────────────
function showToast(msg, icon = 'fa-check') {
    let t = document.querySelector('.toast');
    if (!t) { t = document.createElement('div'); t.className = 'toast'; document.body.appendChild(t); }
    t.innerHTML = `<i class="fa-solid ${icon}"></i> ${msg}`;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 2500);
}

// ── Print & Copy Link ─────────────────────────────────────
document.getElementById('print-btn')?.addEventListener('click', () => window.print());
document.getElementById('copy-link-btn')?.addEventListener('click', () => {
    navigator.clipboard.writeText(window.location.href).then(() => showToast('Link copied!', 'fa-link'));
});

// ── Doc Meta ──────────────────────────────────────────────
function updateMeta(text) {
    const words = text.trim().split(/\s+/).length;
    const mins = Math.max(1, Math.round(words / 200));
    const sections = (docViewer.querySelectorAll('h2').length) || 0;
    const el = id => document.getElementById(id);
    if (el('meta-words')) el('meta-words').innerHTML = `<i class="fa-solid fa-align-left"></i> ${words.toLocaleString()} كلمة`;
    if (el('meta-sections')) el('meta-sections').innerHTML = `<i class="fa-solid fa-layer-group"></i> ${sections} قسم`;
    if (el('meta-read')) el('meta-read').innerHTML = `<i class="fa-regular fa-clock"></i> ${mins} دقيقة قراءة`;
    if (el('topbar-reading-time')) { el('topbar-reading-time').style.display = 'flex'; el('reading-time-text').textContent = `${mins} دقيقة قراءة`; }
}

// ── Prev/Next ─────────────────────────────────────────────
function getDocIndex() { return navLinks.findIndex(l => l.classList.contains('active')); }

function loadByIndex(idx) {
    if (idx < 0 || idx >= navLinks.length) return;
    const link = navLinks[idx];
    navLinks.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    window.location.hash = link.getAttribute('data-doc').split('/').pop().replace('.md','');
    loadDocument(link.getAttribute('data-doc'), link.innerText.trim());
}

function updateFooterNav() {
    const idx = getDocIndex();
    const prev = navLinks[idx-1], next = navLinks[idx+1];
    const prevFooter = document.getElementById('doc-prev-footer');
    const nextFooter = document.getElementById('doc-next-footer');
    if (prevFooter) { prevFooter.style.display = prev ? 'flex' : 'none'; if (prev) document.getElementById('prev-footer-title').textContent = prev.innerText.trim(); }
    if (nextFooter) { nextFooter.style.display = next ? 'flex' : 'none'; if (next) document.getElementById('next-footer-title').textContent = next.innerText.trim(); }
}

document.getElementById('prev-doc-btn')?.addEventListener('click', () => loadByIndex(getDocIndex()-1));
document.getElementById('next-doc-btn')?.addEventListener('click', () => loadByIndex(getDocIndex()+1));
document.getElementById('doc-prev-footer')?.addEventListener('click', () => loadByIndex(getDocIndex()-1));
document.getElementById('doc-next-footer')?.addEventListener('click', () => loadByIndex(getDocIndex()+1));

// ── Sidebar Search ────────────────────────────────────────
const searchInput = document.getElementById('sidebar-search');
const searchResults = document.getElementById('sidebar-search-results');
const searchClr = document.getElementById('sidebar-search-clr');

function doSearch(q) {
    if (!q || !currentDocText) { searchResults.innerHTML = ''; return; }
    const lines = currentDocText.split('\n');
    const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'), 'gi');
    const hits = [];
    let section = '';
    lines.forEach(line => {
        if (/^#{1,4}\s/.test(line)) section = line.replace(/^#+\s/,'').trim();
        if (re.test(line) && !(/^#{1,4}\s/.test(line))) {
            hits.push({ section, text: line.trim().substring(0,120) });
        }
    });
    if (!hits.length) { searchResults.innerHTML = '<div class="search-no-results">No results found</div>'; return; }
    searchResults.innerHTML = hits.slice(0,8).map(h =>
        `<div class="search-result-item"><div class="sri-section">${h.section||'—'}</div>${h.text.replace(re, m => `<mark>${m}</mark>`)}</div>`
    ).join('');
}

searchInput?.addEventListener('input', () => {
    const q = searchInput.value.trim();
    searchClr.style.display = q ? 'block' : 'none';
    doSearch(q);
});
searchClr?.addEventListener('click', () => { searchInput.value = ''; searchClr.style.display = 'none'; searchResults.innerHTML = ''; searchInput.focus(); });

// ── Keyboard Shortcuts ────────────────────────────────────
document.addEventListener('keydown', e => {
    if (['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
    if (e.key === 'ArrowLeft' || e.key === 'j') loadByIndex(getDocIndex()-1);
    if (e.key === 'ArrowRight' || e.key === 'k') loadByIndex(getDocIndex()+1);
    if (e.key === 'f' || e.key === 'F') document.body.classList.toggle('focus-mode');
    if (e.key === 't' || e.key === 'T') toggleToc();
    if (e.key === 'b' || e.key === 'B') window.scrollTo({ top:0, behavior:'smooth' });
    if (e.key === 'p' || e.key === 'P') window.print();
    if (e.key === '/' ) { e.preventDefault(); searchInput?.focus(); }
    if (e.key === '?') shortcutsOverlay?.classList.add('active');
    if (e.key === 'Escape') {
        shortcutsOverlay?.classList.remove('active');
        closeLightbox();
        if (searchInput === document.activeElement) searchInput.blur();
    }
});

// ── Lightbox ──────────────────────────────────────────────
const lightbox = document.getElementById('media-lightbox');
const lbContainer = document.getElementById('lightbox-content-container');
const lbViewport = document.getElementById('lightbox-viewport');
let lbScale = 1, lbDragging = false, lbStart = {x:0,y:0}, lbPos = {x:0,y:0};

function applyLb() { if(lbContainer) lbContainer.style.transform = `translate(${lbPos.x}px,${lbPos.y}px) scale(${lbScale})`; }

function openLightbox(html, title) {
    if(!lightbox||!lbContainer) return;
    lbScale=1; lbPos={x:0,y:0}; lbContainer.innerHTML=html; lbContainer.style.transform='';
    lightbox.classList.add('active'); document.body.style.overflow='hidden';
    const mEl = lbContainer.querySelector('.mermaid');
    if(mEl && typeof mermaid!=='undefined') mermaid.run({nodes:[mEl]}).catch(()=>{});
}
function closeLightbox() { if(!lightbox) return; lightbox.classList.remove('active'); document.body.style.overflow=''; }

document.getElementById('lb-close')?.addEventListener('click', closeLightbox);
lightbox?.addEventListener('click', e => { if(e.target===lightbox) closeLightbox(); });
document.getElementById('lb-zoom-in')?.addEventListener('click', () => { lbScale=Math.min(lbScale+.25,5); applyLb(); });
document.getElementById('lb-zoom-out')?.addEventListener('click', () => { lbScale=Math.max(lbScale-.25,.25); applyLb(); });
document.getElementById('lb-reset')?.addEventListener('click', () => { lbScale=1; lbPos={x:0,y:0}; applyLb(); });
document.getElementById('lb-native-fs')?.addEventListener('click', () => {
    if(!document.fullscreenElement) { lightbox.requestFullscreen?.(); document.querySelector('#lb-native-fs i').className='fa-solid fa-compress'; }
    else { document.exitFullscreen?.(); document.querySelector('#lb-native-fs i').className='fa-solid fa-expand'; }
});
lbViewport?.addEventListener('wheel', e => { e.preventDefault(); lbScale=Math.min(Math.max(lbScale+(e.deltaY>0?-.15:.15),.25),5); applyLb(); }, {passive:false});
lbViewport?.addEventListener('mousedown', e => { lbDragging=true; lbStart={x:e.clientX-lbPos.x,y:e.clientY-lbPos.y}; lbViewport.style.cursor='grabbing'; });
window.addEventListener('mousemove', e => { if(!lbDragging) return; lbPos={x:e.clientX-lbStart.x,y:e.clientY-lbStart.y}; applyLb(); });
window.addEventListener('mouseup', () => { lbDragging=false; if(lbViewport) lbViewport.style.cursor='grab'; });

// ── Media Toolbars ────────────────────────────────────────
function createToolbar(openWin, openFull, zoomEl) {
    const bar = document.createElement('div'); bar.className='media-toolbar';
    let scale=1;
    const btns = [
        {icon:'fa-solid fa-arrow-up-right-from-square',label:'فتح في نافذة',cls:'mtb-window',fn:openWin},
        {icon:'fa-solid fa-expand',label:'ملء الشاشة',cls:'mtb-fullscreen',fn:openFull},
        {icon:'fa-solid fa-magnifying-glass-plus',label:'تكبير',cls:'mtb-zoomin',fn:()=>{ scale=Math.min(scale+.25,4); zoomEl.style.transform=`scale(${scale})`; zoomEl.style.transformOrigin='top center'; }},
        {icon:'fa-solid fa-magnifying-glass-minus',label:'تصغير',cls:'mtb-zoomout',fn:()=>{ scale=Math.max(scale-.25,.25); zoomEl.style.transform=`scale(${scale})`; zoomEl.style.transformOrigin='top center'; }},
    ];
    btns.forEach(({icon,label,cls,fn}) => {
        const b=document.createElement('button'); b.className=`mtb-btn ${cls}`; b.title=label;
        b.innerHTML=`<i class="${icon}"></i><span>${label}</span>`; b.addEventListener('click',fn); bar.appendChild(b);
    });
    return bar;
}

function injectMediaToolbars(container) {
    container.querySelectorAll('.md-view img').forEach(img => {
        if(img.parentElement.classList.contains('media-wrapper')) return;
        const wrap=document.createElement('div'); wrap.className='media-wrapper';
        img.parentNode.insertBefore(wrap,img); wrap.appendChild(img);
        wrap.appendChild(createToolbar(
            ()=>{ const w=window.open('','_blank','width=900,height=700,resizable=yes'); w.document.write(`<!DOCTYPE html><html><head><title>${img.alt||'Image'}</title><style>body{margin:0;background:#070b16;display:flex;align-items:center;justify-content:center;min-height:100vh;}img{max-width:100%;max-height:100vh;}</style></head><body><img src="${img.src}"></body></html>`); w.document.close(); },
            ()=>openLightbox(`<img src="${img.src}" alt="${img.alt||''}">`, img.alt||'Image'),
            img
        ));
    });
    container.querySelectorAll('.mermaid-wrapper').forEach(wrap => {
        if(wrap.querySelector('.media-toolbar')) return;
        wrap.style.position='relative';
        const svg=wrap.querySelector('svg'), zt=svg||wrap.querySelector('.mermaid')||wrap;
        wrap.appendChild(createToolbar(
            ()=>{ const svgH=wrap.querySelector('svg')?.outerHTML||''; const w=window.open('','_blank','width=1100,height=800,resizable=yes'); w.document.write(`<!DOCTYPE html><html><head><title>Diagram</title><style>body{margin:0;background:#070b16;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;}svg{max-width:100%;height:auto;}</style></head><body>${svgH}</body></html>`); w.document.close(); },
            ()=>{ const svgH=wrap.querySelector('svg')?.outerHTML||''; openLightbox(`<div style="max-width:90vw;overflow:auto;">${svgH}</div>`,'Diagram'); },
            zt
        ));
    });
    // Copy buttons
    container.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const code=btn.nextElementSibling?.querySelector('code')?.innerText||'';
            navigator.clipboard.writeText(code).then(()=>{ btn.innerHTML='<i class="fa-solid fa-check"></i> Copied!'; btn.classList.add('copied'); setTimeout(()=>{ btn.innerHTML='<i class="fa-regular fa-copy"></i> Copy'; btn.classList.remove('copied'); },2000); });
        });
    });
}

// ── Load Document ─────────────────────────────────────────
async function loadDocument(docPath, title) {
    if(!docViewer) return;
    docViewer.innerHTML=`<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:400px;color:var(--text-muted);"><div style="font-size:2rem;margin-bottom:1rem;"><i class="fas fa-circle-notch fa-spin"></i></div><div>جاري تحميل <code>${docPath}</code>…</div></div>`;
    if(docTitleEl) docTitleEl.textContent=title||docPath;
    window.scrollTo({top:0,behavior:'smooth'});
    try {
        const r=await fetch(docPath);
        if(!r.ok) throw new Error(`HTTP ${r.status}`);
        currentDocText=await r.text();
        docViewer.innerHTML=`<div class="md-view" style="direction:ltr;text-align:left;">${marked.parse(currentDocText)}</div>`;
        if(typeof Prism!=='undefined') try { Prism.highlightAllUnder(docViewer); } catch(e){}
        if(typeof mermaid!=='undefined') {
            const nodes=docViewer.querySelectorAll('.mermaid');
            if(nodes.length) try { await mermaid.run({nodes}); } catch(e){}
        }
        injectMediaToolbars(docViewer);
        buildToc();
        updateFooterNav();
        updateMeta(currentDocText);
        // clear search
        if(searchInput) { searchInput.value=''; if(searchClr) searchClr.style.display='none'; if(searchResults) searchResults.innerHTML=''; }
    } catch(err) {
        docViewer.innerHTML=`<div class="alert-box danger"><div style="font-size:1.5rem;"><i class="fas fa-exclamation-circle"></i></div><div><h4 style="color:#fb7185;margin-bottom:8px;">خطأ في التحميل</h4><p>تعذّر تحميل: <code>${docPath}</code></p><pre style="margin-top:12px;background:rgba(0,0,0,.3)!important;"><code>${err.message}</code></pre></div></div>`;
    }
}

// ── Nav Links ─────────────────────────────────────────────
navLinks.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        navLinks.forEach(l=>l.classList.remove('active'));
        link.classList.add('active');
        window.location.hash=link.getAttribute('data-doc').split('/').pop().replace('.md','');
        loadDocument(link.getAttribute('data-doc'), link.innerText.trim());
    });
});

// ── Initial Load ──────────────────────────────────────────
const hash=window.location.hash.replace('#','');
let def=navLinks[0];
if(hash) { const m=navLinks.find(l=>l.getAttribute('data-doc').includes(hash)); if(m) def=m; }
if(def) { def.classList.add('active'); loadDocument(def.getAttribute('data-doc'), def.innerText.trim()); }

});
