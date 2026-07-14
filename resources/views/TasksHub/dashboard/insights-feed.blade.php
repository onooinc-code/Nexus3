<div class="row g-3">
    <!-- AI Insights Panel -->
    <div class="col-md-6">
        <div class="tasks-glass-card p-3 h-100 d-flex flex-column">
            <h6 class="mb-3 font-inter text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-brain me-2 text-primary"></i>AI Insights</h6>
            <div id="ai-insights-container" class="flex-grow-1 overflow-auto" style="max-height: 300px;">
                <!-- Placeholder Insight -->
                <div class="p-3 mb-2 rounded" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-lightbulb text-primary mt-1"></i>
                        <div>
                            <p class="mb-2 text-light" style="font-size: 0.8rem; line-height: 1.4;">
                                "Queue depth has been consistently low today. System is running optimally. No action required."
                            </p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary" style="font-size: 0.7rem; padding: 2px 8px;">Acknowledge</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="col-md-6">
        <div class="tasks-glass-card p-3 h-100 d-flex flex-column">
            <h6 class="mb-3 font-inter text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-bolt me-2 text-warning"></i>Live Activity Feed</h6>
            <div id="dashboard-activity-feed" class="flex-grow-1 overflow-auto pe-2" style="max-height: 300px;">
                <!-- Loading State -->
                <div class="text-center text-muted py-4">
                    <i class="fa-solid fa-circle-notch fa-spin mb-2"></i>
                    <p class="font-mono mb-0" style="font-size: 0.75rem;">Listening for events...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // AI Insights Logic
    function generateInsights() {
        if (!window.TaskAPI) return;
        TaskAPI.get('/tasks/stats').done(function(res) {
            if(!res.data) return;
            const stats = res.data;
            const container = document.getElementById('ai-insights-container');
            container.innerHTML = ''; // Clear

            let insights = [];
            
            let total = stats.total || 0;
            let failed = stats.failed || 0;
            let failureRate = total > 0 ? (failed / total) : 0;

            if (failureRate > 0.3) {
                insights.push({
                    type: 'danger',
                    icon: 'fa-triangle-exclamation',
                    text: `High failure rate detected (${(failureRate*100).toFixed(1)}%). Consider investigating the Dead Letter Queue for patterns.`,
                    action: 'Open DLQ'
                });
            }

            // Assuming we stored queued count locally or we fetch it
            TaskAPI.get('/tasks/queue-stats').done(function(qRes) {
                if(qRes.data && qRes.data.queued > 50) {
                    insights.push({
                        type: 'warning',
                        icon: 'fa-layer-group',
                        text: `Queue is building up (${qRes.data.queued} tasks). Possible backpressure soon.`,
                        action: 'View Queue Monitor'
                    });
                }

                if(insights.length === 0) {
                    insights.push({
                        type: 'primary',
                        icon: 'fa-lightbulb',
                        text: "System is operating within normal parameters. Automation flows are healthy.",
                        action: null
                    });
                }

                insights.forEach(insight => {
                    let btnHtml = insight.action ? `<button class="btn btn-sm btn-${insight.type}" style="font-size: 0.7rem; padding: 2px 8px;">${insight.action}</button>` : '';
                    let colorCode = insight.type === 'primary' ? '99, 102, 241' : (insight.type === 'danger' ? '239, 68, 68' : '245, 158, 11');
                    
                    let html = `
                    <div class="p-3 mb-2 rounded" style="background: rgba(${colorCode}, 0.1); border: 1px solid rgba(${colorCode}, 0.2); animation: slide-in-right 0.3s ease;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid ${insight.icon} text-${insight.type} mt-1"></i>
                            <div>
                                <p class="mb-2 text-light" style="font-size: 0.8rem; line-height: 1.4;">"${insight.text}"</p>
                                <div class="d-flex gap-2">${btnHtml}</div>
                            </div>
                        </div>
                    </div>`;
                    container.insertAdjacentHTML('beforeend', html);
                });
            });
        });
    }

    // Run once on load
    setTimeout(generateInsights, 1500);
    // Poll every 60s
    setInterval(function() {
        if ($('#content-dashboard').hasClass('active')) {
            generateInsights();
        }
    }, 60000);

    // Activity Feed Logic via Laravel Echo
    const feedContainer = document.getElementById('dashboard-activity-feed');
    
    function addFeedEntry(eventData) {
        // Remove loading state if present
        if(feedContainer.querySelector('.fa-circle-notch')) {
            feedContainer.innerHTML = '';
        }
        
        let color = 'var(--text-muted)';
        let icon = 'fa-circle-info';
        
        if (eventData.status === 'completed') { color = 'var(--tasks-success)'; icon = 'fa-circle-check'; }
        else if (eventData.status === 'failed') { color = 'var(--tasks-danger)'; icon = 'fa-circle-xmark'; }
        else if (eventData.status === 'in-progress') { color = 'var(--tasks-primary)'; icon = 'fa-play'; }

        let timeStr = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
        
        let html = `
        <div class="d-flex align-items-start gap-2 mb-2" style="animation: slide-in-right 0.3s ease;">
            <div class="mt-1" style="color: ${color}; font-size: 0.7rem;"><i class="fa-solid ${icon}"></i></div>
            <div>
                <div class="text-light" style="font-size: 0.8rem;">Task #${eventData.task_id} updated to <span style="color: ${color};">${eventData.status}</span></div>
                <div class="text-muted font-mono" style="font-size: 0.65rem;">${timeStr}</div>
            </div>
        </div>
        `;
        
        feedContainer.insertAdjacentHTML('afterbegin', html);
        
        // Keep only last 50
        if(feedContainer.children.length > 50) {
            feedContainer.lastElementChild.remove();
        }
    }

    if (window.Echo) {
        window.Echo.channel('tasks.activity')
            .listen('TaskStatusUpdated', (e) => {
                addFeedEntry(e);
            });
    } else {
        // Fallback polling for feed if no websockets
        setInterval(function() {
            if ($('#content-dashboard').hasClass('active')) {
                 TaskAPI.get('/tasks', { per_page: 1, sort: 'updated_at', dir: 'desc' }).done(function(res) {
                     if(res.data && res.data.length > 0) {
                         let latest = res.data[0];
                         // Prevent duplicate showing (basic check)
                         if(feedContainer.innerHTML.indexOf('Task #' + latest.id) === -1) {
                             addFeedEntry({task_id: latest.id, status: latest.status});
                         }
                     }
                 });
            }
        }, 15000);
    }
});
</script>
