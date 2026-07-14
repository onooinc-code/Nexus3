@extends('layouts.app')

@push('styles')
<style>
/* ===== Nexus Dev — glassmorphism + 3D + mobile-app mode ===== */
.dev-orb{position:fixed;border-radius:50%;filter:blur(70px);opacity:.45;z-index:0;pointer-events:none;animation:devfloat 16s ease-in-out infinite}
.dev-orb.a{width:360px;height:360px;background:#7c5cff;top:-90px;left:-70px}
.dev-orb.b{width:300px;height:300px;background:#22d3ee;top:28%;right:-90px;animation-delay:-6s}
.dev-orb.c{width:260px;height:260px;background:#34d399;bottom:-70px;left:28%;animation-delay:-10s}
@keyframes devfloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(24px,-34px) scale(1.08)}}
.dev-glass{background:rgba(255,255,255,.06);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
  border:1px solid rgba(255,255,255,.12);border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.08);
  transition:box-shadow .25s ease;position:relative;z-index:1}
.dev-glass:hover{box-shadow:0 16px 50px rgba(124,92,255,.3),inset 0 1px 0 rgba(255,255,255,.12)}
.dev-tilt{perspective:1000px}
.dev-tilt .dev-glass{transform-style:preserve-3d;transition:transform .15s ease}
.dev-title{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#22d3ee;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:6px}
.dev-badge-ok{background:rgba(52,211,153,.15);color:#34d399;border:1px solid rgba(52,211,153,.3)}
.dev-badge-idle{background:rgba(147,161,181,.12);color:#93a1b5}
.dev-badge-err{background:rgba(251,113,133,.15);color:#fb7185}
.dev-task{border-bottom:1px solid rgba(255,255,255,.07);padding:9px 0;font-size:13px;animation:devfade .4s}
.dev-task:last-child{border-bottom:none}
.dev-pill{padding:2px 9px;border-radius:12px;font-size:11px}
.dev-pill.todo{background:rgba(147,161,181,.15);color:#93a1b5}
.dev-pill.prog{background:rgba(124,92,255,.2);color:#c4b5fd}
.dev-pill.done{background:rgba(52,211,153,.18);color:#34d399}
#dev-log{background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px;font-family:'JetBrains Mono',monospace;font-size:12px;height:200px;overflow-y:auto;white-space:pre-wrap;color:#86efac}
.dev-chat{width:100%;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:12px;color:#e6edf6;padding:10px;font-family:inherit;font-size:13px;min-height:64px;resize:vertical}
.dev-send{background:linear-gradient(90deg,#7c5cff,#22d3ee);color:#06121f;border:none;border-radius:10px;padding:8px 18px;font-weight:700}
.dev-kv{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.07)}
.dev-kv:last-child{border-bottom:none}
.dev-masked{color:#93a1b5;font-family:'JetBrains Mono',monospace}
.dev-tree{font-family:'JetBrains Mono',monospace;font-size:12px;max-height:220px;overflow-y:auto;color:#93a1b5}
.dev-tree .f{color:#e6edf6;cursor:pointer}.dev-tree .f:hover{color:#22d3ee}
.dev-diagram{background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:14px;font-family:'JetBrains Mono',monospace;font-size:12px;line-height:1.6}
.dev-diagram .cls{color:#c4b5fd}.dev-diagram .rel{color:#22d3ee}
.dev-hubcard{cursor:pointer;border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px;background:rgba(255,255,255,.04);transition:.2s}
.dev-hubcard:hover{border-color:#7c5cff;transform:translateY(-3px)}
.dev-done{width:9px;height:9px;border-radius:50%;background:#34d399;box-shadow:0 0 8px #34d399;display:inline-block}
.dev-todo{width:9px;height:9px;border-radius:50%;background:#fbbf24;display:inline-block}
@keyframes devfade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.dev-fade{animation:devfade .5s}
.dev-bottom-nav{display:none}
@media(max-width:768px){
  .dev-desktop{display:none!important}
  .dev-bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;z-index:30;justify-content:space-around;
    background:rgba(255,255,255,.06);backdrop-filter:blur(20px);border-top:1px solid rgba(255,255,255,.12);padding:8px 4px;padding-bottom:calc(8px + env(safe-area-inset-bottom))}
  .dev-bottom-nav button{flex:1;background:none;border:none;color:#93a1b5;font-size:11px;display:flex;flex-direction:column;align-items:center;gap:3px}
  .dev-bottom-nav button.active{color:#22d3ee}
  .dev-bottom-nav .ico{font-size:20px}
  .dev-mobile-page{display:none}
  .dev-mobile-page.active{display:block;padding-bottom:90px}
  .dev-fab{position:fixed;right:18px;bottom:80px;z-index:31;width:56px;height:56px;border-radius:50%;border:none;
    background:linear-gradient(135deg,#7c5cff,#22d3ee);color:#06121f;font-size:24px;box-shadow:0 8px 24px rgba(124,92,255,.5)}
}
@media(min-width:769px){.dev-mobile-page{display:block!important}}
</style>
@endpush

@section('content')
<div class="dev-orb a"></div><div class="dev-orb b"></div><div class="dev-orb c"></div>

{{-- ============ DESKTOP ============ --}}
<div class="container-fluid p-3 dev-desktop">
  <div class="row g-3">
    <div class="col-lg-3 col-md-6 dev-tilt"><div class="dev-glass p-3 h-100"><div class="dev-title">🟢 Agent Status</div>
      <div class="mb-2"><span class="badge dev-badge-ok" id="astat">● <span id="astat-t">checking…</span></span></div>
      <div class="dev-kv"><span>Current task</span><span id="ctask">—</span></div>
      <div class="dev-kv"><span>Model</span><span id="cmodel">Gemini 3.5 Flash</span></div>
      <div class="dev-kv"><span>Thinking</span><span id="cthink">medium</span></div>
      <div class="dev-kv"><span>Uptime</span><span id="up">00:00:00</span></div>
      <div class="dev-kv"><span>Git</span><span id="gitbranch">main</span></div>
      <div class="dev-kv"><span>Horizon</span><span id="horizon">—</span></div>
      <div class="dev-kv"><span>PHP</span><span id="phpv">—</span></div>
      <div class="dev-kv"><span>App files</span><span id="fc">—</span></div></div></div>
    <div class="col-lg-3 col-md-6 dev-tilt"><div class="dev-glass p-3 h-100"><div class="dev-title">📋 Task Board</div>
      <div id="taskboard"></div>
      <div class="mt-2 d-flex gap-2"><button class="btn btn-sm btn-outline-secondary" onclick="devAbort()">Abort</button><button class="btn btn-sm btn-outline-secondary" onclick="devRetry()">Retry</button></div></div></div>
    <div class="col-lg-3 col-md-6 dev-tilt"><div class="dev-glass p-3 h-100"><div class="dev-title">🎛️ Model Switcher</div>
      <select class="form-select form-select-sm bg-transparent text-light border-secondary mb-2" id="model"><option>Gemini 3.5 Flash</option><option>Gemini 3.1 Pro</option><option>Claude (Opus)</option></select>
      <label class="small text-muted">Thinking</label>
      <select class="form-select form-select-sm bg-transparent text-light border-secondary" id="think"><option>low</option><option selected>medium</option><option>high</option></select></div></div>
    <div class="col-lg-3 col-md-6 dev-tilt"><div class="dev-glass p-3 h-100"><div class="dev-title">🔐 Credentials (masked)</div>
      <div class="dev-kv"><span>Key 1 (Gemini)</span><span class="dev-masked">AIza••••</span></div>
      <div class="dev-kv"><span>Key 2 (OAuth)</span><span class="dev-masked">AQ.Ab••</span></div>
      <div class="dev-kv"><span>Key 3 (OAuth)</span><span class="dev-masked">AQ.Ab••</span></div>
      <div class="dev-kv"><span>Active key</span><span class="text-success" id="akey">Key 1</span></div></div></div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-lg-8 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">📜 Live Log <span class="badge dev-badge-idle">ws://localhost:9812</span></div>
      <div id="dev-log">[boot] Nexus Dev (Blade hub) loaded
[info] agent path = /www/wwwroot/Nexus/core/Nexus3
[info] polling /hub/dev/status every 3s
</div></div></div>
    <div class="col-lg-4 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">📁 Project Tree</div>
      <div class="dev-tree"><span class="f">📁 Nexus3/</span> <span class="f">📁 app/</span> <span class="f">📁 database/</span>
        <span class="f">📁 Documentation/</span> <span class="f">📁 dashboard/</span> <span class="f">📁 resources/</span>
        <span class="f">📄 composer.json</span> <span class="f">📄 .env</span></div></div></div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-lg-8 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">💬 Chat with Agent</div>
      <textarea class="dev-chat" id="chat" placeholder="Type a prompt for the dev agent... (Souly routes + verifies)"></textarea>
      <div class="mt-2 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Human-in-loop: Souly reviews before any deploy</span>
        <button class="dev-send" onclick="devSend()">Send →</button></div>
      <div id="ack" class="text-success mt-2 small"></div></div></div>
    <div class="col-lg-4 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">📡 Reports Feed (Souly)</div>
      <div id="reports"></div></div></div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-lg-6 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">🧩 Hub Feature Explorer</div>
      <div id="hublist" class="d-flex flex-wrap gap-2"></div>
      <div id="hubdetail" class="mt-3 small text-muted"></div></div></div>
    <div class="col-lg-6 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">🎨 Hub UI Gallery &amp; Status</div>
      <div id="uigallery" class="row g-2"></div></div></div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-lg-12 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">🧬 Class / ER Diagram <span class="badge dev-badge-idle">laravel-er-diagram-generator</span></div>
      <div class="dev-diagram" id="diagram">[ContactsHub] --hasMany--> [Contact]
[Contact] --hasMany--> [ContactMemory]
[AgentExecutionService] --runs--> [Agent]
[WorkflowExecutionService] --runs--> [Workflow]
[HedraSoulHub] --uses--> [MemoryAPI]
[Mem0Integration] --syncs--> [External Mem0]
<span class="rel">// run: php artisan er:generate --format=svg</span></div>
      <div class="mt-2"><button class="btn btn-sm btn-outline-secondary" onclick="devDiagram()">⚙️ Generate live ER diagram</button></div></div></div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-lg-4 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">📝 Task &amp; Prompt</div>
      <div class="dev-kv"><span>Task</span><span>Souly Core Docker</span></div>
      <div class="dev-kv"><span>Status</span><span class="text-success">done</span></div>
      <pre class="dev-diagram small mt-2" style="white-space:pre-wrap">"Create a Dockerfile for Souly Core: PHP 8.3, install composer, run php artisan serve on :8000, mount /root/nexus/core."</pre></div></div>
    <div class="col-lg-8 dev-tilt"><div class="dev-glass p-3"><div class="dev-title">🗨️ Execution Transcript (Souly ↔ Agent)</div>
      <div id="transcript" class="dev-diagram" style="height:200px;overflow-y:auto">[Souly] task-001 → agent (headless)
[Agent] ✅ wrote Dockerfile
[Agent] ✅ built image souly-core:latest
[Souly] verify: docker images → ok
[Souly] ✅ reported to Hedra
</div></div></div>
  </div>
</div>

{{-- ============ MOBILE APP ============ --}}
<div class="dev-mobile-page active" data-page="home">
  <div class="container p-2 pt-3">
    <div class="dev-glass p-3 dev-tilt mb-2"><div class="dev-title">🟢 Agent Status</div>
      <span class="badge dev-badge-ok">● <span id="astat-m">LIVE</span></span>
      <div class="dev-kv mt-2"><span>Task</span><span id="ctask-m">Build dashboard</span></div>
      <div class="dev-kv"><span>Model</span><span>Gemini 3.5 Flash</span></div></div>
    <div class="dev-glass p-3 dev-tilt mb-2"><div class="dev-title">📋 Tasks</div><div id="taskboard-m"></div></div>
    <div class="dev-glass p-3 dev-tilt"><div class="dev-title">📡 Reports</div><div id="reports-m"></div></div>
  </div>
</div>
<div class="dev-mobile-page" data-page="chat">
  <div class="container p-2 pt-3"><div class="dev-glass p-3 dev-tilt"><div class="dev-title">💬 Chat with Agent</div>
    <textarea class="dev-chat" id="chatm" placeholder="Prompt for dev agent..."></textarea>
    <button class="dev-send w-100 mt-2" onclick="devSendM()">Send →</button>
    <div id="ackm" class="text-success mt-2 small"></div></div></div>
</div>
<div class="dev-mobile-page" data-page="hubs">
  <div class="container p-2 pt-3"><div class="dev-glass p-3 dev-tilt"><div class="dev-title">🧩 Hubs</div><div id="hublistm" class="d-flex flex-wrap gap-2"></div></div></div>
</div>
<div class="dev-mobile-page" data-page="diagram">
  <div class="container p-2 pt-3"><div class="dev-glass p-3 dev-tilt"><div class="dev-title">🧬 Class Diagram</div>
    <div class="dev-diagram" id="diagramm">[ContactsHub]--hasMany-->[Contact]
[AgentExecutionService]--runs-->[Agent]</div>
    <button class="btn btn-sm btn-outline-secondary mt-2 w-100" onclick="devDiagram()">⚙️ Generate ER</button></div></div>
</div>
<div class="dev-mobile-page" data-page="log">
  <div class="container p-2 pt-3"><div class="dev-glass p-3 dev-tilt"><div class="dev-title">📜 Live Log</div>
    <div id="logm" class="dev-diagram" style="height:300px;overflow-y:auto">[boot] mobile mode
[info] agent path = Nexus3</div></div></div>
</div>

<button class="dev-fab" onclick="devQuick()">⚡</button>
<nav class="dev-bottom-nav">
  <button class="active" data-go="home"><span class="ico">🏠</span>Home</button>
  <button data-go="chat"><span class="ico">💬</span>Chat</button>
  <button data-go="hubs"><span class="ico">🧩</span>Hubs</button>
  <button data-go="diagram"><span class="ico">🧬</span>Diagram</button>
  <button data-go="log"><span class="ico">📜</span>Log</button>
</nav>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script>
// 3D tilt
document.querySelectorAll('.dev-tilt').forEach(function(el){
  el.addEventListener('mousemove',function(e){var r=el.getBoundingClientRect();var x=(e.clientX-r.left)/r.width-.5;var y=(e.clientY-r.top)/r.height-.5;var g=el.querySelector('.dev-glass');g.style.transform='rotateY('+(x*6)+'deg) rotateX('+(-y*6)+'deg) translateZ(8px)';});
  el.addEventListener('mouseleave',function(){var g=el.querySelector('.dev-glass');g.style.transform='';});
});
// mobile nav
document.querySelectorAll('.dev-bottom-nav button').forEach(function(b){b.addEventListener('click',function(){
  document.querySelectorAll('.dev-bottom-nav button').forEach(x=>x.classList.remove('active'));b.classList.add('active');
  var p=b.dataset.go;document.querySelectorAll('.dev-mobile-page').forEach(function(m){m.classList.toggle('active',m.dataset.page===p);});});});
// hubs
var HUBS=[
  {n:'Contacts Hub',f:['CRUD','Memory','Intelligence'],d:true},
  {n:'AI Models Hub',f:['Provider registry','Cost'],d:true},
  {n:'Agents Hub',f:['Agent types','MCP'],d:true},
  {n:'Workflows Hub',f:['Triggers','DAG'],d:true},
  {n:'Tasks Hub',f:['Board','Assign'],d:true},
  {n:'Hedra Soul',f:['Souly core','Approval'],d:true},
  {n:'Proactive AI',f:['Rules','Alerts'],d:true},
  {n:'People Connect',f:['WhatsApp','Msg'],d:true},
  {n:'Memory Hub',f:['Versioned','Decay'],d:true},
  {n:'Logs Hub',f:['Dual-write','Telemetry'],d:true},
  {n:'Scheduler Hub',f:['Cron','Heartbeat'],d:true},
  {n:'Settings Hub',f:['API proxy','Keys'],d:true},
  {n:'Admin Hub',f:['Ops','Users'],d:true}
];
function renderHubs(t){var h='';HUBS.forEach(function(x){h+='<div class="dev-hubcard" style="flex:1 1 140px" onclick="devHub(\''+x.n+'\')"><div><span class="'+(x.d?'dev-done':'dev-todo')+'"></span> '+x.n+'</div><div class="small text-muted mt-1">'+(x.d?'✅ done':'🟡 pending')+'</div></div>';});$(t).html(h);}
function devHub(n){var h=HUBS.find(x=>x.n===n);$('#hubdetail').html('<b>'+n+'</b><br>Features: '+(h?h.f.join(', '):'')+'<br>Status: '+(h&&h.d?'<span class="text-success">implemented &amp; verified</span>':'pending'));}
renderHubs('#hublist');renderHubs('#hublistm');
var ui='';HUBS.slice(0,6).forEach(function(h){ui+='<div class="col-6"><div class="dev-hubcard"><span class="'+(h.d?'dev-done':'dev-todo')+'"></span> '+h.n+'<div class="small text-muted">blade ✓</div></div></div>';});$('#uigallery').html(ui);
// seed task board
var TASKS=[['Souly Core Docker','done'],['SOUL-CORE.md','prog'],['Wire Telegram gateway','todo'],['Build dashboard UI','prog']];
function renderTasks(){var h='';TASKS.forEach(function(t){h+='<div class="dev-task d-flex justify-content-between"><span>'+t[0]+'</span><span class="dev-pill '+t[1]+'">'+(t[1]==='done'?'done':t[1]==='prog'?'in progress':'todo')+'</span></div>';});$('#taskboard').html(h);$('#taskboard-m').html(h);}
renderTasks();
var REPORTS=[['✅ Dockerfile + SOUL-CORE','05:10'],['🔄 gateway wiring','05:14']];
function renderReports(){$('#reports').html(REPORTS.map(r=>'<div class="dev-task d-flex justify-content-between"><span>'+r[0]+'</span><span class="text-muted small">'+r[1]+'</span></div>').join(''));$('#reports-m').html($('#reports').html());}
renderReports();
// live status poll (real project telemetry + agent probe)
function poll(){$.get('/hub/dev/status').done(function(s){
  $('#astat-t').text(s.server_up?'RUNNING (agent)':'IDLE');
  $('#astat-m').text(s.server_up?'RUNNING':'IDLE');
  $('#ctask').text(s.current_task||'—');$('#ctask-m').text(s.current_task||'—');
  $('#cmodel').text(s.model);$('#cthink').text(s.thinking);
  $('#akey').text('Key '+(s.active_key||1));
  $('#gitbranch').text((s.git&&s.git.branch)||'—');
  $('#horizon').text(s.horizon||'—');
  $('#phpv').text(s.php||'—');
  $('#fc').text((s.files_count||'—')+' php');
  if(s.server_up){$('#dev-log').append('\n[poll] agent='+s.agent);}
  else{$('#dev-log').append('\n[poll] project live · horizon='+s.horizon);}
}).always(function(){setTimeout(poll,3000);});}
poll();
// send command (LIVE → /hub/dev/command → localhost:5000/send_command)
function devSend(){var t=$('#chat').val();if(!t.trim())return;devLog('[Souly→agent] '+t);$('#ack').text('⏳ Queued → Souly routes, verifies, reports.');$.post('/hub/dev/command',{command:t}).done(function(r){if(r.success){$('#ack').text('✅ '+r.message);devLog('[agent] queued ok');}else{$('#ack').text('⚠️ '+r.message);}}).fail(function(){$('#ack').text('⚠️ agent server unreachable');});$('#chat').val('');}
function devSendM(){var t=$('#chatm').val();if(!t.trim())return;devLog('[Souly→agent] '+t);$('#ackm').text('⏳ Queued → Souly.');$.post('/hub/dev/command',{command:t}).done(function(r){$('#ackm').text(r.success?'✅ queued':'⚠️ '+r.message);}).fail(function(){$('#ackm').text('⚠️ unreachable');});$('#chatm').val('');}
function devLog(m){var el=$('#dev-log');el.append('\n'+m);el.scrollTop=el[0].scrollHeight;$('#logm').append('\n'+m);}
function devAbort(){devLog('[Souly] ⛔ Abort requested');}
function devRetry(){devLog('[Souly] 🔄 Retry requested');}
function devQuick(){$('[data-go=chat]').click();$('#chatm').focus();}
function devDiagram(){$('#diagram').append('\n[generated] php artisan er:generate → er-diagram.svg');$('#diagramm').text($('#diagram').text());}
// uptime
var s=0;setInterval(function(){s++;$('#up').text([String(Math.floor(s/3600)).padStart(2,'0'),String(Math.floor(s%3600/60)).padStart(2,'0'),String(s%60).padStart(2,'0')].join(':'));},1000);
</script>
@endpush
