@include('hubs.partials.ai-hub.logs-audit.toolbar')

<div class="tab-content">
    @include('hubs.partials.ai-hub.logs-audit.request-logs')
    @include('hubs.partials.ai-hub.logs-audit.cache-inspector')
</div>

@include('hubs.partials.ai-hub.logs-audit.drawers.log-details')
