/**
 * Nexus3 Audit Portal — Mobile Engine v1
 */
document.addEventListener('DOMContentLoaded', () => {

// ── Mermaid ──
if (typeof mermaid !== 'undefined') {
    mermaid.initialize({ startOnLoad:false, theme:'dark', securityLevel:'loose',
        fontFamily:"system-ui,-apple-system,'Segoe UI',Roboto,sans-serif",
        themeVariables:{darkMode:true,background:'#0B0E14',primaryColor:'#8b5cf6',
            primaryTextColor:'#fff',primaryBorderColor:'#6366f1',lineColor:'#22d3ee',
            secondaryColor:'#34d399',tertiaryColor:'#0f172a'},
        flowchart:{useMaxWidth:true,htmlLabels:true,curve:'basis'}
    });
}

// ── Marked ──
if (typeof marked !== 'undefined') {
    const renderer = new marked.Renderer();
    renderer.code = (code, lang) => {
        if (lang === 'mermaid') return `<div class="mob-mermaid-wrap"><div class="mermaid">${code}</div></div>`;
        const vl = (typeof Prism!=='undefined' && Prism.languages[lang]) ? lang : 'clike';
        return `<div class="pre-wrap"><button class="copy-btn"><i class="fa-regular fa-copy"></i> نسخ</button><pre class="language-${vl}"><code class="language-${vl}">${code}</code></pre></div>`;
    };
    renderer.blockquote = (q) => {
        if (q.includes('[!NOTE]')||q.includes('[!INFO]')) return `<div class="alert-box info"><div><i class="fas fa-info-circle"></i></div><div>${q.replace(/\[!(NOTE|INFO)\]/g,'<strong>ملاحظة:</strong>')}</div></div>`;
        if (q.includes('[!WARNING]')) return `<div class="alert-box warning"><div><i class="fas fa-exclamation-triangle"></i></div><div>${q.replace(/\[!WARNING\]/g,'<strong>تحذير:</strong>')}</div></div>`;
        if (q.includes('[!IMPORTANT]')) return `<div class="alert-box"><div><i class="fas fa-exclamation-circle"></i></div><div>${q.replace(/\[!IMPORTANT\]/g,'<strong>هام جداً:</strong>')}</div></div>`;
        if (q.includes('[!CAUTION]')) return `<div class="alert-box danger"><div><i class="fas fa-shield-alt"></i></div><div>${q.replace(/\[!CAUTION\]/g,'<strong>تنبيه حرج:</strong>')}</div></div>`;
        return `<div class="alert-box"><div>${q}</div></div>`;
    };
    marked.setOptions({gfm:true,breaks:true,headerIds:true,mangle:false});
    marked.use({renderer});
}

// ── Refs ──
const navLinks = Array.from(document.querySelectorAll('.mob-nav-link[data-doc]'));
const drawer = document.getElementById('mob-drawer');
const overlay = document.getElementById('mob-overlay');
const content = document.getElementById('mob-content');
const headerTitle = document.getElementById('mob-header-title');
const progressBar = document.getElementById('mob-progress');
const backTop = document.getElementById('mob-back-top');

let currentDocText = '';
let currentIdx = 0;

// ── Drawer ──
function openDrawer() { drawer.classList.add('open'); overlay.classList.add('active'); }
function closeDrawer() { drawer.classList.remove('open'); overlay.classList.remove('active'); }
document.getElementById('mob-menu-btn')?.addEventListener('click', openDrawer);
overlay.addEventListener('click', closeDrawer);

// ── Sheets ──
const sheets = { toc: document.getElementById('mob-toc-sheet'), search: document.getElementById('mob-search-sheet'), settings: document.getElementById('mob-settings-sheet') };
function openSheet(name) { Object.values(sheets).forEach(s => s?.classList.remove('open')); sheets[name]?.classList.add('open'); overlay.classList.add('active'); }
function closeSheets() { Object.values(sheets).forEach(s => s?.classList.remove('open')); overlay.classList.remove('active'); if(!drawer.classList.contains('open')) overlay.classList.remove('active'); }
document.querySelectorAll('.mob-sheet-close').forEach(b => b.addEventListener('click', closeSheets));

// ── Bottom Tabs ──
const tabs = document.querySelectorAll('.mob-tab');
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const t = tab.dataset.tab;
        if (t === 'docs') { openDrawer(); return; }
        if (t === 'toc') { openSheet('toc'); return; }
        if (t === 'search') { openSheet('search'); document.getElementById('mob-search-input')?.focus(); return; }
        if (t === 'settings') { openSheet('settings'); return; }
    });
});

// ── Progress + Back to Top ──
window.addEventListener('scroll', () => {
    const el = document.documentElement;
    const pct = (el.scrollTop / (el.scrollHeight - el.clientHeight)) * 100;
    if (progressBar) progressBar.style.width = pct + '%';
    if (backTop) backTop.classList.toggle('visible', el.scrollTop > 300);
    updateTocActive();
}, {passive:true});
backTop?.addEventListener('click', () => window.scrollTo({top:0,behavior:'smooth'}));

// ── TOC ──
function buildToc() {
    const tocBody = document.getElementById('mob-toc-body');
    if (!tocBody) return;
    const headings = content.querySelectorAll('h2,h3,h4');
    if (!headings.length) { tocBody.innerHTML = '<p style="color:var(--dim);font-size:.82rem;padding:12px;">No sections found.</p>'; return; }
    tocBody.innerHTML = Array.from(headings).map(h => {
        const cls = `mob-toc-link${h.tagName==='H3'?' mob-toc-h3':h.tagName==='H4'?' mob-toc-h4':''}`;
        return `<div class="${cls}" data-id="${h.id}">${h.childNodes[0]?.textContent?.trim()||h.textContent.trim()}</div>`;
    }).join('');
    tocBody.querySelectorAll('.mob-toc-link').forEach(a => {
        a.addEventListener('click', () => {
            document.getElementById(a.dataset.id)?.scrollIntoView({behavior:'smooth',block:'start'});
            closeSheets();
        });
    });
}
function updateTocActive() {
    const tocBody = document.getElementById('mob-toc-body');
    if (!tocBody) return;
    let active = null;
    content.querySelectorAll('h2,h3,h4').forEach(h => { if(h.getBoundingClientRect().top < 100) active = h.id; });
    tocBody.querySelectorAll('.mob-toc-link').forEach(a => a.classList.toggle('active', a.dataset.id === active));
}

// ── Search ──
const mobSearchInput = document.getElementById('mob-search-input');
const mobSearchResults = document.getElementById('mob-search-results');
mobSearchInput?.addEventListener('input', () => {
    const q = mobSearchInput.value.trim();
    if (!q || !currentDocText) { mobSearchResults.innerHTML = ''; return; }
    const lines = currentDocText.split('\n');
    const re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'), 'gi');
    const hits = []; let section = '';
    lines.forEach(line => {
        if (/^#{1,4}\s/.test(line)) section = line.replace(/^#+\s/,'').trim();
        if (re.test(line) && !(/^#{1,4}\s/.test(line))) hits.push({section, text: line.trim().substring(0,100)});
    });
    if (!hits.length) { mobSearchResults.innerHTML = '<p style="color:var(--dim);font-size:.82rem;padding:12px;">No results found.</p>'; return; }
    mobSearchResults.innerHTML = hits.slice(0,10).map(h =>
        `<div class="mob-search-result"><div class="sri-section">${h.section||'—'}</div>${h.text.replace(re,m=>`<mark>${m}</mark>`)}</div>`
    ).join('');
});

// ── Font Size ──
let fs = localStorage.getItem('nexus-mob-font') || 'md';
function applyFont(s) {
    document.body.classList.remove('fs-sm','fs-lg');
    if (s !== 'md') document.body.classList.add('fs-'+s);
    document.querySelectorAll('.mob-font-btn').forEach(b => b.classList.toggle('active', b.dataset.fs === s));
    localStorage.setItem('nexus-mob-font', s);
    fs = s;
}
applyFont(fs);
document.querySelectorAll('.mob-font-btn').forEach(b => b.addEventListener('click', () => applyFont(b.dataset.fs)));

// ── Toast ──
function showToast(msg) {
    let t = document.querySelector('.mob-toast');
    if (!t) { t = document.createElement('div'); t.className='mob-toast'; document.body.appendChild(t); }
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 2500);
}

// ── Lightbox ──
const lightbox = document.getElementById('mob-lightbox');
const lbContent = document.getElementById('mob-lb-content');
let lbScale = 1, lbPos = {x:0,y:0};
let pinchDist = 0;
function applyLb() { if(lbContent) lbContent.style.transform = `translate(${lbPos.x}px,${lbPos.y}px) scale(${lbScale})`; }
function openLightbox(html) {
    if(!lightbox||!lbContent) return;
    lbScale=1; lbPos={x:0,y:0}; lbContent.innerHTML=html; lbContent.style.transform='';
    lightbox.classList.add('active'); document.body.style.overflow='hidden';
    const mEl=lbContent.querySelector('.mermaid');
    if(mEl && typeof mermaid!=='undefined') mermaid.run({nodes:[mEl]}).catch(()=>{});
}
function closeLightbox() { if(!lightbox) return; lightbox.classList.remove('active'); document.body.style.overflow=''; }
document.getElementById('mob-lb-close')?.addEventListener('click', closeLightbox);
document.getElementById('mob-lb-zoom-in')?.addEventListener('click', () => { lbScale=Math.min(lbScale+.3,5); applyLb(); });
document.getElementById('mob-lb-zoom-out')?.addEventListener('click', () => { lbScale=Math.max(lbScale-.3,.25); applyLb(); });
document.getElementById('mob-lb-reset')?.addEventListener('click', () => { lbScale=1; lbPos={x:0,y:0}; applyLb(); });
// Pinch zoom
const lbViewport = document.getElementById('mob-lb-viewport');
if (lbViewport) {
    let startDist=0, startScale=1;
    lbViewport.addEventListener('touchstart', e => { if(e.touches.length===2){ startDist=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY); startScale=lbScale; }}, {passive:true});
    lbViewport.addEventListener('touchmove', e => { if(e.touches.length===2){ e.preventDefault(); const d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY); lbScale=Math.min(Math.max(startScale*(d/startDist),.25),5); applyLb(); }}, {passive:false});
}

// ── Media Toolbars ──
function createToolbar(openWin, openFull, zoomEl) {
    const bar = document.createElement('div'); bar.className='media-toolbar';
    let scale=1;
    const btns = [
        {icon:'fa-solid fa-arrow-up-right-from-square',label:'نافذة',cls:'mtb-window',fn:openWin},
        {icon:'fa-solid fa-expand',label:'ملء الشاشة',cls:'mtb-fullscreen',fn:openFull},
        {icon:'fa-solid fa-magnifying-glass-plus',label:'تكبير',cls:'mtb-zoomin',fn:()=>{ scale=Math.min(scale+.25,4); zoomEl.style.transform=`scale(${scale})`; zoomEl.style.transformOrigin='top center'; }},
        {icon:'fa-solid fa-magnifying-glass-minus',label:'تصغير',cls:'mtb-zoomout',fn:()=>{ scale=Math.max(scale-.25,.25); zoomEl.style.transform=`scale(${scale})`; zoomEl.style.transformOrigin='top center'; }},
    ];
    btns.forEach(({icon,label,cls,fn}) => {
        const b=document.createElement('button'); b.className=`mtb-btn ${cls}`; b.innerHTML=`<i class="${icon}"></i><span>${label}</span>`; b.addEventListener('click',fn); bar.appendChild(b);
    });
    return bar;
}
function injectToolbars(container) {
    container.querySelectorAll('.mob-md img').forEach(img => {
        if(img.parentElement.classList.contains('media-wrapper')) return;
        const w=document.createElement('div'); w.className='media-wrapper';
        img.parentNode.insertBefore(w,img); w.appendChild(img);
        w.appendChild(createToolbar(
            ()=>{ const win=window.open('','_blank','width=800,height=600'); win.document.write(`<!DOCTYPE html><html><head><style>body{margin:0;background:#070b16;display:flex;align-items:center;justify-content:center;min-height:100vh;}img{max-width:100%;}</style></head><body><img src="${img.src}"></body></html>`); win.document.close(); },
            ()=>openLightbox(`<img src="${img.src}" alt="${img.alt||''}">`),
            img
        ));
    });
    container.querySelectorAll('.mob-mermaid-wrap').forEach(wrap => {
        if(wrap.querySelector('.media-toolbar')) return;
        const svg=wrap.querySelector('svg')||wrap, zt=svg;
        wrap.appendChild(createToolbar(
            ()=>{ const svgH=wrap.querySelector('svg')?.outerHTML||''; const win=window.open('','_blank','width=900,height=700'); win.document.write(`<!DOCTYPE html><html><head><style>body{margin:0;background:#070b16;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:16px;}svg{max-width:100%;height:auto;}</style></head><body>${svgH}</body></html>`); win.document.close(); },
            ()=>{ const svgH=wrap.querySelector('svg')?.outerHTML||''; openLightbox(`<div style="max-width:90vw;overflow:auto;">${svgH}</div>`); },
            zt
        ));
    });
    container.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const code=btn.nextElementSibling?.querySelector('code')?.innerText||'';
            navigator.clipboard.writeText(code).then(()=>{ btn.innerHTML='<i class="fa-solid fa-check"></i> Copied!'; btn.classList.add('copied'); setTimeout(()=>{ btn.innerHTML='<i class="fa-regular fa-copy"></i> Copy'; btn.classList.remove('copied'); },2000); });
        });
    });
}

// ── Footer Nav ──
function updateFooterNav() {
    const idx = navLinks.findIndex(l=>l.classList.contains('active'));
    const prev=navLinks[idx-1], next=navLinks[idx+1];
    const prevBtn=document.getElementById('mob-doc-prev'), nextBtn=document.getElementById('mob-doc-next');
    if(prevBtn){ prevBtn.style.display=prev?'flex':'none'; if(prev) prevBtn.querySelector('.dfn-title').textContent=prev.querySelector('span').textContent.trim(); }
    if(nextBtn){ nextBtn.style.display=next?'flex':'none'; if(next) nextBtn.querySelector('.dfn-title').textContent=next.querySelector('span').textContent.trim(); }
}
document.getElementById('mob-doc-prev')?.addEventListener('click', ()=>{ const idx=navLinks.findIndex(l=>l.classList.contains('active')); if(idx>0) { navLinks.forEach(l=>l.classList.remove('active')); navLinks[idx-1].classList.add('active'); loadDoc(navLinks[idx-1]); } });
document.getElementById('mob-doc-next')?.addEventListener('click', ()=>{ const idx=navLinks.findIndex(l=>l.classList.contains('active')); if(idx<navLinks.length-1) { navLinks.forEach(l=>l.classList.remove('active')); navLinks[idx+1].classList.add('active'); loadDoc(navLinks[idx+1]); } });

// ── Load Document ──
async function loadDoc(link) {
    const docPath = link.getAttribute('data-doc');
    const title = link.querySelector('span').textContent.trim();
    if(headerTitle) headerTitle.textContent = title;
    window.location.hash = docPath.split('/').pop().replace('.md','');
    window.scrollTo({top:0,behavior:'smooth'});
    closeDrawer(); closeSheets();
    content.innerHTML = `<div class="mob-loading"><i class="fas fa-circle-notch fa-spin"></i><span>جاري التحميل…</span></div>`;
    try {
        const r = await fetch(docPath);
        if(!r.ok) throw new Error(`HTTP ${r.status}`);
        currentDocText = await r.text();
        const كلمة = currentDocText.trim().split(/\s+/).length;
        const mins = Math.max(1,Math.round(كلمة/200));
        content.innerHTML = `
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                <span style="font-size:.72rem;color:var(--dim);display:flex;align-items:center;gap:4px;"><i class="fa-regular fa-clock"></i> ${mins} دقيقة قراءة</span>
                <span style="font-size:.72rem;color:var(--dim);display:flex;align-items:center;gap:4px;"><i class="fa-solid fa-align-left"></i> ${كلمة.toLocaleString()} كلمة</span>
            </div>
            <div class="mob-md">${marked.parse(currentDocText)}</div>
            <div class="mob-doc-nav">
                <button class="mob-doc-btn" id="mob-doc-prev" style="display:none"><i class="fa-solid fa-arrow-right"></i><div><div class="dfn-label">السابق</div><div class="dfn-title">—</div></div></button>
                <button class="mob-doc-btn mob-doc-btn-next" id="mob-doc-next" style="display:none"><div><div class="dfn-label">التالي</div><div class="dfn-title">—</div></div><i class="fa-solid fa-arrow-left"></i></button>
            </div>`;
        if(typeof Prism!=='undefined') try{Prism.highlightAllUnder(content);}catch(e){}
        if(typeof mermaid!=='undefined') {
            const nodes=content.querySelectorAll('.mermaid');
            if(nodes.length) try{await mermaid.run({nodes});}catch(e){}
        }
        injectToolbars(content);
        buildToc();
        updateFooterNav();
        document.getElementById('mob-doc-prev')?.addEventListener('click',()=>{ const idx=navLinks.findIndex(l=>l.classList.contains('active')); if(idx>0){navLinks.forEach(l=>l.classList.remove('active'));navLinks[idx-1].classList.add('active');loadDoc(navLinks[idx-1]);} });
        document.getElementById('mob-doc-next')?.addEventListener('click',()=>{ const idx=navLinks.findIndex(l=>l.classList.contains('active')); if(idx<navLinks.length-1){navLinks.forEach(l=>l.classList.remove('active'));navLinks[idx+1].classList.add('active');loadDoc(navLinks[idx+1]);} });
    } catch(err) {
        content.innerHTML=`<div class="alert-box danger"><div><i class="fas fa-exclamation-circle"></i></div><div><strong>Error:</strong> ${err.message}</div></div>`;
    }
}

// ── Nav Links ──
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navLinks.forEach(l=>l.classList.remove('active'));
        link.classList.add('active');
        loadDoc(link);
    });
});

// ── Initial Load ──
const hash = window.location.hash.replace('#','');
let def = navLinks[0];
if(hash) { const m=navLinks.find(l=>l.getAttribute('data-doc').includes(hash)); if(m) def=m; }
if(def) { def.classList.add('active'); loadDoc(def); }

});

// Header search button opens search sheet
document.getElementById('mob-search-hdr-btn')?.addEventListener('click', () => {
    const sheets_obj = { toc: document.getElementById('mob-toc-sheet'), search: document.getElementById('mob-search-sheet'), settings: document.getElementById('mob-settings-sheet') };
    Object.values(sheets_obj).forEach(s => s?.classList.remove('open'));
    sheets_obj.search?.classList.add('open');
    document.getElementById('mob-overlay')?.classList.add('active');
    setTimeout(() => document.getElementById('mob-search-input')?.focus(), 300);
});
